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

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'models.fields.interface' );

class SPFieldType extends SPObject
{
	/**
	 * @var SPField
	 */
	private $_field = null;
	/**
	 * @var array
	 */
	protected $_attr = array();
	/**
	 * @var string
	 */
	protected $_selected = null;
	/**
	 * @var string
	 */
	protected $dType = 'free_single_simple_data';
	/**
	 * @var string
	 */
	protected $_rdata = null;
	/**
	 * @var string
	 */
	protected $cssClass = "inputbox";
	/** @var bool */
	protected $showLabel = true;
	/** @var bool */
	protected $showEditLabel = true;
	/** @var array */
	protected $sets = array();


	public function __construct( &$field )
	{
		$this->_field =& $field;
		/* transform params from the basic object to the spec. field properties */
		if ( count( $this->params ) ) {
			foreach ( $this->params as $k => $v ) {
//				if ( property_exists( $this, $k ) ) {
				if ( isset( $this->$k ) ) {
					$this->$k = $v;
				}
			}
		}
//		$this->cssClass = $field->get( 'cssClass' );

//		if ( !( $this->cssClass ) ) {
//			$this->cssClass = 'input-medium';
//		}
	}

	protected function setData( $data )
	{
		$this->_rdata = $data;
		$this->_field->setRawData( $data );
	}

	/**
	 * @param string $val
	 * @return void
	 */
	public function setCSS( $val = 'spField' )
	{
		$this->cssClass = $val;
	}

	/**
	 * @param string $val
	 * @return void
	 */
	public function setSelected( $val )
	{
		$this->_selected = $val;
	}

	/**
	 * Proxy pattern
	 * @param string $method
	 * @param array $args
	 * @throws SPException
	 * @return mixed
	 */
	public function __call( $method, $args )
	{
		if ( $this->_field && method_exists( $this->_field, $method ) ) {
			return call_user_func_array( array( $this->_field, $method ), $args );
		}
		else {
			throw new SPException( SPLang::e( 'CALL_TO_UNDEFINED_METHOD_S', $method ) );
		}
	}

	/**
	 * @param string $var
	 * @param mixed $val
	 * @return \SPObject|void
	 */
	public function set( $var, $val )
	{
		if ( isset( $this->$var ) ) {
			$this->$var = $val;
		}
	}

	/**
	 * This function is used for the case that a field wasn't used for some reason while saving an entry
	 * But it has to perform some operation
	 * E.g. Category field is set to be administrative and isn't used
	 * but it needs to pass the previously selected categories to the entry model
	 * @param SPEntry $entry
	 * @param string $request
	 * @return bool
	 * */
	public function finaliseSave( $entry, $request = 'post' )
	{
		return true;
	}

	protected function rangeSearch( $values, $freeInput = false )
	{
		$request[ 'from' ] = isset( $this->_selected[ 'from' ] ) ? (int)$this->_selected[ 'from' ] : '';
		$request[ 'to' ] = isset( $this->_selected[ 'to' ] ) ? (int)$this->_selected[ 'to' ] : '';
		if ( !( $freeInput ) ) {
			$values = str_replace( array( "\n", "\r", "\t" ), null, $values );
			$values = explode( ',', $values );
			$data = array();
			$data2 = array();
			if ( count( $values ) ) {
				foreach ( $values as $k => $v ) {
					$data[ '' ] = Sobi::Txt( 'SH.SEARCH_SELECT_RANGE_FROM', array( 'name' => $this->name ) );
					$data2[ '' ] = Sobi::Txt( 'SH.SEARCH_SELECT_RANGE_TO', array( 'name' => $this->name ) );
					$data[ preg_replace( '/[^\d\.\-]/', null, trim( $v ) ) ] = $v;
					$data2[ preg_replace( '/[^\d\.\-]/', null, trim( $v ) ) ] = $v;
				}
			}
			$from = SPHtml_Input::select( $this->nid . '[from]', $data, $request[ 'from' ], false, array( 'class' => $this->cssClass . ' ' . Sobi::Cfg( 'search.form_list_def_css', 'SPSearchSelect' ), 'size' => '1' ) );
			$to = SPHtml_Input::select( $this->nid . '[to]', $data2, $request[ 'to' ], false, array( 'class' => $this->cssClass . ' ' . Sobi::Cfg( 'search.form_list_def_css', 'SPSearchSelect' ), 'size' => '1' ) );

//			return '<div class="SPSearchSelectRangeFrom"><span>' . Sobi::Txt( 'SH.RANGE_FROM' ) . '</span> ' . $from . ' ' . $this->suffix . '</div><div class="SPSearchSelectRangeTo"><span>' . Sobi::Txt( 'SH.RANGE_TO' ) . '</span> ' . $to . ' ' . $this->suffix . '</div>';
			return '<div class="spSelectRangeFrom">' . $from . ' ' . $this->suffix . '</div><div class="spSelectRangeTo">' . $to . ' ' . $this->suffix . '</div>';
		}
		else {
			$from = SPHtml_Input::text( $this->nid . '[from]', $request[ 'from' ], array( 'size' => '1', 'placeholder' => Sobi::Txt( 'SH.RANGE_FROM' ) ) );
			$to = SPHtml_Input::text( $this->nid . '[to]', $request[ 'to' ], array( 'size' => '1', 'placeholder' => Sobi::Txt( 'SH.RANGE_TO' ) ) );
//			return '<div class="SPSearchInputRangeFrom"><span>' . Sobi::Txt( 'SH.RANGE_FROM' ) . '</span> ' . $from . ' ' . $this->suffix . '</div><div class="SPSearchInputRangeTo"><span>' . Sobi::Txt( 'SH.RANGE_TO' ) . '</span> ' . $to . ' ' . $this->suffix . '</div>';
			return '<div class="spInputRangeFrom">' . $from . ' ' . $this->suffix . '</div><div class="spInputRangeTo">' . $to . ' ' . $this->suffix . '</div>';
		}
	}

