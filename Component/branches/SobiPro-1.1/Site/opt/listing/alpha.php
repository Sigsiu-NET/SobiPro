<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'listing_interface' );
SPLoader::loadController( 'section' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 16-Aug-2010 16:14:15
 */
class SPAlphaListing extends SPSectionCtrl implements SPListing
{
	/**
	 * @var string
	 */
	private $_letter = null;
	/**
	 * @var string
	 */
	private $_field = null;
	/**
	 * @var string
	 */
	private $_nid = null;
	/**
	 * @var string
	 */
	private $_fieldType = null;
	/**
	 * @var string
	 */
	protected $_type = 'listing';
	/**
	 * @var string
	 */
	public static $compatibility = '1.1';

	public function execute()
	{
		$task = str_replace( ':', '-', SPRequest::task( 'get' ) );
		$task = explode( '.', $task );
		if ( isset( $task[ 2 ] ) && $task[ 2 ] == 'switch' && isset( $task[ 3 ] ) ) {
			return $this->switchIndex( $task[ 3 ] );
		}
		else {
			if ( SPRequest::cmd( 'letter' ) ) {
				$this->_letter = urldecode( SPRequest::cmd( 'letter' ) );
			}
			else {
				$this->_letter = urldecode( $task[ 2 ] );
				SPRequest::set( 'letter', strtoupper( $this->_letter ), 'get' );
				if ( isset( $task[ 3 ] ) ) {
					$this->determineFid( $task[ 3 ] );
				}
				else {
					$this->determineFid( Sobi::Cfg( 'alphamenu.primary_field' ) );
				}
			}
			if ( !( $this->_letter ) || !( Sobi::Section() ) ) {
				Sobi::Error( $this->name(), SPLang::e( 'SITE_NOT_FOUND_MISSING_PARAMS' ), SPC::NOTICE, 404, __LINE__, __FILE__ );
			}
			if ( !( preg_match( '/^[\x20-\x7f]*$/D', $this->_letter ) ) && function_exists( 'mb_strtolower' ) ) {
				$this->_letter = mb_strtoupper( $this->_letter );
			}
			else {
				$this->_letter = strtoupper( $this->_letter );
			}
			$this->view();
		}
	}

	private function switchIndex( $field )
	{
		$tplPckg = Sobi::Cfg( 'section.template', 'default2' );
		$letters = explode( ',', Sobi::Cfg( 'alphamenu.letters' ) );
		if ( Sobi::Cfg( 'alphamenu.verify' ) ) {
			$entries = SPFactory::cache()->getVar( 'alpha_entries_' . $field );
			if ( !$entries ) {
				$entries = array();
				foreach ( $letters as $letter ) {
					$params = array( 'letter' => $letter );
					if ( $field ) {
						$params[ 'field' ] = $field;
					}
					$this->setParams( $params );
					$entries[ $letter ] = $this->entries( $field );
				}
				SPFactory::cache()->addVar( $entries, 'alpha_entries_' . $field );
			}
			foreach ( $letters as $letter ) {
				$le = array( '_complex' => 1, '_data' => trim( $letter ) );
				if ( count( $entries[ $letter ] ) ) {
					$task = 'list.alpha.' . trim( strtolower( $letter ) ) . '.' . $field;
					$le[ '_attributes' ] = array( 'url' => Sobi::Url( array( 'sid' => Sobi::Section(), 'task' => $task ) ) );
				}
				$l[ ] = $le;
			}
		}
		else {
			foreach ( $letters as $i => $letter ) {
				$l[ ] = array(
					'_complex' => 1,
					'_data' => trim( $letter ),
					'_attributes' => array( 'url' => Sobi::Url( array( 'sid' => Sobi::Section(), 'task' => 'list.alpha.' . trim( strtolower( $letter ) ) ) ) )
				);
			}
		}
		$data = array( '_complex' => 1, '_data' => array( 'letters' => $l ) );
		/* get view class */
		$view = SPFactory::View( 'listing' );
		$view->setTemplate( $tplPckg . '.common.alphaindex' );
		$view->assign( $data, 'alphaMenu' );
		ob_start();
		$view->display( 'menu', 'raw' );
		$out = ob_get_contents();
		SPFactory::mainframe()->cleanBuffer();
		header( 'Content-type: application/json' );
		echo json_encode( array( 'index' => $out ) );
		exit;
	}

	protected function determineFid( $nid )
	{
		if ( is_numeric( $nid ) ) {
			$field = SPFactory::db()->select(
				array( 'fid', 'fieldType', 'nid' ),
				'spdb_field',
				array( 'section' => Sobi::Section(), /*'enabled' => 1, */
					'fid' => $nid )
			)->loadObject();
		}
		else {
			$field = SPFactory::db()->select(
				array( 'fid', 'fieldType', 'nid' ),
				'spdb_field',
				array( 'section' => Sobi::Section(), /*'enabled' => 1, */
					'nid' => $nid )
			)->loadObject();
		}
		$this->_field = $field->fid;
		$this->_nid = $field->nid;
		$this->_fieldType = $field->fieldType;
		SPRequest::set( 'alpha_field', strtolower( $this->_nid ), 'get' );
	}

	protected function view()
	{
		/* determine template package */
		$tplPckg = Sobi::Cfg( 'section.template', 'default2' );
		Sobi::ReturnPoint();
		$this->_task = 'alpha';

		if ( !( $this->_model ) ) {
			$this->setModel( 'section' );
			$this->_model->init( Sobi::Section() );
		}

		/* load template config */
		$this->template();
		$this->tplCfg( $tplPckg );

		/* get limits - if defined in template config - otherwise from the section config */
		$eLimit = $this->tKey( $this->template, 'entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) );
		$eInLine = $this->tKey( $this->template, 'entries_in_line', Sobi::Cfg( 'list.entries_in_line', 2 ) );

		/* get the site to display */
		$site = SPRequest::int( 'site', 1 );
		$eLimStart = ( ( $site - 1 ) * $eLimit );
		$eCount = count( $this->getEntries( 0, 0, true ) );
		$entries = $this->getEntries( $eLimit, $site );
		$compare = $this->_field ? $this->_field : $this->_nid;
		if ( strlen( $compare ) && $compare != Sobi::Cfg( 'alphamenu.primary_field' ) ) {
			$t = 'list.alpha.' . strtolower( $this->_letter ) . '.' . $this->_nid;
		}
		else {
			$t = 'list.alpha.' . strtolower( $this->_letter );
		}

		$pn = SPFactory::Instance(
			'helpers.pagenav_' . $this->tKey( $this->template, 'template_type', 'xslt' ),
			$eLimit, $eCount, $site,
			array( 'sid' => SPRequest::sid(), 'task' => $t )
		);
		$cUrl = array( 'sid' => SPRequest::sid(), 'task' => $t );
		if ( SPRequest::int( 'site', 0 ) ) {
			$cUrl[ 'site' ] = SPRequest::int( 'site', 0 );
		}
		SPFactory::header()->addCanonical( Sobi::Url( $cUrl, true, true, true ) );

		/* handle meta data */
		SPFactory::header()->objMeta( $this->_model );
		$letter = urldecode( SPRequest::cmd( 'letter' ) );
		/* add pathway */
		if ( !( $this->_fieldType ) ) {
			SPFactory::mainframe()->addToPathway( Sobi::Txt( 'AL.PATH_TITLE', array( 'letter' => $letter ) ), Sobi::Url( 'current' ) );
			SPFactory::mainframe()->setTitle( Sobi::Txt( 'AL.TITLE', array( 'letter' => $letter, 'section' => $this->_model->get( 'name' ) ) ) );
		}
		else {
			$field =& SPFactory::Model( 'field' );
			$field->init( $this->_field );
			SPFactory::mainframe()->addToPathway( Sobi::Txt( 'AL.PATH_TITLE_FIELD', array( 'letter' => $letter, 'field' => $field->get( 'name' ) ) ), Sobi::Url( 'current' ) );
			SPFactory::mainframe()->setTitle( Sobi::Txt( 'AL.TITLE_FIELD', array( 'letter' => $letter, 'section' => $this->_model->get( 'name' ), 'field' => $field->get( 'name' ) ) ) );
		}

		/* get view class */
		$view = SPFactory::View( 'listing' );
		$view->assign( $eLimit, '$eLimit' );
		$view->assign( $eLimStart, '$eLimStart' );
		$view->assign( $eCount, '$eCount' );
		$view->assign( $eInLine, '$eInLine' );
		$view->assign( $this->_task, 'task' );
		$view->assign( $this->_model, 'section' );
		$view->assign( Sobi::Txt( 'AL.PATH_TITLE', array( 'letter' => $this->_letter ) ), 'listing_name' );
		$view->setConfig( $this->_tCfg, $this->template );
		$view->setTemplate( $tplPckg . '.' . $this->templateType . '.' . $this->template );
		$view->assign( $pn->get(), 'navigation' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->assign( $entries, 'entries' );
		Sobi::Trigger( 'AlphaListing', 'View', array( &$view ) );
		$view->display();
	}

	public function entries( $field = null )
	{
		if ( $field ) {
			$this->determineFid( $field );
		}
		else {
			$this->_field = Sobi::Cfg( 'alphamenu.primary_field', SPFactory::config()->nameField()->get( 'id' ) );
		}
		return $this->getEntries( 0, 0, true );
	}

	public function getEntries( $eLimit, $site, $ids = false )
	{
		$conditions = array();
		$entries = array();

		/* get the site to display */
		$eLimStart = ( ( $site - 1 ) * $eLimit );

		if ( isset( $this->_letter[ 1 ] ) && $this->_letter[ 1 ] == '-' ) {
			$this->_letter = "[{$this->_letter[ 0 ]}-{$this->_letter[ 2 ]}]";
		}
		$db = SPFactory::db();
		/*
		 * Don't know exactly why but on Windows servers there seems to be some problem with unicode chars
		 *     - strtolower/strtoupper is destroying these chars completely
		 *     - MySQL seems to be suddenly case sensitive with non-latin chars so we need to ask both
		 *
		 * Wed, Apr 4, 2012: Apparently it's not only Windows related
		 */
		if ( !( preg_match( '/^[\x20-\x7f]*$/D', $this->_letter ) ) && function_exists( 'mb_strtolower' ) ) {
			// if we have multibyte string support - ask both cases ...
			$baseCondition = "REGEXP:^{$this->_letter}|^" . mb_strtoupper( $this->_letter );
		}
		else {
			// if no unicode - great, it'll work.
			// if we don't have MB - shit happens
			$baseCondition = "REGEXP:^{$this->_letter}";
		}
		switch ( $this->_fieldType ) {
			case 'chbxgroup':
			case 'select':
			case 'multiselect':
				$eOrder = 'sValue';
				$table = $db->join(
					array(
						array( 'table' => 'spdb_field_option_selected', 'as' => 'opts' ),
						array( 'table' => 'spdb_language', 'as' => 'lang', 'key' => array( 'opts.optValue', 'lang.sKey' ) ),
						array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => array( 'opts.sid', 'spo.id' ) ),
						array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => array( 'opts.sid', 'sprl.id' ) ),
					)
				);
				$oPrefix = 'spo.';
				$conditions[ 'spo.oType' ] = 'entry';
				$conditions[ 'opts.fid' ] = $this->_field;
				$conditions[ 'lang.sValue' ] = $baseCondition;
				break;
			default:
				$eOrder = 'baseData';
				$table = $db->join(
					array(
						array( 'table' => 'spdb_field', 'as' => 'fdef', 'key' => 'fid' ),
						array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ),
						array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => array( 'fdata.sid', 'spo.id' ) ),
						array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => array( 'fdata.sid', 'sprl.id' ) ),
					)
				);
				$oPrefix = 'spo.';
				$conditions[ 'spo.oType' ] = 'entry';
				$conditions[ 'fdef.fid' ] = $this->_field;
				$conditions[ 'fdata.baseData' ] = $baseCondition;
				break;
		}
		$this->_field = $this->_field ? $this->_field : Sobi::Cfg( 'alphamenu.primary_field', SPFactory::config()->nameField()->get( 'id' ) );

		/* check user permissions for the visibility */
		if ( Sobi::My( 'id' ) ) {
			$this->userPermissionsQuery( $conditions, $oPrefix );
		}
		else {
			$conditions = array_merge( $conditions, array( $oPrefix . 'state' => '1', '@VALID' => $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' ) ) );
		}
		$conditions[ 'sprl.copy' ] = '0';
		try {
			$db->select( $oPrefix . 'id', $table, $conditions, $eOrder, $eLimit, $eLimStart, true );
			$results = $db->loadResultArray();
		} catch ( SPException $x ) {
			Sobi::Error( 'AlphaListing', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if ( $ids ) {
			return $results;
		}
		if ( count( $results ) ) {
			foreach ( $results as $i => $sid ) {
				// it needs too much memory moving the object creation to the view
				//$entries[ $i ] = SPFactory::Entry( $sid );
				$entries[ $i ] = $sid;
			}
		}
		return $entries;
	}

	public function setParams( $request )
	{
		if ( isset( $request[ 'letter' ] ) ) {
			$this->_letter = $request[ 'letter' ];
		}
		if ( isset( $request[ 'field' ] ) ) {
			$this->_field = $this->determineFid( $request[ 'field' ] );
		}
	}

	/**
	 * @param string $task
	 */
	public function setTask( $task )
	{
		$this->_task = strlen( $task ) ? $task : $this->_defTask;
		$helpTask = $this->_type . '.' . $this->_task;
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$this->_task ) );
		SPFactory::registry()->set( 'task', $helpTask );
	}
}
