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

SPLoader::loadController( 'section' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 29-March-2010 13:08:21
 */
final class SPSearchCtrl extends SPSectionCtrl
{
	/**
	 * @var string
	 */
	protected $_type = 'search';
	/**
	 * @var string
	 */
	protected $_defTask = 'view';
	/**
	 * @var array
	 */
	protected $_request = array();
	/**
	 * @var array
	 */
	protected $_fields = array();
	/**
	 * @var array
	 */
	protected $_results = array();
	/**
	 * @var array
	 */
	protected $_resultsByPriority = array();
	/**
	 * @var int
	 */
	protected $_resultsCount = 0;
	/**
	 * @var array
	 */
	protected $_categoriesResults = array();
	/**
	 * @var SPDb
	 */
	protected $_db = array();

	public function __construct()
	{
		$this->_db =& SPFactory::db();
		parent::__construct();
	}

	/**
	 */
	public function execute()
	{
		if ( !( Sobi::Can( 'section.search' ) ) ) {
			if ( $this->_task != 'suggest' ) {
				if ( Sobi::Cfg( 'redirects.section_search_enabled' ) && strlen( Sobi::Cfg( 'redirects.section_search_url', null ) ) ) {
					$this->escape( Sobi::Cfg( 'redirects.section_search_url', null ), SPLang::e( Sobi::Cfg( 'redirects.section_search_msg', 'UNAUTHORIZED_ACCESS' ) ), Sobi::Cfg( 'redirects.section_search_msgtype', SPC::ERROR_MSG ) );
				}
				else {
					Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
				}
			}
			else {
				exit;
			}
		}
		$r = false;
		SPLoader::loadClass( 'env.cookie' );
		SPLoader::loadClass( 'env.browser' );
		SPRequest::set( 'task', $this->_type . '.' . $this->_task );
		switch ( $this->_task ) {
			case 'results':
			case 'view':
				$this->form();
				$r = true;
				break;
			case 'search':
				$this->search();
				$r = true;
				break;
			case 'suggest':
				$this->suggest();
				$r = true;
				break;
			default:
				if ( !parent::execute() ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
		return $r;
	}

	private function suggest()
	{
		$this->_request[ 'search_for' ] = str_replace( '*', '%', SPRequest::string( 'term', null ) );
		$results = array();
		if ( strlen( $this->_request[ 'search_for' ] ) >= Sobi::Cfg( 'search.suggest_min_chars', 1 ) ) {
			Sobi::Trigger( 'OnSuggest', 'Search', array( &$this->_request[ 'search_for' ] ) );
			$this->_fields = $this->loadFields();
			$search = str_replace( '.', '\.', $this->_request[ 'search_for' ] );
			if ( count( $this->_fields ) ) {
				foreach ( $this->_fields as $field ) {
					$fr = $field->searchSuggest( $search, Sobi::Section(), Sobi::Cfg( 'search.suggest_start_with', true ) );
					if ( is_array( $fr ) && count( $fr ) ) {
						$results = array_merge( $results, $fr );
					}
				}
			}
		}
		$results = array_unique( $results );
		if ( count( $results ) ) {
			foreach ( $results as $k => $v ) {
				$v = strip_tags( $v );
				if ( Sobi::Cfg( 'search.suggest_split_words', true ) && strstr( $v, ' ' ) ) {
					$v = explode( ' ', $v );
					$v = $v[ 0 ];
				}
				$results[ $k ] = $v;
			}
		}
		usort( $results, array( 'self', 'sortByLen' ) );
		Sobi::Trigger( 'AfterSuggest', 'Search', array( &$results ) );
		header( 'Content-type: application/json' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		SPFactory::mainframe()->cleanBuffer();
		echo json_encode( $results );
		exit();
	}

	private function sortByLen( $from, $to )
	{
		return strlen( $to ) - strlen( $from );
	}

	private function search()
	{
		$this->_request = SPRequest::search( 'field_' );
		$this->_request[ 'search_for' ] = str_replace( '*', '%', SPRequest::string( 'sp_search_for', null ) );
		$this->_request[ 'phrase' ] = SPRequest::string( 'spsearchphrase', Sobi::Cfg( 'search.searchphrase', 'all' ) );
		$ssid = SPRequest::cmd( 'ssid', SPRequest::cmd( 'ssid', null, 'cookie' ) );
		$this->_fields = $this->loadFields();
		$searchForString = false;
		Sobi::Trigger( 'OnRequest', 'Search', array( &$this->_request ) );
		for ( $i = 1; $i < 11; $i++ ) {
			$this->_resultsByPriority[ $i ] = array();
		}
		// if the visitor wasn't on the search page first
		if ( !( $ssid ) || SPRequest::int( 'reset', 0 ) ) {
			$this->session( $ssid );
		}

		/* clean request */
		if ( count( $this->_request ) ) {
			foreach ( $this->_request as $i => $v ) {
				if ( is_array( $v ) ) {
					foreach ( $v as $index => $value ) {
						$v[ $index ] = htmlspecialchars_decode( $value, ENT_QUOTES );
					}
					$this->_request[ $i ] = SPRequest::cleanArray( $v, true );
				}
				else {
					$this->_request[ $i ] = $this->_db->escape( $v );
				}
			}
		}

		/* sort fields by priority */
		usort( $this->_fields, array( 'self', 'sortByPrio' ) );

		/* First the basic search ..... */
		/* if we have a string to search */
		if ( strlen( $this->_request[ 'search_for' ] ) && $this->_request[ 'search_for' ] != Sobi::Txt( 'SH.SEARCH_FOR_BOX' ) ) {
			$searchForString = true;
			switch ( $this->_request[ 'phrase' ] ) {
				case 'all':
				case 'any':
					$this->searchWords( ( $this->_request[ 'phrase' ] == 'all' ) );
					break;
				case 'exact':
					$this->searchPhrase();
					break;
			}
			$this->_results = array_unique( $this->_results );
		}
		Sobi::Trigger( 'AfterBasic', 'Search', array( &$this->_results ) );

		/* ... now the extended search. Check which data we've recieved */
		if ( count( $this->_fields ) ) {
			$results = null;
			foreach ( $this->_fields as $field ) {
				if ( isset( $this->_request[ $field->get( 'nid' ) ] ) && ( $this->_request[ $field->get( 'nid' ) ] != null ) ) {
					$fr = $field->searchData( $this->_request[ $field->get( 'nid' ) ], Sobi::Section() );
					$priority = $field->get( 'priority' );
					if ( is_array( $fr ) ) {
						$this->_resultsByPriority[ $priority ] = array_merge( $this->_resultsByPriority[ $priority ], $fr );
					}
					/* if we didn't got any results before this array contains the results */
					if ( !( is_array( $results ) ) ) {
						$results = $fr;
					}
					/* otherwise intersect these two arrays */
					else {
						if ( is_array( $fr ) ) {
							$results = array_intersect( $results, $fr );
						}
					}
				}
			}
			if ( is_array( $results ) && count( $results ) ) {
				/* if we had also a string to search we have to get the intersection */
				if ( $searchForString ) {
					$this->_results = array_intersect( $this->_results, $results );
				}
				/* otherwise THESE are the results */
				else {
					$this->_results = $results;
				}
			}
		}
		$this->verify();
		/** @since 1.1 - a method to narrow the search results down */
		if ( count( $this->_fields ) ) {
			foreach ( $this->_fields as &$field ) {
				$request = isset( $this->_request[ $field->get( 'nid' ) ] ) ? $this->_request[ $field->get( 'nid' ) ] : null;
				if ( $request ) {
					$field->searchNarrowResults( $request, $this->_results );
				}
			}
		}
		$this->_request[ 'search_for' ] = str_replace( '%', '*', $this->_request[ 'search_for' ] );
		Sobi::Trigger( 'AfterExtended', 'Search', array( &$this->_results ) );
		$this->sortPriority();
		$req = ( is_array( $this->_request ) && count( $this->_request ) ) ? SPConfig::serialize( $this->_request ) : null;
		$res = ( is_array( $this->_results ) && count( $this->_results ) ) ? implode( ', ', $this->_results ) : null;
		$cre = ( is_array( $this->_categoriesResults ) && count( $this->_categoriesResults ) ) ? implode( ', ', $this->_categoriesResults ) : null;
		/* determine the search parameters */
		$attr = array(
			'entriesResults' => array( 'results' => $res, 'resultsByPriority' => $this->_resultsByPriority ),
			'catsResults' => $cre,
			'uid' => Sobi::My( 'id' ),
			'browserData' => SPConfig::serialize( SPBrowser::getInstance() )
		);
		if ( strlen( $req ) ) {
			$attr[ 'requestData' ] = $req;
		}

		/* finally save */
		try {
			Sobi::Trigger( 'OnSave', 'Search', array( &$attr, &$ssid ) );
			$this->_db->update( 'spdb_search', $attr, array( 'ssid' => $ssid ) );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_CREATE_SESSION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		$url = array( 'task' => 'search.results', 'sid' => Sobi::Section() );
		/* if we cannot transfer the search id in cookie */
		if ( !( SPRequest::cmd( 'ssid', null, 'cookie' ) ) ) {
			$url[ 'ssid' ] = $ssid;
		}
		Sobi::Redirect( Sobi::Url( $url ) );
	}

	protected function sortPriority()
	{
		foreach ( $this->_resultsByPriority as $prio => $ids ) {
			$this->_resultsByPriority[ $prio ] = array_unique( $ids );
			foreach ( $ids as $i => $sid ) {
				if ( !( in_array( $sid, $this->_results ) ) ) {
					unset( $this->_resultsByPriority[ $prio ][ $i ] );
				}
			}
		}
		foreach ( $this->_resultsByPriority as $prio => $ids ) {
			foreach ( $ids as $id ) {
				foreach ( $this->_resultsByPriority as $p => $sids ) {
					if ( $p <= $prio ) {
						continue;
					}
					foreach ( $sids as $i => $sid ) {
						if ( $sid == $id ) {
							unset( $this->_resultsByPriority[ $p ][ $i ] );
						}
					}
				}
			}
		}
		if ( Sobi::Cfg( 'search.entries_ordering', 'disabled' ) != 'disabled' ) {
			$this->_results = array();
			foreach ( $this->_resultsByPriority as $prio => $ids ) {
				if ( count( $ids ) ) {
					if ( Sobi::Cfg( 'search.entries_ordering', 'disabled' ) == 'random' ) {
						shuffle( $this->_resultsByPriority[ $prio ] );
					}
					else {
						$this->_resultsByPriority[ $prio ] = SPFactory::db()
								->select( 'id', 'spdb_object', array( 'id' => $ids ), Sobi::Cfg( 'search.entries_ordering', 'disabled' ) )
								->loadResultArray();
					}
					$this->_results = array_merge( $this->_results, $this->_resultsByPriority[ $prio ] );
				}
			}
		}
	}

	protected function verify()
	{
		if ( $this->_results ) {
			$conditions = array();
			if ( Sobi::My( 'id' ) ) {
				$this->userPermissionsQuery( $conditions, null );
			}
			else {
				$conditions = array( 'state' => '1', /* 'approved' => '1' ,*/
					'@VALID' => $this->_db->valid( 'validUntil', 'validSince' ) );
			}
			$conditions[ 'id' ] = $this->_results;
			$conditions[ 'oType' ] = 'entry';
			try {
				$this->_db->select( 'id', 'spdb_object', $conditions );
				$results = $this->_db->loadResultArray();
				foreach ( $this->_results as $i => $sid ) {
					if ( !( in_array( $sid, $results ) ) ) {
						unset( $this->_results[ $i ] );
					}
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			Sobi::Trigger( 'OnVerify', 'Search', array( &$this->_results ) );
		}
	}

	private function searchPhrase()
	{
		/* @TODO categories */
		$search = str_replace( '.', '\.', $this->_request[ 'search_for' ] );
		$this->_results = $this->travelFields( "REGEXP:[[:<:]]{$search}[[:>:]]", true );
	}

	private function searchWords( $all )
	{
		/* @TODO categories */
		$matches = array();

		/* extrapolate single words */
		preg_match_all( Sobi::Cfg( 'search.word_filter', '/\p{L}+|\d+|%/iu' ), $this->_request[ 'search_for' ], $matches );
		if ( count( $matches ) && isset( $matches[ 0 ] ) ) {
			$wordResults = array();
			$results = null;
			/* search all fields for this word */
			foreach ( $matches[ 0 ] as $word ) {
				$wordResults[ $word ] = $this->travelFields( $word );
			}
			if ( count( $wordResults ) ) {
				foreach ( $wordResults as $wordResult ) {
					if ( is_null( $results ) ) {
						$results = $wordResult;
					}
					else {
						if ( $all ) {
							if ( is_array( $wordResult ) ) {
								$results = array_intersect( $results, $wordResult );
							}
						}
						else {
							if ( is_array( $wordResult ) ) {
								$results = array_merge( $results, $wordResult );
							}
						}
					}
				}
			}
			$this->_results = $results;
		}
	}

	private function travelFields( $word, $regex = false )
	{
		$results = array();
		if ( count( $this->_fields ) ) {
			foreach ( $this->_fields as $field ) {
				$priority = $field->get( 'priority' );
				$fr = $field->searchString( $word, Sobi::Section(), $regex );
				if ( is_array( $fr ) && count( $fr ) ) {
					$results = array_merge( $results, $fr );
					$this->_resultsByPriority[ $priority ] = array_merge( $this->_resultsByPriority[ $priority ], $fr );
				}
			}
		}
		return $results;
	}

	private function sortByPrio( $obj, $to )
	{
		return ( $obj->get( 'priority' ) == $to->get( 'priority' ) ) ? 0 : ( ( $obj->get( 'priority' ) < $to->get( 'priority' ) ) ? -1 : 1 );
	}

	private function form()
	{
		$ssid = 0;
		/* determine template package */
		$tplPackage = Sobi::Cfg( 'section.template', 'default2' );

		/* load template config */
		$this->template();
		$this->tplCfg( $tplPackage, 'search' );
		if ( $this->template == 'results' ) {
			$this->template = 'view';
		}
		if ( !( $this->_model ) ) {
			$this->setModel( 'section' );
			$this->_model->init( Sobi::Section() );
		}

		/* handle meta data */
		SPFactory::header()->objMeta( $this->_model );
		$section = SPFactory::Section( Sobi::Section() );
		SPFactory::header()
				->addKeyword( $section->get( 'sfMetaKeys' ) )
				->addDescription( $section->get( 'sfMetaDesc' ) );

		/* add pathway */
		SPFactory::mainframe()->addToPathway( Sobi::Txt( 'SH.PATH_TITLE' ), Sobi::Url( 'current' ) );
		SPFactory::mainframe()->setTitle( Sobi::Txt( 'SH.TITLE', array( 'section' => $this->_model->get( 'name' ) ) ) );


		Sobi::Trigger( 'OnFormStart', 'Search' );
		SPLoader::loadClass( 'mlo.input' );
		$view = SPFactory::View( 'search' );

		/* if we cannot transfer the search id in cookie */
		if ( !( $this->session( $ssid ) ) ) {
			$view->addHidden( $ssid, 'ssid' );
		}
		if ( $this->_task == 'results' && $ssid ) {
			/* get limits - if defined in template config - otherwise from the section config */
			$eLimit = $this->tKey( $this->template, 'entries_limit', Sobi::Cfg( 'search.entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) ) );
			$eInLine = $this->tKey( $this->template, 'entries_in_line', Sobi::Cfg( 'search.entries_in_line', Sobi::Cfg( 'list.entries_in_line', 2 ) ) );
			/* get the site to display */
			$site = SPRequest::int( 'site', 1 );
			$eLimStart = ( ( $site - 1 ) * $eLimit );
			$view->assign( $eLimit, '$eLimit' );
			$view->assign( $eLimStart, '$eLimStart' );
			$view->assign( $eInLine, '$eInLine' );
			$entries = $this->getResults( $ssid, $this->template );
			$view->assign( count( $this->_results ), '$eCount' );
			$view->assign( $this->_resultsByPriority, 'priorities' );
			$view->assign( $entries, 'entries' );
			/* create page navigation */
			$pnc = SPLoader::loadClass( 'helpers.pagenav_' . $this->tKey( $this->template, 'template_type', 'xslt' ) );
			$url = array( 'task' => 'search.results', 'sid' => SPRequest::sid() );
			if ( !( SPRequest::cmd( 'ssid', null, 'cookie' ) ) ) {
				$url[ 'ssid' ] = $ssid;
			}
			/* @var SPPageNavXSLT $pn */
			$pn = new $pnc( $eLimit, $this->_resultsCount, $site, $url );
			$view->assign( $pn->get(), 'navigation' );
		}
		else {
			$eLimit = -1;
			$view->assign( $eLimit, '$eCount' );
		}
		/* load all fields */
		$fields = $this->loadFields();
		if ( isset( $this->_request[ 'search_for' ] ) ) {
			$view->assign( $this->_request[ 'search_for' ], 'search_for' );
			$view->assign( $this->_request[ 'phrase' ], 'search_phrase' );
		}
		$view->assign( $fields, 'fields' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->assign( $this->_task, 'task' );
		$view->addHidden( Sobi::Section(), 'sid' );
		$view->addHidden( 'search.search', 'task' );
		$view->setConfig( $this->_tCfg, $this->template );
		$view->setTemplate( $tplPackage . '.' . $this->templateType . '.' . $this->template );
		Sobi::Trigger( 'OnCreateView', 'Search', array( &$view ) );
		$view->display();
	}

	private function getResults( $ssid, $template )
	{
		$results = array();
		/* case some plugin overwrites this method */
		Sobi::Trigger( 'GetResults', 'Search', array( &$results, &$ssid, &$template ) );
		if ( count( $results ) ) {
			return $results;
		}
		/* get limits - if defined in template config - otherwise from the section config */
		$eLimit = $this->tKey( $template, 'entries_limit', Sobi::Cfg( 'search.entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) ) );
		$eInLine = $this->tKey( $template, 'entries_in_line', Sobi::Cfg( 'search.entries_in_line', Sobi::Cfg( 'list.entries_in_line', 2 ) ) );

		/* get the site to display */
		$site = SPRequest::int( 'site', 1 );
		$eLimStart = ( ( $site - 1 ) * $eLimit );

		try {
			$this->_db->select( array( 'entriesResults', 'requestData' ), 'spdb_search', array( 'ssid' => $ssid ) );
			$r = $this->_db->loadAssocList();
			if ( strlen( $r[ 0 ][ 'entriesResults' ] ) ) {
				$store = SPConfig::unserialize( $r[ 0 ][ 'entriesResults' ] );
				if ( $store[ 'results' ] ) {
					$this->_results = explode( ',', $store[ 'results' ] );
					$this->_resultsByPriority = $store[ 'resultsByPriority' ];
				}
				$this->_resultsCount = count( $this->_results );
			}
			$this->_request = SPConfig::unserialize( $r[ 0 ][ 'requestData' ] );
			if ( count( $this->_results ) ) {
				$r = array_slice( $this->_results, $eLimStart, $eLimit );
				/* so we have a results */
				foreach ( $r as $i => $sid ) {
					$results[ $i ] = ( int )$sid;
					//$results[ $i ] = new $eClass();
					//$results[ $i ]->init( $sid );
				}
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_SESSION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		return $results;
	}

	private function session( &$ssid )
	{
		/* if it wasn't new search */
		$ssid = SPRequest::cmd( 'ssid', SPRequest::cmd( 'ssid', null, 'cookie' ) );
		$new = false;
		/* otherwise create new ssid */
		if ( !$ssid ) {
			$ssid = ( microtime( true ) * 100 ) . '.' . rand( 0, 99 );
			$new = true;
		}

		$attr = array(
			'ssid' => $ssid,
			'uid' => Sobi::My( 'id' ),
			'browserData' => SPConfig::serialize( SPBrowser::getInstance() )
		);

		/* get search request */
		if ( !( count( $this->_request ) ) ) {
			$r = SPRequest::search( 'field_' );
			if ( is_array( $r ) && count( $r ) ) {
				$attr[ 'requestData' ] = SPConfig::serialize( $r );
			}
		}
		/* determine the search parameters */
		if ( $new ) {
			$attr[ 'searchCreated' ] = 'FUNCTION:NOW()';
		}
		/* finally save */
		try {
			$this->_db->insertUpdate( 'spdb_search', $attr );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_CREATE_SESSION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		return SPCookie::set( 'ssid', $ssid, SPCookie::days( 7 ) );
	}

	private function loadFields()
	{
		$fields = null;
		$fmod = SPLoader::loadModel( 'field' );
		/* get fields */
		try {
			$this->_db->select( '*', 'spdb_field', array( 'section' => Sobi::Section(), 'inSearch' => 1, 'enabled' => 1 ), 'position' );
			$fields = $this->_db->loadObjectList();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		if ( count( $fields ) ) {
			foreach ( $fields as $i => $f ) {
				/* @var SPField $field */
				$field = new $fmod();
				$field->extend( $f );
				if ( count( $this->_request ) && isset( $this->_request[ $field->get( 'nid' ) ] ) ) {
					$field->setSelected( $this->_request[ $field->get( 'nid' ) ] );
				}
				$fields[ $i ] = $field;
			}
		}
		Sobi::Trigger( 'LoadField', 'Search', array( &$fields ) );
		return $fields;
	}
}
