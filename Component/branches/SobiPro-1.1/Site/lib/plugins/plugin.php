<?php
/**
 * @version: $Id: plugin.php 1277 2011-04-26 10:43:06Z Radek Suski $
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
 * $Date: 2011-04-26 12:43:06 +0200 (Tue, 26 Apr 2011) $
 * $Revision: 1277 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/plugins/plugin.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @updated 13-Feb-2010 14:11:22
 */
abstract class SPApplication extends SPObject
{
	/**
	 * to check if the plugin have implementation for the called action
	 * @param string $action
	 * @return bool
	 */
	abstract function provide( $action );

	/**
	 * @var string
	 */
	protected $id = null;

	/**
	 * @param string $id - unique id string of the plugin
	 * @return void
	 */
	public function __construct( $id )
	{
		$this->id = $id;
	}
}
// well, ... hmmm - shit happens
abstract class SPPlugin extends SPApplication {}
?>