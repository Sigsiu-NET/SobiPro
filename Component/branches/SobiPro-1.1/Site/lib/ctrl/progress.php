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
	public function __construct() {}
	private $id = null;
	private $msg = null;

	/**
	 */
	public function execute()
	{
		SPFactory::mainframe()->cleanBuffer();
		header( 'Content-type: application/json' );
		if( SPFs::exists( SPLoader::path( 'tmp.'.SPRequest::cmd( 'sppbid', null, 'cookie' ), 'front', false, 'tmp' ) ) ) {
			echo SPFs::read( SPLoader::path( 'tmp.'.SPRequest::cmd( 'sppbid', null, 'cookie' ), 'front', false, 'tmp' ) );
		}
		exit;
	}

	public function message( $message )
	{
		$progress = "<div class=\"SPPbarMsgbox\" style=\"text-align:left;\">{$message}</div>";
		SPFs::write( SPLoader::path( 'tmp.'.SPRequest::cmd( 'sppbid', null, 'cookie' ), 'front', false, 'tmp' ), json_encode( array( 'progress' => 0, 'msg' => $progress, 'interval' => 0 ) ) );
	}

	public function error( $message )
	{
		$bgimg = Sobi::FixPath( Sobi::Cfg( 'img_folder_live' ).'/progress/bg.gif' );
		$progress = "<div class=\"SPPbarMsgbox\" style=\"color:red;\">{$message}</div>";
		SPFs::write( SPLoader::path( 'tmp.'.SPRequest::cmd( 'sppbid', null, 'cookie' ), 'front', false, 'tmp' ), json_encode( array( 'progress' => 0, 'msg' => $progress ) ) );
		sleep( 5 );
		SPFs::write( SPLoader::path( 'tmp.'.SPRequest::cmd( 'sppbid', null, 'cookie' ), 'front', false, 'tmp' ), json_encode( array( 'progress' => 100, 'msg' => $progress,  'interval' => 0 ) ) );
	}

	public function progress( $percent, $message = null, $interval = 1000 )
	{
		$this->id = SPRequest::cmd( 'sppbid', null, 'cookie' );
		$percent = ceil( $percent );
		$this->msg = ( strlen( $message ) ) ? $message : $this->msg;
		$bgimg = Sobi::FixPath( Sobi::Cfg( 'img_folder_live' ).'/progress/bg.gif' );
		$stimg = Sobi::FixPath( Sobi::Cfg( 'img_folder_live' ).'/progress/single.gif' );
		$progress  = null;
		for( $i = 0; $i < $percent; $i++ ) {
			if( $i > 100 ) {
				break;
			}
			$progress .= "<img src=\"{$stimg}\" width=\"5\" height=\"15\">";
		}
		$progress = "
			<div class=\"SPPbarMsgbox\">{$this->msg}</div>
			<div class=\"SPPbarProgressbar\" style=\"background-image: url({$bgimg});\">
				<div class=\"SPPbarPercentbox\">{$progress}</div>
			</div>";
		SPFs::write( SPLoader::path( 'tmp.'.$this->id, 'front', false, 'tmp' ), json_encode( array( 'progress' => $percent, 'msg' => $progress,  'interval' => $interval ) ) );
	}
}
