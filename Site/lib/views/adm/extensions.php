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
		$plugins = [];
		if ( count( $list ) ) {
			$c = 0;
			foreach ( $list as $plugin ) {
				$plugin[ 'id' ] = $plugin[ 'type' ] . '.' . $plugin[ 'pid' ];
				$plugins[ $c++ ] = $plugin;
			}
		}
		$this->assign( $plugins, 'applications' );
		$sectionName = Sobi::Section( true );
		$this->assign( $sectionName, 'section' );
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
