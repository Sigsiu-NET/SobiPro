<?php
/**
 * @version: $Id: front.php 2076 2011-12-15 18:04:51Z Radek Suski $
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
 * $Date: 2011-12-15 19:04:51 +0100 (Thu, 15 Dec 2011) $
 * $Revision: 2076 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/front.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:38:03 PM
 */
class SPFront extends SPController
{
	/**
	 * @var array
	 */
	private $_sections = array();
	/**
	 * @var string
	 */
	protected $_defTask = 'front';
	/**
	 * @var string
	 */
	protected $_type = 'front';

	/**
	 */
	private function getSections()
	{
		try {
			$sections = SPFactory::db()
					->select( '*', 'spdb_object', array( 'oType' => 'section' ), 'id' )
					->loadObjectList();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_SECTIONS_LIST', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		if ( count( $sections ) ) {
			foreach ( $sections as $section ) {
				if ( Sobi::Can( 'section', 'access', $section->id, 'valid' ) ) {
					$s = SPFactory::Section( $section->id );
					$s->extend( $section );
					$this->_sections[ ] = $s;
				}
			}
			Sobi::Trigger( $this->name(), __FUNCTION__, array( &$this->_sections ) );
		}
	}

	/**
	 */
	public function execute()
	{
		/* parent class executes the plugins */
		SPRequest::set( 'task', $this->_type . '.' . $this->_task );
		switch ( $this->_task ) {
			case 'front':
				$this->getSections();
				/** @var $view SPAdmPanelView */
				$view = SPFactory::View( 'front' );
				/* load template config */
//				$this->tplCfg( 'front' );
//				$view->setConfig( $this->_tCfg, 'general' );
				$view->determineTemplate( 'front', 'default' );
				$view->assign( $this->_sections, 'sections' );
				$view->display();
				break;

			default:
				/* case parents or plugin didn't registered this task, it was an error */
				if ( !( parent::execute() ) ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}
}
