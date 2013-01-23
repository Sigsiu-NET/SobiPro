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
 * @created 20-Jul-2008 12:43:29
 */
abstract class SPRequest
{
	/**
	 * @var mixed
	 */
	private static $val = null;
	/**
	 * @var string
	 */
	private static $name = null;
	/**
	 * @var mixed
	 */
	private static $default = null;
	/**
	 * @var string
	 */
	private static $method = 'REQUEST';
	/**
	 * @var string
	 */
	private static $request = null;
	/**
	 * @var bool
	 */
	private static $commentsAllowed = true;
	/**
	 * @var Zend_Filter_StripTags
	 */
	private static $filter = null;

	/**
	 * initialising
	 *
	 * @param string $name
	 * @param string $method
	 * @param mixed $default
	 */
	private static function init( $name, $method )
	{
		self::$method = strtoupper( $method );
		$name = ( self::$method == 'COOKIE' ) ? 'SPro_' . $name : $name;
		self::$name = $name;

		switch ( self::$method ) {
			case 'GET':
				self::$request =& $_GET;
				break;
			case 'POST':
				self::$request =& $_POST;
				break;
			case 'FILES':
				self::$request =& $_FILES;
				break;
			case 'COOKIE':
				self::$request =& $_COOKIE;
				break;
			case 'ENV':
				self::$request =& $_ENV;
				break;
			case 'SERVER':
				self::$request =& $_SERVER;
				break;
			case 'REQUESTCACHE':
				self::$request =& Sobi::Reg( 'requestcache' );
			default:
				self::$request =& $_REQUEST;
				self::$method = 'REQUEST';
				break;
		}
	}

	/**
	 * Sets the tagsAllowed option
	 *
	 * @param array $tags
	 * @return void
	 */
	public static function setTagsAllowed( $tags )
	{
		self::createFilter();
		return self::$filter->setTagsAllowed( $tags );
	}

	/**
	 * Sets the attributesAllowed option
	 *
	 * @param array $attributes
	 * @return void
	 */
	public static function setAttributesAllowed( $attributes )
	{
		self::createFilter();
		return self::$filter->setAttributesAllowed( $attributes );
	}

	/**
	 * Filter variable from request
	 * @param string $value
	 * @return string
	 */
	public static function filter( $value )
	{
		if ( class_exists( 'SPFactory' ) ) {
			if ( ( SPFactory::user()->isAdmin() ) ) {
				return stripslashes( $value );
			}
		}
		self::createFilter();
		$a = self::$filter->filter( stripslashes( $value ) );
		return $a;
	}

	/**
	 * Reset filter to the default settings for the current section
	 * @return void
	 */
	public static function resetFilter()
	{
		if ( !self::$filter || !( self::$filter instanceof Zend_Filter_StripTags ) ) {
			self::$filter = new Zend_Filter_StripTags();
		}
		self::$filter->setAttributesAllowed( Sobi::Cfg( 'html.allowed_attributes_array' ) );
		self::$filter->setTagsAllowed( Sobi::Cfg( 'html.allowed_tags_array' ) );
	}

	private static function createFilter()
	{
		if ( !self::$filter || !( self::$filter instanceof Zend_Filter_StripTags ) ) {
			self::$filter = new Zend_Filter_StripTags();
		}
	}

	/**
	 * Returns integer value of requested variable
	 *
	 * @param string $name variable name
	 * @param int $default default value
	 * @param string $method request method
	 * @return int
	 */
	static public function int( $name, $default = 0, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		if ( !is_array( self::$val ) ) {
			self::$val = ( int )self::$val;
		}
		else {
			self::$val = $default;
		}
		return self::$val;
	}

	/**
	 * Set variable
	 *
	 * @param string $name variable name
	 * @param mixed $var value
	 * @param string $method request method
	 */
	static public function set( $name, $var, $method = 'REQUEST' )
	{
		$_REQUEST[ $name ] = $var;
		self::init( $name, $method );
		self::$request[ self::$name ] = $var;
	}

	/**
	 * Returns integer value of the 'sid' variable
	 *
	 * @param string $method request method
	 * @param int $default
	 * @return int
	 */
	static public function sid( $method = 'REQUEST', $default = 0 )
	{
		return self::int( 'sid', $default, $method );
	}

