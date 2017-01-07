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
	private $_sections = [];
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
					->select( '*', 'spdb_object', [ 'oType' => 'section' ], 'id' )
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
			Sobi::Trigger( $this->name(), __FUNCTION__, [ &$this->_sections ] );
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
				$view->determineTemplate( 'front', SPC::DEFAULT_TEMPLATE );
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
