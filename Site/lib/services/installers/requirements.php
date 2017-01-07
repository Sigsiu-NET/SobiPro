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
 * @created 05-Jul-2010 10:22:53
 */

class SPRequirements
{
	public function __construct()
	{
	}

	/**
	 * @param DOMNodeList $requirements
	 * @throws SPException
	 * @return bool
	 */
	public function check( $requirements )
	{
		if ( $requirements && ( $requirements instanceof DOMNodeList ) ) {
			for ( $i = 0; $i < $requirements->length; $i++ ) {
				$reqVersion = null;
				if ( $requirements->item( $i )->attributes->getNamedItem( 'version' ) && $requirements->item( $i )->attributes->getNamedItem( 'version' )->nodeValue ) {
					$reqVersion = $this->parseVer( $requirements->item( $i )->attributes->getNamedItem( 'version' )->nodeValue );
				}
				switch ( $requirements->item( $i )->nodeName ) {
					case 'core':
						if ( !( $this->compareVersion( $reqVersion, SPFactory::CmsHelper()->myVersion() ) ) ) {
							throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_CORE', implode( '.', $reqVersion ), implode( '.', SPFactory::CmsHelper()->myVersion() ) ) );
						}
						break;
					case 'cms':
						$cms = SPFactory::CmsHelper()->cmsVersion( $requirements->item( $i )->nodeValue );
						if ( !( is_array( $cms ) ) ) {
							throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_REQU_CMS', $requirements->item( $i )->nodeValue, implode( '.', $reqVersion ), $cms ) );
						}
						if ( !( $this->compareVersion( $reqVersion, $cms ) ) ) {
							throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_REQ', $requirements->item( $i )->nodeValue, implode( '.', $reqVersion ), implode( '.', SPFactory::CmsHelper()->cmsVersion() ) ) );
						}
						break;
					case 'field':
					case 'plugin':
					case 'application':
						$version = $this->extension( $requirements->item( $i )->nodeValue, $requirements->item( $i )->nodeName );
						if ( !( $version ) ) {
							// "Cannot install extension. This extension requires %s %s version >= %s, But this %s is not installed."
							if ( $reqVersion ) {
								throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_PLG', $requirements->item( $i )->nodeName, $requirements->item( $i )->nodeValue, implode( '.', $reqVersion ), $requirements->item( $i )->nodeName ) );
							}
							else {
								throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_PLG_NO_VERSION', $requirements->item( $i )->nodeName, $requirements->item( $i )->nodeValue, $requirements->item( $i )->nodeName ) );
							}
						}
						if ( isset( $reqVersion ) && !( $this->compareVersion( $reqVersion, $version ) ) ) {
							throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_FIELD', $requirements->item( $i )->nodeName, $requirements->item( $i )->nodeValue, implode( '.', $reqVersion ), implode( '.', $version ) ) );
						}
						break;
					case 'php':
						$version = $this->phpReq( $requirements->item( $i ) );
						$type = 'PHP';
						if ( ( $requirements->item( $i )->attributes->getNamedItem( 'type' ) ) && ( $requirements->item( $i )->attributes->getNamedItem( 'type' )->nodeValue ) ) {
							$type = $requirements->item( $i )->attributes->getNamedItem( 'type' );
						}
						if ( strlen( $version ) && isset( $reqVersion ) ) {
							if ( isset( $reqVersion ) && !( $this->compareVersion( $reqVersion, $version ) ) ) {
								throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_FIELD', $type, $requirements->item( $i )->nodeValue, implode( '.', $reqVersion ), implode( '.', $version ) ) );
							}
						}
						elseif ( $version == false ) {
							throw new SPException( SPLang::e( 'CANNOT_INSTALL_EXT_MISSING', $type, $requirements->item( $i )->nodeValue, implode( '.', $reqVersion ), implode( '.', $version ) ) );
						}
						break;
				}
			}
		}
	}

	private function parseVer( $v )
	{
		$v = explode( '.', $v );
		return [ 'major' => $v[ 0 ], 'minor' => $v[ 1 ], 'build' => ( isset( $v[ 2 ] ) ? $v[ 2 ] : 0 ), 'rev' => ( isset( $v[ 3 ] ) ? $v[ 3 ] : 0 ) ];
	}

	/*
	 * @todo = check disabled functions and classes
	 */
	private function phpReq( $node )
	{
		if ( ( $node->attributes->getNamedItem( 'version' ) ) && ( $node->attributes->getNamedItem( 'version' )->nodeValue ) ) {
			if ( isset( $node->nodeValue ) && $node->nodeValue ) {
				$v = phpversion( $node->nodeValue );
			}
			else {
				$v = PHP_VERSION;
			}
			if ( !( $v ) ) {
				return false;
			}
			return $this->parseVer( $v );
		}
		elseif ( ( $node->attributes->getNamedItem( 'type' ) ) && ( $node->attributes->getNamedItem( 'type' )->nodeValue ) ) {
			switch ( $node->attributes->getNamedItem( 'type' )->nodeValue ) {
				case 'function':
					$r = function_exists( $node->nodeValue );
					break;
				case 'class':
					$r = class_exists( $node->nodeValue );
					break;
				default:
					$r = false;
					break;
			}
			return $r;
		}
	}

	private function extension( $eid, $type )
	{
		static $extensions = null;
		if ( !( $extensions ) ) {
			try {
				SPFactory::db()->select( [ 'version', 'type', 'pid' ], 'spdb_plugins' );
				$exts = SPFactory::db()->loadObjectList();
			} catch ( SPException $x ) {
				Sobi::Error( 'installer', SPLang::e( 'CANNOT_GET_INSTALLED_EXTS', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
				return false;
			}
			if ( count( $exts ) ) {
				$extensions = [ 'plugin' => [], 'field' => [], 'payment' => [] ];
				foreach ( $exts as $ext ) {
					$extensions[ $ext->type ][ $ext->pid ] = $this->parseVer( $ext->version );
				}
			}
		}
		return isset( $extensions[ $type ][ $eid ] ) ? $extensions[ $type ][ $eid ] : false;
	}

	/**
	 * @param required version $req
	 * @param current version $to
	 * @return mixed
	 */
	private function compareVersion( $req, $to )
	{
		if ( $req[ 'major' ] > $to[ 'major' ] ) {
			return false;
		}
		elseif ( $req[ 'major' ] < $to[ 'major' ] ) {
			return true;
		}

		if ( $req[ 'minor' ] > $to[ 'minor' ] ) {
			return false;
		}
		elseif ( $req[ 'minor' ] < $to[ 'minor' ] ) {
			return true;
		}

		if ( $req[ 'build' ] > $to[ 'build' ] ) {
			return false;
		}
		elseif ( $req[ 'build' ] < $to[ 'build' ] ) {
			return true;
		}

		if ( $req[ 'rev' ] > $to[ 'rev' ] ) {
			return false;
		}
		return true;
	}
}
