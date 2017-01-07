<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.chbxgroup' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 27-Nov-2009 17:10:03
 */
class SPField_ChbxGrAdm extends SPField_ChbxGr
{
	public function save( &$attr )
	{
		static $lang = null;
		static $defLang = null;
		if ( !( $lang ) ) {
			$lang = Sobi::Lang();
			$defLang = Sobi::DefLang();
		}
		$file = SPRequest::file( 'spfieldsopts', 'tmp_name' );
		if ( $file ) {
			$data = parse_ini_file( $file, true );
		}
		elseif ( is_string( $attr[ 'options' ] ) ) {
			$data = parse_ini_string( $attr[ 'options' ], true );
		}
		else {
			$data = null;
		}
		$options = $this->parseOptsFile( $data );

		if ( !( count( $options ) ) && count( $attr[ 'options' ] ) ) {
			$p = 0;
			$hold = [];
			foreach ( $attr[ 'options' ] as $o ) {
				if ( is_numeric( $o[ 'id' ] ) ) {
					$o[ 'id' ] = $this->nid . '_' . $o[ 'id' ];
				}
				if ( isset( $o[ 'id' ] ) ) {
					$i = 0;
					$oid = $o[ 'id' ];
					while ( isset( $hold[ $oid ] ) ) {
						$oid = $o[ 'id' ] . '_' . ++$i;
					}
					$options[ ] = [ 'id' => $oid, 'name' => $o[ 'name' ], 'parent' => null, 'position' => ++$p ];
					$hold[ $oid ] = $oid;
				}
			}
		}

		if ( count( $options ) ) {
			unset( $attr[ 'options' ] );
			$optionsArr = [];
			$labelsArr = [];
			$defLabelsArr = [];
			$optsIds = [];
			foreach ( $options as $i => $option ) {
				/* check for doubles */
				foreach ( $options as $pos => $opt ) {
					if ( $i == $pos ) {
						continue;
					}
					if ( $option[ 'id' ] == $opt[ 'id' ] ) {
						$option[ 'id' ] = $option[ 'id' ] . '_' . substr( ( string )microtime(), 2, 8 ) . rand( 1, 100 );
						SPFactory::message()->warning( 'FIELD_WARN_DUPLICATE_OPT_ID' );
					}
				}
				$optionsArr[ ] = [ 'fid' => $this->id, 'optValue' => $option[ 'id' ], 'optPos' => $option[ 'position' ], 'optParent' => $option[ 'parent' ] ];
				$defLabelsArr[ ] = [ 'sKey' => $option[ 'id' ], 'sValue' => $option[ 'name' ], 'language' => $defLang, 'oType' => 'field_option', 'fid' => $this->id ];
				$labelsArr[ ] = [ 'sKey' => $option[ 'id' ], 'sValue' => $option[ 'name' ], 'language' => $lang, 'oType' => 'field_option', 'fid' => $this->id ];
				$optsIds[ ] = $option[ 'id' ];
			}
			/* @var SPdb $db */
			$db =& SPFactory::db();

			/* try to delete the existing labels */
			try {
				$db->delete( 'spdb_field_option', [ 'fid' => $this->id ] );
				$db->delete( 'spdb_language', [ 'oType' => 'field_option', 'fid' => $this->id, '!sKey' => $optsIds ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DELETE_SELECTED_OPTIONS', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			/* insert new values */
			try {
				$db->insertArray( 'spdb_field_option', $optionsArr );
				$db->insertArray( 'spdb_language', $labelsArr, true );
				if ( $defLang != $lang ) {
					$db->insertArray( 'spdb_language', $defLabelsArr, false, true );
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_STORE_FIELD_OPTIONS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		if ( !isset( $attr[ 'params' ] ) ) {
			$attr[ 'params' ] = [];
		}
		$myAttr = $this->getAttr();
		$properties = [];
		if ( count( $myAttr ) ) {
			foreach ( $myAttr as $property ) {
				$properties[ $property ] = isset( $attr[ $property ] ) ? ( $attr[ $property ] ) : null;
			}
		}
		$attr[ 'params' ] = $properties;
		$this->sets[ 'field.options' ] = SPFactory::Instance( 'types.array' )
				->toINIString( $data );
	}
}
