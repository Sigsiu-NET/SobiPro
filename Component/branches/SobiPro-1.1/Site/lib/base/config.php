<?php
/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:24:15 PM
 */
class SPConfig
{
	/**
	 * @var array
	 */
	private $_store = array();
	/**
	 * @var bool
	 */
	static $cs = false;

	private static $fields = array();

	private function __construct()
	{
		SPLoader::loadClass( 'cms.base.fs' );
		SPLoader::loadClass( 'base.registry' );
	}

	/**
	 * Simple initialisation method
	 *
	 */
	public function init()
	{
		if ( self::$cs ) {
			Sobi::Error( 'config', SPLang::e( 'CRITICAL_SECTION_VIOLATED' ), SPC::ERROR, 500, __LINE__, __CLASS__ );
		}
		/* define critical section to avoid infinite loops */
		self::$cs = true;
		$nameField = self::key( 'entry.name_field' );
		if ( $nameField ) {
			$fc = SPLoader::loadModel( 'field' );
			$field = new $fc();
			$field->init( $nameField );
			$this->set( 'name_field_nid', $field->get( 'nid' ), 'entry' );
			$this->set( 'name_field_id', $field->get( 'fid' ), 'entry' );
		}
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( self::key( 'language.adm_domain' ) ) {
				SPLang::registerDomain( self::key( 'language.adm_domain' ) );
			}
		}
		else {
			if ( self::key( 'language.domain' ) ) {
				SPLang::registerDomain( self::key( 'language.domain' ) );
			}
		}
		/* set allowed request attributes and tags */
		SPRequest::setTagsAllowed( $this->key( 'html.allowed_tags_array' ) );
		SPRequest::setAttributesAllowed( $this->key( 'html.allowed_attributes_array' ) );
		$this->_store[ 'general' ][ 'root' ] = SOBI_ROOT;
		$this->_store[ 'general' ][ 'path' ] = SOBI_PATH;
		$this->_store[ 'general' ][ 'cms' ] = SOBI_CMS;
		$this->_store[ 'general' ][ 'live_path' ] = SOBI_LIVE_PATH;

