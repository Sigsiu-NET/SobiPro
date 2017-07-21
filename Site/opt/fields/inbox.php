<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

use Sobi\Input\Input;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.fieldtype' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Sep-2009 12:52:45 PM
 */
class SPField_Inbox extends SPFieldType implements SPFieldInterface
{
	/** * @var int */
	protected $maxLength = 150;
	/** * @var int */
	protected $width = 350;
	/** * @var int */
	protected $bsWidth = 6;
	/** * @var string */
	protected $cssClass = 'spClassInbox';
	/** * @var string */
	protected $cssClassView = 'spClassViewInbox';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditInbox';
	/** * @var string */
	protected $cssClassSearch = 'spClassSearchInbox';
	/** * @var string */
	protected $searchRangeValues = "";
	/** @var bool */
	protected $freeRange = false;
	/** * @var string */
	protected $searchMethod = 'general';
	/** * @var int */
	protected $bsSearchWidth = 6;
	/** * @var string */
	protected $itemprop = '';
	/** * @var string */
	protected $metaSeparator = ' ';
	/** @var bool */
	protected $labelAsPlaceholder = false;
	/** @var bool */
	static private $CAT_FIELD = true;


	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$class = $this->required ? $this->cssClass . ' required' : $this->cssClass;
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( $this->bsWidth ) {
				$width = SPHtml_Input::_translateWidth( $this->bsWidth );
				$class .= ' ' . $width;
			}
		}

		$params = [ 'id' => $this->nid, /*'size' => $this->width,*/
				'class' => $class ];
		if ( $this->maxLength ) {
			$params[ 'maxlength' ] = $this->maxLength;
		}
		//for compatibility reason still there
		if ( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}
		if ( $this->labelAsPlaceholder ) {
			$params[ 'placeholder' ] = $this->__get( 'name' );
		}

		$value = $this->getRaw();
		$value = strlen( $value ) ? $value : $this->defaultValue;

		$field = SPHtml_Input::text( $this->nid, $value, $params );
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return [ 'suggesting', 'maxLength', 'width', 'searchMethod', 'searchRangeValues', 'freeRange', 'itemprop', 'metaSeparator', 'cssClassView', 'cssClassSearch', 'cssClassEdit', 'showEditLabel', 'labelAsPlaceholder', 'defaultValue', 'bsWidth', 'bsSearchWidth' ];
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsId
	 * @param string $request
	 * @return array
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' )
	{
		$data = $this->verify( $entry, $request );
		if ( strlen( $data ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return [];
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @throws SPException
	 * @return string
	 */
	private function verify( $entry, $request )
	{
		$data = SPRequest::raw( $this->nid, null, $request );
		$dexs = strlen( $data );
		/* check if it was required */
		if ( $this->required && !( $dexs ) ) {
			throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
		}
		/* check if there was a filter */
		if ( $this->filter && $dexs ) {
			static $filters = null;
			if ( !( $filters ) ) {
				$registry = SPFactory::registry();
				$registry->loadDBSection( 'fields_filter' );
				$filters = $registry->get( 'fields_filter' );
			}
			$filter = isset( $filters[ $this->filter ] ) ? $filters[ $this->filter ] : null;
			if ( !( count( $filter ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_FILTER_ERR', $this->filter ) );
			}
			else {
				if ( !( preg_match( base64_decode( $filter[ 'params' ] ), $data ) ) ) {
					throw new SPException( str_replace( '$field', $this->name, SPLang::e( $filter[ 'description' ] ) ) );
				}
			}
		}
		/* check if there was an adminField */
		if ( $this->adminField && $dexs ) {
			if ( !( Sobi:: Can( 'entry.adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->name ) );
			}
		}
		/* check if it was free */
		if ( !( $this->isFree ) && $this->fee && $dexs ) {
			SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
		}
		/* check if it should contains unique data */
		if ( $this->uniqueData && $dexs ) {
			$matches = $this->searchData( $data, Sobi::Reg( 'current_section' ) );
			if ( count( $matches ) > 1 || ( ( count( $matches ) == 1 ) && ( $matches[ 0 ] != $entry->get( 'id' ) ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_UNIQUE', $this->name ) );
			}
		}
		/* check if it was editLimit */
		if ( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
		}
		/* check if it was editable */
		if ( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs && $entry->get( 'version' ) > 1 ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
		}
		if ( !( $dexs ) ) {
			$data = null;
		}
		$this->setData( $data );
		return $data;
	}


	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @param string $request
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}

		$data = $this->verify( $entry, $request );
		$time = Input::Now();
		$IP = Input::Ip4( 'REMOTE_ADDR' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */
		/* @var SPdb $db */
		$db = SPFactory::db();

		/* collect the needed params */
		$params = [];
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
		$params[ 'params' ] = null;
		$params[ 'options' ] = null;
		$params[ 'baseData' ] = strip_tags( $db->escape( trim( $data ) ) );
		$params[ 'approved' ] = $entry->get( 'approved' );
		$params[ 'confirmed' ] = $entry->get( 'confirmed' );
		/* if it is the first version, it is new entry */
		if ( $entry->get( 'version' ) == 1 ) {
			$params[ 'createdTime' ] = $time;
			$params[ 'createdBy' ] = $uid;
			$params[ 'createdIP' ] = $IP;
		}
		$params[ 'updatedTime' ] = $time;
		$params[ 'updatedBy' ] = $uid;
		$params[ 'updatedIP' ] = $IP;
		$params[ 'copy' ] = !( $entry->get( 'approved' ) );
		if ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) {
			--$this->editLimit;
		}
		$params[ 'editLimit' ] = $this->editLimit;

		/* save it */
		try {
			/* Notices:
				* If it was new entry - insert
				* If it was an edit and the field wasn't filled before - insert
				* If it was an edit and the field was filled before - update
				*     " ... " and changes are not autopublish it should be insert of the copy .... but
				* " ... " if a copy already exist it is update again
				* */
			$db->insertUpdate( 'spdb_field_data', $params );
		} catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		/* if it wasn't edited in the default language, we have to try to insert it also for def lang */
		if ( Sobi::Lang() != Sobi::DefLang() ) {
			$params[ 'lang' ] = Sobi::DefLang();
			try {
				$db->insert( 'spdb_field_data', $params, true, true );
			} catch ( SPException $x ) {
				Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
	}

	/**
	 * Shows the field in the search form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function searchForm( $return = false )
	{
		if ( $this->searchMethod == 'general' ) {
			return false;
		}
		if ( $this->searchMethod == 'range' ) {
			return $this->rangeSearch( $this->searchRangeValues, $this->freeRange );
		}
		$fdata = [];
		try {
			$data = SPFactory::db()
					->dselect( [ 'baseData', 'sid', 'lang' ], 'spdb_field_data', [ 'fid' => $this->fid, 'copy' => '0', 'enabled' => 1 ], 'field( lang, \'' . Sobi::Lang() . '\'), baseData', 0, 0, 'baseData' )
					->loadAssocList();
			$languages = [];
			$output = [];
			$lang = Sobi::Lang( false );
			$defLang = Sobi::DefLang();
			if ( count( $data ) ) {
				foreach ( $data as $row ) {
					$languages[ $row[ 'lang' ] ][ $row[ 'sid' ] ] = $row[ 'baseData' ];
				}
			}
			if ( isset( $languages[ $lang ] ) ) {
				foreach ( $languages[ $lang ] as $sid => $fieldData ) {
					$output[ $sid ] = $fieldData;
				}
				unset( $languages[ $lang ] );
			}
			if ( isset( $languages[ $defLang ] ) ) {
				foreach ( $languages[ $defLang ] as $sid => $fieldData ) {
					if ( !( isset( $output[ $sid ] ) ) ) {
						$output[ $sid ] = $fieldData;
					}
				}
				unset( $languages[ $defLang ] );
			}
			if ( count( $languages ) ) {
				foreach ( $languages as $language => $langData ) {
					foreach ( $langData as $sid => $fieldData ) {
						if ( !( isset( $output[ $sid ] ) ) ) {
							$output[ $sid ] = $fieldData;
						}
					}
					unset( $languages[ $language ] );
				}
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DATA_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		$data = ( ( array )$output );
		if ( count( $data ) ) {
			$fdata[ '' ] = Sobi::Txt( 'FD.INBOX_SEARCH_SELECT', [ 'name' => $this->name ] );
			foreach ( $data as $i => $d ) {
				if ( strlen( $d ) ) {
					$fdata[ strip_tags( $d ) ] = strip_tags( $d );
				}
			}
		}
		if ( function_exists( 'iconv' ) ) {
			uasort( $fdata, function ( $a, $b ) {
				return strcmp( iconv( 'UTF-8', 'ASCII//TRANSLIT', $a ), iconv( 'UTF-8', 'ASCII//TRANSLIT', $b ) );
			} );
		}
		else {
			asort( $fdata );
		}

		return SPHtml_Input::select( $this->nid, $fdata, $this->_selected, false, [ 'class' => $this->cssClass . ' ' . Sobi::Cfg( 'search.form_list_def_css', 'SPSearchSelect' ), 'size' => '1', 'id' => $this->nid ] );
	}

	/**
	 * @param string $data
	 * @param int $section
	 * @param bool $regex
	 * @return array
	 */
	public function searchString( $data, $section, $regex = false )
	{
		return $this->search( ( $regex ? $data : "%{$data}%" ), $section );
	}

	/**
	 * @param string $data
	 * @param int $section
	 * @param bool $startWith
	 * @param bool $ids
	 * @return array
	 */
	public function searchSuggest( $data, $section, $startWith = true, $ids = false )
	{
//		$nid = $this->__get('nid');
		$terms = [];
		$data = $startWith ? "{$data}%" : "%{$data}%";
		$request = [ 'baseData' ];
		if ( $ids ) {
			$request[] = 'sid';
		}
		try {
			if ( $ids ) {
				$conditions = [ 'fid' => $this->fid, 'baseData' => $data, 'section' => $section ];
				if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
					$conditions[ 'copy' ] = 0;
					$conditions[ 'enabled' ] = 1;
				}
				$result = SPFactory::db()
//					->dselect( $request, 'spdb_field_data', $conditions, [ 'baseData' ] )
						->dselect( $request, 'spdb_field_data', $conditions )
						->loadAssocList();
				$terms = [];
				if ( count( $result ) ) {
					foreach ( $result as $row ) {
						$terms[] = [ 'id' => $row[ 'sid' ], 'name' => SPLang::clean( $row[ 'baseData' ] ) ];
					}
				}
			}
			else {
				$terms = SPFactory::db()
						->select( $request, 'spdb_field_data', [ 'fid' => $this->fid, 'copy' => '0', 'enabled' => 1, 'baseData' => $data, 'section' => $section ], 'baseData' )
						->loadResultArray();
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SEARCH_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		return $terms;
	}

	/**
	 * @param string $data
	 * @param int $section
	 * @return array
	 */
	private function search( $data, $section )
	{
		$sids = [];
		try {
			$sids = SPFactory::db()
					/** Fri, Oct 9, 2015 15:10:42
					 * We do not need the enabled / copy check as all entries are being verified in the search controller anyway
					 * ->dselect( 'sid', 'spdb_field_data', array( 'fid' => $this->fid, 'copy' => '0', 'enabled' => 1, 'baseData' => $data, 'section' => $section ) )
					 */
					->dselect( 'sid', 'spdb_field_data', [ 'fid' => $this->fid, 'baseData' => $data, 'section' => $section ] )
					->loadResultArray();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SEARCH_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		return $sids;
	}

	/* (non-PHPdoc)
	  * @see Site/opt/fields/SPFieldType#searchData()
	  */
	public function searchData( $request, $section )
	{
		if ( is_array( $request ) && ( isset( $request[ 'from' ] ) || isset( $request[ 'to' ] ) ) && ( $request[ 'from' ] || $request[ 'to' ] ) ) {
			return $this->searchForRange( $request, $section );
		}
		else {
			$request = preg_quote( $request );
			return $this->search( "REGEXP:^{$request}$", $section );
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		return $this->verify( $entry, $request );
	}
}
