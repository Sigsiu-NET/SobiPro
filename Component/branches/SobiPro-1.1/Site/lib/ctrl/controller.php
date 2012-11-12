<?php
/**
 * @version: $Id: controller.php 2354 2012-04-17 06:45:16Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-04-17 08:45:16 +0200 (Tue, 17 Apr 2012) $
 * $Revision: 2354 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/controller.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 13-Jan-2009 3:40:14 PM
 */
abstract class SPController extends SPObject implements SPControl
{

	/**
	 * @var string
	 */
	protected $_task = null;
	/**
	 * @var string
	 */
	protected $_defTask = null;
	/**
	 * @var array
	 */
	protected $_tCfg = null;
	/**
	 * @var SPDataModel
	 */
	protected $_model = null;
	/**
	 * @var string
	 */
	protected $_type = null;
	/**
	 * @var string
	 */
	protected $templateType = null;
	/**
	 * @var string
	 */
	protected $template = null;

	/**
	 * @param string $model
	 */
	public function setModel( $model )
	{
		if ( is_string( $model ) ) {
			if ( !class_exists( $model ) && !( $model = SPLoader::loadModel( $model ) ) ) {
				throw new SPException( SPLang::e( 'Cannot instantiate model for "%s" controller. Missing class definition', $this->name() ) );
			}
			$this->_model = new $model();
		}
		else {
			$this->_model = $model;
		}
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$model ) );
	}

	/**
	 * @param stdClass $obj
	 */
	public function extend( $obj )
	{
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$obj ) );
		$this->_model->extend( $obj );
	}

	public function __construct()
	{
		return Sobi::Trigger( 'CreateController', $this->name(), array( &$this ) );
	}

	/**
	 * authorise action
	 * @return bool
	 */
	protected function authorise( $action = 'access', $ownership = 'valid' )
	{
		if ( !( Sobi::Can( $this->_type, $action, $ownership, Sobi::Section() ) ) ) {
			if ( $action == 'add' && $this->_type == 'entry' && Sobi::Cfg( 'redirects.entry_enabled', false ) && strlen( Sobi::Cfg( 'redirects.entry_url', null ) ) ) {
				$redirect = Sobi::Cfg( 'redirects.entry_url', null );
				$msg = Sobi::Cfg( 'redirects.entry_msg', SPLang::e( 'UNAUTHORIZED_ACCESS', SPRequest::task() ) );
				$msgtype = Sobi::Cfg( 'redirects.entry_msgtype', 'message' );
				if ( !( preg_match( '/http[s]?:\/\/.*/', $redirect ) ) && $redirect != 'index.php' ) {
					$redirect = Sobi::Url( $redirect );
				}
				Sobi::Redirect( $redirect, Sobi::Txt( $msg ), $msgtype, true );
			}
			else {
				Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
			}
			exit;
		}
		return true;
	}

	/**
	 * @return bool
	 */
	public function execute()
	{
		$r = false;
		SPRequest::set( 'task', $this->_type . '.' . $this->_task );
		switch ( $this->_task ) {
			/* if someone want edit an object - just check if it is not checked out */
			case 'edit':
				if ( $this->_model && $this->_model->isCheckedOut() ) {
					Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'MSG.OBJ_CHECKED_OUT', array( 'type' => Sobi::Txt( $this->_type ) ) ), SPC::ERROR_MSG, true );
					exit();
				}
				break;
			case 'hide':
			case 'publish':
				if ( $this->authorise( 'manage' ) ) {
					$r = true;
					$this->_model->changeState( $this->_task == 'publish' );
					$state = ( int )( $this->_task == 'publish' );
					$this->response( Sobi::Back(), Sobi::Txt(  $state ? 'OBJ_PUBLISHED' : 'OBJ_UNPUBLISHED', array( 'type' => Sobi::Txt( $this->_type ) ) ), false );
					Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'MSG.OBJ_CHANGED', array( 'type' => Sobi::Txt( $this->_type ) ) ) );
				}
				break;
			case 'apply':
			case 'save':
				$r = true;
				$this->save( $this->_task == 'apply' );
				break;
			case 'cancel':
				if ( defined( 'SOBI_ADM_PATH' ) ) {
					$this->checkIn( SPRequest::sid(), false );
					$this->response( Sobi::Back() );
				}
				$this->checkIn( SPRequest::int( 'sid' ) );
				$r = true;
				if ( SPRequest::int( 'sid' ) ) {
					$url = Sobi::Url( array( 'sid' => SPRequest::sid() ) );
				}
				elseif ( SPRequest::int( 'pid' ) ) {
					$url = Sobi::Url( array( 'sid' => SPRequest::int( 'pid' ) ) );
				}
				else {
					$url = Sobi::Url( array( 'sid' => Sobi::Section() ) );
				}
				$this->response( $url );
				break;
			case 'delete':
				if ( $this->authorise( 'delete', '*' ) ) {
					$r = true;
					if ( $this->_model->get( 'id' ) ) {
						$this->_model->delete();
						$this->response( Sobi::Back(), Sobi::Txt( 'MSG.OBJ_DELETED', array( 'type' => Sobi::Txt( $this->_type ) ) ), false );
					}
					else {
						$this->response( Sobi::Back(), Sobi::Txt( 'CHANGE_NO_ID' ), false, SPC::ERROR_MSG );
					}
				}
				break;
			case 'view':
				$r = true;
				$this->visible();
				$this->view();
				break;
			case 'resetCounter':
				if ( $this->authorise( 'edit', '*' ) ) {
					$r = true;
					$this->_model->countVisit( true );
				}
				break;
			default:
				$r = Sobi::Trigger( 'Execute', $this->name(), array( &$this ) );
				break;
		}
		return $r;
	}

	/**
	 * @return SPDataModel
	 */
	public function getModel()
	{
		return $this->_model;
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

	/**
	 * returns current object type
	 * @return string
	 */
	public function type()
	{
		return $this->_type;
	}

	/**
	 * Save an object
	 * @param bool $apply
	 * @param bool $clone
	 */
	protected function save( $apply, $clone = false )
	{
		$sets = array();
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$apply = ( int )$apply;
		if ( !$this->_model ) {
			$this->setModel( SPLoader::loadModel( $this->_type ) );
		}
		$sid = SPRequest::sid() ? SPRequest::sid() : SPRequest::int( $this->_type . '_id' );
		if ( $sid ) {
			$this->_model->init( $sid );
		}
		$this->_model->getRequest( $this->_type );
		if ( $this->_model->get( 'id' ) ) {
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
		$this->_model->save();
		$sid = $this->_model->get( 'id' );
		$sets[ 'sid' ] = $sid;
		$sets[ $this->_type . '.nid' ] = $this->_model->get( 'nid' );
		$sets[ $this->_type . '.id' ] = $sid;
		if ( $apply || $clone ) {
			if ( $clone ) {
				$msg = Sobi::Txt( 'MSG.OBJ_CLONED', array( 'type' => Sobi::Txt( $this->_type ) ) );
				$this->response( Sobi::Url( array( $this->_type . '.edit', 'sid' => $sid ) ), $msg );
			}
			else {
				$msg = Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' );
				$this->response( Sobi::Url( array( $this->_type . '.edit', 'sid' => $sid ) ), $msg, false, 'success', array( 'sets' => $sets ) );
			}
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'MSG.OBJ_SAVED', array( 'type' => Sobi::Txt( $this->_type ) ) ) );
		}
	}

	/**
	 */
	protected function visible()
	{
		$type = $this->_model->get( 'oType' );
		if ( Sobi::Can( $type, 'access', '*' ) ) {
			return true;
		}
		$error = false;
		$owner = $this->_model->get( 'owner' );
		$state = $this->_model->get( 'state' );
		Sobi::Trigger( $type, 'CheckVisibility', array( &$state, &$owner ) );
		/* if it's unpublished */
		if ( !( $state ) ) {
			if ( $owner == Sobi::My( 'id' ) ) {
				if ( !( Sobi::Can( $type, 'access', 'unpublished_own' ) ) ) {
					$error = true;
				}
			}
			else {
				if ( !( Sobi::Can( $type, 'access', 'unpublished_any' ) ) ) {
					$error = true;
				}
			}
		}
		else {
			if ( !( Sobi::Can( $type, 'access', 'valid' ) ) ) {
				$error = true;
			}
		}
		/* if it's expired or not valid yet  */
		$va = $this->_model->get( 'validUntil' );
		$va = $va ? strtotime( $va ) : 0;
		if ( !( $error ) ) {
			if ( strtotime( $this->_model->get( 'validSince' ) ) > time() || $va > 0 && $va < time() ) {
				if ( $owner == Sobi::My( 'id' ) ) {
					if ( !( Sobi::Can( $type, 'access', 'unpublished_own' ) ) ) {
						$error = true;
					}
				}
				else {
					if ( !( Sobi::Can( $type, 'access', 'unpublished_any' ) ) ) {
						$error = true;
					}
				}
			}
		}
		if ( $error ) {
			$redirect = Sobi::Cfg( 'redirects.' . $type . '_url', null );
			if ( Sobi::Cfg( 'redirects.' . $type . '_enabled', false ) && strlen( $redirect ) ) {
				$msg = Sobi::Cfg( 'redirects.' . $type . '_msg', SPLang::e( 'UNAUTHORIZED_ACCESS', SPRequest::task() ) );
				$msgtype = Sobi::Cfg( 'redirects.' . $type . '_msgtype', 'message' );
				if ( !( preg_match( '/http[s]?:\/\/.*/', $redirect ) ) && $redirect != 'index.php' ) {
					$redirect = Sobi::Url( $redirect );
				}
				Sobi::Redirect( $redirect, Sobi::Txt( $msg ), $msgtype, true );
				exit;
			}
			else {
				Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
			}
		}
	}

	protected function template()
	{
		/* determine template file */
		$template = SPRequest::cmd( 'sptpl', $this->_task );
		if ( strstr( $template, '.' ) ) {
			$template = explode( '.', $template );
			$this->templateType = $template[ 0 ];
			$this->template = $template[ 1 ];
		}
		else {
			$this->templateType = $this->_type;
			$this->template = $template ? $template : $this->_task;
		}
	}

	/**
	 */
	protected function tplCfg( $path, $task = null )
	{
		$file = explode( '.', $path );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$path = SPLoader::dirPath( $file, 'root', true );
			$this->_tCfg = SPLoader::loadIniFile( "{$path}.config", true, false, false, true );
		}
		else {
			$this->_tCfg = SPLoader::loadIniFile( "usr.templates.{$path}.config" );
			$path = SPLoader::dirPath( 'usr.templates.' . $path, 'front', true );
		}
		if ( !$task ) {
			$task = ( $this->_task == 'add' || $this->_task == 'submit' ) ? 'edit' : $this->template;
		}
		if ( SPLoader::translatePath( "{$path}.{$this->templateType}.{$task}", 'absolute', true, 'ini' ) ) {
			$taskCfg = SPLoader::loadIniFile( "{$path}.{$this->templateType}.{$task}", true, false, false, true );
			foreach ( $taskCfg as $section => $keys ) {
				if ( isset( $this->_tCfg[ $section ] ) ) {
					$this->_tCfg[ $section ] = array_merge( $this->_tCfg[ $section ], $keys );
				}
				else {
					$this->_tCfg[ $section ] = $keys;
				}
			}
		}
		Sobi::Trigger( $this->name(), __FUNCTION__, array( &$this->_tCfg ) );
		SPFactory::registry()->set( 'current_template', $path );
	}

	/**
	 */
	protected function tKey( $section, $key, $default = null )
	{
		return isset( $this->_tCfg[ $section ][ $key ] ) ? $this->_tCfg[ $section ][ $key ] : ( isset( $this->_tCfg[ 'general' ][ $key ] ) ? $this->_tCfg[ 'general' ][ $key ] : $default );
	}

	/**
	 * @param string $subject
	 * @param string $col
	 * @param string $def
	 * @return string
	 */
	protected function parseOrdering( $subject, $col, $def )
	{
		return Sobi::GetUserState( $subject . '.ordering.' . SPLang::nid( Sobi::Section( true ) ), $col, $def );
	}

	/**
	 * @param int $sid
	 */
	protected function checkIn( $sid, $redirect = true )
	{
		if ( $sid ) {
			$this->setModel( SPLoader::loadModel( $this->_type ) );
			$this->_model->load( $sid );
			$this->_model->checkIn();
		}
		if ( $redirect ) {
			Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ) );
		}
	}

	protected function response( $url, $message = null, $redirect = true, $type = 'message', $data = array() )
	{
		if ( SPRequest::cmd( 'method', null ) == 'xhr' ) {
			if ( $redirect && $message ) {
				SPFactory::mainframe()->msg( $message, $type );
				SPFactory::mainframe()->proceedMessageQueue();
			}
			SPFactory::mainframe()->cleanBuffer();
			header( 'Content-type: application/json' );
			echo json_encode(
				array(
					'message' => array( 'text' => $message, 'type' => $type ),
					'redirect' => array( 'url' => $url, 'execute' => ( bool )$redirect ),
					'data' => $data
				)
			);
			exit;
		}
		else {
			Sobi::Redirect( $url, $message, $type, true );
		}
	}
}
