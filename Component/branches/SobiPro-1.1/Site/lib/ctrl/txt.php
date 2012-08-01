<?php
/**
 * @version: $Id: txt.php 1187 2011-04-15 07:47:13Z Radek Suski $
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
 * $Date: 2011-04-15 09:47:13 +0200 (Fri, 15 Apr 2011) $
 * $Revision: 1187 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/txt.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 15-Jul-2010 18:17:28
 */
class SPJsTxt extends SPController
{
	/**
	 * @var string
	 */
	protected $_defTask = 'js';
	public function __construct() {}
	/**
	 */
	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'js':				
				$this->js();
				break;
		}

	}
	protected function js()
	{
		$lang = SPLang::jsLang();
		if( count( $lang ) ) {
			foreach ( $lang as $term => $text ) {
				unset( $lang[ $term ] );
				$term = str_replace( 'SP.JS_', null, $term );
				$lang[ $term ] = $text;
			}
		}
		if( !( SPRequest::int( 'deb' ) ) ) {
			SPFactory::mainframe()->cleanBuffer();
			header( 'Content-type: text/javascript' );
		}
		echo 'SobiPro.setLang( '.json_encode( $lang ).' );';
		exit;
	}
}
?>