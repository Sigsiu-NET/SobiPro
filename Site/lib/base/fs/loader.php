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

use Sobi\Autoloader\Autoloader;
use Sobi\Framework;

if ( !( class_exists( '\\Sobi\\Framework' ) ) ) {
	// Suppressing warning because the error is being handled
	@include_once 'phar://' . SOBI_ROOT . '/libraries/sobi/Sobi-1.0.2.phar.tar.gz/Framework.php';
	if ( !( class_exists( '\\Sobi\\Framework' ) ) ) {
		if ( file_exists( SOBI_ROOT . '/libraries/sobi/Framework.php' ) ) {
			include_once SOBI_ROOT . '/libraries/sobi/Framework.php';
		}
		else {
			throw new Exception( 'Cannot initialise Sobi Framework. Ensure that your server has PHAR support or install the Sobi Framework manually.' );
		}
	}
	Framework::Init();
}

Autoloader::getInstance()
		->registerClasses( [
				'SPImage' => SOBI_PATH . '/lib/base/fs/image.php',
				'SPCache' => SOBI_PATH . '/lib/base/cache.php',
				'SPConfig' => SOBI_PATH . '/lib/base/config.php',
				'SPFactory' => SOBI_PATH . '/lib/base/factory.php',
				'SPHeader' => SOBI_PATH . '/lib/base/header.php',
				'SPMessage' => SOBI_PATH . '/lib/base/message.php',
				'SPObject' => SOBI_PATH . '/lib/base/object.php',
				'SPRegistry' => SOBI_PATH . '/lib/base/registry.php',
				'SPMainframeInterface' => SOBI_PATH . '/lib/base/mainframe.php',
				'SPUserInterface' => SOBI_PATH . '/lib/base/user.php',
		] );


/**
 * @author Radek Suski
 * @version 1.0
 * @since 1.0
 * @created 10-Jan-2009 5:04:33 PM
 */
abstract class SPLoader
{
	/**
	 * @var int
	 */
	private static $count = 1;
	/**
	 * @var array
	 */
	private static $loaded = [];

