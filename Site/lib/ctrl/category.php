<?php
/**
 * @version: $Id$
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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

SPLoader::loadController( 'section' );


/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:08:52 PM
 */
class SPCategoryCtrl extends SPSectionCtrl
{
	/**
	 * @var string
	 */
	protected $_defTask = 'view';
	/**
	 * @var string
	 */
	protected $_type = 'category';

	/**
	 */
	public function execute()
	{
		$r = false;
		switch ( $this->_task ) {
			case 'chooser':
			case 'expand':
				SPLoader::loadClass( 'html.input' );
				$r = true;
				$this->chooser( ( $this->_task == 'expand' ) );
				break;
			case 'parents':
				$r = true;
				$this->parents();
				break;
			case 'icon':
				$r = true;
				$this->iconChooser();
				break;
			default:
				/* case parent didn't registered this task, it was an error */
				if ( !( parent::execute() ) && $this->name() == __CLASS__ ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				else {
					$r = true;
				}
				break;
		}
		return $r;
	}

	protected function iconChooser()
	{
		if ( !( Sobi::Can( 'category.edit' ) ) ) {
			Sobi::Error( 'category', 'You have no permission to access this site', SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		if ( strlen( SPRequest::cmd( 'font' ) ) ) {
			return $this->icontFonts();
		}
		$folder = SPRequest::cmd( 'iconFolder', null );
		$callback = SPRequest::cmd( 'callback', 'SPSelectIcon' );
		$dir = $folder ? Sobi::Cfg( 'images.category_icons' ) . str_replace( '.', '/', $folder ) . '/' : Sobi::Cfg( 'images.category_icons' );
		$files = array();
		$dirs = array();
		if ( $folder ) {
			$up = explode( '.', $folder );
			unset( $up[ count( $up ) - 1 ] );
			$dirs[ ] = array(
					'name' => Sobi::Txt( 'FOLEDR_UP' ),
					'count' => ( count( scandir( $dir . '..' ) ) - 2 ),
					'url' => Sobi::Url( array( 'task' => 'category.icon', 'out' => 'html', 'iconFolder' => ( count( $up ) ? implode( '.', $up ) : null ) ) )
			);
		}
		$ext = array( 'png', 'jpg', 'jpeg', 'gif' );
		if ( ( is_dir( $dir ) ) && ( $dh = opendir( $dir ) ) ) {
			while ( ( $file = readdir( $dh ) ) !== false ) {
				if ( ( filetype( $dir . $file ) == 'file' ) && in_array( strtolower( SPFs::getExt( $file ) ), $ext ) ) {
					$files[ ] = array(
							'name' => $folder ? str_replace( '.', '/', $folder ) . '/' . $file : $file,
							'path' => str_replace( '\\', '/', str_replace( SOBI_ROOT, Sobi::Cfg( 'live_site' ), str_replace( '//', '/', $dir . $file ) ) )
					);
				}
				elseif ( filetype( $dir . $file ) == 'dir' && !( $file == '.' || $file == '..' ) ) {
					$dirs[ ] = array(
							'name' => $file,
							'count' => ( count( scandir( $dir . $file ) ) - 2 ),
							'path' => str_replace( '\\', '/', str_replace( SOBI_ROOT, Sobi::Cfg( 'live_site' ), str_replace( '//', '/', $dir . $file ) ) ),
							'url' => Sobi::Url( array( 'task' => 'category.icon', 'out' => 'html', 'iconFolder' => ( $folder ? $folder . '.' . $file : $file ) ) )
					);
				}
			}
			closedir( $dh );
		}
		sort( $files );
		sort( $dirs );
		$view = SPFactory::View( 'category' );
		$view->setTemplate( 'category.icon' );
		$view->assign( $this->_task, 'task' );
		$view->assign( $callback, 'callback' );
		$view->assign( $files, 'files' );
		$view->assign( Sobi::Cfg( 'images.folder_ico' ), 'folder' );
		$view->assign( $dirs, 'directories' );
		$view->icon();
	}

	protected function icontFonts()
	{
		$font = SPRequest::cmd( 'font' );
		if ( strstr( $font, 'font-' ) ) {
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			exit( SPFs::read( SPLoader::translatePath( 'etc.fonts.' . $font, 'front', true, 'json' ) ) );
		}
	}

	/**
	 * Show category chooser
	 * @param bool $menu
	 * @throws SPException
	 */
	protected function chooser( $menu = false )
	{
		$out = SPRequest::cmd( 'out', null );
		$exp = SPRequest::int( 'expand', 0 );
		$multi = SPRequest::int( 'multiple', 0 );
		$tpl = SPRequest::word( 'treetpl', null );
		/* load the SigsiuTree class */
		$tree = SPLoader::loadClass( 'mlo.tree' );
		$ordering = defined( 'SOBI_ADM_PATH' ) ? Sobi::GetUserState( 'categories.order', 'corder', 'position.asc' ) : Sobi::Cfg( 'list.categories_ordering' );
		/* create new instance */
		$tree = new $tree( $ordering );

		/* set link */
		if ( $menu ) {
			$tree->setId( 'menuTree' );
			if ( defined( 'SOBIPRO_ADM' ) ) {
				$link = Sobi::Url( array( 'sid' => '{sid}' ), false, false, true );
			}
			else {
				$link = Sobi::Url( array( 'sid' => '{sid}' ) );
			}
		}
		else {
			$link = "javascript:SP_selectCat( '{sid}' )";
		}
		$tree->setHref( $link );

		/* set the task to expand the tree */
		$tree->setTask( 'category.chooser' );

		/* disable the category which is currently edited - category cannot be within it self */
		if ( !$multi ) {
			if ( SPRequest::sid() != Sobi::Section() ) {
				$tree->disable( SPRequest::sid() );
			}
			$tree->setPid( SPRequest::sid() );
		}
		else {
			$tree->disable( Sobi::Reg( 'current_section' ) );
		}

		/* case we extending existing tree */
		if ( $out == 'xml' && $exp ) {
			$pid = SPRequest::int( 'pid', 0 );
			$pid = $pid ? $pid : SPRequest::sid();
			$tree->setPid( $pid );
			$tree->disable( $pid );
			$tree->extend( $exp );
		}

		/* otherwise we are creating new tree */
		else {
			/* init the tree for the current section */
			$tree->init( Sobi::Reg( 'current_section' ) );
			/* load model */
			if ( !$this->_model ) {
				$this->setModel( SPLoader::loadModel( 'category' ) );
			}
			/* create new view */
			$class = SPLoader::loadView( 'category' );
			$view = new $class();
			/* assign the task and the tree */
			$view->assign( $this->_task, 'task' );
			$view->assign( $tree, 'tree' );
			$view->assign( $this->_model, 'category' );
			/* select template to show */
			if ( $tpl ) {
				$view->setTemplate( 'category.' . $tpl );
			}
			elseif ( $multi ) {
				$view->setTemplate( 'category.mchooser' );
			}
			else {
				$view->setTemplate( 'category.chooser' );
			}
			Sobi::Trigger( 'Category', 'ChooserView', array( &$view ) );
			$view->chooser();
		}
	}

	/**
	 * @return void
	 */
	private function parents()
	{
//		sleep( 5 );
		$sid = SPRequest::sid();
		$out = SPRequest::cmd( 'out', 'json' );
		$path = SPFactory::config()->getParentPath( $sid, true, false, true );
		$cats = array();
		if ( count( $path ) ) {
			$childs = 0;
			foreach ( $path as $category ) {
				if ( $category[ 'id' ] == $sid ) {
					$childs = count( SPFactory::Category( $sid )->getChilds( 'category', false, 1 ) );
				}
				$cats[ ] = array( 'id' => $category[ 'id' ], 'name' => $category[ 'name' ], 'childsCount' => $childs );
			}
		}
		switch ( $out ) {
			case 'json':
				SPFactory::mainframe()
						->cleanBuffer()
						->customHeader();
				echo json_encode( array( 'id' => $sid, 'categories' => $cats ) );
				exit;
		}
	}

	/**
	 * @param int $sid
	 * @param bool $redirect
	 * @return void
	 */
	protected function checkIn( $sid, $redirect = true )
	{
		parent::checkIn( SPRequest::int( 'category_id' ), $redirect );
	}

}
