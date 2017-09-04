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

use Sobi\Input\Input;
use Sobi\FileSystem\FileSystem;

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
				$this->authorise( 'approve' );
				$this->approval( $this->_task == 'approve' );
				break;
			case 'up':
			case 'down':
				$this->authorise( 'edit' );
				$r = true;
				$this->singleReorder( $this->_task == 'up' );
				break;
			case 'clone':
				$this->authorise( 'edit' );
				$r = true;
				$this->_model = null;
				Input::Set( 'entry_id', 0, 'post' );
				Input::Set( 'entry_state', 0, 'post' );
				$this->save( false, true );
				break;
			case 'saveWithRevision':
				$this->authorise( 'edit' );
				$this->save( true );
				break;
			case 'reorder':
				$this->authorise( 'edit' );
				$r = true;
				$this->reorder();
				break;
			case 'reject':
				$this->authorise( 'approve' );
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
			case 'deleteAll':
				$this->deleteAll();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !parent::execute() ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', Input::Task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
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
		$revision = SPFactory::message()->getRevision( Input::Cmd( 'revision' ) );
		$sid = Input::Sid();
		$fid = Input::Cmd( 'fid' );
		if ( strstr( $fid, 'field_' ) ) {
			$fid = SPFactory::db()
					->select( 'fid', 'spdb_field', [ 'nid' => $fid, 'section' => Sobi::Section(), 'adminField>' => -1 ] )
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
				$data = [
						'current' => $current,
						'revision' => $revision
				];
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
					$data = [
							'current' => $currentUser,
							'revision' => $pastUser,
					];
					break;
				default:
					$data = [
							'current' => $this->_model->get( $i ),
							'revision' => $revision
					];
					break;
			}
		}
		if ( !( Input::Bool( 'html', 'post', false ) ) ) {
			$data = [
					'current' => html_entity_decode( strip_tags( $data[ 'current' ] ), ENT_QUOTES, 'UTF-8' ),
					'revision' => html_entity_decode( strip_tags( $data[ 'revision' ] ), ENT_QUOTES, 'UTF-8' ),
			];

		}
		$data = [
				'current' => explode( "\n", $data[ 'current' ] ),
				'revision' => explode( "\n", $data[ 'revision' ] )
		];
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
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::Task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		if ( $this->authorise( 'manage' ) ) {
			$changes = [];
			$objects = [
					'entry' => $this->_model,
					'user' => SPFactory::user(),
					'author' => SPFactory::Instance( 'cms.base.user', $this->_model->get( 'owner' ) )
			];
			$messages =& SPFactory::registry()->get( 'messages' );
			$reason = SPLang::replacePlaceHolders( Input::Html( 'reason', 'post', null ), $objects );
			$objects[ 'reason' ] = nl2br( $reason );
			$messages[ 'rejection' ] = $objects;
			SPFactory::registry()->set( 'messages', $messages );
			$this->_model->setMessage( $reason, 'reason' );
			SPFactory::message()->logAction( 'reject', $this->_model->get( 'id' ), [], $reason );

			if ( Input::Bool( 'unpublish', 'post', false ) ) {
				$this->_model->changeState( 0, $reason, false );
				$changes[] = 'unpublish';
				SPFactory::message()->logAction( 'unpublished', $this->_model->get( 'id' ), [], Sobi::Txt( 'EN.REJECT_HISTORY' ) );
			}
			if ( Input::Bool( 'trigger_unpublish', 'post', false ) ) {
				Sobi::Trigger( 'Entry', 'AfterChangeState', [ $this->_model, 0, 'messages' => $this->_model->get( 'messages' ) ] );
			}
			if ( Input::Bool( 'discard', 'post', false ) ) {
				$changes[] = 'discard';
				$data = $this->_model->discard( false );
				SPFactory::message()->logAction( 'discard', $this->_model->get( 'id' ), $data, Sobi::Txt( 'EN.REJECT_HISTORY' ) );
			}
			if ( Input::Bool( 'trigger_unapprove', 'post', false ) ) {
				Sobi::Trigger( 'Entry', 'AfterUnapprove', [ $this->_model, 0 ] );
			}
			Sobi::Trigger( 'Entry', 'AfterReject', [ $this->_model, 0 ] );
			$this->response( Sobi::Back(), Sobi::Txt( 'ENTRY_REJECTED', $this->_model->get( 'name' ) ), true, SPC::SUCCESS_MSG );
		}
	}

	protected function search()
	{
		$term = Input::String( 'search', 'post', null );
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
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::Task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$sets = [];
		if ( !( $clone ) ) {
			$sid = Input::Sid() ? Input::Sid() : Input::Int( 'entry_id' );
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

		$preState = [
				'approved' => $this->_model->get( 'approved' ),
				'state' => $this->_model->get( 'state' ),
				'new' => !( $this->_model->get( 'id' ) )
		];
		SPFactory::registry()->set( 'object_previous_state', $preState );

		$this->_model->getRequest( $this->_type );
		$this->authorise( $this->_model->get( 'id' ) ? 'edit' : 'add' );

		try {
			$this->_model->validate( 'post' );
		} catch ( SPException $x ) {
			$back = Sobi::GetUserState( 'back_url', Sobi::Url( [ 'task' => 'entry.add', 'sid' => Sobi::Section() ] ) );
			$data = $x->getData();
			$this->response( $back, $x->getMessage(), false, 'error', [ 'required' => $data[ 'field' ] ] );
		}

		try {
			$this->_model->save();
		} catch ( SPException $x ) {
			$back = Sobi::GetUserState( 'back_url', Sobi::Url( [ 'task' => 'entry.add', 'sid' => Sobi::Section() ] ) );
			$this->response( $back, $x->getMessage(), false, 'error' );
		}
		$sid = $this->_model->get( 'id' );
		$sets[ 'sid' ] = $sid;
		$sets[ 'entry.nid' ] = $this->_model->get( 'nid' );
		$sets[ 'entry.id' ] = $sid;

		if ( $customClass && method_exists( $customClass, 'AfterStoreEntry' ) ) {
			$customClass::AfterStoreEntry( $this->_model );
		}
		if ( Input::String( 'history-note' ) || $this->_task == 'saveWithRevision' || Sobi::Cfg( 'entry.versioningAdminBehaviour', 1 ) ) {
			$this->logChanges( 'save', Input::String( 'history-note' ) );
		}
		if ( $apply || $clone ) {
			if ( $clone ) {
				$msg = Sobi::Txt( 'MSG.OBJ_CLONED', [ 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ] );
				$this->response( Sobi::Url( [ 'task' => $this->_type . '.edit', 'sid' => $sid ] ), $msg );
			}
			else {
				$msg = Sobi::Txt( 'MSG.OBJ_SAVED', [ 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ] );
				$this->response( Sobi::Url( [ 'task' => $this->_type . '.edit', 'sid' => $sid ] ), $msg, false, 'success', [ 'sets' => $sets ] );
			}
		}
		elseif ( $this->_task == 'saveAndNew' ) {
			$msg = Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' );
			$sid = $this->_model->get( 'parent' );
			if ( !( $sid ) ) {
				$sid = Sobi::Section();
			}
			$this->response( Sobi::Url( [ 'task' => $this->_type . '.add', 'sid' => $sid ] ), $msg, true, 'success', [ 'sets' => $sets ] );

		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'MSG.OBJ_SAVED', [ 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ] ) );
		}
	}

	protected function deleteAll()
	{
		SPFactory::mainframe()->checkToken();
		$count = Input::Int( 'counter' );
		$eid = Input::Eid();
		if ( !( $eid ) ) {
			if ( !( $count ) ) {
				$entries = SPFactory::Section( Sobi::Section() )
						->getChilds( 'entry', true, 0, false );
				$count = count( $entries );
				$store = json_encode( $entries );
				// storing it in JSON file so the Ajax needs to only deliver an index and not hold the complete array.
				FileSystem::Write( SOBI_PATH . '/var/tmp/' . Input::Cmd( 'spsid' ) . '.json', $store );
			}
			else {
				$entries = json_decode( FileSystem::Read( SOBI_PATH . '/var/tmp/' . Input::Cmd( 'spsid' ) . '.json' ) );
			}
			$this->response( Sobi::Back(), Sobi::Txt( 'DELETE_ENTRIES_COUNT', $count ), false, SPC::SUCCESS_MSG, [ 'counter' => $count, 'entries' => $entries ] );
		}
		$entry = SPFactory::Entry( $eid );
		$entry->delete();
		SPFactory::cache()->purgeSectionVars();
		$this->response( Sobi::Back(), Sobi::Txt( 'DELETED_ENTRY', $entry->get( 'name' ) ), false, SPC::SUCCESS_MSG );
	}

	/**
	 * @param $approve
	 */
	protected function approval( $approve )
	{
		$sids = Input::Arr( 'e_sid', 'request', [] );
		if ( !count( $sids ) ) {
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
					SPFactory::db()->update( 'spdb_object', [ 'approved' => $approve ? 1 : 0 ], [ 'id' => $sid, 'oType' => 'entry' ] );
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
			$log_message = $approve ? 'approved' : 'unapproved';
			$this->logChanges( $log_message );
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
		$sids = Input::Arr( 'ep_sid', 'request', [] );
		/* re-order it to the valid ordering */
		$order = [];
		asort( $sids );
		$eLimStart = Input::Int( 'eLimStart', 'request', 0 );
		$eLimit = Sobi::GetUserState( 'adm.entries.limit', 'elimit', Sobi::Cfg( 'adm_list.entries_limit', 25 ) );
		$LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;

		if ( count( $sids ) ) {
			$c = 0;
			foreach ( $sids as $sid => $pos ) {
				$order[ ++$c ] = $sid;
			}
		}
		$pid = Input::Sid();
		foreach ( $order as $sid ) {
			try {
				$db->update( 'spdb_relations', [ 'position' => ++$LimStart ], [ 'id' => $sid, 'oType' => 'entry', 'pid' => $pid ] );
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
		$current = $this->_model->getPosition( Input::Pid() );
		try {
			$db->select( 'position, id', 'spdb_relations', [ 'position' . $eq => $current, 'oType' => 'entry', 'pid' => Input::Pid() ], $dir, 1 );
			$interchange = $db->loadAssocList();
			if ( $interchange && count( $interchange ) ) {
				$db->update( 'spdb_relations', [ 'position' => $interchange[ 0 ][ 'position' ] ], [ 'oType' => 'entry', 'pid' => Input::Pid(), 'id' => $this->_model->get( 'id' ) ], 1 );
				$db->update( 'spdb_relations', [ 'position' => $current ], [ 'oType' => 'entry', 'pid' => Input::Pid(), 'id' => $interchange[ 0 ][ 'id' ] ], 1 );
			}
			else {
				$current = $up ? $current-- : $current++;
				$db->update( 'spdb_relations', [ 'position' => $current ], [ 'oType' => 'entry', 'pid' => Input::Pid(), 'id' => $this->_model->get( 'id' ) ], 1 );
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
		$sid = Input::Pid();
		$sid = $sid ? $sid : Input::Sid();
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
			$this->_model->set( 'approved', 1 );
		}
		else {
			$languages = $view->languages();
			$view->assign( $languages, 'languages-list' );
		}
		$this->_model->loadFields( Sobi::Section(), true );
		$this->_model->formatDatesToEdit();

		if ( $this->_model->isCheckedOut() ) {
			SPFactory::message()->error( Sobi::Txt( 'EN.IS_CHECKED_OUT', $this->_model->get( 'name' ) ), false );
		}
		else {
			/* check out the model */
			$this->_model->checkOut();
		}
		/* get fields for this section */
		/* @var SPEntry $this ->_model */
		$fields = $this->_model->get( 'fields' );
		if ( !count( $fields ) ) {
			throw new SPException( SPLang::e( 'CANNOT_GET_FIELDS_IN_SECTION', Sobi::Reg( 'current_section' ) ) );
		}
		$revisionChange = false;
		$rev = Input::Cmd( 'revision' );
		$revisionsDelta = [];
		if ( $rev ) {
			$revision = SPFactory::message()->getRevision( Input::Cmd( 'revision' ) );
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
		$f = [];
		foreach ( $fields as $field ) {
			if ( ( $field->get( 'enabled' ) ) && $field->enabled( 'form' ) ) {
				$f[] = $field;
			}
		}
		/* create the validation script to check if required fields are filled in and the filters, if any, match */
		$this->createValidationScript( $fields );
		$view->assign( $this->_model, 'entry' );

		$history = [];
		$messages = SPFactory::message()->getHistory( $id );
		if ( count( $messages ) ) {
			foreach ( $messages as $message ) {
				$message[ 'changeAction' ] = Sobi::Txt( 'HISTORY_CHANGE_TYPE_' . str_replace( '-', '_', strtoupper( $message[ 'changeAction' ] ) ) );
				$message[ 'site' ] = Sobi::Txt( 'HISTORY_CHANGE_AREA_' . strtoupper( $message[ 'site' ] ) );
				if ( strlen( $message[ 'reason' ] ) ) {
					$message[ 'status' ] = 1;
				}
				else {
					$message[ 'status' ] = 0;
				}
				$history[] = $message;
			}
		}
		$versioningAdminBehaviour = Sobi::Cfg( 'entry.versioningAdminBehaviour', 1 );
		if ( $versioningAdminBehaviour || !( Sobi::Cfg( 'entry.versioning', true ) ) ) {
			SPFactory::header()->addJsCode( '
				SobiPro.jQuery( document ).ready( function () { SobiPro.jQuery( "[rel=\'entry.saveWithRevision\']" ).parent().css( "display", "none" ); } );
			' );
		}
		$reg = Sobi::Reg( 'current_section' );
		$owner = SPFactory::CmsHelper()->userSelect( 'entry.owner', ( $this->_model->get( 'owner' ) ? $this->_model->get( 'owner' ) : ( $this->_model->get( 'id' ) ? 0 : Sobi::My( 'id' ) ) ), true );
		$view->assign( $this->_task, 'task' )
				->assign( $f, 'fields' )
				->assign( $id, 'id' )
				->assign( $history, 'history' )
				->assign( $revisionChange, 'revision-change' )
				->assign( $revisionsDelta, 'revision' )
				->assign( $versioningAdminBehaviour, 'history-behaviour' )
				->assign( $owner, 'owner' )
				->assign( $reg, 'sid' )
				->determineTemplate( 'entry', 'edit' )
				->addHidden( $rev, 'revision' )
				->addHidden( $sid, 'pid' );
		$view->display();
	}
}
