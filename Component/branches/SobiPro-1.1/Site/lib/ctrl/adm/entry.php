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
SPLoader::loadController( 'entry' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:38:30 PM
 */
class SPEntryAdmCtrl extends SPEntryCtrl
{
	/**
	 */
	public function execute()
	{
		$r = false;
		switch ( $this->_task ) {
			case 'edit':
			case 'add':
				$r = true;
				$this->editForm();
				break;
			case 'approve':
			case 'unapprove':
				$r = true;
				$this->approval( $this->_task == 'approve' );
				break;
			case 'up':
			case 'down':
				$r = true;
				$this->singleReorder( $this->_task == 'up' );
				break;
			case 'clone':
				$r = true;
				$this->_model = null;
				SPRequest::set( 'entry_id', 0, 'post' );
				SPRequest::set( 'entry_state', 0, 'post' );
				$this->save( false, true );
				break;
			case 'reorder':
				$r = true;
				$this->reorder();
				break;
			case 'reject':
				$r = true;
				$this->reject();
				break;
			case 'revisions':
				$r = true;
				$this->revisions();
				break;
			case 'search':
				$this->search();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !parent::execute() ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				else {
					$r = true;
				}
				break;
		}
		return $r;
	}

	protected function revisions()
	{
		$revision = SPFactory::message()->getRevision( SPRequest::cmd( 'revision' ) );
		$sid = SPRequest::sid();
		$fid = SPRequest::cmd( 'fid' );
		if ( strstr( $fid, 'field_' ) ) {
			$fid = SPFactory::db()
					->select( 'fid', 'spdb_field', array( 'nid' => $fid, 'section' => Sobi::Section() ) )
					->loadResult();
			/** @var SPField $field */
			$field = SPFactory::Model( 'field' );
			$field->init( $fid );
			$field->loadData( $sid );
			if ( isset( $revision[ 'changes' ][ 'fields' ][ $field->get( 'nid' ) ] ) ) {
				$revision = $revision[ 'changes' ][ 'fields' ][ $field->get( 'nid' ) ];
			}
			else {
				$revision = "";
			}
			$current = $field->getRaw();
			if ( !( is_array( $current ) ) ) {
				try {
					$current = SPConfig::unserialize( $current );
				} catch ( SPException $x ) {
				}
			}
			if ( !( is_array( $revision ) ) ) {
				try {
					$revision = SPConfig::unserialize( $revision );
				} catch ( SPException $x ) {
				}
			}
			try {
				$data = $field->compareRevisions( $revision, $current );
			} catch ( SPException $x ) {
				if ( is_array( $current ) ) {
					$current = print_r( $current, true );
				}
				if ( is_array( $revision ) ) {
					$revision = print_r( $revision, true );
				}
				$data = array(
					'current' => $current,
					'revision' => $revision
				);
			}
		}
		// core data
		else {
			$i = str_replace( 'entry.', null, $fid );
			if ( isset( $revision[ 'changes' ][ $i ] ) ) {
				$revision = $revision[ 'changes' ][ $i ];
			}
			else {
				$revision = "";
			}
			switch ( $i ) {
				case 'owner':
				case 'updater':
					$currentUser = null;
					$pastUser = null;
					if ( $this->_model->get( $i ) ) {
						$currentUser = SPUser::getBaseData( ( int )$this->_model->get( $i ) );
						$currentUser = $currentUser->name . ' (' . $currentUser->id . ')';
					}
					if ( $revision ) {
						$pastUser = SPUser::getBaseData( ( int )$revision );
						$pastUser = $pastUser->name . ' (' . $pastUser->id . ')';
					}
					$data = array(
						'current' => $currentUser,
						'revision' => $pastUser,
					);
					break;
				default:
					$data = array(
						'current' => $this->_model->get( $i ),
						'revision' => $revision
					);
					break;
			}
		}
		if ( !( SPRequest::bool( 'html', false, 'post' ) ) ) {
			$data = array(
				'current' => html_entity_decode( strip_tags( $data[ 'current' ] ), ENT_QUOTES, 'UTF-8' ),
				'revision' => html_entity_decode( strip_tags( $data[ 'revision' ] ), ENT_QUOTES, 'UTF-8' ),
			);

		}
		$data = array(
			'current' => explode( "\n", $data[ 'current' ] ),
			'revision' => explode( "\n", $data[ 'revision' ] )
		);
		$diff = SPFactory::Instance( 'services.third-party.diff.lib.Diff', $data[ 'revision' ], $data[ 'current' ] );
		$renderer = SPFactory::Instance( 'services.third-party.diff.lib.Diff.Renderer.Html.SideBySide' );
//		$renderer = SPFactory::Instance( 'services.third-party.diff.lib.Diff.Renderer.Html.Inline' );
		$difference = $diff->Render( $renderer );
		$data[ 'diff' ] = $difference;
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		echo json_encode( $data );
		exit;
	}

	protected function reject()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		if ( $this->authorise( 'manage' ) ) {
			$changes = array();
			$objects = array(
				'entry' => $this->_model,
				'user' => SPFactory::user(),
				'author' => SPFactory::Instance( 'cms.base.user', $this->_model->get( 'owner' ) )
			);
			$messages =& SPFactory::registry()->get( 'messages' );
			$reason = SPLang::replacePlaceHolders( SPRequest::string( 'reason', null, true, 'post' ), $objects );
			$objects[ 'reason' ] = $reason;
			$messages[ 'rejection' ] = $objects;
			SPFactory::registry()->set( 'messages', $messages );
			$this->_model->setMessage( $reason, 'reason' );
			if ( SPRequest::bool( 'unpublish', false, 'post' ) ) {
				$this->_model->changeState( 0, $reason, false );
				$changes[ ] = 'unpublish';
			}
			if ( SPRequest::bool( 'trigger_unpublish', false, 'post' ) ) {
				Sobi::Trigger( 'Entry', 'AfterChangeState', array( $this->_model, 0, 'messages' => $this->_model->get( 'messages' ) ) );
			}
			if ( SPRequest::bool( 'discard', false, 'post' ) ) {
				$changes[ ] = 'discard';
				$data = $this->_model->discard( false );
			}
			if ( SPRequest::bool( 'trigger_unapprove', false, 'post' ) ) {
				Sobi::Trigger( 'Entry', 'AfterUnapprove', array( $this->_model, 0 ) );
			}
			Sobi::Trigger( 'Entry', 'AfterReject', array( $this->_model, 0 ) );
			SPFactory::message()->logAction( 'reject', $this->_model->get( 'id' ), $data, $reason );
			$this->response( Sobi::Back(), Sobi::Txt( 'ENTRY_REJECTED', $this->_model->get( 'name' ) ), true, SPC::SUCCESS_MSG );
		}
	}

	protected function search()
	{
		$term = SPRequest::string( 'search', null, false, 'post' );
		$fid = Sobi::Cfg( 'entry.name_field' );
		/**
		 * @var $field SPField
		 */
		$field = SPFactory::Model( 'field' );
		$field->init( $fid );
		$s = Sobi::Section();
		$data = $field->searchSuggest( $term, $s, true, true );
		SPFactory::mainframe()->cleanBuffer();
		echo json_encode( $data );
		exit;
	}

	protected function save( $apply, $clone = false )
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$sets = array();
		if ( !( $clone ) ) {
			$sid = SPRequest::sid() ? SPRequest::sid() : SPRequest::int( 'entry_id' );
		}
		else {
			$sid = 0;
		}
		$apply = ( int )$apply;
		if ( !$this->_model ) {
			$this->setModel( SPLoader::loadModel( $this->_type ) );
		}
		$this->_model->init( $sid );

		$tplPackage = Sobi::Cfg( 'section.template', SPC::DEFAULT_TEMPLATE );
		$this->tplCfg( $tplPackage );
		$customClass = null;
		if ( isset( $this->_tCfg[ 'general' ][ 'functions' ] ) && $this->_tCfg[ 'general' ][ 'functions' ] ) {
			$customClass = SPLoader::loadClass( '/' . str_replace( '.php', null, $this->_tCfg[ 'general' ][ 'functions' ] ), false, 'templates' );
			if ( method_exists( $customClass, 'BeforeStoreEntry' ) ) {
				$customClass::BeforeStoreEntry( $this->_model, $_POST );
			}
		}

		$this->_model->getRequest( $this->_type );
		$this->authorise( $this->_model->get( 'id' ) ? 'edit' : 'add' );

		try {
			$this->_model->validate( 'post' );
		} catch ( SPException $x ) {
			$back = Sobi::GetUserState( 'back_url', Sobi::Url( array( 'task' => 'entry.add', 'sid' => Sobi::Section() ) ) );
			$data = $x->getData();
			$this->response( $back, $x->getMessage(), false, 'error', array( 'required' => $data[ 'field' ] ) );
		}

		try {
			$this->_model->save();
		} catch ( SPException $x ) {
			$back = Sobi::GetUserState( 'back_url', Sobi::Url( array( 'task' => 'entry.add', 'sid' => Sobi::Section() ) ) );
			$this->response( $back, $x->getMessage(), false, 'error' );
		}
		$sid = $this->_model->get( 'id' );
		$sets[ 'sid' ] = $sid;
		$sets[ 'entry.nid' ] = $this->_model->get( 'nid' );
		$sets[ 'entry.id' ] = $sid;

		if ( $customClass && method_exists( $customClass, 'AfterStoreEntry' ) ) {
			$customClass::AfterStoreEntry( $this->_model );
		}
		$this->logChanges( 'save', SPRequest::string( 'history-note' ) );
		if ( $apply || $clone ) {
			if ( $clone ) {
				$msg = Sobi::Txt( 'MSG.OBJ_CLONED', array( 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ) );
				$this->response( Sobi::Url( array( 'task' => $this->_type . '.edit', 'sid' => $sid ) ), $msg );
			}
			else {
				$msg = Sobi::Txt( 'MSG.OBJ_SAVED', array( 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ) );
				$this->response( Sobi::Url( array( 'task' => $this->_type . '.edit', 'sid' => $sid ) ), $msg, false, 'success', array( 'sets' => $sets ) );
			}
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'MSG.OBJ_SAVED', array( 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ) ) );
		}
	}

	/**
	 */
	protected function approval( $approve )
	{
		$sids = SPRequest::arr( 'e_sid', array() );
		if ( !count( $sids ) ) {
			if ( $this->_model->get( 'id' ) ) {
				$sids = array( $this->_model->get( 'id' ) );
			}
			else {
				$sids = array();
			}
		}
		if ( !( count( $sids ) ) ) {
			$this->response( Sobi::Back(), Sobi::Txt( 'CHANGE_NO_ID' ), false, SPC::ERROR_MSG );
		}
		else {
			foreach ( $sids as $sid ) {
				try {
					SPFactory::db()->update( 'spdb_object', array( 'approved' => $approve ? 1 : 0 ), array( 'id' => $sid, 'oType' => 'entry' ) );
					$entry = SPFactory::Entry( $sid );
					if ( $approve ) {
						$entry->approveFields( $approve );
					}
					else {
						SPFactory::cache()->deleteObj( 'entry', $sid );
					}
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
			$this->logChanges( 'approve' );
			SPFactory::cache()->purgeSectionVars();
			$this->response( Sobi::Back(), Sobi::Txt( $approve ? 'EMN.APPROVED' : 'EMN.UNAPPROVED', $entry->get( 'name' ) ), false, SPC::SUCCESS_MSG );
		}
	}

	/**
	 */
	private function reorder()
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$sids = SPRequest::arr( 'ep_sid', array() );
		/* re-order it to the valid ordering */
		$order = array();
		asort( $sids );
		$eLimStart = SPRequest::int( 'eLimStart', 0 );
		$eLimit = Sobi::GetUserState( 'adm.entries.limit', 'elimit', Sobi::Cfg( 'adm_list.entries_limit', 25 ) );
		$LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;

		if ( count( $sids ) ) {
			$c = 0;
			foreach ( $sids as $sid => $pos ) {
				$order[ ++$c ] = $sid;
			}
		}
		$pid = SPRequest::int( 'sid' );
		foreach ( $order as $sid ) {
			try {
				$db->update( 'spdb_relations', array( 'position' => ++$LimStart ), array( 'id' => $sid, 'oType' => 'entry', 'pid' => $pid ) );
				SPFactory::cache()->deleteObj( 'entry', $sid );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		$this->response( Sobi::Back(), Sobi::Txt( 'EMN.REORDERED' ), true, SPC::SUCCESS_MSG );
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
		$current = $this->_model->getPosition( SPRequest::int( 'pid' ) );
		try {
			$db->select( 'position, id', 'spdb_relations', array( 'position' . $eq => $current, 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ) ), $dir, 1 );
			$interchange = $db->loadAssocList();
			if ( $interchange && count( $interchange ) ) {
				$db->update( 'spdb_relations', array( 'position' => $interchange[ 0 ][ 'position' ] ), array( 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ), 'id' => $this->_model->get( 'id' ) ), 1 );
				$db->update( 'spdb_relations', array( 'position' => $current ), array( 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ), 'id' => $interchange[ 0 ][ 'id' ] ), 1 );
			}
			else {
				$current = $up ? $current-- : $current++;
				$db->update( 'spdb_relations', array( 'position' => $current ), array( 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ), 'id' => $this->_model->get( 'id' ) ), 1 );
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		$this->response( Sobi::Back(), Sobi::Txt( 'ENTRY_POSITION_CHANGED' ), true, SPC::SUCCESS_MSG );
	}

	/**
	 */
	private function editForm()
	{
		$sid = SPRequest::int( 'pid' );
		$sid = $sid ? $sid : SPRequest::sid();
		$view = SPFactory::View( 'entry', true );
		$this->checkTranslation();
		/* if adding new */
		if ( !( $this->_model ) ) {
			$this->setModel( SPLoader::loadModel( 'entry' ) );
		}
		$this->_model->formatDatesToEdit();
		$id = $this->_model->get( 'id' );
		if ( !$id ) {
			$this->_model->set( 'state', 1 );
		}
		else {
			$view->assign( $view->languages(), 'languages-list' );
		}
		$this->_model->loadFields( Sobi::Reg( 'current_section' ), true );
		$this->_model->formatDatesToEdit();

		if ( $this->_model->isCheckedOut() ) {
			SPFactory::message()->error( Sobi::Txt( 'EN.IS_CHECKED_OUT', $this->_model->get( 'name' ) ), false );
		}
		else {
			/* check out the model */
			$this->_model->checkOut();
		}
		/* get fields for this section */
		/* @var SPEntry $this->_model */
		$fields = $this->_model->get( 'fields' );
		if ( !count( $fields ) ) {
			throw new SPException( SPLang::e( 'CANNOT_GET_FIELDS_IN_SECTION', Sobi::Reg( 'current_section' ) ) );
		}
		$revisionChange = false;
		$rev = SPRequest::cmd( 'revision' );
		$revisionsDelta = array();
		if ( $rev ) {
			$revision = SPFactory::message()->getRevision( SPRequest::cmd( 'revision' ) );
			if ( isset( $revision[ 'changes' ] ) && count( $revision[ 'changes' ] ) ) {
				SPFactory::message()->warning( Sobi::Txt( 'HISTORY_REVISION_WARNING', $revision[ 'changedAt' ] ), false );
				foreach ( $fields as $i => $field ) {
					if ( ( $field->get( 'enabled' ) ) && $field->enabled( 'form' ) ) {
						if ( isset( $revision[ 'changes' ][ 'fields' ][ $field->get( 'nid' ) ] ) ) {
							$revisionData = $revision[ 'changes' ][ 'fields' ][ $field->get( 'nid' ) ];
						}
						else {
							$revisionData = null;
						}
						$currentData = $field->getRaw();
						if ( is_array( $revisionData ) && !( is_array( $currentData ) ) ) {
							try {
								$currentData = SPConfig::unserialize( $currentData );
							} catch ( SPException $x ) {

							}
						}
						if ( $revisionData || $currentData ) {
							if ( md5( serialize( $currentData ) ) != md5( serialize( $revisionData ) ) ) {
								$field->revisionChanged()
										->setRawData( $revisionData );
							}
						}
						$fields[ $i ] = $field;
					}
				}
				unset( $revision[ 'changes' ][ 'fields' ] );
				foreach ( $revision[ 'changes' ] as $attr => $value ) {
					if ( $value != $this->_model->get( $attr ) ) {
						$revisionsDelta[ $attr ] = $value;
						$this->_model->setRevData( $attr, $value );
					}
				}
				$revisionChange = true;
			}
			else {
				SPFactory::message()
						->error( Sobi::Txt( 'HISTORY_REVISION_NOT_FOUND' ), false )
						->setSystemMessage();
			}
		}
		$f = array();
		foreach ( $fields as $field ) {
			if ( ( $field->get( 'enabled' ) ) && $field->enabled( 'form' ) ) {
				$f[ ] = $field;
			}
		}
		/* create the validation script to check if required fields are filled in and the filters, if any, match */
		$this->createValidationScript( $fields );
		$view->assign( $this->_model, 'entry' );

		/* get the categories */
		$cats = $this->_model->getCategories( true );
		if ( count( $cats ) ) {
			$tCats = array();
			foreach ( $cats as $cid ) {
				/* ROTFL ... damn I like arrays ;-) */
				$tCats2 = SPFactory::config()->getParentPath( $cid, true );
				if ( is_array( $tCats2 ) && count( $tCats2 ) ) {
					$tCats[ ] = implode( Sobi::Cfg( 'string.path_separator' ), $tCats2 );
				}
			}
			if ( count( $tCats ) ) {
				$view->assign( implode( "\n", $tCats ), 'parent_path' );
			}
			$view->assign( implode( ", ", $cats ), 'parents' );
		}
		elseif ( $this->_model->get( 'valid' ) ) {
			$parent = ( ( $sid == Sobi::Reg( 'current_section' ) ) ? 0 : $sid );
			if ( $parent ) {
				$view->assign( implode( Sobi::Cfg( 'string.path_separator', ' > ' ), SPFactory::config()->getParentPath( $parent, true ) ), 'parent_path' );
			}
			$view->assign( $parent, 'parents' );
		}
		else {
			$n = null;
			$view->assign( $n, 'parents' );
			$view->assign( $n, 'parent_path' );
		}

		$history = array();
		$messages = SPFactory::message()->getHistory( $id );
		if ( count( $messages ) ) {
			foreach ( $messages as $message ) {
				$message[ 'change' ] = Sobi::Txt( 'HISTORY_CHANGE_TYPE_' . str_replace( '-', '_', strtoupper( $message[ 'change' ] ) ) );
				$message[ 'site' ] = Sobi::Txt( 'HISTORY_CHANGE_AREA_' . strtoupper( $message[ 'site' ] ) );
				if ( strlen( $message[ 'reason' ] ) ) {
					$message[ 'status' ] = 1;
				}
				else {
					$message[ 'status' ] = 0;
				}
				$history[ ] = $message;
			}
		}
		$view->assign( $this->_task, 'task' )
				->assign( $f, 'fields' )
				->assign( $id, 'id' )
				->assign( $history, 'history' )
				->assign( $revisionChange, 'revision-change' )
				->assign( $revisionsDelta, 'revision' )
				->assign( SPFactory::CmsHelper()->userSelect( 'entry.owner', ( $this->_model->get( 'owner' ) ? $this->_model->get( 'owner' ) : ( $this->_model->get( 'id' ) ? 0 : Sobi::My( 'id' ) ) ), true ), 'owner' )
				->assign( Sobi::Reg( 'current_section' ), 'sid' )
				->determineTemplate( 'entry', 'edit' )
				->addHidden( $rev, 'revision' )
				->addHidden( $sid, 'pid' );
		$view->display();
	}
}
