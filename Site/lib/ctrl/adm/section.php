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

SPLoader::loadController( 'controller' );
SPLoader::loadController( 'section' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:39:25 PM
 */
class SPSectionAdmCtrl extends SPSectionCtrl
{
	/**
	 */
	public function execute()
	{
		switch ( $this->_task ) {
			case 'add':
				$this->setModel( SPLoader::loadModel( 'section' ) );
				$this->editForm();
				break;
			case 'edit':
				Sobi::Redirect( Sobi::Url( array( 'task' => 'config', 'sid' => SPRequest::sid() ) ), null, true );
				break;
			case 'view':
			case 'entries':
				Sobi::ReturnPoint();
				$this->view( $this->_task == 'entries', Sobi::GetUserState( 'entries_filter', 'sp_entries_filter', null ) );
				break;
			case 'toggle.enabled':
			case 'toggle.approval':
				$this->toggleState();
				break;
			default:
				/* case plugin didn't register this task, it was an error */
				if ( !( parent::execute() ) ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	/**
	 */
	protected function view( $allEntries, $term = null )
	{
		if ( $allEntries ) {
			SPRequest::set( 'task', 'section.entries' );
		}
		else {
			SPRequest::set( 'task', 'section.view' );
		}
		/* @var SPdb $db */
		$db = SPFactory::db();
		$c = array();
		$e = array();
		if ( !( Sobi::Section() ) ) {
			Sobi::Error( 'Section', SPLang::e( 'Missing section identifier' ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		$this->_model->init( Sobi::Section() );
		/* get the lists ordering and limits */
		$eLimit = Sobi::GetUserState( 'entries.limit', 'elimit', Sobi::Cfg( 'admin.entries-limit', 25 ) );
		$cLimit = Sobi::GetUserState( 'categories.limit', 'climit', Sobi::Cfg( 'admin.categories-limit', 15 ) );

		$eLimStart = SPRequest::int( 'eSite', 0 );
		$cLimStart = SPRequest::int( 'cSite', 0 );

		/* get child categories and entries */
		/* @todo: need better method - the query can be very large with lot of entries */
		if ( !( $allEntries ) ) {
			$e = $this->_model->getChilds();
			$c = $this->_model->getChilds( 'category' );
		}
		/** yes - this is needed. In case we have entries without data in the name field */
		elseif ( !( $term && $allEntries ) ) {
			$c = $this->_model->getChilds( 'category', true );
			$c[ ] = Sobi::Section();
			if ( count( $c ) ) {
				try {
					$e1 = $db
							->dselect( 'id', 'spdb_relations', array( 'pid' => $c, 'oType' => 'entry' ) )
							->loadResultArray();
					$e2 = $db
							->dselect( 'sid', 'spdb_field_data', array( 'section' => Sobi::Section(), 'fid' => Sobi::Cfg( 'entry.name_field' ) ) )
							->loadResultArray();
					$e = array_merge( $e1, $e2 );
					$e = array_unique( $e );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
		}
		else {
			try {
				$e = $db
						->dselect( 'sid', 'spdb_field_data', array( 'section' => Sobi::Section(), 'fid' => Sobi::Cfg( 'entry.name_field' ), 'baseData' => "%{$term}%" ) )
						->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}

		// just in case the given site is grater than all existing sites
		$cCount = count( $c );
		$cPages = ceil( $cCount / $cLimit );
		if ( $cLimStart > $cPages ) {
			$cLimStart = $cPages;
			SPRequest::set( 'cSite', $cPages );
		}
		$eCount = count( $e );
		$ePages = ceil( $eCount / $eLimit );
		if ( $eLimStart > $ePages ) {
			$eLimStart = $ePages;
			SPRequest::set( 'eSite', $ePages );
		}

		$entries = array();
		$categories = array();
		/* if there are entries in the root */
		if ( count( $e ) ) {
			try {
				$Limit = $eLimit > 0 ? $eLimit : 0;
				$LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;
				$eOrder = $this->parseOrdering( 'entries', 'eorder', 'position.asc', $Limit, $LimStart, $e );
				$results = $db
						->select( 'id', 'spdb_object', array( 'id' => $e, 'oType' => 'entry' ), $eOrder, $Limit, $LimStart )
						->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			foreach ( $results as $i => $entry ) {
				$entries[ $i ] = $entry;
			}
		}

		/* if there are categories in the root */
		if ( count( $c ) ) {
			try {
				$LimStart = $cLimStart ? ( ( $cLimStart - 1 ) * $cLimit ) : $cLimStart;
				$Limit = $cLimit > 0 ? $cLimit : 0;
				$cOrder = $this->parseOrdering( 'categories', 'corder', 'order.asc', $Limit, $LimStart, $c );
				$results = $db
						->select( 'id', 'spdb_object', array( 'id' => $c, 'oType' => 'category' ), $cOrder, $Limit, $LimStart )
						->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			foreach ( $results as $i => $category ) {
				$categories[ $i ] = SPFactory::Category( $category );
			}
		}
		/* create menu */
		$mClass = SPLoader::loadClass( 'views.adm.menu' );
		$menu = new $mClass( 'section.' . $this->_task, Sobi::Section() );
		/* load the menu definition */
		$cfg = SPLoader::loadIniFile( 'etc.adm.section_menu' );
		Sobi::Trigger( 'Create', 'AdmMenu', array( &$cfg ) );
		if ( count( $cfg ) ) {
			foreach ( $cfg as $section => $keys ) {
				$menu->addSection( $section, $keys );
			}
		}
		Sobi::Trigger( 'AfterCreate', 'AdmMenu', array( &$menu ) );
		/* create new SigsiuTree */
		$tree = SPLoader::loadClass( 'mlo.tree' );
		$tree = new $tree( Sobi::GetUserState( 'categories.order', 'corder', 'order.asc' ) );
		/* set link */
		$tree->setHref( Sobi::Url( array( 'sid' => '{sid}' ) ) );
		$tree->setId( 'menuTree' );
		/* set the task to expand the tree */
		$tree->setTask( 'category.expand' );
		$tree->init( Sobi::Reg( 'current_section' ) );
		/* add the tree into the menu */
		$menu->addCustom( 'AMN.ENT_CAT', $tree->getTree() );

		$entriesName = SPFactory::config()->nameField()->get( 'name' );
		$entriesField = SPFactory::config()->nameField()->get( 'nid' );
		$view = SPFactory::View( 'section', true );
		$view->assign( $entriesName, 'entries_name' )
				->assign( $entriesField, 'entries_field' )
				->assign( $eLimit, 'entries-limit' )
				->assign( $cLimit, 'categories-limit' )
				->assign( SPRequest::int( 'eSite', 1 ), 'entries-site' )
				->assign( SPRequest::int( 'cSite', 1 ), 'categories-site' )
				->assign( $cCount, 'categories-count' )
				->assign( $eCount, 'entries-count' )
				->assign( $this->_task, 'task' )
				->assign( $term, 'filter' )
				->assign( $this->customCols(), 'fields' )
				->assign( $this->_model, 'section' )
				->assign( $categories, 'categories' )
				->assign( $entries, 'entries' )
				->assign( SPFactory::config()->nameField()->get( 'name' ), 'entries_name' )
				->assign( $menu, 'menu' )
				->assign( Sobi::GetUserState( 'entries.eorder', 'eorder', 'order.asc' ), 'ordering' )
				->assign( Sobi::GetUserState( 'categories.corder', 'corder', 'order.asc' ), 'corder' )
				->assign( Sobi::Section( true ), 'category' )
				->addHidden( Sobi::Section(), 'pid' )
				->addHidden( SPRequest::sid(), 'sid' );
		Sobi::Trigger( 'Section', 'View', array( &$view ) );
		$view->display();
	}

	// @todo duplicates the same method in category ctrl - need to merge it
	protected function customCols()
	{
		/* get fields for header */
		$fields = array();
		try {
			$fieldsData = SPFactory::db()
					->select( '*', 'spdb_field', array( '!admList' => 0, 'section' => Sobi::Reg( 'current_section' ) ), 'admList' )
					->loadObjectList();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if ( count( $fieldsData ) ) {
			$fModel = SPLoader::loadModel( 'field', true );
			foreach ( $fieldsData as $field ) {
				$fit = new $fModel();
				/* @var SPField $fit */
				$fit->extend( $field );
				$fields[ ] = $fit;
			}
		}
		return $fields;
	}

	/**
	 * @param string $subject
	 * @param string $col
	 * @param string $def
	 * @param int $lim
	 * @param int $lStart
	 * @param $sids
	 * @return string
	 */
	protected function parseOrdering( $subject, $col, $def, &$lim, &$lStart, &$sids )
	{
		$ord = Sobi::GetUserState( $subject . '.order', $col, Sobi::Cfg( 'admin.' . $subject . '-order', $def ) );
		$ord = str_replace( array( 'e_s', 'c_s' ), null, $ord );
		if ( strstr( $ord, '.' ) ) {
			$ord = explode( '.', $ord );
			$dir = $ord[ 1 ];
			$ord = $ord[ 0 ];
		}
		else {
			$dir = 'asc';
		}
		if ( $ord == 'order' || $ord == 'position' ) {
			$subject = $subject == 'categories' ? 'category' : 'entry';
			/* @var SPdb $db */
			$db = SPFactory::db();
			$db->select( 'id', 'spdb_relations', array( 'oType' => $subject, 'pid' => $this->_model->get( 'id' ) ), 'position.' . $dir, $lim, $lStart );
			$fields = $db->loadResultArray();
			if ( count( $fields ) ) {
				$sids = $fields;
				$fields = implode( ',', $fields );
				$ord = "field( id, {$fields} )";
				$lStart = 0;
				$lim = 0;
			}
			else {
				$ord = 'id.' . $dir;
			}
		}
		elseif ( $ord == 'name' ) {
			$subject = $subject == 'categories' ? 'category' : 'entry';
			/* @var SPdb $db */
			$db =& SPFactory::db();
			$db->select( 'id', 'spdb_language', array( 'oType' => $subject, 'sKey' => 'name', 'language' => Sobi::Lang() ), 'sValue.' . $dir );
			$fields = $db->loadResultArray();
			if ( !count( $fields ) && Sobi::Lang() != Sobi::DefLang() ) {
				$db->select( 'id', 'spdb_language', array( 'oType' => $subject, 'sKey' => 'name', 'language' => Sobi::DefLang() ), 'sValue.' . $dir );
				$fields = $db->loadResultArray();
			}
			if ( count( $fields ) ) {
				$fields = implode( ',', $fields );
				$ord = "field( id, {$fields} )";
			}
			else {
				$ord = 'id.' . $dir;
			}
		}
		elseif ( strstr( $ord, 'field_' ) ) {
			$db = SPFactory::db();
			static $field = null;
			if ( !$field ) {
				try {
					$db->select( 'fieldType', 'spdb_field', array( 'nid' => $ord, 'section' => Sobi::Section() ) );
					$fType = $db->loadResult();
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DETERMINE_FIELD_TYPE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
				if ( $fType ) {
					$field = SPLoader::loadClass( 'opt.fields.' . $fType );
				}
			}
			/* *
			 * @TODO The whole sort by custom field method in admin panel has to be re-implemented -
			 * We could use the same field 'sortBy' method for backend and frontend.
			 * The current method could be very inefficient !!!
			 */
			if ( $field && method_exists( $field, 'sortByAdm' ) ) {
				$fields = call_user_func_array( array( $field, 'sortByAdm' ), array( &$ord, &$dir ) );
			}
			else {
				$join = array(
					array( 'table' => 'spdb_field', 'as' => 'def', 'key' => 'fid' ),
					array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' )
				);
				$db->select( 'sid', $db->join( $join ), array( 'def.nid' => $ord, 'lang' => Sobi::Lang() ), 'baseData.' . $dir );
				$fields = $db->loadResultArray();
			}
			if ( count( $fields ) ) {
				$fields = implode( ',', $fields );
				$ord = "field( id, {$fields} )";
			}
			else {
				$ord = 'id.' . $dir;
			}
		}
		elseif ( $ord == 'state' ) {
			$ord = $ord . '.' . $dir . ', validSince.' . $dir . ', validUntil.' . $dir;
		}
		else {
			$ord = $ord . '.' . $dir;
		}
		return $ord;
	}

	/**
	 */
	private function editForm()
	{
		$this->_model->formatDatesToEdit();
		/** @var $view SPSectionAdmView */
		$view = SPFactory::View( 'section', true );
		$view->assign( $this->_task, 'task' )
				->assign( $this->_model, 'section' )
				->determineTemplate( 'section', 'edit' )
				->display();
	}
}
