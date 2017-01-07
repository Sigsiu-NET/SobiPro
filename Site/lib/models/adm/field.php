<?php
/**
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Mar-2009 12:00:45 PM
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadModel( 'field' );

final class SPAdmField extends SPField
{
	/**
	 * @var array
	 */
	private $_translatable = [ 'name', 'description' ];

	public function save( $attr )
	{
		/* @var SPdb $db */
		$db = SPFactory::db();
		$base = $attr;
		$this->loadType();
		/* clean input */
		if ( isset( $attr[ 'name' ] ) )
			$base[ 'name' ] = $db->escape( $attr[ 'name' ] );
		else
			$base[ 'name' ] = 'missing name - something went wrong';
		if ( isset( $attr[ 'nid' ] ) )
			$base[ 'nid' ] = $this->nid( $db->escape( preg_replace( '/[^[:alnum:]\-\_]/', null, $attr[ 'nid' ] ) ), false );
		if ( isset( $attr[ 'cssClass' ] ) )
			$base[ 'cssClass' ] = $db->escape( preg_replace( '/[^[:alnum:]\-\_ ]/', null, $attr[ 'cssClass' ] ) );
		if ( isset( $attr[ 'notice' ] ) )
			$base[ 'notice' ] = ( $attr[ 'notice' ] );
		if ( isset( $attr[ 'showIn' ] ) )
			$base[ 'showIn' ] = $db->escape( preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'showIn' ] ) );
		if ( isset( $attr[ 'filter' ] ) )
			$base[ 'filter' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'filter' ] );
		if ( isset( $attr[ 'fieldType' ] ) )
			$base[ 'fieldType' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'fieldType' ] );
		if ( isset( $attr[ 'type' ] ) )
			$base[ 'fieldType' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'type' ] );
		if ( isset( $attr[ 'enabled' ] ) )
			$base[ 'enabled' ] = ( int )$attr[ 'enabled' ];
		if ( isset( $attr[ 'required' ] ) )
			$base[ 'required' ] = ( int )$attr[ 'required' ];
		if ( isset( $attr[ 'adminField' ] ) )
			$base[ 'adminField' ] = ( int )$attr[ 'adminField' ];
		if ( isset( $attr[ 'adminField' ] ) && $attr[ 'adminField' ] ) {
			$attr[ 'required' ] = false;
		}
		if ( isset( $attr[ 'editable' ] ) )
			$base[ 'editable' ] = ( int )$attr[ 'editable' ];
		if ( isset( $attr[ 'inSearch' ] ) )
			$base[ 'inSearch' ] = ( int )$attr[ 'inSearch' ];
		if ( isset( $attr[ 'editLimit' ] ) )
			$base[ 'editLimit' ] = ( int )$attr[ 'editLimit' ];
		$base[ 'editLimit' ] = isset( $base[ 'editLimit' ] ) && $base[ 'editLimit' ] > 0 ? $base[ 'editLimit' ] : -1;
		if ( isset( $attr[ 'isFree' ] ) )
			$base[ 'isFree' ] = ( int )$attr[ 'isFree' ];
		if ( isset( $attr[ 'withLabel' ] ) )
			$base[ 'withLabel' ] = ( int )$attr[ 'withLabel' ];
		if ( isset( $attr[ 'fee' ] ) )
			$base[ 'fee' ] = ( double )str_replace( ',', '.', $attr[ 'fee' ] );
		if ( isset( $attr[ 'addToMetaDesc' ] ) )
			$base[ 'addToMetaDesc' ] = ( int )$attr[ 'addToMetaDesc' ];
		if ( isset( $attr[ 'addToMetaKeys' ] ) )
			$base[ 'addToMetaKeys' ] = ( int )$attr[ 'addToMetaKeys' ];
		if ( isset( $attr[ 'uniqueData' ] ) )
			$base[ 'uniqueData' ] = ( int )$attr[ 'uniqueData' ];
		/* both strpos are removed because it does not allow to have one parameter only */
