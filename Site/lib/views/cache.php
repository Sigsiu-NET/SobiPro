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

use Sobi\FileSystem\FileSystem;
use Sobi\Input\Input;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadView( 'view' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:16:04 PM
 */
class SPCachedView extends SPFrontView implements SPView
{
	public function cachedView( $xml, $template, $cacheId, $config = [] )
	{
		$this->_xml = $xml;
		Sobi::Trigger( 'Start', ucfirst( __FUNCTION__ ), [ &$this->_xml ] );
		$templatePackage = SPLoader::translateDirPath( Sobi::Cfg( 'section.template' ), 'templates' );

		if ( file_exists( $templatePackage . $template ) ) {
			$template = $templatePackage . $template;
		}
		else {
			$templateOverride = Input::Cmd( 'sptpl' );
			if ( $templateOverride ) {
				if ( strstr( $templateOverride, '.' ) ) {
					$templateOverride = str_replace( '.', '/', $templateOverride );
				}
				$template = $templateOverride . '.xsl';
			}
			if ( !( file_exists( $template ) ) ) {
				if ( file_exists( FileSystem::FixPath( $templatePackage . '/' . $template ) ) ) {
					$template = FileSystem::FixPath( $templatePackage . '/' . $template );
				}
				else {
					$type = SPFactory::db()
							->select( 'oType', 'spdb_object', [ 'id' => SPRequest::sid() ] )
							->loadResult();
					$template = ( $templatePackage . '/' . $type . '/' . $template );
				}
			}
		}

		SPFactory::registry()->set( 'current_template', $templatePackage );
		$this->_templatePath = $templatePackage;
		$this->_template = str_replace( '.xsl', null, $template );
		$ini = [];
		if ( count( $config ) ) {
			foreach ( $config as $file ) {
				$file = parse_ini_file( $file, true );
				foreach ( $file as $section => $keys ) {
					if ( isset( $ini[ $section ] ) ) {
						$ini[ $section ] = array_merge( $ini[ $section ], $keys );
					}
					else {
						$ini[ $section ] = $keys;
					}
				}
			}
		}
		$this->setConfig( $ini, SPRequest::task( 'get' ) );
		$this->parseXml();
		$this->validateData( $cacheId );
		Sobi::Trigger( 'After', ucfirst( __FUNCTION__ ), [ &$this->_xml ] );
	}

	protected function validateData( $cacheId )
	{
		$sids = SPFactory::db()
				->select( 'sid', 'spdb_view_cache_relation', [ 'cid' => $cacheId ] )
				->loadResultArray();
		if ( $sids && count( $sids ) ) {
			$this->loadNonStaticData( $sids );
			$this->validateNodes();
		}
	}

	protected function validateNodes()
	{
		$nodes = $this->_xml->getElementsByTagName( 'counter' );
		if ( $nodes->length ) {
			/** $node DOMNode */
			foreach ( $nodes as $node ) {
				/** $parent DOMNode */
				$parent = $node->parentNode;
				if ( $parent->attributes->getNamedItem( 'id' ) && $parent->attributes->getNamedItem( 'id' )->nodeValue ) {
					$counter = $this->getNonStaticData( $parent->attributes->getNamedItem( 'id' )->nodeValue, 'counter' );
					if ( $counter ) {
						$node->nodeValue = $counter;
					}
				}
			}
		}
		$tokens = $this->_xml->getElementsByTagName( 'token' );
		foreach ( $tokens as $node ) {
			$node->nodeValue = SPFactory::mainframe()->token();
		}
	}

	protected function parseXml()
	{
		$header = $this->_xml->getElementsByTagName( 'header' )->item( 0 );
		if ( $header->hasChildNodes() ) {
			foreach ( $header->childNodes as $node ) {
				if ( !( strstr( $node->nodeName, '#' ) ) ) {
					$params = [];
					$this->parseParams( $node, $params );
					$this->callHeader( $node->nodeName, $params[ $node->nodeName ] );
				}
			}
		}
		$data = $this->_xml->getElementsByTagName( 'cache-data' )->item( 0 );
		if ( $data && $data->hasChildNodes() ) {
			foreach ( $data->childNodes as $node ) {
				if ( !( strstr( $node->nodeName, '#' ) ) ) {
					$params = [];
					$this->parseParams( $node, $params );
					if ( isset( $params[ 'hidden' ] ) && is_array( $params[ 'hidden' ] ) && count( $params[ 'hidden' ] ) ) {
						foreach ( $params[ 'hidden' ] as $k => $v ) {
							$this->addHidden( $v, $k );
						}
					}
					if ( isset( $params[ 'request' ] ) && is_array( $params[ 'request' ] ) && count( $params[ 'request' ] ) ) {
						foreach ( $params[ 'request' ] as $k => $v ) {
							SPRequest::set( $k, $v, 'get' );
						}
					}
					if ( isset( $params[ 'pathway' ] ) && is_array( $params[ 'pathway' ] ) && count( $params[ 'pathway' ] ) ) {
						foreach ( $params[ 'pathway' ] as $v ) {
							SPFactory::mainframe()->addToPathway( $v[ 'name' ], $v[ 'url' ] );
						}
					}
				}
			}
		}
		$visitor = $this->visitorArray( SPFactory::user()->getCurrent() );
		if ( is_array( $visitor ) && ( isset( $visitor[ '_data' ] ) ) ) {
			$this->importData( $this->_xml->documentElement, $visitor, 'visitor' );
		}
		$messages = SPFactory::message()->getMessages();
		$info = [];
		if ( count( $messages ) ) {
			foreach ( $messages as $type => $content ) {
				$info[ $type ] = array_values( $content );
			}
		}
		if ( is_array( $info ) ) {
			$this->importData( $this->_xml->documentElement, $info, 'messages' );
		}
		$this->_xml->formatOutput = true;
	}

	protected function importData( $node, $data, $name )
	{
		$root = $this->_xml->createElement( $name );
		if ( isset( $data[ '_data' ] ) && is_array( $data[ '_data' ] ) ) {
			foreach ( $data[ '_data' ] as $index => $value ) {
				if ( is_array( $value ) ) {
					$this->importData( $root, $value, $index );
				}
				else {
					$child = $this->_xml->createElement( $index, $value );
					$root->appendChild( $child );
				}
			}
		}
		elseif ( !( isset( $data[ '_data' ] ) ) && is_array( $data ) && count( $data ) ) {
			foreach ( $data as $i => $v ) {
				if ( is_numeric( $i ) ) {
					$i = SPLang::singular( $name );
				}
				if ( is_array( $v ) ) {
					$this->importData( $root, $v, $i );
				}
				else {
					$root->appendChild( $this->_xml->createElement( $i, $v ) );
				}
			}
		}
		elseif ( isset( $data[ '_data' ] ) ) {
			$root->nodeValue = $data[ '_data' ];
		}
		if ( isset( $data[ '_attributes' ] ) && $data[ '_attributes' ] ) {
			foreach ( $data[ '_attributes' ] as $i => $v ) {
				$a = $this->_xml->createAttribute( SPLang::varName( $i ) );
				$a->appendChild( $this->_xml->createTextNode( $v ) );
				$root->appendChild( $a );
			}
		}
		$node->appendChild( $root );
	}

	protected function callHeader( $method, $calls )
	{
		static $header = null;
		static $methods = null;
		if ( !( $header ) ) {
			$header = SPFactory::header();
			$m = get_class_methods( $header );
			foreach ( $m as $function ) {
				$methods[ strtolower( $function ) ] = $function;
			}
		}
		if ( count( $calls ) ) {
			if ( method_exists( $header, $methods[ $method ] ) ) {
				$instance = new ReflectionMethod( $header, $methods[ $method ] );
				$methodParams = $instance->getParameters();
				foreach ( $calls as $call ) {
					$methodArgs = [];
					foreach ( $methodParams as $param ) {
						if ( isset( $call[ $param->name ] ) ) {
							$methodArgs[] = $call[ $param->name ];
						}
						elseif ( $param->name == 'value' && !( isset( $call[ 'value' ] ) ) && isset( $call[ 'name' ] ) ) {
							$methodArgs[] = $this->get( $call[ 'name' ] );
						}
						elseif ( $param->isDefaultValueAvailable() ) {
							$methodArgs[] = $param->getDefaultValue();
						}
						else {
							$methodArgs[] = null;
						}
					}
					call_user_func_array( [ $header, $methods[ $method ] ], $methodArgs );
				}
			}
		}
	}

	/**
	 * @param $node DOMNode
	 * @param $params array
	 */
	protected function parseParams( $node, &$params )
	{
		$value = null;
		if ( !( strstr( $node->nodeName, '#' ) ) ) {
			if ( $node->hasChildNodes() ) {
				if ( $node->childNodes->item( 0 )->nodeName == '#text' && $node->childNodes->length == 1 ) {
					$value = $node->nodeValue;
				}
				else {
					$value = [];
					foreach ( $node->childNodes as $subNode ) {
						$this->parseParams( $subNode, $value );
					}
				}
			}
			else {
				$value = $node->nodeValue;
			}
			if ( $node->nodeName == 'value' ) {
				$params[] = $value;
			}
			else {
				$params[ $node->nodeName ] = $value;
			}
		}
	}
}
