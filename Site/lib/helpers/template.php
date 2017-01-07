<?php
/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

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

/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Oct-2010 10:39:33
 */
abstract class SobiPro
{
	/**
	 * Pass given string to the plugins to parse the conten
	 * @param string $content
	 * @return string
	 */
	public static function ParseContent( $content )
	{
		Sobi::Trigger( 'Parse', 'Content', [ &$content ] );
		return $content;
	}

	/**
	 * Translates given string into the current language
	 * @return string
	 */
	public static function Txt()
	{
		$args = func_get_args();
		return call_user_func_array( [ 'Sobi', 'Txt' ], $args );
	}

	public static function LoadLang( $lang )
	{
		SPLang::load( $lang );
	}

	/**
	 * @return string
	 */
	public static function Token()
	{
		return SPFactory::mainframe()->token();
	}

	/**
	 * count childs of a category / section
	 * @param int $sid
	 * @param string $childs
	 * @return int
	 */
	public static function Count( $sid, $childs = 'entry' )
	{
		static $cache = [];
		if ( !( isset( $cache[ $sid ] ) ) ) {
			$cache[ $sid ] = SPFactory::Model( 'category' );
			$cache[ $sid ]->init( $sid );
		}
		return $cache[ $sid ]->countChilds( $childs, 1 );
	}

	/**
	 * Creates a tooltip with the given title and text
	 * @param string $tooltip
	 * @param string $title
	 * @param null $img
	 * @return string
	 */
	public static function Tooltip( $tooltip, $title = null, $img = null )
	{
		SPLoader::loadClass( 'html.tooltip' );
		return SPTooltip::_( $tooltip, $title, $img );
	}

	/**
	 * Returns formatted as a currency text with given amount and currency
	 * @param double $value
	 * @return string
	 */
	public static function Currency( $value )
	{
		return SPLang::currency( $value );
	}

	/**
	 * @param $format - string
	 * @param $date - string
	 * @return string
	 */
	public static function FormatDate( $format, $date )
	{
		return date( $format, strtotime( $date ) );
	}


	/**
	 * @param $format - string
	 * @param $date - string
	 * @param null $locale
	 * @return string
	 */
	public static function SFormatDate( $format, $date, $locale = null )
	{
		if ( $locale ) {
			setlocale( LC_ALL, $locale );
		}
		return strftime( $format, strtotime( $date ) );
	}

	/**
	 * @param $file - string
	 * @param $tplPath - string
	 * @return string
	 */
	public static function LoadCssFile( $file, $tplPath = null )
	{
		if ( $tplPath ) {
			$tplPath = str_replace( Sobi::Cfg( 'live_site' ), SOBI_ROOT . DS, $tplPath );
			$file = 'absolute.' . $tplPath . '.css.' . $file;
		}
		SPFactory::header()->addCSSFile( $file );
	}

	/**
	 * @param $file - string
	 * @param $tplPath - string
	 * @return string
	 */
	public static function LoadJsFile( $file, $tplPath = null )
	{
		if ( $tplPath ) {
			$tplPath = str_replace( Sobi::Cfg( 'live_site' ), SOBI_ROOT . DS, $tplPath );
			$file = 'absolute.' . $tplPath . '.js.' . $file;
		}
		SPFactory::header()->addJsFile( $file );
	}

	/**
	 * @param $file - string
	 * @return string
	 */
	public static function AddJsFile( $file )
	{
		if ( strstr( $file, ',' ) ) {
			$file = explode( ',', $file );
		}
		SPFactory::header()->addJsFile( $file );
	}

	/**
	 * @param $file - string
	 * @return string
	 */
	public static function AddCSSFile( $file )
	{
		if ( strstr( $file, ',' ) ) {
			$file = explode( ',', $file );
		}
		SPFactory::header()->addJsFile( $file );
	}

	/**
	 * @param $key
	 * @param $def
	 * @param $section
	 * @return mixed
	 */
	public static function Cfg( $key, $def = null, $section = 'general' )
	{
		return Sobi::Cfg( $key, $def, $section );
	}

