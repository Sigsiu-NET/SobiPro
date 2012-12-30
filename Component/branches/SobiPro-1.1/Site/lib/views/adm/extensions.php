<?php
/**
 * @version: $Id: extensions.php 1431 2011-05-28 12:00:13Z Radek Suski $
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
 * $Date: 2011-05-28 14:00:13 +0200 (Sat, 28 May 2011) $
 * $Revision: 1431 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/extensions.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jun-2010 17:09:48
 */
class SPExtensionsView extends SPAdmView
{
	public function display()
	{
		switch ( $this->get( 'task' ) ) {
			case 'manage':
				$this->browse();
				$this->determineTemplate( 'extensions', 'section' );
				break;
		}
		parent::display();
	}

	private function browse()
	{
		/* create the header */
		$list =& $this->get( 'applications' );
		$plugins = array();
		if ( count( $list ) ) {
			$c = 0;
			foreach ( $list as $plugin ) {
				$plugin[ 'id' ] = $plugin[ 'type' ] . '.' . $plugin[ 'pid' ];
				$plugins[ $c++ ] = $plugin;
			}
		}
		$this->assign( $plugins, 'applications' );
		$this->assign( Sobi::Section( true ), 'section' );
	}

	/**
	 * @param string $title
	 * @return string
	 */
	public function setTitle( $title )
	{
		$title = parent::setTitle( $title );
		return $title;
	}
}
