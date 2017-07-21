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

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'section' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 29-March-2010 13:08:21
 */
class SPSearchCtrl extends SPSectionCtrl
{
	/*** @var string */
	protected $_type = 'search';
	/*** @var string */
	protected $_defTask = 'view';
	/*** @var array */
	protected $_request = [];
	/*** @var array */
	protected $_fields = [];
	/*** @var array */
	protected $_results = [];
	/*** @var array */
	protected $_resultsByPriority = [];
	/*** @var int */
	protected $_resultsCount = 0;
	/*** @var array */
	protected $_categoriesResults = [];
	/*** @var SPDb */
	protected $_db = [];
	/*** @var bool */
	protected $_narrowing = true;

	public function __construct()
	{
		$this->_db =& SPFactory::db();
		parent::__construct();
		/** because we have always the same URL - disable Joomla! cache */
		SPFactory::cache()->setJoomlaCaching( false );
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

	protected function suggest()
	{
		$this->_request[ 'search_for' ] = str_replace( '*', '%', SPRequest::string( 'term', null ) );
		$fieldNid = SPRequest::string( 'fid', null );
		if ( strlen( $fieldNid ) ) {
			$fieldNids = SPFactory::config()->structuralData( $fieldNid, true );
		}
		$results = [];
		if ( strlen( $this->_request[ 'search_for' ] ) >= Sobi::Cfg( 'search.suggest_min_chars', 1 ) ) {
			Sobi::Trigger( 'OnSuggest', 'Search', [ &$this->_request[ 'search_for' ] ] );
			$this->_fields = $this->loadFields();
			$search = str_replace( '.', '\.', $this->_request[ 'search_for' ] );
			if ( count( $this->_fields ) ) {
				foreach ( $this->_fields as $field ) {
					if ( !( $field->get( 'suggesting' ) ) || ( count( $fieldNids ) && !( in_array( $field->get( 'nid' ), $fieldNids ) ) ) ) {
						continue;
					}
					else {
						$fr = $field->searchSuggest( $search, Sobi::Section(), Sobi::Cfg( 'search.suggest_start_with', false ) );
						if ( is_array( $fr ) && count( $fr ) ) {
							$results = array_merge( $results, $fr );
						}
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
//		usort( $results, [ 'self', 'sortByLen' ] );
		if ( class_exists( 'Collator' ) ) {
			$collator = new Collator( Sobi::Cfg( 'language' ) );
			$collator->sort( $results );
		}
		else {
			natcasesort( $results );
		}

		Sobi::Trigger( 'AfterSuggest', 'Search', [ &$results ] );
		if ( count( $results ) ) {
			foreach ( $results as $i => $term ) {
				$results[ $i ] = SPLang::clean( $term );
			}
		}
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		echo json_encode( (array)array_values( $results ) );
		exit();
	}

	protected function sortByLen( $from, $to )
	{
		return strlen( $to ) - strlen( $from );
	}

	protected function search()
	{
		$this->_request = SPRequest::search( 'field_' );
		$this->_request[ 'search_for' ] = str_replace( '*', '%', SPRequest::string( 'sp_search_for', null ) );
		$this->_request[ 'phrase' ] = SPRequest::string( 'spsearchphrase', Sobi::Cfg( 'search.form_searchphrase_def', 'all' ) );
		$this->_request[ 'phrase' ] = strlen( $this->_request[ 'phrase' ] ) ? $this->_request[ 'phrase' ] : Sobi::Cfg( 'search.form_searchphrase_def', 'all' );
		$ssid = SPRequest::cmd( 'ssid', SPRequest::cmd( 'ssid', null, 'cookie' ) );
		$this->_fields = $this->loadFields();
		$searchForString = false;
		Sobi::Trigger( 'OnRequest', 'Search', [ &$this->_request ] );
		$searchLimit = Sobi::Cfg( 'search.result_limit', 1000 );
		for ( $i = 1; $i < 11; $i++ ) {
			$this->_resultsByPriority[ $i ] = [];
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
		usort( $this->_fields, [ 'self', 'sortByPrio' ] );

		/* First the basic search ..... */
		/* if we have a string to search */
		if ( strlen( $this->_request[ 'search_for' ] ) && $this->_request[ 'search_for' ] != Sobi::Txt( 'SH.SEARCH_FOR_BOX' ) ) {
			$searchForString = true;
			$this->_narrowing = true;
			switch ( $this->_request[ 'phrase' ] ) {
				case 'exact':
					$this->searchPhrase();
					break;
				default:
				case 'all':
				case 'any':
					$this->searchWords( ( $this->_request[ 'phrase' ] == 'all' ) );
					break;
			}
			$this->_results = array_unique( $this->_results );
		}
		Sobi::Trigger( 'AfterBasic', 'Search', [ &$this->_results, &$this->_resultsByPriority ] );

		/* ... now the extended search. Check which data we've received */
		if ( count( $this->_fields ) ) {
			$results = null;
			foreach ( $this->_fields as $field ) {
				if ( isset( $this->_request[ $field->get( 'nid' ) ] ) && ( $this->_request[ $field->get( 'nid' ) ] != null ) ) {
					$this->_narrowing = true;
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
			/** Tue, Oct 21, 2014 10:18:37
			 * No result is also a result so no "count"
			 * */
//			if ( is_array( $results ) && count( $results ) ) {
			if ( is_array( $results ) ) {
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
		/** @since 1.1 - a method to narrow the search results down */
		if ( count( $this->_fields ) ) {
			// If we have any results already - the we are limiting results down
			// if we don't have results but we were already searching then skip - because there is nothing to narrow down
			// if we don't have results but we weren't searching for anything else - then we are narrowing down everything
			if ( count( $this->_results ) || !( $this->_narrowing ) ) {
				foreach ( $this->_fields as &$field ) {
					$request = isset( $this->_request[ $field->get( 'nid' ) ] ) ? $this->_request[ $field->get( 'nid' ) ] : null;
					if ( $request ) {
						$field->searchNarrowResults( $request, $this->_results, $this->_resultsByPriority );
					}
				}
			}
		}
		$this->_request[ 'search_for' ] = str_replace( '%', '*', $this->_request[ 'search_for' ] );
		Sobi::Trigger( 'AfterExtended', 'Search', [ &$this->_results, &$this->_resultsByPriority ] );
		if ( count( $this->_results ) > $searchLimit ) {
			SPFactory::message()->error( Sobi::Txt( 'SH.SEARCH_TOO_MANY_RESULTS', count( $this->_results ), $searchLimit ), false );
			$this->_resultsByPriority = [];
			$this->_results = array_slice( $this->_results, 0, $searchLimit );
		}
		else {
			$this->sortPriority();
		}
		$req = ( is_array( $this->_request ) && count( $this->_request ) ) ? SPConfig::serialize( $this->_request ) : null;
		$res = ( is_array( $this->_results ) && count( $this->_results ) ) ? implode( ', ', $this->_results ) : null;
		$cre = ( is_array( $this->_categoriesResults ) && count( $this->_categoriesResults ) ) ? implode( ', ', $this->_categoriesResults ) : null;
		/* determine the search parameters */
		$attr = [
				'entriesResults' => [ 'results' => $res, 'resultsByPriority' => $this->_resultsByPriority ],
				'catsResults' => $cre,
				'uid' => Sobi::My( 'id' ),
				'browserData' => SPConfig::serialize( SPBrowser::getInstance() )
		];
		if ( strlen( $req ) ) {
			$attr[ 'requestData' ] = $req;
		}

		/* finally save */
		try {
			Sobi::Trigger( 'OnSave', 'Search', [ &$attr, &$ssid ] );
			$this->verify( $attr[ 'entriesResults' ][ 'results' ] );
			$this->_db->update( 'spdb_search', $attr, [ 'ssid' => $ssid ] );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_CREATE_SESSION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		$url = [ 'task' => 'search.results', 'sid' => Sobi::Section() ];
		// For Peter's Components Anywhere extension and other
		$params = Sobi::Cfg( 'search.params_to_pass' );
		if ( count( $params ) ) {
			foreach ( $params as $param ) {
				$val = SPRequest::raw( $param );
				if ( $val ) {
					$url[ $param ] = SPRequest::raw( $param );
				}
			}
		}

		/* if we cannot transfer the search id in cookie */
		if ( !( SPRequest::cmd( 'ssid', null, 'cookie' ) ) ) {
			$url[ 'ssid' ] = $ssid;
		}

		if ( Sobi::Cfg( 'cache.unique_search_url' ) ) {
			$url[ 't' ] = microtime( true );
		}
		Sobi::Redirect( Sobi::Url( $url ) );
	}

	protected function sortPriority()
	{
		if ( Sobi::Cfg( 'search.entries_ordering', 'disabled' ) != 'disabled' ) {
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
			foreach ( $this->_resultsByPriority as $prio => $ids ) {
				if ( count( $ids ) ) {
					$this->_resultsByPriority[ $prio ] = array_unique( $ids );
				}
			}
			$this->_results = [];
			foreach ( $this->_resultsByPriority as $prio => $ids ) {
				if ( count( $ids ) ) {
					if ( Sobi::Cfg( 'search.entries_ordering' ) == 'random' ) {
						shuffle( $this->_resultsByPriority[ $prio ] );
					}
					elseif ( Sobi::Cfg( 'search.entries_ordering' ) == 'counter.asc' || Sobi::Cfg( 'search.entries_ordering' ) == 'counter.desc' ) {
						$this->_resultsByPriority[ $prio ] = SPFactory::db()
								->select( 'sid', 'spdb_counter', [ 'sid' => $ids ], Sobi::Cfg( 'search.entries_ordering' ) )
								->loadResultArray();
					}
					else {
						$this->_resultsByPriority[ $prio ] = SPFactory::db()
								->select( 'id', 'spdb_object', [ 'id' => $ids ], Sobi::Cfg( 'search.entries_ordering' ) )
								->loadResultArray();
					}
					$this->_results = array_merge( $this->_results, $this->_resultsByPriority[ $prio ] );
				}
			}
		}
		else {
			$this->_resultsByPriority = [];
		}
	}

	protected function verify( &$entries )
	{
		if ( $entries ) {
			$entries = explode( ',', $entries );
			$conditions = [];
			if ( Sobi::My( 'id' ) ) {
				$this->userPermissionsQuery( $conditions, null );
			}
			else {
				$conditions = [ 'state' => '1', '@VALID' => $this->_db->valid( 'validUntil', 'validSince' ) ];
			}
			$conditions[ 'id' ] = $entries;
			$conditions[ 'oType' ] = 'entry';
			try {
				$results = $this->_db->select( 'id', 'spdb_object', $conditions )
						->loadResultArray();
				foreach ( $entries as $i => $sid ) {
					if ( !( in_array( $sid, $results ) ) ) {
						unset( $entries[ $i ] );
					}
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			Sobi::Trigger( 'OnVerify', 'Search', [ &$entries ] );
		}
	}

	protected function searchPhrase()
	{
		/* @TODO categories */
		$search = str_replace( '.', '\.', $this->_request[ 'search_for' ] );
		$this->_results = $this->travelFields( "REGEXP:[[:<:]]{$search}[[:>:]]", true );
	}

	protected function searchWords( $all )
	{
		/* @TODO categories */
		$matches = [];

		/* extrapolate single words */
		preg_match_all( Sobi::Cfg( 'search.word_filter', '/\p{L}+|\d+|%/iu' ), $this->_request[ 'search_for' ], $matches );
		if ( count( $matches ) && isset( $matches[ 0 ] ) ) {
			$wordResults = [];
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

	protected function travelFields( $word, $regex = false )
	{
		$results = [];
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

	protected function sortByPrio( $obj, $to )
	{
		return ( $obj->get( 'priority' ) == $to->get( 'priority' ) ) ? 0 : ( ( $obj->get( 'priority' ) < $to->get( 'priority' ) ) ? -1 : 1 );
	}

	protected function form()
	{
		$ssid = 0;
		/* determine template package */
		$tplPackage = Sobi::Cfg( 'section.template', SPC::DEFAULT_TEMPLATE );

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
		if ( Sobi::Cfg( 'meta.always_add_section' ) ) {
			SPFactory::header()->objMeta( $this->_model );
		}
		$section = SPFactory::Section( Sobi::Section() );
		SPFactory::header()
				->addKeyword( $section->get( 'sfMetaKeys' ) );

		$desc = $section->get( 'sfMetaDesc' );
		if ( $desc ) {
			$separator = Sobi::Cfg( 'meta.separator', '.' );
			$desc .= $separator;
			SPFactory::header()
					->addDescription( $desc );
		}

		if ( Sobi::Cfg( 'search.highlight-search' ) ) {
			SPFactory::header()->addJsFile( [ 'jquery-highlight', 'search-highlight' ] );
		}

		Sobi::Trigger( 'OnFormStart', 'Search' );
		SPLoader::loadClass( 'mlo.input' );

		SPFactory::mainframe()->setTitle( Sobi::Txt( 'SH.TITLE', [ 'section' => $this->_model->get( 'name' ) ] ) );

		/** @var SPSearchView $view */
		$view = SPFactory::View( 'search' );

		/* if we cannot transfer the search id in cookie */
		if ( !( $this->session( $ssid ) ) ) {
			$view->addHidden( $ssid, 'ssid' );
		}

		if ( $this->_task == 'results' && $ssid ) {
			/* add pathway */
			SPFactory::mainframe()->addToPathway( Sobi::Txt( 'SH.PATH_TITLE_RESULT' ), Sobi::Url( 'current' ) );

			/* get limits - if defined in template config - otherwise from the section config */
			$eLimit = $this->tKey( $this->template, 'entries_limit', Sobi::Cfg( 'search.entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) ) );
			$eInLine = $this->tKey( $this->template, 'entries_in_line', Sobi::Cfg( 'search.entries_in_line', Sobi::Cfg( 'list.entries_in_line', 2 ) ) );
			/* get the site to display */
			$site = SPRequest::int( 'site', 1 );
			$eLimStart = ( ( $site - 1 ) * $eLimit );
			$view->assign( $eLimit, '$eLimit' )
					->assign( $eLimStart, '$eLimStart' )
					->assign( $eInLine, '$eInLine' );
			$entries = $this->getResults( $ssid, $this->template );
			$count = count( $this->_results );
			$view->assign( $count, '$eCount' )
					->assign( $this->_resultsByPriority, 'priorities' )
					->assign( $entries, 'entries' );
			/* create page navigation */
			$pnc = SPLoader::loadClass( 'helpers.pagenav_' . $this->tKey( $this->template, 'template_type', 'xslt' ) );
			$url = [ 'task' => 'search.results', 'sid' => SPRequest::sid() ];
			if ( !( SPRequest::cmd( 'ssid', null, 'cookie' ) ) ) {
				$url[ 'ssid' ] = $ssid;
			}
			/* @var SPPageNavXSLT $pn */
			$pn = new $pnc( $eLimit, $this->_resultsCount, $site, $url );
			$nav = $pn->get();
			$view->assign( $nav, 'navigation' );
			/**
			 * this is te special case:
			 * no matter what task we currently have - if someone called this we need the data for the V-Card
			 * Soe we have to trigger all these plugins we need and therefore also fake the task
			 */
			$task = 'list.custom';
			SPFactory::registry()->set( 'task', $task );
		}
		else {
			/* add pathway */
			SPFactory::mainframe()->addToPathway( Sobi::Txt( 'SH.PATH_TITLE' ), Sobi::Url( 'current' ) );
			$eLimit = -1;
			$view->assign( $eLimit, '$eCount' );
		}
		/* load all fields */
		$fields = $this->loadFields();
		if ( isset( $this->_request[ 'search_for' ] ) ) {
			$view->assign( $this->_request[ 'search_for' ], 'search_for' )
					->assign( $this->_request[ 'phrase' ], 'search_phrase' );
		}
		$visitor = SPFactory::user()->getCurrent();
		$sid = Sobi::Section();
		$view->assign( $fields, 'fields' )
				->assign( $visitor, 'visitor' )
				->assign( $this->_task, 'task' )
				->addHidden( $sid, 'sid' )
				->addHidden( 'search.search', 'task' )
				->setConfig( $this->_tCfg, $this->template )
				->setTemplate( $tplPackage . '.' . $this->templateType . '.' . $this->template );
		Sobi::Trigger( 'OnCreateView', 'Search', [ &$view ] );
		$view->display();
	}

	protected function getResults( $ssid, $template )
	{
		$results = [];
		/* case some plugin overwrites this method */
		Sobi::Trigger( 'GetResults', 'Search', [ &$results, &$ssid, &$template ] );
		if ( count( $results ) ) {
			return $results;
		}
		/* get limits - if defined in template config - otherwise from the section config */
		$eLimit = $this->tKey( $template, 'entries_limit', Sobi::Cfg( 'search.entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) ) );

		/* get the site to display */
		$site = SPRequest::int( 'site', 1 );
		$eLimStart = ( ( $site - 1 ) * $eLimit );

		try {
			$r = $this->_db->select( [ 'entriesResults', 'requestData' ], 'spdb_search', [ 'ssid' => $ssid ] )
					->loadAssocList();
			if ( strlen( $r[ 0 ][ 'entriesResults' ] ) ) {
				$store = SPConfig::unserialize( $r[ 0 ][ 'entriesResults' ] );
				if ( $store[ 'results' ] ) {
					$this->_results = array_unique( $store[ 'results' ] );
					$this->_resultsByPriority = $store[ 'resultsByPriority' ];
				}
				$this->_resultsCount = count( $this->_results );
			}
			$this->_request = SPConfig::unserialize( $r[ 0 ][ 'requestData' ] );
			SPFactory::registry()->set( 'requestcache', $this->_request );
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
		Sobi::SetUserData( 'currently-displayed-entries', $results );

		return $results;
	}

	protected function session( &$ssid )
	{
		/* if it wasn't new search */
		$ssid = SPRequest::cmd( 'ssid', SPRequest::cmd( 'ssid', null, 'cookie' ) );
		$new = false;
		/* otherwise create new ssid */
		if ( !$ssid ) {
			$ssid = ( microtime( true ) * 100 ) . '.' . rand( 0, 99 );
			$new = true;
		}

		$attr = [
				'ssid' => $ssid,
				'uid' => Sobi::My( 'id' ),
				'browserData' => SPConfig::serialize( SPBrowser::getInstance() )
		];

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

	protected function loadFields()
	{
		$fields = null;
		$fmod = SPLoader::loadModel( 'field' );
		/* get fields */
		try {
			$fields = $this->_db
					->select( '*', 'spdb_field', [ 'section' => Sobi::Section(), 'inSearch' => 1, 'enabled' => 1, 'adminField>' => -1 ], 'position' )
					->loadObjectList();
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
		Sobi::Trigger( 'LoadFields', 'Search', [ &$fields ] );

		return $fields;
	}
}
