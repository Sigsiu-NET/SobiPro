<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
defined( 'SQLITE_ASSOC' ) || define( 'SQLITE_ASSOC', null );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 16-Aug-2009 9:54:07
 */
final class SPCache
{
	/*** @var SQLiteDatabase */
	protected $_db = null;
	/** @var string */
	protected $_driver = '';
	/** @var bool|string */
	protected $_enabled = true;
	/** @var bool */
	protected $_apc = true;
	/** @var null */
	protected $_store = null;
	/** @var null */
	protected $_check = null;
	/** @var int */
	protected $_section = -1;
	/** @var int */
	protected $_sid = 0;
	/** @var array */
	protected $_disableObjectCache = [ '.save', '.clone', '.payment', '.submit', '.approve', '.publish', '.icon' ];
	/** @var array */
	protected $requestStore = [];
	/** @var array */
	protected $view = [ 'xml' => null, 'template' => null ];
	/** @var array */
	protected $_disableViewCache = [ 'entry.add', 'entry.edit', 'search.search', 'search.results', 'entry.disable', 'txt.js' ];
	/** @var bool */
	protected $_cachedView = false;
	/** @var array */
	protected $cacheViewQuery = [];
	/** @var array */
	protected $cacheViewRequest = [];

	/**
	 * Singleton - returns instance of the config object
	 *
	 * @param int $sid
	 * @return SPCache
	 */
	public static function & getInstance( $sid = 0 )
	{
		if ( !( $sid ) ) {
			$sid = Sobi::Section();
		}
		if ( !( $sid ) ) {
			$sid = -1;
		}
		static $cache = [];
		if ( !( isset( $cache[ $sid ] ) ) || !( $cache[ $sid ] instanceof self ) ) {
			$cache[ $sid ] = new self( $sid );
		}
		return $cache[ $sid ];
	}

	protected function close()
	{
		switch ( $this->_driver ) {
			case 'SQLITE':
				$this->_db = null;
				unset( $this->_db );
				//sqlite_close( $this->_db );
				break;
			case 'PDO':
				//http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html#6
				$this->_db = null;
				break;
		}
	}

	protected function __construct( $sid )
	{
		$this->_section = $sid;
		$this->_enabled = Sobi::Cfg( 'cache.l3_enabled', true );
		$this->requestStore = $_REQUEST;
		$this->_apc = Sobi::Cfg( 'cache.apc_enabled', false );
		if ( $this->_apc ) {
			$this->_apc = extension_loaded( 'apc' ) && function_exists( 'apc_fetch' );
		}
		$this->initialise();
	}

