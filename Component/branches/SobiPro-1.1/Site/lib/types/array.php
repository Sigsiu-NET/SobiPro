<?php
/**
 * @version: $Id: array.php 2347 2012-04-09 15:36:07Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-04-09 17:36:07 +0200 (Mon, 09 Apr 2012) $
 * $Revision: 2347 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/types/array.php $
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
	private $_arr = array();

	/**
	 * @param string $str
	 * @param string $sep
	 * @param string $sep2
	 */
	public function fromString( $str, $sep, $sep2 = null )
	{
		if( strstr( $str, $sep ) ) {
			$arr = explode( $sep, $str );
			if( $sep2 ) {
				$c = 0;
				foreach ( $arr as $field ) {
					if( strstr( $field, $sep2 ) ) {
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
	function toString( $array = null, $inDel = '=', $outDel = ' ' )
	{
		$out = array();
		if( is_array( $array ) && count( $array ) ) {
			foreach( $array as $key => $item ) {
				if( is_array( $item ) ) {
					$out[] = $this->toString( $item, $inDel, $outDel );
				}
				else {
					$out[] = "{$key}{$inDel}\"{$item}\"";
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
		if( is_array( $arr ) && count( $arr ) ) {
			foreach ( $arr as $i => $k ) {
				if( ( int ) $k != $k  ) {
					return false;
				}
			}
		}
		return true;
	}
	public function toXML( $arr, $root = 'root' )
	{
		$content = null;
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->formatOutput = true;
		$node = $dom->appendChild( $dom->createElement( SPLang::nid( $root ) ) );
		$this->_toXML( $arr, $node, $dom );
		$conntent = $dom->saveXML();
		return $conntent;
	}

	private function _toXML( $arr, &$node, &$dom )
	{
		if( is_array( $arr ) && count( $arr ) ) {
			foreach ( $arr as $name => $value ) {
				if( is_array( $value ) ) {
					$nn = $node->appendChild( $dom->createElement( SPLang::nid( $name ) ) );
					$this->_toXML( $value, $nn, $dom );
				}
				else {
					if( is_numeric( $name ) ) {
						$name = 'value';
					}
					$node->appendChild( $dom->createElement( SPLang::nid( $name ), preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $value ) ) );
				}
			}
		}
	}

	/**
	 * @param DOMDocument $dom
	 * @return array
	 */
	public function fromXML( $dom, $root )
	{
		$r = $dom->getElementsByTagName( $root );
		$arr = array();
		$this->_fromXML( $r, $arr );
		return $arr;
	}

	/**
	 * @param DOMNodeList $dom
	 * @param array $arr
	 * @return void
	 */
	private function _fromXML( $dom, &$arr )
	{
		foreach ( $dom as $node ) {
			if( $node->hasChildNodes() ) {
				if( $node->childNodes->item( 0 )->nodeName == '#text' && $node->childNodes->length == 1 ) {
					$arr[ $node->nodeName ] = $node->nodeValue;
				}
				else {
					$arr[ $node->nodeName ] = array();
					$this->_fromXML( $node->childNodes, $arr[ $node->nodeName ] );
				}
			}
			else {
				if( $node->nodeName != '#text' ) {
					$arr[ $node->nodeName ] = $node->nodeValue;
				}
			}
		}
	}
}
