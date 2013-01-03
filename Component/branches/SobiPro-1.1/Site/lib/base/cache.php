<?php
/**
 * @version: $Id: cache.php 2340 2012-04-04 11:59:40Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-04-04 13:59:40 +0200 (Wed, 04 Apr 2012) $
 * $Revision: 2340 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/cache.php $
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
	/**
	 * @var SQLiteDatabase
	 */
	private $_db = null;
	private $_driver = '';
	private $_enabled = true;
	private $_store = null;
	private $_check = null;
	private $_section = -1;
	private $_disableObjectCache = array( '.save', '.clone', '.payment', '.submit', '.approve', 'publish' );

	/**
	 * Singleton - returns instance of the config object
	 *
	 * @return SPCache
	 */
	public static function & getInstance()
	{
		static $cache = false;
		if ( !$cache || !( $cache instanceof self ) ) {
			$cache = new self();
		}
		return $cache;
	}

	private function close()
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

	private function __construct()
	{
		$this->_enabled = Sobi::Cfg( 'cache.l3_enabled', true );
		if ( $this->_enabled ) {
			$sid = Sobi::Section();
			$this->_section = $sid ? $sid : $this->_section;
			$this->_store = Sobi::Cfg( 'cache.store', SOBI_PATH . '/var/cache/' );
			if ( !( strlen( $this->_store ) ) ) {
				$this->_store = SOBI_PATH . DS . 'var' . DS . 'cache' . DS;
			}
			if ( SPFs::exists( SOBI_PATH . '/var/reset' ) ) {
				$this->cleanAll();
				SPFs::delete( SOBI_PATH . '/var/reset' );
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
					Sobi::Error( 'cache', sprintf( 'SQLite error: %s', $msg ), SPC::WARNING, 0, __LINE__, __FILE__ );
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

	private function disable()
	{
		if ( defined( 'SOBIPRO_ADM' ) ) {
			SPFactory::config()
					->saveCfg( 'cache.l3_enabled', false );
		}
	}

	private function Query( $query )
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

	private function Exec( $query )
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
	public function & cleanSection( $section = -1, $system = true )
	{
		$section = $section ? $section : $this->_section;
		if ( $section == Sobi::Section() && $this->enabled() ) {
			$this->Exec( "BEGIN; DELETE FROM vars; COMMIT;" );
			$this->Exec( "BEGIN; DELETE FROM objects; COMMIT;" );
		}
		elseif ( SPFs::exists( $this->_store . '.htCache_' . $section . '.db' ) ) {
			// we need an exception because this files are owned by Apache probably
			@unlink( $this->_store . '.htCache_' . $section . '.db' );
			if ( SPFs::exists( $this->_store . '.htCache_' . $section . '.db' ) ) {
				SPFs::delete( $this->_store . '.htCache_' . $section . '.db' );
			}
		}
		if ( $section > 0 ) {
			$this->cleanSection( 0 );
		}
		if( $system ) {
			SPFactory::message()->resetSystemMessages();
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
		$this->cleanTemp();
		if ( $this->enabled() ) {
			$section = $section ? $section : $this->_section;
			$this->Exec( "BEGIN; DELETE FROM vars WHERE( section = '{$section}' ); COMMIT;" );
		}
		return $this;
	}

	/**
	 * Store variable in to the cache
	 * @param mixed $var - variable to store
	 * @param string $id - identifier
	 * @param int $sid - id of an object
	 * @param string $lang - language
	 * @param id $section - section id
	 * @return SPCache
	 */
	public function & addVar( $var, $id, $sid = 0, $lang = null, $section = 0 )
	{
		if ( $this->enabled() ) {
			if ( !( $var ) ) {
				$var = SPC::NO_VALUE;
			}
			$section = $section ? $section : $this->_section;
			$lang = $lang ? $lang : Sobi::Lang();
			$checksum = null; //md5( serialize( $var ) );
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
	 * @param id $section - section id
	 * @return mixed - variable on success or false if not found
	 */
	public function getVar( $id, $sid = 0, $lang = null, $section = 0 )
	{
		if ( $this->enabled() ) {
			$section = $section ? $section : $this->_section;
			$lang = $lang ? $lang : Sobi::Lang( false );
			$result = $this->Query( "SELECT * FROM vars WHERE( name = '{$id}' AND lang = '{$lang}' AND section = '{$section}' AND sid = {$sid} )" );
			if ( !( is_array( $result ) ) || !( count( $result ) ) || !( strlen( $result[ 'data' ] ) ) ) {
				return false;
			}
			if ( $result[ 'schecksum' ] != md5( $result[ 'data' ] ) ) {
				Sobi::Error( 'cache', SPLang::e( 'Checksum of the encoded variable does not match' ), SPC::WARNING, 0, __LINE__, __FILE__ );
				return false;
			}
			$var = SPConfig::unserialize( $result[ 'data' ] );
			return $var;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $var
	 * @param $id
	 * @param $lang
	 * @param $section
	 * @return unknown_type
	 */
	public function addContent( $var, $id, $lang = null, $section = 0 )
	{

	}

	/**
	 * @param $var
	 * @param $id
	 * @param $lang
	 * @param $section
	 * @return unknown_type
	 */
	public function getContent( $var, $id, $lang = null, $section = 0 )
	{

	}

	/**
	 * Store object in to the cache
	 * @param mixed $obj - object to store
	 * @param string $type - type of object entry/category/section
	 * @param int $id - id of the object
	 * @param int $sid
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
			if ( !defined( 'SOBIPRO_ADM' ) && microtime( true ) - $startTime > 5 ) {
				return $this;
			}

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
			$obj = SPConfig::serialize( $obj );
			$schecksum = md5( $obj );
			$this->deleteObj( $type, $id, $sid );
			$this->Exec( "BEGIN; REPLACE INTO objects ( type, validtime, id, sid, lang, params, checksum, schecksum, data, classes ) VALUES( '{$type}', '0', '{$id}', '{$sid}', '{$lang}', NULL, '{$checksum}', '{$schecksum}', '{$obj}', '{$loaded}' ); COMMIT;" );
		}
		return $this;
	}

	/**
	 * Removes stored object from the cache
	 * @param string $type - type of object entry/category/section
	 * @param int $id - id of the object
	 * @param int $sid - section id
	 * @return SPCache
	 */
	public function & deleteObj( $type, $id, $sid = 0 )
	{
		if ( $this->enabled() ) {
			$this->Exec( "BEGIN; DELETE FROM objects WHERE( type LIKE '{$type}%' AND id = '{$id}' AND sid = '{$sid}' ); COMMIT;" );
			if ( $type == 'entry' ) {
				$this->Exec( "BEGIN; DELETE FROM objects WHERE( type = 'field_data' AND sid = '{$id}' ); COMMIT;" );
			}
		}
		return $this;
	}

	/**
	 * Removes stored variable from the cache
	 * @param string $id - identifier
	 * @param string $lang - language
	 * @param id $section - section id
	 * @return SPCache
	 */
	public function & deleteVar( $id, $section = 0 )
	{
		if ( $this->enabled() ) {
			$section = $section ? $section : $this->_section;
			$this->Exec( "BEGIN; DELETE FROM vars WHERE( name = '{$id}' AND section = '{$section}' ); COMMIT;" );
		}
		return $this;
	}

	/**
	 * @param $type
	 * @param $id
	 * @param $sid
	 * @return bool
	 */
	public function getObj( $type, $id, $sid = 0, $force = false )
	{
		if ( $this->enabled( !( $force ) ) ) {
			$id = ( int )$id;
			$sid = ( int )$sid;
			$lang = Sobi::Lang( false );
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

	private function enabled( $obj = false )
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

	private function init()
	{
		$this->Exec(
			"
			BEGIN;
			CREATE TABLE vars ( name CHAR(150), validtime int(11), section int(11) default NULL, sid int(11) default NULL, lang CHAR(50) default NULL, params text, checksum CHAR(150) default NULL, schecksum CHAR(150) default NULL, data blob, PRIMARY KEY( name, section, sid ) );
			CREATE INDEX vars_name on vars( name );
			CREATE INDEX vars_section on vars( section );
			CREATE INDEX vars_sid on vars( sid );
			CREATE TABLE objects ( type CHAR(150), validtime int(11), id int(11) default NULL, sid int(11) default NULL, lang CHAR(50) default NULL, params text, checksum CHAR(150) default NULL, schecksum CHAR(150) default NULL, data blob, classes text );
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
						$c = Sobi::FixPath( $this->_store . DS . $file );
						// we need an exception because this files are owned by Apache probably
						@unlink( $c );
						if ( SPFs::exists( $c ) ) {
							SPFs::delete( $c );
						}
					}
				}
			}
		}
		$this->cleanTemp( true );
		return $this;
	}

	private function cleanDir( $dir, $ext, $force = false )
	{
		if ( $dir ) {
			$js = scandir( $dir );
			if ( count( $js ) ) {
				foreach ( $js as $file ) {
					if (
						$file != '.' &&
						$file != '..' &&
						is_file( Sobi::FixPath( $dir . DS . $file ) ) &&
						( SPFs::getExt( $file ) == $ext || $ext == -1 ) &&
						( $force || ( time() - filemtime( Sobi::FixPath( $dir . DS . $file ) ) > ( 60 * 60 * 24 * 7 ) ) )
					) {
						SPFs::delete( Sobi::FixPath( $dir . DS . $file ) );
					}
				}
			}
		}
	}

	private function cleanTemp( $force = false )
	{
		$this->cleanDir( SPLoader::dirPath( 'var.js' ), 'js', $force );
		$this->cleanDir( SPLoader::dirPath( 'var.css' ), 'css', $force );
		$this->cleanDir( SPLoader::dirPath( 'tmp.edit' ), -1, $force );
		$this->cleanDir( SPLoader::dirPath( 'tmp.img' ), -1, $force );
		$this->cleanDir( SPLoader::dirPath( 'tmp' ), -1, $force );
		try {
			SPFactory::db()->delete( 'spdb_search', array( 'lastActive<' => 'FUNCTION:DATE_SUB( CURDATE() , INTERVAL 7 DAY )' ) );
		} catch ( SPException $x ) {
			Sobi::Error( 'cache', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$updatesDef = SPLoader::path( 'etc.updates', 'front', false, 'xml' );
		if ( SPFs::exists( $updatesDef ) ) {
			SPFs::delete( $updatesDef );
		}
	}
}
