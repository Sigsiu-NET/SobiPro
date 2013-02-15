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
/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:08:12 PM
 */
class SPEntryCtrl extends SPController
{
	/**
	 * @var string
	 */
	protected $_type = 'entry';
	/**
	 * @var string
	 */
	protected $_defTask = 'details';

	/**
	 */
	public function execute()
	{
		$r = false;
		SPRequest::set( 'task', $this->_type . '.' . $this->_task );
		switch ( $this->_task ) {
			case 'edit':
			case 'add':
				Sobi::ReturnPoint();
				SPLoader::loadClass( 'html.input' );
				$this->editForm();
				break;
			case 'approve':
			case 'unapprove':
				$r = true;
				$this->approve( $this->_task == 'approve' );
				break;
			case 'publish':
			case 'unpublish':
			case 'hide':
				$r = true;
				$this->state( $this->_task == 'publish' );
				break;
			case 'submit':
				$this->submit();
				break;
			case 'details':
				$this->visible();
				$this->details();
				Sobi::ReturnPoint();
				break;
			case 'payment':
				$this->payment();
				break;
			default:
				if ( !parent::execute() ) {
					Sobi::Error( 'entry_ctrl', SPLang::e( 'TASK_NOT_FOUND' ), SPC::NOTICE, 404, __LINE__, $this->name() );
				}
				else {
					$r = true;
				}
				break;
		}
		return $r;
	}

	/**
	 */
	private function approve()
	{
		if ( $this->_model->isCheckedOut() ) {
			Sobi::Redirect( Sobi::Back(), Sobi::Txt( 'EN.IS_CHECKED_OUT' ), SPC::ERROR_MSG, true );
		}
		if ( ( ( $this->_model->get( 'owner' ) == Sobi::My( 'id' ) ) && Sobi::Can( 'entry.manage.own' ) ) || Sobi::Can( 'entry.manage.*' ) ) {
			try {
				SPFactory::db()->update( 'spdb_object', array( 'approved' => 1 ), array( 'id' => $this->_model->get( 'id' ), 'oType' => 'entry' ) );
				$this->_model->approveFields( true );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			Sobi::Trigger( $this->name(), __FUNCTION__, array( &$this->_model ) );
			Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url( array( 'sid' => $this->_model->get( 'id' ) ) ) ), Sobi::Txt( 'EN.APPROVED' ) );
		}
		else {
			Sobi::Error( 'entry', SPLang::e( 'UNAUTHORIZED_ACCESS' ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
	}

	/**
	 * @param int $sid
	 * @param bool $redirect
	 * @return void
	 */
	protected function checkIn( $sid, $redirect = true )
	{
		parent::checkIn( SPRequest::int( 'entry_id' ), $redirect );
	}

	/**
	 */
	protected function state( $state )
	{
		if ( $this->_model->get( 'id' ) ) {
			if ( $this->_model->isCheckedOut() ) {
				$this->response( Sobi::Back(), Sobi::Txt( 'EN.IS_CHECKED_OUT', $this->_model->get( 'name' ) ), false, SPC::WARN_MSG );
			}
			if ( ( ( $this->_model->get( 'owner' ) == Sobi::My( 'id' ) ) && Sobi::Can( 'entry.publish.own' ) ) || Sobi::Can( 'entry.publish.*' ) ) {
				$this->_model->changeState( $state );
//				if ( time() % 2 ) {
//					SPFactory::message()->setReport( 'Send an email to someone for it', SPRequest::cmd( 'spsid' ) );
//					$this->response( Sobi::Back(), Sobi::Txt( 'EN.IS_CHECKED_OUT', $this->_model->get( 'name' ) ), false, SPC::WARN_MSG );
//				}
//				elseif ( time() % 3 ) {
//					SPFactory::message()->setReport( 'here is an error', SPRequest::cmd( 'spsid' ), SPC::WARN_MSG );
//					$this->response( Sobi::Back(), Sobi::Txt( 'CHANGE_NO_ID' ), false, SPC::ERROR_MSG );
//				}
//				else {
//					SPFactory::message()->setReport( 'I sent this email', SPRequest::cmd( 'spsid' ), SPC::SUCCESS_MSG );
				$this->response( Sobi::Back(), Sobi::Txt( $state ? 'EN.PUBLISHED' : 'EN.UNPUBLISHED', $this->_model->get( 'name' ) ), false, SPC::SUCCESS_MSG );
//				}
			}
			else {
				Sobi::Error( 'entry', SPLang::e( 'UNAUTHORIZED_ACCESS' ), SPC::ERROR, 403, __LINE__, __FILE__ );
			}
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'CHANGE_NO_ID' ), false, SPC::ERROR_MSG );
		}
	}

