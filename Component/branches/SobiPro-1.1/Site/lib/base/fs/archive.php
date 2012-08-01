<?php
/**
 * @version: $Id: archive.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/fs/archive.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'base.fs.file' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 17-Jun-2010 10:13:15
 */
class SPArchive extends SPFile
{
	/**
	 * @param string $to - path where the archive should be extracted to
	 * @return bool
	 */
	public function extract( $to )
	{
		$r = false;
		$ext = SPFs::getExt( $this->_filename );
		switch ( $ext ) {
			case 'zip':
				$zip = new ZipArchive();
				if ( $zip->open( $this->_filename ) === true ) {
					SPException::catchErrors( SPC::WARNING );
					try {
						$zip->extractTo( $to );
						$zip->close();
						$r = true;
					}
					catch( SPException $x ) {
						$t = Sobi::FixPath( Sobi::Cfg( 'fs.temp' ).DS.md5( microtime() ) );
						SPFs::mkdir( $t, 0777 );
						$dir =& SPFactory::Instance( 'base.fs.directory', $t );
						if( $zip->extractTo( $t ) ) {
							$zip->close();
							$dir->moveFiles( $to );
							$r = true;
						}
						SPFs::delete( $dir->getPathname() );
					}
					SPException::catchErrors( 0 );
				}
				break;
		}
		return $r;
	}
}
?>