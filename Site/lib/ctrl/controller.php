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

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 13-Jan-2009 3:40:14 PM
 */
abstract class SPController extends SPObject implements SPControl
{

	/*** @var string */
	protected $_task = null;
	/*** @var string */
	protected $_defTask = null;
	/*** @var array */
	protected $_tCfg = null;
	/*** @var SPDataModel */
	protected $_model = null;
	/*** @var string */
	protected $_type = null;
	/*** @var string */
	protected $templateType = null;
	/*** @var string */
	protected $template = null;

	/**
	 * @param string $model
	 * @throws SPException
	 * @return void
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
		Sobi::Trigger( $this->name(), __FUNCTION__, [ &$model ] );
	}

	/**
	 * @param stdClass $obj
	 * @param bool $cache
	 * @return void
	 */
	public function extend( $obj, $cache = false )
	{
		Sobi::Trigger( $this->name(), __FUNCTION__, [ &$obj ] );
		$this->_model->extend( $obj, $cache );
		if ( $cache ) {
			$this->_model->countVisit();
		}
	}

	public function __construct()
	{
		return Sobi::Trigger( 'CreateController', $this->name(), [ &$this ] );
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
			Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
			exit;
		}
		return true;
	}

	protected function escape( $redirect, $msg, $msgType )
	{
		if ( !( preg_match( '/http[s]?:\/\/.*/', $redirect ) ) && $redirect != 'index.php' ) {
			$redirect = Sobi::Url( $redirect );
		}
		if ( $msgType != 'none' ) {
			Sobi::Redirect( $redirect, Sobi::Txt( $msg ), $msgType, true );
		}
		else {
			Sobi::Redirect( $redirect, null, null, true );
		}
		exit;
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
					Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'MSG.OBJ_CHECKED_OUT', [ 'type' => Sobi::Txt( $this->_type ) ] ), SPC::ERROR_MSG, true );
					exit();
				}
				break;
			case 'hide':
			case 'publish':
				$r = true;
				$this->state( $this->_task == 'publish' );
				break;
			case 'toggle.enabled':
			case 'toggle.approval':
				$r = true;
				$this->toggleState();
				break;
			case 'apply':
			case 'save':
			case 'saveAndNew':
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
				if ( Input::Sid() ) {
					$url = Sobi::Url( [ 'sid' => Input::Sid() ] );
				}
				elseif ( Input::Int( 'pid' ) ) {
					$url = Sobi::Url( [ 'sid' => Input::Int( 'pid' ) ] );
				}
				else {
					$url = Sobi::Url( [ 'sid' => Sobi::Section() ] );
				}
				$this->response( $url );
				break;
			case 'delete':
				if ( ( $this->_model->get( 'owner' ) == Sobi::My( 'id' ) && $this->authorise( 'delete', 'own' ) ) || $this->authorise( 'delete', '*' ) ) {
					$r = true;
					if ( $this->_model->get( 'id' ) ) {
						$this->_model->delete();
						if ( $this->_type == 'entry' && !( defined( 'SOBIPRO_ADM' ) ) ) {
							if ( SPRequest::int( 'pid' ) ) {
								$url = Sobi::Url( [ 'sid' => SPRequest::int( 'pid' ) ] );
							}
							else {
								$url = Sobi::Url( [ 'sid' => Sobi::Section() ] );
							}
						}
						else {
							$url = Sobi::Back();
						}
						$this->response( $url, Sobi::Txt( 'MSG.OBJ_DELETED', [ 'type' => Sobi::Txt( $this->_type ) ] ), false );
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
					$this->_model->countVisit( true );
					exit( true );
				}
				break;
			default:
				$r = Sobi::Trigger( 'Execute', $this->name(), [ &$this ] );
				break;
		}
		return $r;
	}

	protected function checkTranslation()
	{
		$lang = Input::Cmd( 'sp-language', 'get' );
		if ( $lang && $lang != Sobi::Cfg( 'language' ) ) {
			$languages = SPFactory::CmsHelper()->availableLanguages();
			SPFactory::message()
					->info( Sobi::Txt( 'INFO_DIFFERENT_LANGUAGE', $this->_type, $languages[ $lang ][ 'name' ] ), false );
		}
	}

	protected function state( $state )
	{
		if ( $this->_model->get( 'id' ) ) {
			if ( $this->authorise( 'manage' ) ) {
				$this->_model->changeState( $state );
				$state = ( int )( $this->_task == 'publish' ) ? true : $state;
				$this->response( Sobi::Back(), Sobi::Txt( $state ? 'OBJ_PUBLISHED' : 'OBJ_UNPUBLISHED', [ 'type' => Sobi::Txt( $this->_type ) ] ), false );
			}
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'CHANGE_NO_ID' ), true, SPC::ERROR_MSG );
		}
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
		Sobi::Trigger( $this->name(), __FUNCTION__, [ &$this->_task ] );
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
		$sets = [];
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$this->validate( $this->_type . '.edit', $this->_type );
		$apply = ( int )$apply;
		if ( !$this->_model ) {
			$this->setModel( SPLoader::loadModel( $this->_type ) );
		}
		$sid = Input::Sid() ? Input::Sid() : Input::Int( $this->_type . '_id' );
		if ( $sid ) {
			$this->_model->init( $sid );
		}
		/** store previous state for possible triggers */
		$preState = [
				'approved' => $this->_model->get( 'approved' ),
				'state' => $this->_model->get( 'state' ),
				'new' => !( $this->_model->get( 'id' ) )
		];
		SPFactory::registry()->set( 'object_previous_state', $preState );
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
				$msg = Sobi::Txt( 'MSG.OBJ_CLONED', [ 'type' => Sobi::Txt( $this->_type ) ] );
				$this->response( Sobi::Url( [ 'task' => $this->_type . '.edit', 'sid' => $sid ] ), $msg, false, 'success', [ 'sets' => $sets ] );
			}
			else {
				$msg = Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' );
				$this->response( Sobi::Url( [ 'task' => $this->_type . '.edit', 'sid' => $sid ] ), $msg, $this->_type == 'section', 'success', [ 'sets' => $sets ] );
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
			$this->response( Sobi::Back(), Sobi::Txt( 'MSG.OBJ_SAVED', [ 'type' => Sobi::Txt( $this->_type ) ] ), true, 'success' );
		}
	}

	protected function toggleState()
	{
		if ( $this->authorise( 'manage' ) ) {
			if ( $this->_task == 'toggle.enabled' ) {
				$this->state( !( $this->_model->get( 'state' ) ) );
			}
			else {
				if ( Sobi::Cfg( 'entry.approval_publish', true ) ) {
					if ( !( $this->_model->get( 'approved' ) ) ) {
						$this->state( true );
					}
				}
				$this->approval( !( $this->_model->get( 'approved' ) ) );
			}
		}
	}

	/**
	 */
	public function visible()
	{
		$type = $this->_model->get( 'oType' );
		if ( Sobi::Can( $type, 'access', '*' ) ) {
			return true;
		}
		$error = false;
		$owner = $this->_model->get( 'owner' );
		$state = $this->_model->get( 'state' );
		Sobi::Trigger( $type, 'CheckVisibility', [ &$state, &$owner ] );
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
		/** if not approved */
		/** and unapproved entry can be accessed
		 * because then the previously created version
		 * should be displayed
		 */
		if ( $type == 'category' ) {
			$approved = $this->_model->get( 'approved' );
			if ( !( $approved ) ) {
				if ( !( Sobi::Can( $type, 'access', 'unapproved_any' ) ) ) {
					$error = true;
				}
			}
		}
		/* if it's expired or not valid yet  */
		$va = $this->_model->get( 'validUntil' );
		$va = $va ? strtotime( $va . ' UTC' ) : 0;
		if ( !( $error ) ) {
			if ( strtotime( $this->_model->get( 'validSince' ) . ' UTC' ) > gmdate( 'U' ) ) {
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
			elseif ( $va > 0 && $va < gmdate( 'U' ) ) {
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
			$redirect = Sobi::Cfg( 'redirects.' . $type . '_access_url', null );
			if ( Sobi::Cfg( 'redirects.' . $type . '_access_enabled', false ) && strlen( $redirect ) ) {
				$this->escape( $redirect, Sobi::Cfg( 'redirects.' . $type . '_access_msg', SPLang::e( 'UNAUTHORIZED_ACCESS', SPRequest::task() ) ),
					Sobi::Cfg( 'redirects.' . $type . '_access_msgtype', 'message' ) );
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
		if ( strlen( $template && $template != $this->_task ) && !( SPRequest::bool( 'xmlc' ) ) ) {
			$template = "/{$this->templateType}/{$this->template}.xsl";
			SPFactory::registry()->set( 'cache_view_template', $template );
		}
		SPFactory::registry()->set( 'template_type', $this->templateType );
	}

	/**
	 * @param $path
	 * @param null $task
	 */
	protected function tplCfg( $path, $task = null )
	{
		$file = explode( '.', $path );
		$files = [];
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$path = SPLoader::dirPath( $file, 'root', true );
			$this->_tCfg = SPLoader::loadIniFile( "{$path}.config", true, false, false, true );
			$files[] = SPLoader::iniStorage();
		}
		else {
			$this->_tCfg = SPLoader::loadIniFile( "usr.templates.{$path}.config" );
			$files[] = SPLoader::iniStorage();
			$path = SPLoader::dirPath( 'usr.templates.' . $path, 'front', true );
		}
		if ( !$task ) {
			$task = ( $this->_task == 'add' || $this->_task == 'submit' || $this->_task == 'edit' ) ? 'edit' : $this->_defTask;
			Input::Set( 'task', "{$this->_type}.{$task}" );
		}
		if ( SPLoader::translatePath( "{$path}.{$this->templateType}.{$task}", 'absolute', true, 'ini' ) ) {
			$taskCfg = SPLoader::loadIniFile( "{$path}.{$this->templateType}.{$task}", true, false, false, true );
			$files[] = SPLoader::iniStorage();
			foreach ( $taskCfg as $section => $keys ) {
				if ( isset( $this->_tCfg[ $section ] ) ) {
					$this->_tCfg[ $section ] = array_merge( $this->_tCfg[ $section ], $keys );
				}
				else {
					$this->_tCfg[ $section ] = $keys;
				}
			}
		}
		if ( count( $files ) ) {
			foreach ( $files as $i => $file ) {
				$files[ $i ] = [ 'file' => str_replace( SPLoader::translateDirPath( Sobi::Cfg( 'section.template' ), 'templates' ), null, $file ), 'checksum' => md5_file( $file ) ];
			}
			SPFactory::registry()->set( 'template_config', $files );
		}
		if ( SPLoader::translatePath( "{$path}.config", 'absolute', true, 'json' ) ) {
			$config = json_decode( SPFs::read( SPLoader::translatePath( "{$path}.config", 'absolute', true, 'json' ) ), true );
			$settings = [];
			foreach ( $config as $section => $setting ) {
				$settings[ str_replace( '-', '.', $section ) ] = $setting;
			}
			if ( Input::Cmd( 'sptpl' ) ) {
				$file = Input::String( 'sptpl' );
			}
			else {
				$file = $task;
			}
			if ( strstr( $file, $this->templateType ) ) {
				$file = str_replace( $this->templateType, null, $file );
			}
			if ( SPLoader::translatePath( "{$path}.{$this->templateType}.{$file}", 'absolute', true, 'json' ) ) {
				$subConfig = json_decode( SPFs::read( SPLoader::translatePath( "{$path}.{$this->templateType}.{$file}", 'absolute', true, 'json' ) ), true );
				if ( count( $subConfig ) ) {
					foreach ( $subConfig as $section => $subSettings ) {
						foreach ( $subSettings as $k => $v ) {
							$settings[ str_replace( '-', '.', $section ) ][ $k ] = $v;
						}
					}
				}
			}
			if ( isset( $settings[ 'general' ] ) ) {
				foreach ( $settings[ 'general' ] as $k => $v ) {
					$this->_tCfg[ 'general' ][ $k ] = $v;
				}
			}
			$task = SPRequest::task() == 'entry.add' ? 'entry.edit' : SPRequest::task();
			if ( isset( $settings[ $task ] ) ) {
				foreach ( $settings[ $task ] as $k => $v ) {
					$this->_tCfg[ 'general' ][ $k ] = $v;
				}
			}
		}

		Sobi::Trigger( $this->name(), __FUNCTION__, [ &$this->_tCfg ] );
		SPFactory::registry()->set( 'current_template', $path );
	}

	/**
	 * @param $section
	 * @param $key
	 * @param null $default
	 * @return null
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
	 * @param bool $redirect
	 * @return void
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

	protected function response( $url, $message = null, $redirect = true, $type = SPC::INFO_MSG, $data = [], $request = 'post' )
	{
		if ( is_array( $message ) ) {
			$type = $message[ 'type' ];
			$message = $message[ 'text' ];
		}
		if ( SPRequest::cmd( 'method', null, $request ) == 'xhr' ) {
			if ( $redirect && $message ) {
				SPFactory::message()->setMessage( $message, false, $type );
			}
			$url = str_replace( '&amp;', '&', $url );
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			echo json_encode(
					[
							'message' => [ 'text' => $message, 'type' => $type ],
							'redirect' => [ 'url' => $url, 'execute' => ( bool )$redirect ],
							'data' => $data
					]
			);
			exit;
		}
		else {
			if ( $message ) {
				if ( strstr( $url, 'com_sobipro' ) ) {
					SPFactory::message()->setMessage( $message, false, $type );
					$message = null;
				}
			}
			Sobi::Redirect( ( is_array( $url ) || strstr( 'index.php', $url ) ) && trim( $url != 'index.php' ) ? Sobi::Url( $url ) : $url, $message, null, $redirect );
		}
	}

	/**
	 * @param $xml - path to xml file inside the administrator directory (e.g. field.definitions.filter)
	 * @param $type - object type or array with error url
	 * */
	protected function validate( $xml, $type )
	{
		$definition = SPLoader::path( $xml, 'adm', true, 'xml' );
		if ( $definition ) {
			if ( is_array( $type ) ) {
				$errorUrl = Sobi::Url( $type );
			}
			else {
				$errorUrl = Sobi::Url( [ 'task' => $type . '.edit', 'sid' => SPRequest::sid() ] );
			}
			$xdef = new DOMXPath( SPFactory::LoadXML( $definition ) );
			$required = $xdef->query( '//field[@required="true"]' );
			if ( $required->length ) {
				for ( $i = 0; $i < $required->length; $i++ ) {
					$node = $required->item( $i );
					$name = $node->attributes->getNamedItem( 'name' )->nodeValue;
					if ( !( SPRequest::raw( str_replace( '.', '_', $name ) ) ) ) {
						$this->response( $errorUrl, Sobi::Txt( 'PLEASE_FILL_IN_ALL_REQUIRED_FIELDS' ), false, SPC::ERROR_MSG, [ 'required' => $name ] );
					}
				}
			}
		}
	}
}
