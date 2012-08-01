<?php
/**
 * @version: $Id: factory.php 2101 2011-12-24 14:03:25Z Radek Suski $
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
 * $Date: 2011-12-24 15:03:25 +0100 (Sat, 24 Dec 2011) $
 * $Revision: 2101 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/base/factory.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 11-Jan-2009 6:26:45 PM
 */
abstract class SPFactory
{
    /**
     * @return SPMainFrame
     */
    public static function & mainframe()
    {
        SPLoader::loadClass( 'base.mainframe' );
        SPLoader::loadClass( 'cms.base.mainframe' );
        return SPMainFrame::getInstance();
    }

    /**
     * @return SPCache
     */
    public static function & cache()
    {
        SPLoader::loadClass( 'base.cache' );
        return SPCache::getInstance();
    }

    /**
     * @return SPCMSHelper
     */
    public static function CmsHelper()
    {
        SPLoader::loadClass( 'cms.base.helper' );
        return SPCMSHelper::getInstance();
    }

    /**
     * @return SPConfig
     */
    public static function & config()
    {
        SPLoader::loadClass( 'base.config' );
        return SPConfig::getInstance();
    }

    /**
     * @return SPDb
     */
    public static function & db()
    {
        SPLoader::loadClass( 'base.database' );
        SPLoader::loadClass( 'cms.base.database' );
        return SPDb::getInstance();
    }

    /**
     * @return SPUser
     */
    public static function & user()
    {
        SPLoader::loadClass( 'base.user' );
        SPLoader::loadClass( 'cms.base.user' );
        return SPUser::getCurrent();
    }

    /**
     * @return SPRegistry
     */
    public static function & registry()
    {
        SPLoader::loadClass( 'base.registry' );
        return SPRegistry::getInstance();
    }

    /**
     * @return SPPayment
     */
    public static function & payment()
    {
        SPLoader::loadClass( 'services.payment' );
        return SPPayment::getInstance();
    }

    /**
     * @return SPLang
     */
    public static function & lang()
    {
        SPLoader::loadClass( 'cms.base.lang' );
        return SPLang::getInstance();
    }

    /**
     * @return SPHeader
     */
    public static function & header()
    {
        SPLoader::loadClass( 'base.header' );
        $h =& SPHeader::getInstance();
        return $h;
    }

    /**
     * @return SPSection
     */
    public static function & currentSection()
    {
        SPLoader::loadModel( 'section' );
        return SPSection::getInstance();
    }

    /**
     * @return stdClass
     */
    public static function & object( $id )
    {
        static $instances = array();
        if ( !isset( $instances[ $id ] ) ) {
            $db = self::db();
            try {
                $db->select( '*', 'spdb_object', array( 'id' => $id ) );
                $instances[ $id ] = $db->loadObject();
            }
            catch ( SPException $x ) {
                Sobi::Error( 'factory', 'cannot_get_object', SPC::WARNING, 500, __LINE__, __CLASS__, $x->getMessage() );
                return false;
            }
        }
        return $instances[ $id ];
    }

    /**
     * @param string $classPath - class path
     * @return stdClass
     */
    public static function & Instance( $class )
    {
        static $loaded = array();
        if ( !( isset( $loaded[ $class ] ) ) ) {
            $c = SPLoader::loadClass( $class, false, null, false );
            if ( !( strlen( $c ) ) ) {
                $c = SPLoader::loadClass( $class, defined( 'SOBIPRO_ADM' ) );
            }
            if ( !( strlen( $c ) ) ) {
                throw new SPException( SPLang::e( 'Cannot create instance of "%s". Class file does not exist', $class ) );
            }
            $loaded[ $class ] = $c;
        }
        $args = func_get_args();
        unset( $args[ 0 ] );
        $obj = new ReflectionClass( $loaded[ $class ] );
        $instance = $obj->newInstanceArgs( $args );
        return $instance;
    }

    /**
     * @param string $name
     * @return SPView
     */
    public static function & View( $name, $adm = false )
    {
        return self::Instance( self::instancePath( $name, 'views', $adm ) );
    }

    /**
     * @param string $name
     * @return SPDBObject
     */
    public static function & Model( $name, $adm = false )
    {
        return self::Instance( self::instancePath( $name, 'models', $adm ) );
    }

    /**
     * Factory method for entries models
     * @param id of the entry $sid
     * @return SPEntry
     */
    public static function & Entry( $sid )
    {
        $cached = SPFactory::cache()->getObj( 'entry', $sid );
        if ( $cached && is_object( $cached ) ) {
            $cached->validateCache();
            return $cached;
        }
        else {
            $entry = self::Model( 'entry' );
            $entry->init( $sid );
            return $entry;
        }
    }

    /**
     * Factory method for entries models
     * @param id of the entry $sid
     * @return SPEntryAdm
     */
    public static function & EntryRow( $sid )
    {
        $cached = SPFactory::cache()->getObj( 'entry_row', $sid );
        if ( $cached && is_object( $cached ) ) {
            return $cached;
        }
        else {
            $entry = self::Model( 'entry', true );
            $entry->init( $sid );
            SPFactory::cache()->addObj( $entry, 'entry_row', $sid );
            return $entry;
        }
    }

    /**
     * Factory method for category models
     * @param id of the entry $sid
     * @return SPCategory
     */
    public static function & Category( $sid )
    {
        static $cats = array();
        if ( !( isset( $cats[ $sid ] ) ) ) {
            $cats[ $sid ] = self::Model( 'category' );
            $cats[ $sid ]->init( $sid );
        }
        return $cats[ $sid ];
    }

    /**
     * @param string $name
     * @return SPController
     */
    public static function & Controller( $name, $adm = false )
    {
        return self::Instance( self::instancePath( $name, 'ctrl', $adm ) );
    }

    private static function instancePath( $name, $type, $adm )
    {
        $adm = defined( 'SOBIPRO_ADM' ) ? $adm : false;
        return $adm ? "{$type}.adm.{$name}" : "{$type}.{$name}";
    }

    /**
     * @return SPPlugins
     */
    public static function & plugins()
    {
        $r =& self::registry();
        if ( !$r->__isset( 'plugins' ) ) {
            SPLoader::loadClass( 'plugins.interface' );
            $plugins =& SPPlugins::getInstance();
            $r->set( 'plugins', $plugins );
        }
        else {
            $plugins =& $r->__get( 'plugins' );
        }
        return $plugins;
    }
}

?>
