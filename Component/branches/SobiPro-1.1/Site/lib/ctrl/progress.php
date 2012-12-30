<?php
/**
 * @version: $Id: progress.php 2193 2012-01-28 12:34:01Z Radek Suski $
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
 * $Date: 2012-01-28 13:34:01 +0100 (Sat, 28 Jan 2012) $
 * $Revision: 2193 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/progress.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 12-Jul-2010 11:11:27
 */
class SPProgressCtrl extends SPController
{
	private $file = null;
	private $message = null;
	private $type = null;
	private $progress = 0;
	private $interval = 0;

	public function __construct()
	{
		$ident = SPRequest::cmd( 'session' ) ? SPRequest::cmd( 'ProgressMsg' . SPRequest::cmd( 'session' ), null, 'cookie' ) : SPRequest::cmd( 'ProgressMsg', null, 'cookie' );
		$this->file = SPLoader::path( 'tmp.' . $ident, 'front', false, 'tmp' );
		if ( SPFs::exists( $this->file ) ) {
			$content = json_decode( SPFs::read( $this->file ), true );
			$this->message = $content[ 'message' ];
			$this->type = $content[ 'type' ];
			$this->progress = $content[ 'progress' ];
			$this->interval = $content[ 'interval' ];
		}
	}

	/**
	 */
	public function execute()
	{
		SPFactory::mainframe()->cleanBuffer();
		header( 'Content-type: application/json' );
		if ( SPFs::exists( $this->file ) ) {
			echo SPFs::read( $this->file );
		}
		else {
			echo json_encode( array( 'progress' => 0, 'message' => '', 'interval' => 100, 'type' => '' ) );
		}
		exit;
	}

	private function status( $message, $progress = 0, $interval = 0, $type = SPC::INFO_MSG )
	{
		if ( !( strlen( $message ) ) ) {
			$message = Sobi::Txt( 'PROGRESS_WORKING' );
		}
		$progress = $progress ? $progress : $this->progress;
		$interval = $interval ? $interval : $this->interval;
		$type = $type ? $type : $this->type;
		$this->progress = $progress;
		$this->message = $message;
		$this->interval = $interval;
		$this->type = $type;
		$typeText = Sobi::Txt( 'STATUS_' . $type );
		SPFs::write( $this->file, json_encode( array( 'progress' => $progress, 'message' => $message, 'interval' => $interval, 'type' => $type, 'typeText' => $typeText ) ) );
	}

	public function message( $message, $type = SPC::INFO_MSG )
	{
		$this->status( $message, 0, 0, $type );
	}

	public function error( $message )
	{
		$this->status( $message, 0, 0, SPC::ERROR_MSG );
	}

	public function progress( $percent, $message = null, $type = SPC::INFO_MSG, $interval = 1000 )
	{
		$this->id = SPRequest::cmd( 'ProgressMsg', null, 'cookie' );
		$percent = ceil( $percent );
		$this->msg = ( strlen( $message ) ) ? $message : $this->msg;
		$this->status( $message, $percent, $interval, $type );
	}
}
