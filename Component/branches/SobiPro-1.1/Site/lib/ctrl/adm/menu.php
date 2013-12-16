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
SPLoader::loadController( 'file' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created Sat, Nov 30, 2013 13:50:49
 */
class SPMenuAdm extends SPController
{

	protected $menu = null;

	public function execute()
	{
		$function = SPRequest::cmd( 'function' );
		SPFactory::header()->addJsFile( 'jnmenu', true );
		SPLang::load( 'com_sobipro.sys' );
		if ( !( $function ) ) {
			$this->listFunctions();
		}
		else {
			$this->loadFunction( $function );
		}
	}

	protected function loadFunction( $function )
	{
		$xml = new DOMDocument();
		$xml->load( SPLoader::path( "menu.{$function}", 'adm.templates', true, 'xml' ) );
		$xpath = new DOMXPath( $xml );
		$this->loadLanguage( $xpath );
		$calls = $xpath->query( '/definition/config/calls/call' );

		$this->menu = SPFactory::db()
				->select( '*', '#__menu', array( 'id' => SPRequest::int( 'mid' ) ) )
				->loadObject();
		if ( isset( $this->menu->params ) ) {
			$this->menu->params = json_decode( $this->menu->params );
			if ( isset( $this->menu->params->SobiProSettings ) ) {
				$this->menu->params->SobiProSettings = json_decode( base64_decode( $this->menu->params->SobiProSettings ) );
			}
		}
		/** @var SPAdmView $view */
		$view = SPFactory::View( 'joomla-menu', true );
		$view->assign( $this->menu, 'joomlaMenu' );
		$section = SPRequest::int( 'section' );
		if ( $calls->length ) {
			foreach ( $calls as $file ) {
				$method = $file->attributes->getNamedItem( 'method' )->nodeValue;
				if ( $file->attributes->getNamedItem( 'static' ) && $file->attributes->getNamedItem( 'static' )->nodeValue == 'true' ) {
					$class = SPLoader::loadClass( $file->attributes->getNamedItem( 'file' )->nodeValue );
					$class::$method( $view, $this->menu );
				}
				else {
					$obj = SPFactory::Instance( $file->attributes->getNamedItem( 'file' )->nodeValue );
					$obj->$method( $view, $this->menu );
				}
			}
		}
		$view->assign( $section, 'sectionId' )
				->determineTemplate( 'menu', $function )
				->display();
	}

	/**
	 * @param SPAdmJoomlaMenuView $view
	 * @param stdClass $menu
	 */
	public function calendar( &$view, $menu )
	{
		$months = array( null => Sobi::Txt( 'FMN.HIDDEN_OPT' ) );
		$years = array( null => Sobi::Txt( 'FD.SEARCH_SELECT_LABEL' ) );
		$days = array( null => Sobi::Txt( 'FMN.HIDDEN_OPT' ) );

		$monthsNames = Sobi::Txt( 'JS_CALENDAR_MONTHS' );
		$monthsNames = explode( ',', $monthsNames );

		$link = $view->get( 'joomlaMenu' )->link;
		$query = array();
		parse_str( $link, $query );
		$selected = array( 'year' => null, 'month' => null, 'day' => null );
		if ( isset( $query[ 'date' ] ) ) {
			$date = explode( '.', $query[ 'date' ] );
			$selected[ 'year' ] = isset( $date[ 0 ] ) && $date[ 0 ] ? $date[ 0 ] : null;
			$selected[ 'month' ] = isset( $date[ 1 ] ) && $date[ 1 ] ? $date[ 1 ] : null;
			$selected[ 'day' ] = isset( $date[ 2 ] ) && $date[ 2 ] ? $date[ 2 ] : null;
		}
		else {
			$query[ 'date' ] = '';
		}

		for ( $i = 1; $i < 13; $i++ ) {
			$months[ $i ] = $monthsNames[ $i - 1 ];
		}

		for ( $i = 1; $i < 32; $i++ ) {
			$days[ $i ] = $i;
		}

		$exYears = SPFactory::db()
				->dselect( 'EXTRACT( YEAR FROM createdTime )', 'spdb_object' )
				->loadResultArray();
		if ( count( $exYears ) ) {
			foreach ( $exYears as $year ) {
				$years[ $year ] = $year;
			}
		}
		$view
				->assign( $years, 'years' )
				->assign( $months, 'months' )
				->assign( $selected, 'date' )
				->assign( $days, 'days' );
		$this->addTemplates( $view, $menu, 'list.date' );
	}

	/**
	 * @param SPAdmJoomlaMenuView $view
	 * @param stdClass $menu
	 */
	public function entryForm( &$view, $menu )
	{
		$section = SPRequest::int( 'section' );
		$tree = $this->initialiseTree();
		$tree->init( $section );
		$view->assign( $tree, 'tree' );
		$this->addTemplates( $view, $menu, 'entry' );
	}

	/**
	 * @param SPAdmJoomlaMenuView $view
	 * @param stdClass $menu
	 */
	public function entry( &$view, $menu )
	{
		$this->addTemplates( $view, $menu, 'entry' );
	}

	/**
	 * @param SPAdmJoomlaMenuView $view
	 * @param stdClass $menu
	 */
	public function section( &$view, $menu )
	{
		$this->addTemplates( $view, $menu, 'section' );
	}

	/**
	 * @param SPAdmJoomlaMenuView $view
	 * @param stdClass $menu
	 */
	public function user( &$view, $menu )
	{
		$this->addTemplates( $view, $menu, 'list.user' );
	}

	public function calendarLabel()
	{

	}

	public function categoryLabel( $sid )
	{
		return Sobi::Txt( 'MENU_LINK_TO_SELECTED_CATEGORY', SPFactory::Category( $sid )->get( 'name' ) );
	}

	public function entryLabel( $sid )
	{
		return Sobi::Txt( 'MENU_LINK_TO_SELECTED_ENTRY', SPFactory::Entry( $sid )->get( 'name' ) );
	}

	public function entryFormLabel( $sid, $section )
	{
		return Sobi::Txt( 'MENU_LINK_TO_ADD_ENTRY_FORM_SELECTED', $section == $sid ? SPFactory::Section( $sid )->get( 'name' ) : SPFactory::Category( $sid )->get( 'name' ) );
	}


	/**
	 * @param SPAdmJoomlaMenuView $view
	 * @param stdClass $menu
	 */
	public function category( &$view, $menu )
	{
		$section = SPRequest::int( 'section' );
		$tree = $this->initialiseTree();
		$tree->disable( $section );
		$tree->init( $section );
		$view->assign( $tree, 'tree' );
		$this->addTemplates( $view, $menu, 'category' );
	}

	protected function listFunctions()
	{
		/** @var SPDirectory $directory */
		$directory = SPFactory::Instance( 'base.fs.directory', SPLoader::dirPath( 'menu', 'adm.templates' ) );
		$files = $directory->searchFile( '.xml', false );
		$functions = array();
		if ( count( $files ) ) {
			foreach ( $files as $file ) {
				$path = $file->getPathInfo();
				$functions[ $path[ 'filename' ] ] = $this->functionDetails( $file );
			}
		}
		$functions = array_merge( array( 'null' => Sobi::Txt( 'SOBI_SELECT_FUNCTIONALITY' ) ), $functions );
		SPFactory::View( 'joomla-menu', true )
				->assign( $functions, 'functions' )
				->functions();
	}

	/**
	 * @param SPFile $file
	 * @return string
	 * */
	protected function functionDetails( $file )
	{
		$xml = new DOMDocument();
		$xml->load( $file->getPathname() );
		$xpath = new DOMXPath( $xml );
		$title = $xpath->query( '/definition/header/title' )
				->item( 0 )
				->attributes
				->getNamedItem( 'value' )
				->nodeValue;
		$this->loadLanguage( $xpath );
		return Sobi::Txt( $title );
	}

	/**
	 * @param $xpath
	 */
	protected function loadLanguage( $xpath )
	{
		$files = $xpath->query( '/definition/header/file[@type="language"]' );
		if ( $files->length ) {
			foreach ( $files as $file ) {
				SPLang::load( $file->attributes->getNamedItem( 'filename' )->nodeValue );
			}
		}
	}

	/**
	 * @return SigsiuTree
	 */
	protected function initialiseTree()
	{
		/** @var SigsiuTree $tree */
		$tree = SPFactory::Instance( 'mlo.tree', Sobi::GetUserState( 'categories.order', 'corder', 'position.asc' ) );
		$tree->setHref( "javascript:SP_selectCat( '{sid}' )" );
		$tree->setTask( 'category.chooser' );
		return $tree;
	}

	protected function getTemplates( $type )
	{
		$templates = array();
		$templates[ '' ] = Sobi::Txt( 'SELECT_TEMPLATE_OVERRIDE' );
		$template = SPFactory::db()
				->select( 'sValue', 'spdb_config', array( 'section' => SPRequest::int( 'section' ), 'sKey' => 'template', 'cSection' => 'section' ) )
				->loadResult();
		$templateDir = $this->templatePath( $template );
		$this->listTemplates( $templates, $templateDir, $type );
		return $templates;
	}

	protected function templatePath( $tpl )
	{
		$file = explode( '.', $tpl );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$template = SPLoader::path( $file, 'root', false, null );
		}
		else {
			$template = SOBI_PATH . '/usr/templates/' . str_replace( '.', '/', $tpl );
		}
		return $template;
	}

	protected function listTemplates( &$arr, $path, $type )
	{
		switch ( $type ) {
			case 'entry':
			case 'entry.add':
			case 'section':
			case 'category':
			case 'search':
				$path = Sobi::FixPath( $path . '/' . $type );
				break;
			case 'list.user':
			case 'list.date':
				$path = Sobi::FixPath( $path . '/listing' );
				break;
			default:
				if ( strstr( $type, 'list' ) ) {
					$path = Sobi::FixPath( $path . '/listing' );
				}
				break;
		}
		if ( file_exists( $path ) ) {
			$files = scandir( $path );
			if ( count( $files ) ) {
				foreach ( $files as $file ) {
					$stack = explode( '.', $file );
					if ( array_pop( $stack ) == 'xsl' ) {
						$arr[ $stack[ 0 ] ] = $file;
					}
				}
			}
		}
	}

	/**
	 * @param $view
	 * @param $menu
	 * @param $type
	 */
	protected function addTemplates( &$view, $menu, $type )
	{
		$templates = $this->getTemplates( $type );
		$view->assign( $templates, 'templates' );
		$query = array();
		if ( isset( $menu->link ) ) {
			$link = $menu->link;
			parse_str( $link, $query );
		}
		if ( isset( $query[ 'sptpl' ] ) ) {
			$view->assign( $query[ 'sptpl' ], 'template' );
		}
	}
}