	/**
	 * pre-save an entry
	 */
	protected function submit()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		if ( !$this->_model ) {
			$this->setModel( SPLoader::loadModel( $this->_type ) );
		}
		else {
			if ( $this->_model->get( 'oType' ) != 'entry' ) {
				Sobi::Error( 'Entry', sprintf( 'Serious security violation. Trying to save an object which claims to be an entry but it is a %s. Task was %s', $this->_model->get( 'oType' ), SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
				exit;
			}
		}
		$sid = $this->_model->get( 'id' );
		$this->_model->init( SPRequest::sid() );
		$this->_model->getRequest( $this->_type );
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$this->_model ) );
		if ( $sid ) {
			if ( Sobi::My( 'id' ) && Sobi::My( 'id' ) == $this->_model->get( 'owner' ) ) {
				$this->authorise( 'edit', 'own' );
			}
			else {
				$this->authorise( 'edit', '*' );
			}
		}
		else {
			$this->authorise( 'add', 'own' );
		}

		if ( Sobi::Cfg( 'legacy.sigsiutree', false ) && !( SPRequest::int( 'entry_parent', 0 ) ) ) {
			$this->response( Sobi::Back(), Sobi::Txt( 'CAT.SELECT_ONE' ), true, SPC::ERROR_MSG );
		}
		$this->_model->loadFields( Sobi::Reg( 'current_section' ) );
		$fields = $this->_model->get( 'fields' );
		$tsId = SPRequest::string( 'editentry', null, false, 'cookie' );

