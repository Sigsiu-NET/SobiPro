<?php
/**
 * @version: $Id: field.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/field.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Mar-2009 11:23:22 AM
 */
class SPFieldCtrl extends SPController
{
	protected $m_SPField;


	public function __construct()
	{
	}

	public function execute()
	{

	}

	public function authorise()
	{
	}

	public function extend( $extend )
	{
	}

	public function getModel()
	{
	}

	public function setModel( $name )
	{
	}

	/**
	 *
	 * @param sid
	 */
	public function loadData($sid)
	{
	}

	/**
	 *
	 * @param sid
	 */
	public function loadDefinitions($sid)
	{
	}

	/**
	 *
	 * @param fid
	 */
	public function getField($fid)
	{
	}

	/**
	 *
	 * @param sid
	 */
	public function loadViewData($sid)
	{
	}
}
?>
