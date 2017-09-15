<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:38:03 PM
 */
class SPAdminPanel extends SPController
{
	/**
	 * @var array
	 */
	private $_sections = [];
	/**
	 * @var string
	 */
	protected $_defTask = 'panel';
	/**
	 * @var string
	 */
	protected $_type = 'front';

	/**
	 */
	private function getSections()
	{
		$order = $this->getOrdering();
		try {
			$sections = SPFactory::db()
				->select( '*', 'spdb_object', [ 'oType' => 'section' ], $order )
				->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		if ( count( $sections ) ) {
			SPLoader::loadClass( 'models.datamodel' );
			SPLoader::loadClass( 'models.dbobject' );
			SPLoader::loadModel( 'section' );
			foreach ( $sections as $section ) {
				if ( Sobi::Can( 'section', 'access', 'any', $section->id ) ) {
					$s = new SPSection();
					$s->extend( $section );
					$this->_sections[] = $s;
				}
			}
		}
	}

	/**
	 */
	protected function getOrdering()
	{
		$order = Sobi::GetUserState( 'sections.order', 'order', 'name.asc' );
		$ord   = $order;
		$dir   = 'asc';
		if ( strstr( $order, '.' ) ) {
			$ord = explode( '.', $ord );
			$dir = $ord[ 1 ];
			$ord = $ord[ 0 ];
		}
		if ( $ord == 'position' ) {
			$ord = 'name';
		}
		if ( $ord == 'name' ) {
			/* @var SPdb $db */
			$db =& SPFactory::db();
			try {
				$db->select( 'id', 'spdb_language', [ 'oType' => 'section', 'sKey' => 'name', 'language' => Sobi::Lang() ], 'sValue.' . $dir );
				$fields = $db->loadResultArray();
				if ( !count( $fields ) && Sobi::Lang() != Sobi::DefLang() ) {
					$db->select( 'id', 'spdb_language', [ 'oType' => 'section', 'sKey' => 'name', 'language' => Sobi::DefLang() ], 'sValue.' . $dir );
					$fields = $db->loadResultArray();
				}
			}
			catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );

				return false;
			}
			if ( count( $fields ) ) {
				$fields = implode( ',', $fields );
				$ord    = "field( id, {$fields} )";
			}
			else {
				$ord = 'id.' . $dir;
			}
		}
		else {
			$ord = ( isset( $dir ) && strlen( $dir ) ) ? $ord . '.' . $dir : $ord;
		}
		SPFactory::user()->setUserState( 'sections.order', $ord );

