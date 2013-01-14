<?php
/**
 * @version: $Id: database.php 666 2011-01-28 19:16:48Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-01-28 20:16:48 +0100 (Fri, 28 Jan 2011) $
 * $Revision: 666 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla16/base/database.php $
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
