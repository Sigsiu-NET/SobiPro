<?php
/**
 * @version: $Id: loader.php 2272 2012-02-28 17:17:25Z Sigrid Suski $
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
 * $Date: 2012-02-28 18:17:25 +0100 (Tue, 28 Feb 2012) $
 * $Revision: 2272 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/fs/loader.php $
 */

defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
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
    private static $loaded = array();

    /**
     * @author Radek Suski
     * @param string $name
     * @param string $type
     * @param bool $adm
     * @param bool $redirect
     * @return string
     */
    public static function loadClass( $name, $adm = false, $type = null, $raiseErr = true )
    {
        static $types = array( 'base' => 'base', 'controller' => 'ctrl', 'controls' => 'ctrl', 'ctrl' => 'ctrl', 'model' => 'models', 'plugin' => 'plugins', 'application' => 'plugins', 'view' => 'views' );
        $type = strtolower( trim( $type ) );
        $name = strtolower( trim( $name ) );
        if ( isset( $types[ $type ] ) ) {
            $type = $types[ $type ] . DS;
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
                $path = SOBI_ADM_PATH . DS . $type;
            }
            else {
                $path = SOBI_PATH . DS . 'lib' . DS . $type . 'adm' . DS;
            }
        }
        elseif ( strstr( $type, 'plugin' ) ) {
            $path = SOBI_PATH . DS . 'opt' . DS . $type;
        }
        elseif ( !strstr( $name, 'opt.' ) ) {
            $path = SOBI_PATH . DS . 'lib' . DS . $type;
        }
        else {
            $path = SOBI_PATH . DS . $type;
        }
        $name = str_replace( '.', DS, $name );
        $path .= $name . '.php';
        $path = self::clean( $path );

        /* to prevent double loading of the same class */
        /* class exxists don't works with interfaces */
        if ( isset( self::$loaded[ $path ] ) ) {
            return self::$loaded[ $path ];
        }
        //		if ( key_exists( $path, self::$loaded ) && class_exists( self::$loaded[ $path ] ) ) {
        //			return self::$loaded[ $path ];
        //		}
        if ( !file_exists( $path ) || !is_readable( $path ) ) {
            if ( $raiseErr ) {
                /* We had to chonge it to notice because all these script kiddies are trying to call some not existent file which causes this error here
                     * As a result we have the error log file full of USER_ERRORs and it looks badly but it's not really an error.
                     * So we result wit the 500 response code but we log a notice for the logfile
                     * */
                if ( !( strstr( $path, 'index.php' ) ) ) {
                    if ( class_exists( 'Sobi' ) ) {
                        Sobi::Error( 'Class Load', sprintf( 'Cannot load file at %s. File does not exist or is not readable.', str_replace( SOBI_ROOT . DS, null, $path ) ), SPC::NOTICE, 0 );
                        throw new SPException( sprintf( 'Cannot load file at %s. File does not exist or is not readable.', str_replace( SOBI_ROOT . DS, null, $path ) ) );
                    }
                }
            }
            return false;
        }
        else {
            ob_start();
            $content = file_get_contents( $path );
            $class = array();
            preg_match( '/\s*(class|interface)\s+(\w+)/', $content, $class );
            if ( isset( $class[ 2 ] ) ) {
                $className = $class[ 2 ];
            }
            else {
                Sobi::Error( 'Class Load', sprintf( 'Cannot determine class name in file %s.', str_replace( SOBI_ROOT . DS, null, $path ) ), SPC::ERROR, 500 );
                return false;
            }
            require_once ( $path );
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
                    require_once ( $file );
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
     * @return array
     */
    public static function loadIniFile( $name, $sections = true, $adm = false, $try = false, $absolute = false, $fixedPath = false, $custom = false )
    {
        $path = $absolute ? null : ( $adm ? SOBI_ADM_PATH . DS : SOBI_PATH . DS );
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
                /* We had to chonge it to notice because all these script kiddies are trying to call some not existent file which causes this error here
                     * As a result we have the error log file full of USER_ERRORs and it looks badly but it's not really an error.
                     * So we result wit the 500 response code but we log a notice for the logfile
                     * */
                Sobi::Error( 'ini_load', sprintf( 'Cannot load file at %s', str_replace( SOBI_ROOT . DS, null, $path ) ), SPC::NOTICE, 500, __LINE__, __FILE__ );
            }
            return false;
        }
        else {
            ob_start();
            self::$count++;
            $ini = parse_ini_file( $path, $sections );
            ob_end_clean();
            return $ini;
        }
    }

    /**
     * @author Radek Suski
     * @version 1.0
     * @param string $name
     * @param bool $adm
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
     * @param bool $existCheck
     * @param string $ext
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
     * @param bool $existCheck
     * @param string $ext
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
        elseif ( strstr( $path, 'absolute.' ) ) {
            $file = self::translatePath( str_replace( 'absolute.', null, $path ), 'absolute', $checkExist, $ext, $count );
        }
        else {
            $root = $adm ? 'adm.' : null;
            $file = self::translatePath( $root . $path, 'js', $checkExist, $ext, $count );
        }
        if ( $toLive ) {
            $file = str_replace( SOBI_ROOT . DS, SPFactory::config()->get( 'live_site' ), $file );
            $file = str_replace( '\\', '/', $file );
        }
        return Sobi::FixPath( $file );
    }

    /**
     * @author Radek Suski
     * @version 1.0
     * @param string $path
     * @param string $root
     * @param bool $existCheck
     * @param string $ext
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
        elseif ( strstr( $path, 'absolute.' ) ) {
            $file = self::translatePath( str_replace( 'absolute.', null, $path ), 'absolute', $checkExist, $ext, $count );
        }
        else {
            $root = $adm ? 'adm.' : null;
            $file = self::translatePath( $root . $path, 'css', $checkExist, $ext, $count );
        }
        if ( $toLive ) {
            $file = str_replace( SOBI_ROOT . DS, SPFactory::config()->get( 'live_site' ), $file );
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
     * @return string
     */
    public static function translatePath( $path, $start = 'front', $existCheck = true, $ext = 'php', $count = false )
    {
        $start = $start ? $start : 'front';
        switch ( $start )
        {
            case 'root':
                $spoint = SOBI_ROOT . DS;
                break;
            case 'front':
                $spoint = SOBI_PATH . DS;
                break;
            case 'lib':
                $spoint = SOBI_PATH . DS . 'lib' . DS;
                break;
            case 'lib.base':
            case 'base':
                $spoint = SOBI_PATH . DS . 'lib' . DS . 'base' . DS;
                break;
            case 'lib.ctrl':
            case 'ctrl':
                $spoint = SOBI_PATH . DS . 'lib' . DS . 'ctrl' . DS;
                break;
            case 'lib.html':
                $spoint = SOBI_PATH . DS . 'lib' . DS . 'mlo' . DS;
                break;
            case 'lib.model':
            case 'lib.models':
            case 'model':
            case 'models':
                $spoint = SOBI_PATH . DS . 'lib' . DS . 'models' . DS;
                break;
            case 'lib.views':
            case 'lib.view':
            case 'views':
            case 'view':
                $spoint = SOBI_PATH . DS . 'lib' . DS . 'views' . DS;
                break;
            case 'js':
            case 'lib.js':
                $spoint = SOBI_PATH . DS . 'lib' . DS . 'js' . DS;
                break;
            case 'css':
            case 'media.css':
                $spoint = SOBI_MEDIA . DS . 'css' . DS;
                break;
            case 'media':
                $spoint = SOBI_MEDIA . DS;
                break;
            case 'locale':
            case 'lang':
                $spoint = SOBI_PATH . DS . 'usr' . DS . 'locale' . DS;
                break;
            case 'img':
            case 'media.img':
                $spoint = SOBI_MEDIA . DS . 'img' . DS;
                break;
            case 'adm':
	        case 'administrator':
                if ( defined( 'SOBI_ADM_PATH' ) ) {
                    $spoint = SOBI_ADM_PATH . DS;
                }
                else {
                    return false;
                }
                break;
            case 'adm.template':
            case 'adm.templates':
                if ( defined( 'SOBI_ADM_PATH' ) ) {
                    $spoint = SOBI_ADM_PATH . DS;
                }
                else {
                    return false;
                }
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
        $path = str_replace( '|', DS, $path );
        if ( $ext ) {
            $path = $spoint ? $spoint . DS . $path . '|' . $ext : $path . '|' . $ext;
        }
        else {
            $path = $spoint ? $spoint . DS . $path : $path;
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
        //$path = str_replace( DS.DS, DS, $path );
        $start = null;
        /* don't play with the constant parts of the path */
        if ( defined( 'SOBI_ADM_PATH' ) && strstr( $path, SOBI_ADM_PATH ) ) {
            $path = str_replace( SOBI_ADM_PATH, null, $path );
            $start = SOBI_ADM_PATH;
        }
        elseif ( defined( 'SOBI_ADM_PATH' ) && strstr( $path, str_replace( DS, '.', SOBI_ADM_PATH ) ) ) {
            $path = str_replace( str_replace( DS, '.', SOBI_ADM_PATH ), null, $path );
            $start = SOBI_ADM_PATH;
        }
        elseif ( strstr( $path, SOBI_PATH ) ) {
            $path = str_replace( SOBI_PATH, null, $path );
            $start = SOBI_PATH;
        }
        elseif ( strstr( $path, str_replace( DS, '.', SOBI_PATH ) ) ) {
            $path = str_replace( str_replace( DS, '.', SOBI_PATH ), null, $path );
            $start = SOBI_PATH;
        }
        elseif ( strstr( $path, SOBI_ROOT ) ) {
            $path = str_replace( SOBI_ROOT, null, $path );
            $start = SOBI_ROOT;
        }
        elseif ( strstr( $path, str_replace( DS, '.', SOBI_ROOT ) ) ) {
            $path = str_replace( str_replace( DS, '.', SOBI_ROOT ), null, $path );
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
        return self::translatePath( str_replace( '.', DS, $path ), $start, $existCheck, null, false );
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
        $path = self::translatePath( str_replace( '.', DS, $path ), $root, $checkExist, null, false );
        return strlen( $path ) ? self::clean( $path . DS ) : $path;
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

?>
