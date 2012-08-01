<?php
/**
 * @version: $Id: user.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/user.php $
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
	 * @access 	public
	 * @param string $action - e.g. edit
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
?>