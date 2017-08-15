<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 21-Jul-2010 17:11:46
 */
class SPRequirements extends SPController
{
	/**
	 * @var string
	 */
	protected $_defTask = 'view';
	const langFile = '/language/en-GB/en-GB.com_sobipro.check.ini';

	public function execute()
	{
		SPLang::load( 'com_sobipro.check' );
		$task = $this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		// this is need to delete all old cache after installation
		if ( SPRequest::int( 'init' ) ) {
			SPFactory::cache()->cleanAll();
		}
		switch ( $this->_task ) {
			case 'view':
				$this->view();
				break;
			case 'download':
				$this->download();
				break;
			default:
				if ( method_exists( $this, $this->_task ) ) {
					SPFactory::mainframe()
							->cleanBuffer()
							->customHeader();
					$this->$task();
					exit;
				}
				else {
					Sobi::Error( 'requirements', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
					exit;
				}
				break;
		}
	}

	private function txt( $text, $params = [] )
	{
		return [ 'current' => Sobi::Txt( $text, $params ), 'org' => [ 'label' => $text, 'params' => $params ] ];
	}

	private function mySQLCache()
	{
		try {
			SPFactory::db()->exec( 'SHOW VARIABLES LIKE "have_query_cache"' );
			$cache = SPFactory::db()->loadRow();
			if ( $cache[ 1 ] == 'YES' ) {
				echo $this->ok( $this->txt( 'REQ.MYSQL_CACHE_AVAILABLE' ), __FUNCTION__ );
			}
			else {
				echo $this->warning( $this->txt( 'REQ.MYSQL_CACHE_NOT_AVAILABLE' ), __FUNCTION__ );
			}
		} catch ( SPException $x ) {
			echo $this->warning( $this->txt( 'REQ.MYSQL_CACHE_CANNOT_CHECK' ), __FUNCTION__ );
		}
	}

	private function createView()
	{
		$db =& SPFactory::db();
		try {
			$db->exec( 'DROP VIEW IF EXISTS spView' );
		} catch ( SPException $x ) {
		}
		try {
			$db->exec( 'CREATE VIEW spView AS SELECT * FROM spdb_category' );
		} catch ( SPException $x ) {
			echo $this->warning( $this->txt( 'REQ.MYSQL_VIEWS_NOT_AVAILABLE' ), __FUNCTION__ );
			exit;
		}
		try {
			$db->exec( 'DROP VIEW IF EXISTS spView' );
		} catch ( SPException $x ) {
		}
		echo $this->ok( $this->txt( 'REQ.MYSQL_VIEWS_AVAILABLE' ), __FUNCTION__ );
	}

	private function createFunction()
	{
		$db =& SPFactory::db();
		try {
			$db->exec( 'DROP FUNCTION IF EXISTS SpStatFunc' );
			$db->commit();
		} catch ( SPException $x ) {
		}
		try {
			$db->exec( '
				CREATE FUNCTION SpStatFunc ( msg VARCHAR( 20 ) ) returns VARCHAR( 50 )
				BEGIN
					RETURN ( "Hello in SQL Function" );
				END
			' );
		} catch ( SPException $x ) {
			echo $this->warning( $this->txt( 'REQ.MYSQL_FUNCTIONS_NOT_AVAILABLE' ), __FUNCTION__ );
			exit;
		}
		$db->exec( 'DROP FUNCTION IF EXISTS SpStatFunc' );
		echo $this->ok( $this->txt( 'REQ.MYSQL_FUNCTIONS_AVAILABLE' ), __FUNCTION__ );
	}

	private function createProcedure()
	{
		$db =& SPFactory::db();
		try {
			$db->exec( 'DROP PROCEDURE IF EXISTS SpStatProc' );
			$db->commit();
		} catch ( SPException $x ) {
		}
		try {
			$db->exec( '
				CREATE PROCEDURE SpStatProc ( OUT resp INT )
				BEGIN
					SELECT COUNT(*) INTO resp FROM spdb_cache;
				END
			' );
		} catch ( SPException $x ) {
			echo $this->warning( $this->txt( 'REQ.MYSQL_PROCEDURES_NOT_AVAILABLE' ), __FUNCTION__ );
			exit;
		}
		$db->exec( 'DROP PROCEDURE IF EXISTS SpStatProc' );
		echo $this->ok( $this->txt( 'REQ.MYSQL_PROCEDURES_AVAILABLE' ), __FUNCTION__ );
	}

	private function mySQLcharset()
	{
		SPFactory::db()->exec( 'SELECT collation( "spdb_object" )' );
		$col = SPFactory::db()->loadResult();
		if ( !( strstr( $col, 'utf8' ) ) ) {
			echo $this->error( $this->txt( 'REQ.MYSQL_WRONG_COLL', [ 'collation' => $col ] ), __FUNCTION__ );
		}
		else {
			echo $this->ok( $this->txt( 'REQ.MYSQL_COLL_OK', [ 'collation' => $col ] ), __FUNCTION__ );
		}
	}

	private function mySQLversion()
	{
		$ver = SPFactory::db()->getVersion();
		$ver = preg_replace( '/[^0-9\.]/i', null, $ver );
		$ver = explode( '.', $ver );
		$ver = [ 'major' => $ver[ 0 ], 'minor' => $ver[ 1 ], 'build' => ( isset( $ver[ 2 ] ) ? substr( $ver[ 2 ], 0, 2 ) : 0 ) ];
		$minVer = [ 'major' => 5, 'minor' => 0, 'build' => 0 ];
		$rVer = [ 'major' => 5, 'minor' => 1, 'build' => 0 ];
		if ( !( $this->compareVersion( $minVer, $ver ) ) ) {
			echo $this->error( $this->txt( 'REQ.MYSQL_WRONG_VER', [ 'required' => implode( '.', $minVer ), 'installed' => implode( '.', $ver ) ] ), __FUNCTION__ );
		}
		elseif ( !( $this->compareVersion( $rVer, $ver ) ) ) {
			echo $this->warning( $this->txt( 'REQ.MYSQL_NOT_REC_VER', [ 'recommended' => implode( '.', $rVer ), 'installed' => implode( '.', $ver ) ] ), __FUNCTION__ );
		}
		else {
			echo $this->ok( $this->txt( 'REQ.MYSQL_VERSION_OK', [ 'installed' => implode( '.', $ver ) ] ), __FUNCTION__ );
		}
	}

	private function PEAR()
	{
		@include_once( 'PEAR.php' );
		$v = class_exists( 'PEAR' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.PEAR_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.PEAR_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function memoryLimit()
	{
		$v = ini_get( 'memory_limit' );
		$v = preg_replace( '/[^0-9]/i', null, $v );
		if ( $v >= 48 ) {
			echo $this->ok( $this->txt( 'REQ.MEM_LIM_IS', [ 'memory' => $v ] ), __FUNCTION__ );
		}
		elseif ( $v >= 32 ) {
			echo $this->warning( $this->txt( 'REQ.MEM_LIM_IS_LOW', [ 'memory' => $v ] ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.MEM_LIM_IS_TOO_LOW', [ 'memory' => $v ] ), __FUNCTION__ );
		}
	}

	private function maxExecutionTime()
	{
		$v = ini_get( 'max_execution_time' );
		$v = preg_replace( '/[^0-9]/i', null, $v );
		if ( $v == 0 ) {
			$options = ini_get_all();
			$v = $options[ 'max_execution_time' ][ 'global_value' ];
		}

		if ( $v >= 30 ) {
			echo $this->ok( $this->txt( 'REQ.MAX_EXEC_IS', [ 'limit' => $v ] ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.MAX_EXEC_IS_LOW', [ 'limit' => $v ] ), __FUNCTION__ );
		}
	}

	private function iniParse()
	{
		$v = function_exists( 'parse_ini_file' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.PARSE_INI_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.PARSE_INI_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function exec()
	{
		$v = function_exists( 'exec' ) ? true : false;
		$disabled = explode( ', ', ini_get( 'disable_functions' ) );
		if ( $v && ( !( in_array( 'exec', $disabled ) ) ) ) {
			echo $this->ok( $this->txt( 'REQ.EXEC_ENABLED' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.EXEC_NOT_ENABLED' ), __FUNCTION__ );
		}
	}

	private function PSpell()
	{
		$v = function_exists( 'pspell_check' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.PSPELL_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.PSPELL_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function Calendar()
	{
		$v = function_exists( 'cal_days_in_month' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.CALENDAR_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.CALENDAR_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function reflection()
	{
		$v = class_exists( 'ReflectionClass' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.REFLECTION_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.REFLECTION_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function filterFunctions()
	{
		$v = function_exists( 'filter_var' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.FILTER_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.FILTER_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function tidy()
	{
		$v = class_exists( 'tidy' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.TIDY_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.TIDY_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function ZipArchive()
	{
		$v = class_exists( 'ZipArchive' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.ZIP_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.ZIP_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	// @todo: ROTFL ;)
	private function json()
	{
		$v = function_exists( 'json_encode' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.JSON_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.JSON_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function OpenSSL()
	{
		$v = function_exists( 'openssl_x509_parse' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.OPENSSL_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.OPENSSL_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function SOAP()
	{
		$v = class_exists( 'SoapClient' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.SOAP_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.SOAP_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function CURL()
	{
		$v = function_exists( 'curl_init' ) ? true : false;
		if ( $v ) {
			$cfg = $this->curlFull();
			if ( $cfg[ 'available' ] && $cfg[ 'response' ][ 'http_code' ] == 200 ) {
				echo $this->ok( $this->txt( 'REQ.CURL_INSTALLED' ), __FUNCTION__ );
			}
			else {
				echo $this->warning( $this->txt( 'REQ.CURL_NOT_USABLE' ), __FUNCTION__ );
			}
		}
		else {
			echo $this->error( $this->txt( 'REQ.CURL_NOT_INSTALLED' ), __FUNCTION__ );
		}
	}

	private function PCRE()
	{
		$v = function_exists( 'preg_grep' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.REPC_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.REPC_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function SPL()
	{
		$v = class_exists( 'DirectoryIterator' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.SPL_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.SPL_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function XPath()
	{
		$v = class_exists( 'DOMXPath' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.DOMXPATH_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.DOMXPATH_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function XSL()
	{
		$v = class_exists( 'XSLTProcessor' ) ? true : false;
//		$v = false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.XSL_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.XSL_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function DOM()
	{
		$v = class_exists( 'DOMDocument' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.DOM_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.DOM_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function exif()
	{
		$v = function_exists( 'exif' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.EXIF_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.EXIF_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function SQLite()
	{
		$v = false;
		if ( class_exists( 'SQLiteDatabase' ) ) {
			$v = true;
		}
		else {
			if ( class_exists( 'PDO' ) ) {
				try {
					$db = new PDO( 'sqlite:' . Sobi::Cfg( 'cache.store', SOBI_PATH . DS . 'var' . DS . 'cache' . DS ) . '.htCache.db' );
					$v = true;
				} catch ( PDOException $e ) {
				}
			}
		}
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.SQLITE_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.SQLITE_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function gDlib()
	{
		$v = function_exists( 'gd_info' ) ? true : false;
		if ( $v ) {
			echo $this->ok( $this->txt( 'REQ.GD_AVAILABLE' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.GD_NOT_AVAILABLE' ), __FUNCTION__ );
		}
	}

	private function registerGlobals()
	{
		$v = ini_get( 'register_globals' );
		if ( !( $v ) || strtolower( $v ) == 'off' ) {
			echo $this->ok( $this->txt( 'REQ.RG_DISABLED' ), __FUNCTION__ );
		}
		else {
			echo $this->warning( $this->txt( 'REQ.RG_ENABLED' ), __FUNCTION__ );
		}
	}

	private function safeMode()
	{
		$v = ini_get( 'safe_mode' );
		if ( !( $v ) || strtolower( $v ) == 'off' ) {
			echo $this->ok( $this->txt( 'REQ.PHP_SAFE_MODE_DISABLED' ), __FUNCTION__ );
		}
		else {
			echo $this->error( $this->txt( 'REQ.PHP_SAFE_MODE_ENABLED' ), __FUNCTION__ );
		}
	}

	private function phpVersion()
	{
		$phpVer = $ver = preg_replace( '/[^0-9\.]/i', null, substr( PHP_VERSION, 0, 6 ) );
		$ver = explode( '.', $phpVer );
		$ver = [ 'major' => $ver[ 0 ], 'minor' => $ver[ 1 ], 'build' => ( isset( $ver[ 2 ] ) ? $ver[ 2 ] : 0 ) ];
		$minVer = [ 'major' => 5, 'minor' => 4, 'build' => 0 ];
		$rVer = [ 'major' => 7, 'minor' => 0, 'build' => 10 ];
		if ( !( $this->compareVersion( $minVer, $ver ) ) ) {
			echo $this->error( $this->txt( 'REQ.PHP_WRONG_VER', [ 'required' => implode( '.', $minVer ), 'installed' => implode( '.', $ver ) ] ), __FUNCTION__ );
		}
		elseif ( !( $this->compareVersion( $minVer, $ver ) ) ) {
			echo $this->warning( $this->txt( 'REQ.PHP_NOT_REC_VER', [ 'recommended' => implode( '.', $rVer ), 'installed' => implode( '.', $ver ) ] ), __FUNCTION__ );
		}
		else {
			echo $this->ok( $this->txt( 'REQ.PHP_VERSION_OK', [ 'installed' => implode( '.', $ver ) ] ), __FUNCTION__ );
		}
	}

	private function webServer()
	{
		$server = SPRequest::string( 'SERVER_SOFTWARE', getenv( 'SERVER_SOFTWARE' ), null, 'server' );
//		$server = 'Apache';
		$server = preg_split( '/[\/ ]/', $server );
		$soft = isset( $server[ 0 ] ) ? $server[ 0 ] : 'Unknown';
		$ver = isset( $server[ 1 ] ) ? preg_replace( '/[^0-9\.]/i', null, $server[ 1 ] ) : '0.0.0';
		$ver = explode( '.', $ver );
		$sapi = function_exists( 'php_sapi_name' ) ? php_sapi_name() : 'Unknown';
		if ( strtolower( $soft ) != 'apache' ) {
			echo $this->warning( $this->txt( 'REQ.WS_WRONG_SOFTWARE', [ 'webserver' => SPRequest::string( 'SERVER_SOFTWARE', getenv( 'SERVER_SOFTWARE' ), null, 'server' ) ] ), __FUNCTION__ );
		}
		else {
			$minVer = [ 'major' => 2, 'minor' => 0, 'build' => 0 ];
//			$rVer = array( 'major' => 2, 'minor' => 2, 'build' => 0 );
			if ( !( isset( $ver[ 0 ] ) && isset( $ver[ 1 ] ) && isset( $ver[ 2 ] ) ) || !( $ver[ 0 ] && $ver[ 1 ] ) ) {
				echo $this->warning( $this->txt( 'REQ.WS_NO_APACHE_VER', [ 'required' => implode( '.', $minVer ), 'sapi' => $sapi ] ), __FUNCTION__ );
				exit;
			}
			$ver = [ 'major' => $ver[ 0 ], 'minor' => $ver[ 1 ], 'build' => ( isset( $ver[ 2 ] ) ? $ver[ 2 ] : 0 ) ];
			if ( !( $this->compareVersion( $minVer, $ver ) ) ) {
				echo $this->error( $this->txt( 'REQ.WS_WRONG_VER', [ 'required' => implode( '.', $minVer ), 'installed' => implode( '.', $ver ), 'sapi' => $sapi ] ), __FUNCTION__ );
			}
//			elseif ( !( $this->compareVersion( $rVer, $ver ) ) ) {
//				echo $this->warning( $this->txt( 'REQ.WS_NOT_REC_VER', array( 'recommended' => implode( '.', $rminVer ), 'installed' => implode( '.', $ver ), 'sapi' => $sapi ) ), __FUNCTION__ );
//			}
			else {
				echo $this->ok( $this->txt( 'REQ.WS_VERSION_OK', [ 'installed' => implode( '.', $ver ), 'sapi' => $sapi ] ), __FUNCTION__ );
			}
		}
	}

	private function cmsEncoding()
	{
		$e = strtolower( SPFactory::CmsHelper()->cmsSetting( 'charset' ) );
		if ( $e != 'utf-8' ) {
			echo $this->error( $this->txt( 'REQ.CMS_ENCODING_NOK', [ 'encoding' => $e ] ), __FUNCTION__ );
		}
		else {
			echo $this->ok( $this->txt( 'REQ.CMS_ENCODING_OK', [ 'encoding' => $e ] ), __FUNCTION__ );
		}
	}

	private function cmsFtp()
	{
		$e = SPFactory::CmsHelper()->cmsSetting( 'ftp_enable' );
		if ( $e && $e != 'disabled' ) {
			echo $this->warning( $this->txt( 'REQ.CMS_FTP_NOK' ), __FUNCTION__ );
		}
		else {
			echo $this->ok( $this->txt( 'REQ.CMS_FTP_OK' ), __FUNCTION__ );
		}
	}

	private function cms()
	{
		$cmsVer = SPFactory::CmsHelper()->cmsVersion();
		$cmsName = SPFactory::CmsHelper()->cmsVersion( 'name' );
		$minVer = SPFactory::CmsHelper()->minCmsVersion();
		$rminVer = SPFactory::CmsHelper()->minCmsVersion( true );
		unset( $cmsVer[ 'rev' ] );
		unset( $minVer[ 'rev' ] );

		if ( !( $this->compareVersion( $minVer, $cmsVer ) ) ) {
			echo $this->error( $this->txt( 'REQ.CMS_WRONG_VER', [ 'cms' => $cmsName, 'required' => implode( '.', $minVer ), 'installed' => implode( '.', $cmsVer ) ] ), __FUNCTION__ );
		}
		elseif ( !( $this->compareVersion( $rminVer, $cmsVer ) ) ) {
			echo $this->warning( $this->txt( 'REQ.CMS_NOT_REC_VER', [ 'cms' => $cmsName, 'recommended' => implode( '.', $rminVer ), 'installed' => implode( '.', $cmsVer ) ] ), __FUNCTION__ );
		}
		else {
			echo $this->ok( $this->txt( 'REQ.CMS_VERSION_OK', [ 'cms' => $cmsName, 'installed' => implode( '.', $cmsVer ), 'cms' => $cmsName, ] ), __FUNCTION__ );
		}
	}

	private function store( $key, $value, $msg = null )
	{
		// let's try to create kinda mutex here
		$file = SPLoader::path( 'tmp.info', 'front', false, 'txt' );
		while ( SPFs::exists( $file ) ) {
			usleep( 100000 );
		}
		$c = date( DATE_RFC822 );
		SPFs::write( $file, $c );
		$store = Sobi::GetUserData( 'requirements', [] );
		$store[ $key ] = [ 'value' => $value, 'message' => $msg ];
		Sobi::SetUserData( 'requirements', $store );
		SPFs::delete( $file );

//		$msg = $msg ? $msg[ 'org' ][ 'label' ] : null;
//		$file = SPLoader::path( 'tmp.info', 'front', false, 'txt' );
//		$cont = null;
//		if ( SPFs::exists( $file ) ) {
//			$cont = SPFs::read( $file );
//		}
//		$txt = "{$cont}\n{$key}={$msg};{$value}";
//		SPFs::write( $file, $txt );
	}

	private function prepareStoredData( &$settings )
	{
		$store = Sobi::GetUserData( 'requirements', [] );
		if ( Sobi::Lang() != 'en-GB' && file_exists( JPATH_ADMINISTRATOR . self::langFile ) ) {
			$file = parse_ini_file( JPATH_ADMINISTRATOR . self::langFile );
		}
		if ( count( $store ) ) {
			foreach ( $store as $key => $data ) {
				if ( Sobi::Lang() != 'en-GB' ) {
					$translate = $file[ 'SP.' . $data[ 'message' ][ 'org' ][ 'label' ] ];
					if ( count( $data[ 'message' ][ 'org' ][ 'params' ] ) ) {
						foreach ( $data[ 'message' ][ 'org' ][ 'params' ] as $param => $value ) {
							$translate = str_replace( "var:[$param]", $value, $translate );
						}
					}
					$settings[ $key ] = [ 'key' => $key, 'response' => [ 'en-GB' => $translate, Sobi::Lang() => $data[ 'message' ][ 'current' ] ], 'status' => $data[ 'value' ] ];
				}
				else {
					$settings[ $key ] = [ 'key' => $key, 'response' => [ 'en-GB' => $data[ 'message' ][ 'current' ] ], 'status' => $data[ 'value' ] ];
				}
			}
		}
	}

	private function download()
	{
//		$file = SPLoader::path( 'tmp.info', 'front', false, 'txt' );
		$cont = null;
		$settings = [];
		$settings[ 'SobiPro' ] = [ 'Version' => SPFactory::CmsHelper()->myVersion( true ), 'Version_Num' => implode( '.', SPFactory::CmsHelper()->myVersion() ) ];
//		$file = SPLoader::path( 'tmp.info', 'front', false, 'txt' );
//		if ( SPFs::exists( $file ) ) {
//			$cont = SPFs::read( $file );
//		}
//		$cont = explode( "\n", $cont );
//		if ( count( $cont ) ) {
//			foreach ( $cont as $line ) {
//				if ( strstr( $line, '=' ) ) {
//					$line = explode( "=", $line );
//					$line[ 1 ] = explode( ';', $line[ 1 ] );
//					$settings[ $line[ 0 ] ] = array( 'key' => $line[ 0 ], 'response' => $line[ 1 ][ 0 ], 'status' => $line[ 1 ][ 1 ] );
//				}
//			}
//		}
		$this->prepareStoredData( $settings );
		$settings[ 'env' ] = [
				'PHP_OS' => PHP_OS,
				'php_uname' => php_uname(),
				'PHP_VERSION_ID' => PHP_VERSION_ID
		];
		$settings[ 'ftp' ] = $this->ftp();
		$settings[ 'curl' ] = $this->curlFull();
		$settings[ 'exec' ][ 'response' ] = $this->execResp();
		$settings[ 'SOBI_SETTINGS' ] = SPFactory::config()->getSettings();
		$c = SPFactory::db()->select( '*', 'spdb_config' )->loadObjectList();
		$sections = SPFactory::db()
				->select( [ 'nid', 'id' ], 'spdb_object', [ 'oType' => 'section' ] )
				->loadAssocList( 'id' );
		$as = [];
		foreach ( $c as $key ) {
			if ( $key->section == 0 || !( isset( $sections[ $key->section ] ) ) ) {
				continue;
			}
			$key->section = $sections[ $key->section ][ 'nid' ];
			if ( !( isset( $as[ $key->section ] ) ) ) {
				$as[ $key->section ] = [];
			}
			if ( !( isset( $as[ $key->section ][ $key->cSection ] ) ) ) {
				$as[ $key->section ][ $key->cSection ] = [];
			}
			$_c = explode( '_', $key->sKey );
			if ( $_c[ count( $_c ) - 1 ] == 'array' ) {
				$key->sValue = SPConfig::unserialize( $key->sValue );
			}
			$as[ $key->section ][ $key->cSection ][ $key->sKey ] = $key->sValue;
		}
		$settings[ 'SOBI_SETTINGS' ][ 'sections' ] = $as;
		$apps = SPFactory::db()->select( '*', 'spdb_plugins' )->loadObjectList();
		foreach ( $apps as $app ) {
			$settings[ 'Apps' ][ $app->pid ] = get_object_vars( $app );
		}
		$settings[ 'SOBI_SETTINGS' ][ 'mail' ][ 'smtphost' ] = $settings[ 'SOBI_SETTINGS' ][ 'mail' ][ 'smtphost' ] ? 'SET' : 0;
		$settings[ 'SOBI_SETTINGS' ][ 'mail' ][ 'smtpuser' ] = $settings[ 'SOBI_SETTINGS' ][ 'mail' ][ 'smtpuser' ] ? 'SET' : 0;
		$settings[ 'SOBI_SETTINGS' ][ 'mail' ][ 'smtppass' ] = $settings[ 'SOBI_SETTINGS' ][ 'mail' ][ 'smtppass' ] ? 'SET' : 0;
		$php = ini_get_all();
		unset( $php[ 'extension_dir' ] );
		unset( $php[ 'include_path' ] );
		unset( $php[ 'mysql.default_user' ] );
		unset( $php[ 'mysql.default_password' ] );
		unset( $php[ 'mysqli.default_pw' ] );
		unset( $php[ 'mysqli.default_user' ] );
		unset( $php[ 'open_basedir' ] );
		unset( $php[ 'pdo_mysql.default_socket' ] );
		unset( $php[ 'sendmail_path' ] );
		unset( $php[ 'session.name' ] );
		unset( $php[ 'session.save_path' ] );
		unset( $php[ 'soap.wsdl_cache_dir' ] );
		unset( $php[ 'upload_tmp_dir' ] );
		unset( $php[ 'doc_root' ] );
		unset( $php[ 'docref_ext' ] );
		unset( $php[ 'docref_root' ] );
		unset( $php[ 'mysql.default_socket' ] );
		$settings[ 'PHP_SETTINGS' ] = $php;
		$php = get_loaded_extensions();
		$settings[ 'PHP_EXT' ] = $php;
		$out = SPFactory::Instance( 'types.array' );
		$data = $out->toXML( $settings, 'settings' );
		$data = str_replace( [ SOBI_ROOT, '></' ], [ 'REMOVED', '>0</' ], $data );
		$f = SPLang::nid( $settings[ 'SOBI_SETTINGS' ][ 'general' ][ 'site_name' ] . '-' . date( DATE_RFC822 ) );

		SPFactory::mainframe()->cleanBuffer();
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		header( "Content-type: application/xml" );
		header( "Content-Disposition: attachment; filename=\"sobipro_system_{$f}.xml\"" );
		header( 'Content-Length: ' . strlen( $data ) );
		ob_clean();
		flush();
		echo( $data );
		exit;
	}

	private function execResp()
	{
		$cmd = 'date';
		$cfg = [ 'shell' => [] ];
		$n = null;
		if ( function_exists( 'exec' ) ) {
			set_time_limit( 15 );
			$cfg[ 'shell' ][ 'exec' ] = trim( exec( $cmd, $n ) );
		}
		if ( function_exists( 'shell_exec' ) ) {
			set_time_limit( 15 );
			$cfg[ 'shell' ][ 'shell_exec' ] = trim( shell_exec( $cmd ) );
		}
		if ( function_exists( 'system' ) ) {
			set_time_limit( 15 );
			$cfg[ 'shell' ][ 'system' ] = trim( system( $cmd, $n ) );
		}
		return $cfg;
	}

	private function ftp()
	{
		$cfg = [];
		if ( function_exists( 'ftp_connect' ) ) {
			$cfg[ 'available' ] = 'available';
			$address = 'sigsiu-net.de';
			set_time_limit( 15 );
			if ( ( $ftp = ftp_connect( $address ) ) !== false ) {
				$cfg[ 'connected' ] = 'created';
				if ( ( $login = @ftp_login( $ftp, 'ftp', '' ) ) !== false ) {
				}
				else {
					$cfg[ 'available' ] = 'available but seems to be not usable';
				}
			}
			else {
				$cfg[ 'available' ] = 'available but seems to be not usable';
			}
		}
		else {
			$cfg[ 'available' ] = 'disabled';
		}
		return $cfg;
	}

	private function curlFull()
	{
		if ( function_exists( 'curl_init' ) ) {
			$cfg[ 'available' ] = 'available';
			set_time_limit( 15 );
			$cfg[ 'version' ] = curl_version();
			$c = curl_init( "https://www.sigsiu.net/sobipro-check/testcurl" );
			if ( $c !== false ) {
				$fp = fopen( "temp.txt", "w" );
				// 'ssl_verifypeer' => false,
				// 'ssl_verifyhost' => 2,
				curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $c, CURLOPT_SSL_VERIFYHOST, 2 );
				curl_setopt( $c, CURLOPT_FILE, $fp );
				curl_setopt( $c, CURLOPT_HEADER, 0 );
				curl_exec( $c );
				$cfg[ 'response' ] = curl_getinfo( $c );
				$c = curl_init( "http://ip.sigsiu-net.de" );
				if ( $c !== false ) {
					curl_setopt( $c, CURLOPT_FILE, $fp );
					curl_setopt( $c, CURLOPT_HEADER, 0 );
					curl_exec( $c );
					$cfg[ 'mip' ] = curl_getinfo( $c );
					$cfg[ 'mip' ][ 'content' ] = file_get_contents( 'temp.txt' );
				}
				fclose( $fp );
				unlink( "temp.txt" );
			}
			else {
				$cfg[ 'response' ] = curl_getinfo( $c );
				$cfg[ 'error' ] = curl_error( $c );
				$cfg[ 'available' ] = 'available but not usable';
			}
		}
		else {
			$cfg[ 'available' ] = 'not available';
		}
		return $cfg;
	}

	private function ok( $msg, $key, $storeOnly = false )
	{
		$this->store( $key, __FUNCTION__, $msg );
		if ( !( $storeOnly ) ) {
			return $this->out( $msg );
		}
	}

	private function warning( $msg, $key, $storeOnly = false )
	{
		$this->store( $key, __FUNCTION__, $msg );
		if ( !( $storeOnly ) ) {
			return $this->out( $msg, SPC::WARN_MSG );
		}
	}

	private function error( $msg, $key, $storeOnly = false )
	{
		$this->store( $key, __FUNCTION__, $msg );
		if ( !( $storeOnly ) ) {
			return $this->out( $msg, SPC::ERROR_MSG );
		}
	}

	protected function out( $message, $type = SPC::SUCCESS_MSG )
	{
		return json_encode( [ 'type' => $type, 'message' => $message[ 'current' ], 'textType' => Sobi::Txt( 'STATUS_' . $type ) ] );
	}

	protected function view()
	{
		$msg = null;
		$store = [];
		Sobi::SetUserData( 'requirements', $store );
		$home = SPRequest::int( 'init' ) ? Sobi::Url( null, true ) : Sobi::Url( 'config', true );
		/** @var $view SPAdmView */
//		header( 'Cache-Control: no-cache, must-revalidate' );
//		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		$init = SPRequest::int( 'init' );
		SPFactory::View( 'view', true )
				->assign( $init, 'init' )
				->addHidden( $home, 'redirect' )
				->determineTemplate( 'config', 'requirements' )
				->display();
	}

	private function compareVersion( $from, $to )
	{
		if ( $from[ 'major' ] > $to[ 'major' ] ) {
			return false;
		}
		elseif ( $from[ 'major' ] < $to[ 'major' ] ) {
			return true;
		}
		if ( $from[ 'minor' ] > $to[ 'minor' ] ) {
			return false;
		}
		elseif ( $from[ 'minor' ] < $to[ 'minor' ] ) {
			return true;
		}
		if ( $from[ 'build' ] > $to[ 'build' ] ) {
			return false;
		}
		return true;
	}
}
