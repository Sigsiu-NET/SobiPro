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
SPLoader::loadController( 'config', true );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 06-Aug-2010 15:38:15
 */
class SPError extends SPConfigAdmCtrl
{
	/**
	 * @var string
	 */
	protected $_defTask = 'list';

	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'list':
				$this->screen();
				Sobi::ReturnPoint();
				break;
			case 'purge':
				$this->purge();
				break;
			case 'download':
				$this->download();
				break;
			case 'details':
				$this->details();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !parent::execute() ) {
					Sobi::Error( 'error_ctrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	private function download()
	{
		$Error = new DOMDocument( '1.0', 'utf-8' );
		$Error->formatOutput = true;
		$Root = $Error->createElement( 'errorLog' );
		$Date = $Error->createAttribute( 'createdAt' );
		$Date->appendChild( $Error->createTextNode( date( DATE_RFC822 ) ) );
		$Root->appendChild( $Date );
		$Site = $Error->createAttribute( 'site' );
		$Site->appendChild( $Error->createTextNode( Sobi::Cfg( 'live_site' ) ) );
		$Root->appendChild( $Site );
		$Error->appendChild( $Root );
		$levels = $this->levels();
		try {
			$errors = SPFactory::db()->select( '*', 'spdb_errors', null, 'eid.desc' )->loadAssocList();
		} catch ( SPException $x ) {
		}
		$c = 0;
		if ( count( $errors ) ) {
			foreach ( $errors as $i => $err ) {
				$c++;
				if ( $c > Sobi::Cfg( 'err_log.limit', 50 ) ) {
					break;
				}
				$err[ 'errNum' ] = $levels[ $err[ 'errNum' ] ];
				$Err = $Error->createElement( 'error' );

				$Date = $Error->createAttribute( 'date' );
				$Date->appendChild( $Error->createTextNode( $err[ 'date' ] ) );
				$Err->appendChild( $Date );

				$Level = $Error->createAttribute( 'level' );
				$Level->appendChild( $Error->createTextNode( $err[ 'errNum' ] ) );
				$Err->appendChild( $Level );

				$Code = $Error->createAttribute( 'returnCode' );
				$Code->appendChild( $Error->createTextNode( $err[ 'errCode' ] ) );
				$Err->appendChild( $Code );

				$Section = $Error->createAttribute( 'section' );
				$Section->appendChild( $Error->createTextNode( $err[ 'errSect' ] ) );
				$Err->appendChild( $Section );

				$err[ 'errBacktrace' ] = unserialize( gzuncompress( base64_decode( $err[ 'errBacktrace' ] ) ) );
				$err[ 'errBacktrace' ] = str_replace( SOBI_ROOT, null, $err[ 'errBacktrace' ] );
				$err[ 'errMsg' ] = str_replace( SOBI_ROOT, null, $err[ 'errMsg' ] );

				$err[ 'errCont' ] = unserialize( gzuncompress( base64_decode( $err[ 'errCont' ] ) ) );
				$err[ 'errCont' ] = str_replace( SOBI_ROOT, null, $err[ 'errCont' ] );

				$ErrMsg = $Error->createElement( 'message', $err[ 'errMsg' ] );
				$Err->appendChild( $ErrMsg );

				$ErrMsg = $Error->createElement( 'file', $err[ 'errFile' ] . ':' . $err[ 'errLine' ] );
				$Err->appendChild( $ErrMsg );

				$ErrUser = $Error->createElement( 'user' );
				$Uid = $Error->createAttribute( 'uid' );
				$Uid->appendChild( $Error->createTextNode( $err[ 'errUid' ] ) );
				$ErrUser->appendChild( $Uid );

				$UsrIp = $Error->createElement( 'ip', $err[ 'errIp' ] );
				$ErrUser->appendChild( $UsrIp );

				$UsrA = $Error->createElement( 'userAgent', $err[ 'errUa' ] );
				$ErrUser->appendChild( $UsrA );

				$UsrReq = $Error->createElement( 'requestedUri', htmlentities( $err[ 'errReq' ] ) );
				$ErrUser->appendChild( $UsrReq );

				$UsrRef = $Error->createElement( 'referrerUri', str_replace( Sobi::Cfg( 'live_site' ), null, htmlentities( $err[ 'errRef' ] ) ) );
				$ErrUser->appendChild( $UsrRef );

				$Err->appendChild( $ErrUser );

				$ErrStack = $Error->createElement( 'callStack' );
				$ErrStack->appendChild( $Error->createCDATASection( "\n" . stripslashes( var_export( $err[ 'errCont' ], true ) ) . "\n" ) );
				$Err->appendChild( $ErrStack );

				$ErrTrace = $Error->createElement( 'callTrace' );
				$ErrTrace->appendChild( $Error->createCDATASection( "\n" . stripslashes( var_export( $err[ 'errBacktrace' ], true ) ) . "\n" ) );
				$Err->appendChild( $ErrTrace );

				$Root->appendChild( $Err );
			}
		}
		$file = SPLoader::path( 'var.log.errors', 'front', false, 'xml' );
		SPFs::write( $file, $Error->saveXML() );
		$fp = SPFs::read( $file );
		SPFactory::mainframe()->cleanBuffer();
		header( "Content-type: application/xml" );
		header( 'Content-Disposition: attachment; filename=error.xml' );
		echo $fp;
		flush();
		exit;
	}

	private function levels()
	{
		$levels = get_defined_constants();
		// no response under PHP 5.3 No idea why
		//$levels = get_defined_constants( true );
		//$levels = isset( $levels[ 'Core' ] ) ? $levels[ 'Core' ] : $levels[ 'internal' ];
		foreach ( $levels as $level => $v ) {
			if ( !( preg_match( '/^E_/', $level ) ) ) {
				unset( $levels[ $level ] );
			}
		}
		return array_flip( $levels );
	}

	private function details()
	{
		$id = SPRequest::int( 'eid' );
		try {
			$err = SPFactory::db()
					->select( '*', 'spdb_errors', [ 'eid' => $id ] )
					->loadObject();
		} catch ( SPException $x ) {
		}
		$err->errCont = unserialize( gzuncompress( base64_decode( $err->errCont ) ) );
		$err->errBacktrace = unserialize( gzuncompress( base64_decode( $err->errBacktrace ) ) );
		$l = $this->levels();
		/** @var $view SPAdmView */
		$menu = $this->createMenu( 'error' );
		SPFactory::View( 'error', true )
				->assign( $this->_task, 'task' )
				->assign( $menu, 'menu' )
				->assign( $l, 'levels' )
				->assign( $err, 'error' )
				->display();
	}

	private function purge()
	{
		try {
			SPFactory::db()->truncate( 'spdb_errors' );
		} catch ( SPException $x ) {
			$this->response( Sobi::Url( 'error' ), Sobi::Txt( 'ERR.ERROR_LOG_NOT_DELETED', [ 'error' => $x->getMessage() ] ), false, SPC::ERROR_MSG );
		}
		if ( SPFs::exists( SOBI_PATH . '/var/log/error.log' ) ) {
			SPFs::delete( SOBI_PATH . '/var/log/error.log' );
		}
		$this->response( Sobi::Url( 'error' ), Sobi::Txt( 'ERR.ERROR_LOG_DELETED' ), false, SPC::SUCCESS_MSG );
	}

	private function screen()
	{
		$eLimit = Sobi::GetUserState( 'adm.errors.limit', 'elimit', Sobi::Cfg( 'adm_list.entries_limit', 25 ) );
		$eLimStart = SPRequest::int( 'errSite', 1 );
		$LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;
		$eCount = 0;
		try {
			$eCount = SPFactory::db()
					->select( 'COUNT(eid)', 'spdb_errors' )
					->loadResult();
		} catch ( SPException $x ) {
		}
		if ( $eLimit == -1 ) {
			$eLimit = $eCount;
		}
		try {
			$errors = SPFactory::db()
					->select( [ 'eid', 'date', 'errNum', 'errCode', 'errFile', 'errLine', 'errMsg', 'errUid', 'errSect', 'errReq' ], 'spdb_errors', null, 'eid.desc', $eLimit, $LimStart )
					->loadAssocList();
		} catch ( SPException $x ) {
		}
		$l = $this->levels();
		$menu = $this->createMenu( 'error' );
		/** @var $view SPAdmView */
		SPFactory::View( 'error', true )
				->assign( $this->_task, 'task' )
				->assign( $menu, 'menu' )
				->assign( $errors, 'errors' )
				->assign( $l, 'levels' )
				->assign( $eLimit, 'errors-limit' )
				->assign( $eCount, 'errors-count' )
				->assign( $eLimStart, 'errors-site' )
				->display();
	}
}