	/**
	 * Returns float value of requested variable
	 *
	 * @param string $name variable name
	 * @param float $default default value
	 * @param string $method request method
	 * @return float
	 */
	static public function float( $name, $default = 0, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		self::$val = preg_replace( "/[^0-9\.]/", null, self::$val );
		self::$val = ( float )self::$val;
		return self::$val;
	}

	/**
	 * Returns double value of requested variable
	 *
	 * @param string $name variable name
	 * @param double $default default value
	 * @param string $method request method
	 * @return double
	 */
	static public function double( $name, $default = 0, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		self::$val = preg_replace( '/[^0-9\.]/', null, self::$val );
		self::$val = ( double )self::$val;
		return self::$val;
	}

	/**
	 * Search for indexes within the requested method
	 *
	 * @param string $search variable name
	 * @param string $method request method
	 * @return double
	 */
	static public function search( $search, $method = 'REQUEST' )
	{
		self::init( null, $method );
		self::$val = array();
		if ( count( self::$request ) ) {
			foreach ( self::$request as $name => $value ) {
				if ( strstr( $name, $search ) ) {
					self::$val[ $name ] = $value;
				}
			}
		}
		return self::$val;
	}

	/**
	 * Returns bool value of requested variable
	 *
	 * @param string $name variable name
	 * @param bool $default default value
	 * @param string $method request method
	 * @return bool
	 */
	static public function bool( $name, $default = false, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		self::$val = preg_replace( "/[^0-1]/", null, self::$val );
		self::$val = ( bool )self::$val;
		return self::$val;
	}

	/**
	 * Returns word (alpha numeric) value of requested variable
	 *
	 * @param string $name variable name
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function word( $name, $default = null, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		self::$val = preg_replace( "/[^a-zA-Z0-9\p{L}\_\-\s]/u", null, self::$val );
		return self::$val;
	}

	/**
	 * Returns commmand of requested variable
	 *
	 * @param string $name variable name
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function cmd( $name, $default = null, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		self::$val = preg_replace( "/[^a-zA-Z0-9\p{L}\.\-\_\:]/u", null, self::$val );
		return self::$val;
	}

	/**
	 * Returns base64 encoded value of requested variable
	 *
	 * @param string $name variable name
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function base64( $name, $default = null, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		self::$val = preg_replace( "/[^A-Za-z0-9\/+=]/", null, self::$val );
		return self::$val;
	}

	/**
	 * Returns string value of requested variable
	 *
	 * @param string $name variable name
	 * @param bool $html allow html tags
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function string( $name, $default = null, $html = false, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		$back = array();
		if ( $html == 1 ) {
			$val = self::$val;
			if ( preg_match_all( '/(<pre((?!>).)*>*)(((?!<\/pre|<pre).)+)\s*<\/pre>/s', $val, $matches ) ) {
				self::createFilter();
				$allowed = self::$filter->getTagsAllowed();
				if ( isset( $allowed[ 'pre' ] ) ) {
					foreach ( $matches[ 3 ] as $i => $pre ) {
						$id = '[%pre%]' . $i . '[%pre%]';
						$back[ $id ] = array( 'content' => $pre, 'tag' => $matches[ 1 ][ $i ] );
						$val = str_replace( $matches[ 1 ][ $i ] . $pre, $id, $val );
					}
				}
			}
			$val = self::filter( $val );
			$conv = Sobi::Cfg( 'html.pre_to_entities', true );
			if ( count( $back ) ) {
				foreach ( $back as $id => $pre ) {
					if ( $conv ) {
						$pre[ 'content' ] = htmlentities( $pre[ 'content' ] );
					}
					$val = str_replace( $id, $pre[ 'tag' ] . $pre[ 'content' ], $val );
				}
			}
			self::$val = $val;
		}
		elseif ( !( $html ) ) {
			self::$val = strip_tags( self::$val );
		}
		return filter_var( self::$val, FILTER_SANITIZE_MAGIC_QUOTES );
	}

	/**
	 * Returns string value of requested variable
	 *
	 * @param string $name variable name
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function html( $name, $default = null, $method = 'REQUEST' )
	{
		return self::string( $name, $default, true, $method );
	}

	/**
	 * Returns ip value of requested variable
	 *
	 * @param string $name variable name
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function ip( $name, $default = null, $method = 'REQUEST' )
	{
		self::$val = self::string( $name, $default, false, $method );
		self::$val = preg_replace( "/[^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}]/", null, self::$val );
		return self::$val;
	}

	/**
	 * Returns ip value of requested variable
	 *
	 * @param string $name variable name
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function datetime( $name, $default = null, $method = 'REQUEST' )
	{
		$config =& SPFactory::config();
		$config->addIniFile( 'etc.calendar' );
		self::$val = self::string( $name, $default, false, $method );
		if ( self::$val ) {
			self::$val = SPFactory::config()->rdate( self::$val );
			self::$val = date( 'Y-m-d H:i:s', self::$val );
		}
		else {
			self::$val = SPFactory::db()->getNullDate();
		}
		return self::$val;
	}

	/**
	 * Returns ip value of requested variable
	 *
	 * @param null $time
	 * @internal param string $name variable name
	 * @internal param string $default default value
	 * @internal param string $method request method
	 * @return string
	 */
	static public function now( $time = null )
	{
		self::$val = gmdate( 'Y-m-d H:i:s' );
		return self::$val;
	}

