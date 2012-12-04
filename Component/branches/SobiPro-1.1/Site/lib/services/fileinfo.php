<?php
/**
 * @version: $Id: fileinfo.php 1367 2011-05-18 09:50:00Z Radek Suski $
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
 * $Date: 2011-05-18 11:50:00 +0200 (Wed, 18 May 2011) $
 * $Revision: 1367 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/services/fileinfo.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 4-Nov-2010 19:41:40
 */
class SPFileInfo
{
	/**
	 * @var string - file path
	 */
	private $_path = null;
	/**
	 * @var string
	 */
	private $_mime = null;
	/**
	 * @var string
	 */
	private $_charset = null;
	/**
	 * @var array
	 */
	private static $_exts = array();

	/**
	 * @param
	 */
	public function __construct( $file )
	{
		$this->_path = $file;
	}

	/**
	 * Returns the mime type of a given file
	 * @return string
	 */
	public function mimeType()
	{
		if( !( strlen( $this->_mime ) ) ) {
			if( !( $this->mimeFromFinfo() ) || !( strlen( $this->_mime ) ) ) {
				if( !( $this->mimeFromShell() ) || !( strlen( $this->_mime ) ) ) {
					$this->mimeFromExt();
					Sobi::Error( 'FileInfo', SPLang::e( 'There is no reliable method to determine the right file type. Fallback to file extension' ), SPC::WARNING, 0 );
				}
			}
		}
		return $this->_mime;
	}

	/**
	 * Returns charset of a given file
	 * @return string
	 */
	public function charset()
	{
		if( !( strlen( $this->_mime ) ) ) {
			$this->mimeType();
		}
		return $this->_charset;
	}

	private function mimeFromShell()
	{
		if( !( strstr( strtolower( PHP_OS ), 'win' ) ) ) {
			if( ( $this->_mime = exec( 'file -bi '.escapeshellarg( $this->_path ) ) ) && strlen( $this->_mime ) ) {
				/*
				 * it's a stupid exception for MS docs files
				 * The linux command "file -bi" returns then this:
				 * application/msword application/msword
				 * which sucks totally :(
				 */
				if( strstr( $this->_mime, ' ' ) && !( strstr( $this->_mime, ';' ) ) ) {
					$this->_mime = explode( ' ', $this->_mime );
					if( trim( $this->_mime[ 0 ] ) == ( $this->_mime[ 1 ] ) ) {
						$this->_mime = $this->_mime[ 0 ];
					}
				}
				$this->parseMime();
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	private function mimeFromFinfo()
	{
		if( function_exists( 'finfo_file' ) ) {
			$finfo = new finfo( FILEINFO_MIME );
			$this->_mime = $finfo->file( $this->_path );
			$this->parseMime();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 *
	 */
	private function parseMime()
	{
		$this->_mime = preg_split( '/[;=]/', $this->_mime );
		$this->_charset = isset( $this->_mime[ 2 ] ) ? $this->_mime[ 2 ] : null;
		$this->_mime = $this->_mime[ 0 ];
	}

	/**
	 *
	 */
	private function mimeFromExt()
	{
		$ext = SPFs::getExt(  $this->_path );
		if( !( count( self::$_exts ) ) ) {
			self::$_exts = SPLoader::loadIniFile( 'etc.mime', false );
		}
		if( !( isset( self::$_exts[ $ext ] ) ) ) {
			Sobi::Error( 'FileInfo', SPLang::e( 'Cannot determine the right file type from extension' ), SPC::WARNING, 0 );
		}
		else {
			$this->_mime = self::$_exts[ $ext ];
		}
	}
}
