<?php
/**
 * @version: $Id: exception.php 1716 2011-07-21 13:15:28Z Radek Suski $
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
 * $Date: 2011-07-21 15:15:28 +0200 (Thu, 21 Jul 2011) $
 * $Revision: 1716 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/exception.php $
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
	 * @param int $errno
	 * @param int $errcode
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 * @param string $errsection
	 * @param string $errcontext
	 */
	public static function storeError( $errno, $errcode, $errstr, $errfile, $errline, $errsection, $errcontext, $backtrace = null )
	{
		if ( !( self::$_cs ) && ( self::$_trigger && $errno < self::$_trigger ) ) {
			self::$_cs = true;
			throw new SPException( $errstr );
			return false;
		}
		SPLoader::loadClass( 'base.factory' );
		SPLoader::loadClass( 'base.database' );
		SPLoader::loadClass( 'cms.base.database' );
		$uid = 0;
//		unset( $backtrace[ 2 ] );
//		unset( $backtrace[ 1 ] );
//		unset( $backtrace[ 0 ] );

		$errcontext = serialize( $errcontext );
		$backtrace = serialize( $backtrace );
		if ( class_exists( 'SPUser' ) ) {
			$uid = SPUser::getCurrent()->get( 'id' );
		}
		$db =& SPDb::getInstance();
		$date = $db->now();
		$ip = isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : 'unknown';
		$reff = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? $_SERVER[ 'HTTP_REFERER' ] : 'unknown';
		$agent = isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ? $_SERVER[ 'HTTP_USER_AGENT' ] : 'unknown';
		$uri = isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : 'unknown';

		$errstr = $db->getEscaped( $errstr );
		$errsection = $db->getEscaped( $errsection );
		$errcontext = $db->getEscaped( base64_encode( gzcompress( $errcontext ) ) );
		if ( strlen( $errcontext ) > 15000 ) {
			$errcontext = 'Stack to large - skipping';
		}
		$backtrace = $db->getEscaped( base64_encode( gzcompress( $backtrace ) ) );
		$reff = $db->getEscaped( $reff );
		$agent = $db->getEscaped( $agent );
		$uri = $db->getEscaped( $uri );
		$errno = ( int )$errno;
		$errcode = ( int )$errcode;
		$errline = ( int )$errline;
//		$is = ini_set( 'display_errors', 0 );
//		@file_put_contents( SOBI_PATH.DS.'var'.DS.'log'.DS.'error.log', strip_tags( stripslashes( "\n=========\n[ {$date} ][ {$errsection}:{$errno} ][ {$errcode} ]\n{$errstr}\nIn: {$errfile}:{$errline}" ) ), SPC::FS_APP );
//		ini_set( 'display_errors', $is );
		try {
			$db->exec( "INSERT INTO spdb_errors VALUES ( NULL, '{$date}', '{$errno}', '{$errcode}', '{$errstr}', '{$errfile}', '{$errline}', '{$errsection}', '{$uid}', '{$ip}', '{$reff}', '{$agent}', '{$uri}', '{$errcontext}', '{$backtrace}' );" );
		} catch ( SPException $x ) {
			SPLoader::loadClass( 'base.mainframe' );
			SPLoader::loadClass( 'cms.base.mainframe' );
			SPMainFrame::runAway( 'Fatal error while inserting error message. ' . $x->getMessage(), 500 );
		}
		self::$_cs = false;
	}

	/**
	 * This function cacth errors and throws an exception instead
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
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 * @param string $errcontext
	 * @return bool
	 */
	function SPExceptionHandler( $errno, $errstr, $errfile, $errline, $errcontext )
	{
		if ( !( strstr( $errfile, 'sobipro' ) ) ) {
			return false;
		}
		static $cs = false;
		if ( !class_exists( 'SPLoader' ) ) {
			require_once( SOBI_PATH . DS . 'lib' . DS . 'base' . DS . 'fs' . DS . 'loader.php' );
		}
		if ( ini_get( 'error_reporting' ) < $errno ) {
			return false;
		}
		$err = explode( '|', $errstr );
		$b = null;
		if ( class_exists( 'SPConfig' ) ) {
			$b = SPConfig::getBacktrace();
		}
		if ( isset( $err[ 0 ] ) && $err[ 0 ] == 'sobipro' ) {
			if ( $cs ) {
				echo '<h1>Error handler: Violation of critical section. Possible infinite loop. Error reporting temporary disabled.</h1>';
				return false;
			}
			$cs = true;
			$section = $err[ 1 ];
			$errMsg = $err[ 2 ];
			$retCode = $err[ 3 ];
			$addMsg = isset( $err[ 4 ] ) && $err[ 4 ] ? $err[ 4 ] : null;
			$errStr = $errMsg . '. ' . $addMsg;
			SPException::storeError( $errno, $retCode, $errStr, $errfile, $errline, $section, $errcontext, $b );
			if ( $retCode ) {
				SPLoader::loadClass( 'base.mainframe' );
				SPLoader::loadClass( 'cms.base.mainframe' );
				SPMainFrame::runAway( $errMsg, $retCode, $errMsg . '. ' . $b );
			}
			$cs = false;
		}
		else {
			/* ignore strict errors which are not caused by Sobi*/
			if ( $errno == 2048 ) {
				return false;
			}
			/* stupid errors we already handle
			 * and there is no other possibility to catch it
			 * before it happens
			 */
			if ( strstr( $errstr, 'gzinflate' ) ) {
				return false;
			}
			if ( strstr( $errstr, 'compress' ) ) {
				return false;
			}
			if ( strstr( $errstr, 'domdocument.loadxml' ) ) {
				return false;
			}
			/* output of errors / call stack causes sometimes it - it's not really important */
			if ( strstr( $errstr, 'Property access is not allowed yet' ) ) {
				return false;
			}
			if ( strstr( $errfile, 'sobi' ) ) {
				SPException::storeError( $errno, 0, $errstr, $errfile, $errline, 'PHP', $errcontext, $b );
			}
		}
		return false;
	}
}
