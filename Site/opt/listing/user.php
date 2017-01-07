<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
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
class SPUserListing extends SPSectionCtrl implements SPListing
{
	/** @var string */
	protected $_type = 'listing';
	/** @var string */
	public static $compatibility = '1.1';
	/** @var int */
	protected $uid = 0;
	/** @var stdClass */
	protected $user = 0;

	public function execute()
	{
		$this->view();
	}

	protected function view()
	{
		/* determine template package */
		$tplPackage = Sobi::Cfg( 'section.template', SPC::DEFAULT_TEMPLATE );
		Sobi::ReturnPoint();
		$this->_task = 'user';

		if ( !( $this->_model ) ) {
			$this->setModel( 'section' );
			$this->_model->init( Sobi::Section() );
		}
		$this->visible();
		/* load template config */
		$this->template();
		$this->tplCfg( $tplPackage );

		/* get limits - if defined in template config - otherwise from the section config */
		$eLimit = $this->tKey( $this->template, 'entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) );
		$eInLine = $this->tKey( $this->template, 'entries_in_line', Sobi::Cfg( 'list.entries_in_line', 2 ) );

		$url = [ 'sid' => SPRequest::sid(), 'task' => 'list.user' ];
		if ( SPRequest::int( 'uid' ) ) {
			$url[ 'uid' ] = SPRequest::int( 'uid' );
			$this->uid = (int)SPRequest::int( 'uid' );
		}
		else {
			$this->uid = (int)Sobi::My( 'id' );
			SPRequest::set( 'uid', $this->uid );
		}
		$this->user = SPJoomlaUser::getBaseData( (int)$this->uid );
		if ( !( $this->user ) ) {
			throw new SPException( SPLang::e( 'UNAUTHORIZED_ACCESS' ) );
		}

		/* get the site to display */
		$site = SPRequest::int( 'site', 1 );
		$eLimStart = ( ( $site - 1 ) * $eLimit );

		$eOrder = $this->parseOrdering( 'entries', 'eorder', $this->tKey( $this->template, 'entries_ordering', Sobi::Cfg( 'list.entries_ordering', 'name.asc' ) ) );
		$eCount = count( $this->getEntries( $eOrder, 0, 0, true, [ 'spo.owner' => $this->uid ], true, Sobi::Section() ) );
		$entries = $this->getEntries( $eOrder, $eLimit, $eLimStart, true, [ 'spo.owner' => $this->uid ], true, Sobi::Section() );
//		$eCount = count( $this->_getEntries( 0, 0, true ) );
//		$entries = $this->_getEntries( $eLimit, $site );

		$pn = SPFactory::Instance( 'helpers.pagenav_' . $this->tKey( $this->template, 'template_type', 'xslt' ), $eLimit, $eCount, $site, $url );
		if ( SPRequest::int( 'site', 0 ) ) {
			$url[ 'site' ] = SPRequest::int( 'site', 0 );
		}
		SPFactory::header()->addCanonical( Sobi::Url( $url, true, true, true ) );
		/* handle meta data */
		SPFactory::header()->objMeta( $this->_model );
		SPFactory::mainframe()->addToPathway( Sobi::Txt( 'UL.PATH_TITLE', [ 'username' => $this->user->username, 'user' => $this->user->name ] ), Sobi::Url( 'current' ) );
		SPFactory::header()->addTitle( Sobi::Txt( 'UL.TITLE', [ 'username' => $this->user->username, 'user' => $this->user->name, 'section' => $this->_model->get( 'name' ) ] ), [ ceil( $eCount / $eLimit ), $site ] );
		/* add pathway */

		/* get view class */
		$view = SPFactory::View( 'listing' );
		$view->assign( $eLimit, '$eLimit' );
		$view->assign( $eLimStart, '$eLimStart' );
		$view->assign( $eCount, '$eCount' );
		$view->assign( $eInLine, '$eInLine' );
		$view->assign( $this->_task, 'task' );
		$view->assign( $this->_model, 'section' );
		$view->setConfig( $this->_tCfg, $this->template );
		$view->setTemplate( $tplPackage . '.' . $this->templateType . '.' . $this->template );
		$navigation = $pn->get();
		$view->assign( $navigation, 'navigation' );
		$visitor = SPFactory::user()->getCurrent();
		$view->assign( $visitor, 'visitor' );
		$view->assign( $entries, 'entries' );
		Sobi::Trigger( 'UserListing', 'View', [ &$view ] );
		$view->display();
	}

	public function entries( $field = null )
	{
		return $this->getEntries( 0, 0, true );
	}

//	public function _getEntries( $eLimit, $site, $ids = false )
//	{
//		$conditions = array( 'owner' => $this->uid );
//		$entries = array();
//		$eOrder = 'id';
//		/* get the site to display */
//		$eLimStart = ( ( $site - 1 ) * $eLimit );
//
//		/* check user permissions for the visibility */
//		if ( Sobi::My( 'id' ) ) {
//			$this->userPermissionsQuery( $conditions );
//		}
//		else {
//			$conditions = array_merge( $conditions, array( 'state' => '1', '@VALID' => SPFactory::db()->valid( 'validUntil', 'validSince' ) ) );
//		}
//		try {
//			$results = SPFactory::db()
//					->select( 'id', 'spdb_object', $conditions, $eOrder, $eLimit, $eLimStart, true )
//					->loadResultArray();
//		} catch ( SPException $x ) {
//			Sobi::Error( 'UserListing', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
//		}
//		if ( $ids ) {
//			return $results;
//		}
//		if ( count( $results ) ) {
//			foreach ( $results as $i => $sid ) {
//				$entries[ $i ] = $sid;
//			}
//		}
//		return $entries;
//	}

	public function setParams( $request )
	{
	}

	/**
	 * @param string $task
	 */
	public function setTask( $task )
	{
		$this->_task = strlen( $task ) ? $task : $this->_defTask;
		$helpTask = $this->_type . '.' . $this->_task;
		Sobi::Trigger( $this->name(), __FUNCTION__, [ &$this->_task ] );
		SPFactory::registry()->set( 'task', $helpTask );
	}
}
