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
			case 'installed':
//				$this->installed();
				break;
//			case 'browse':
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
				if ( isset( $plugin[ 'availability_expl' ] ) ) {
					if ( isset( $plugin[ 'availability_link' ] ) ) {
						$plugin[ 'availability' ] = SPTooltip::toolTip( $plugin[ 'availability_expl' ], $plugin[ 'availability' ], null, null, $plugin[ 'availability' ], $plugin[ 'availability_link' ] );
					}
					else {
						$plugin[ 'availability' ] = SPTooltip::toolTip( $plugin[ 'availability_expl' ], $plugin[ 'availability' ], null, null, $plugin[ 'availability' ] );
					}
				}
				if ( isset( $plugin[ 'installed' ] ) ) {
					switch ( $plugin[ 'installed' ] ) {
						case 0:
							$plugin[ 'installed' ] = SPTooltip::toolTip(
								Sobi::Txt( 'EX.BRWOSE_NOT_INSTALLED_EXPL' ),
								Sobi::Txt( 'EX.BRWOSE_NOT_INSTALLED' ),
								Sobi::Cfg( 'list_icons.category_goin' )
							);
							break;
						case 1:
							$plugin[ 'installed' ] = SPTooltip::toolTip(
								Sobi::Txt( 'EX.BRWOSE_INSTALLED_EXPL' ),
								Sobi::Txt( 'EX.BRWOSE_INSTALLED' ),
								Sobi::Cfg( 'list_icons.field_editable_1' )
							);
							break;
						case 2:
							$plugin[ 'installed' ] = SPTooltip::toolTip(
								Sobi::Txt( 'EX.BRWOSE_INSTALLED_UPD_EXPL' ),
								Sobi::Txt( 'EX.BRWOSE_INSTALLED_UPD' ),
								Sobi::Cfg( 'list_icons.field_editable_0' )
							);
							break;
					}
				}
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
//		$name = $this->get( 'section.name' );
//		if ( $name ) {
//			Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
//			$title = Sobi::Txt( $title, array( 'category_name' => $name ) );
//			$this->set( $name, 'category_name' );
//			$this->set( $name, 'section_name' );
//			$this->set( $title, 'site_title' );
//		}
		$title = parent::setTitle( $title );
		return $title;
	}
}
