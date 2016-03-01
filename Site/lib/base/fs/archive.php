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
						$t = Sobi::FixPath( Sobi::Cfg( 'fs.temp' ).'/'.md5( microtime() ) );
						SPFs::mkdir( $t, 0777 );
						$dir = SPFactory::Instance( 'base.fs.directory', $t );
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