	/**
	 * Creates URL to the internal SobiPro function
	 * @param array $var - JSON encoded array
	 * @param bool $js
	 * @param bool $sef
	 * @param bool $live
	 * @param bool $forceItemId
	 * @return string
	 */
	public static function Url( $var = null, $js = false, $sef = true, $live = false, $forceItemId = false )
	{
		if ( $var == 'current' ) {
			return Sobi::Url( $var, $js, $sef, $live, $forceItemId );
		}
		$url = json_decode( $var, true );
		if ( count( $url ) ) {
			foreach ( $url as $k => $v ) {
				$url[ $k ] = trim( $v );
			}
		}
		return Sobi::Url( $url, $js, $sef, $live, $forceItemId );
	}

	/**
	 * Add a alternate link to the header section
	 * @param array $url - JSON encoded array
	 * @param string $type
	 * @param string $title
	 * @return void
	 */
	public static function AlternateLink( $url, $type, $title = null )
	{
		SPFactory::header()->addHeadLink( self::Url( $url ), $type, $title );
	}

	/**
	 * Triggers plugin for the given content
	 * @param string $name
	 * @param int $sid
	 * @param int $section
	 * @return string
	 */
	public static function Application( $name, $sid, $section = 0 )
	{
		$section = $section ? $section : Sobi::Section();
		$content = null;
		Sobi::Trigger( $name, 'TemplateDisplay', [ &$content, $sid, $section ] );
		return $content;
	}

	/**
	 * check permission for an action.
	 * Can be also used like this:
	 *         SobiPro::Can( 'subject.action.ownership' )
	 *         SobiPro::Can( 'entry.see_unpublished.own' )
	 *
	 * @param $subject
	 * @param string $action - e.g. edit
	 * @param int $section - section. If not given, the current section will be used
	 * @param string $ownership - e.g. own, all or valid
	 * @return bool - true if authorized
	 */
	public static function Can( $subject, $action = 'access', $section = null, $ownership = 'valid' )
	{
		return SPFactory::user()->can( $subject, $action, $ownership, $section );
	}

	/**
	 * Returns copy of stored registry value key
	 *
	 * @param string $key - stored key
	 * @param mixed $def - default value
	 * @return mixed
	 */
	public static function Reg( $key, $def = null )
	{
		return SPFactory::registry()->get( $key, $def );
	}

	/**
	 * Returns copy of stored registry value key
	 *
	 * @param string $key - stored key
	 * @param mixed $def - default value
	 * @return mixed
	 */
	public static function Request( $key, $def = null )
	{
		return SPRequest::string( $key, $def );
	}

	/**
	 * Returns selected property of the currently visiting user
	 * e.g SobiPro::My( 'id' ); SobiPro::My( 'name' );
	 *
	 * @param string $property
	 * @return mixed
	 */
	public static function My( $property )
	{
		if ( in_array( $property, [ 'password', 'block', 'sendEmail', 'activation', 'params' ] ) ) {
			return false;
		}
		static $user = null;
		if ( !( $user ) ) {
			$user =& SPFactory::user();
		}
		return $user->get( $property );
	}

	/**
	 * Returns selected property of the a selected user
	 * e.g SobiPro::User( 'id' ); SobiPro::User( 'name' );
	 *
	 * @param $id
	 * @param string $property
	 * @return mixed
	 */
	public static function User( $id, $property )
	{
		$property = trim( $property );
		if ( in_array( $property, [ 'password', 'block', 'sendEmail', 'activation', 'params' ] ) ) {
			return false;
		}
		$id = ( int )$id;
		static $loaded = [];
		if ( !( isset( $loaded[ $id ] ) ) ) {
			$loaded[ $id ] = SPUser::getBaseData( $id );
		}
		return isset( $loaded[ $id ]->$property ) ? $loaded[ $id ]->$property : null;
	}

}