		return $ord;
	}

	/**
	 */
	public function execute()
	{
		switch ( $this->_task ) {
			case 'panel':
				$this->getSections();
				/** @var $view SPAdmPanelView */
				$news      = $this->getNews();
				$ordering  = Sobi::GetUserState( 'sections.order', 'order', 'name.asc' );
				$myVersion = SPFactory::CmsHelper()->myVersion( true );
				$cfg       = Sobi::Cfg( 'cpanel.show_entries', true );
				$cfgCats   = Sobi::Cfg( 'cpanel.show_categories', false );
				$fVersion  = \Sobi\C::VERSION;

				$state = $this->getState();
				$view  = SPFactory::View( 'front', true )
					->assign( $acl, 'acl' )
					->assign( $this->_sections, 'sections' )
					->assign( $news, 'news' )
					->assign( $ordering, 'order' )
					->assign( $myVersion, 'version' )
					->assign( $fVersion, 'frameworkVersion' )
					->assign( $cfg, 'show-entries' )
					->assign( $cfgCats, 'show-categories' )
					->assign( $state, 'system-state' );
				if ( $cfg ) {
					$entries = $this->getEntries();
					$view->assign( $entries, 'entries' );
				}
				if ( $cfgCats ) {
					$categories = $this->getCategories();
					$view->assign( $categories, 'categories' );
				}
				SPLang::load( 'com_sobipro.about' );
				$view->determineTemplate( 'front', 'cpanel' );
				Sobi::Trigger( 'Panel', 'View', [ &$view ] );
				$view->display();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !parent::execute() ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	private function getNews()
	{
		$out  = [];
		$path = SPLoader::path( 'etc.news', 'front', false, 'xml' );
		if ( SPFs::exists( $path ) && ( time() - filemtime( $path ) < ( 60 * 60 * 12 ) ) ) {
			$content = SPFs::read( SPLoader::path( 'etc.news', 'front', false, 'xml' ) );
		}
		else {
			try {
				$connection = SPFactory::Instance( 'services.remote' );
				$news       = 'http://rss.sigsiu.net';
				$connection->setOptions(
					[
						'url'            => $news,
						'connecttimeout' => 10,
						'header'         => false,
						'returntransfer' => true,
					]
				);
				$file    = SPFactory::Instance( 'base.fs.file', $path );
				$content = $connection->exec();
				$cinf    = $connection->info();
				if ( isset( $cinf[ 'http_code' ] ) && $cinf[ 'http_code' ] != 200 ) {
					return Sobi::Error( 'about', sprintf( 'CANNOT_GET_NEWS', $news, $cinf[ 'http_code' ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
				$file->content( $content );
				$file->save();
			}
			catch ( SPException $x ) {
				return Sobi::Error( 'about', SPLang::e( 'CANNOT_LOAD_NEWS', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		try {
			$open = false;
			if ( ( strpos( $content, "DOCTYPE html" ) > 0 ) ) { // wenn kein XML format (passiert z.B. bei SSL Fehler)
				$content = "";
			}
			if ( strlen( $content ) ) {
				$document = new DOMDocument();
				$document->loadXML( $content );
				$news = new DOMXPath( $document );

				$atom = false;  // to show an image, our RSS feeds are RSS 2.0 and not Atom!
				if ( $atom ) {    //Atom
					$news->registerNamespace( 'atom', 'http://www.w3.org/2005/Atom' );
					$out[ 'title' ] = $news->query( '/atom:feed/atom:title' )->item( 0 )->nodeValue;
					$items          = $news->query( '/atom:feed/atom:entry[*]' );
					$c              = 5;
					foreach ( $items as $item ) {
						$date = $item->getElementsByTagName( 'updated' )->item( 0 )->nodeValue;
						if ( !( $open ) && time() - strtotime( $date ) < ( 60 * 60 * 24 ) ) {
							$open = true;
						}
						$feed = [
							'url'     => $item->getElementsByTagName( 'link' )->item( 0 )->nodeValue,
							'title'   => $item->getElementsByTagName( 'title' )->item( 0 )->nodeValue,
							'content' => $item->getElementsByTagName( 'content' )->item( 0 )->nodeValue
						];
						if ( !( $c-- ) ) {
							break;
						}
						$out[ 'feeds' ][] = $feed;
					}
				}
				else {  //RSS
					$out[ 'title' ] = $news->query( '/rss/channel/title' )->item( 0 )->nodeValue;
					$items          = $news->query( '/rss/channel/item[*]' );
					$c              = 5;
					$open           = false;
					foreach ( $items as $item ) {
						$date = $item->getElementsByTagName( 'pubDate' )->item( 0 )->nodeValue;
						if ( !( $open ) && time() - strtotime( $date ) < ( 60 * 60 * 24 ) ) {
							$open = true;
						}
						$feed = [
							'url'     => $item->getElementsByTagName( 'link' )->item( 0 )->nodeValue,
							'title'   => $item->getElementsByTagName( 'title' )->item( 0 )->nodeValue,
							'content' => $item->getElementsByTagName( 'description' )->item( 0 )->nodeValue,
							'image'   => $item->getElementsByTagName( 'enclosure' )->item( 0 )->attributes->getNamedItem( 'url' )->nodeValue,
						];
						if ( !( $c-- ) ) {
							break;
						}
						$out[ 'feeds' ][] = $feed;
					}
				}
			}
			if ( $open ) {
				SPFactory::header()->addJsCode( 'SobiPro.jQuery( document ).ready( function () { SobiPro.jQuery( \'#SobiProNews\' ).trigger(\'click\'); } );' );
			}
		}
		catch ( DOMException $x ) {
			return Sobi::Error( 'about', SPLang::e( 'CANNOT_LOAD_NEWS', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		return $out;
	}

	protected function getEntries()
	{
		$entries = SPFactory::cache()->getObj( 'cpanel_entries', -1 );
		if ( $entries && is_array( $entries ) ) {
			return $entries;
		}
		$entries                 = [];
		$popular                 = SPFactory::db()
			->select( 'id', 'spdb_object', [ 'oType' => 'entry' ], 'counter.desc', 15 )
			->loadResultArray();
		$entries[ 'popular' ]    = $this->addEntries( $popular );
		$latest                  = SPFactory::db()
			->select( 'id', 'spdb_object', [ 'oType' => 'entry' ], 'createdTime.desc', 15 )
			->loadResultArray();
		$entries[ 'latest' ]     = $this->addEntries( $latest );
		$unapproved              = SPFactory::db()
			->select( 'id', 'spdb_object', [ 'oType' => 'entry', 'approved' => 0 ], 'createdTime.desc', 15 )
			->loadResultArray();
		$entries[ 'unapproved' ] = $this->addEntries( $unapproved );
		SPFactory::cache()->addObj( $entries, 'cpanel_entries', -1 );

		return $entries;
	}

	protected function getCategories()
	{
		$categories = SPFactory::cache()->getObj( 'cpanel_categories', -1 );
		if ( $categories && is_array( $categories ) ) {
			return $categories;
		}
		$categories              = [];
		$popular                 = SPFactory::db()
			->select( 'id', 'spdb_object', [ 'oType' => 'category' ], 'counter.desc', 15 )
			->loadResultArray();
		$categories[ 'popular' ] = $this->addCategories( $popular );
		$latest                  = SPFactory::db()
			->select( 'id', 'spdb_object', [ 'oType' => 'category' ], 'createdTime.desc', 15 )
			->loadResultArray();
		$categories[ 'latest' ]  = $this->addCategories( $latest );
		SPFactory::cache()->addObj( $categories, 'cpanel_categories', -1 );

		return $categories;
	}

	protected function addEntries( $ids )
	{
		static $sections = [];
		$entries = [];
		if ( count( $ids ) ) {
			$c = 0;
			foreach ( $ids as $sid ) {
				$c++;
				if ( $c > 5 ) {
					break;
				}
				$entry = SPFactory::EntryRow( $sid );
				if ( !( $entry->get( 'valid' ) ) ) {    //check if the entry is valid (has categories assigned and a name)
					$c--;
					continue;
				}
				$section = $entry->get( 'section' );

				if ( !( isset( $sections[ $section ] ) ) ) {
					$sections[ $section ] = SPFactory::Section( $section );
				}
				$entry->setProperty( 'section', $sections[ $section ] );
				$entries[] = $entry;
			}
		}

		return $entries;
	}

	protected function addCategories( $ids )
	{
		static $sections = [];
		$categories = [];
		if ( count( $ids ) ) {
			$c = 0;
			foreach ( $ids as $sid ) {
				$c++;
				if ( $c > 5 ) {
					break;
				}
				$category = SPFactory::Category( $sid );
				$section = SPFactory::config()->getParentPath( $sid )[0];
				if ( !( isset( $sections[ $section ] ) ) ) {
					$sections[ $section ] = SPFactory::Section( $section );
				}
				$category->setProperty( 'section', $sections[ $section] );
				$categories[] = $category;
			}
		}

		return $categories;
	}

	protected function getState()
	{
		$state = SPFactory::cache()->getVar( 'system_state' );
		if ( !( $state ) ) {
			SPLang::load( 'com_sobipro.messages' );
			$state                       = [];
			$state[ 'accelerator' ]      = [
				'type'  => Sobi::Cfg( 'cache.l3_enabled', true ) ? 'success' : 'error',
				'label' => Sobi::Cfg( 'cache.l3_enabled', true ) ? Sobi::Txt( 'ACCELERATOR_ENABLED' ) : Sobi::Txt( 'ACCELERATOR_DISABLED' ),
			];
			$state[ 'xml-optimiser' ]    = [
				'type'  => Sobi::Cfg( 'cache.xml_enabled', true ) ? 'success' : 'error',
				'label' => Sobi::Cfg( 'cache.xml_enabled', true ) ? Sobi::Txt( 'XML_CACHE_ENABLED' ) : Sobi::Txt( 'XML_CACHE_DISABLED' ),
			];
			$state[ 'javascript-cache' ] = [
				'type'  => Sobi::Cfg( 'cache.include_js_files', false ) ? 'success' : 'warning',
				'label' => Sobi::Cfg( 'cache.include_js_files', false ) ? Sobi::Txt( 'JS_CACHE_ENABLED' ) : Sobi::Txt( 'JS_CACHE_DISABLED' ),
			];
			$state[ 'css-cache' ]        = [
				'type'  => Sobi::Cfg( 'cache.include_css_files', false ) ? 'success' : 'warning',
				'label' => Sobi::Cfg( 'cache.include_css_files', false ) ? Sobi::Txt( 'CSS_CACHE_ENABLED' ) : Sobi::Txt( 'CSS_CACHE_DISABLED' ),
			];
			$state[ 'display-errors' ]   = [
				'type'  => Sobi::Cfg( 'debug.display_errors', false ) ? 'error' : 'success',
				'label' => Sobi::Cfg( 'debug.display_errors', false ) ? Sobi::Txt( 'DISPLAY_ERRORS_ENABLED' ) : Sobi::Txt( 'DISPLAY_ERRORS_DISABLED' ),
			];
			$state[ 'debug-level' ]      = [
				'type'  => Sobi::Cfg( 'debug.level', 0 ) > 2 ? 'warning' : 'success',
				'label' => Sobi::Cfg( 'debug.level', 0 ) > 2 ? Sobi::Txt( 'DEBUG_LEVEL_TOO_HIGH' ) : Sobi::Txt( 'DEBUG_LEVEL_OK' ),
			];
			$state[ 'debug-xml' ]        = [
				'type'  => Sobi::Cfg( 'debug.xml_raw', false ) ? 'error' : 'success',
				'label' => Sobi::Cfg( 'debug.xml_raw', false ) ? Sobi::Txt( 'DEBUG_XML_ENABLED' ) : Sobi::Txt( 'DEBUG_XML_DISABLED' ),
			];
//			uasort( $state, array( $this, 'sortMessages' ) );
			$messages = SPFactory::message()->getSystemMessages();
			$content  = null;
			if ( count( $messages ) ) {
				foreach ( $messages as $message ) {
					$url                            = Sobi::Url( [ 'sid' => $message[ 'section' ][ 'id' ] ] );
					$url                            = "<a href=\"{$url}\">{$message['section']['name']}</a> ";
					$message[ 'section' ][ 'link' ] = $url;
					$message[ 'type-text' ]         = ucfirst( Sobi::Txt( $message[ 'type' ] ) );
					$state[ 'messages' ][]          = $message;
				}
			}
			SPFactory::cache()->addVar( $state, 'system_state' );
		}

		return $state;
	}

//	private function sortMessages( $first, $second )
//	{
//		$return = 0;
//		if ( $first[ 'type' ] != $second[ 'type' ] ) {
//			switch( $first[ 'type' ] ) {
//				case 'error':
//					$return = -1;
//					break;
//				case 'warning':
//					$return = $second[ 'type' ] == 'error' ? 1 : -1;
//					break;
//				case 'success':
//					$return = 1;
//			}
//		}
//		return $return;
//	}
}
