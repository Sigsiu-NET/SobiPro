<?php
/**
 * @version: $Id$
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'config', true );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 06-Aug-2010 15:38:15
 */
class SPFilter extends SPConfigAdmCtrl
{
	/**
	 * @var string
	 */
	protected $_defTask = 'list';
	/**
	 * @var string
	 */
	protected $_type = 'filter';

	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'list':
				$this->screen();
				Sobi::ReturnPoint();
				break;
			case 'edit':
			case 'add':
				$this->edit();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'save':
				$this->save();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !parent::execute() ) {
					Sobi::Error( 'filter_ctrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	protected function delete()
	{
		$filters = $this->getFilters();
		$id = SPRequest::cmd( 'filter_id' );
		if ( $id && isset( $filters[ $id ] ) && ( strlen( $filters[ $id ][ 'options' ] ) ) ) {
			unset( $filters[ $id ] );
			SPFactory::registry()->saveDBSection( $filters, 'fields_filter' );
			$this->response( Sobi::Url( 'filter' ), Sobi::Txt( 'FLR.MSG_FILTER_DELETED' ), true, SPC::SUCCESS_MSG );
		}
		else {
			$this->response( Sobi::Url( 'filter' ), SPLang::e( 'FILTER_NOT_FOUND' ), true, SPC::ERROR_MSG );
		}
	}

	protected function save()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$id = SPRequest::cmd( 'filter_id' );
		if ( $id ) {
			$this->validate( 'field.filter', 'filter' );
			$filters = $this->getFilters();
			$name = SPRequest::string( 'filter_name', 'Filter Name' );
			$msg = str_replace( array( "\n", "\t", "\r" ), null, SPLang::clean( SPRequest::string( 'filter_message', 'The data entered in the $field field contains not allowed characters' ) ) );
			$regex = SPLang::clean( SPRequest::raw( 'filter_regex', '/^[\.*]+$/' ) );
			$regex = str_replace( '[:apostrophes:]', '\"' . "\'", $regex );
			$regex = base64_encode( str_replace( array( "\n", "\t", "\r" ), null, $regex ) );
			$custom = 'custom';
			if ( isset( $filters[ $id ] ) && !( strlen( $filters[ $id ][ 'options' ] ) ) ) {
				$regex = $filters[ $id ][ 'params' ];
				$custom = null;
			}
			$filters[ $id ] = array(
				'params' => $regex,
				'key' => $id,
				'value' => $name,
				'description' => $msg,
				'options' => $custom
			);
			SPFactory::registry()->saveDBSection( $filters, 'fields_filter' );
			$this->response( Sobi::Url( 'filter' ), Sobi::Txt( 'FLR.MSG_FILTER_SAVED' ), false, 'success' );
		}
		else {
			$this->response( Sobi::Url( 'filter' ), SPLang::e( 'FILTER_NOT_FOUND' ), true, SPC::ERROR_MSG );
		}
	}

	private function getFilters()
	{
		$registry =& SPFactory::registry();
		$registry->loadDBSection( 'fields_filter' );
		$filters = $registry->get( 'fields_filter' );
		$f = array();
		foreach ( $filters as $fid => $filter ) {
			$f[ $fid ] = array(
				'params' => $filter[ 'params' ],
				'key' => $fid,
				'value' => $filter[ 'value' ],
				'description' => $filter[ 'description' ],
				'options' => $filter[ 'options' ]
			);
		}
		ksort( $f );
		return $f;
	}

	private function edit()
	{
		$id = SPRequest::cmd( 'fid' );
		$filters = $this->getFilters();
		if ( count( $filters ) && isset( $filters[ $id ] ) ) {
			$Filter = array(
				'id' => $id,
				'regex' => str_replace( '\"' . "\'", '[:apostrophes:]', base64_decode( $filters[ $id ][ 'params' ] ) ),
				'name' => $filters[ $id ][ 'value' ],
				'message' => $filters[ $id ][ 'description' ],
				'editable' => strlen( $filters[ $id ][ 'options' ] ),
				'readonly' => !( strlen( $filters[ $id ][ 'options' ] ) )
			);
		}
		else {
			$Filter = array( 'id' => '', 'regex' => '', 'name' => '', 'message' => '', 'editable' => true, 'readonly' => false );
		}
		$view = SPFactory::View( 'view', true );
		$view->assign( $this->_task, 'task' );
		$view->assign( $Filter, 'filter' );
		$view->determineTemplate( 'field', 'filter' );
		$view->setTemplate( 'default' );
		$view->display();
	}

	private function screen()
	{
		$filters = $this->getFilters();
		$Filters = array();
		if ( count( $filters ) ) {
			foreach ( $filters as $name => $filter ) {
				$Filters[ ] = array(
					'id' => $name,
					'regex' => str_replace( '\"' . "\'", '[:apostrophes:]', base64_decode( $filter[ 'params' ] ) ),
					'name' => $filter[ 'value' ],
					'message' => $filter[ 'description' ],
					'editable' => strlen( $filter[ 'options' ] )
				);
			}
		}
		$menu = $this->createMenu( 'filter' );
		/** @var $view  SPAdmView */
		$view = SPFactory::View( 'view', true );
		$view->assign( $this->_task, 'task' )
				->assign( $this->createMenu(), 'menu' )
				->assign( Sobi::Url( array( 'task' => 'filter.edit', 'out' => 'html' ), true ), 'edit_url' )
				->assign( $Filters, 'filters' )
				->assign( $menu, 'menu' )
				->determineTemplate( 'field', 'filters' );
		$view->display();
	}
}
