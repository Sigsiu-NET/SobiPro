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
 * Cookie handler
 * @author Radek Suski
 * @version 1.0
 * @created 10-Feb-2010 09:25:42
 */
abstract class SPCookie
{
	const prefix = 'SPro_';

	/**
	 * @param string $name - The name of the cookie.
	 * @param string $value - The value of the cookie
	 * @param int $expire - The time the cookie expires. This is a Unix timestamp so is in number of seconds since the epoch
	 * @param bool $httponly - When true the cookie will be made accessible only through the HTTP protocol.
	 * @param bool $secure - Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client
	 * @param string $path - The path on the server in which the cookie will be available on
	 * @param string $domain - The domain that the cookie is available
	 * @return bool
	 */
	public static function set( $name, $value, $expire = 0, $httponly = false, $secure = false, $path = '/', $domain = null )
	{
		$name = self::prefix.$name;
		$expire = ( $expire == 0 ) ? $expire : time() + $expire;
		return SPFactory::mainframe()->setCookie( $name, $value, $expire, $httponly, $secure , $path, $domain ) && SPRequest::string( $name, null, false, 'cookie' );
	}

	/**
	 * Delete cookie
	 * @param $name - The name of the cookie.
	 * @return bool
	 */
	public static function delete( $name )
	{
		$name = self::prefix.$name;
		return SPFactory::mainframe()->setCookie( $name, '', ( time() - 36000 ) );
	}

	/**
	 * convert hours to minutes
	 * @param int $time number of minutes
	 * @return int
	 */
	public static function minutes( $time )
	{
		return $time * 60;
	}

	/**
	 * convert hours to seconds
	 * @param int $time number of hours
	 * @return int
	 */
	public static function hours( $time )
	{
		return self::minutes( $time ) * 60;
	}

	/**
	 * convert days to seconds
	 * @param int $time number of days
	 * @return int
	 */
	public static function days( $time )
	{
		return self::hours( $time ) * 24;
	}
}