//      if( isset( $attr[ 'allowedAttributes' ] ) && strpos( $attr[ 'allowedAttributes' ], '|' ) )
		if ( isset( $attr[ 'allowedAttributes' ] ) ) {
			$att = SPFactory::config()->structuralData( $attr[ 'allowedAttributes' ], true );
			if ( count( $att ) ) {
				foreach ( $att as $i => $k ) {
					$att[ $i ] = trim( $k );
				}
			}
			$base[ 'allowedAttributes' ] = SPConfig::serialize( $att );
		}
		if ( isset( $attr[ 'allowedTags' ] ) ) {
			$tags = SPFactory::config()->structuralData( $attr[ 'allowedTags' ], true );
			if ( count( $tags ) ) {
				foreach ( $tags as $i => $k ) {
					$tags[ $i ] = trim( $k );
				}
			}
			$base[ 'allowedTags' ] = SPConfig::serialize( $tags );
		}
		if ( isset( $attr[ 'admList' ] ) )
			$base[ 'admList' ] = ( int )$attr[ 'admList' ];
		if ( isset( $attr[ 'description' ] ) )
			$base[ 'description' ] = ( $attr[ 'description' ] );
		else
			$base[ 'description' ] = null;
		if ( isset( $attr[ 'suffix' ] ) )
			$base[ 'suffix' ] = $db->escape( $attr[ 'suffix' ] );
		else
			$base[ 'suffix' ] = null;
		$this->version++;
		$base[ 'version' ] = $this->version;

		/* section id is needed only if it was new field */
		if ( !( ( isset( $attr[ 'section' ] ) && $attr[ 'section' ] ) ) ) {
			if ( !( SPRequest::int( 'fid' ) ) ) {
				$base[ 'section' ] = SPRequest::sid();
			}
		}

		/* bind attributes to this object */
		foreach ( $attr as $a => $v ) {
			$a = trim( $a );
			if ( $this->has( $a ) ) {
				$this->$a = $v;
			}
		}

		$baseIndexes = array_keys( $base );
		$attrIndexes = array_keys( $attr );
		$additionalInfo = array_diff( $attrIndexes, $baseIndexes );
		if ( $this->_type && method_exists( $this->_type, 'save' ) ) {
			$this->_type->save( $base, $additionalInfo );
		}

		/* get database columns and their ordering */
		$cols = $db->getColumns( 'spdb_field' );
		$values = [];

		/* and sort the properties in the same order */
		foreach ( $cols as $col ) {
			if ( array_key_exists( $col, $base ) ) {
				$values[ $col ] = $base[ $col ];
			}
		}
		/* save field */
		try {
			$db->update( 'spdb_field', $values, [ 'fid' => $this->fid ] );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}

		/* save language dependent properties */
		$labels = [];
		$defLabels = [];
		$labels[ ] = [ 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		$labels[ ] = [ 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		$labels[ ] = [ 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		if ( Sobi::Lang() != Sobi::DefLang() ) {
			$defLabels[ ] = [ 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
			$defLabels[ ] = [ 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
			$defLabels[ ] = [ 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		}
		if ( count( $labels ) ) {
			try {
				if ( Sobi::Lang() != Sobi::DefLang() ) {
					$db->insertArray( 'spdb_language', $defLabels, false, true );
				}
				$db->insertArray( 'spdb_language', $labels, true );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELD_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		SPFactory::cache()->cleanSection();
	}

	private function nid( $nid, $new )
	{
		$c = 1;
		$a = 2;
		$suffix = null;
		while ( $c ) {
			/* field alias has to be unique */
			try {
				$condition = [ 'nid' => $nid . $suffix, 'section' => Sobi::Section() ];
				if ( !( $new ) ) {
					$condition[ '!fid' ] = $this->id;
				}
				$c = SPFactory::db()
						->select( 'COUNT( nid )', 'spdb_field', $condition )
						->loadResult();
				if ( $c > 0 ) {
					$suffix = '_' . $a++;
				}
			} catch ( SPException $x ) {
			}
		}
		return $nid . $suffix;
	}

	/**
	 * Adding new field
	 * Save base data and redirect to the edit function when the field type has been chosed
	 * @param $attr
	 * @return int
	 */
	public function saveNew( $attr )
	{
		$db = SPFactory::db();

		/* cast all needed data and clean - it is possible just in admin panel but "strzeżonego pan Bóg strzeże" ;-) */
		$base = [];
		$base[ 'section' ] = ( isset( $attr[ 'section' ] ) && $attr[ 'section' ] ) ? $attr[ 'section' ] : SPRequest::sid();
		$this->loadType();

		if ( isset( $attr[ 'name' ] ) )
			$base[ 'name' ] = $db->escape( $attr[ 'name' ] );
		if ( isset( $attr[ 'description' ] ) )
			$base[ 'description' ] = $db->escape( $attr[ 'description' ] );
		else
			$base[ 'description' ] = null;
		if ( isset( $attr[ 'suffix' ] ) )
			$base[ 'suffix' ] = $db->escape( $attr[ 'suffix' ] );
		else
			$base[ 'suffix' ] = null;
		if ( isset( $attr[ 'nid' ] ) )
			$base[ 'nid' ] = $this->nid( $db->escape( preg_replace( '/[^[:alnum:]\-\_]/', null, $attr[ 'nid' ] ) ), true );
		if ( isset( $attr[ 'cssClass' ] ) )
			$base[ 'cssClass' ] = $db->escape( preg_replace( '/[^[:alnum:]\-\_ ]/', null, $attr[ 'cssClass' ] ) );
		if ( isset( $attr[ 'notice' ] ) )
			$base[ 'notice' ] = $db->escape( $attr[ 'notice' ] );
		if ( isset( $attr[ 'showIn' ] ) )
			$base[ 'showIn' ] = $db->escape( preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'showIn' ] ) );
		if ( isset( $attr[ 'fieldType' ] ) )
			$base[ 'fieldType' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'fieldType' ] );
		if ( isset( $attr[ 'type' ] ) )
			$base[ 'fieldType' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'type' ] );
		if ( isset( $attr[ 'description' ] ) )
			$base[ 'description' ] = $db->escape( $attr[ 'description' ] );
		if ( isset( $attr[ 'enabled' ] ) )
			$base[ 'enabled' ] = ( int )$attr[ 'enabled' ];
		if ( isset( $attr[ 'required' ] ) )
			$base[ 'required' ] = ( int )$attr[ 'required' ];
		if ( isset( $attr[ 'adminField' ] ) )
			$base[ 'adminField' ] = ( int )$attr[ 'adminField' ];
		if ( isset( $attr[ 'adminField' ] ) && $attr[ 'adminField' ] ) {
			$attr[ 'required' ] = false;
		}
		if ( isset( $attr[ 'editable' ] ) )
			$base[ 'editable' ] = ( int )$attr[ 'editable' ];
		if ( isset( $attr[ 'editLimit' ] ) ) {
			$base[ 'editLimit' ] = ( int )$attr[ 'editLimit' ];
			$base[ 'editLimit' ] = $base[ 'editLimit' ] > 0 ? $base[ 'editLimit' ] : -1;
		}
		if ( isset( $attr[ 'isFree' ] ) )
			$base[ 'isFree' ] = ( int )$attr[ 'isFree' ];
		if ( isset( $attr[ 'withLabel' ] ) )
			$base[ 'withLabel' ] = ( int )$attr[ 'withLabel' ];
		if ( isset( $attr[ 'inSearch' ] ) )
			$base[ 'inSearch' ] = ( int )$attr[ 'inSearch' ];
		if ( isset( $attr[ 'admList' ] ) )
			$base[ 'admList' ] = ( int )$attr[ 'admList' ];
		if ( isset( $attr[ 'fee' ] ) )
			$base[ 'fee' ] = ( double )$attr[ 'fee' ];
		if ( isset( $attr[ 'section' ] ) )
			$base[ 'section' ] = ( int )$attr[ 'section' ];
		$base[ 'version' ] = 1;

		/* determine the right position */
		try {
			$condition = [ 'section' => SPRequest::sid() ];
			if ( !( SPRequest::int( 'category-field' ) ) ) {
				$condition[ 'adminField>' ] = -1;
			}
			$base[ 'position' ] = ( int )$db->select( 'MAX( position )', 'spdb_field', $condition )
							->loadResult() + 1;
			if ( !$base[ 'position' ] ) {
				$base[ 'position' ] = 1;
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELD_POSITION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		/* get database columns and their ordering */
		$cols = $db->getColumns( 'spdb_field' );
		$values = [];

		/* and sort the properties in the same order */
		foreach ( $cols as $col ) {
			$values[ $col ] = array_key_exists( $col, $base ) ? $base[ $col ] : '';
		}

		/* save new field */
		try {
			$db->insert( 'spdb_field', $values );
			$this->fid = $db->insertid();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), $x->getMessage(), SPC::ERROR, 500, __LINE__, __FILE__ );
		}

		if ( $this->_type && method_exists( $this->_type, 'saveNew' ) ) {
			$this->_type->saveNew( $base, $this->fid );
		}

		/* save language depend properties */
		$labels = [];
		$defLabels = [];
		$labels[ ] = [ 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		$labels[ ] = [ 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		$labels[ ] = [ 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		if ( Sobi::Lang() != Sobi::DefLang() ) {
			$defLabels[ ] = [ 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
			$defLabels[ ] = [ 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
			$defLabels[ ] = [ 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid ];
		}
		if ( count( $labels ) ) {
			try {
				if ( Sobi::Lang() != Sobi::DefLang() ) {
					$db->insertArray( 'spdb_language', $defLabels, false, true );
				}
				$db->insertArray( 'spdb_language', $labels, true );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELD_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		SPFactory::cache()->cleanSection();
		return $this->fid;
	}

	/* (non-PHPdoc)
	 * @see Site/lib/models/SPField#loadType()
	 */
	public function loadType( $type = null )
	{
		if ( $type ) {
			$this->type = $type;
		}
		else {
			$this->type =& $this->fieldType;
		}
		if ( $this->type && SPLoader::translatePath( 'opt.fields.adm.' . $this->type ) ) {
			SPLoader::loadClass( 'opt.fields.fieldtype' );
			$fType = SPLoader::loadClass( 'opt.fields.adm.' . $this->type );
			$this->_type = new $fType( $this );
		}
		elseif ( $this->type && SPLoader::translatePath( 'opt.fields.' . $this->type ) ) {
			SPLoader::loadClass( 'opt.fields.fieldtype' );
			$fType = SPLoader::loadClass( 'opt.fields.' . $this->type );
			$this->_type = new $fType( $this );
		}
		else {
			parent::loadType();
		}
	}

	public function onFieldEdit( &$view )
	{
		$this->loadType();
		$this->editLimit = $this->editLimit > 0 ? $this->editLimit : 0;
		$this->fee = SPLang::currency( $this->fee, false );
		if ( is_array( $this->allowedAttributes ) && !( is_string( $this->allowedAttributes ) ) ) {
			$this->allowedAttributes = implode( ', ', $this->allowedAttributes );
		}
		if ( is_array( $this->allowedTags ) && !( is_string( $this->allowedTags ) ) ) {
			$this->allowedTags = implode( ', ', $this->allowedTags );
		}
		if ( $this->_type && method_exists( $this->_type, 'onFieldEdit' ) ) {
			$this->_type->onFieldEdit( $view );
		}
	}
}
