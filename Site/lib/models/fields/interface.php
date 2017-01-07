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
	 * @param string $tsId
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' );

	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @param string $request
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
