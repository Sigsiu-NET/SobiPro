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

/**
 * @author Radek Suski
 * @version 1.0
 * @created 12-Jan-2009 10:50:52 AM
 */
class SPException extends Exception
{
	private static $_trigger = 0;
	private static $_cs = false;
	protected $data = array();

	public function setData( $data )
	{
		$this->data = $data;
	}

	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param int $number
	 * @param int $errCode
	 * @param string $errStr
	 * @param string $errFile
	 * @param int $errLine
	 * @param string $errSection
	 * @param string $errContext
	 * @param null $backtrace
	 * @throws SPException
	 * @return bool
	 */
	public static function storeError( $number, $errCode, $errStr, $errFile, $errLine, $errSection, $errContext, $backtrace = null )
	{
		if ( !( self::$_cs ) && ( self::$_trigger && $number < self::$_trigger ) ) {
			self::$_cs = true;
			throw new SPException( $errStr );
			return false;
		}
		SPLoader::loadClass( 'base.factory' );
		SPLoader::loadClass( 'base.database' );
		SPLoader::loadClass( 'cms.base.database' );
		$uid = 0;
//		unset( $backtrace[ 2 ] );
//		unset( $backtrace[ 1 ] );
//		unset( $backtrace[ 0 ] );

		$errContext = serialize( $errContext );
		$backtrace = serialize( $backtrace );
		if ( class_exists( 'SPUser' ) ) {
			$uid = SPUser::getCurrent()->get( 'id' );
		}
		$db = SPDb::getInstance();
		$date = $db->now();
		$ip = isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : 'unknown';
		$reff = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? $_SERVER[ 'HTTP_REFERER' ] : 'unknown';
		$agent = isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ? $_SERVER[ 'HTTP_USER_AGENT' ] : 'unknown';
		$uri = isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : 'unknown';

		$errStr = $db->escape( $errStr );
		$errSection = $db->escape( $errSection );
		$errContext = $db->escape( base64_encode( gzcompress( $errContext ) ) );
		if ( strlen( $errContext ) > 15000 ) {
			$errContext = 'Stack to large - skipping';
		}
		$backtrace = $db->escape( base64_encode( gzcompress( $backtrace ) ) );
		$reff = $db->escape( $reff );
		$agent = $db->escape( $agent );
		$uri = $db->escape( $uri );
		$number = ( int )$number;
		$errCode = ( int )$errCode;
		$errLine = ( int )$errLine;
//		$is = ini_set( 'display_errors', 0 );
//		@file_put_contents( SOBI_PATH.DS.'var'.DS.'log'.DS.'error.log', strip_tags( stripslashes( "\n=========\n[ {$date} ][ {$errsection}:{$errno} ][ {$errcode} ]\n{$errstr}\nIn: {$errfile}:{$errline}" ) ), SPC::FS_APP );
//		ini_set( 'display_errors', $is );
		try {
			$db->exec( "INSERT INTO spdb_errors VALUES ( NULL, '{$date}', '{$number}', '{$errCode}', '{$errStr}', '{$errFile}', '{$errLine}', '{$errSection}', '{$uid}', '{$ip}', '{$reff}', '{$agent}', '{$uri}', '{$errContext}', '{$backtrace}' );" );
		} catch ( SPException $x ) {
			SPLoader::loadClass( 'base.mainframe' );
			SPLoader::loadClass( 'cms.base.mainframe' );
			SPFactory::mainframe()->runAway( 'Fatal error while inserting error message. ' . $x->getMessage(), 500 );
		}
		self::$_cs = false;
	}

	/**
	 * This function catch errors and throws an exception instead
	 * It is going to be used to handle errors from function which does not throws exceptions
	 * @param $type - type of the error to catch
	 */
	public static function catchErrors( $type = E_ALL )
	{
		self::$_trigger = $type;
	}
}

if ( !function_exists( 'SPExceptionHandler' ) ) {
	/**
	 *
	 * @param int $errNumber
	 * @param string $errString
	 * @param string $errFile
	 * @param int $errLine
	 * @param string $errContext
	 * @return bool
	 */
	function SPExceptionHandler( $errNumber, $errString, $errFile, $errLine, $errContext )
	{
		if ( $errNumber == E_STRICT && ( !( defined( 'SOBI_TESTS' ) ) || !( SOBI_TESTS ) ) ) {
			return true;
		}
		$error = null;
		if ( !( strstr( $errFile, 'sobipro' ) ) ) {
			return false;
		}
		static $cs = false;
		if ( $cs ) {
			echo '<h1>Error handler: Violation of critical section. Possible infinite loop. Error reporting temporary disabled. ' . $errString . '</h1>';
			return false;
		}
		if ( !class_exists( 'SPLoader' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once( SOBI_PATH . '/lib/base/fs/loader.php' );
		}
		if ( ini_get( 'error_reporting' ) < $errNumber ) {
			return false;
		}
		if ( strstr( $errString, 'json://' ) ) {
			$error = json_decode( str_replace( 'json://', null, $errString ), true );
		}
		$backTrace = null;
		if ( class_exists( 'SPConfig' ) ) {
			$backTrace = SPConfig::getBacktrace();
		}
		if ( $error ) {
			$retCode = $error[ 'code' ];
			$errString = $error[ 'message' ];
			$errFile = $error[ 'file' ];
			$errLine = $error[ 'line' ];
			$section = $error[ 'section' ];
			$errContext = $error[ 'content' ];
		}
		else {
			$retCode = 0;
			if ( !( strstr( $errFile, 'sobi' ) ) ) {
				return false;
			}
			/* stupid errors we already handle
			 * and there is no other possibility to catch it
			 * before it happens
			 */
			if ( strstr( $errString, 'gzinflate' ) ) {
				return false;
			}
			if ( strstr( $errString, 'compress' ) ) {
				return false;
			}
			if ( strstr( $errString, 'domdocument.loadxml' ) ) {
				return false;
			}
			/** This really sucks - why do I have the possibility to override a method when I cannot change its parameters :(
			 * A small design flaw - has to be changed later */
			if ( strstr( $errString, 'should be compatible with' ) ) {
				return false;
			}
			/* output of errors / call stack causes sometimes it - it's not really important */
			if ( strstr( $errString, 'Property access is not allowed yet' ) ) {
				return false;
			}
			$section = 'PHP';
		}
		$cs = true;
		SPException::storeError( $errNumber, $retCode, $errString, $errFile, $errLine, $section, $errContext, $backTrace );
		if ( $retCode ) {
			SPLoader::loadClass( 'base.mainframe' );
			SPLoader::loadClass( 'cms.base.mainframe' );
			SPFactory::mainframe()->runAway( $errString, $retCode, $backTrace );
		}
		$cs = false;
		/** do not display our internal errors because this is an array */
		if ( $error ) {
			return true;
		}
		return false;
	}
}
