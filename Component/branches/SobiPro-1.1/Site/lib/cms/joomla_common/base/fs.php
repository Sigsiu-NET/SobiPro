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

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );

/**
 * Interface to Joomla! files system
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:02:55 PM
 * @todo !!!! verify - Joomla changed this class
 */
abstract class SPJoomlaFs
{
	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function exists( $file )
	{
		return file_exists( $file );
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function clean( $file, $safe = false )
	{
		$file = str_replace( DS, '/', $file );
		$file = preg_replace( '|([^:])(//)+([^/]*)|', '\1/\3', $file );
		$file = str_replace( '__BCKSL__', '\\', preg_replace( '|([^:])(\\\\)+([^\\\])|', "$1__BCKSL__$3", $file ) );
		$file = str_replace( '\\', '/', $file );
		if ( $safe ) {
			$file = Jfile::makeSafe( $file );
		}
		if ( !( strstr( $file, ':' ) ) ) {
			while ( strstr( $file, '//' ) ) {
				$file = str_replace( '//', '/', $file );
			}
		}
		return $file;
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function getExt( $file )
	{
		$ext = explode( ".", $file );
		return $ext[ count( $ext ) - 1 ];
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function getFileName( $file )
	{
		$ext = explode( DS, $file );
		return $ext[ count( $ext ) - 1 ];
	}

	/**
	 *     *
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	public static function copy( $source, $destination )
	{
		$destination = Sobi::FixPath( str_replace( '\\', '/', $destination ) );
		$path = explode( '/', str_replace( array( SOBI_ROOT, str_replace( '\\', '/', SOBI_ROOT ) ), null, $destination ) );
		$part = SOBI_ROOT;
		$i = count( $path );
		// yeah I know ... shame on me :(
		while ( !( @$path[ $i ] ) ) {
			unset( $path[ $i-- ] );
		}
		array_pop( $path );
		if ( !( is_string( $path ) ) && count( $path ) ) {
			foreach ( $path as $dir ) {
				$part .= "/{$dir}";
				if ( $dir && !( file_exists( $part ) ) ) {
					self::mkdir( $part );
				}
			}
		}
		if ( !( is_dir( $source ) ) ) {
			return Jfile::copy( self::clean( $source ), self::clean( $destination ) );
		}
		else {
			return Jfolder::copy( self::clean( $source ), self::clean( $destination ) );
		}
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function delete( $file )
	{
		if ( is_dir( $file ) ) {
			return Jfolder::delete( $file );
		}
		else {
			return Jfile::delete( $file );
		}
	}

	/**
	 *     *
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	public static function move( $source, $destination )
	{
		return Jfile::move( $source, $destination );
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function read( $file )
	{
		return file_get_contents( $file );
	}

	public static function fixPath( $path )
	{
		return str_replace( DS . DS, DS, str_replace( DS . DS, DS, str_replace( '\\', '/', $path ) ) );
	}

	/**
	 *     *
	 * @param string $file
	 * @param string $buffer
	 * @return bool
	 */
	public static function write( $file, &$buffer )
	{
		$return = Jfile::write( $file, $buffer );
		if ( $return === false ) {
			throw new SPException( SPLang::e( 'CANNOT_WRITE_TO_FILE_AT', $file ) );
			return false;
		}
		else {
			return $return;
		}
	}

	/**
	 * @param string $name
	 * @param string $destination
	 * @return bool
	 */
	public static function upload( $name, $destination )
	{
		if ( !( file_exists( dirname( $destination ) ) ) ) {
			self::mkdir( dirname( $destination ) );
		}
		/** Ajax uploader exception */
		if ( strstr( $name, SOBI_PATH ) ) {
			return self::move( $name, $destination );
		}
		return Jfile::upload( $name, $destination );
	}

	/**
	 * @param string $path
	 * @param string $hex
	 * @return bool
	 */
	public static function chmod( $path, $hex )
	{
		return Jfile::chmod( $path, $hex );
	}

	/**
	 * @param string $path
	 * @param int $mode
	 * @throws SPException
	 * @return bool
	 */
	public static function mkdir( $path, $mode = 0755 )
	{
		$path = Sobi::FixPath( $path );
		if ( !( JFolder::create( $path, $mode ) ) ) {
			throw new SPException( SPLang::e( 'CANNOT_CREATE_DIR', str_replace( SOBI_ROOT, null, $path ) ) );
		}
		else {
			return true;
		}
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function rmdir( $path )
	{
		return JFolder::delete( $path );
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function readable( $path )
	{
		return Jfile::isReadable( $path );
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function writable( $path )
	{
		return Jfile::isWritable( $path );
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function owner( $path )
	{
		return fileowner( $path );
	}

	/**
	 *     *
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	public static function rename( $source, $destination )
	{
		return self::move( $source, $destination );
	}
}
