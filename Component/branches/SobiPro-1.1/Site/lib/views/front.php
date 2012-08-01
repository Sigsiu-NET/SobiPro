<?php
/**
 * @version: $Id: front.php 1448 2011-05-30 10:02:08Z Radek Suski $
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
 * $Date: 2011-05-30 12:02:08 +0200 (Mon, 30 May 2011) $
 * $Revision: 1448 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/front.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadView( 'view' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:16:04 PM
 */
class SPSectionsView extends SPFrontView implements SPView
{
	/**
	 *
	 */
	public function display()
	{
		$this->_type = 'frontpage';
		$type = $this->key( 'template_type', 'xslt' );
		if( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if( $type == 'xslt' ) {
			$sections = $this->get( 'sections' );
			$data = array();
			if( count( $sections ) ) {
				foreach ( $sections as $section ) {
					$s = array(
								'name' => array (
									'_complex' => 1,
									'_data' => $section->get( 'name' ),
									'_attributes' => array(
										'lang' => Sobi::Lang( false )
							)
					),
							'description' => array (
								'_complex' => 1,
								'_cdata' => 1,
								'_data' => $section->get( 'description' ),
								'_attributes' => array(
									'lang' => Sobi::Lang( false )
						)
					),
								'createdTime' => $section->get( 'createdTime' ),
								'meta' => array(
									'description' => $section->get( 'metaDesc' ),
									'keys' => $this->metaKeys( $section ),
									'author' => $section->get( 'metaAuthor' ),
									'robots' => $section->get( 'metaRobots' ),
					),
								'owner' => $section->get( 'owner' ),
								'version' => $section->get( 'version' ),
								'validSince' => $section->get( 'validSince' ),
								'validUntil' => $section->get( 'validUntil' ),
								'url' => Sobi::Url( array( 'sid' => $section->get( 'id' ) ) )
					);
					$data[] = array(
									'_complex' => 1,
									'_data' => $s,
									'_attributes' => array(
										'id' => $section->get( 'id' ),
										'nid' => $section->get( 'nid' ),
							)
					);
				}
			}
			$this->assign( $data, 'sections' );
			Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), array( &$this->_attr ) );
		}
		parent::display();
	}
}
?>