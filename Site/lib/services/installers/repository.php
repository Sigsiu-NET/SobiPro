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
 * @created 22-Jun-2010 13:39:51
 */
SPLoader::loadClass( 'services.installers.installer' );

class SPRepository extends SPInstaller
{

	/*** @var SPSoapClient */
	private $_server = null;
	/** @var array  */
	protected $_repoDefArr = [];

	public function __construct()
	{

	}

	public function loadDefinition( $path )
	{
		$this->xmlFile = $path;
		$this->definition = new DOMDocument();
		$this->definition->load( $this->xmlFile );
		$this->xdef = new DOMXPath( $this->definition );
		$this->root = dirname( $this->xmlFile );
		$this->type = 'repository';
	}

	public function saveToken( $token )
	{
		$arrdef = SPFactory::Instance( 'types.array' );
		$def = $arrdef->fromXML( $this->definition, 'repository' );
		$ndef = [];
		$u = false;
		$rid = null;
		foreach ( $def[ 'repository' ] as $k => $v ) {
			if ( $u ) {
				$ndef[ 'token' ] = $token;
			}
			if ( $k == 'id' ) {
				$rid = $v;
			}
			if ( $k == 'url' ) {
				$u = true;
			}
			$ndef[ $k ] = $v;
		}
		$path = SPLoader::path( "etc.repos.{$rid}.repository", 'front', true, 'xml' );
		$file = SPFactory::Instance( 'base.fs.file', $path );
		$xdef = SPFactory::Instance( 'types.array' );
		$file->content( $xdef->toXML( $ndef, 'repository' ) );
		$file->save();
	}

	public function getDef()
	{
		if ( empty( $this->_repoDefArr ) ) {
			$def = SPFactory::Instance( 'types.array' );
			$this->_repoDefArr = $def->fromXML( $this->definition, 'repository' );
		}
		return $this->_repoDefArr;
	}

	public function get( $attr, $default = null )
	{
		return $this->xGetString( $attr );
	}

	public function connect()
	{
		if ( ( $this->definition instanceof DOMDocument ) && $this->xGetString( 'url' ) ) {
			$connection = SPFactory::Instance( 'services.remote' );
			$ssl = $connection->certificate( $this->xGetString( 'url' ) );
			if ( isset( $ssl[ 'err' ] ) ) {
				throw new SPException( $ssl[ 'msg' ] );
			}
			if ( $ssl[ 'serialNumber' ] != $this->xGetString( 'certificate/serialnumber' ) ) {
				throw new SPException(
					SPLang::e(
						'SSL validation error: stored serial number is %s but the serial number for the repository at %s has the number %s.',
						$this->xGetString( 'certificate/serialnumber' ),
						$this->xGetString( 'url' ),
						$ssl[ 'serialNumber' ]
					)
				);
			}
			// for some reason on some servers the hash is being indeed modified
			// although it has been correctly transferred
			// it seems that it is depend on the protocol used (TSL/SSL)
//			if ( $ssl[ 'hash' ] != $this->xGetString( 'certificate/hash' ) ) {
//				throw new SPException(
//					SPLang::e(
//						'SSL validation error: stored hash does not accords the hash for the repository at %s. %s != %s',
//						$this->xGetString( 'url' ), $ssl[ 'hash' ], $this->xGetString( 'certificate/hash' )
//					)
//				);
//			}
			if ( $ssl[ 'validTo' ] < time() ) {
				throw new SPException(
					SPLang::e(
						'SSL validation error: SSL certificate for %s is expired.',
						$this->xGetString( 'url' )
					)
				);
			}
			$this->_server = SPFactory::Instance( 'services.soap', null, [ 'location' => $this->xGetString( 'url' ) ] );
		}
		else {
			throw new SPException( SPLang::e( 'No repository definition file %s or the definition is invalid.', $this->xmlFile ) );
		}
	}

	public function __call( $fn, $args )
	{
		$return = [ 'error' => 500 ];
		array_unshift( $args, Sobi::Lang( false ) );
		array_unshift( $args, Sobi::Cfg( 'live_site' ) );
		if ( $this->_server instanceof SPSoapClient ) {
			try {
				$return = $this->_server->__soapCall( $fn, $args );
			} catch ( SoapFault $x ) {
				throw new SPException( $x->getMessage() );
			}
			/* what the hell ???!!!!!*/
			if ( $return instanceof SoapFault ) {
				throw new SPException( $return->getMessage() );
			}
		}
		return $return;
	}
}
