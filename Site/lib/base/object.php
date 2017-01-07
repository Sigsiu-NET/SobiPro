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
 * @created 13-Jan-2009 3:55:13 PM
 */

class SPObject
{

	protected $messages = [];
	/**
	 * @return string
	 */
	public function name()
	{
		return get_class( $this );
	}

	public function __construct()
	{
	}

	public function & setMessage( $message, $index = null )
	{
		if( $index ) {
			$this->messages[ $index ] = $message;
		}
		else {
			$this->messages[] = $message;
		}
		return $this;
	}

	/**
	 * Converts array to attributes
	 * @param array $arr
	 * @return void
	 */
	public function castArray( $arr )
	{
		if ( is_array( $arr ) && count( $arr ) ) {
			foreach ( $arr as $attr => $value ) {
				$this->$attr = $value;
			}
		}
	}

	/**
	 * Std. getter. Returns a property of the object or the default value if the property is not set.
	 * @param string $attr
	 * @param mixed $default
	 * @return mixed
	 */
	public function get( $attr, $default = null )
	{
		if ( $this->has( $attr ) ) {
			if ( is_string( $this->$attr ) ) {
				return stripslashes( $this->$attr );
			}
			else {
				return $this->$attr;
			}
		}
		else {
			return $default;
		}
	}

	/**
	 * @param string $var
	 * @param mixed $val
	 * @return $this
	 */
	public function & set( $var, $val )
	{
		if ( isset( $this->$var ) || property_exists( $this, $var ) ) {
			if ( is_array( $this->$var ) && is_string( $val ) && strlen( $val ) > 2 ) {
				try {
					$val = SPConfig::unserialize( $val, $var );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( '%s.', $x->getMessage() ), SPC::NOTICE, 0, __LINE__, __FILE__ );
				}
			}
			$this->$var = $val;
		}
		return $this;
	}

	/**
	 * Check if attribute exist
	 *
	 * @param string $var
	 * @return bool
	 */
	public function has( $var )
	{
		return /*isset( $this->$var ); // */
				property_exists( $this, $var );
	}
}
