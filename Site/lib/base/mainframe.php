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
 * @created 10-Jan-2009 17:09:58
 */
interface SPMainframeInterface
{
	public function __construct();

	public function path( $path );

	/**
	 * Gets basic data from the CMS (e.g Joomla); and stores in the #SPConfig instance
	 */
	public function getBasicCfg();

	/**
	 * @return SPMainFrame
	 */
	public static function & getInstance();

	/**
	 * @static
	 * @param    string    $msg    The error message, which may also be shown the user if need be.
	 * @param int|string $code The application-internal error code for this error
	 * @param    mixed    $info    Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN);.
	 * @param bool $translate
	 * @return    object    $error    The configured JError object
	 */
	public function runAway( $msg, $code = 500, $info = null, $translate = false );

	/**
	 * @return string
	 */
	public function getBack();

	/**
	 * @static
	 * @param $add
	 * @param    string    $msg    The message, which may also be shown the user if need be.
	 * @param string $msgtype
	 * @param bool $now
	 * @return
	 */
	public function setRedirect( $add, $msg = null, $msgtype = 'message', $now = false );

	/**
	 * @static
	 * @param string $msg The message, which may also be shown the user if need be.
	 * @param null $type
	 * @return
	 */
	public function msg( $msg, $type = null );

	/**
	 * @static
	 */
	public function redirect();

	/**
	 * @param SPDBObject $obj
	 * @param array $site
	 * @return void
	 */
	public function & addObjToPathway( $obj, $site = [] );

	/**
	 * @param array $head
	 */
	public function addHead( $head );

	/**
	 * Creating array of additional variables depend on the CMS
	 * @internal param array $var
	 * @return string
	 */
	public function form();

	/**
	 * Creating URL from a array for the current CMS
	 * @param array $var
	 * @param bool $js
	 * @return string
	 */
	public static function url( $var = null, $js = false );

	public function endOut();

	/**
	 * @param int $id
	 * @return
	 * @internal param $id
	 */
	public function & getUser( $id = 0 );

	/**
	 * Switching error reporting and displaying of errors compl. off
	 * For e.g JavaScript, or XML output where the document structure is very sensible
	 *
	 */
	public function & cleanBuffer();

	/**
	 * @param string $title
	 */
	public function setTitle( $title );
}
