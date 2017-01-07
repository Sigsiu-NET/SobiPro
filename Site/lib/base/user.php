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
 * @created 10-Jan-2009 5:51:03 PM
 */
interface SPUserInterface
{
	public function __destruct();

	/**
	 * @access    public
	 * @param $subject
	 * @param string $action - e.g. edit
	 * @param null $section
	 * @param string $ownership - e.g. own, all
	 * @return bool - true if authorized
	 */
	public function can( $subject, $action = 'access', $section = null, $ownership = 'all' );
	/**
	 * @return SPUser
	 */
	public static function & getCurrent();
	/**
	 * Sets the value of a user state variable.
	 * @param	string	$key 	- The path of the state.
	 * @param	string	$value 	- The value of the variable.
	 * @return	mixed	The previous state, if one existed.
	 */
	public function setUserState(  $key, $value );
	/**
	 * Gets the value of a user state variable.
	 * @param	string $key 	- The key of the user state variable.
	 * @param	string $request - The name of the variable passed in a request.
	 * @param	string $default - The default value for the variable if not found. Optional.
	 * @param	string $type	- Filter for the variable.
	 * @return	mixed
	 */
	public function getUserState( $key, $request, $default = null, $type = 'none' );

	/**
	 * Creates new user instance or returns existing if already created
	 *
	 * @param int $id
	 * @return	SPUserInterface
	 */
	public function & getInstance( $id = 0 );
}
