<?php
/**
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 19-Nov-2009 13:29:58
 */
class SPField_Select extends SPFieldType implements SPFieldInterface
{
	/**
	 * @var int
	 */
	protected $width = 350;
	/**
	 * @var int
	 */
	protected $size = 1;
	/**
	 * @var array
	 */
	protected $options = array();
	/**
	 * @var array
	 */
	protected $optionsById = array();
	/**
	 * @var string
	 */
	protected $selectLabel = 'Select %s';
	/** * @var string */
	protected $cssClass = 'spClassSelect';
	/** * @var string */
	protected $cssClassView = 'spClassViewSelect';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditSelect';
	/** * @var string */
	protected $cssClassSearch = 'spClassSearchSelect';
	/**
	 * @var bool
	 */
	protected $multi = false;
	/**
	 * @var string
	 */
	protected $searchMethod = 'general';
	/**
	 * @var int
	 */
	protected $swidth = 350;
	/**
	 * @var int
	 */
	protected $ssize = 1;
	/**
	 * @var string
	 */
	protected $dType = 'predefined_multi_data_single_choice';
	/** * @var string */
	protected $itemprop = '';
	/** @var bool */
	protected $dependency = false;
	/** @var bool */
	protected $allowParents = true;
	/** @var string */
	protected $dependencyDefinition = '';
	/** * @var string */
	protected $metaSeparator = ' ';

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
		$class = $this->dependency ? $class . ' ctrl-dependency-field' : $class;

		$params = array( 'id' => $this->nid, 'size' => $this->size, 'class' => $class );
		if ( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}
		if ( $this->dependency ) {
			$params[ 'data' ] = array( 'order' => '1' );
		}
		$selected = $this->getRaw();
		/*
			* if isset( $selected[ 0 ] )  - then we have the data from edit cache
			* because the it contain the data like in request 0 => value
			* Otherwise we need to swap this
			*/
		if ( is_array( $selected ) && count( $selected ) && !( isset( $selected[ 0 ] ) ) ) {
			$selected = array_keys( $selected );
		}
		if ( !( $this->dependency ) ) {
			$field = SPHtml_Input::select( $this->nid, $this->getValues(), $selected, $this->multi, $params );
		}
		else {
			$path = null;
			$subFields = null;
			$hiddenValue = null;
			if ( isset( $this->_fData->options ) && strlen( $this->_fData->options ) ) {
				$path = SPConfig::unserialize( $this->_fData->options );
				$subFields = $this->travelDependencyPath( $path, $params );
				$selected = $path[ 1 ];
				$hiddenValue = str_replace( '"', "'", json_encode( (object)$path ) );
			}
			$field = SPHtml_Input::select( $this->nid, $this->getValues(), $selected, $this->multi, $params );
			$field .= $subFields;
			$field .= SPHtml_Input::hidden( $this->nid . '_path', $hiddenValue, null, array( 'data' => array( 'section' => Sobi::Section() ) ) );
		}
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	private function getValues( $required = true )
	{
		$values = array();
		if ( $this->dependency ) {
			SPFactory::header()
					->addJsFile( 'opt.field_select' );
			$options = json_decode( SPFs::read( SOBI_PATH . '/etc/fields/select-list/definitions/' . ( str_replace( '.xml', '.json', $this->dependencyDefinition ) ) ), true );
			if ( isset( $options[ 'translation' ] ) ) {
				SPLang::load( $options[ 'translation' ] );
				$values[ 0 ] = Sobi::Txt( $this->selectLabel, $this->name );
				foreach ( $options[ 'options' ] as $option ) {
					if ( isset( $options[ 'translation' ] ) ) {
						$values[ $option[ 'id' ] ] = Sobi::Txt( strtoupper( $options[ 'prefix' ] ) . '.' . strtoupper( $option[ 'id' ] ) );
					}
					else {
						$values[ $option[ 'id' ] ] = $option[ 'id' ];
					}
				}
			}
		}
		elseif ( count( $this->options ) ) {
			if ( $required ) {
				$this->required( $values );
			}
			foreach ( $this->options as $option ) {
				if ( isset( $option[ 'options' ] ) && is_array( $option[ 'options' ] ) && count( $option[ 'options' ] ) ) {
					$values[ $option[ 'label' ] ] = array();
					foreach ( $option[ 'options' ] as $subOption ) {
						$values[ $option[ 'label' ] ][ $subOption[ 'id' ] ] = $subOption[ 'label' ];
					}
				}
				else {
					$values[ $option[ 'id' ] ] = $option[ 'label' ];
				}
			}
		}
		return $values;
	}

