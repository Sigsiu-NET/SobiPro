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
SPLoader::loadClass( 'cms.base.fs' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:02:55 PM
 */
class SPFile
{
	/**
	 * file path
	 *
	 * @var string
	 */
	protected $_filename = null;
	/**
	 * @var string
	 */
	protected $_content = null;
	/**
	 * @var string
	 */
	protected $_pathinfo = null;

	/**
	 * @param string $filename
	 */
	public function __construct( $filename = null )
	{
		$this->_filename = $filename;
		if ( $this->_filename ) {
			$this->_pathinfo = pathinfo( $this->_filename );
		}
	}

	/**
	 * @return array
	 */
	public function getPathInfo()
	{
		return $this->_pathinfo;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return SPFs::fixPath( $this->_filename );
	}

	/**
	 * @return string - full path to current file
	 */
	public function getPathname()
	{
		return $this->getName();
	}

	/**
	 * @return string - name of the current file
	 */
	public function getFileName()
	{
		return $this->_pathinfo[ 'basename' ];
	}

	/**
	 * @return bool
	 */
	public function isDot()
	{
		return in_array( $this->getFileName(), array( '.', '..' ) );
	}

	/**
	 * Check if file is a directory
	 * @return bool
	 */
	public function isDir()
	{
		return is_dir( $this->_filename );
	}

	/**
	 * Check if file is file
	 * @return bool
	 */
	public function isFile()
	{
		return is_file( $this->_filename );
	}

	/**
	 * @param string $mode
	 * @return bool
	 */
	public function chmod( $mode )
	{
		return SPFs::chmod( $this->_filename, $mode );
	}

	/**
	 * Copy file
	 * @param string $target - path
	 * @return bool
	 */
	public function copy( $target )
	{
		return SPFs::copy( $this->_filename, $target );
	}

	/**
	 * Get file from the request and upload to the given path
	 * @param string $name - file name from the request
	 * @param string $destination - destination path
	 * @throws SPException
	 * @return bool
	 */
	public function upload( $name, $destination )
	{
		$destination = Sobi::FixPath( $destination );
		if ( SPFs::upload( $name, $destination ) ) {
			$this->_filename = $destination;
			return $this->_filename;
		}
		else {
			throw new SPException( SPLang::e( 'CANNOT_UPLOAD_FILE_TO', str_replace( SOBI_ROOT, null, $destination ) ) );
		}
	}

	/**
	 * Deletes a file
	 * @return bool
	 */
	public function delete()
	{
		return SPFs::delete( $this->_filename );
	}

	/**
	 * Moves file to new location
	 * @param string $target - destination path
	 * @return bool
	 */
	public function move( $target )
	{
		$f = explode( DS, $target );
		$path = str_replace( $f[ count( $f ) - 1 ], null, $target );
		if ( !( SPFs::exists( $path ) ) ) {
			SPFs::mkdir( $path );
		}
		if ( SPFs::move( $this->_filename, $target ) ) {
			$this->_filename = $target;
		}
		return $this;
	}

	/**
	 * Reads file and returns the content of it
	 * @return string
	 */
	public function & read()
	{
		$this->_content = SPFs::read( $this->_filename );
		return $this->_content;
	}

	/**
	 * Set file content
	 * @param $content - string
	 * @return void
	 */
	public function content( $content )
	{
		$this->_content = $content;
		return $this;
	}

	/**
	 * Writes the content to the file
	 * @return bool
	 */
	public function write()
	{
		return SPFs::write( $this->_filename, $this->_content );
	}

	/**
	 * alias for @see SPFile#write()
	 * @return bool
	 */
	public function save()
	{
		return $this->write();
	}

	/**
	 * Saves file as a copy
	 * @param string $path
	 * @return bool
	 */
	public function saveAs( $path )
	{
		return SPFs::write( $path, $this->_content );
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function filename()
	{
		return $this->_filename;
	}

	/**
	 * @param $filename
	 * @return void
	 */
	public function setFile( $filename )
	{
		$this->_filename = $filename;
		$this->_pathinfo = pathinfo( $this->_filename );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function rename( $name )
	{
		$filename = SPFs::getFileName( $this->_filename );
		$new = str_replace( $filename, $name, $this->_filename );
		if ( SPFs::move( $this->_filename, $new ) ) {
			$this->_filename = $new;
			return true;
		}
		else {
			return false;
		}
	}
}