	protected function searchForRange( $request, $section )
	{
		$sids = array();
		if ( $request[ 'from' ] || $request[ 'to' ] ) {
			$request[ 'from' ] = isset( $request[ 'from' ] ) ? $request[ 'from' ] : SPC::NO_VALUE;
			$request[ 'to' ] = isset( $request[ 'to' ] ) ? $request[ 'to' ] : SPC::NO_VALUE;
			$request[ 'from' ] = strstr( $request[ 'from' ], '.' ) ? ( floatval( $request[ 'from' ] ) ) : (int)$request[ 'from' ];
			$request[ 'to' ] = strstr( $request[ 'to' ], '.' ) ? ( floatval( $request[ 'to' ] ) ) : (int)$request[ 'to' ];
			try {
				$sids = SPFactory::db()
						->dselect( 'sid', 'spdb_field_data', array( 'fid' => $this->fid, 'copy' => '0', 'enabled' => 1, 'baseData' => $request, 'section' => $section ) )
						->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SEARCH_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		return $sids;
	}

	/**
	 * Returns meta description
	 */
	public function metaDesc()
	{
		return $this->addToMetaDesc ? $this->data() : null;
	}

	public function cleanCss()
	{
		$css = explode( ' ', $this->cssClass );
		if ( count( $css ) ) {
			$this->cssClass = implode( ' ', array_unique( $css ) );
		}
	}

	/**
	 * Returns meta keys
	 */
	public function metaKeys()
	{
		return $this->addToMetaKeys ? $this->data() : null;
	}

	/**
	 * Proxy pattern
	 * @param string $property
	 * @return mixed
	 */
	public function __get( $property )
	{
		if ( !( isset( $this->$property ) ) && $this->_field ) {
			return $this->_field->get( $property );
		}
		else {
			return $this->get( $property );
		}
	}

	/**
	 * @param $vals
	 * @return void
	 */
	public function save( &$vals )
	{
		$this->_attr =& $vals;
		if ( !isset( $vals[ 'params' ] ) ) {
			$vals[ 'params' ] = array();
		}
		$attr = $this->getAttr();
		$properties = array();
		if ( count( $attr ) ) {
			foreach ( $attr as $property ) {
				$properties[ $property ] = isset( $vals[ $property ] ) ? ( $vals[ $property ] ) : null;
			}
		}
		$vals[ 'params' ] = $properties;
	}

	public function properties()
	{
		return $this->getAttr();
	}

	protected function getAttr()
	{
		return array( 'itemprop' );
	}

	public function approve( $sid )
	{
		$db = SPFactory::db();
		static $lang = null;
		if ( !( $lang ) ) {
			$lang = Sobi::Lang( false );
		}
		try {
			$copy = $db
					->select( 'COUNT( fid )', 'spdb_field_data', array( 'sid' => $sid, 'copy' => '1', 'fid' => $this->fid ) )
					->loadResult();
			if ( $copy ) {
				/**
				 * Fri, Apr 6, 2012
				 * Ok, this is tricky now.
				 * Normally we have such situation:
				 * User is adding an entry and flags are:
				 * approved    | copy  | baseData
				 *    0        |  1    |    Org
				 * When it's just being approved everything works just fine
				 * Problem is when the admin is changing the data then after edit it looks like this
				 * approved    | copy  | baseData
				 *    0        |  1    |    Org         << org user data
				 *    1        |  0    |    Changed     << data changed by the administrator
				 * So in the normal way we'll delete the changed data and approve the old data
				 * Therefore we have to check if the approved data is maybe newer than the non-approved copy
				 */
				$date = $db
						->select( 'copy', 'spdb_field_data', array( 'sid' => $sid, 'fid' => $this->fid ), 'updatedTime.desc', 1 )
						->loadResult();
				/**
				 * If the copy flag of the newer version is 0 - then delete all non-approved versions
				 * and this is our current version
				 */
				if ( $date == 0 ) {
					$db->delete( 'spdb_field_data', array( 'sid' => $sid, 'copy' => '1', 'fid' => $this->fid ) );
				}
				else {
					$params = array( 'sid' => $sid, 'copy' => '1', 'fid' => $this->fid );
					/**
					 * when we have good multilingual management
					 * we can change it
					 * for the moment if an entry is entered in i.e. de_DE
					 * but the admin approves the entry in en_GB and the multilingual mode is enabled
					 * in case it was a new entry - empty data is being displayed
					 */
					/** Mon, Sep 23, 2013 10:39:37 - I think is should always change the data in the current lang
					 * Since 1.1 we have good multilingual management so it is probably this issue */
//					if ( !( Sobi::Cfg( 'entry.approve_all_langs', true ) ) ) {
//						$params[ 'lang' ] = array( $lang, SPC::NO_VALUE );
//					}
					$el = $db
							->select( 'editLimit', 'spdb_field_data', $params )
							->loadResult();
					$cParams = $params;
					/** we need to delete only the entries that have the copy flag set to 1 with the selected language */
					$languages = $db
							->select( 'lang', 'spdb_field_data', array( 'sid' => $sid, 'copy' => '1', 'fid' => $this->fid ) )
							->loadResultArray();
					$cParams[ 'copy' ] = 0;
					$cParams[ 'lang' ] = $languages;
					$db->delete( 'spdb_field_data', $cParams );
					$db->update( 'spdb_field_data', array( 'copy' => '0', 'editLimit' => $el ), $params );
				}
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DATA_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
	}

	public function changeState( $sid, $state )
	{
		$db =& SPFactory::db();
		try {
			$db->update( 'spdb_field_data', array( 'enabled' => $state ), array( 'sid' => $sid, 'fid' => $this->fid ) );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_CHANGE_FIELD_STATE', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
	}

	/**
	 * @param $data
	 * @param $section
	 * @return bool
	 */
	public function searchString( $data, $section )
	{
		return true;
	}

	public function searchData( $request, $section )
	{
		return true;
	}

	public function searchSuggest()
	{
		return false;
	}

	/**
	 * @param int $sid - entry id
	 * @return void
	 */
	public function rejectChanges( $sid )
	{
		static $deleted = array();
		if ( !( isset( $deleted[ $sid ] ) ) ) {
			$db =& SPFactory::db();
			try {
				$db->delete( 'spdb_field_data', array( 'sid' => $sid, 'copy' => 1 ) );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DELETE_FIELD_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			$deleted[ $sid ] = true;
		}
	}

	/**
	 * @param int $sid - entry id
	 * @return void
	 */
	public function deleteData( $sid )
	{
		static $deleted = array();
		if ( !( isset( $deleted[ $sid ] ) ) ) {
			$db =& SPFactory::db();
			try {
				$db->delete( 'spdb_field_data', array( 'sid' => $sid ) );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DELETE_FIELD_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			$deleted[ $sid ] = true;
		}
	}

	protected function checkCopy()
	{
		return ( in_array( SPRequest::task(), array( 'entry.approve', 'entry.edit', 'entry.save', 'entry.submit' ) ) || ( Sobi::Can( 'entry.access.unapproved_any' ) ) || Sobi::Can( 'entry.manage.*' ) );
	}

	protected function parseOptsFile( $file )
	{
		$p = 0;
		$group = null;
		$gid = null;
		$options = array();
		if ( is_array( $file ) && count( $file ) ) {
			foreach ( $file as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( strstr( $key, ',' ) ) {
						$group = explode( ',', $key );
						$gid = SPLang::nid( $group[ 0 ] );
						$group = $group[ 1 ];
					}
					else {
						$gid = SPLang::nid( $key, true, true );
						$group = $key;
					}
					$options[ ] = array( 'id' => $gid, 'name' => $group, 'parent' => null, 'position' => ++$p );
					if ( count( $value ) ) {
						foreach ( $value as $k => $v ) {
							if ( is_numeric( $k ) ) {
								$k = SPLang::nid( $v );
							}
							$options[ ] = array( 'id' => SPLang::nid( $k ), 'name' => $v, 'parent' => $gid, 'position' => ++$p );
						}
					}
				}
				else {
					$group = null;
					$gid = null;
					$options[ ] = array( 'id' => SPLang::nid( $key ), 'name' => $value, 'parent' => null, 'position' => ++$p );
				}
			}
		}
		return $options;
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		return true;
	}
}
