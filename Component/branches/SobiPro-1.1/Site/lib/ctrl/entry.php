<?php
/**
 * @version: $Id: entry.php 2404 2012-04-27 14:14:12Z Sigrid Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-04-27 16:14:12 +0200 (Fri, 27 Apr 2012) $
 * $Revision: 2404 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/entry.php $
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
			case 'toggle.enabled':
			case 'toggle.approval':
				$r = true;
				$this->toggleState();
				break;
			case 'approve':
			case 'unapprove':
				$r = true;
				$this->approve( $this->_task == 'approve' );
				break;
			case 'publish':
			case 'unpublish':
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
	 */
	protected function checkIn( $sid, $redirect = true )
	{
		parent::checkIn( SPRequest::int( 'entry_id' ), $redirect );
	}

	/**
	 */
	private function state( $state )
	{
		if ( $this->_model->get( 'id' ) ) {
			if ( $this->_model->isCheckedOut() ) {
				$this->response( Sobi::Back(), Sobi::Txt( 'EN.IS_CHECKED_OUT' ), false, SPC::WARN_MSG );
			}
			if ( ( ( $this->_model->get( 'owner' ) == Sobi::My( 'id' ) ) && Sobi::Can( 'entry.publish.own' ) ) || Sobi::Can( 'entry.publish.*' ) ) {
				$this->_model->changeState( $state );
				$this->response( Sobi::Back(), Sobi::Txt( $state ? 'EN.PUBLISHED' : 'EN.UNPUBLISHED' ), false );
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
	 *
	 * @param bool $apply
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
				Sobi::Error( 'Entry',
					sprintf(
						'Serious security violation. Trying to save an object which claims to be an entry but it is a %s. Task was %s', $this->_model->get( 'oType' ), SPRequest::task()
					), SPC::ERROR, 403, __LINE__, __FILE__ );
				exit;
			}
		}
		$error = false;
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

		if ( !( SPRequest::int( 'entry_parent', 0 ) ) ) {
			$error = Sobi::Txt( 'CAT.SELECT_ONE' );
			SPMainFrame::msg( array( 'msgtype' => SPC::ERROR_MSG, 'msg' => $error ) );
		}
		$this->_model->loadFields( Sobi::Reg( 'current_section' ) );
		$fields = $this->_model->get( 'fields' );
		$tsid = SPRequest::string( 'editentry', null, false, 'cookie' );

		if ( !strlen( $tsid ) ) {
			/** Cannot write to file \tmp\edit\2011-04-27_05-04-00_::1\post.var
			 * ^^^^ how the hell it's possible to have IP like ::1 ???!!! ^^^ */
			$tsid = date( 'Y-m-d_H-m-s_' ) . str_replace( array( '.', ':' ), array( '-', null ), SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ) );
			SPLoader::loadClass( 'env.cookie' );
			SPCookie::set( 'editentry', $tsid, SPCookie::hours( 2 ) );
		}
		$store = array();
		if ( count( $fields ) ) {
			foreach ( $fields as $field ) {
				$field->enabled( 'form' );
				try {
					$request = $field->submit( $this->_model, $tsid );
					if ( is_array( $request ) && count( $request ) ) {
						$store = array_merge( $store, $request );
					}
				} catch ( SPException $x ) {
					$error = $x->getMessage();
				}
			}
		}
		/* try in Sobi Cache first */
		if ( Sobi::Cfg( 'cache.l3_enabled', true ) ) {
			SPFactory::cache()->addVar( array( 'post' => $_POST, 'files' => $_FILES, 'store' => $store ), 'request_cache_' . $tsid );
		}
		else {
			SPFs::write( SPLoader::path( 'tmp.edit.' . $tsid . '.post', 'front', false, 'var' ), SPConfig::serialize( $_POST ) );
			SPFs::write( SPLoader::path( 'tmp.edit.' . $tsid . '.files', 'front', false, 'var' ), SPConfig::serialize( $_FILES ) );
			SPFs::write( SPLoader::path( 'tmp.edit.' . $tsid . '.store', 'front', false, 'var' ), SPConfig::serialize( $store ) );

		}
		if ( $error ) {
			Sobi::Redirect( Sobi::Back(), $error, 'error', true );
			exit();
		}
		elseif ( !( Sobi::Can( 'entry.payment.free' ) ) && SPFactory::payment()->count( $this->_model->get( 'id' ) ) ) {
			$this->paymentView( $tsid );
		}
		else {
			Sobi::Redirect( Sobi::Url( array( 'task' => 'entry.save', 'pid' => Sobi::Reg( 'current_section' ), 'sid' => $sid ) ) );
		}
	}

	private function getCache( $tsid, $cache = 'requestcache' )
	{
		$store = SPFactory::cache()->getVar( 'request_cache_' . $tsid );
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
			$tdir = SPLoader::dirPath( 'tmp.edit.' . $tsid );
			if ( strlen( $tsid ) && $tdir ) {
				$tfile = SPLoader::path( 'tmp.edit.' . $tsid . '.post', 'front', true, 'var' );
				$ffile = SPLoader::path( 'tmp.edit.' . $tsid . '.files', 'front', true, 'var' );
				$sfile = SPLoader::path( 'tmp.edit.' . $tsid . '.store', 'front', true, 'var' );
				$post = SPConfig::unserialize( SPFs::read( $tfile ) );
				$files = SPConfig::unserialize( SPFs::read( $ffile ) );
				$store = SPConfig::unserialize( SPFs::read( $sfile ) );
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
		$data = SPFactory::cache()->getObj( 'payment', $sid, 0, true );
		if ( !$data ) {
			$tsid = SPRequest::base64( 'tsid' );
			$tfile = SPLoader::path( 'tmp.edit.' . $tsid . '.payment', 'front', false, 'var' );
			if ( SPFs::exists( $tfile ) ) {
				$data = SPConfig::unserialize( SPFs::read( $tfile ) );
			}
		}
		if ( !$data ) {
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

	private function paymentView( $tsid = null, $data = null )
	{
		/* determine template package */
		$tplPckg = Sobi::Cfg( 'section.template', 'default' );

		/* load template config */
		$this->tplCfg( $tplPckg );

		/* @TODO add pathway */
		SPFactory::mainframe()->addObjToPathway( $this->_model );
		$class = SPLoader::loadView( 'payment' );
		$view = new $class( $this->template );
		$view->assign( $this->_model, 'entry' );
		$view->assign( $data, 'pdata' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->assign( $this->_task, 'task' );
		$view->addHidden( $tsid, 'speditentry' );
		$view->setConfig( $this->_tCfg, $this->_task );
		$view->setTemplate( $tplPckg . '.payment.' . $this->_task );
		Sobi::Trigger( ucfirst( $this->_task ), $this->name(), array( &$view, &$this->_model ) );
		$view->display();
	}

	/**
	 * Save an entry
	 *
	 * @param bool $apply
	 */
	protected function save( $apply )
	{
		// @todo need to get special handling for it
		//		if( !( SPFactory::mainframe()->checkToken() ) ) {
		//			Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		//		}
		$apply = ( int )$apply;
		$new = true;
		if ( !$this->_model ) {
			$this->setModel( SPLoader::loadModel( $this->_type ) );
		}
		if ( $this->_model->get( 'oType' ) != 'entry' ) {
			Sobi::Error( 'Entry',
				sprintf(
					'Serious security violation. Trying to save an object which claims to be an entry but it is a %s. Task was %s', $this->_model->get( 'oType' ), SPRequest::task()
				), SPC::ERROR, 403, __LINE__, __FILE__ );
			exit;
		}

		/* check if we have stored last edit in cache */
		$tsid = SPRequest::string( 'editentry', null, false, 'cookie' );
		$request = $this->getCache( $tsid );
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
			$this->paymentView( $tsid );
			SPFactory::payment()->store( $this->_model->get( 'id' ) );
		}
		/* delete cache files on after */
		if ( SPLoader::dirPath( 'tmp.edit.' . $tsid ) ) {
			SPFs::delete( SPLoader::dirPath( 'tmp.edit.' . $tsid ) );
		}
		else {
			SPFactory::cache()->deleteVar( 'request_cache_' . $tsid );
		}
		SPLoader::loadClass( 'env.cookie' );
		SPCookie::delete( $tsid );

		$sid = $this->_model->get( 'id' );
		$pid = SPRequest::int( 'pid' ) ? SPRequest::int( 'pid' ) : Sobi::Section();
		if ( $new ) {
			if ( $this->_model->get( 'state' ) || Sobi::Can( 'entry.see_unpublished.own' ) ) {
				$msg = $this->_model->get( 'state' ) ? Sobi::Txt( 'EN.ENTRY_SAVED' ) : Sobi::Txt( 'EN.ENTRY_SAVED_NP' );
				$url = Sobi::Url( array( 'sid' => $sid, 'pid' => $pid ) );
			}
			else {
				$msg = Sobi::Txt( 'EN.ENTRY_SAVED_NP' );
				$url = Sobi::Url( array( 'sid' => $pid ) );
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
			$ident = md5( microtime() . $tsid . $sid . time() );
			$data = array( 'data' => SPFactory::payment()->summary( $sid ), 'ident' => $ident );
			$url = Sobi::Url( array( 'sid' => $sid, 'task' => 'entry.payment' ), false, false );
			if ( Sobi::Cfg( 'cache.l3_enabled', true ) ) {
				SPFactory::cache()->addObj( $data, 'payment', $sid, 0, true );
			}
			else {
				SPFs::write( SPLoader::path( 'tmp.edit.' . $ident . '.payment', 'front', false, 'var' ), SPConfig::serialize( $data ) );
				$url = Sobi::Url( array( 'sid' => $sid, 'task' => 'entry.payment', 'tsid' => $ident ), false, false );
			}
			SPLoader::loadClass( 'env.cookie' );
			SPCookie::set( 'payment_' . $sid, $ident, SPCookie::days( 1 ) );
		}
		Sobi::Redirect( $url, $msg );
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
		}

		if ( $this->_model && $this->_model->isCheckedOut() ) {
			Sobi::Redirect( Sobi::Url( array( 'sid' => SPRequest::sid() ) ), Sobi::Txt( 'EN.IS_CHECKED_OUT' ), SPC::ERROR_MSG, true );
		}

		/* determine template package */
		$tplPckg = Sobi::Cfg( 'section.template', 'default' );

		/* load template config */
		$this->template();
		$this->tplCfg( $tplPckg );

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
		$view->setTemplate( $tplPckg . '.' . $this->templateType . '.' . ( $this->template == 'add' ? 'edit' : $this->template ) );
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
		$tplPckg = Sobi::Cfg( 'section.template', 'default' );

		/* load template config */
		$this->template();
		$this->tplCfg( $tplPckg );

		if ( $this->_model->get( 'oType' ) != 'entry' ) {
			Sobi::Error( 'Entry',
				sprintf(
					'Serious security violation. Trying to save an object which claims to be an entry but it is a %s. Task was %s', $this->_model->get( 'oType' ), SPRequest::task()
				), SPC::ERROR, 403, __LINE__, __FILE__ );
			exit;
		}

		/* handle meta data */
		SPFactory::header()->objMeta( $this->_model );

		/* add pathway */
		SPFactory::mainframe()->addObjToPathway( $this->_model );
		$this->_model->countVisit();

		//        $this->_model->loadFields( $this->_model->get( 'id' ) );
		$this->_model->loadFields( Sobi::Reg( 'current_section' ) );
		$class = SPLoader::loadView( 'entry' );
		$view = new $class( $this->template );
		$view->assign( $this->_model, 'entry' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->assign( $this->_task, 'task' );
		$view->setConfig( $this->_tCfg, $this->template );
		$view->setTemplate( $tplPckg . '.' . $this->templateType . '.' . $this->template );
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$view ) );
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