		/* leave critical section */
		self::$cs = false;
	}

	/**
	 * Singleton - returns instance of the config object
	 *
	 * @return SPConfig
	 */
	public static function & getInstance()
	{
		static $config = false;
		if ( !$config || !( $config instanceof SPConfig ) ) {
			$config = new SPConfig();
		}
		return $config;
	}

	/**
	 * getting config values from ini file
	 *
	 * @param string $path
	 * @param bool $sections
	 * @param string $defSection
	 * @return bool
	 */
	public function addIniFile( $path, $sections = true, $adm = false, $defSection = 'general' )
	{
		if ( !( $arr = SPLoader::loadIniFile( $path, $sections, $adm ) ) ) {
			Sobi::Error( 'config', sprintf( 'CANNOT_PARSE_INI_FILE', $path ), SPC::WARNING, 0, __LINE__, __CLASS__ );
			return false;
		}
		if ( is_array( $arr ) && !empty( $arr ) ) {
			if ( $sections ) {
				foreach ( $arr as $section => $values ) {
					if ( !isset( $this->_store[ $section ] ) ) {
						$this->_store[ $section ] = array();
					}
					$currSec =& $this->_store[ $section ];
					if ( !empty( $values ) ) {
						foreach ( $values as $k => $v ) {
							$_c = explode( '_', $k );
							if ( $_c[ count( $_c ) - 1 ] == 'array' ) {
								$v = explode( '|', $v );
							}
							$currSec[ $k ] = $this->structuralData( $v );
						}
					}
				}
			}
			else {
				$currSec =& $this->_store[ $defSection ];
				foreach ( $arr as $k => $v ) {
					$currSec[ $k ] = $v;
				}
			}
			return true;
		}
		else {
			Sobi::Error( 'config', SPLang::e( 'EMPTY_INIFILE', $path ), SPC::WARNING, 0, __LINE__, __CLASS__ );
			return false;
		}
	}

	/**
	 * getting config values from database table
	 *
	 * @param string $table name of table
	 * @param int $id object id/directory section number
	 * @param string $section name of row where the section name is stored
	 * @param string $key name of row where the key name is stored
	 * @param string $value name of row where the value is stored
	 * @param bool $parseObject parse directory section
	 * @return bool
	 */
	public function addTable( $table, $id = 0, $section = 'cSection', $key = 'sKey', $value = 'sValue', $object = 'section', $parseObject = true )
	{
		/* var SPDb $db */
		$db =& SPFactory::db();
		$where = null;
		$order = 'configsection';
		if ( $parseObject ) {
			if ( $id ) {
				$where = array( $object => array( 0, $id ) );
				$order = "{$object}, configsection";
			}
			else {
				$where = array( $object => 0 );
			}
		}
		try {
			$db->select( array( "{$section} AS configsection", "{$key} AS sKey", "{$value} AS sValue" ), $table, $where, $order );
			$config = $db->loadObjectList();
			foreach ( $config as $row ) {
				if ( !isset( $this->_store[ $row->configsection ] ) ) {
					$this->_store[ $row->configsection ] = array();
				}
				$_c = explode( '_', $row->sKey );
				if ( $_c[ count( $_c ) - 1 ] == 'array' ) {
					try {
						$row->sValue = self::unserialize( $row->sValue );
					} catch ( SPException $x ) {
						Sobi::Error( 'config', $x->getMessage() . ' [ ' . $row->sKey . ' ] ', SPC::WARNING, 0, __LINE__, __CLASS__ );
					}
				}
				if ( $row->configsection == 'debug' && $row->sKey == 'level' ) {
					if ( !( defined( 'PHP_VERSION_ID' ) ) || PHP_VERSION_ID < 50300 ) {
						$row->sKey = $row->sKey == 30719 ? 6143 : $row->sKey;
					}
				}
				$this->_store[ $row->configsection ][ $row->sKey ] = $this->structuralData( $row->sValue );
			}
		} catch ( SPException $x ) {
			Sobi::Error( 'config', $x->getMessage(), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
		return true;
	}

	public function structuralData( $data, $force = false )
	{
		if ( is_string( $data ) && strstr( $data, '://' ) ) {
			$data = explode( '://', $data );
			switch ( $data[ 0 ] ) {
				case 'json':
					if ( strstr( $data[ 1 ], "':" ) || strstr( $data[ 1 ], "{'" ) || strstr( $data[ 1 ], "['" ) ) {
						$data[ 1 ] = str_replace( "'", '"', $data[ 1 ] );
					}
					$data = json_decode( $data[ 1 ] );
					break;
				case 'serialized':
					if ( strstr( $data[ 1 ], "':" ) || strstr( $data[ 1 ], ":'" ) || strstr( $data[ 1 ], "['" ) ) {
						$data[ 1 ] = str_replace( "'", '"', $data[ 1 ] );
					}
					$data = unserialize( $data[ 1 ] );
					break;
				case 'csv':
					if ( function_exists( 'str_getcsv' ) ) {
						$data = str_getcsv( $data[ 1 ] );
					}
					else {
						Sobi::Error( 'config', 'Function "str_getcsv" does not exists' );
					}
					break;
			}
		}
		elseif ( is_string( $data ) && $force ) {
			if ( strstr( $data, '|' ) ) {
				$data = explode( '|', $data );
			}
			elseif ( strstr( $data, ',' ) ) {
				$data = explode( ',', $data );
			}
			elseif ( strstr( $data, ';' ) ) {
				$data = explode( ';', $data );
			}
			else {
				$data = array( $data );
			}
		}
		return $data;
	}

	/**
	 * Storing key
	 *
	 * @param string $label
	 * @param mixed $var
	 * @param string $section
	 * @return bool
	 */
	public function set( $label, $var, $section = 'general' )
	{
		if ( !isset( $this->_store[ $section ][ $label ] ) ) {
			$this->_store[ $section ][ $label ] = $var;
			return true;
		}
		else {
			/** @todo need to think here something **/
			//Sobi::Error( 'config', SPLang::e( 'SET_EXISTING_KEY', $label ), SPC::NOTICE, 0, __LINE__, __CLASS__ );
			return false;
		}
	}

	/**
	 * Storing key
	 *
	 * @param string $label
	 * @param mixed $var
	 * @param string $section
	 * @return bool
	 */
	public function change( $label, $var, $section = "general" )
	{
		if ( isset( $this->_store[ $section ][ $label ] ) ) {
			$this->_store[ $section ][ $label ] = $var;
			return true;
		}
		else {
			Sobi::Error( 'config', SPLang::e( 'CHANGE_NOT_EXISTING_KEY', $label ), SPC::NOTICE, 0, __LINE__, __CLASS__ );
			return false;
		}
	}

	/**
	 * Deleting stored variable
	 *
	 * @param string $label
	 * @param string $section
	 * @return bool
	 */
	public function unsetKey( $label, $section = "general" )
	{
		if ( isset( $this->_store[ $section ][ $label ] ) ) {
			unset( $this->_store[ $section ][ $label ] );
			return true;
		}
		else {
			Sobi::Error( 'config', SPLang::e( 'UNSET_NOT_EXISTING_KEY', $label ), SPC::NOTICE, 0, __LINE__, __CLASS__ );
			return false;
		}
	}

	/**
	 * Returns copy of stored key
	 *
	 * @param string $label
	 * @param mixed $def
	 * @param string $section
	 * @return mixed
	 */
	public function get( $label, $def = null, $section = 'general' )
	{
		return $this->key( $label, $def, $section );
	}

	/**
	 * Returns copy of stored key
	 *
	 * @param string $label
	 * @param mixed $def
	 * @param string $section
	 * @return mixed
	 */
	public function key( $key, $def = null, $section = 'general' )
	{
		if ( strstr( $key, '.' ) ) {
			$key = explode( '.', $key );
			$section = $key[ 0 ];
			$key = $key[ 1 ];
		}
		$return = isset( $this->_store[ $section ][ $key ] ) ? $this->_store[ $section ][ $key ] : $def;

		/* the config value can contain other config keys to pars in the form:
		   * [cfg:live_site] - deprecated or {cfg:live_site}
		   */
		if ( is_string( $return ) && ( strstr( $return, '[cfg:' ) || strstr( $return, '{cfg:' ) ) ) {
			preg_match_all( '/\[cfg:([^\]]*)\]/', $return, $matches );
			if ( !( isset( $matches[ 1 ] ) ) || !( count( $matches[ 1 ] ) ) ) {
				preg_match_all( '/\{cfg:([^}]*)\}/', $return, $matches );
			}
			if ( count( $matches[ 1 ] ) ) {
				foreach ( $matches[ 1 ] as $i => $replacement ) {
					$return = str_replace( $matches[ 0 ][ $i ], $this->key( $replacement ), $return );
				}
			}
		}
		return $return;
	}

	public function getSettings()
	{
		return $this->_store;
	}

	/**
	 * checking if variable is already stored
	 *
	 * @param string $label
	 * @param string $section
	 * @return bool
	 */
	public function keyIsset( $label, $section = "general" )
	{
		return isset( $this->_store[ $section ][ $label ] ) ? true : false;
	}

	/**
	 * returns backtrace array
	 * @return array
	 */
	public static function getBacktrace()
	{
		return self::backtrace( false, $out = array( 'file', 'line', 'function', 'class' ), true, false, false );
	}


	/**
	 * creates backtrace array
	 * @param bool $store - store into a file
	 * @param array $out - format
	 * @param bool $return as array
	 * @param bool $hide - embed within html comment
	 * @param bool $do - output directly
	 * @return mixed
	 */
	public static function backtrace( $store = false, $out = array( 'file', 'line', 'function', 'class' ), $return = false, $hide = false, $do = true )
	{
		$trace = array();
		$backtrace = debug_backtrace();
		if ( count( $backtrace ) ) {
			foreach ( $backtrace as $level ) {
				$l = array();
				foreach ( $out as $i ) {
					$l[ $i ] = isset( $level[ $i ] ) ? $level[ $i ] : 'none';
					$l[ $i ] = str_replace( SOBI_ROOT, null, $l[ $i ] );
				}
				$trace[ ] = $l;
			}
		}
		if ( $do ) {
			return self::debOut( $trace, $hide, $return, $store );
		}
		else {
			return $trace;
		}
	}

	/**
	 * Creates debug output
	 * @param mixed $str - string/object/array to parse
	 * @param bool $hide - embed within a HTML comment
	 * @param bool $return - return or output directly
	 * @param bool $store - store within a file
	 * @return mixed
	 */
	public static function debOut( $str = null, $hide = false, $return = false, $store = false )
	{
		$return = $store ? 1 : $return;
		if ( !$str ) {
			$str = 'Empty';
		}
		if ( $hide ) {
			echo "\n\n<!-- Sobi Pro Debug: ";
		}
		elseif ( !( $return ) ) {
			echo "<h4>";
		}
		if ( is_object( $str ) /*|| is_array( $str )*/ ) {
			try {
				$str = @var_export( $str );
				$str = trim( highlight_string( $str, true ) );
			} catch ( Exception $x ) {
				$str = $x->getMessage();
			}
		}
		elseif ( is_array( $str ) ) {
			$str = @print_r( $str, true );
			$str = highlight_string( $str, true );
		}
		if ( !( $return ) ) {
			echo $str;
		}
		if ( $hide ) {
			echo "  -->\n\n";
		}
		elseif ( !( $return ) ) {
			echo "</h4>";
		}
		if ( $store ) {
			file_put_contents(
				SPLoader::path( 'var.log.debug', 'front', false, 'html' ),
					'<br/>[' . date( DATE_RFC822 ) . "]<br/>{$str}<br/>",
				SPC::FS_APP
			);
		}
		elseif ( $return ) {
			return $str;
		}
	}

	/**
	 *  Tries to reverts date created by calendar field to database acceptable format
	 *
	 * @param string $str
	 * @param string $format
	 * @return double
	 */
	public function rdate( $str, $format = 'calendar.date_format' )
	{
//        $date = array();
//        $format = $this->key( $format );
//        $format = preg_replace( '/[^\w]/', '_', $format );
//        $format = str_replace( array( 'dd', 'y' ), array( 'd', 'Y' ), $format );
//        $format = explode( '_', $format );
//        $str = preg_replace( '/[^\w]/', '_', $str );
//        $str = explode( '_', $str );
//        foreach ( $format as $i => $k ) {
//            $date[ strtolower( $k ) ] = $str[ $i ];
//        }
//        $str = null;
//        $str .= isset( $date[ 'd' ] ) ? $date[ 'd' ] : ' ';
//        $str .= ' ';
//        /** @todo find alternative for it */
//        //$str .= isset( $date[ 'm' ] ) ? SPFactory::lang()->revert( $date[ 'm' ] ) : ' ';
//        $str .= isset( $date[ 'm' ] ) ? $date[ 'm' ] : ' ';
//        $str .= ' ';
//        $str .= isset( $date[ 'y' ] ) ? $date[ 'y' ] : ' ';
//        if ( isset( $date[ 'h' ] ) && isset( $date[ 'h' ] ) && isset( $date[ 'h' ] ) ) {
//            $str .= ' ' . $date[ 'h' ] . ' ' . $date[ 'i' ] . ' ' . $date[ 's' ];
//        }
//        return strtotime( $str );
		$date = array();
		$format = $this->key( $format );
		$format = preg_replace( '/[^\w]/', '_', $format );
		$format = str_replace( array( 'dd', 'y' ), array( 'd', 'Y' ), $format );
		$format = explode( '_', $format );
		$str = preg_replace( '/[^\w]/', '_', $str );
		$str = explode( '_', $str );
		foreach ( $format as $i => $k ) {
			$date[ strtolower( $k ) ] = $str[ $i ];
		}
		$str = null;
		$str .= isset( $date[ 'y' ] ) ? $date[ 'y' ] : ' ';
		$str .= '-';
		/** @todo find alternative for it */
		//$str .= isset( $date[ 'm' ] ) ? SPFactory::lang()->revert( $date[ 'm' ] ) : ' ';
		$str .= isset( $date[ 'm' ] ) ? $date[ 'm' ] : ' ';
		$str .= '-';
		$str .= isset( $date[ 'd' ] ) ? $date[ 'd' ] : ' ';

		if ( isset( $date[ 'h' ] ) && isset( $date[ 'h' ] ) && isset( $date[ 'h' ] ) ) {
			$str .= ' ' . $date[ 'h' ] . ':' . $date[ 'i' ] . ':' . $date[ 's' ];
		}
		return strtotime( $str );
	}

	/**
	 * Returns the name/title field
	 * @return SPField
	 */
	public function nameField()
	{
		if ( !( isset( self::$fields[ Sobi::Section() ][ Sobi::Cfg( 'entry.name_field' ) ] ) ) ) {
			if ( Sobi::Cfg( 'entry.name_field' ) ) {
				/* @var SPField $f */
				$f = SPFactory::Model( 'field', true );
				$f->init( Sobi::Cfg( 'entry.name_field' ) );
				self::$fields[ Sobi::Section() ][ Sobi::Cfg( 'entry.name_field' ) ] = $f;
			}
			else {
				SPFactory::message()->warning( 'NO_NAME_FIELD_SELECTED' );
			}
		}
		return isset( self::$fields[ Sobi::Section() ][ Sobi::Cfg( 'entry.name_field' ) ] ) ? self::$fields[ Sobi::Section() ][ Sobi::Cfg( 'entry.name_field' ) ] : SPFactory::Model( 'field', true ) ;
	}

//	/**
//	 * Returns the name/title field
//	 * @return SPField
//	 */
//	public function sectionFields()
//	{
//		if( Sobi::Section() ) {
//			if( !( isset( self::$fields[ Sobi::Section() ] ) && count( self::$fields[ Sobi::Section() ] ) ) ) {
//				$db =& SPFactory::db();
//		        try {
//		        	$db->select( '*', 'spdb_field', array( 'section' => $sid ), 'position' );
//		        	$fields[ $sid ] = $db->loadObjectList();
//		        	Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$fields ) );
//		        }
//		        catch ( SPException $x ) {
//		        	Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
//		        }
//			}
//			return self::$fields[ Sobi::Section() ];
//		}
//	}

	/**
	 * Returns formated date
	 *
	 * @param string $time - time or date
	 * @param string $format - section and key in the config
	 * @return string
	 */
	public function date( $time = null, $formatKey = 'date.publishing_format', $format = null )
	{
		if ( $time == SPFactory::db()->getNullDate() ) {
			return null;
		}
		if ( !( is_numeric( $time ) ) ) {
			$time = strtotime( $time );
		}
		if ( !( $time ) || ( $time < 0 ) ) {
			return 0;
		}
		if ( !( $format ) ) {
			$format = $this->key( $formatKey, 'Y-m-d H:i:s' );
		}
		$date = $time ? ( is_numeric( $time ) ? $time : strtotime( $time ) ) : time();
		return date( $format, $date );
	}

	/**
	 * @param string $var
	 * @return mixed
	 */
	public static function unserialize( $var, $name = null )
	{
		$r = null;
		if ( is_string( $var ) && strlen( $var ) > 2 ) {
			if ( ( $var2 = base64_decode( $var, true ) ) ) {
				if ( function_exists( 'gzinflate' ) ) {
					if ( ( $r = @gzinflate( $var2 ) ) ) {
						if ( !$r = unserialize( $r ) ) {
							throw new SPException( sprintf( 'Cannot unserialize compressed variable %s', $name ) );
						}
					}
					else {
						if ( !( $r = @unserialize( $var2 ) ) ) {
							throw new SPException( sprintf( 'Cannot unserialize raw (?) encoded variable %s', $name ) );
						}
					}
				}
				else {
					if ( !( $r = @unserialize( $var2 ) ) ) {
						throw new SPException( sprintf( 'Cannot unserialize raw encoded variable %s', $name ) );
					}
				}
			}
			else {
				if ( !( $r = @unserialize( $var ) ) ) {
					throw new SPException( sprintf( 'Cannot unserialize raw variable %s', $name ) );
				}
			}
		}
		return $r;
	}

	/**
	 * @param mixed $var
	 * @return string
	 */
	public static function serialize( $var )
	{
		if ( !( is_string( $var ) ) && ( is_array( $var ) && count( $var ) ) || is_object( $var ) ) {
			$var = serialize( $var );
		}
		if ( is_string( $var ) && function_exists( 'gzdeflate' ) && ( strlen( $var ) > 500 ) ) {
			$var = gzdeflate( $var, 9 );
		}
		if ( is_string( $var ) && strlen( $var ) > 2 ) {
			$var = base64_encode( $var );
		}
		return is_string( $var ) ? $var : null;
	}

	/**
	 * @param $key
	 * @param $val
	 * @param $cfgSection
	 * @return SPConfig
	 */
	public function & saveCfg( $key, $val, $cfgSection = 'general' )
	{
		if ( Sobi::Can( 'configure', 'section' ) ) {
			if ( strstr( $key, '.' ) ) {
				$key = explode( '.', $key );
				$cfgSection = $key[ 0 ];
				$key = $key[ 1 ];
			}
			Sobi::Trigger( 'Config', 'Save', array( &$key, &$val, &$cfgSection ) );
			/* @var SPdb $db */
			$db =& SPFactory::db();
			try {
				$db->insertUpdate( 'spdb_config', array( 'sKey' => $key, 'sValue' => $val, 'section' => Sobi::Reg( 'current_section', 0 ), 'critical' => 0, 'cSection' => $cfgSection ) );
			} catch ( SPException $x ) {
				Sobi::Error( 'config', SPLang::e( 'CANNOT_SAVE_CONFIG', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
			}
		}
		return $this;
	}

	/**
	 * Returns linked lists ( names or ids ) of parent elements to the given id
	 *
	 * @param int $id - the id of object
	 * @param bool $names - names or ids only
	 * @return array
	 */
	public function getParentPath( $id, $names = false, $parents = false, $join = false )
	{
		/** @todo We have to implement the MySQL procedure for it */
		$db = SPFactory::db();
		if ( !( is_numeric( $id ) ) ) {
			return false;
		}
		$ident = 'relations_path' . ( $names ? '_names' : '' ) . ( $parents ? '_parents' : '' ) . ( $join ? '_join' : '' );
		$cached = SPFactory::cache()->getVar( $ident, $id );
		if ( $cached ) {
			return $cached;
		}
		else {
			$cid = $id;
		}
		$path = $parents ? array() : array( $id );
		while ( $id > 0 ) {
			try {
				$id = $db
						->select( 'pid', 'spdb_relations', array( 'id' => $id ) )
						->loadResult();
				if ( $id ) {
					$path[ ] = ( int )$id;
				}
			} catch ( SPException $x ) {
				Sobi::Error( __FUNCTION__, SPLang::e( 'CANNOT_GET_PARENT_ID', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
			}
		}
		if ( $names && count( $path ) ) {
			$names = SPLang::translateObject( $path, 'name', array( 'section', 'category', 'entry' ) );
			if ( is_array( $names ) && !empty( $names ) ) {
				foreach ( $path as $i => $id ) {
					if ( $join ) {
						$path[ $i ] = array( 'id' => $id, 'name' => $names[ $id ][ 'value' ] );
					}
					else {
						$path[ $i ] = $names[ $id ][ 'value' ];
					}
				}
			}
		}
		$path = array_reverse( $path );
		SPFactory::cache()->addVar( $path, $ident, $cid );
		return $path;
	}
}
