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

SPLoader::loadView( 'view' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:16:04 PM
 */
class SPCachedView extends SPFrontView implements SPView
{
	public function cachedView( $xml, $template, $cacheId, $config = array() )
	{
		$this->_xml = $xml;
		$this->parseXml();
		$templatePackage = SPLoader::translateDirPath( Sobi::Cfg( 'section.template' ), 'templates' );
		$template = Sobi::FixPath( $templatePackage . '/' . $template );
		$this->_templatePath = $template;
		$this->_template = str_replace( '.xsl', null, $template );
		$ini = array();
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
		$this->validateData( $cacheId );
	}

	protected function validateData( $cacheId )
	{
		$sids = SPFactory::db()
				->select( 'sid', 'spdb_view_cache_relation', array( 'cid' => $cacheId ) )
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
	}

	protected function parseXml()
	{
		$header = $this->_xml->getElementsByTagName( 'header' )->item( 0 );
		if ( $header->hasChildNodes() ) {
			foreach ( $header->childNodes as $node ) {
				if ( !( strstr( $node->nodeName, '#' ) ) ) {
					$params = array();
					$this->parseParams( $node, $params );
					$this->callHeader( $node->nodeName, $params[ $node->nodeName ] );
				}
			}
		}
		$data = $this->_xml->getElementsByTagName( 'cache-data' )->item( 0 );
		if ( $data && $data->hasChildNodes() ) {
			foreach ( $data->childNodes as $node ) {
				if ( !( strstr( $node->nodeName, '#' ) ) ) {
					$params = array();
					$this->parseParams( $node, $params );
					if ( isset( $params[ 'hidden' ] ) && is_array( $params[ 'hidden' ] ) && count( $params[ 'hidden' ] ) ) {
						foreach ( $params[ 'hidden' ] as $k => $v ) {
							$this->addHidden( $v, $k );
						}
					}
				}
			}
		}
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
					$methodArgs = array();
					foreach ( $methodParams as $param ) {
						if ( isset( $call[ $param->name ] ) ) {
							$methodArgs[ ] = $call[ $param->name ];
						}
						elseif ( $param->name == 'value' && !( isset( $call[ 'value' ] ) ) && isset( $call[ 'name' ] ) ) {
							$methodArgs[ ] = $this->get( $call[ 'name' ] );
						}
						elseif ( $param->isDefaultValueAvailable() ) {
							$methodArgs[ ] = $param->getDefaultValue();
						}
						else {
							$methodArgs[ ] = null;
						}
					}
					call_user_func_array( array( $header, $methods[ $method ] ), $methodArgs );
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
					$value = array();
					foreach ( $node->childNodes as $subNode ) {
						$this->parseParams( $subNode, $value );
					}
				}
			}
			else {
				$value = $node->nodeValue;
			}
			if ( $node->nodeName == 'value' ) {
				$params[ ] = $value;
			}
			else {
				$params[ $node->nodeName ] = $value;
			}
		}
	}
}