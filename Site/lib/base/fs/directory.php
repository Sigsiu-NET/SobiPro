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

SPLoader::loadClass( 'base.fs.file' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:02:35 PM
 */
class SPDirectory extends SPFile
{
	/**
	 * @var SPDirectoryIterator
	 */
	private $_dirIterator = null;
	private $_files = array();
	private $_struct = array();

	 /**
     * @param string $string - part or full name of the file to search for
     * @param bool $exact - search for exact string or the file nam can contain this string
     * @param $recLevel - recursion level
     * @return array
     */
	public function searchFile( $string, $exact = true, $recLevel = 1 )
	{
		$this->iterator();
		$results = array();
		if( !( is_array( $string ) ) ) {
			$string = array( $string );
		}
		foreach ( $string as $search ) {
			$this->searchRecursive( $this->_dirIterator, $search, $exact, $recLevel, $results );
		}
		return $results;
	}
// @todo
//	public function scan()
//	{
//		foreach ( $this->_dirIterator as $child ) {
//			if( !( $child->isDot() ) ) {
//
//			}
//		}
//	}

	/**
	 * @return SPDirectoryIterator
	 */
	public function iterator()
	{
		if( !$this->_dirIterator ) {
			$this->_dirIterator =& SPFactory::Instance( 'base.fs.directory_iterator', $this->_filename );
		}
		return $this->_dirIterator;
	}

	/**
	 * Move files from directory to given path
	 * @param string $target - target path
	 * @return array
	 */
	public function moveFiles( $target )
	{
		$this->iterator();
		$log = array();
		foreach ( $this->_dirIterator as $child ) {
			if( !( $child->isDot() ) && !( SPFs::exists( Sobi::FixPath( $target.DS.$child->getFileName() ) ) ) ) {
				if( SPFs::move( $child->getPathname(), Sobi::FixPath( $target.DS.$child->getFileName() ) ) ) {
					$log[] = Sobi::FixPath( $target.DS.$child->getFileName() );
				}
			}
		}
		return $log;
	}

	/**
	 * Remove all files in directory
	 * @return bool
	 */
	public function deleteFiles()
	{
		$this->iterator();
//		$log = array();
		foreach ( $this->_dirIterator as $child ) {
			if( !( $child->isDot() ) ) {
				SPFs::delete( $child->getPathname() );
			}
		}
	}

	/**
	 * @param $dir
	 * @param $string
	 * @param $exact
	 * @param $recLevel
	 * @param $results
	 * @param $level
	 * @return array
	 */
	private function searchRecursive( $dir, $string, $exact, $recLevel, &$results, $level = 0 )
	{
		$level++;
		if( $level > $recLevel ) {
			return true;
		}
		$r = $dir->searchFile( $string, $exact );
		$results = array_merge( $results, $r );
		foreach ( $dir as $file ) {
			if( $file->isDir() && !( $file->isDot() ) ) {
				$this->searchRecursive( SPFactory::Instance( 'base.fs.directory_iterator', $file->getPathname() ), $string, $exact, $recLevel, $results, $level );
			}
		}
	}
}
