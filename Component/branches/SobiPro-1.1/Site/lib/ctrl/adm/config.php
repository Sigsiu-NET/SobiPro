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
 * @created 10-Jan-2009 4:38:03 PM
 */
class SPConfigAdmCtrl extends SPController
{
	/**
	 * @var string
	 */
	protected $_type = 'config';
	/**
	 * @var string
	 */
	protected $_defTask = 'general';

	public function __construct()
	{
		$registry =& SPFactory::registry();
		$registry->loadDBSection( 'config' );
		parent::__construct();
	}

	/**
	 */
	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		if ( !( Sobi::Reg( 'current_section' ) ) && $this->_task == 'general' ) {
			$this->_task = 'global';
		}
		switch ( $this->_task ) {
			case 'clean':
				SPFactory::cache()->cleanSection();
				$this->response( Sobi::Back(), Sobi::Txt( 'MSG.CACHE_CLEANED' ), false, SPC::SUCCESS_MSG );
				break;
			case 'saveOrdering':
				$this->saveDefaultOrdering();
				break;
			case 'crawler':
				$this->crawler();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !( parent::execute() ) && !( $this->view() ) ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				else {
					return true;
				}
				break;
		}
	}

	/**
	 * @return bool
	 */
	protected function crawler()
	{
		/** @var $view SPAdmView */
		$view = $this->getView( 'config.' . $this->_task );
		$view->setCtrl( $this );
		$view->determineTemplate( 'config', $this->_task );
		$view->display();
		Sobi::Trigger( 'After' . ucfirst( $this->_task ), $this->name(), array( &$view ) );
		return true;
	}

	protected function saveDefaultOrdering()
	{
		$target = SPRequest::cmd( 'target' );
		$order = Sobi::GetUserState( $target . '.order', null );
		SPFactory::config()->saveCfg( 'admin.' . $target . '-order', $order );
		$limit = Sobi::GetUserState( $target . '.limit', 10 );
		SPFactory::config()->saveCfg( 'admin.' . $target . '-limit', $limit );
		$this->response( Sobi::Back(), Sobi::Txt( 'MSG_DEFAULT_ORDERING_SAVED' ), false );
	}

	/**
	 * @param string
	 * @return SPConfigAdmView
	 */
	protected function getView( $task )
	{
		SPLoader::loadClass( 'html.input' );
		$sid = Sobi::Reg( 'current_section' );
		/* create menu */
		$class = SPLoader::loadClass( 'views.adm.menu' );
		$menu = new $class( $task, $sid );
		/* load the menu definition */
		if ( $sid ) {
			$cfg = SPLoader::loadIniFile( 'etc.adm.section_menu' );
		}
		else {
			$cfg = SPLoader::loadIniFile( 'etc.adm.config_menu' );
		}
		Sobi::Trigger( 'Create', 'AdmMenu', array( &$cfg ) );
		if ( count( $cfg ) ) {
			foreach ( $cfg as $section => $keys ) {
				$menu->addSection( $section, $keys );
			}
		}
		Sobi::Trigger( 'AfterCreate', 'AdmMenu', array( &$menu ) );
		if ( $sid ) {
			if ( Sobi::Cfg( 'section.template' ) == SPC::DEFAULT_TEMPLATE && strstr( SPRequest::task(), 'config' ) ) {
				SPFactory::message()
						->warning( Sobi::Txt( 'TP.DEFAULT_WARN', 'http://sobipro.sigsiu.net/help_screen/template.info' ), false )
						->setSystemMessage();
			}
			/* create new SigsiuTree */
			$tree = SPLoader::loadClass( 'mlo.tree' );
			$tree = new $tree( Sobi::GetUserState( 'categories.order', 'corder', 'position.asc' ) );
			/* set link */
			$tree->setHref( Sobi::Url( array( 'sid' => '{sid}' ) ) );
			$tree->setId( 'menuTree' );
			/* set the task to expand the tree */
			$tree->setTask( 'category.expand' );
			$tree->init( $sid );
			/* add the tree into the menu */
			$menu->addCustom( 'AMN.ENT_CAT', $tree->getTree() );
			$seClass = SPLoader::loadModel( 'section' );
			$cSec = new $seClass();
			$cSec->init( $sid );
		}
		else {
			$cSec = array( 'name' => Sobi::Txt( 'GB.CFG.GLOBAL_CONFIGURATION' ) );
			$menu->addCustom( 'GB.CFG.GLOBAL_TEMPLATES', $this->listTemplates() );
		}
		$view = SPFactory::View( 'config', true );
		$view->assign( $task, 'task' );
		$view->assign( $cSec, 'section' );
		$view->assign( $menu, 'menu' );
		$view->addHidden( SPFactory::registry()->get( 'current_section' ), 'sid' );
		return $view;
	}

	/**
	 * @return bool
	 */
	protected function view()
	{
		Sobi::ReturnPoint();
		/** @var $view SPAdmView */
		$view = $this->getView( 'config.' . $this->_task );
		$view->setCtrl( $this );
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		if ( $this->_task == 'general' ) {
			$this->checkTranslation();
			$fields = $this->getNameFields();
			$nameFields = array();
			if ( count( $fields ) ) {
				foreach ( $fields as $field ) {
					$nameFields[ $field->get( 'fid' ) ] = $field->get( 'name' );
				}
			}
			$alphaFields = array();
			$fields = $this->getNameFields( true, Sobi::Cfg( 'alphamenu.field_types' ) );
			if ( count( $fields ) ) {
				if ( count( $fields ) ) {
					foreach ( $fields as $field ) {
						$alphaFields[ $field->get( 'fid' ) ] = $field->get( 'name' );
					}
				}
			}
			$templateList = $view->templatesList();
			$entriesOrdering = $view->namesFields( null, true );
			$view->assign( $nameFields, 'nameFields' );
			$view->assign( $templateList, 'templatesList' );
			$view->assign( $entriesOrdering, 'entriesOrdering' );
			$view->assign( $alphaFields, 'alphaMenuFields' );
			$view->assign( $view->languages(), 'languages-list' );
		}
		$view->addHidden( $IP, 'current-ip' );
		Sobi::Trigger( $this->_task, $this->name(), array( &$view ) );
		$view->determineTemplate( 'config', $this->_task );
		$view->display();
		Sobi::Trigger( 'After' . ucfirst( $this->_task ), $this->name(), array( &$view ) );
		return true;
	}

	protected function listTemplates( $tpl = null, $cmsOv = true )
	{
		SPFactory::header()
				->addJsFile( 'dtree' )
				->addCssFile( 'dtree', true );
		SPLoader::loadClass( 'base.fs.directory_iterator' );
		$ls = Sobi::Cfg( 'live_site' ) . 'media/sobipro/tree';
		$nodes = null;
		$count = 0;
		$tpl = Sobi::FixPath( $tpl ? $tpl : SPLoader::dirPath( 'usr.templates' ) );
		if ( Sobi::Section() ) {
			$realName = Sobi::Txt( 'TP.INFO' );
			$iTask = Sobi::Url( array( 'task' => 'template.info', 'template' => basename( $tpl ), 'sid' => Sobi::Section() ) );
			$nodes .= "spTpl.add( -123, 0,'{$realName}','{$iTask}', '', '', '{$ls}/info.png' );\n";
		}
		$this->traveTpl( new SPDirectoryIterator( $tpl ), $nodes, 0, $count );
		if ( $cmsOv ) {
			$cms = SPFactory::CmsHelper()->templatesPath();
			if ( is_array( $cms ) && isset( $cms[ 'name' ] ) && isset( $cms[ 'data' ] ) && is_array( $cms[ 'data' ] ) && count( $cms[ 'data' ] ) ) {
				$count++;
				if ( isset( $cms[ 'icon' ] ) ) {
					$nodes .= "spTpl.add( {$count}, 0, '{$cms['name']}', '', '', '', '{$cms['icon']}', '{$cms['icon']}' );\n";
				}
				else {
					$nodes .= "spTpl.add( {$count}, 0, '{$cms['name']}' );\n";
				}
				$current = $count;
				foreach ( $cms[ 'data' ] as $name => $path ) {
					$count++;
					$nodes .= "spTpl.add( {$count}, {$current},'{$name}' );\n";
					$this->traveTpl( new SPDirectoryIterator( $path ), $nodes, $count, $count, true );
				}
			}
		}
		if ( Sobi::Section() ) {
			$file = SPLoader::path( 'usr.templates.' . Sobi::Cfg( 'section.template', SPC::DEFAULT_TEMPLATE ) . '.template', 'front', true, 'xml' );
			$def = new DOMDocument();
			$def->load( $file );
			$xdef = new DOMXPath( $def );
			$t = $xdef->query( '/template/name' )->item( 0 )->nodeValue;
		}
		else {
			$t = Sobi::Txt( 'GB.TEMPLATES' );
		}
		SPFactory::header()->addJsCode( "
			icons = {
						root : '{$ls}/base.gif',
						folder : '{$ls}/folder.gif',
						folderOpen : '{$ls}/folderopen.gif',
						node : '{$ls}/page.gif',
						empty : '{$ls}/empty.gif',
						line : '{$ls}/line.gif',
						join : '{$ls}/join.gif',
						joinBottom : '{$ls}/joinbottom.gif',
						plus : '{$ls}/plus.gif',
						plusBottom : '{$ls}/plusbottom.gif',
						minus : '{$ls}/minus.gif',
						minusBottom	: '{$ls}/minusbottom.gif',
						nlPlus : '{$ls}/nolines_plus.gif',
						nlMinus : '{$ls}/nolines_minus.gif'
			};
			var spTpl = new dTree( 'spTpl', icons );	\n
			SobiPro.jQuery( document ).ready( function ()
			{
				spTpl.add(0, -1, '{$t}' );\n
				{$nodes} \n
				try { document.getElementById( 'spTpl' ).innerHTML = spTpl } catch( e ) {}
			} );
		" );
		/** for some reason jQuery is not able to add the tree  */
		return "<div id=\"spTpl\"></div>";
	}

	/**
	 * @param $dir SPDirectoryIterator
	 * @param $nodes string
	 * @param $current int
	 * @param $count
	 * @param bool $package
	 * @return void
	 */
	private function traveTpl( $dir, &$nodes, $current, &$count, $package = false )
	{
		$ls = Sobi::FixPath( Sobi::Cfg( 'img_folder_live' ) . '/tree' );
		static $root = null;
		if ( !( $root ) ) {
			$root = new SPFile( SOBI_PATH );
		}
		foreach ( $dir as $file ) {
			$task = null;
			$fname = $file->getFilename();
			if ( $file->isDot() ) {
				continue;
			}
			$count++;
			if ( $file->isDir() ) {
				if ( $current == 0 || $package ) {
					if ( strstr( $file->getPathname(), $root->getPathname() ) ) {
						$fpath = str_replace( $root->getPathname() . '/usr/templates/', null, $file->getPathname() );
					}
					else {
						$fpath = 'cms:' . str_replace( SOBI_ROOT . DS, null, $file->getPathname() );
					}
					$fpath = str_replace( '/', '.', $fpath );
					$itask = Sobi::Url( array( 'task' => 'template.info', 'template' => $fpath ) );
					$nodes .= "spTpl.add( {$count}, {$current},'{$fname}','', '', '', '{$ls}/imgfolder.gif', '{$ls}/imgfolder.gif' );\n";
					if ( !( Sobi::Section() ) ) {
						$count2 = $count * -100;
						$fname = Sobi::Txt( 'TP.INFO' );
						$nodes .= "spTpl.add( {$count2}, {$count},'{$fname}','{$itask}', '', '', '{$ls}/info.png' );\n";
					}
				}
				else {
					$nodes .= "spTpl.add( {$count}, {$current},'{$fname}','');\n";
				}
				$this->traveTpl( new SPDirectoryIterator( $file->getPathname() ), $nodes, $count, $count );
			}
			else {
				$ext = SPFs::getExt( $fname );
				if ( in_array( $ext, array( 'htaccess', 'zip' ) ) || $fname == 'index.html' ) {
					continue;
				}
				switch ( strtolower( $ext ) ) {
					case 'php':
						$ico = $ls . '/php.png';
						break;
					case 'xml':
					case 'xsl':
						$ico = $ls . '/xml.png';
						break;
					case 'css':
						$ico = $ls . '/css.png';
						break;
					case 'jpg':
					case 'jpeg':
					case 'png':
					case 'bmp':
					case 'gif':
						$ico = $ls . '/img.png';
						$task = 'javascript:void(0);';
						break;
					default:
						$ico = $ls . '/page.gif';
				}
				if ( !( $task ) ) {
					if ( strstr( $file->getPathname(), $root->getPathname() ) ) {
						$fpath = str_replace( $root->getPathname() . '/usr/templates/', null, $file->getPathname() );
					}
					else {
						$fpath = 'cms:' . str_replace( SOBI_ROOT . DS, null, $file->getPathname() );
					}
					$fpath = str_replace( '/', '.', $fpath );
					if ( Sobi::Section() ) {
						$task = Sobi::Url( array( 'task' => 'template.edit', 'file' => $fpath, 'sid' => Sobi::Section() ) );
					}
					else {
						$task = Sobi::Url( array( 'task' => 'template.edit', 'file' => $fpath ) );
					}
				}
				$nodes .= "spTpl.add( {$count}, {$current},'{$fname}','{$task}', '', '', '{$ico}' );\n";
			}
		}
	}

	protected function & createMenu( $task = null )
	{
		if ( !( $task ) ) {
			$task = 'config.' . $this->_task;
		}
		/* load the menu definition */
		if ( Sobi::Section() ) {
			/* create menu */
			$menu =& SPFactory::Instance( 'views.adm.menu', $task, Sobi::Section() );
			$cfg = SPLoader::loadIniFile( 'etc.adm.section_menu' );
			/* create new SigsiuTree */
			$tree = SPFactory::Instance( 'mlo.tree' );
			/* set link */
			$tree->setHref( Sobi::Url( array( 'sid' => '{sid}' ) ) );
			$tree->setId( 'menuTree' );
			/* set the task to expand the tree */
			$tree->setTask( 'category.expand' );
			$tree->init( Sobi::Section() );
			/* add the tree into the menu */
			$menu->addCustom( 'AMN.ENT_CAT', $tree->getTree() );
			$seClass = SPLoader::loadModel( 'section' );
			$cSec = new $seClass();
			$cSec->init( Sobi::Section() );
		}
		else {
			$cfg = SPLoader::loadIniFile( 'etc.adm.config_menu' );
			/* create menu */
			$menu =& SPFactory::Instance( 'views.adm.menu', $task );
		}
		Sobi::Trigger( 'Create', 'AdmMenu', array( &$cfg ) );
		if ( count( $cfg ) ) {
			foreach ( $cfg as $section => $keys ) {
				$menu->addSection( $section, $keys );
			}
		}
		if ( !( Sobi::Section() ) ) {
			$menu->addCustom( 'GB.CFG.GLOBAL_TEMPLATES', $this->listTemplates() );
		}
		Sobi::Trigger( 'AfterCreate', 'AdmMenu', array( &$menu ) );
		return $menu;
	}

	/**
	 * Returns an array with field object of field type which is possible to use it as entry name field
	 * @param bool $pos
	 * @param array $types
	 * @return array
	 */
	public function getNameFields( $pos = false, $types = array() )
	{
		// removed static because we have different settings for Alpha Index
		/*static */
		$cache = array( 'pos' => null, 'npos' => null );
		/**
		 * alpha index/ordering
		 */
		if ( $pos ) {
			if ( $cache[ 'pos' ] ) {
				return $cache[ 'pos' ];
			}
			if ( !( count( $types ) ) ) {
				$types = explode( ', ', Sobi::Cfg( 'field_types_for_ordering', 'inbox, select' ) );
			}
		}
		else {
			if ( $cache[ 'npos' ] ) {
				return $cache[ 'npos' ];
			}
			if ( !( count( $types ) ) ) {
				$types = explode( ', ', Sobi::Cfg( 'field_types_for_name', 'inbox' ) );
			}
		}

		/* @var SPdb $db */
		$db =& SPFactory::db();
		try {
			$db->select( 'fid', 'spdb_field', array( 'fieldType' => $types, 'section' => Sobi::Reg( 'current_section' ) ) );
			$fids = $db->loadResultArray();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELD_FOR_NAMES', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$fields = array();
		if ( count( $fids ) ) {
			foreach ( $fids as $fid ) {
				$f = SPFactory::Model( 'field', true );
				$f->init( $fid );
				$fields[ $fid ] = $f;
			}
		}
		$cache[ $pos ? 'pos' : 'npos' ] = $fields;
		return $fields;
	}

	/**
	 * Save the config
	 */
	protected function save( $apply, $clone = false )
	{
		$sid = Sobi::Section();
		$this->authorise( $sid );
		if ( $sid ) {
			$this->validate( 'config.general', array( 'task' => 'config.general', 'sid' => $sid ) );
		}
		else {
			$this->validate( 'config.global', array( 'task' => 'config.global' ) );
		}
		$fields = array();
		$section = false;
		$data = SPRequest::arr( 'spcfg', array() );
		// strange thing =8-O
		if ( !( isset( $data[ 'alphamenu.extra_fields_array' ] ) ) ) {
			$data[ 'alphamenu.extra_fields_array' ] = array();
		}
		foreach ( $data as $k => $v ) {
			if ( is_string( $v ) ) {
				$v = htmlspecialchars_decode( $v );
			}
//			$k = str_replace( array( 'spcfg_', '.' ), array( null, '_' ), $k );
			$k = str_replace( 'spcfg_', null, $k );
			$s = explode( '.', $k );
			$s = $s[ 0 ];
			if ( !( isset( $fields[ $s ] ) ) ) {
				$fields[ $s ] = array();
			}
			$k = str_replace( "{$s}.", null, $k );
			$c = explode( '_', $k );
			if ( $c[ count( $c ) - 1 ] == 'array' && !( is_array( $v ) ) ) {
				if ( !( strstr( $v, '|' ) ) ) {
					$v = explode( ',', $v );
				}
				else {
					$v = explode( '|', $v );
				}
			}
			$fields[ $s ][ $k ] = $v;
			if ( preg_match( '/^section.*/', $k ) ) {
				$section = true;
			}
		}
		$values = array();
		if ( count( $fields ) ) {
			foreach ( $fields as $sec => $keys ) {
				if ( count( $keys ) ) {
					foreach ( $keys as $k => $v ) {
						$values[ ] = array( 'sKey' => $k, 'sValue' => $v, 'section' => Sobi::Section(), 'critical' => 0, 'cSection' => $sec );
					}
				}
			}
		}
		if ( $section ) {
			/* @var $sec SPSection */
			$sec = SPFactory::Model( 'section' );
			$sec->init( SPRequest::sid() );
			$sec->getRequest( 'section' );
			$sec->save( true );
		}
		Sobi::Trigger( 'SaveConfig', $this->name(), array( &$values ) );
		try {
			SPFactory::db()->insertArray( 'spdb_config', $values, true );
		} catch ( SPException $x ) {
			$this->response( Sobi::Back(), $x->getMessage(), false, SPC::ERROR_MSG );
		}
		if ( !( $section && !( Sobi::Section() ) ) ) {
			SPFactory::cache()->cleanAll();
		}
		else {
			SPFactory::cache()->cleanSection();
		}

		Sobi::Trigger( 'After', 'SaveConfig', array( &$values ) );
		$this->response( Sobi::Back(), Sobi::Txt( 'MSG.CONFIG_SAVED' ), false, 'success' );
	}
}
