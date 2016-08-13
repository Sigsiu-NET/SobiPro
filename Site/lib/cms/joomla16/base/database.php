<?php
/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname( __FILE__ ) . '/../../joomla_common/base/database.php';
/**
 * @author Radek Suski
 * @version 1.0
 * @created 08-Jul-2008 9:43:25 AM
 */
class SPDb extends SPJoomlaDb implements SPDatabase
{
	/**
	 * @return SPDb
	 */
	public static function & getInstance()
	{
		static $db = null;
		if ( !$db || !( $db instanceof self ) ) {
			$db = new self();
		}
		return $db;
	}

	/**
	 * Returns a database escaped string
	 *
	 * @param string $text string to be escaped
	 * @param bool $esc extra escaping
	 * @return string
	 */
	public function escape( $text, $esc = false )
	{
		return $this->db->escape( $text, $esc );
	}

	/**
	 * Load an array of single field results into an array
	 *
	 * @throws SPException
	 * @return array
	 */
	public function loadResultArray()
	{
		try {
			$r = $this->db->loadColumn();
			$this->count++;
		} catch ( JException $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Load a assoc list of database rows
	 *
	 * @param string $key field name of a primary key
	 * @throws SPException
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadAssocList( $key = null )
	{
		try {
			$r = $this->db->loadAssocList( $key );
			$this->count++;
		} catch ( JException $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}
}