	/**
	 * Returns array value of requested variable
	 *
	 * @param string $name variable name
	 * @param array $default default value
	 * @param string $method request method
	 * @return array
	 */
	static public function arr( $name, $default = array(), $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		if ( is_array( self::$val ) ) {
			self::$val = ( array )self::$val;
			self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
			self::$val = self::cleanArray( self::$val );
		}
		else {
			self::$val = $default;
		}
		return self::$val;
	}

	/**
	 * Returns raw value of requested variable
	 *
	 * @param string $name variable name
	 * @param string $default default value
	 * @param string $method request method
	 * @return string
	 */
	static public function raw( $name, $default = null, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : $default;
		return self::$val;
	}

	/**
	 * @param string $name variable name
	 * @param string $property
	 * @param string $request request method
	 * @return string
	 */
	static public function file( $name, $property = null, $request = 'files' )
	{
		if ( $request == 'files' ) {
			/** check for Ajax uploaded files */
			$check = self::string( $name );
			if ( $check ) {
				$secret = md5( Sobi::Cfg( 'secret' ) );
				$fileName = str_replace( 'file://', null, $check );
				$path = SPLoader::dirPath( "tmp.files.{$secret}", 'front', false ) . '/' . $fileName;
				$cfg = SPFs::read( "{$path}.var" );
				$data = SPConfig::unserialize( $cfg );
				$_FILES[ $name ] = $data;
			}
		}
		self::init( $name, $request );
		self::$val = isset( self::$request[ self::$name ] ) ? self::$request[ self::$name ] : null;
		return ( $property && isset( self::$val[ $property ] ) ) ? self::$val[ $property ] : self::$val;
	}

	/**
	 * Returns task value
	 *
	 * @param string $name variable name
	 * @param bool $default default value
	 * @param string $method request method
	 * @return bool
	 */
	static public function task( $method = 'REQUEST' )
	{
		return self::cmd( SOBI_TASK, null, $method );
	}

	/**
	 * checks if index exist within the request
	 * @param string $name variable name
	 * @param string $method
	 * @return string
	 */
	static public function exists( $name, $method = 'REQUEST' )
	{
		self::init( $name, $method );
		return isset( self::$request[ self::$name ] ) ? true : false;
	}

	/**
	 * Clean array
	 *
	 * @param array $arr array to clean
	 * @return array
	 */
	static public function cleanArray( $arr, $delEmpty = false )
	{
		if ( !empty( $arr ) ) {
			foreach ( $arr as $k => $v ) {
				if ( is_array( $v ) ) {
					$arr[ $k ] = self::cleanArray( $v, $delEmpty );
				}
				else {
					$arr[ $k ] = self::filter( $v );
					if ( $delEmpty && !( strlen( $v ) ) ) {
						unset( $arr[ $k ] );
					}
				}
			}
		}
		return $arr;
	}
}
