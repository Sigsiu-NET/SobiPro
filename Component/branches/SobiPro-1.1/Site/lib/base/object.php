<?php
/**
 * @version: $Id: object.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/object.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 13-Jan-2009 3:55:13 PM
 */

class SPObject
{

	/**
	 * @return string
	 */
	public function name()
	{
		return get_class( $this );
	}

	public function __construct() {}

	/**
	 */
	public function toXML()
	{
	}

	/**
	 * Converts array to attributes
	 * @param array $arr
	 * @return void
	 */
	public function castArray( $arr )
	{
		if( is_array( $arr ) && count( $arr ) ) {
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
		if( $this->has( $attr ) ) {
			if( is_string( $this->$attr ) ) {
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
	 * Check if attribute exist
	 *
	 * @param string $var
	 * @return bool
	 */
	public function has( $var )
	{
		return /*isset( $this->$var ); // */property_exists( $this, $var );
	}
}
?>