	protected function required( &$values )
	{
		if ( $this->required || strlen( $this->selectLabel ) ) {
			if ( $this->required && strlen( $this->selectLabel ) < 1 ) {
				$this->selectLabel = Sobi::Txt( 'FD.SEARCH_SELECT_LABEL' );
			}
			$values[ 0 ] = Sobi::Txt( $this->selectLabel, $this->name );
		}
	}

	public function __construct( &$field )
	{
		parent::__construct( $field );
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$options = array();

		try {
			$db->select( '*', 'spdb_field_option', array( 'fid' => $this->fid ) );
			$o = $db->loadObjectList();
			$db->select( array( 'sValue', 'language', 'sKey' ), 'spdb_language', array( 'fid' => $this->fid, 'oType' => 'field_option' ) );
			$l = $db->loadObjectList();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELD_POSITION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		static $lang, $defLang = null;
		if ( !( $lang ) ) {
			$lang = Sobi::Lang( false );
			$defLang = Sobi::DefLang();
		}
		if ( count( $o ) ) {
			$labels = array();
			if ( count( $l ) ) {
				foreach ( $l as $label ) {
					if ( !isset( $labels[ $label->sKey ] ) ) {
						$labels[ $label->sKey ] = array();
					}
					$labels[ $label->sKey ][ $label->language ] = $label->sValue;
				}
			}
			/* re-label */
			foreach ( $o as $opt ) {
				$option = array();
				$option[ 'id' ] = $opt->optValue;
				$option[ 'label' ] = isset( $labels[ $opt->optValue ][ $lang ] ) ? $labels[ $opt->optValue ][ $lang ] : ( isset( $labels[ $opt->optValue ][ $defLang ] ) ? $labels[ $opt->optValue ][ SOBI_DEFLANG ] : ( isset( $labels[ $opt->optValue ][ 0 ] ) ? $labels[ $opt->optValue ][ 0 ] : $opt->optValue ) );
				$option[ 'position' ] = $opt->optPos;
				$option[ 'parent' ] = $opt->optParent;
				if ( $option[ 'parent' ] ) {
					if ( !( isset( $options[ $option[ 'parent' ] ] ) ) ) {
						$options[ $option[ 'parent' ] ] = array();
					}
					$options[ $option[ 'parent' ] ][ 'options' ][ $option[ 'id' ] ] = $option;
					$this->optionsById[ $option[ 'id' ] ] = $option;
				}
				else {
					if ( !( isset( $options[ $option[ 'id' ] ] ) ) ) {
						$options[ $option[ 'id' ] ] = array();
					}
					$options[ $option[ 'id' ] ] = array_merge( $options[ $option[ 'id' ] ], $option );
					$this->optionsById[ $option[ 'id' ] ] = $options[ $option[ 'id' ] ];
				}
			}
			$this->options = $this->sortOpt( $options );
		}
		else {
			$this->options[ 0 ][ 'id' ] = 'option-id';
			$this->options[ 0 ][ 'label' ] = Sobi::Txt( 'FD.SELECT_OPTION_NAME' );
			$this->options[ 0 ][ 'position' ] = 1;
			$this->options[ 0 ][ 'parent' ] = null;
		}
	}

	/**
	 * Get field specific values if these are in an other table
	 * @param $sid - id of the entry
	 * @param $fullData - the database row form the spdb_field_data table
	 * @param $rawData - raw data of the field content
	 * @param $fData - full formatted data of the field content
	 * @return void
	 */
	public function loadData( $sid, &$fullData, &$rawData, &$fData )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$table = $db->join(
				array(
						array( 'table' => 'spdb_field_option_selected', 'as' => 'sdata', 'key' => 'fid' ),
						array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ),
						array( 'table' => 'spdb_language', 'as' => 'ldata', 'key' => array( 'sdata.optValue', 'ldata.sKey' ) ),
				)
		);
		try {
			//$order = $this->checkCopy() ? 'scopy.desc' : 'scopy.asc';
			$order = $this->checkCopy() ? 'scopy.asc' : 'scopy.desc';
			$db->select(
					'*, sdata.copy as scopy',
					$table,
					array(
							'sdata.fid' => $this->id,
							'sdata.sid' => $sid,
							'fdata.sid' => $sid,
							'ldata.oType' => 'field_option',
							'ldata.fid' => $this->id
					), $order, 0, 0, true /*, 'sdata.copy' */ );
			$data = $db->loadObjectList( 'language' );
			if ( $data ) {
				if ( isset( $data[ Sobi::Lang() ] ) ) {
					$data = $data[ Sobi::Lang() ];
				}
				elseif ( isset( $data[ Sobi::DefLang() ] ) ) {
					$data = $data[ Sobi::DefLang() ];
				}
				else {
					foreach ( $data as $k => $v ) {
						$data = $v;
					}
				}
				$rawData = isset( $data->sKey ) ? $data->sKey : null;
				$fullData->baseData = isset( $data->sValue ) ? $data->sValue : null;
				$fData = isset( $data->sValue ) ? $data->sValue : null;
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_SELECTED_OPTIONS', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	protected function sortOpt( $options )
	{
		$sorted = array();
		if ( count( $options ) ) {
			foreach ( $options as $option ) {
				if ( isset( $option[ 'options' ] ) ) {
					$option[ 'options' ] = $this->sortOpt( $option[ 'options' ] );
				}
				if ( isset( $sorted[ $option[ 'position' ] ] ) ) {
					$option[ 'position' ] = +rand( 1000, 9999 );
				}
				$sorted[ $option[ 'position' ] ] = $option;
			}
		}
		ksort( $sorted );
		return $sorted;
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return array( 'width', 'size', 'selectLabel', 'searchMethod', 'swidth', 'ssize', 'itemprop', 'dependencyDefinition', 'dependency', 'allowParents', 'metaSeparator', 'cssClassView', 'cssClassSearch', 'cssClassEdit', 'showEditLabel' );
	}

	protected function fetchData( $data, $request = 'post' )
	{
		if ( $data && strlen( $data ) || $this->dependency ) {
			if ( $this->dependency ) {
				$path = json_decode( str_replace( "'", '"', Sobi::Clean( SPRequest::string( $this->nid . '_path', null, false, $request ) ) ), true );
				if ( count( $path ) ) {
					$options = json_decode( SPFs::read( SOBI_PATH . '/etc/fields/select-list/definitions/' . ( str_replace( '.xml', '.json', $this->dependencyDefinition ) ) ), true );
					$selected = $options[ 'options' ];
					foreach ( $path as $part ) {
						if ( isset( $selected[ $part ] ) ) {
							$selected = $selected[ $part ][ 'childs' ];
						}
						else {
							throw new SPException( SPLang::e( 'FIELD_NO_SUCH_OPT', $data, $this->name ) );
						}
					}
					if ( count( $selected ) && !( $this->allowParents ) ) {
						throw new SPException( SPLang::e( 'SELECT_FIELD_NO_PARENT', $this->name ) );
					}
				}
				return $path;
			}
			/* check if such option exist at all */
			elseif ( $data && strlen( $data ) && !( isset( $this->optionsById[ $data ] ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NO_SUCH_OPT', $data, $this->name ) );
			}
			return array( $data );
		}
		else {
			return null;
		}
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsId
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' )
	{
		$data = $this->fetchData( $this->multi ? SPRequest::arr( $this->nid, array(), $request ) : SPRequest::word( $this->nid, null, $request ) );
		if ( count( $this->verify( $entry, $request, $data ) ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return array();
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @param $data
	 * @throws SPException
	 * @return string
	 */
	private function verify( $entry, $request, $data )
	{
		$cdata = count( $data );

		/* check if it was required */
		if ( $this->required && !( $cdata ) ) {
			throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR_OPT', $this->name ) );
		}

		/* check if there was an adminField */
		if ( $this->adminField && $cdata ) {
			if ( !( Sobi:: Can( 'entry.adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->name ) );
			}
		}
		/* check if it was free */
		if ( !( $this->isFree ) && $this->fee && $cdata ) {
			SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
		}

		/* check if it was editLimit */
		if ( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $cdata ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
		}

		/* check if it was editable */
		if ( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $cdata && $entry->get( 'version' ) > 1 ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
		}
		return $cdata;
	}


	/**
	 * @param $entry
	 * @param $data
	 * @param $request
	 */
	protected function saveDependencyField( &$entry, $data, $request )
	{
		$time = SPRequest::now();
		$uid = Sobi::My( 'id' );
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$params = array();
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
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
		$params[ 'options' ] = $data;
		$params[ 'copy' ] = 0;
		$params[ 'baseData' ] = SPRequest::string( $this->nid, null, false, $request );
		$params[ 'copy' ] = ( int )!( $entry->get( 'approved' ) );
		if ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) {
			--$this->editLimit;
		}
		$params[ 'editLimit' ] = $this->editLimit;
		try {
			SPFactory::db()
					->insertUpdate( 'spdb_field_data', $params );
		} catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		foreach ( $data as $selected ) {
			/* collect the needed params */
			$params[ 'baseData' ] = strip_tags( SPFactory::db()->escape( $selected ) );
			$options[ ] = array( 'fid' => $this->fid, 'sid' => $entry->get( 'id' ), 'optValue' => $selected, 'copy' => $params[ 'copy' ], 'params' => null );
		}

		/* delete old selected values */
		try {
			SPFactory::db()->delete( 'spdb_field_option_selected', array( 'fid' => $this->fid, 'sid' => $entry->get( 'id' ), 'copy' => $params[ 'copy' ] ) );
		} catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_DELETE_PREVIOUS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		/* insert new selected value */
		try {
			SPFactory::db()->insertArray( 'spdb_field_option_selected', $options );
		} catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_SELECTED_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
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

		$data = $this->fetchData( $this->multi ? SPRequest::arr( $this->nid, array(), $request ) : SPRequest::word( $this->nid, null, $request ), $request );
		$cdata = $this->verify( $entry, $request, $data );
		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* @var SPdb $db */
		$db =& SPFactory::db();

		/* if we are here, we can save these data */
		if ( $cdata ) {
			if ( $this->dependency ) {
				return $this->saveDependencyField( $entry, $data, $request );
			}
			$options = array();
			$params = array();
			$params[ 'publishUp' ] = $entry->get( 'publishUp' );
			$params[ 'publishDown' ] = $entry->get( 'publishDown' );
			$params[ 'fid' ] = $this->fid;
			$params[ 'sid' ] = $entry->get( 'id' );
			$params[ 'section' ] = Sobi::Reg( 'current_section' );
			$params[ 'lang' ] = Sobi::Lang();
			$params[ 'enabled' ] = $entry->get( 'state' );
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
			$params[ 'copy' ] = 0;
			$params[ 'baseData' ] = null;
			$params[ 'copy' ] = ( int )!( $entry->get( 'approved' ) );
			if ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) {
				--$this->editLimit;
			}
			$params[ 'editLimit' ] = $this->editLimit;


			/* save it */
			try {
				$db->insertUpdate( 'spdb_field_data', $params );
			} catch ( SPException $x ) {
				Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			foreach ( $data as $selected ) {
				/* collect the needed params */
				$params[ 'baseData' ] = strip_tags( $db->escape( $selected ) );
				$options[ ] = array( 'fid' => $this->fid, 'sid' => $entry->get( 'id' ), 'optValue' => $selected, 'copy' => $params[ 'copy' ], 'params' => null );
			}

			/* delete old selected values */
			try {
				$db->delete( 'spdb_field_option_selected', array( 'fid' => $this->fid, 'sid' => $entry->get( 'id' ), 'copy' => $params[ 'copy' ] ) );
			} catch ( SPException $x ) {
				Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_DELETE_PREVIOUS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}

			/* insert new selected value */
			try {
				$db->insertArray( 'spdb_field_option_selected', $options );
			} catch ( SPException $x ) {
				Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_SELECTED_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		elseif ( $entry->get( 'version' ) > 1 ) {
			if ( !( $entry->get( 'approved' ) ) ) {
				try {
					$db->update( 'spdb_field_option_selected', array( 'copy' => 1 ), array( 'fid' => $this->fid, 'sid' => $entry->get( 'id' ) ) );
				} catch ( SPException $x ) {
					Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_UPDATE_PREVIOUS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
			else {
				/* delete old selected values */
				try {
					$db->delete( 'spdb_field_option_selected', array( 'fid' => $this->fid, 'sid' => $entry->get( 'id' ) ) );
				} catch ( SPException $x ) {
					Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_DELETE_PREVIOUS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
		}
	}

	/**
	 * Static function to create the right SQL-Query if a entries list should be sorted by this field
	 * @param string $tables - table or tables join
	 * @param array $conditions - array with conditions
	 * @param string $oPrefix
	 * @param string $eOrder
	 * @param string $eDir
	 * @return void
	 */
	public static function sortBy( &$tables, &$conditions, &$oPrefix, &$eOrder, $eDir )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$tables = $db->join(
				array(
						array( 'table' => 'spdb_field_option_selected', 'as' => 'sdata', 'key' => 'fid' ),
						array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => array( 'sdata.sid', 'spo.id' ) ),
						array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => array( 'fdata.fid', 'sdata.fid' ) ),
						array( 'table' => 'spdb_field', 'as' => 'fdef', 'key' => array( 'fdef.fid', 'sdata.fid' ) ),
						array( 'table' => 'spdb_language', 'as' => 'ldata', 'key' => array( 'sdata.optValue', 'ldata.sKey' ) ),
						array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => array( 'spo.id', 'sprl.id' ) ),
				)
		);
		$oPrefix = 'spo.';
		$conditions[ 'spo.oType' ] = 'entry';
		if ( !( isset( $conditions[ 'sprl.pid' ] ) ) ) {
			$conditions[ 'sprl.pid' ] = SPRequest::sid();
		}
		$conditions[ 'ldata.oType' ] = 'field_option';
		$conditions[ 'fdef.nid' ] = $eOrder;
		$eOrder = 'sValue.' . $eDir . ", field( language, '" . Sobi::Lang( false ) . "', '" . Sobi::DefLang() . "' )";
		return true;
	}

	public function approve( $sid )
	{
		parent::approve( $sid );
		$db =& SPFactory::db();
		if ( $db->select( 'COUNT(*)', 'spdb_field_option_selected', array( 'sid' => $sid, 'copy' => '1', 'fid' => $this->fid ) )->loadResult() ) {
			try {
				$db->delete( 'spdb_field_option_selected', array( 'sid' => $sid, 'copy' => '0', 'fid' => $this->fid ) );
				$db->update( 'spdb_field_option_selected', array( 'copy' => '0' ), array( 'sid' => $sid, 'copy' => '1', 'fid' => $this->fid ) );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
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
		$hidden = null;
		$d = $this->getValues( false );
		$data = array( '' => Sobi::Txt( 'FD.SEARCH_SELECT_LIST', array( 'name' => $this->name ) ) );
		foreach ( $d as $k => $v ) {
			$data[ $k ] = $v;
		}
		$params = array( 'id' => $this->nid, 'size' => $this->ssize, 'class' => $this->cssClass . ' ' . Sobi::Cfg( 'search.form_list_def_css', 'SPSearchSelect' ) );
		if ( $this->swidth ) {
			$params[ 'style' ] = "width: {$this->swidth}px;";
		}
		if ( $this->dependency ) {
			SPFactory::header()
					->addJsFile( 'opt.field_select' );
			$request = json_decode( SPLang::clean( SPRequest::raw( $this->nid . '_path', null, 'requestcache' ) ), true );
			$params[ 'class' ] .= ' ctrl-dependency-field';
			$hidden = $this->travelDependencyPath( $request, $params );
			$this->_selected = $request[ 1 ];
			$hiddenValue = str_replace( '"', "'", json_encode( (object)$request ) );
			$hidden .= SPHtml_Input::hidden( $this->nid . '_path', $hiddenValue, null, array( 'data' => array( 'selected' => '', 'section' => Sobi::Section() ) ) );
			$params[ 'data' ] = array( 'order' => '1' );
		}
		return SPHtml_Input::select( $this->nid, $data, $this->_selected, ( $this->searchMethod == 'mselect' ), $params ) . $hidden;
	}

	/**
	 * @param int $sid - entry id
	 * @return void
	 */
	public function rejectChanges( $sid )
	{
		parent::rejectChanges( $sid );
		try {
			SPFactory::db()
					->delete( 'spdb_field_option_selected', array( 'sid' => $sid, 'fid' => $this->fid, 'copy' => '1', ) );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

	}

	/* (non-PHPdoc)
	 * @see Site/opt/fields/SPFieldType#deleteData($sid)
	 */
	public function deleteData( $sid )
	{
		parent::deleteData( $sid );
		try {
			SPFactory::db()
					->delete( 'spdb_field_option_selected', array( 'sid' => $sid, 'fid' => $this->fid ) );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/* (non-PHPdoc)
	 * @see /Site/lib/models/field.php#delete()
	 */
	public function delete()
	{
		$db =& SPFactory::db();
		try {
			$db->delete( 'spdb_field_option_selected', array( 'fid' => $this->fid ) );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		try {
			$db->delete( 'spdb_field_option', array( 'fid' => $this->fid ) );
			$db->delete( 'spdb_language', array( 'oType' => 'field_option', 'fid' => $this->id ) );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}


	/**
	 * @param string $data
	 * @param int $section
	 * @param bool $regex
	 * @return array
	 */
	public function searchString( $data, $section, $regex = false )
	{
		if ( ( $this->dependency ) ) {
			return parent::searchString( $data, $section, $regex );
		}
		/* @var SPdb $db */
		$db = SPFactory::db();
		$sids = array();
		try {
			$query = array( 'oType' => 'field_option', 'fid' => $this->fid, 'sValue' => $regex ? $data : "%{$data}%" );
			$db->select( 'sKey', 'spdb_language', $query );
			$fids = $db->loadResultArray();
			if ( count( $fids ) ) {
				foreach ( $fids as $opt ) {
					$db->dselect( 'sid', 'spdb_field_option_selected', array( 'copy' => '0', 'fid' => $this->fid, 'optValue' => $opt ) );
					$ids = $db->loadResultArray();
					if ( is_array( $ids ) && count( $ids ) ) {
						$sids = array_unique( array_merge( $ids, $sids ) );
					}
				}
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SEARCH_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		return $sids;
	}

	/**
	 * @param string $data
	 * @param int $section
	 * @param bool $startWith
	 * @return array
	 */
	public function searchSuggest( $data, $section, $startWith = true )
	{
		if ( ( $this->dependency ) ) {
			return parent::searchSuggest( $data, $section, $startWith );
		}
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$terms = array();
		$data = $startWith ? "{$data}%" : "%{$data}%";
		try {
			$fids = SPFactory::db()
					->dselect( 'sKey', 'spdb_language', array( 'oType' => 'field_option', 'fid' => $this->fid, 'sValue' => $data ) )
					->loadResultArray();
			if ( count( $fids ) ) {
				foreach ( $fids as $opt ) {
					$c = SPFactory::db()
							->dselect( 'COUNT(*)', 'spdb_field_option_selected', array( 'copy' => '0', 'fid' => $this->fid, 'optValue' => $opt ) )
							->loadResult();
					if ( $c ) {
						$terms[ ] = $opt;
					}
				}
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SEARCH_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		return $terms;
	}

	/* (non-PHPdoc)
	 * @see Site/opt/fields/SPFieldType#searchData()
	 */
	public function searchData( $request, $section )
	{
		if ( ( $this->dependency ) ) {
			$path = json_decode( Sobi::Clean( SPRequest::string( $this->nid . '_path' ) ), true );
			if ( count( $path ) ) {
				$request = array_pop( $path );
			}
		}
		$sids = array();
		/* check if there was something to search for */
		if ( ( is_array( $request ) && count( $request ) ) || ( is_string( $request ) && strlen( $request ) ) ) {
			/** @var SPDb $db */
			$db = SPFactory::db();
			try {
				/* if we are searching for multiple options
				 * and the field contains 'predefined_multi_data_multi_choice'
				 * - we have to find entries matches all these options */
				if ( is_array( $request ) && $this->multi ) {
					foreach ( $request as $opt ) {
						$db->select( 'sid', 'spdb_field_option_selected', array( 'copy' => '0', 'fid' => $this->fid, 'optValue' => $opt ) );
						if ( !( isset( $results ) ) ) {
							$results = $db->loadResultArray();
						}
						else {
							$cids = $db->loadResultArray();
							$results = array_intersect( $results, $cids );
						}
					}
					$sids = $results;
				}
				else {
					$db->select( 'sid', 'spdb_field_option_selected', array( 'copy' => '0', 'fid' => $this->fid, 'optValue' => $request ) );
					$sids = $db->loadResultArray();
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SEARCH_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		return $sids;
	}

	protected function _parseOptions( $options, &$arr )
	{
		foreach ( $options as $value ) {
			if ( isset( $value[ 'options' ] ) && is_array( $value[ 'options' ] ) ) {
				$arr[ $value[ 'label' ] ] = array();
				$this->_parseOptions( $value[ 'options' ], $arr[ $value[ 'label' ] ] );
			}
			else {
				$arr[ $value[ 'id' ] ] = $value[ 'label' ];
			}
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		/** it can be for core files only at the moment because a stupid developer (yes, we all know which one) declared too many private methods and inherited classes returning always wrong results */
		$class = strtolower( get_class( $this ) );
		if ( strstr( $class, 'select' ) || strstr( $class, 'radio' ) || strstr( $class, 'chbxgr' ) ) {
			return $this->verify( $entry, $request, $this->fetchData( $this->multi ? SPRequest::arr( $this->nid, array(), $request ) : SPRequest::word( $this->nid, null, $request ) ) );
		}
		else {
			return true;
		}
	}

	public function compareRevisions( $revision, $current )
	{
		if ( is_array( $revision ) || is_array( $current ) ) {
			if ( is_array( $current ) ) {
				ksort( $current );
				$cur = implode( "\n", ( $current ) );
			}
			if ( is_array( $revision ) ) {
				ksort( $revision );
				$rev = implode( "\n", ( $revision ) );
			}
			return array( 'current' => $cur, 'revision' => $rev );
		}
		else {
			return array( 'current' => $current, 'revision' => $revision );
		}
	}

	public function onFieldEdit( &$view )
	{
		$dependencyDefinitions = scandir( SOBI_PATH . '/etc/fields/select-list/' );
		if ( count( $dependencyDefinitions ) ) {
			$set = array();
			foreach ( $dependencyDefinitions as $file ) {
				if ( !( is_dir( SOBI_PATH . '/etc/fields/select-list/' . $file ) ) ) {
					$set[ $file ] = $file;
				}
			}
			$view->assign( $set, 'dependencyDefinition' );
		}
		/** @var $arr SPData_Array */
		$arr = SPFactory::Instance( 'types.array' );
		$options = array();
		$this->_parseOptions( $this->options, $options );
		$options = $arr->toINIString( $options );
		$view->assign( $options, 'options' );
	}


	/**
	 * */
	public function ProxyDependency()
	{
		$path = json_decode( Sobi::Clean( SPRequest::string( 'path' ) ), true );
		$values = $this->loadDependencyDefinition( $path );
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		exit( json_encode( array( 'options' => $values, 'path' => ( json_encode( $path ) ) ) ) );
	}

	/**
	 * @param $path
	 * @return array
	 */
	protected function loadDependencyDefinition( $path )
	{
		static $options = array();
		if ( !( count( $options ) ) ) {
			$options = json_decode( SPFs::read( SOBI_PATH . '/etc/fields/select-list/definitions/' . ( str_replace( '.xml', '.json', $this->dependencyDefinition ) ) ), true );
		}
		if ( isset( $options[ 'translation' ] ) ) {
			SPLang::load( $options[ 'translation' ] );
		}
		$type = null;
		$selected = $options[ 'options' ];
		foreach ( $path as $option ) {
			if ( !( strlen( $option ) ) ) {
				continue;
			}
			$type = isset( $selected[ $option ][ 'child-type' ] ) ? Sobi::Txt( strtoupper( $options[ 'prefix' ] ) . '.' . strtoupper( $selected[ $option ][ 'child-type' ] ) ) : null;
			$selected = $selected[ $option ][ 'childs' ];
		}
		$values = array();
		if ( is_array( $selected ) && count( $selected ) ) {
			$values[ 0 ] = Sobi::Txt( $this->selectLabel, ( strlen( $type ) ? $type : $this->name ) );
			foreach ( $selected as $child ) {
				if ( isset( $options[ 'translation' ] ) ) {
					$values[ $child[ 'id' ] ] = Sobi::Txt( strtoupper( $options[ 'prefix' ] ) . '.' . strtoupper( $child[ 'id' ] ) );
				}
				else {
					$values[ $child[ 'id' ] ] = $child[ 'id' ];
				}
			}
			return $values;
		}
		return $values;
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		if ( !( $this->dependency ) ) {
			return parent::struct( true );
		}
		$selected = $this->getRaw();
		if ( isset( $this->_fData->options ) ) {
			$path = SPConfig::unserialize( $this->_fData->options );
		}
		else {
			return null;
		}
		$selectedPath = array();
		$options = json_decode( SPFs::read( SOBI_PATH . '/etc/fields/select-list/definitions/' . ( str_replace( '.xml', '.json', $this->dependencyDefinition ) ) ), true );
		if ( isset( $options[ 'translation' ] ) ) {
			SPLang::load( $options[ 'translation' ] );
			$selected = Sobi::Txt( strtoupper( $options[ 'prefix' ] ) . '.' . strtoupper( $selected ) );
		}
		if ( count( $path ) && isset( $options[ 'translation' ] ) ) {
			foreach ( $path as $step ) {
				$selectedPath[ $step ] = $selected = Sobi::Txt( strtoupper( $options[ 'prefix' ] ) . '.' . strtoupper( $step ) );
			}
		}
		$this->cleanCss();
		return array(
				'_complex' => 1,
				'_data' => $selected,
				'_attributes' => array(
						'class' => $this->cssClass
				),
				'_options' => array( 'path' => count( $selectedPath ) ? $selectedPath : $path ),
		);

	}

	/**
	 * @param $path
	 * @param $subParams
	 * @return string
	 */
	protected function travelDependencyPath( $path, $subParams )
	{
		$subFields = null;
		if ( count( $path ) ) {
			$progress = array();
			foreach ( $path as $index => $step ) {
				$progress[ ] = $step;
				$subParams[ 'data' ][ 'order' ] = $index + 1;
				$subParams[ 'id' ] = $this->nid . '_' . $index;
				$lists = $this->loadDependencyDefinition( $progress );
				if ( count( $lists ) ) {
					$subFields .= SPHtml_Input::select( $this->nid, $lists, $path[ $index + 1 ], false, $subParams );
				}
			}
		}
		return $subFields;
	}
}
