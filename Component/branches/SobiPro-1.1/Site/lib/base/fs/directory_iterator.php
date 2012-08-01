<?php
/**
 * @version: $Id: directory_iterator.php 551 2011-01-11 14:34:26Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/fs/directory_iterator.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'base.fs.file' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jun-2010 10:21:27
 */

class SPDirectoryIterator extends ArrayObject
{
    /**
     * @var string
     */
    private $_dir = null;

    /**
     * @param string $dir - path
     * @return SPDirectoryIterator
     */
    public function __construct( $dir )
    {
		$Dir = scandir( $dir );
		$dirs = array();
        $this->_dir = new ArrayObject();
        foreach( $Dir as $file ) {
            $this->append( new SPFile( Sobi::FixPath( $dir.DS.$file ) ) );
        }
        $this->uasort( array( $this, '_spSort' ) );
    }

    /**
     * @param string $string - part or full name of the file to search for
     * @param bool $exact - search for exact string or the file nam can contain this string
     * @return array
     */
    public function searchFile( $string, $exact = true )
    {
    	$results = array();
    	foreach( $this as $item ) {
    		if( $item->isDot() ) {
    			continue;
    		}
    		if( $exact ) {
    			if( $item->getFileName() == $string ) {
    				$results[ $item->getPathname() ] = $item;
    			}
    		}
    		else {
    			if( strstr( $item->getFileName(), $string ) ) {
    				$results[ $item->getPathname() ] = $item;
    			}
    		}
    	}
    	return $results;
    }

    /**
     * Compare callback for the uasort
     * @param $from
     * @param $to
     * @return int
     */
    public function _spSort( $from, $to )
    {
    	if( ( $from->isDir() && $to->isDir() ) || ( $from->isFile() && $to->isFile() ) ) {
    		return strcmp( $from->getFileName(), $to->getFileName() );
    	}
    	else {
    		return ( $from->isDir() && !( $from->isDot() ) ) ? -1 : 1;
    	}
    }
}
?>