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

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 21-Jan-2009 1:35:29 PM
 */

final class SPData_Array extends SPObject
{
	/**
	 * @var array
	 */
	private $_arr = [];
	/** @var bool */
	private $legacy = true;

	/**
	 * @param string $str
	 * @param string $sep
	 * @param string $sep2
	 */
	public function fromString( $str, $sep, $sep2 = null )
	{
		if ( strstr( $str, $sep ) ) {
			$arr = explode( $sep, $str );
			if ( $sep2 ) {
				$c = 0;
				foreach ( $arr as $field ) {
					if ( strstr( $field, $sep2 ) ) {
						$f = explode( $sep2, $field );
						$this->_arr[ $f[ 0 ] ] = $f[ 1 ];
					}
					else {
						$this->_arr[ $c ] = $field;
						$c++;
					}
				}
			}
			else {
				$this->_arr = $arr;
			}
		}
	}

	/**
	 * @param $array
	 * @param $inDel
	 * @param $outDel
	 * @return string
	 */
	function toString( $array, $inDel = '=', $outDel = ' ' )
	{
		$out = [];
		if ( is_array( $array ) && count( $array ) ) {
			foreach ( $array as $key => $item ) {
				if ( is_array( $item ) ) {
					$out[ ] = $this->toString( $item, $inDel, $outDel );
				}
				else {
					$out[ ] = "{$key}{$inDel}\"{$item}\"";
				}
			}
		}
		return implode( $outDel, $out );
	}

	/**
	 * @return array
	 */
	public function toArr()
	{
		return $this->_arr;
	}

	/**
	 * Check if given array ia array of integers
	 *
	 * @param array $arr
	 * @return bool
	 */
	public static function is_int( $arr )
	{
		if ( is_array( $arr ) && count( $arr ) ) {
			foreach ( $arr as $i => $k ) {
				if ( ( int )$k != $k ) {
					return false;
				}
			}
		}
		return true;
	}

	public function toINIString( $arr )
	{
		$this->_arr = $arr;
		$out = [];
		if ( is_array( $this->_arr ) && count( $this->_arr ) ) {
			foreach ( $this->_arr as $key => $value ) {
				if ( is_array( $value ) && !( is_string( $value ) ) ) {
					$out[ ] = "[{$key}]";
					if ( count( $value ) ) {
						foreach ( $value as $k => $v ) {
							$k = $this->_cleanIni( $k );
							$out[ ] = "{$k} = \"{$v}\"";
						}
					}
				}
				else {
					$key = $this->_cleanIni( $key );
					$out[ ] = "{$key} = \"{$value}\"";
				}
			}
		}
		return implode( "\n", $out );
	}

	private function _cleanIni( $txt )
	{
		return str_replace( [ '?{}|&~![()^"' ], null, $txt );
	}

	public function toXML( $arr, $root = 'root', $returnDOM = false )
	{
		$content = null;
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->formatOutput = true;
		$node = $dom->appendChild( $dom->createElement( $this->nid( $root ) ) );
		$this->_toXML( $arr, $node, $dom );
		if ( $returnDOM ) {
			return $dom;
		}
		$content = $dom->saveXML();
		return $content;
	}

	private function _toXML( $arr, &$node, &$dom )
	{
		if ( is_array( $arr ) && count( $arr ) ) {
			foreach ( $arr as $name => $value ) {
				if ( is_numeric( $name ) ) {
					$name = 'value';
				}
				if ( is_array( $value ) ) {
					$nn = $node->appendChild( $dom->createElement( $this->nid( $name ) ) );
					$this->_toXML( $value, $nn, $dom );
				}
				else {
					$node->appendChild( $dom->createElement( $this->nid( $name ), preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $value ) ) );
				}
			}
		}
	}

	protected function nid( $txt )
	{
		return $this->legacy ? strtolower( SPLang::nid( $txt ) ) : SPLang::nid( $txt );
	}

	/**
	 * @param DOMDocument $dom
	 * @param $root
	 * @return array
	 */
	public function fromXML( $dom, $root )
	{
		$r = $dom->getElementsByTagName( $root );
		$arr = [];
		$this->_fromXML( $r, $arr );
		return $arr;
	}

	/**
	 * @param $arr
	 * @param string $root
	 * @param bool $returnDOM
	 * @internal param \DOMDocument $dom
	 * @return array
	 */
	public function createXML( $arr, $root = 'root', $returnDOM = false )
	{
		$this->legacy = false;
		return $this->toXML( $arr, $root, $returnDOM );
	}

	/**
	 * @param DOMNodeList $dom
	 * @param array $arr
	 * @return void
	 */
	private function _fromXML( $dom, &$arr )
	{
		foreach ( $dom as $node ) {
			if ( $node->hasChildNodes() ) {
				if ( $node->childNodes->item( 0 )->nodeName == '#text' && $node->childNodes->length == 1 ) {
					$arr[ $node->nodeName ] = $node->nodeValue;
				}
				else {
					$arr[ $node->nodeName ] = [];
					$this->_fromXML( $node->childNodes, $arr[ $node->nodeName ] );
				}
			}
			else {
				if ( $node->nodeName != '#text' ) {
					$arr[ $node->nodeName ] = $node->nodeValue;
				}
			}
		}
	}
}