	/**
	 * @author Radek Suski
	 * @param string $name
	 * @param bool $adm
	 * @param string $type
	 * @param bool $raiseErr
	 * @throws SPException
	 * @return string
	 */
	public static function loadClass( $name, $adm = false, $type = null, $raiseErr = true )
	{
		static $types = [ 'sp-root' => 'sp-root', 'base' => 'base', 'controller' => 'ctrl', 'controls' => 'ctrl', 'ctrl' => 'ctrl', 'model' => 'models', 'plugin' => 'plugins', 'application' => 'plugins', 'view' => 'views', 'templates' => 'templates' ];
		$type = strtolower( trim( $type ) );
		$name = ( trim( $name ) );
		if ( isset( $types[ $type ] ) ) {
			$type = $types[ $type ] . '/';
		}
		else {
			$type = null;
		}
		if ( strstr( $name, 'cms' ) !== false ) {
			$name = str_replace( 'cms.', 'cms.' . SOBI_CMS . '.', $name );
		}
		else {
			if ( strstr( $name, 'html.' ) ) {
				$name = str_replace( 'html.', 'mlo.', $name );
			}
		}
		if ( $adm ) {
			if ( $type == 'view' ) {
				$path = SOBI_ADM_PATH . '/' . $type;
			}
			else {
				$path = SOBI_PATH . "/lib/{$type}/adm/";
			}
		}
		elseif ( strstr( $type, 'plugin' ) ) {
			$path = SOBI_PATH . '/opt/' . $type;
		}
		elseif ( strstr( $type, 'template' ) ) {
			$path = SOBI_PATH . '/usr/templates/' . Sobi::Cfg( 'section.template', SPC::DEFAULT_TEMPLATE );
		}
		elseif ( $type == 'sp-root/' ) {
			$path = SOBI_PATH . '/';
		}
		elseif ( !strstr( $name, 'opt.' ) ) {
			$path = SOBI_PATH . '/lib/' . $type;
		}
		else {
			$path = SOBI_PATH . '/' . $type;
		}
		$name = str_replace( '.', '/', $name );
		$path .= $name . '.php';
		$path = self::clean( $path );
		/* to prevent double loading of the same class */
		/* class exists don't works with interfaces */
		if ( isset( self::$loaded[ $path ] ) ) {
			return self::$loaded[ $path ];
		}
		//		if ( key_exists( $path, self::$loaded ) && class_exists( self::$loaded[ $path ] ) ) {
		//			return self::$loaded[ $path ];
		//		}
		if ( !file_exists( $path ) || !is_readable( $path ) ) {
			if ( $raiseErr ) {
				/* We had to change it to notice because all these script kiddies are trying to call some not existent file which causes this error here
					 * As a result we have the error log file full of USER_ERRORs and it looks badly but it's not really an error.
					 * So we result wit the 500 response code but we log a notice for the logfile
					 * */
				if ( !( strstr( $path, 'index.php' ) ) ) {
					if ( class_exists( 'Sobi' ) ) {
						Sobi::Error( 'Class Load', sprintf( 'Cannot load file at %s. File does not exist or is not readable.', str_replace( SOBI_ROOT . '/', null, $path ) ), SPC::NOTICE, 0 );
						throw new SPException( sprintf( 'Cannot load file at %s. File does not exist or is not readable.', str_replace( SOBI_ROOT . '/', null, $path ) ) );
					}
				}
			}
			return false;
		}
		else {
			ob_start();
			$content = file_get_contents( $path );
			$class = [];
			preg_match( '/\s*(class|interface)\s+(\w+)/', $content, $class );
			if ( isset( $class[ 2 ] ) ) {
				$className = $class[ 2 ];
			}
			else {
				Sobi::Error( 'Class Load', sprintf( 'Cannot determine class name in file %s.', str_replace( SOBI_ROOT . '/', null, $path ) ), SPC::ERROR, 500 );
				return false;
			}
			require_once( $path );
			self::$count++;
			ob_end_clean();
			self::$loaded[ $path ] = $className;
			return $className;
		}
	}

	private static function clean( $file )
	{
		// double slashes
		$file = preg_replace( '|([^:])(//)+([^/])|', '\1/\3', $file );
		// clean
		//$file = preg_replace( "|[^a-zA-Z\\\\0-9\.\-\_\/\|]|", null, $file );
		$file = preg_replace( "|[^a-zA-Z\\\\0-9\.\-\_\/\|\: ]|", null, $file );
		return str_replace( '__BCKSL__', '\\', preg_replace( '|([^:])(\\\\)+([^\\\])|', "$1__BCKSL__$3", $file ) );
	}

	/**
	 * Load classes from an array - used for the cache/unserialize
	 * @param array $arr array with file names
	 * @return void
	 */
	public static function wakeUp( $arr )
	{
		foreach ( $arr as $file => $class ) {
			if ( !( class_exists( $class ) ) ) {
				if ( file_exists( $file ) && is_readable( $file ) ) {
					require_once( $file );
					self::$count++;
					self::$loaded[ $file ] = $class;
				}
			}
		}
	}

	/**
	 * @return array - array with all loaded classes
	 */
	public static function getLoaded()
	{
		return self::$loaded;
	}

