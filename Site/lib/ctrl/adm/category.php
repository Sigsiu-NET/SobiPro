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
SPLoader::loadController( 'category' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:39:25 PM
 */
class SPCategoryAdmCtrl extends SPCategoryCtrl
{
	/**
	 */
	public function execute()
	{
		/* parent class executes the plugins */
		$r = false;
		switch ( $this->_task ) {
			case 'edit':
			case 'add':
				SPLoader::loadClass( 'html.input' );
				$this->editForm();
//				$this->authorise( $this->_task );
				break;
			case 'view':
				Sobi::ReturnPoint();
				SPLoader::loadClass( 'html.input' );
				$this->view();
				break;
			case 'clone':
				$this->authorise( 'edit' );
				$r = true;
				$this->_model = null;
				SPRequest::set( 'category_id', 0, 'post' );
				SPRequest::set( 'category_state', 0, 'post' );
				$this->save( false, true );
				break;
			case 'reorder':
				$this->authorise( 'edit' );
				$r = true;
				$this->reorder();
				SPFactory::cache()->cleanCategories();
				break;
			case 'up':
			case 'down':
				SPFactory::cache()->cleanCategories();
				$this->authorise( 'edit' );
				$r = true;
				$this->singleReorder( $this->_task == 'up' );
				break;
			case 'approve':
			case 'unapprove':
				$this->authorise( 'edit' );
				$r = true;
				$this->approval( $this->_task == 'approve' );
				break;
			case 'hide':
			case 'publish':
				$this->authorise( 'edit' );
				$r = true;
				SPFactory::cache()->cleanCategories();
				$this->state( $this->_task == 'publish' );
				break;
			case 'toggle.enabled':
			case 'toggle.approval':
				SPFactory::cache()->cleanCategories();
				$this->authorise( 'edit' );
				$r = true;
				$this->toggleState();
				break;
			case 'delete':
				/** Wed, Jan 15, 2014 11:05:28
				 * in the administrator are we can delete category only from the list
				 * Preventing deletion of THIS category (Issue #1162)
				 * Basically if there was no array of $cids something went wrong
				 */
				$cids = SPRequest::arr( 'c_sid', [] );
				if ( count( $cids ) ) {
					SPFactory::cache()->cleanCategories();
					parent::execute();
				}
				else {
					$this->response( Sobi::Back(), SPLang::e( 'DELETE_CAT_NO_ID' ), false, SPC::ERROR_MSG );
				}
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !parent::execute() ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
		return $r;
	}

	protected function state( $state )
	{
		if ( $this->_model->get( 'id' ) ) {
			$this->authorise( $this->_task );
			$this->_model->changeState( $state );
			SPFactory::cache()
					->purgeSectionVars()
					->deleteObj( 'category', $this->_model->get( 'id' ) )
					->deleteObj( 'category', $this->_model->get( 'parent' ) );

			$this->response( Sobi::Back(), Sobi::Txt( $state ? 'CAT.PUBLISHED' : 'CAT.UNPUBLISHED' ), false );
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'CHANGE_NO_ID' ), false, SPC::ERROR_MSG );
		}
	}

	protected function toggleState()
	{
		if ( $this->_task == 'toggle.enabled' ) {
			$this->state( !( $this->_model->get( 'state' ) ) );
		}
		else {
			$this->approval( !( $this->_model->get( 'approved' ) ) );
		}
	}

	/**
	 * @param $approve
	 */
	private function approval( $approve )
	{
		$sids = SPRequest::arr( 'c_sid', [] );
		if ( !( count( $sids ) ) ) {
			if ( $this->_model->get( 'id' ) ) {
				$sids = [ $this->_model->get( 'id' ) ];
			}
			else {
				$sids = [];
			}
		}
		if ( !( count( $sids ) ) ) {
			$this->response( Sobi::Back(), Sobi::Txt( 'CHANGE_NO_ID' ), false, SPC::ERROR_MSG );
		}
		else {
			foreach ( $sids as $sid ) {
				try {
					SPFactory::db()->update( 'spdb_object', [ 'approved' => $approve ? 1 : 0 ], [ 'id' => $sid, 'oType' => 'category' ] );
					SPFactory::cache()->deleteObj( 'category', $sid );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
			SPFactory::cache()->purgeSectionVars();
			$this->response( Sobi::Back(), Sobi::Txt( $approve ? 'CAT.APPROVED' : 'CAT.UNAPPROVED' ), false );
		}
	}

	/**
	 */
	private function reorder()
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		/* get the requested ordering */
		$sids = SPRequest::arr( 'cp_sid', [] );
		/* re-order it to the valid ordering */
		$order = [];
		asort( $sids );

		$cLimStart = SPRequest::int( 'cLimStart', 0 );
		$cLimit = Sobi::GetUserState( 'adm.categories.limit', 'climit', Sobi::Cfg( 'adm_list.cats_limit', 15 ) );
		$LimStart = $cLimStart ? ( ( $cLimStart - 1 ) * $cLimit ) : $cLimStart;
		if ( count( $sids ) ) {
			$c = 0;
			foreach ( $sids as $sid => $pos ) {
				$order[ ++$c ] = $sid;
			}
		}
		foreach ( $order as $sid ) {
			try {
				$db->update( 'spdb_relations', [ 'position' => ++$LimStart ], [ 'id' => $sid, 'oType' => 'category' ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		$this->response( Sobi::Back(), Sobi::Txt( 'CATEGORIES_ARE_RE_ORDERED' ), true, SPC::SUCCESS_MSG );
	}

	/**
	 */
	protected function view()
	{
		/* @var SPdb $db */
		$db = SPFactory::db();

		SPRequest::set( 'task', 'category.view' );

		/* get the lists ordering and limits */
		$eLimit = Sobi::GetUserState( 'entries.limit', 'elimit', Sobi::Cfg( 'admin.entries-limit', 25 ) );
		$cLimit = Sobi::GetUserState( 'categories.limit', 'climit', Sobi::Cfg( 'admin.categories-limit', 15 ) );

		$eLimStart = SPRequest::int( 'eSite', 0 );
		$cLimStart = SPRequest::int( 'cSite', 0 );

		/* get child categories and entries */
		$e = $this->_model->getChilds();
		$c = $this->_model->getChilds( 'category' );

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

		$entries = [];
		$categories = [];
		SPLoader::loadClass( 'models.dbobject' );

		/* if there are categories in the root */
		if ( count( $c ) ) {
			try {
				$LimStart = $cLimStart ? ( ( $cLimStart - 1 ) * $cLimit ) : $cLimStart;
				$Limit = $cLimit > 0 ? $cLimit : 0;
				$cOrder = $this->parseCategoryOrdering( 'categories', 'corder', 'position.asc', $Limit, $LimStart, $c );
				$db->select( '*', 'spdb_object', [ 'id' => $c, 'oType' => 'category' ], $cOrder, $Limit, $LimStart );
				$results = $db->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			foreach ( $results as $i => $category ) {
				$categories[ $i ] = SPFactory::Category( $category ); // new $cClass();
				//$categories[ $i ]->extend( $category );
			}
		}

		/* if there are entries in the root */
		if ( count( $e ) ) {
			try {
				$LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;
				$Limit = $eLimit > 0 ? $eLimit : 0;
				$eOrder = $this->parseCategoryOrdering( 'entries', 'eorder', 'position.asc', $Limit, $LimStart, $e );
				$entries = $db
						->select( '*', 'spdb_object', [ 'id' => $e, 'oType' => 'entry' ], $eOrder, $Limit, $LimStart )
						->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		$entriesName = SPFactory::config()
				->nameField()
				->get( 'name' );
		$entriesField = SPFactory::config()
				->nameField()
				->get( 'nid' );

		/* create menu */
		SPLoader::loadClass( 'views.adm.menu' );
		$menu = new SPAdmSiteMenu( 'section.' . $this->_task, SPRequest::sid() );
		/* load the menu definition */
		$cfg = SPLoader::loadIniFile( 'etc.adm.section_menu' );
		Sobi::Trigger( 'Create', 'AdmMenu', [ &$cfg ] );
		if ( count( $cfg ) ) {
			$i = 0;
			foreach ( $cfg as $section => $keys ) {
				$i++;
				$menu->addSection( $section, $keys );
				if ( $i && !( Sobi::Can( 'section.configure' ) ) ) {
					break;
				}
			}
		}
		/* create new SigsiuTree */
		$tree = SPLoader::loadClass( 'mlo.tree' );
		$tree = new $tree( Sobi::GetUserState( 'categories.order', 'corder', 'position.asc' ) );
		/* set link */
		$tree->setHref( Sobi::Url( [ 'sid' => '{sid}' ] ) );
		$tree->setId( 'menuTree' );
		/* set the task to expand the tree */
		$tree->setTask( 'category.expand' );
		$tree->init( Sobi::Reg( 'current_section' ), SPRequest::sid() );
		/* add the tree into the menu */
		$menu->addCustom( 'AMN.ENT_CAT', $tree->getTree() );

		/* get view class */
		$view = SPFactory::View( 'category', true );
		$eSite = SPRequest::int( 'eSite', 1 );
		$cSite = SPRequest::int( 'cSite', 1 );
		$customCols = $this->customCols();
		$userStateEOrder = Sobi::GetUserState( 'entries.eorder', 'eorder', 'position.asc' );
		$userStateCOrder = Sobi::GetUserState( 'categories.corder', 'corder', 'position.asc' );
		$catName = $this->_model->get( 'name' );
		$pid = Sobi::Section();
		$sid = SPRequest::sid();
		$view->assign( $eLimit, '$eLimit' )
				->assign( $eLimit, 'entries-limit' )
				->assign( $cLimit, 'categories-limit' )
				->assign( $eSite, 'entries-site' )
				->assign( $cSite, 'categories-site' )
				->assign( $cCount, 'categories-count' )
				->assign( $eCount, 'entries-count' )
				->assign( $this->_task, 'task' )
				->assign( $this->_model, 'category' )
				->assign( $categories, 'categories' )
				->assign( $entries, 'entries' )
				->assign( $customCols, 'fields' )
				->assign( $entriesName, 'entries_name' )
				->assign( $entriesField, 'entries_field' )
				->assign( $menu, 'menu' )
				->assign( $userStateEOrder, 'eorder' )
				->assign( $userStateCOrder, 'corder' )
				->assign( $catName, 'category_name' )
				->addHidden( $pid, 'pid' )
				->addHidden( $sid, 'sid' );
		Sobi::Trigger( 'Category', 'View', [ &$view ] );
		$view->display();
	}

	// @todo duplicates the same method in section ctrl - need to merge it
	public function customCols()
	{
		/* get fields for header */
		$fields = [];
		try {
			$fieldsData = SPFactory::db()
					->select( '*', 'spdb_field', [ 'admList' => 1, 'section' => Sobi::Reg( 'current_section' ) ] )
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
				$fields[] = $fit;
			}
		}
		return $fields;
	}

	/**
	 */
	private function editForm()
	{
		/* if adding new */
		if ( !$this->_model || $this->_task == 'add' ) {
			$this->setModel( SPLoader::loadModel( 'category' ) );
		}
		$this->checkTranslation();
		$fonts = SPFactory::config()->getSettings();
		$fonts = $fonts[ 'icon-fonts' ];
		foreach ( $fonts as $font ) {
			SPFactory::header()->addHeadLink( $font, null, null, 'stylesheet' );
		}
		$this->_model->formatDatesToEdit();
		$id = $this->_model->get( 'id' );
		if ( !$id ) {
			$this->_model->set( 'state', 1 );
			$this->_model->set( 'parent', SPRequest::sid() );
		}
		if ( $this->_model->isCheckedOut() ) {
			SPFactory::message()->error( Sobi::Txt( 'CAT.IS_CHECKED_OUT' ), false );
		}
		else {
			$this->_model->checkOut();
		}
		$this->_model->loadFields( Sobi::Section(), true );
		$eFields = SPConfig::fields( Sobi::Section() );
		unset( $eFields[ Sobi::Cfg( 'entry.name_field' ) ] );
		$entryFields = [];
		$selectedEntryFields = $this->_model->get( 'entryFields' );
        if( !( is_array( $selectedEntryFields ) ) ) {
            $selectedEntryFields = [];
        }
		$all = $this->_model->get( 'allFields' );
		foreach ( $eFields as $id => $field ) {
			$entryFields[] = [ 'id' => $id, 'name' => $field, 'included' => $all ? true : in_array( $id, $selectedEntryFields ) ];
		}
		/* @var SPEntry $this ->_model */
		$fields = $this->_model->get( 'fields' );
		// we need it for the icons' fonts
		SPFactory::header()->initBase();
		$view = SPFactory::View( 'category', true );
		$owner = SPFactory::CmsHelper()->userSelect( 'category.owner', ( $this->_model->get( 'owner' ) ? $this->_model->get( 'owner' ) : ( $this->_model->get( 'id' ) ? 0 : Sobi::My( 'id' ) ) ), true );
		$view->assign( $this->_model, 'category' )
				->assign( $this->_task, 'task' )
				->assign( $owner, 'owner' )
				->assign( $id, 'cid' )
				->assign( $fields, 'fields' )
				->assign( $entryFields, 'entryFields' )
				->addHidden( Sobi::Section(), 'pid' );
		Sobi::Trigger( 'Category', 'EditView', [ &$view ] );
		$view->display();
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
	protected function parseCategoryOrdering( $subject, $col, $def, &$lim, &$lStart, &$sids )
	{
		$ord = Sobi::GetUserState( $subject . '.order', $col, Sobi::Cfg( 'admin.' . $subject . '-order', $def ) );
		/** legacy - why the hell I called it order?! */
		$ord = str_replace( 'order', 'position', $ord );
		$ord = str_replace( [ 'e_s', 'c_s' ], null, $ord );
		$dir = 'asc';
		if ( strstr( $ord, '.' ) ) {
			$ord = explode( '.', $ord );
			$dir = $ord[ 1 ];
			$ord = $ord[ 0 ];
		}
		if ( $ord == 'position' ) {
			$subject = $subject == 'categories' ? 'category' : 'entry';
			/* @var SPdb $db */
			$db = SPFactory::db();
			$db->select( 'id', 'spdb_relations', [ 'oType' => $subject, 'pid' => $this->_model->get( 'id' ) ], 'position.' . $dir, $lim, $lStart );
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
			$db->select( 'id', 'spdb_language', [ 'oType' => $subject, 'sKey' => 'name', 'language' => Sobi::Lang() ], 'sValue.' . $dir );
			$fields = $db->loadResultArray();
			if ( !count( $fields ) && Sobi::Lang() != Sobi::DefLang() ) {
				$db->select( 'id', 'spdb_language', [ 'oType' => $subject, 'sKey' => 'name', 'language' => Sobi::DefLang() ], 'sValue.' . $dir );
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
		elseif ( strstr( $ord, 'field_' ) ) {
			$db =& SPFactory::db();
			static $field = null;
			if ( !$field ) {
				try {
					$db->select( 'fieldType', 'spdb_field', [ 'nid' => $ord, 'section' => Sobi::Section() ] );
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
				$fields = call_user_func_array( [ $field, 'sortByAdm' ], [] );
			}
			else {
				$join = [
						[ 'table' => 'spdb_field', 'as' => 'def', 'key' => 'fid' ],
						[ 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ]
				];
				$db->select( 'sid', $db->join( $join ), [ 'def.nid' => $ord, 'lang' => Sobi::Lang() ], 'baseData.' . $dir );
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
		else {
			$ord = $ord . '.' . $dir;
		}
		return $ord;
	}

	/**
	 * @param bool $up
	 */
	private function singleReorder( $up )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$eq = $up ? '<' : '>';
		$dir = $up ? 'position.desc' : 'position.asc';
		$current = $this->_model->get( 'position' );
		try {
			$interchange = $db
					->select( 'position, id', 'spdb_relations', [ 'position' . $eq => $current, 'oType' => $this->_model->type(), 'pid' => SPRequest::int( 'pid' ) ], $dir, 1 )
					->loadAssocList();
			if ( $interchange && count( $interchange ) ) {
				$db->update( 'spdb_relations', [ 'position' => $interchange[ 0 ][ 'position' ] ], [ 'oType' => $this->_model->type(), 'pid' => SPRequest::int( 'pid' ), 'id' => $this->_model->get( 'id' ) ], 1 );
				$db->update( 'spdb_relations', [ 'position' => $current ], [ 'oType' => $this->_model->type(), 'pid' => SPRequest::int( 'pid' ), 'id' => $interchange[ 0 ][ 'id' ] ], 1 );
			}
			else {
				$current = $up ? $current-- : $current++;
				$db->update( 'spdb_relations', [ 'position' => $current ], [ 'oType' => $this->_model->type(), 'pid' => SPRequest::int( 'pid' ), 'id' => $this->_model->get( 'id' ) ], 1 );
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$this->response( Sobi::Back(), Sobi::Txt( 'CATEGORY_POSITION_CHANGED' ), true, SPC::SUCCESS_MSG );
	}
}
