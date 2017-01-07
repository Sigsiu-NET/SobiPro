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
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		if ( SPFs::exists( $this->file ) ) {
			echo SPFs::read( $this->file );
		}
		else {
			echo json_encode( [ 'progress' => 0, 'message' => '', 'interval' => 100, 'type' => '' ] );
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
		$out = json_encode( [ 'progress' => $progress, 'message' => $message, 'interval' => $interval, 'type' => $type, 'typeText' => $typeText ] );
		SPFs::write( $this->file, $out );
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