	/**
	 * @return int
	 */
	public static function getCount()
	{
		return self::$count;
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $name
	 * @param bool $sections
	 * @param bool $adm
	 * @param bool $try
	 * @param bool $absolute
	 * @param bool $fixedPath
	 * @param bool $custom
	 * @return array
	 */
	public static function loadIniFile( $name, $sections = true, $adm = false, $try = false, $absolute = false, $fixedPath = false, $custom = false )
	{
		$path = $absolute ? null : ( $adm ? SOBI_ADM_PATH . '/' : SOBI_PATH . '/' );
		/* if there is a customized ini file
		   * it should be named like filename_override.ini
		   * i.e config_my.ini will be loaded instead fo config.ini
		   */
		if ( !( $custom ) ) {
			$customIni = self::loadIniFile( $name . '_override', $sections, $adm, true, $absolute, $fixedPath, true );
			if ( $customIni && is_array( $customIni ) ) {
				return $customIni;
			}
		}
		if ( !$fixedPath ) {
			$path = self::fixPath( $path . $name, !false );
			$path .= '.ini';
		}
		else {
			$path .= $name . '.ini';
		}
		if ( !file_exists( $path ) || !is_readable( $path ) ) {
			if ( !$try ) {
				/* We had to change it to notice because all these script kiddies are trying to call some not existent file which causes this error here
				 * As a result we have the error log file full of USER_ERRORs and it looks badly but it's not really an error.
				 * So we result wit the 500 response code but we log a notice for the logfile
				 * */
				Sobi::Error( 'ini_load', sprintf( 'Cannot load file at %s', str_replace( SOBI_ROOT . '/', null, $path ) ), SPC::NOTICE, 500, __LINE__, __FILE__ );
			}
			return false;
		}
		else {
			ob_start();
			self::$count++;
			$ini = parse_ini_file( $path, $sections );
			ob_end_clean();
			self::iniStorage( $path );
			return $ini;
		}
	}

	public static function iniStorage( $file = null )
	{
		static $name = null;
		if ( $file ) {
			$name = $file;
		}
		else {
			return $name;
		}
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $name
	 * @param bool $adm
	 * @param bool $redirect
	 * @return string
	 */
	public static function loadController( $name, $adm = false, $redirect = true )
	{
		return self::loadClass( $name, $adm, 'ctrl', $redirect );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $name
	 * @param bool $adm
	 * @param bool $redirect
	 * @return string
	 */
	public static function loadModel( $name, $adm = false, $redirect = true )
	{
		if ( strstr( $name, 'field' ) ) {
			self::loadClass( 'fields.interface', false, 'model', $redirect );
			if ( $adm ) {
				$name = 'adm.' . $name;
			}
		}
		return self::loadClass( $name, false, 'model', $redirect );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param string $type
	 * @param bool $check
	 * @return string
	 */
	public static function loadTemplate( $path, $type = 'xslt', $check = true )
	{
		return self::translatePath( $path, 'absolute', $check, $type );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $name
	 * @param bool $adm
	 * @param bool $redirect
	 * @return string
	 */
	public static function loadView( $name, $adm = false, $redirect = true )
	{
		return self::loadClass( $name, $adm, 'view', $redirect );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param string $root
	 * @param bool $checkExist
	 * @param string $ext
	 * @param bool $count
	 * @return string
	 */
	public static function path( $path, $root = 'front', $checkExist = true, $ext = 'php', $count = true )
	{
		return self::translatePath( $path, $root, $checkExist, $ext, $count );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $domain
	 * @param bool $checkExist
	 * @param bool $count
	 * @return string
	 */
	public static function langFile( $domain, $checkExist = true, $count = true )
	{
		return self::translatePath( $domain, 'locale', $checkExist, 'mo', $count );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param bool $adm
	 * @param bool $checkExist
	 * @param bool $toLive
	 * @param string $ext
	 * @param bool $count
	 * @internal param bool $existCheck
	 * @return string
	 */
	public static function JsFile( $path, $adm = false, $checkExist = false, $toLive = true, $ext = 'js', $count = false )
	{
		if ( strstr( $path, 'root.' ) ) {
			$file = self::translatePath( str_replace( 'root.', null, $path ), 'root', $checkExist, $ext, $count );
		}
		elseif ( strstr( $path, 'front.' ) ) {
			$file = self::translatePath( str_replace( 'front.', null, $path ), 'front', $checkExist, $ext, $count );
		}
		elseif ( strstr( $path, 'storage.' ) ) {
			$file = self::translatePath( str_replace( 'storage.', null, $path ), 'storage', $checkExist, $ext, $count );
		}
		elseif ( strstr( $path, 'absolute.' ) ) {
			$file = self::translatePath( str_replace( 'absolute.', null, $path ), 'absolute', $checkExist, $ext, $count );
		}
		else {
			$root = $adm ? 'adm.' : null;
			$file = self::translatePath( $root . $path, 'js', $checkExist, $ext, $count );
		}
		if ( $toLive ) {
			$file = str_replace( SOBI_ROOT, SPFactory::config()->get( 'live_site' ), $file );
			$file = str_replace( '\\', '/', $file );
		}
		return Sobi::FixPath( $file );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param bool $adm
	 * @param bool $checkExist
	 * @param bool $toLive
	 * @param string $ext
	 * @param bool $count
	 * @return string
	 */
	public static function CssFile( $path, $adm = false, $checkExist = true, $toLive = true, $ext = 'css', $count = false )
	{
		if ( strstr( $path, 'root.' ) ) {
			$file = self::translatePath( str_replace( 'root.', null, $path ), 'root', $checkExist, $ext, $count );
		}
		elseif ( strstr( $path, 'front.' ) ) {
			$file = self::translatePath( str_replace( 'front.', null, $path ), 'front', $checkExist, $ext, $count );
		}
		elseif ( strstr( $path, 'storage.' ) ) {
			$file = self::translatePath( str_replace( 'storage.', null, $path ), 'storage', $checkExist, $ext, $count );
		}
		elseif ( strstr( $path, 'absolute.' ) ) {
			$file = self::translatePath( str_replace( 'absolute.', null, $path ), 'absolute', $checkExist, $ext, $count );
		}
		else {
			$root = $adm ? 'adm.' : null;
			$file = self::translatePath( $root . $path, 'css', $checkExist, $ext, $count );
		}
		if ( $toLive ) {
			$file = str_replace( SOBI_ROOT, SPFactory::config()->get( 'live_site' ), $file );
			$file = str_replace( '\\', '/', $file );
		}
		return Sobi::FixPath( $file );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param string $start
	 * @param bool $existCheck
	 * @param string $ext
	 * @param bool $count
	 * @return string
	 */
	public static function translatePath( $path, $start = 'front', $existCheck = true, $ext = 'php', $count = false )
	{
		$start = $start ? $start : 'front';
		switch ( $start ) {
			case 'root':
				$spoint = SOBI_ROOT . '/';
				break;
			case 'front':
				$spoint = SOBI_PATH . '/';
				break;
			case 'lib':
				$spoint = SOBI_PATH . '/lib/';
				break;
			case 'lib.base':
			case 'base':
				$spoint = SOBI_PATH . '/lib/base/';
				break;
			case 'lib.ctrl':
			case 'ctrl':
				$spoint = SOBI_PATH . '/lib/ctrl/';
				break;
			case 'lib.html':
				$spoint = SOBI_PATH . '/lib/mlo/';
				break;
			case 'lib.model':
			case 'lib.models':
			case 'model':
			case 'models':
				$spoint = SOBI_PATH . '/lib/models/';
				break;
			case 'lib.views':
			case 'lib.view':
			case 'views':
			case 'view':
				$spoint = SOBI_PATH . '/lib/views/';
				break;
			case 'js':
			case 'lib.js':
				$spoint = SOBI_PATH . '/lib/js/';
				break;
			case 'css':
			case 'media.css':
				$spoint = SOBI_MEDIA . '/css/';
				break;
			case 'less':
			case 'media.less':
				$spoint = SOBI_MEDIA . '/less/';
				break;
			case 'media':
				$spoint = SOBI_MEDIA . '/';
				break;
			case 'locale':
			case 'lang':
				$spoint = SOBI_PATH . '/usr/locale/';
				break;
			case 'templates':
				$spoint = SOBI_PATH . '/usr/templates/';
				break;
			case 'img':
			case 'media.img':
				$spoint = SOBI_IMAGES . '/img/'; //does not exist
				break;
			case 'media.categories':
				$spoint = SOBI_IMAGES . '/categories/';
				break;
			case 'adm':
			case 'administrator':
				if ( defined( 'SOBI_ADM_PATH' ) ) {
					$spoint = SOBI_ADM_PATH . '/';
				}
				else {
					return false;
				}
				break;
			case 'adm.template':
			case 'adm.templates':
				if ( defined( 'SOBI_ADM_PATH' ) ) {
					$spoint = SOBI_ADM_PATH . '/';
				}
				else {
					return false;
				}
				break;
			case 'storage':
				$spoint = SOBI_PATH . '/usr/templates/storage/';
				break;
			case 'absolute':
			default:
				$spoint = null;
				break;
		}
		//		if ( strstr( $path, $ext ) ) {
		//			$tPath = explode( '.', $path );
		//			if ( strstr( $tPath[ count( $tPath ) - 1 ], $ext ) ) {
		//				$ext = null;
		//			}
		//		}
		$path = str_replace( '|', '/', $path );
		if ( $ext ) {
			$path = $spoint ? $spoint . '/' . $path . '|' . $ext : $path . '|' . $ext;
		}
		else {
			$path = $spoint ? $spoint . '/' . $path : $path;
		}
		$path = self::fixPath( $path );
		if ( $ext ) {
			$path = str_replace( '|', '.', $path );
		}
		if ( $existCheck ) {
			if ( !file_exists( $path ) || !is_readable( $path ) ) {
				return false;
			}
			else {
				if ( $count ) {
					self::$count++;
				}
				return $path;
			}
		}
		else {
			if ( $count ) {
				self::$count++;
			}
			return $path;
		}
	}

	private static function fixPath( $path )
	{
		$start = null;
		/* don't play with the constant parts of the path */
		if ( defined( 'SOBI_ADM_PATH' ) && strstr( $path, SOBI_ADM_PATH ) ) {
			$path = str_replace( SOBI_ADM_PATH, null, $path );
			$start = SOBI_ADM_PATH;
		}
		elseif ( defined( 'SOBI_ADM_PATH' ) && strstr( $path, str_replace( '/', '.', SOBI_ADM_PATH ) ) ) {
			$path = str_replace( str_replace( '/', '.', SOBI_ADM_PATH ), null, $path );
			$start = SOBI_ADM_PATH;
		}
		elseif ( strstr( $path, SOBI_PATH ) ) {
			$path = str_replace( SOBI_PATH, null, $path );
			$start = SOBI_PATH;
		}
		elseif ( strstr( $path, str_replace( '/', '.', SOBI_PATH ) ) ) {
			$path = str_replace( str_replace( '/', '.', SOBI_PATH ), null, $path );
			$start = SOBI_PATH;
		}
		elseif ( strstr( $path, SOBI_ROOT ) ) {
			$path = str_replace( SOBI_ROOT, null, $path );
			$start = SOBI_ROOT;
		}
		elseif ( strstr( $path, str_replace( '/', '.', SOBI_ROOT ) ) ) {
			$path = str_replace( str_replace( '/', '.', SOBI_ROOT ), null, $path );
			$start = SOBI_ROOT;
		}

		$path = str_replace( '.', '/', $path );
		return self::clean( $start . $path );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param string $start
	 * @param bool $existCheck
	 * @return string
	 */
	public static function translateDirPath( $path, $start = 'front', $existCheck = true )
	{
		return self::translatePath( str_replace( '.', '/', $path ), $start, $existCheck, null, false );
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param string $root
	 * @param bool $checkExist
	 * @return string
	 */
	public static function dirPath( $path, $root = 'front', $checkExist = true )
	{
		$path = self::translatePath( str_replace( '.', '/', $path ), $root, $checkExist, null, false );
		return strlen( $path ) ? self::clean( $path . '/' ) : $path;
	}

	/**
	 * @author Radek Suski
	 * @version 1.0
	 * @param string $path
	 * @param string $root
	 * @return string
	 */
	public static function newDir( $path, $root = 'front' )
	{
		return self::translatePath( $path, $root, false, null, false );
	}
}
