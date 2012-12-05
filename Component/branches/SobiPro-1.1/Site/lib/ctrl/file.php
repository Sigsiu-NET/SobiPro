<?php
/**
 * @version: $Id: txt.php 1187 2011-04-15 07:47:13Z Radek Suski $
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
 * $Date: 2011-04-15 09:47:13 +0200 (Fri, 15 Apr 2011) $
 * $Revision: 1187 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/txt.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created Mon, Dec 3, 2012 13:00:53
 */
class SPFileUploader extends SPController
{
	/**
	 */
	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'upload':
				$this->upload();
				break;
		}

	}

	protected function upload()
	{
		$ident = SPRequest::cmd( 'ident', null, 'post' );
		$data = SPRequest::file( $ident, 'tmp_name' );
		$secret = md5( Sobi::Cfg( 'secret' ) );
		if ( $data ) {
			$properties = SPRequest::file( $ident );
			$fileName = md5( SPRequest::file( $ident, 'name' ) . time() . $secret );
			$path = SPLoader::dirPath( "tmp.files.{$secret}", 'front', false ) . '/' . $fileName;
			/** @var $file SPFile */
			$file = SPFactory::Instance( 'base.fs.file' );
			if ( !( $file->upload( $data, $path ) ) ) {
				$this->message( array( 'type' => 'error', 'text' => SPLang::e( 'CANNOT_UPLOAD_FILE' ), 'id' => '' ) );
			}
			$path = $file->getPathname();
			$type = $this->check( $path );
			$properties[ 'tmp_name' ] = $path;
			SPFs::write( $path . '.txt', SPConfig::serialize( $properties ) );
			$response = array(
				'type' => 'success',
				'text' => Sobi::Txt( 'FILE_UPLOADED', $properties[ 'name' ], $type ),
				'id' => 'file://' . $fileName
			);
		}
		else {
			$response = array(
				'type' => 'error',
				'text' => SPLang::e( 'CANNOT_UPLOAD_FILE_NO_DATA' ),
				'id' => ''
			);
		}
		$field = SPRequest::cmd( 'field', null );
		$this->message( $response );
	}

	protected function check( $file )
	{
		$allowed = SPLoader::loadIniFile( 'etc.download' );
		$mType = SPFactory::Instance( 'services.fileinfo', $file )->mimeType();
		if ( strlen( $mType ) && !( in_array( $mType, $allowed ) ) ) {
			SPFs::delete( $file );
			$this->message( array( 'type' => 'error', 'text' => SPLang::e( 'FILE_WRONG_TYPE', $mType ), 'id' => '' ) );
		}
		return $mType;
	}

	protected function message( $response )
	{
		SPFactory::mainframe()->cleanBuffer();
		echo json_encode( $response );
		exit;

	}
}