		if ( !strlen( $tsId ) ) {
			/** Cannot write to file \tmp\edit\2011-04-27_05-04-00_::1\post.var
			 * ^^^^ how the hell it's possible to have IP like ::1 ???!!! ^^^ */
			$tsId = date( 'Y-m-d_H-m-s_' ) . str_replace( array( '.', ':' ), array( '-', null ), SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ) );
			SPLoader::loadClass( 'env.cookie' );
			SPCookie::set( 'editentry', $tsId, SPCookie::hours( 2 ) );
		}
		$store = array();
		if ( count( $fields ) ) {
			foreach ( $fields as $field ) {
				$field->enabled( 'form' );
				try {
					$request = $field->submit( $this->_model, $tsId );
					if ( is_array( $request ) && count( $request ) ) {
						$store = array_merge( $store, $request );
					}
				} catch ( SPException $x ) {
					$this->response( Sobi::Back(), $x->getMessage(), false, SPC::ERROR_MSG, array( 'error' => $field->get( 'nid' ) ) );
				}
			}
		}
		/* try in Sobi Cache first */
		if ( Sobi::Cfg( 'cache.l3_enabled', true ) ) {
			SPFactory::cache()->addVar( array( 'post' => $_POST, 'files' => $_FILES, 'store' => $store ), 'request_cache_' . $tsId );
		}
		else {
			SPFs::write( SPLoader::path( 'tmp.edit.' . $tsId . '.post', 'front', false, 'var' ), SPConfig::serialize( $_POST ) );
			SPFs::write( SPLoader::path( 'tmp.edit.' . $tsId . '.files', 'front', false, 'var' ), SPConfig::serialize( $_FILES ) );
			SPFs::write( SPLoader::path( 'tmp.edit.' . $tsId . '.store', 'front', false, 'var' ), SPConfig::serialize( $store ) );

		}

		if ( !( Sobi::Can( 'entry.payment.free' ) ) && SPFactory::payment()->count( $this->_model->get( 'id' ) ) ) {
			$this->paymentView( $tsId );
		}
		else {
			$this->response( Sobi::Url( array( 'task' => 'entry.save', 'pid' => Sobi::Reg( 'current_section' ), 'sid' => $sid ) ) );
		}
	}

	private function getCache( $tsId, $cache = 'requestcache' )
	{
		$store = SPFactory::cache()->getVar( 'request_cache_' . $tsId );
		/* try from Sobi Cache first */
		if ( $store && isset( $store[ 'post' ] ) && isset( $store[ 'store' ] ) && isset( $store[ 'files' ] ) ) {
			$post = $store[ 'post' ];
			$files = $store[ 'files' ];
			$store = $store[ 'store' ];
			if ( is_array( $files ) ) {
				$post = array_merge( $post, $files );
			}
			SPFactory::registry()->set( $cache, $post );
			SPFactory::registry()->set( 'requestcache_stored', $store );
			$request = $cache;
		}
		else {
			$tempDir = SPLoader::dirPath( 'tmp.edit.' . $tsId );
			if ( strlen( $tsId ) && $tempDir ) {
				$tempFile = SPLoader::path( 'tmp.edit.' . $tsId . '.post', 'front', true, 'var' );
				$filesFile = SPLoader::path( 'tmp.edit.' . $tsId . '.files', 'front', true, 'var' );
				$storeFile = SPLoader::path( 'tmp.edit.' . $tsId . '.store', 'front', true, 'var' );
				$post = SPConfig::unserialize( SPFs::read( $tempFile ) );
				$files = SPConfig::unserialize( SPFs::read( $filesFile ) );
				$store = SPConfig::unserialize( SPFs::read( $storeFile ) );
				if ( is_array( $files ) ) {
					$post = array_merge( $post, $files );
				}
				SPFactory::registry()->set( $cache, $post );
				SPFactory::registry()->set( 'requestcache_stored', $store );
				$request = $cache;
			}
			else {
				$request = 'post';
			}
		}
		return $request;
	}

	private function payment()
	{
		$sid = SPRequest::sid();
		$data = SPFactory::cache()->getObj( 'payment', $sid, Sobi::Section(), true );
		if ( !( $data ) ) {
			$tsid = SPRequest::base64( 'tsid' );
			$tfile = SPLoader::path( 'tmp.edit.' . $tsid . '.payment', 'front', false, 'var' );
			if ( SPFs::exists( $tfile ) ) {
				$data = SPConfig::unserialize( SPFs::read( $tfile ) );
			}
		}
		if ( !( $data ) ) {
			Sobi::Error( 'payment', SPLang::e( 'Session expired' ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		/*
		   * It doesn't make sense because this has been already checked for the edit function. Right?
		   * Or do I miss something?
		   */
		//		if( Sobi::My( 'id' ) && Sobi::My( 'id' ) == $this->_model->get( 'owner' ) ) {
		//			$this->authorise( 'edit', 'own' );
		//		}
		//		else {
		//			$this->authorise( 'edit', '*' );
		//		}
		if ( ( $data[ 'ident' ] != SPRequest::string( 'payment_' . $sid, null, false, 'cookie' ) ) ) {
			Sobi::Error( 'payment', SPLang::e( 'UNAUTHORIZED_ACCESS' ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$this->paymentView( null, $data[ 'data' ] );
	}

	private function paymentView( $tsId = null, $data = null )
	{
		/* determine template package */
		$tplPackage = Sobi::Cfg( 'section.template', 'default2' );
		/* load template config */
		$this->tplCfg( $tplPackage );
		SPFactory::mainframe()->addObjToPathway( $this->_model );
		$view = SPFactory::View( 'payment', $this->template );
		$view->assign( $this->_model, 'entry' );
		$view->assign( $data, 'pdata' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->assign( $this->_task, 'task' );
		$view->addHidden( $tsId, 'speditentry' );
		$view->setConfig( $this->_tCfg, $this->_task );
		$view->setTemplate( $tplPackage . '.payment.' . $this->_task );
		Sobi::Trigger( ucfirst( $this->_task ), $this->name(), array( &$view, &$this->_model ) );
		if ( SPRequest::cmd( 'method', null, 'post' ) == 'xhr' ) {
			$view->display();
			$response = ob_get_contents();
			$this->response( Sobi::Back(), $response, false, SPC::INFO_MSG );
		}
		else {
			$view->display();
		}
	}

	/**
	 * Save an entry
	 *
	 * @param bool $apply
	 */
	protected function save( $apply )
	{
		$new = true;
		if ( !$this->_model ) {
			$this->setModel( SPLoader::loadModel( $this->_type ) );
		}
		if ( $this->_model->get( 'oType' ) != 'entry' ) {
			Sobi::Error( 'Entry', sprintf( 'Serious security violation. Trying to save an object which claims to be an entry but it is a %s. Task was %s', $this->_model->get( 'oType' ), SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
			exit;
		}

		/* check if we have stored last edit in cache */
		$tsId = SPRequest::string( 'editentry', null, false, 'cookie' );
		$request = $this->getCache( $tsId );
		$this->_model->init( SPRequest::sid( $request ) );
		$this->_model->getRequest( $this->_type, $request );
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$this->_model ) );

		if ( $this->_model->get( 'id' ) && $this->_model->get( 'id' ) == SPRequest::sid() ) {
			$new = false;
			if ( Sobi::My( 'id' ) && Sobi::My( 'id' ) == $this->_model->get( 'owner' ) ) {
				$this->authorise( 'edit', 'own' );
			}
			else {
				$this->authorise( 'edit', '*' );
			}
		}
		else {
			$this->authorise( 'add', 'own' );
		}
		$this->_model->save( $request );

		/* if there is something pay */
		$pCount = SPFactory::payment()->count( $this->_model->get( 'id' ) );
		if ( $pCount && !( Sobi::Can( 'entry.payment.free' ) ) ) {
//			$this->paymentView( $tsid );
			SPFactory::payment()->store( $this->_model->get( 'id' ) );
		}
		/* delete cache files on after */
		if ( SPLoader::dirPath( 'tmp.edit.' . $tsId ) ) {
			SPFs::delete( SPLoader::dirPath( 'tmp.edit.' . $tsId ) );
		}
		else {
			SPFactory::cache()->deleteVar( 'request_cache_' . $tsId );
		}
		SPLoader::loadClass( 'env.cookie' );
		SPCookie::delete( $tsId );

		$sid = $this->_model->get( 'id' );
		$pid = SPRequest::int( 'pid' ) ? SPRequest::int( 'pid' ) : Sobi::Section();
		if ( $new ) {
			if ( $this->_model->get( 'state' ) || Sobi::Can( 'entry.see_unpublished.own' ) ) {
				$msg = $this->_model->get( 'state' ) ? Sobi::Txt( 'EN.ENTRY_SAVED' ) : Sobi::Txt( 'EN.ENTRY_SAVED_NP' );
				$url = Sobi::Url( array( 'sid' => $sid, 'pid' => $pid ) );
			}
			else {
				// determine if there is a custom redirect
				if ( Sobi::Cfg( 'redirects.entry_save_enabled' ) && !( $pCount && !( Sobi::Can( 'entry.payment.free' ) ) ) ) {
					$redirect = Sobi::Cfg( 'redirects.entry_save_url', null );
					if ( !( preg_match( '/http[s]?:\/\/.*/', $redirect ) ) && $redirect != 'index.php' ) {
						$redirect = Sobi::Url( $redirect );
					}
					$this->response( $redirect, Sobi::Txt( Sobi::Cfg( 'redirects.entry_save_msg', 'EN.ENTRY_SAVED_NP' ) ), true, Sobi::Cfg( 'redirects.entry_save_msgtype', SPC::SUCCESS_MSG ) );
				}
				else {
					$msg = Sobi::Txt( 'EN.ENTRY_SAVED_NP' );
					$url = Sobi::Url( array( 'sid' => $pid ) );
				}
			}
		}
		/* I know, it could be in one statement but it is more readable like this */
		elseif ( $this->_model->get( 'approved' ) || Sobi::Can( 'entry.see_unapproved.own' ) ) {
			$url = Sobi::Url( array( 'sid' => $sid, 'pid' => $pid ) );
			$msg = $this->_model->get( 'approved' ) ? Sobi::Txt( 'EN.ENTRY_SAVED' ) : Sobi::Txt( 'EN.ENTRY_SAVED_NA' );
		}
		else {
			if ( $this->_model->get( 'approved' ) ) {
				$msg = Sobi::Txt( 'EN.ENTRY_SAVED' );
			}
			else {
				$msg = Sobi::Txt( 'EN.ENTRY_SAVED_NA' );
			}
			$url = Sobi::Url( array( 'sid' => $sid, 'pid' => $pid ) );
		}
		if ( $pCount && !( Sobi::Can( 'entry.payment.free' ) ) ) {
			$ident = md5( microtime() . $tsId . $sid . time() );
			$data = array( 'data' => SPFactory::payment()->summary( $sid ), 'ident' => $ident );
			$url = Sobi::Url( array( 'sid' => $sid, 'task' => 'entry.payment' ), false, false );
			if ( Sobi::Cfg( 'cache.l3_enabled', true ) ) {
				SPFactory::cache()->addObj( $data, 'payment', $sid, Sobi::Section(), true );
			}
			else {
				SPFs::write( SPLoader::path( 'tmp.edit.' . $ident . '.payment', 'front', false, 'var' ), SPConfig::serialize( $data ) );
				$url = Sobi::Url( array( 'sid' => $sid, 'task' => 'entry.payment', 'tsid' => $ident ), false, false );
			}
			SPLoader::loadClass( 'env.cookie' );
			SPCookie::set( 'payment_' . $sid, $ident, SPCookie::days( 1 ) );
		}
		$this->response( $url, $msg, true, SPC::SUCCESS_MSG );
	}


	/**
	 * authorise action
	 * @param string $action
	 * @param string $ownership
	 * @return bool
	 */
	protected function authorise( $action = 'access', $ownership = 'valid' )
	{
		if ( !( Sobi::Can( $this->_type, $action, $ownership, Sobi::Section() ) ) ) {
			switch ( $action ) {
				case 'add':
					if ( Sobi::Cfg( 'redirects.entry_add_enabled', false ) && strlen( Sobi::Cfg( 'redirects.entry_add_url', null ) ) ) {
						$this->escape( Sobi::Cfg( 'redirects.entry_add_url', null ), SPLang::e( Sobi::Cfg( 'redirects.entry_add_msg', 'UNAUTHORIZED_ACCESS' ) ), Sobi::Cfg( 'redirects.entry_add_msgtype', 'message' ) );
					}
					else {
						Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
					}
					break;
			}
		}
		return true;
	}

	/**
	 */
	private function editForm()
	{
		if ( $this->_task != 'add' ) {
			$sid = SPRequest::sid();
			$sid = $sid ? $sid : SPRequest::int( 'pid' );
		}
		else {
			$this->authorise( $this->_task, 'own' );
			$this->_model = null;
			$sid = SPRequest::int( 'pid' );
			$section = SPFactory::Section( Sobi::Section() );
			SPFactory::header()
					->addKeyword( $section->get( 'efMetaKeys' ) )
					->addDescription( $section->get( 'efMetaDesc' ) );
		}

		if ( $this->_model && $this->_model->isCheckedOut() ) {
			Sobi::Redirect( Sobi::Url( array( 'sid' => SPRequest::sid() ) ), Sobi::Txt( 'EN.IS_CHECKED_OUT' ), SPC::ERROR_MSG, true );
		}

		/* determine template package */
		$tplPackage = Sobi::Cfg( 'section.template', 'default2' );

		/* load template config */
		$this->template();
		$this->tplCfg( $tplPackage );

		/* check if we have stored last edit in cache */
		$this->getCache( SPRequest::string( 'editentry', null, false, 'cookie' ), 'editcache' );
		$section = SPFactory::Model( 'section' );
		$section->init( Sobi::Section() );

		if ( $this->_model ) {
			/* handle meta data */
			SPFactory::header()->objMeta( $this->_model );

			/* add pathway */
			SPFactory::mainframe()->addObjToPathway( $this->_model );
		}
		/* if adding new */
		else {
			/* handle meta data */
			SPFactory::header()->objMeta( $section );
			SPFactory::mainframe()->addToPathway( Sobi::Txt( 'EN.ADD_PATH_TITLE' ), Sobi::Url( 'current' ) );
			SPFactory::mainframe()->setTitle( Sobi::Txt( 'EN.ADD_TITLE', array( 'section' => $section->get( 'name' ) ) ) );

			/* add pathway */
			SPFactory::mainframe()->addObjToPathway( $section );
			$this->setModel( SPLoader::loadModel( 'entry' ) );
		}
		$this->_model->formatDatesToEdit();
		$id = $this->_model->get( 'id' );
		if ( !$id ) {
			$this->_model->set( 'state', 1 );
		}

		if ( $this->_task != 'add' && !( $this->authorise( $this->_task, ( $this->_model->get( 'owner' ) == Sobi::My( 'id' ) ) ? 'own' : '*' ) ) ) {
			throw new SPException( SPLang::e( 'YOU_ARE_NOT_AUTH_TO_EDIT_THIS_ENTRY' ) );
		}

		$this->_model->loadFields( Sobi::Reg( 'current_section' ) );

		/* get fields for this section */
		$fields = $this->_model->get( 'fields' );

		if ( !count( $fields ) ) {
			throw new SPException( SPLang::e( 'CANNOT_GET_FIELDS_IN_SECTION', Sobi::Reg( 'current_section' ) ) );
		}

		/* create the validation script to check if required fields are filled in and the filters, if any, match */
		$this->createValidationScript( $fields );

		/* check out the model */
		$this->_model->checkOut();
		$class = SPLoader::loadView( 'entry' );
		$view = new $class( $this->template );
		$view->assign( $this->_model, 'entry' );

		$cache = Sobi::Reg( 'editcache' );
		/* get the categories */
		if ( isset( $cache ) && isset( $cache[ 'entry_parent' ] ) ) {
			$cats = explode( ',', $cache[ 'entry_parent' ] );
		}
		else {
			$cats = $this->_model->getCategories( true );
		}
		if ( count( $cats ) ) {
			$tCats = array();
			foreach ( $cats as $cid ) {
				$tCats2 = SPFactory::config()->getParentPath( ( int )$cid, true );
				if ( is_array( $tCats2 ) && count( $tCats2 ) ) {
					$tCats[ ] = implode( Sobi::Cfg( 'string.path_separator', ' > ' ), $tCats2 );
				}
			}
			if ( count( $tCats ) ) {
				$view->assign( implode( "\n", $tCats ), 'parent_path' );
			}
			$view->assign( implode( ", ", $cats ), 'parents' );
		}
		else {
			$parent = ( ( $sid == Sobi::Reg( 'current_section' ) ) ? 0 : $sid );
			if ( $parent ) {
				$view->assign( implode( Sobi::Cfg( 'string.path_separator', ' > ' ), SPFactory::config()->getParentPath( $parent, true ) ), 'parent_path' );
			}
			$view->assign( $parent, 'parents' );
		}
		$view->assign( $this->_task, 'task' );
		$view->assign( $fields, 'fields' );
		$view->assign( $id, 'id' );
		$view->assign( $id, 'sid' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->setConfig( $this->_tCfg, $this->template );
		$view->setTemplate( $tplPackage . '.' . $this->templateType . '.' . ( $this->template == 'add' ? 'edit' : $this->template ) );
		$view->addHidden( ( $sid ? $sid : SPRequest::sid() ), 'pid' );
		$view->addHidden( $id, 'sid' );
		$view->addHidden( ( SPRequest::int( 'pid' ) && SPRequest::int( 'pid' ) != $id ) ? SPRequest::int( 'pid' ) : Sobi::Section(), 'pid' );
		$view->addHidden( 'entry.submit', SOBI_TASK );
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$view ) );
		$view->display();
	}

	/**
	 * Details view
	 * @return void
	 */
	private function details()
	{
		/* determine template package */
		$tplPackage = Sobi::Cfg( 'section.template', 'default2' );

		/* load template config */
		$this->template();
		$this->tplCfg( $tplPackage );

		if ( $this->_model->get( 'oType' ) != 'entry' ) {
			Sobi::Error( 'Entry', sprintf( 'Serious security violation. Trying to save an object which claims to be an entry but it is a %s. Task was %s', $this->_model->get( 'oType' ), SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
			exit;
		}
		/* add pathway */
		SPFactory::mainframe()->addObjToPathway( $this->_model );
		$this->_model->countVisit();
		$this->_model->loadFields( Sobi::Reg( 'current_section' ) );
		$class = SPLoader::loadView( 'entry' );
		$view = new $class( $this->template );
		$view->assign( $this->_model, 'entry' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->assign( $this->_task, 'task' );
		$view->setConfig( $this->_tCfg, $this->template );
		$view->setTemplate( $tplPackage . '.' . $this->templateType . '.' . $this->template );
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$view ) );
		SPFactory::header()->objMeta( $this->_model );
		$view->display();
		SPFactory::cache()->addObj( $this->_model, 'entry', $this->_model->get( 'id' ) );
	}


	/**
	 * @param SPField[] $fields
	 * @return void
	 */
	protected function createValidationScript( $fields )
	{
		/* get input filters */
		$registry =& SPFactory::registry();
		$registry->loadDBSection( 'fields_filter' );
		$filters = $registry->get( 'fields_filter' );
		$validate = array();
		foreach ( $fields as $field ) {
			$filter = $field->get( 'filter' );
			if ( $filter && isset( $filters[ $filter ] ) ) {
				$f = new stdClass();
				$f->name = $field->get( 'nid' );
				$f->filter = base64_decode( $filters[ $filter ][ 'params' ] );
				$f->msg = Sobi::Txt( '[JS]' . $filters[ $filter ][ 'description' ] );
				$validate[ ] = $f;
			}
		}
		if ( count( $validate ) ) {
			Sobi::Trigger( $this->name(), __FUNCTION__, array( &$validate ) );
			$validate = json_encode( ( $validate ) );
			$header =& SPFactory::header();
			$header->addJsVarFile( 'efilter', md5( $validate ), array( 'OBJ' => addslashes( $validate ) ) );
		}
	}
}
