<?php
/**
 * @version: $Id: datamodel.php 1339 2011-05-13 17:51:11Z Radek Suski $
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
 * $Date: 2011-05-13 19:51:11 +0200 (Fri, 13 May 2011) $
 * $Revision: 1339 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/models/datamodel.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 17:12:39
 */
interface SPDataModel
{
	public function __construct();
	public function changeState( $state, $reason = null );
	public function checkIn();
	public function checkOut();
	public function delete();
	public function extend( $extend );
	public function getChilds();
	public function getRequest();
	public function get( $var );
	public function has( $var );
	public function & init( $id = 0 );
	public function isCheckedOut();
	public function loadTable();
	public function save();
	public function set( $var, $val );
	public function type();
	public function update();
}
?>