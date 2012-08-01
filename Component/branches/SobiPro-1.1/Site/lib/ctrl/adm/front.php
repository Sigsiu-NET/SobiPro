<?php
/**
 * @version: $Id: front.php 2318 2012-03-27 12:03:46Z Radek Suski $
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
 * $Date: 2012-03-27 14:03:46 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2318 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/front.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:38:03 PM
 */
class SPAdminPanel extends SPController
{
	/**
	 * @var array
	 */
	private $_sections = array();
	/**
	 * @var string
	 */
	protected $_defTask = 'panel';
	/**
	 * @var string
	 */
	protected $_type = 'front';

	/**
	 */
	private function getSections()
	{
		$order = $this->parseOrdering();
		/* @var SPdb $db */
		$db =& SPFactory::db();
		try {
			$db->select( '*', 'spdb_object', array( 'oType' => 'section' ), $order );
			$sections = $db->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		if( count( $sections ) ) {
			SPLoader::loadClass( 'models.datamodel' );
			SPLoader::loadClass( 'models.dbobject' );
			SPLoader::loadModel( 'section' );
			foreach ( $sections as $section ) {
				if( Sobi::Can( 'section', 'access', 'valid', $section->id ) ) {
					$s = new SPSection();
					$s->extend( $section );
					$this->_sections[] = $s;
				}
			}
		}
	}
	/**
	 * @param string $col
	 * @param string $def
	 * @param int $lim
	 * @param int $lStart
	 * @return string
	 */
	protected function parseOrdering()
	{
		$order = Sobi::GetUserState( 'sections.order', 'order', 'name.asc' );
		$ord = $order;
		$dir = 'asc';
		if( strstr( $order, '.' ) ) {
			$ord = explode( '.', $ord );
			$dir = $ord[ 1 ];
			$ord = $ord[ 0 ];
		}
		if( $ord == 'position' ) {
			$ord = 'name';
		}
		if( $ord == 'name' ) {
			/* @var SPdb $db */
			$db	=& SPFactory::db();
			try {
				$db->select( 'id', 'spdb_language', array( 'oType' => 'section', 'sKey' => 'name', 'language' => Sobi::Lang() ), 'sValue.'.$dir );
				$fields = $db->loadResultArray();
				if( !count( $fields ) && Sobi::Lang() != Sobi::DefLang() ) {
					$db->select( 'id', 'spdb_language', array( 'oType' => 'section', 'sKey' => 'name', 'language' => Sobi::DefLang() ), 'sValue.'.$dir );
					$fields = $db->loadResultArray();
				}
			}
			catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
				return false;
			}
			if( count( $fields ) ) {
				$fields = implode( ',', $fields );
				$ord = "field( id, {$fields} )";
			}
			else {
				$ord = 'id.'.$dir;
			}
		}
		else {
			$ord = ( isset( $dir ) && strlen( $dir ) ) ? $ord . '.' . $dir : $ord;
		}
		SPFactory::user()->setUserState( 'sections.order', $ord );
		return $ord;
	}

	/**
	 */
	public function execute()
	{
		switch ( $this->_task ) {
			case 'panel':
				$icons = array();
				$cfg = SPLoader::loadIniFile( 'etc.adm.cpanel' );
				foreach ( $cfg as $sec => $set ) {
					$set[ 'name' ] = $sec;
					$icons[] = $set;
				}
				$this->getSections();
				$class = SPLoader::loadView( 'front', true );
				$view = new $class();
				$view->loadConfig( 'front.config' );
				$view->setTemplate( 'front.default' );
				$view->assign( $this->_sections, 'sections' );
				$view->assign( $icons, 'icons' );
				$about = SPFactory::Instance( 'cms.html.about' );
				$about->add( $view );
				Sobi::Trigger( 'Panel', 'View', array( &$view ) );
				ob_start( array( $about, 'update' ) );
				$view->display();
				ob_end_flush();
				break;

			default:
				/* case plugin didn't registered this task, it was an error */
				if( !parent::execute() ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}
}
