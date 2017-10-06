<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

use Sobi\Error\Exception;
use Sobi\FileSystem\FileSystem;
use Sobi\Input\Input;
use Sobi\FileSystem\Directory;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'config', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 22-Jun-2010 15:55:21
 */
class SPExtensionsCtrl extends SPConfigAdmCtrl
{
	/**
	 * @var string
	 */
	protected $_type = 'extensions';
	/**
	 * @var string
	 */
	protected $_defTask = 'installed';

	public function __construct()
	{
		if ( Sobi::Section() ) {
			if ( !( Sobi::Can( 'section.configure' ) ) ) {
				Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
				exit();
			}
		}
		elseif ( !( Sobi::Can( 'cms.apps' ) ) ) {
			Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
			exit();
		}
	}

	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'installed':
				$this->installed();
				Sobi::ReturnPoint();
				break;
			case 'install':
				$this->install();
				break;
			case 'repositories':
				$this->repos();
				Sobi::ReturnPoint();
				break;
			case 'addRepo':
				$this->addRepo();
				break;
			case 'delRepo':
				$this->delRepo();
				break;
			case 'confirmRepo':
				$this->confirmRepo();
				break;
			case 'fetch':
				$this->fetch();
				break;
			case 'registerRepo':
				$this->registerRepo();
				break;
			case 'publish':
			case 'unpublish':
				$this->publish( ( $this->_task == 'publish' ) );
				break;
			case 'toggle':
				$this->toggle();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'browse':
				$this->browse();
				break;
			case 'manage':
				$this->section();
				break;
			case 'updates':
				$this->updates();
				break;
			case 'download':
				$this->download();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !( Sobi::Trigger( 'Execute', $this->name(), [ &$this ] ) ) ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	public function updates( $json = true )
	{
		if ( $this->updatesTime() ) {
			$repos = SPLoader::dirPath( 'etc.repos', 'front' );
			$repos = SPFactory::Instance( 'base.fs.directory', $repos );
			$repos = $repos->searchFile( 'repository.xml', true, 2 );
			$repos = array_keys( $repos );
			$cr = count( $repos );
			$list = [];
			$repository = SPFactory::Instance( 'services.installers.repository' );
			try {
				$installed = SPFactory::db()
						->select( [ 'name', 'type', 'pid', 'version' ], 'spdb_plugins' )
						->loadAssocList();
				array_unshift( $installed, [ 'name' => 'SobiPro', 'type' => 'core', 'pid' => 'SobiPro', 'version' => implode( '.', SPFactory::CmsHelper()->myVersion() ) ] );
			} catch ( SPException $x ) {
				Sobi::Error( 'extensions', SPLang::e( 'CANNOT_GET_UPDATES', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				SPFactory::mainframe()->cleanBuffer();
				echo json_encode( [ 'err' => SPLang::e( 'REPO_ERR', $x->getMessage() ) ] );
				exit;
			}

			if ( !( $cr ) ) {
				SPFactory::mainframe()->cleanBuffer();
				echo json_encode( [ 'err' => SPLang::e( 'UPD_NO_REPOS_FOUND' ) ] );
				exit;
			}

			for ( $i = 0; $i < $cr; $i++ ) {
				$repository->loadDefinition( $repos[ $i ] );
				try {
					$repository->connect();
					$l = $repository->updates( $installed );
				} catch ( SPException $x ) {
					if ( !( $json ) ) {
						throw new Exception( $x->getMessage() );
					}
					SPFactory::mainframe()->cleanBuffer();
					//echo json_encode( array( 'err' => SPLang::e( '%s Repository: %s', $x->getMessage(), $repository->get( 'id' ) ) ) );
					echo json_encode( [ 'err' => SPLang::e( '%s', $x->getMessage() ) ] );
					exit;
				}
				if ( is_array( $l ) ) {
					if ( count( $l ) ) {
						$pid = $repository->get( 'id' );
						foreach ( $l as $eid => $values ) {
							$values[ 'repository' ] = $pid;
							$l[ $eid ] = $values;
						}
						$r[ $pid ] = $repository->get( 'url' );
					}
					$list = array_merge( $list, $l );
				}
			}
			if ( count( $list ) ) {
				$updates = [];
				$updates [ 'created' ] = time();
				$updates [ 'createdBy' ] = [ 'id' => Sobi::My( 'id' ), 'name' => Sobi::My( 'name' ) ];
				$updates [ 'repositories' ] = $r;
				$updates [ 'updates' ] = $list;
				$file = SPFactory::Instance( 'base.fs.file', SPLoader::path( 'etc.updates', 'front', false, 'xml' ) );
				$def = SPFactory::Instance( 'types.array' );
				$file->content( $def->toXML( $updates, 'updatesList' ) );
				$file->save();
			}
		}
		return $this->parseUpdates( $json );
	}

	public function updatesTime()
	{
		if ( SPFs::exists( SPLoader::path( 'etc.updates', 'front', false, 'xml' ) ) ) {
			if ( time() - filemtime( SPLoader::path( 'etc.updates', 'front', true, 'xml' ) ) > ( 60 * 60 * 12 ) ) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}

	private function parseUpdates( $json )
	{
		$file = SPLoader::path( 'etc.updates', 'front', true, 'xml' );
		if ( $file ) {
			$def = SPFactory::Instance( 'types.array' );
			$doc = new DOMDocument();
			$doc->load( SPLoader::path( 'etc.updates', 'front', true, 'xml' ) );
			$list = $def->fromXML( $doc, 'updateslist' );
			if ( count( $list[ 'updateslist' ][ 'updates' ] ) ) {
				foreach ( $list[ 'updateslist' ][ 'updates' ] as $id => $upd ) {
					if ( $upd[ 'update' ] == 'true' ) {
						$list[ 'updateslist' ][ 'updates' ][ $id ][ 'update_txt' ] = Sobi::Txt( 'UPD.UPDATE_AVAILABLE', $list[ 'updateslist' ][ 'updates' ][ $id ][ 'current' ] );
					}
					else {
						$list[ 'updateslist' ][ 'updates' ][ $id ][ 'update_txt' ] = Sobi::Txt( 'UPD.UP_TO_DATE' );
					}
				}
			}
			if ( $json ) {
				SPFactory::mainframe()
						->cleanBuffer()
						->customHeader();
				echo json_encode( $list[ 'updateslist' ][ 'updates' ] );
				exit;
			}
			else {
				return $list[ 'updateslist' ][ 'updates' ];
			}
		}
	}

	private function section()
	{
		Sobi::ReturnPoint();

		/* create menu */
		$menu = SPFactory::Instance( 'views.adm.menu', 'extensions.manage', Sobi::Section() );
		$cfg = SPLoader::loadIniFile( 'etc.adm.section_menu' );
		Sobi::Trigger( 'Create', 'AdmMenu', [ &$cfg ] );
		if ( count( $cfg ) ) {
			foreach ( $cfg as $section => $keys ) {
				$menu->addSection( $section, $keys );
			}
		}
		Sobi::Trigger( 'AfterCreate', 'AdmMenu', [ &$menu ] );
		/* create new SigsiuTree */
		$tree = SPFactory::Instance( 'mlo.tree' );
		/* set link */
		$tree->setHref( Sobi::Url( [ 'sid' => '{sid}' ] ) );
		$tree->setId( 'menuTree' );
		/* set the task to expand the tree */
		$tree->setTask( 'category.expand' );
		$tree->init( Sobi::Section() );
		/* add the tree into the menu */
		$menu->addCustom( 'AMN.ENT_CAT', $tree->getTree() );
		/* section model */
		$cSec = SPFactory::Model( 'section' );
		$cSec->init( Sobi::Section() );

		$db = SPFactory::db();
		$all = $db
				->select( '*', 'spdb_plugins', [ '!type' => Sobi::Cfg( 'apps.global_types_array' ), 'enabled' => 1 ] )
				->loadAssocList( 'pid' );
		$list = $db
				->select( '*', 'spdb_plugin_section', [ 'section' => Sobi::Section() ] )
				->loadAssocList( 'pid' );
		if ( count( $all ) ) {
			foreach ( $all as $id => $app ) {
				if ( isset( $list[ $id ] ) ) {
					$all[ $id ][ 'enabled' ] = $list[ $id ][ 'enabled' ];
					$all[ $id ][ 'position' ] = $list[ $id ][ 'position' ];
				}
				else {
					$all[ $id ][ 'enabled' ] = false;
					$all[ $id ][ 'position' ] = 9999;
				}
				$all[ $id ][ 'repository' ] = null;
			}
		}
		/** @var $view SPExtensionsView */
		$view = SPFactory::View( 'extensions', true );
		$sid = Sobi::Section();
		$view->assign( $this->_task, 'task' )
				->assign( $menu, 'menu' )
				->assign( $sid, 'sid' )
				->assign( $all, 'applications' );
		Sobi::Trigger( $this->_task, $this->name(), [ &$view ] );
		$view->display();
		Sobi::Trigger( 'After' . ucfirst( $this->_task ), $this->name(), [ &$view ] );

	}

	public function appsMenu()
	{
		$links = [];
		$db =& SPFactory::db();
		$enabled = $db->select( 'pid', 'spdb_plugins', [ 'enabled' => 1 ] )
				->loadResultArray();
		$all = $db->select( 'pid', 'spdb_plugin_task', [ 'onAction' => 'adm_menu', 'pid' => $enabled ] )->loadResultArray();
		if ( count( $all ) ) {
			if ( Sobi::Section() ) {
				$list = $db->select( 'pid', 'spdb_plugin_section', [ 'section' => Sobi::Section(), 'pid' => $all, 'enabled' => 1 ] )->loadResultArray();
			}
			else {
				$list = $db->select( 'pid', 'spdb_plugins', [ 'pid' => $all, 'enabled' => 1 ] )->loadResultArray();
			}
			if ( count( $list ) ) {
				foreach ( $list as $app ) {
					if ( SPLoader::translatePath( 'opt.plugins.' . $app . '.init' ) ) {
						$pc = SPLoader::loadClass( $app . '.init', false, 'plugin' );
						if ( method_exists( $pc, 'admMenu' ) ) {
							call_user_func_array( [ $pc, 'admMenu' ], [ &$links ] );
						}
					}
					else {
						Sobi::Error( 'Class Load', sprintf( 'Cannot load application file at %s. File does not exist or is not readable.', $app ), SPC::WARNING, 0 );
					}
				}
			}
		}
		return array_flip( $links );
	}

	private function download()
	{
		if ( SPRequest::word( 'callback' ) ) {
			return $this->downloadRequest();
		}

		$pid = str_replace( '-', '_', SPRequest::cmd( 'exid' ) );
		$msg = SPFactory::Controller( 'progress' );
		if ( strstr( $pid, '.disabled' ) ) {
			$msg->error( SPLang::e( 'REPO_ERR_APP_DISABLED' ) );
			exit;
		}
		if ( !( SPFactory::mainframe()->checkToken( 'get' ) ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			$msg->error( SPLang::e( 'REPO_ERR', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ) ) );
			exit;
		}
		$msg->progress( 5, Sobi::Txt( 'EX.CONNECTING_TO_REPO' ) );
		if ( !( strlen( $pid ) ) ) {
			$msg->progress( 100, Sobi::Txt( 'EX.SELECT_EXT_FROM_LIST' ) );
			exit;
		}
		$pid = explode( '.', $pid );
		$rid = $pid[ 0 ];
		$tid = $pid[ 1 ];
		$pid = $pid[ 2 ];
		$repository = SPFactory::Instance( 'services.installers.repository' );
		$repository->loadDefinition( SPLoader::path( "etc.repos.{$rid}.repository", 'front', true, 'xml' ) );
		$msg->progress( 15, Sobi::Txt( 'EX.CONNECTING_TO_REPO_NAME', [ 'repo' => $repository->get( 'name' ) ] ) );
		try {
			$repository->connect( $msg );
			sleep( 1 );
		} catch ( SPException $x ) {
			$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
			exit;
		}
		try {
			$response = $repository->request( $repository->get( 'token' ), $tid, $pid );
//			sleep( 1 );
		} catch ( SPException $x ) {
			$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
			exit;
		}
		$msg->progress( 50, Sobi::Txt( 'EX.SENDING_REQUEST_TO', [ 'repo' => $repository->get( 'name' ) ] ), 2000 );
		$this->downloadResponse( $response, $repository, $msg );
	}

	private function downloadResponse( $response, $repository, $msg )
	{
		if ( is_array( $response ) && isset( $response[ 'callback' ] ) ) {
			$progress = isset( $response[ 'progress' ] ) ? $response[ 'progress' ] : 45;
			$msg->progress( $progress, Sobi::Txt( 'EX.REPO_FEEDBACK_REQ', [ 'repo' => $repository->get( 'name' ) ] ) );
			return $this->parseSoapRequest( $response, null, SPRequest::cmd( 'plid' ) );
		}
		elseif ( is_array( $response ) && isset( $response[ 'message' ] ) ) {
			$type = isset( $response[ 'message-type' ] ) ? $response[ 'message-type' ] : SPC::ERROR_MSG;
			$msg->message( $response[ 'message' ], $type );
			exit;
		}
		elseif ( $response === true || isset( $response[ 'package' ] ) ) {
			$progress = isset( $response[ 'progress' ] ) ? $response[ 'progress' ] : 60;
			$msg->progress( $progress, Sobi::Txt( 'EX.REC_PACKAGE_WITH_TYPE_NAME', [ 'type' => Sobi::Txt( $response[ 'type' ] ), 'name' => $response[ 'name' ] ] ) );
//			sleep( 1 );
			if ( !( $response[ 'package' ] ) ) {
				$msg->error( SPLang::e( 'PACKAGE_ERR' ) );
			}
			$package = $this->packageToFile( $response[ 'package' ], $response[ 'checksum' ], $response[ 'filename' ], $msg );
			try {
				$r = $this->install( $package );
				$msg->progress( 95, $r[ 'msg' ] );
				$msg->progress( 100, $r[ 'msg' ], $r[ 'msgtype' ] );
			} catch ( SPException $x ) {
				$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
				exit;
			}
			exit;
		}
	}

	private function downloadRequest()
	{
		$pid = SPRequest::cmd( 'exid' );
		$msg = SPFactory::Controller( 'progress' );
		$msg->progress( 50, Sobi::Txt( 'EX.CONNECTING_TO_REPO' ) );
		$pid = explode( '.', $pid );
		$repo = $pid[ 0 ];
		$tid = $pid[ 1 ];
		$pid = $pid[ 2 ];
		$data = SPRequest::search( 'sprpfield_' );
		$answer = [];
		$msg->progress( 55, Sobi::Txt( 'EX.PARSING_RESPONSE' ) );
		if ( count( $data ) ) {
			foreach ( $data as $k => $v ) {
				$v = ( strlen( $v ) && $v != '' ) ? $v : SPC::NO_VALUE;
				$answer[ str_replace( 'sprpfield_', null, $k ) ] = $v;
			}
		}
		$defFile = SPLoader::path( "etc.repos.{$repo}.repository", 'front', true, 'xml' );
		$repository = SPFactory::Instance( 'services.installers.repository' );
		$repository->loadDefinition( $defFile );
		try {
			$repository->connect();
		} catch ( SPException $x ) {
			$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
			exit;
		}
		$callback = SPRequest::word( 'callback' );
		try {
			array_unshift( $answer, $pid );
			array_unshift( $answer, $tid );
			array_unshift( $answer, $repository->get( 'token' ) );
			$msg->progress( 60, Sobi::Txt( 'EX.SENDING_REQUEST_TO', [ 'repo' => $repository->get( 'name' ) ] ) );
			$response = call_user_func_array( [ $repository, $callback ], $answer );
//			sleep( 2 );
		} catch ( SPException $x ) {
			$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
			exit;
		}
		$this->downloadResponse( $response, $repository, $msg );
	}

	private function packageToFile( $stream, $checksum, $name, $msg )
	{
		$path = SPLoader::dirPath( 'tmp.install', 'front', false );
		$stream = base64_decode( $stream );
		$msg->progress( 65, Sobi::Txt( 'EX.EXAMINING_CHECKSUM' ), 1000 );
		try {
			SPFs::write( $path . '/' . $name, $stream );
		} catch ( SPException $x ) {
			$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
			exit;
		}
		if ( md5_file( $path . '/' . $name ) != $checksum ) {
			$msg->error( SPLang::e( 'EX.CHECKSUM_NOK' ) );
			exit;
		}
//		sleep( 1 );
		$msg->progress( 75, Sobi::Txt( 'EX.CHECKSUM_OK' ) );
		return $path . '/' . $name;
	}

	private function fetch()
	{
		$msg = SPFactory::Controller( 'progress' );
		if ( !( SPFactory::mainframe()->checkToken( 'get' ) ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			$msg->error( SPLang::e( 'REPO_ERR', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ) ) );
			exit;
		}
		$msg->progress( 0, Sobi::Txt( 'EX.GETTING_REPOS' ) );
		$repos = SPLoader::dirPath( 'etc.repos', 'front' );
		$repos = SPFactory::Instance( 'base.fs.directory', $repos );
		$repos = $repos->searchFile( 'repository.xml', true, 2 );
		$repos = array_keys( $repos );
		$cr = count( $repos );
		$progress = 5;
		$msg->progress( $progress, Sobi::Txt( 'EX.FOUND_NUM_REPOS', [ 'count' => $cr ] ) );
		$repository = SPFactory::Instance( 'services.installers.repository' );
//		sleep( 5 );
		$steps = 2;
		$pstep = ( 80 / $cr ) / $steps;
		$list = [];
		$r = [];
		for ( $i = 0; $i < $cr; $i++ ) {
			$repository->loadDefinition( $repos[ $i ] );
			$progress += ( $pstep / $steps );
			$msg->progress( $progress, Sobi::Txt( 'EX.CON_TO_REPO_D_D', [ 'num' => ( $i + 1 ), 'from' => $cr ] ) );
			try {
				$repository->connect( $msg );
				sleep( 1 );
			} catch ( SPException $x ) {
				$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
				exit;
			}

			$progress += ( $pstep / $steps );
			$msg->progress( $progress, Sobi::Txt( 'EX.FETCHING_FROM_REPO_D_D', [ 'num' => ( $i + 1 ), 'from' => $cr ] ) );
			try {
				$ver = SPFactory::CmsHelper()->cmsVersion();
				$l = $repository->fetchList( $repository->get( 'token' ), 'Joomla ' . $ver[ 'major' ] . '.' . $ver[ 'minor' ] );
//				sleep( 1 );
			} catch ( SPException $x ) {
				$msg->error( SPLang::e( 'REPO_ERR', $x->getMessage() ) );
			}
			if ( is_array( $l ) ) {
				if ( count( $l ) ) {
					$pid = $repository->get( 'id' );
					foreach ( $l as $eid => $values ) {
						$eid = str_replace( [ '.', '_' ], '-', $eid );
						$values[ 'repository' ] = $pid;
						$l[ $eid ] = $values;
					}
					$r[ $pid ] = $repository->get( 'url' );
				}
				$list = array_merge( $list, $l );
			}
			$progress += ( $pstep / $steps );
			$msg->progress( $progress, Sobi::Txt( 'EX.FETCHED_LIST_FROM_REPOSITORY', [ 'count' => count( $l ), 'num' => ( $i + 1 ), 'from' => $cr ] ) );
		}
		$progress += 5;
		if ( count( $list ) ) {
			$msg->progress( $progress, Sobi::Txt( 'EX.FETCHED_D_EXTENSIONS', [ 'count' => count( $list ) ] ) );
			$extensions = [];
			$extensions[ 'created' ] = time();
			$extensions[ 'createdBy' ] = [ 'id' => Sobi::My( 'id' ), 'name' => Sobi::My( 'name' ) ];
			$extensions[ 'repositories' ] = $r;
			$extensions[ 'extensions' ] = $list;
			$progress += 10;
			$msg->progress( $progress );
			/** @var SPFile $file */
			$file = SPFactory::Instance( 'base.fs.file', SPLoader::path( 'etc.extensions', 'front', false, 'xml' ) );
			$def = SPFactory::Instance( 'types.array' );
			$file->content( $def->toXML( $extensions, 'extensionsList' ) );
			$msg->progress( $progress, $def->toXML( $extensions, 'extensionsList' ) );
			try {
				$file->save();
			} catch ( SPException $x ) {
				$msg->progress( $progress, $x->getMessage() );
			}
//			sleep( 1 );
		}
		$msg->progress( 100, Sobi::Txt( 'EX.EXT_LIST_UPDATED' ), SPC::SUCCESS_MSG );
//		SPFactory::message()->success( Sobi::Txt( 'EX.EXT_LIST_UPDATED' ), false );
		exit;
	}

	private function browse()
	{
		/** @var $view SPExtensionsView */
		$view = SPFactory::View( 'extensions', true );
		$def = SPFactory::Instance( 'types.array' );
		$list = null;
		$apps = [];
		if ( SPFs::exists( SPLoader::path( 'etc.extensions', 'front', false, 'xml' ) ) ) {
			$list = $def->fromXML( SPFactory::LoadXML( SPLoader::path( 'etc.extensions', 'front', false, 'xml' ) ), 'extensionslist' );
		}
		if ( !( count( $list ) ) ) {
			SPFactory::message()->warning( 'EX.MSG_UPDATE_FIRST' );
			$status = [ 'label' => Sobi::Txt( 'EX.LAST_UPDATED', Sobi::Txt( 'UNKNOWN' ) ), 'type' => SPC::ERROR_MSG ];
			$view->assign( $status, 'last-update' );
		}
		else {
			try {
				$installed = SPFactory::db()
						->select( '*', 'spdb_plugins' )
						->loadAssocList();
			} catch ( SPException $x ) {
			}
			$status = [ 'label' => Sobi::Txt( 'EX.LAST_UPDATED', SPFactory::config()->date( $list[ 'extensionslist' ][ 'created' ] ) ), 'type' => SPC::INFO_MSG ];
			$view->assign( $status, 'last-update' );
			$list = $list[ 'extensionslist' ][ 'extensions' ];
			if ( count( $list ) ) {
				foreach ( $list as $pid => $plugin ) {
					$plugin[ 'installed' ] = -1;
					$plugin[ 'action' ] = [ 'text' => Sobi::Txt( 'EX.INSTALL_APP' ), 'class' => 'install' ];
					$eid = $pid;
					if ( $plugin[ 'type' ] == 'language' ) {
						$eid = explode( '-', $pid );
						$eid[ 0 ] = strtolower( $eid[ 0 ] );
						$eid[ 1 ] = strtoupper( $eid[ 1 ] );
						$eid = implode( '-', $eid );
					}
					if ( count( $installed ) ) {
						foreach ( $installed as $ex ) {
							if ( $eid == $ex[ 'pid' ] || str_replace( '_', '-', $ex[ 'pid' ] ) == $eid ) {
								$plugin[ 'installed' ] = -2;
								$plugin[ 'action' ] = [ 'text' => Sobi::Txt( 'EX.REINSTALL_APP' ), 'class' => 'reinstall' ];
								if ( version_compare( $plugin[ 'version' ], $ex[ 'version' ], '>' ) ) {
									$plugin[ 'installed' ] = -3;
									$plugin[ 'action' ] = [ 'text' => Sobi::Txt( 'EX.UPDATE_APP' ), 'class' => 'update' ];
								}
							}
						}
					}
					if ( $plugin[ 'type' ] == 'update' ) {
						$compare = version_compare( $plugin[ 'version' ], implode( '.', SPFactory::CmsHelper()->myVersion() ) );
						if ( $compare <= 0 ) {
							$plugin[ 'installed' ] = -1;
							$eid = $eid . '.disabled';
							$plugin[ 'action' ] = [ 'text' => Sobi::Txt( 'EX.APP_UPDATE_DISABLED' ), 'class' => 'disabled' ];
						}
						else {
							$plugin[ 'installed' ] = -3;
							$plugin[ 'action' ] = [ 'text' => Sobi::Txt( 'EX.UPDATE_CORE' ), 'class' => 'update' ];
						}
					}
					$plugin[ 'pid' ] = $eid;
					$plugin[ 'eid' ] = $plugin[ 'repository' ] . '.' . $plugin[ 'type' ] . '.' . $plugin[ 'pid' ];
					$list[ $eid ] = $plugin;
					$index = in_array( $plugin[ 'type' ], [ 'application', 'field', 'update', 'template', 'language' ] ) ? $plugin[ 'type' ] . 's' : 'others';
					$apps[ $index ][] = $plugin;
				}
				if ( isset( $apps[ 'updates' ] ) ) {
					usort( $apps[ 'updates' ], function ( $from, $to ) {
						return version_compare( $to[ 'version' ], $from[ 'version' ] ) > 0;
					} );
				}
			}
		}
		$repos = [];
		$dir =& SPFactory::Instance( 'base.fs.directory', SPLoader::dirPath( 'etc.repos' ) );
		$xml = array_keys( $dir->searchFile( 'repository.xml', false, 2 ) );
		foreach ( $xml as $def ) {
			$repository = SPFactory::Instance( 'services.installers.repository' );
			$repository->loadDefinition( $def );
			$repos[] = $repository->getDef();
		}
		$menu = $this->menu();
		$view->assign( $this->_task, 'task' )
				->assign( $menu, 'menu' )
				->assign( $apps, 'extensions' )
				->assign( $repos, 'repositories' )
				->assign( $list, 'full-list' )
				->determineTemplate( 'extensions', $this->_task );
		Sobi::Trigger( $this->_task, $this->name(), [ &$view ] );
		$view->display();
		Sobi::Trigger( 'After' . ucfirst( $this->_task ), $this->name(), [ &$view ] );
	}

	private function delete()
	{
		$application = SPRequest::cmd( 'eid' );
		if ( !( strlen( $application ) ) ) {
			$this->response( Sobi::Url( 'extensions.installed' ), Sobi::Txt( 'EX.SELECT_TO_DELETE_ERR' ), true, SPC::ERROR_MSG );
		}
		$application = explode( '.', $application );
		$appType = $application[ 0 ];
		$application = $application[ 1 ];
		$def = SPLoader::path( "etc.installed.{$appType}s.{$application}", 'front', true, 'xml' );
		if ( !( $def ) ) {
			Sobi::Error( 'extensions', SPLang::e( 'CANNOT_DELETE_PLUGIN_FILE_NOT_EXISTS', SPLoader::path( "etc.installed.{$appType}s.{$application}", 'front', false, 'xml' ) ), SPC::WARNING, 0, __LINE__, __FILE__ );
			$this->response( Sobi::Url( 'extensions.installed' ), Sobi::Txt( 'EX.CANNOT_LOAD_PLUGIN_DEF_ERR' ), true, SPC::ERROR_MSG );
		}
		$installer = SPFactory::Instance( 'services.installers.sobiproapp', $def, 'SobiProApp' );
		$this->response( Sobi::Url( 'extensions.installed' ), $installer->remove(), true, SPC::SUCCESS_MSG );
	}

	protected function publish( $state )
	{
		exit( 'Deprecated ' . __FILE__ . ' ' . __LINE__ );
//		$plugin = SPRequest::cmd( 'plid' );
//		$plugin = explode( '.', $plugin );
//		$ptype = $plugin[ 0 ];
//		$plugin = $plugin[ 1 ];
//
//		if ( SPRequest::sid( 'get' ) ) {
//			try {
//				SPFactory::db()->replace( 'spdb_plugin_section', array( 'section' => SPRequest::sid( 'get' ), 'pid' => $plugin, 'type' => $ptype, 'enabled' => $state, 0 ) );
//			} catch ( SPException $x ) {
//				Sobi::Error( 'extensions', SPLang::e( 'CANNOT_UPDATE_PLUGIN', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
//				Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'EX.CANNOT_CHANGE_STATE_ERR', 'error' ), true );
//			}
//		}
//		else {
//			try {
//				SPFactory::db()->update( 'spdb_plugins', array( 'enabled' => $state ), array( 'type' => $ptype, 'pid' => $plugin ) );
//			} catch ( SPException $x ) {
//				Sobi::Error( 'extensions', SPLang::e( 'CANNOT_UPDATE_PLUGIN', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
//				Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'EX.CANNOT_CHANGE_STATE_ERR', 'error' ), true );
//			}
//		}
//		Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'EX.PLUGIN_STATE_CHANGED' ) );
	}

	protected function toggle()
	{
		$plugin = SPRequest::cmd( 'eid' );
		$plugin = explode( '.', $plugin );
		$ptype = $plugin[ 0 ];
		$plugin = $plugin[ 1 ];
		$message = null;

		if ( SPRequest::sid() ) {
			try {
				$app = SPFactory::db()
						->select( 'name', 'spdb_plugins', [ 'pid' => $plugin, 'type' => $ptype, ] )
						->loadResult();
				$state = !( SPFactory::db()
						->select( 'enabled', 'spdb_plugin_section', [ 'section' => SPRequest::sid( 'get' ), 'pid' => $plugin, 'type' => $ptype, ] )
						->loadResult() );
				SPFactory::db()
						->replace( 'spdb_plugin_section', [ 'section' => SPRequest::sid( 'get' ), 'pid' => $plugin, 'type' => $ptype, 'enabled' => $state, 0 ] );
				$message = $state ? Sobi::Txt( 'EX.APP_ENABLED', $app ) : Sobi::Txt( 'EX.APP_DISABLED', $app );
				$messageType = $state ? 'success' : 'warning';
			} catch ( SPException $x ) {
				$message = Sobi::Txt( 'EX.CANNOT_CHANGE_STATE_ERR', 'error' );
				$messageType = 'error';
				Sobi::Error( 'extensions', SPLang::e( 'CANNOT_UPDATE_PLUGIN', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		else {
			try {
				$app = SPFactory::db()
						->select( [ 'enabled', 'name' ], 'spdb_plugins', [ 'pid' => $plugin, 'type' => $ptype, ] )
						->loadObject();
				SPFactory::db()
						->update( 'spdb_plugins', [ 'enabled' => !( $app->enabled ) ], [ 'type' => $ptype, 'pid' => $plugin ] );
				$message = !( $app->enabled ) ? Sobi::Txt( 'EX.APP_ENABLED', $app->name ) : Sobi::Txt( 'EX.APP_DISABLED', $app->name );
				$messageType = !( $app->enabled ) ? 'success' : 'warning';
			} catch ( SPException $x ) {
				$message = Sobi::Txt( 'EX.CANNOT_CHANGE_STATE_ERR', 'error' );
				$messageType = 'error';
				Sobi::Error( 'extensions', SPLang::e( 'CANNOT_UPDATE_PLUGIN', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		$this->response( Sobi::Back(), $message, false, $messageType );
	}

	private function registerRepo()
	{
		$repo = trim( preg_replace( '/[^a-zA-Z0-9\.\-\_]/', null, SPRequest::string( 'repository' ) ) );
		$data = SPRequest::arr( 'RepositoryResponse' );
		$answer = [];
		if ( count( $data ) ) {
			foreach ( $data as $k => $v ) {
				$v = ( strlen( $v ) && $v != '' ) ? $v : SPC::NO_VALUE;
				$answer[ $k ] = $v;
			}
		}
		$defFile = SPLoader::path( "etc.repos.{$repo}.repository", 'front', true, 'xml' );
		$repository = SPFactory::Instance( 'services.installers.repository' );
		$repository->loadDefinition( $defFile );
		try {
			$repository->connect();
		} catch ( SPException $x ) {
			$this->ajaxResponse( true, SPLang::e( 'REPO_ERR', $x->getMessage() ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG );
		}
		$callback = SPRequest::word( 'callback' );
		$response = call_user_func_array( [ $repository, $callback ], $answer );
		if ( is_array( $response ) && isset( $response[ 'callback' ] ) ) {
			return $this->parseSoapRequest( $response, $repo );
		}
		elseif ( $response === true || isset( $response[ 'welcome_msg' ] ) ) {
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			if ( isset( $response[ 'token' ] ) ) {
				$repository->saveToken( $response[ 'token' ] );
			}
			if ( isset( $response[ 'welcome_msg' ] ) && $response[ 'welcome_msg' ] ) {
				echo json_encode( [ 'message' => [ 'type' => SPC::SUCCESS_MSG, 'response' => $response[ 'welcome_msg' ] ], 'callback' => null, 'redirect' => true ] );
			}
			else {
				echo json_encode( [ 'message' => [ 'type' => SPC::SUCCESS_MSG, 'response' => Sobi::Txt( 'EX.REPO_HAS_BEEN_ADDED', [ 'location' => $repo ] ) ], 'callback' => null, 'redirect' => true ] );
			}
			exit;
		}
		else {
			if ( isset( $response[ 'error' ] ) ) {
				$this->ajaxResponse( true, SPLang::e( 'REPO_ERR', $response[ 'msg' ] ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG, false );
			}
			else {
				$this->ajaxResponse( true, SPLang::e( 'UNKNOWN_ERR' ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG, false );
			}
		}
	}

	private function parseSoapRequest( $response, $repositoryId = null, $appId = null )
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			$this->response( Sobi::Url( 'extensions.browse' ), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), false, SPC::ERROR_MSG );
			exit;
		}
		/** @var $view SPExtensionsView */
		$view = SPFactory::View( 'extensions', true );
		$callback = $response[ 'callback' ];
		unset( $response[ 'callback' ] );
		if ( isset( $response[ 'message' ] ) ) {
			$message = Sobi::Txt( $response[ 'message' ] );
			$view->assign( $message, 'message' );
			unset( $response[ 'message' ] );
		}
		$fields = [];
		foreach ( $response as $values ) {
			if ( isset( $values[ 'params' ][ 'style' ] ) ) {
				unset( $values[ 'params' ][ 'style' ] );
			}
			if ( isset( $values[ 'params' ][ 'class' ] ) ) {
				unset( $values[ 'params' ][ 'class' ] );
			}
			$values[ 'name' ] = 'RepositoryResponse[' . $values[ 'params' ][ 'id' ] . ']';
			$fields[] = $values;
		}
		$fields[] = [ 'label' => 'Website URL', 'value' => Sobi::Cfg( 'live_site' ), 'name' => 'RepositoryResponse[url]', 'type' => 'text', 'required' => true, 'params' => [ 'id' => 'url', /*'size' => 30,*/
				'readonly' => 'readonly' ] ];
		$request = [ 'fields' => $fields ];
		$view->assign( $request, 'request' );
		$view->determineTemplate( 'extensions', 'soap-request' );
		ob_start();
		$view->display();
		$response = ob_get_contents();
		$response = str_replace( 'id="SobiPro"', 'id="SpRepoModal"', $response );
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		if ( $repositoryId ) {
			echo json_encode( [ 'message' => [ 'type' => SPC::INFO_MSG, 'response' => $response ], 'repository' => $repositoryId, 'callback' => $callback ] );
		}
		else {
			echo json_encode( [ 'message' => [ 'type' => SPC::INFO_MSG, 'response' => $response ], 'extension' => $appId, 'callback' => $callback ] );
		}
		exit;
	}

	private function confirmRepo()
	{
		$repositoryId = trim( preg_replace( '/[^a-zA-Z0-9\.\-\_]/', null, SPRequest::string( 'repository' ) ) );
		$connection = SPFactory::Instance( 'services.remote' );
		$def = "https://{$repositoryId}/repository.xml";
		$connection->setOptions(
				[
						'url' => $def,
						'connecttimeout' => 10,
						'header' => false,
						'returntransfer' => true,
						'ssl_verifypeer' => false,
						'ssl_verifyhost' => 2,
				]
		);
		$path = SPLoader::path( 'etc.repos.' . str_replace( '.', '_', $repositoryId ), 'front', false, 'xml' );
		$file = SPFactory::Instance( 'base.fs.file', $path );
		$info = $connection->exec();
		$connectionInfo = $connection->info();
		if ( isset( $connectionInfo[ 'http_code' ] ) && $connectionInfo[ 'http_code' ] != 200 ) {
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			$response = SPLang::e( 'Error (%d) has occurred and the repository at "%s" could not be added.', $connectionInfo[ 'http_code' ], "https://{$repositoryId}" );
			echo json_encode( [ 'message' => [ 'type' => SPC::ERROR_MSG, 'text' => $response ] ] );
			exit;
//			$this->ajaxResponse( true, SPLang::e( 'Error (%d) has occurred and the repository at "%s" could not be added.', $connectionInfo[ 'http_code' ], "https://{$repositoryId}" ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG );
		}
		else {
			$def = new DOMDocument( '1.0' );
			$rDef = new DOMDocument( '1.0' );
			$def->load( $path );
			$rDef->loadXML( $info );
			if ( !( $rDef->schemaValidate( $this->repoSchema() ) ) ) {
				$this->ajaxResponse( true, SPLang::e( 'SCHEME_ERR', "https://{$repositoryId}/repository.xml", "https://xml.sigsiu.net/SobiPro/repository.xsd" ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG );
			}
			$arrDef = SPFactory::Instance( 'types.array' );
			$arrDef = $arrDef->fromXML( $def, 'repository' );
			$remoteDefinition = SPFactory::Instance( 'types.array' );
			$remoteDefinition = $remoteDefinition->fromXML( $rDef, 'repository' );
			$repoDef = [];
			$repoDef[ 'name' ] = $remoteDefinition[ 'repository' ][ 'name' ];
			$repoDef[ 'id' ] = $remoteDefinition[ 'repository' ][ 'id' ];
			$repoDef[ 'url' ] = $arrDef[ 'repository' ][ 'url' ] . '/' . $remoteDefinition[ 'repository' ][ 'repositoryLocation' ];
			$repoDef[ 'certificate' ] = $arrDef[ 'repository' ][ 'certificate' ];
			$repoDef[ 'description' ] = $remoteDefinition[ 'repository' ][ 'description' ];
			$repoDef[ 'maintainer' ] = $remoteDefinition[ 'repository' ][ 'maintainer' ];
			$file->delete();
			$dir = SPLoader::dirPath( 'etc.repos.' . str_replace( '.', '_', $repoDef[ 'id' ] ), 'front', false );
			try {
				if ( Sobi::Cfg( 'ftp_mode' ) ) {
					SPFs::mkdir( $dir, 0777 );
				}
				else {
					SPFs::mkdir( $dir );
				}
			} catch ( SPException $x ) {
				return $this->ajaxResponse( true, $x->getMessage(), false, SPC::ERROR_MSG );
			}

			$path = $dir . '/repository.xml';
			$file = SPFactory::Instance( 'base.fs.file', $path );
			$def = SPFactory::Instance( 'types.array' );
			$file->content( $def->toXML( $repoDef, 'repository' ) );
			$file->save();
			$repository = SPFactory::Instance( 'services.installers.repository' );
			$repository->loadDefinition( $file->fileName() );
			try {
				$repository->connect();
			} catch ( SPException $x ) {
				$this->ajaxResponse( true, SPLang::e( 'REPO_ERR', $x->getMessage() ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG );
			}
			$response = $repository->register();
			if ( is_array( $response ) && isset( $response[ 'callback' ] ) ) {
				return $this->parseSoapRequest( $response, $repoDef[ 'id' ] );
			}
			elseif ( $response === true || isset( $response[ 'welcome_msg' ] ) ) {
				if ( isset( $response[ 'welcome_msg' ] ) && $response[ 'welcome_msg' ] ) {
					$this->ajaxResponse( true, Sobi::Txt( 'EX.REPO_HAS_BEEN_ADDED_WITH_MSG', [ 'location' => $repositoryId, 'msg' => $response[ 'welcome_msg' ] ] ), Sobi::Url( 'extensions.browse' ), SPC::SUCCESS_MSG );
				}
				else {
					$this->ajaxResponse( true, Sobi::Txt( 'EX.REPO_HAS_BEEN_ADDED_WITH_MSG', [ 'location' => $repositoryId ] ), Sobi::Url( 'extensions.browse' ), SPC::SUCCESS_MSG );
				}
			}
			else {
				if ( isset( $response[ 'error' ] ) ) {
					$this->ajaxResponse( true, SPLang::e( 'REPO_ERR', $response[ 'msg' ] ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG );
				}
				else {
					$this->ajaxResponse( true, SPLang::e( 'UNKNOWN_ERR' ), Sobi::Url( 'extensions.browse' ), SPC::ERROR_MSG );
					exit;
				}
			}
		}
	}

	private function repoSchema()
	{
		$connection = SPFactory::Instance( 'services.remote' );
		$def = 'https://xml.sigsiu.net/SobiPro/repository.xsd';
		$connection->setOptions(
				[
						'url' => $def,
						'connecttimeout' => 10,
						'header' => false,
						'returntransfer' => true,
						'ssl_verifypeer' => false,
						'ssl_verifyhost' => 2,
				]
		);
		$schema =& SPFactory::Instance( 'base.fs.file', SPLoader::path( 'lib.services.installers.schemas.repository', 'front', false, 'xsd' ) );
		$file = $connection->exec();
		$schema->content( $file );
		$schema->save();
		return $schema->filename();
	}

	private function delRepo()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			$this->response( Sobi::Url( 'extensions.browse' ), Sobi::Txt( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), true, SPC::ERROR_MSG );
		}
		$repository = SPRequest::cmd( 'repository' );
		if ( $repository ) {
			if ( SPFs::rmdir( SPLoader::dirPath( 'etc.repos.' . $repository ) ) ) {
				$this->response( Sobi::Url( 'extensions.browse' ), Sobi::Txt( 'EX.REPOSITORY_DELETED' ), true, SPC::SUCCESS_MSG );
			}
			else {
				$this->response( Sobi::Url( 'extensions.browse' ), Sobi::Txt( 'EX.DEL_REPO_ERROR' ), true, SPC::ERROR_MSG );
			}
		}
	}

	private function addRepo()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			$this->response( Sobi::Url( 'extensions.browse' ), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), false, SPC::ERROR_MSG );
			exit;
		}
		$connection = SPFactory::Instance( 'services.remote' );
		$repo = trim( preg_replace( '/[^a-zA-Z0-9\.\-\_]/', null, SPRequest::string( 'repository' ) ) );
		$ssl = $connection->certificate( $repo );
		if ( isset( $ssl[ 'err' ] ) ) {
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			//$response = sprintf( 'The connection could not be validated (error number %s). %s', $ssl[ 'err' ], $ssl[ 'msg' ] );
			$response = SPLang::e( 'NOT_VALIDATED', $ssl[ 'err' ], $ssl[ 'msg' ] );
			echo json_encode( [ 'message' => [ 'type' => SPC::ERROR_MSG, 'text' => $response ] ] );
			exit;
		}
		else {
			$cert = [];
			$file = SPFactory::Instance( 'base.fs.file', SPLoader::path( 'etc.repos.' . str_replace( '.', '_', $repo ), 'front', false, 'xml' ) );
			$cert[ 'url' ] = 'https://' . $repo;
			$cert[ 'certificate' ][ 'serialNumber' ] = $ssl[ 'serialNumber' ];
			$cert[ 'certificate' ][ 'validFrom' ] = Sobi::Date( $ssl[ 'validFrom_time_t' ] );
			$cert[ 'certificate' ][ 'validTo' ] = Sobi::Date( $ssl[ 'validTo_time_t' ] );
			$cert[ 'certificate' ][ 'subject' ] = $ssl[ 'subject' ];
			$cert[ 'certificate' ][ 'issuer' ] = $ssl[ 'issuer' ];
			$cert[ 'certificate' ][ 'hash' ] = $ssl[ 'hash' ];
			$def = SPFactory::Instance( 'types.array', $cert );
			$file->content( $def->toXML( $cert, 'repository' ) );
			$file->save();
			/** @var $view SPExtensionsView */
			$view =& SPFactory::View( 'extensions', true )
					->assign( $this->_task, 'task' )
					->assign( $cert[ 'certificate' ], 'certificate' )
					->determineTemplate( 'extensions', 'certificate' );
			ob_start();
			$view->display();
			$response = ob_get_contents();
			$response = str_replace( 'id="SobiPro"', 'id="SpRepoModal"', $response );
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			echo json_encode( [ 'message' => [ 'type' => SPC::INFO_MSG, 'response' => $response ] ] );
			exit;
		}
	}

	private function repos()
	{
		Sobi::Redirect( Sobi::Url( 'extensions.browse' ), null, null, true );
//		$repos = array();
//		$dir =& SPFactory::Instance( 'base.fs.directory', SPLoader::dirPath( 'etc.repos' ) );
//		$xml = array_keys( $dir->searchFile( 'repository.xml', false, 2 ) );
//		foreach ( $xml as $rdef ) {
//			$repository = SPFactory::Instance( 'services.installers.repository' );
//			$repository->loadDefinition( $rdef );
//			$repos[ ] = $repository->getDef();
//		}
//		$view =& SPFactory::View( 'extensions', true );
//		$view->assign( $this->_task, 'task' );
//		$view->loadConfig( 'extensions.' . $this->_task );
//		$view->setTemplate( 'extensions.' . $this->_task );
//		$view->assign( $this->menu(), 'menu' );
//		$view->assign( $repos, 'repositories' );
//		Sobi::Trigger( $this->_task, $this->name(), array( &$view ) );
//		$view->display();
//		Sobi::Trigger( 'After' . ucfirst( $this->_task ), $this->name(), array( &$view ) );

	}

	private function install( $file = null )
	{
		$arch = SPFactory::Instance( 'base.fs.archive' );
		$ajax = strlen( Input::Cmd( 'ident', 'post', null ) );
		if ( !( $file ) && Input::String( 'root' ) ) {
			$file = str_replace( '.xml', null, Input::String( 'root' ) );
			$file = SPLoader::path( 'tmp.install.' . $file, 'front', true, 'xml' );
		}
		if ( !( $file ) ) {
			$ident = Input::Cmd( 'ident', 'post', null );
			$data = SPRequest::file( $ident );
			$name = str_replace( [ '.' . SPFs::getExt( $data[ 'name' ] ), '.' ], null, $data[ 'name' ] );
			$path = SPLoader::dirPath( 'tmp.install.' . $name, 'front', false );
			$c = 0;
			while ( FileSystem::Exists( $path ) ) {
				$path = SPLoader::dirPath( 'tmp.install.' . $name . '_' . ++$c, 'front', false );
			}
			/** temp directory - will be removed later but it needs to be writable for apache and Joomla! fs (FTP mode)*/
			try {
				if ( Sobi::Cfg( 'ftp_mode' ) ) {
					FileSystem::Mkdir( $path, 0777 );
				}
				else {
					FileSystem::Mkdir( $path );
				}
			} catch ( SPException $x ) {
				return $this->ajaxResponse( $ajax, $x->getMessage(), false, SPC::ERROR_MSG );
			}
			$file = $path . '/' . $data[ 'name' ];
			try {
				$arch->upload( $data[ 'tmp_name' ], $file );
			} catch ( SPException $x ) {
				return $this->ajaxResponse( $ajax, $x->getMessage(), false, SPC::ERROR_MSG );
			}
		}
		// force update
		elseif ( Input::String( 'root' ) && $file ) {
			$path = dirname( $file );
		}
		else {
			$arch->setFile( $file );
			$name = str_replace( [ '.' . FileSystem::GetExt( $file ), '.' ], null, basename( $file ) );
			$path = SPLoader::dirPath( 'tmp.install.' . $name, 'front', false );
			$c = 0;
			while ( FileSystem::Exists( $path ) ) {
				$path = SPLoader::dirPath( 'tmp.install.' . $name . '_' . ++$c, 'front', false );
			}
			/*
			 * temp directory - will be removed later but it needs to  writable for apache and Joomla! fs (FTP mode)
			 */
			try {
				if ( Sobi::Cfg( 'ftp_mode' ) ) {
					FileSystem::Mkdir( $path, 0777 );
				}
				else {
					FileSystem::Mkdir( $path );
				}
			} catch ( SPException $x ) {
				return $this->ajaxResponse( $ajax, $x->getMessage(), false, SPC::ERROR_MSG );
			}

		}
		if ( $path ) {
			if ( !( Input::String( 'root' ) ) ) {
				if ( !( $arch->extract( $path ) ) ) {
					return $this->ajaxResponse( $ajax, SPLang::e( 'CANNOT_EXTRACT_ARCHIVE', basename( $file ), $path ), false, SPC::ERROR_MSG );
				}
			}
			$dir = new Directory( $path );
			$xml = array_keys( $dir->searchFile( '.xml', false, 2 ) );
			if ( !( count( $xml ) ) ) {
				return $this->ajaxResponse( $ajax, SPLang::e( 'NO_INSTALL_FILE_IN_PACKAGE' ), false, SPC::ERROR_MSG );
			}
			$definition = $this->searchInstallFile( $xml );
			if ( !( $definition ) ) {
				if ( SPFactory::CmsHelper()->installerFile( $xml ) ) {
					try {
						$message = SPFactory::CmsHelper()->install( $xml, $path );
						return $this->ajaxResponse( $ajax, $message[ 'msg' ], $ajax, $message[ 'msgtype' ] );
					} catch ( SPException $x ) {
						return $this->ajaxResponse( $ajax, $x->getMessage(), $ajax, SPC::ERROR_MSG );
					}
				}
				else {
					return $this->ajaxResponse( $ajax, SPLang::e( 'NO_INSTALL_FILE_IN_PACKAGE' ), false, SPC::ERROR_MSG );
				}
			}
			/** @var $installer SPInstaller */
			$installer =& SPFactory::Instance( 'services.installers.' . trim( strtolower( $definition->documentElement->tagName ) ), $xml[ 0 ], trim( $definition->documentElement->tagName ) );
			try {
				$installer->validate();
				$msg = $installer->install();
				if ( SPLoader::path( 'tmp.message', 'front', true, 'json' ) ) {
					FileSystem::Delete( SPLoader::path( 'tmp.message', 'front', true, 'json' ) );
				}
				if ( SPLoader::path( 'etc.updates', 'front', true, 'xml' ) ) {
					FileSystem::Delete( SPLoader::path( 'etc.updates', 'front', true, 'xml' ) );
				}
				return $this->ajaxResponse( $ajax, $msg, true, SPC::SUCCESS_MSG );
			} catch ( SPException $x ) {
				return $this->ajaxResponse( $ajax, $x->getMessage(), false, SPC::ERROR_MSG );
			}
		}
		else {
			return $this->ajaxResponse( $ajax, SPLang::e( 'NO_FILE_HAS_BEEN_UPLOADED' ), false, SPC::ERROR_MSG );
		}
	}

	protected function ajaxResponse( $ajax, $message, $redirect, $type, $callback = 'SPExtensionInstaller' )
	{
		if ( $ajax ) {
			if ( $redirect ) {
				SPFactory::message()->setMessage( $message, false, $type );
			}
			$response = [
					'type' => $type,
					'text' => $message,
					'redirect' => $redirect ? Sobi::Url( 'extensions.installed' ) : false,
					'callback' => $type == SPC::SUCCESS_MSG ? $callback : false
			];
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			echo json_encode( $response );
			exit;
		}
		elseif ( $redirect ) {
			SPFactory::message()->setMessage( $message, false, $type );
			Sobi::Redirect( Sobi::Url( 'extensions.installed' ) );
		}
		else {
			return [ 'msg' => $message, 'msgtype' => $type ];
		}
	}

	private function searchInstallFile( &$xml )
	{
		foreach ( $xml as $file ) {
			$def = new DOMDocument();
			$def->load( $file );
			if ( in_array( trim( $def->documentElement->tagName ), [ 'template', 'SobiProApp' ] ) ) {
				$xml = [ $file ];
				return $def;
			}
		}
		return false;
	}

	private function menu()
	{
		/* create menu */
		$menu = SPFactory::Instance( 'views.adm.menu', 'extensions.' . $this->_task );
		$cfg = SPLoader::loadIniFile( 'etc.adm.config_menu' );
		Sobi::Trigger( 'Create', 'AdmMenu', [ &$cfg ] );
		if ( count( $cfg ) ) {
			foreach ( $cfg as $section => $keys ) {
				if ( ( $section == 'GB.CFG.GLOBAL_CONFIG' || $section == 'GB.ACL' ) && !( Sobi::Can( 'cms.admin' ) ) ) {
					continue;
				}
				$menu->addSection( $section, $keys );
			}
		}
		$menu->addCustom( 'GB.CFG.GLOBAL_TEMPLATES', $this->listTemplates() );
		Sobi::Trigger( 'AfterCreate', 'AdmMenu', [ &$menu ] );
		return $menu;
	}

	private function installed()
	{
		$list = [];
		try {
			SPFactory::db()->select( '*', 'spdb_plugins' );
			$list = SPFactory::db()->loadAssocList();
		} catch ( SPException $x ) {
		}
		$cl = count( $list );
		for ( $i = 0; $i < $cl; $i++ ) {
			$list[ $i ][ 'locked' ] = SPLoader::path( "etc.installed.{$list[$i]['type']}s.{$list[$i]['pid']}", 'front', true, 'xml' ) ? false : true;
			$list[ $i ][ 'eid' ] = $list[ $i ][ 'type' ] . '.' . $list[ $i ][ 'pid' ];
			if ( ( $list[ $i ][ 'pid' ] == 'router' ) || ( in_array( $list[ $i ][ 'type' ], [ 'field', 'language', 'module', 'plugin' ] ) ) ) {
				$list[ $i ][ 'enabled' ] = -1;
			}
		}
		/** @var $view SPExtensionsView */
		$view = SPFactory::View( 'extensions', true );
		$menu = $this->menu();
		$view->assign( $this->_task, 'task' )
				->assign( $menu, 'menu' )
				->assign( $list, 'applications' )
				->determineTemplate( 'extensions', $this->_task );
		Sobi::Trigger( $this->_task, $this->name(), [ &$view ] );
		$view->display();
		Sobi::Trigger( 'After' . ucfirst( $this->_task ), $this->name(), [ &$view ] );
	}
}
