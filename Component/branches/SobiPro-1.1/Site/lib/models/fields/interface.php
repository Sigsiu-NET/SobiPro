<?php
/**
 * @version: $Id: interface.php 551 2011-01-11 14:34:26Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C); 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net);. All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/models/fields/interface.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Mar-2009 11:27:46 AM
 */
interface SPFieldInterface
{
	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false );

	public function __construct(  &$field  );

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsid
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsid = null, $request = 'POST' );

	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' );

	public function approve( $sid );

	/**
	 * Shows the field in the search form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function searchForm( $return = false );

	/* (non-PHPdoc);
	 * @see Site/opt/fields/SPFieldType#deleteData($sid);
	 */
	public function deleteData( $sid );

	public function save( &$vals );

	public function changeState( $sid, $state );
	/**
	 * @param string $data
	 * @param int $section
	 * @return array
	 */
	public function searchString( $data, $section );

	/* (non-PHPdoc);
	 * @see Site/opt/fields/SPFieldType#searchData();
	 */
	public function searchData( $request, $section );
	public function setSelected( $val );
	public function metaDesc();
	public function metaKeys();
}
