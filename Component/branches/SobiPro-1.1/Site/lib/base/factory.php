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
 * @created 11-Jan-2009 6:26:45 PM
 */
abstract class SPFactory
{
	/**
	 * @return SPJoomlaMainFrame
	 */
	public static function & mainframe()
	{
		SPLoader::loadClass( 'base.mainframe' );
		$class = SPLoader::loadClass( 'cms.base.mainframe' );
		return $class::getInstance();
	}

	/**
	 * @param int $sid
	 * @return SPCache
	 */
	public static function & cache( $sid = 0 )
	{
		if ( !( Sobi::Section() ) ) {
			$path = array();
			$id = $sid;
			while ( $id > 0 ) {
				try {
					$id = SPFactory::db()
							->select( 'pid', 'spdb_relations', array( 'id' => $id ) )
							->loadResult();
					if ( $id ) {
						$path[ ] = ( int )$id;
					}
				} catch ( SPException $x ) {
					Sobi::Error( 'CoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
				}
			}
			if ( count( $path ) ) {
				$path = array_reverse( $path );
			}
			SPFactory::registry()->set( 'current_section', $path[ 0 ] );
		}
		SPLoader::loadClass( 'base.cache' );
		return SPCache::getInstance( $sid );
	}

	/**
	 * @return SPCMSHelper
	 */
	public static function CmsHelper()
	{
		$class = SPLoader::loadClass( 'cms.base.helper' );
		return $class::getInstance();
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
		$class = SPLoader::loadClass( 'cms.base.database' );
		return $class::getInstance();
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
	 * @return SpAdmToolbar
	 */
	public static function & AdmToolbar()
	{
		SPLoader::loadClass( 'views.adm.toolbar' );
		return SpAdmToolbar::getInstance();
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
		return SPHeader::getInstance();
	}

	/**
	 * @return SPMessage
	 */
	public static function & message()
	{
		SPLoader::loadClass( 'base.message' );
		return SPMessage::getInstance();
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
	 * @param int $id
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
			} catch ( SPException $x ) {
				Sobi::Error( 'factory', 'cannot_get_object', SPC::WARNING, 500, __LINE__, __CLASS__, $x->getMessage() );
				return false;
			}
		}
		return $instances[ $id ];
	}

	/**
	 * @param $class
	 * @throws SPException
	 * @internal param string $classPath - class path
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
		try {
			$obj = new ReflectionClass( $loaded[ $class ] );
			$instance = $obj->newInstanceArgs( $args );
		} catch ( LogicException $Exception ) {
			throw new SPException( SPLang::e( 'Cannot create instance of "%s". Class file does not exist. Error %s', $class, $Exception->getMessage() ) );
		} catch ( ReflectionException $Exception ) {
			throw new SPException( SPLang::e( 'Cannot create instance of "%s". Class file does not exist. Error %s', $class, $Exception->getMessage() ) );
		}
		return $instance;
	}

	/**
	 * @param string $name
	 * @param bool $adm
	 * @return SPView
	 */
	public static function & View( $name, $adm = false )
	{
		return self::Instance( self::instancePath( $name, 'views', $adm ) );
	}

	/**
	 * @param string $name
	 * @param bool $adm
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
		$cached = SPFactory::cache( $sid )->getObj( 'entry', $sid );
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
//			SPConfig::debOut( "$sid: cached" );
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
	 * Factory method for category models
	 * @param id of the entry $sid
	 * @return SPCategory
	 */
	public static function & Section( $sid )
	{
		static $sections = array();
		if ( !( isset( $sections[ $sid ] ) ) ) {
			$sections[ $sid ] = self::Model( 'section' );
			$sections[ $sid ]->init( $sid );
		}
		return $sections[ $sid ];
	}

	/**
	 * @param string $name
	 * @param bool $adm
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