	protected function initialise()
	{
		if ( $this->_enabled ) {
//			$sid = Sobi::Section();
//			$this->_section = $sid ? $sid : $this->_section;
			$this->_store = Sobi::Cfg( 'cache.store', SOBI_PATH . '/var/cache/' );
			if ( !( strlen( $this->_store ) ) ) {
				$this->_store = SOBI_PATH . '/var/cache/';
			}
			if ( SPFs::exists( SOBI_PATH . '/var/reset' ) ) {
				$this->cleanAll();
				SPFs::delete( SOBI_PATH . '/var/reset' );
				if ( SPLoader::path( 'etc.extensions', 'front', true, 'xml' ) ) {
					SPFs::delete( SPLoader::path( 'etc.extensions', 'front', false, 'xml' ) );
				}
			}
			$init = SPFs::exists( $this->_store . '.htCache_' . $this->_section . '.db' ) ? false : true;
			if ( class_exists( 'SQLiteDatabase' ) ) {
				$msg = null;
				$this->_driver = 'SQLITE';
				try {
					$this->_db = new SQLiteDatabase( $this->_store . '.htCache_' . $this->_section . '.db', 0400, $msg );
					if ( strlen( $msg ) ) {
						Sobi::Error( 'cache', sprintf( 'SQLite error: %s', $msg ), SPC::WARNING, 0, __LINE__, __FILE__ );
						$this->_enabled = false;
						$this->cleanAll();
					}
				} catch ( SQLiteException $e ) {
					Sobi::Error( 'cache', sprintf( 'SQLite error: %s', $e->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$this->_enabled = false;
					$this->cleanAll();
				}
			}
			elseif ( class_exists( 'PDO' ) ) {
				try {
					$this->_driver = 'PDO';
					$this->_db = new PDO( 'sqlite:' . $this->_store . '.htCache_' . $this->_section . '.db' );
				} catch ( PDOException $e ) {
					Sobi::Error( 'cache', sprintf( 'SQLite database not supported. %s', $e->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$this->_enabled = false;
					$this->cleanAll();
				}
			}
			else {
				Sobi::Error( 'cache', sprintf( 'SQLite database not supported' ), SPC::WARNING, 0, __LINE__, __FILE__ );
				$this->_enabled = false;
				$this->disable();
			}
			if ( $init && $this->_enabled ) {
				$this->init();
			}
		}
	}

	protected function disable()
	{
		if ( defined( 'SOBIPRO_ADM' ) ) {
			SPFactory::config()
					->saveCfg( 'cache.l3_enabled', false );
		}
	}

	protected function Query( $query )
	{
		//		SPConfig::debOut( $query, false, false, true );
		switch ( $this->_driver ) {
			case 'SQLITE':
				try {
					if ( $r = $this->_db->query( $query, SQLITE_ASSOC ) ) {
						$r = $r->fetch();
					}
					else {
						Sobi::Error( 'cache', sprintf( 'SQLite error on query: %s', $query ), SPC::WARNING, 0, __LINE__, __FILE__ );
						return false;
					}
				} catch ( SQLiteException $x ) {
					Sobi::Error( 'cache', sprintf( 'SQLite error: %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$this->_enabled = false;
					$this->cleanAll();
					return false;
				}
				break;
			case 'PDO':
				if ( $s = $this->_db->prepare( $query ) ) {
					$s->execute();
					$r = $s->fetch( PDO::FETCH_ASSOC );
				}
				else {
					Sobi::Error( 'cache', sprintf( 'SQLite error on query: %s. Error %s', $query, implode( "\n", $this->_db->errorInfo() ) ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$this->_enabled = false;
					$this->cleanAll();
					return false;
				}
				break;
		}
		return $r;
	}

	protected function Exec( $query )
	{
		switch ( $this->_driver ) {
			case 'SQLITE':
				try {
					$this->_db->queryExec( $query );
				} catch ( SQLiteException $x ) {
					Sobi::Error( 'cache', sprintf( 'SQLite error: %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
				break;
			case 'PDO':
				$this->_db->exec( $query );
				break;
		}
	}

	/**
	 * Clean cache of a section
	 * @param $section - section id. If not given, current section will be used
	 * @param bool $system
	 * @return SPCache
	 */
	public function & cleanSection( $section = 0, $system = true )
	{
		$sid = $section ? $section : $this->_section;
		$this->cleanJCache();
		if ( $section == Sobi::Section() && $this->enabled() ) {
			$this->Exec( "BEGIN; DELETE FROM vars; COMMIT;" );
			$this->Exec( "BEGIN; DELETE FROM objects; COMMIT;" );
		}
		elseif ( SPFs::exists( $this->_store . '.htCache_' . $sid . '.db' ) ) {
			// we need an exception because this files are owned by Apache probably
			@unlink( $this->_store . '.htCache_' . $sid . '.db' );
			if ( SPFs::exists( $this->_store . '.htCache_' . $sid . '.db' ) ) {
				SPFs::delete( $this->_store . '.htCache_' . $sid . '.db' );
			}
		}
		if ( $sid > 0 ) {
			$this->cleanSection( -1 );
			$this->cleanApc();
		}
		if ( $system ) {
			SPFactory::message()->resetSystemMessages();
		}
		$this->cleanSectionXML( $this->_section );
		return $this;
	}

	public function & cleanCategories()
	{
		$file = SPLoader::dirPath( 'etc.categories' );
		if ( $file ) {
			SPFs::delete( $file );
		}
		return $this;
	}

	/**
	 * Clean cached variables of a section
	 * @param $section - section id. If not given, current section will be used
	 * @return SPCache
	 */
	public function & purgeSectionVars( $section = 0 )
	{
		$section = $section ? $section : $this->_section;
		$this->cleanTemp();
		if ( $this->enabled() ) {
			$section = $section ? $section : $this->_section;
			$this->Exec( "BEGIN; DELETE FROM vars WHERE( section = '{$section}' ); COMMIT;" );
		}
		$this->cleanXMLLists( $section );
		$this->cleanApc();
		return $this;
	}

	protected function cleanXMLLists( $section )
	{
		$section = $section ? $section : $this->_section;
		if ( Sobi::Cfg( 'cache.xml_enabled' ) ) {
			$xml = SPFactory::db()
					->select( [ 'cid', 'fileName' ], 'spdb_view_cache', [ 'section' => $section, 'task' => '%list.%' ] )
					->loadAssocList();
			$this->cleanXML( $xml );
			$xml = SPFactory::db()
					->select( [ 'cid', 'fileName' ], 'spdb_view_cache', [ 'sid' => $section ] )
					->loadAssocList();
			$this->cleanXML( $xml );
		}
	}

	public function cleanSectionXML( $section )
	{
		if ( Sobi::Cfg( 'cache.xml_enabled' ) ) {
			$xml = SPFactory::db()
					->select( [ 'cid', 'fileName' ], 'spdb_view_cache', [ 'section' => $section ] )
					->loadAssocList();
			$this->cleanXML( $xml );
		}
	}

	protected function cleanXML( $xml )
	{
		$this->cleanJCache();
		if ( count( $xml ) ) {
			$relations = [];
			foreach ( $xml as $cache ) {
				$file = SPLoader::path( 'var.xml.' . $cache[ 'fileName' ], 'front', true, 'xml' );
				if ( $file ) {
					SPFs::delete( $file );
				}
				$relations[ ] = $cache[ 'cid' ];
			}
			if ( count( $relations ) ) {
				SPFactory::db()
						->delete( 'spdb_view_cache_relation', [ 'cid' => $relations ] )
						->delete( 'spdb_view_cache', [ 'cid' => $relations ] );
			}
		}
	}

	public function & cleanXMLRelations( $sid )
	{
		if ( Sobi::Cfg( 'cache.xml_enabled' ) ) {
			if ( is_array( $sid ) ) {
				foreach ( $sid as $id ) {
					$this->cleanXMLRelations( $id );
				}
			}
			else {
				$xml = SPFactory::db()
						->select( 'cid', 'spdb_view_cache_relation', [ 'sid' => $sid ] )
						->loadResultArray();
				if ( count( $xml ) ) {
					$lang = Sobi::Lang( false );
					$files = SPFactory::db()
							->select( 'fileName', 'spdb_view_cache', [ 'cid' => $xml, 'language' => $lang ] )
							->loadResultArray();
					foreach ( $files as $file ) {
						$file = SPLoader::path( 'var.xml.' . $file, 'front', false, 'xml' );
						if ( $file ) {
							SPFs::delete( $file );
						}
					}
					SPFactory::db()
							->delete( 'spdb_view_cache_relation', [ 'cid' => $xml ] )
							->delete( 'spdb_view_cache', [ 'cid' => $xml ] );
				}
			}
		}
		return $this;
	}

	/**
	 * Store variable in to the cache
	 * @param mixed $var - variable to store
	 * @param string $id - identifier
	 * @param int $sid - id of an object
	 * @param string $lang - language
	 * @param \id|int $section - section id
	 * @return SPCache
	 */
	public function & addVar( $var, $id, $sid = 0, $lang = null, $section = 0 )
	{
		if ( $this->enabled() ) {
			if ( !( $var ) ) {
				$var = SPC::NO_VALUE;
			}
			$section = $section ? $section : $this->_section;
			$sid = ( int )$sid;
			$sid = $sid ? $sid : $section;
			$lang = $lang ? $lang : Sobi::Lang();
			$checksum = null; //md5( serialize( $var ) );
			if ( $this->_apc ) {
				apc_store( "com_sobipro_var_{$sid}_{$id}_{$lang}", $var );
			}
			$var = SPConfig::serialize( $var );
			$schecksum = md5( $var );
			$this->Exec( "BEGIN; REPLACE INTO vars ( name, validtime, section, sid, lang, params, checksum, schecksum, data ) VALUES( '{$id}', '0', '{$section}', '{$sid}', '{$lang}', NULL, '{$checksum}', '{$schecksum}', '{$var}' ); COMMIT;" );
		}
		return $this;
	}

	/**
	 * Returns variable stored in the cache
	 * @param string $id - identifier
	 * @param int $sid - id of an object
	 * @param string $lang - language
	 * @param \id|int $section - section id
	 * @return mixed - variable on success or false if not found
	 */
	public function getVar( $id, $sid = 0, $lang = null, $section = 0 )
	{
		if ( $this->enabled() ) {
			$section = $section ? $section : $this->_section;
			$lang = $lang ? $lang : Sobi::Lang( false );
			$sid = ( int )$sid;
			$sid = $sid ? $sid : $section;
			$apc = false;
			if ( $this->_apc ) {
				$var = apc_fetch( "com_sobipro_var_{$sid}_{$id}_{$lang}", $apc );
			}
			if ( !( $apc ) ) {
				$result = $this->Query( "SELECT * FROM vars WHERE( name = '{$id}' AND lang = '{$lang}' AND section = '{$section}' AND sid = '{$sid}' )" );
				if ( !( is_array( $result ) ) || !( count( $result ) ) || !( strlen( $result[ 'data' ] ) ) ) {
					return false;
				}
				if ( $result[ 'schecksum' ] != md5( $result[ 'data' ] ) ) {
					Sobi::Error( 'cache', SPLang::e( 'Checksum of the encoded variable does not match' ), SPC::WARNING, 0, __LINE__, __FILE__ );
					return false;
				}
				$var = SPConfig::unserialize( $result[ 'data' ] );
			}
			return $var;
		}
		else {
			return false;
		}
	}


	/**
	 * Store object in to the cache
	 * @param mixed $obj - object to store
	 * @param string $type - type of object entry/category/section
	 * @param int $id - id of the object
	 * @param int $sid
	 * @param bool $force
	 * @return SPCache
	 */
	public function & addObj( $obj, $type, $id, $sid = 0, $force = false )
	{
		if ( $this->enabled( !( $force ) ) ) {
			static $startTime = 0;
			if ( !( $startTime ) && class_exists( 'Sobi' ) ) {
				$start = Sobi::Reg( 'start' );
				$startTime = $start[ 1 ];
			}
			// storing need time - if we are over five seconds - skip
			if ( !defined( 'SOBIPRO_ADM' ) && !( $force ) && microtime( true ) - $startTime > 5 ) {
				return $this;
			}

			// it was the idea that if entry has been taken from cache, and do not reports any changes - it doesn't have to be stored again
			// but I'm not so sure if this is a good idea any longer
			// so let's skip it and see what's going to happen
			// poor guys from the testing team :P
			// Tue, Feb 19, 2013 14:09:52
			// it makes sense - otherwise the cache is being invalidated again and again
			// anyway stupid solution -  i have to reconsider it therefore @todo
			if ( $type == 'entry' ) {
				// entry has to report if it should be re-validate
				if ( !( isset( $this->_check[ $type ][ $id ] ) ) || !( $this->_check[ $type ][ $id ] ) ) {
					return $this;
				}
			}

			$id = ( int )$id;
			$sid = ( int )$sid;
			$sid = $sid ? $sid : $this->_section;
			$loaded = serialize( SPLoader::getLoaded() );
			$lang = Sobi::Lang( false );
			$checksum = null; //md5( serialize( $obj ) );
			if ( $this->_apc ) {
				$var = [ 'obj' => $obj, 'classes' => $loaded ];
				apc_store( "com_sobipro_{$sid}_{$id}_{$type}_{$lang}", $var );
			}
			$obj = SPConfig::serialize( $obj );
			$schecksum = md5( $obj );
			// the command is a "REPLACE" so there is actually no reason for deleting it anyway
			// the "deleteObj" causing however a chain reaction which would delete lot of other things so it doesn't make any sense here
//			$this->deleteObj( $type, $id, $sid );
			$this->Exec( "BEGIN; REPLACE INTO objects ( type, validtime, id, sid, lang, params, checksum, schecksum, data, classes ) VALUES( '{$type}', '0', '{$id}', '{$sid}', '{$lang}', NULL, '{$checksum}', '{$schecksum}', '{$obj}', '{$loaded}' ); COMMIT;" );
			$this->cleanJCache();
		}
		return $this;
	}

	/**
	 * Removes stored object from the cache
	 * @param string $type - type of object entry/category/section
	 * @param int $id - id of the object
	 * @param int $sid - section id
	 * @param string $lang
	 * @return SPCache
	 */
	public function & deleteObj( $type, $id, $sid = 0, $lang = null )
	{
		$reinit = false;
		$this->cleanJCache();
		if ( $this->enabled() ) {
			if ( $id && $this->_section == -1 ) {
				$section = SPFactory::config()->getParentPath( $id );
				$this->_section = $section[ 0 ];
				$this->initialise();
				$reinit = true;
			}
			$lang = $lang ? $lang : Sobi::Lang( false );
			$sid = $sid ? $sid : $this->_section;
			$this->Exec( "BEGIN; DELETE FROM objects WHERE( type LIKE '{$type}%' AND id = '{$id}' AND sid = '{$sid}' AND lang = '{$lang}' ); COMMIT;" );
			if ( $type == 'entry' ) {
				$this->Exec( "
					BEGIN;
						DELETE FROM objects WHERE( type = 'field_data' AND id = '{$id}' AND lang = '{$lang}' );
						DELETE FROM objects WHERE( type = 'entry_row' AND id = '{$id}' );
					COMMIT;
				" );
//				$a = "BEGIN; DELETE FROM objects WHERE( type = 'entry_row' AND id = '{$id}' ); COMMIT;";
			}
		}
		$this->cleanXMLRelations( $id );
		if ( $reinit ) {
			$this->_section = -1;
			$this->initialise();
		}
		return $this;
	}

	/**
	 * Removes stored variable from the cache
	 * @param string $id - identifier
	 * @param \id|int $section - section id
	 * @param string $lang
	 * @internal param string $lang - language
	 * @return SPCache
	 */
	public function & deleteVar( $id, $section = 0, $lang = null )
	{
		$this->cleanJCache();
		if ( $this->enabled() ) {
			$lang = $lang ? $lang : Sobi::Lang( false );
			$section = $section ? $section : $this->_section;
			$this->Exec( "BEGIN; DELETE FROM vars WHERE( name = '{$id}' AND section = '{$section}' AND lang = '{$lang}' ); COMMIT;" );
		}
		return $this;
	}

	/**
	 * @param $type
	 * @param $id
	 * @param int $sid
	 * @param bool $force
	 * @return mixed
	 */
	public function getObj( $type, $id, $sid = 0, $force = false )
	{
		if ( $this->enabled( !( $force ) ) ) {
			$sid = $sid ? $sid : $this->_section;
			$id = ( int )$id;
			$sid = ( int )$sid;
			$lang = Sobi::Lang( false );
			$apc = false;
			if ( $this->_apc ) {
				$var = apc_fetch( "com_sobipro_{$sid}_{$id}_{$type}_{$lang}", $apc );
				if ( isset( $var[ 'classes' ] ) ) {
					SPLoader::wakeUp( unserialize( $var[ 'classes' ] ) );
				}
			}
			if ( !( $apc ) ) {
				$result = $this->Query( "SELECT * FROM objects WHERE( type = '{$type}' AND id = '{$id}' AND lang = '{$lang}' AND sid = '{$sid}' )" );
				if ( !( is_array( $result ) ) || !( count( $result ) ) ) {
					return false;
				}
				if ( $result[ 'classes' ] ) {
					SPLoader::wakeUp( unserialize( $result[ 'classes' ] ) );
				}
				if ( $result[ 'schecksum' ] != md5( $result[ 'data' ] ) ) {
					Sobi::Error( 'cache', SPLang::e( 'Checksum of the encoded data does not match' ), SPC::WARNING, 0, __LINE__, __FILE__ );
					return false;
				}
				$var = SPConfig::unserialize( $result[ 'data' ] );
			}
			else {
				$var = $var[ 'obj' ];
			}
			$this->_check[ $type ][ $id ] = false;
			return $var;
		}
		else {
			return false;
		}
	}

	public function revalidate( $id, $type = 'entry' )
	{
		$this->_check[ $type ][ $id ] = true;
	}

	protected function enabled( $obj = false )
	{
		if ( $obj ) {
			if ( $this->_enabled && ( $this->_driver ) && class_exists( 'SPConfig' ) && Sobi::Cfg( 'cache.l3_enabled' ) ) {
				$currentTask = SPRequest::task();
				foreach ( $this->_disableObjectCache as $task ) {
					if ( strstr( $currentTask, $task ) ) {
						return false;
					}
				}
				return true;
			}
			else {
				return false;
			}
		}
		else
			return $this->_enabled && ( $this->_driver ) && class_exists( 'SPConfig' );
	}

	protected function init()
	{
		$this->Exec(
				"
				BEGIN;
				CREATE TABLE vars ( name CHAR(150), validtime int(11), section int(11) default NULL, sid int(11) default NULL, lang CHAR(50) default NULL, params text, checksum CHAR(150) default NULL, schecksum CHAR(150) default NULL, data blob, PRIMARY KEY( name, section, sid, lang ) );
				CREATE INDEX vars_name on vars( name );
				CREATE INDEX vars_section on vars( section );
				CREATE INDEX vars_sid on vars( sid );
				CREATE TABLE objects ( type CHAR(150), validtime int(11), id int(11) default NULL, sid int(11) default NULL, lang CHAR(50) default NULL, params text, checksum CHAR(150) default NULL, schecksum CHAR(150) default NULL, data blob, classes text, PRIMARY KEY( id, sid, lang ) );
				CREATE INDEX objects_name on objects( type );
				CREATE INDEX objects_section on objects( id );
				CREATE INDEX objects_sid on objects( sid );
				COMMIT;
				"
		);
	}

	/**
	 * @return SPCache
	 */
	public function & cleanAll()
	{
		$this->close();
		if ( $this->_store ) {
			$cache = scandir( $this->_store );
			if ( count( $cache ) ) {
				foreach ( $cache as $file ) {
					if ( SPFs::getExt( $file ) == 'db' ) {
						$c = Sobi::FixPath( "{$this->_store}/{$file}" );
						// we need an exception because this files are owned by Apache probably
						@unlink( $c );
						if ( SPFs::exists( $c ) ) {
							SPFs::delete( $c );
						}
					}
				}
			}
		}
		if ( Sobi::Cfg( 'cache.xml_enabled' ) ) {
			SPFactory::db()
					->truncate( 'spdb_view_cache_relation' )
					->truncate( 'spdb_view_cache' );
			$this->cleanDir( SPLoader::dirPath( 'var.xml', 'front' ), 'xml', true );
		}
		$this->cleanTemp( true );
		return $this;
	}

	protected function cleanDir( $dir, $ext, $force = false )
	{
		if ( $dir ) {
			$js = scandir( $dir );
			if ( count( $js ) ) {
				foreach ( $js as $file ) {
					if ( $file != '.' && $file != '..' && is_file( Sobi::FixPath( "{$dir}/{$file}" ) ) && ( SPFs::getExt( $file ) == $ext || $ext == -1 ) && ( $force || ( time() - filemtime( Sobi::FixPath( "{$dir}/{$file}" ) ) > ( 60 * 60 * 24 * 7 ) ) ) ) {
						SPFs::delete( Sobi::FixPath( "{$dir}/{$file}" ) );
					}
				}
			}
		}
	}

	protected function cleanTemp( $force = false )
	{
		$this->cleanJCache();
		$this->cleanDir( SPLoader::dirPath( 'var.js' ), 'js', $force );
		$this->cleanDir( SPLoader::dirPath( 'var.css' ), 'css', $force );
		$this->cleanDir( SPLoader::dirPath( 'tmp.edit' ), -1, $force );
		$this->cleanDir( SPLoader::dirPath( 'tmp.img' ), -1, $force );
		$this->cleanDir( SPLoader::dirPath( 'tmp' ), -1, $force );
		try {
			SPFactory::db()->delete( 'spdb_search', [ 'lastActive<' => 'FUNCTION:DATE_SUB( CURDATE() , INTERVAL 7 DAY )' ] );
		} catch ( SPException $x ) {
			Sobi::Error( 'cache', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$updatesDef = SPLoader::path( 'etc.updates', 'front', false, 'xml' );
		if ( SPFs::exists( $updatesDef ) ) {
			SPFs::delete( $updatesDef );
		}
	}

	public function view()
	{
		if ( !( Sobi::Cfg( 'cache.xml_enabled' ) ) || Sobi::Reg( 'break_cache_view' ) || ( Sobi::My( 'id' ) && Sobi::Cfg( 'cache.xml_no_reg' ) ) ) {
			return false;
		}
		if ( !( in_array( SPRequest::task(), $this->_disableViewCache ) ) ) {
			$cacheFile = null;
			$file = null;
			foreach ( $this->_disableObjectCache as $task ) {
				if ( strstr( SPRequest::task(), $task ) ) {
					return false;
				}
			}
			$query = $this->viewRequest();
			/** here comes an exception for the linked entries */
			$link = [];
			if ( isset( JFactory::getApplication()->getMenu()->getActive()->link ) ) {
				parse_str( JFactory::getApplication()->getMenu()->getActive()->link, $link );
			}

			/** now we know that it is directly linked but not if it is an entry link */
			if ( isset( $link[ 'sid' ] ) && $link[ 'sid' ] == SPRequest::sid() ) {
				$request = $this->cacheViewRequest;
				$request[ 'Itemid' ] = SPRequest::int( 'Itemid' );
				$query[ 'request' ] = str_replace( '"', null, json_encode( $request ) );
				$query[ 'task' ] = 'entry.details';
				$file = SPFactory::db()
						->select( [ 'fileName', 'template', 'configFile', 'cid' ], 'spdb_view_cache', $query )
						->loadRow();
			}
			if ( !( $file ) ) {
				$query = $this->viewRequest();
				$file = SPFactory::db()
						->select( [ 'fileName', 'template', 'configFile', 'cid' ], 'spdb_view_cache', $query )
						->loadRow();
			}
			if ( ( $file ) ) {
				$cacheFile = SPLoader::path( 'var.xml.' . $file[ 0 ], 'front', true, 'xml' );
			}
			if ( !( $cacheFile ) ) {
				return false;
			}
			$ini = [];
			if ( $file[ 2 ] ) {
				$configs = json_decode( str_replace( "'", '"', $file[ 2 ] ) );
				if ( count( $configs ) ) {
					$template = SPLoader::translateDirPath( Sobi::Cfg( 'section.template' ), 'templates' );
					foreach ( $configs as $config ) {
						$configFile = $template . $config->file;
						if ( file_exists( $configFile ) ) {
							if ( md5_file( $configFile ) != $config->checksum ) {
								return false;
							}
							$ini[ ] = $configFile;
						}
						else {
							return false;
						}
					}
				}
			}
			$xml = new DOMDocument();
			if ( !( $xml->load( $cacheFile ) ) ) {
				return false;
			}
			$this->_cachedView = true;
			return [ 'xml' => $xml, 'template' => $file[ 1 ], 'config' => $ini, 'cid' => $file[ 3 ] ];
		}
		else {
			return false;
		}
	}

	protected function viewRequest()
	{
		if ( !( count( $this->cacheViewQuery ) ) || Sobi::Reg( 'cache_view_recreate_request' ) ) {
			$request = [];
			if ( count( $this->requestStore ) ) {
				$keys = array_keys( $this->requestStore );
				foreach ( $keys as $k ) {
					if ( !( is_array( $_REQUEST[ $k ] ) ) ) {
						$request[ $k ] = SPRequest::string( $k );
					}
				}
			}

			$reserved = [ 'site', 'task', 'sid', 'dbg', 'Itemid', 'option', 'tmpl', 'format', 'crawl', 'language', 'lang' ];
			if ( Sobi::Reg( 'cache_view_add_itemid' ) ) {
				unset( $reserved[ array_search( 'Itemid', $reserved ) ] );
			}
			foreach ( $reserved as $var ) {
				if ( isset( $request[ $var ] ) ) {
					unset( $request[ $var ] );
				}
			}
			$this->cacheViewRequest = $request;
			$this->cacheViewQuery = [
					'section' => Sobi::Section(),
					'sid' => SPRequest::sid(),
					'task' => SPRequest::task(),
					'site' => SPRequest::int( 'site', 1 ),
					'request' => str_replace( '"', null, json_encode( $request ) ),
					'language' => Sobi::Lang(),
					'userGroups' => str_replace( '"', null, json_encode( Sobi::My( 'groups' ) ) ),
			];
		}
		return $this->cacheViewQuery;
	}

	/**
	 * @param $xml DOMDocument
	 * @param $template string
	 * @param array $data
	 * @return bool
	 */
	public function addView( $xml, $template, $data = [] )
	{
		if ( !( Sobi::Cfg( 'cache.xml_enabled' ) ) || $this->_cachedView || Sobi::Reg( 'break_cache_view' ) || ( Sobi::My( 'id' ) && Sobi::Cfg( 'cache.xml_no_reg' ) ) ) {
			return false;
		}
		if ( !( in_array( SPRequest::task( 'get' ), $this->_disableViewCache ) ) ) {
			foreach ( $this->_disableObjectCache as $task ) {
				if ( strstr( SPRequest::task(), $task ) ) {
					return false;
				}
			}
			if ( count( $_REQUEST ) ) {
				foreach ( $_REQUEST as $k => $v ) {
					if ( !( isset( $this->requestStore[ $k ] ) ) ) {
						$data[ 'request' ][ $k ] = SPRequest::string( $k );
					}
				}
			}
			$data[ 'pathway' ] = SPFactory::mainframe()->getPathway();
			$this->view[ 'xml' ] = $xml;
			$this->view[ 'template' ] = $template;
			$this->view[ 'data' ] = $data;
		}
	}

	/**
	 * @param $head
	 * @return bool
	 */
	public function storeView( $head )
	{
		if ( !( Sobi::Cfg( 'cache.xml_enabled' ) ) || $this->_cachedView || ( Sobi::My( 'id' ) && Sobi::Cfg( 'cache.xml_no_reg' ) ) ) {
			return false;
		}
		if ( $this->view[ 'xml' ] ) {
			$xml = $this->view[ 'xml' ];
			$template = Sobi::Reg( 'cache_view_template' );
			if ( !( $template ) ) {
				$template = $this->view[ 'template' ];
				$template = str_replace( SPLoader::translateDirPath( Sobi::Cfg( 'section.template' ), 'templates' ), null, $template );
			}
			$root = $xml->documentElement;
			$root->removeChild( $root->getElementsByTagName( 'visitor' )->item( 0 ) );
			if ( $root->getElementsByTagName( 'messages' )->length ) {
				$root->removeChild( $root->getElementsByTagName( 'messages' )->item( 0 ) );
			}
			/** @var $header DOMDocument */
			$header = SPFactory::Instance( 'types.array' )->toXML( $head, 'header', true );
			$root->appendChild( $xml->importNode( $header->documentElement, true ) );
			if ( $this->view[ 'data' ] && count( $this->view[ 'data' ] ) ) {
				$data = SPFactory::Instance( 'types.array' )->toXML( $this->view[ 'data' ], 'cache-data', true );
				$root->appendChild( $xml->importNode( $data->documentElement, true ) );
			}
			$request = $this->viewRequest();
			$request[ 'template' ] = $template;
			$configFiles = SPFactory::registry()->get( 'template_config' );
			$request[ 'configFile' ] = str_replace( '"', "'", json_encode( $configFiles ) );
			$request[ 'cid' ] = 'NULL';
			$request[ 'created' ] = 'FUNCTION:NOW()';
			$fileName = md5( serialize( $request ) );
			$request[ 'fileName' ] = $fileName;

			$filePath = SPLoader::path( 'var.xml.' . $fileName, 'front', false, 'xml' );
			$content = $xml->saveXML();
			$content = str_replace( '&nbsp;', '&#160;', $content );
			$content = preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', null, $content );
			$matches = [];
			preg_match_all( '/<(category|entry|subcategory)[^>]*id="(\d{1,})"/', $content, $matches );
			try {
				$cid = SPFactory::db()
						->insert( 'spdb_view_cache', $request, false, true )
						->insertid();
				$relations = [ SPRequest::sid() => [ 'cid' => $cid, 'sid' => SPRequest::sid() ] ];
				if ( isset( $matches[ 2 ] ) ) {
					$ids = array_unique( $matches[ 2 ] );
					foreach ( $ids as $sid ) {
						$relations[ $sid ] = [ 'cid' => $cid, 'sid' => $sid ];
					}
				}
				SPFactory::db()
						->insertArray( 'spdb_view_cache_relation', $relations );
				SPFs::write( $filePath, $content );
			} catch ( SPException $x ) {
				Sobi::Error( 'XML-Cache', $x->getMessage() );
			}
		}
	}

	public function & setJoomlaCaching( $enabled )
	{
		JFactory::getCache()->cache->setCaching( $enabled );
		if ( !( $enabled ) && SOBI_CMS == 'joomla3' ) {
			JEventDispatcher::getInstance()
					->detach( 'PlgSystemCache' );
		}
		return $this;
	}

	protected function cleanJCache()
	{
		static $go = true;
		if ( $go ) {
			$go = false;
			$options = [
					'defaultgroup' => 'page',
					'storage' => JFactory::getConfig()->get( 'cache_handler', '' ),
					'caching' => true,
					'cachebase' => JFactory::getConfig()->get( 'cache_path', JPATH_SITE . '/cache' )
			];
			JCache::getInstance( '', $options )->cache->clean( 'page' );
		}
	}

	protected function cleanApc()
	{
		if ( $this->_apc ) {
			$info = apc_cache_info( 'user' );
			foreach ( $info[ 'cache_list' ] as $obj ) {
				if ( isset( $obj[ 'key' ] ) && strstr( $obj[ 'key' ], 'com_sobipro' ) ) {
					apc_delete( $obj[ 'key' ] );
				}
				elseif ( isset( $obj[ 'info' ] ) && strstr( $obj[ 'info' ], 'com_sobipro' ) ) {
					apc_delete( $obj[ 'info' ] );
				}
			}
		}
	}
}
