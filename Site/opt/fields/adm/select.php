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
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.select' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 19-Nov-2009 13:29:57 PM
 */
class SPField_SelectAdm extends SPField_Select
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
			$hold = array();
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
					$options[ ] = array( 'id' => $oid, 'name' => $o[ 'name' ], 'parent' => null, 'position' => ++$p );
					$hold[ $oid ] = $oid;
				}
			}
		}
		if ( count( $options ) ) {
			unset( $attr[ 'options' ] );
			$optionsArr = array();
			$labelsArr = array();
			$optsIds = array();
			$defLabelsArr = array();
			$duplicates = false;
			foreach ( $options as $i => $option ) {
				/* check for doubles */
				foreach ( $options as $pos => $opt ) {
					if ( $i == $pos ) {
						continue;
					}
					if ( $option[ 'id' ] == $opt[ 'id' ] ) {
						$option[ 'id' ] = $option[ 'id' ] . '_' . substr( ( string )microtime(), 2, 8 ) . rand( 1, 100 );
						$duplicates = true;
					}
				}
				$optionsArr[ ] = array( 'fid' => $this->id, 'optValue' => $option[ 'id' ], 'optPos' => $option[ 'position' ], 'optParent' => $option[ 'parent' ] );
				$defLabelsArr[ ] = array( 'sKey' => $option[ 'id' ], 'sValue' => $option[ 'name' ], 'language' => $defLang, 'oType' => 'field_option', 'fid' => $this->id );
				$labelsArr[ ] = array( 'sKey' => $option[ 'id' ], 'sValue' => $option[ 'name' ], 'language' => $lang, 'oType' => 'field_option', 'fid' => $this->id );
				$optsIds[ ] = $option[ 'id' ];
			}
			if ( $duplicates ) {
				SPFactory::message()->warning( 'FIELD_WARN_DUPLICATE_OPT_ID' );
			}
			$db = SPFactory::db();
			/* try to delete the existing labels */
			try {
				$db->delete( 'spdb_field_option', array( 'fid' => $this->id ) );
				$db->delete( 'spdb_language', array( 'oType' => 'field_option', 'fid' => $this->id, '!sKey' => $optsIds ) );

			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_STORE_FIELD_OPTIONS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			/* insert new values */
			try {
				$db->insertArray( 'spdb_field_option', $optionsArr );
				$db->insertArray( 'spdb_language', $labelsArr, true );
				if ( $defLang != $lang ) {
					$db->insertArray( 'spdb_language', $defLabelsArr, false, true );
				}

			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DELETE_SELECTED_OPTIONS', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		if ( !isset( $attr[ 'params' ] ) ) {
			$attr[ 'params' ] = array();
		}
		$myAttr = $this->getAttr();
		$properties = array();
		if ( count( $myAttr ) ) {
			foreach ( $myAttr as $property ) {
				$properties[ $property ] = isset( $attr[ $property ] ) ? ( $attr[ $property ] ) : null;
			}
		}
		$this->sets[ 'field.options' ] = SPFactory::Instance( 'types.array' )
				->toINIString( $data );

		/** handle upload of new definition file */
		$XMLFile = SPRequest::file( 'select-list-dependency', 'tmp_name' );
		if ( $XMLFile && file_exists( $XMLFile ) ) {
			$XMLFileName = SPRequest::file( 'select-list-dependency', 'name' );
			if ( SPFs::upload( $XMLFile, SOBI_PATH . '/etc/fields/select-list/' . $XMLFileName ) ) {
				$properties[ 'dependencyDefinition' ] = $XMLFileName;
			}
		}

		/** if we use it - let's transform the XML file  */
		if ( $properties[ 'dependency' ] ) {
			$this->parseDependencyDefinition( $properties[ 'dependencyDefinition' ] );
		}
		$attr[ 'params' ] = $properties;
	}

	protected function parseDependencyDefinition( $file )
	{
		$dom = new DOMDocument();
		$dom->load( SOBI_PATH . '/etc/fields/select-list/' . $file );
		$xpath = new DOMXPath( $dom );
		$definition = array();
		$root = $xpath->query( '/definition' );
		$definition[ 'prefix' ] = $root->item( 0 )->attributes->getNamedItem( 'prefix' )->nodeValue;
		$definition[ 'translation' ] = $root->item( 0 )->attributes->getNamedItem( 'translation' )->nodeValue;
		$definition[ 'options' ] = array();
		$this->_parseXML( $xpath->query( '/definition/option' ), $definition[ 'options' ] );
		SPFs::write( SOBI_PATH . '/etc/fields/select-list/definitions/' . ( str_replace( '.xml', '.json', $file ) ), json_encode( $definition ) );
	}

	/**
	 * @param DOMNodeList $nodes
	 * @param $definition
	 */
	protected function _parseXML( DOMNodeList $nodes, &$definition )
	{
		foreach ( $nodes as $node ) {
			if ( !( $node->attributes ) ) {
				continue;
			}
			$option = array(
					'id' => $node->attributes->getNamedItem( 'id' )->nodeValue,
					'final' => $node->attributes->getNamedItem( 'final' ) ? $node->attributes->getNamedItem( 'final' )->nodeValue : false,
					'childs' => array()
			);
			if ( $node->hasChildNodes() ) {
				$this->_parseXML( $node->childNodes, $option[ 'childs' ] );
			}
			$definition[ ] = $option;
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
	}

	public function delete()
	{
		/* @var SPdb $db */
		$db = SPFactory::db();
		try {
			$db->delete( 'spdb_field_option', array( 'fid' => $this->id ) );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}
}
