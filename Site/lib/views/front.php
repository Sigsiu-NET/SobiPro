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
			$data = [];
			if( count( $sections ) ) {
				foreach ( $sections as $section ) {
					$s = [
								'name' => [
									'_complex' => 1,
									'_data' => $section->get( 'name' ),
									'_attributes' => [
										'lang' => Sobi::Lang( false )
									]
								],
							'description' => [
								'_complex' => 1,
								'_cdata' => 1,
								'_data' => $section->get( 'description' ),
								'_attributes' => [
									'lang' => Sobi::Lang( false )
								]
							],
								'createdTime' => $section->get( 'createdTime' ),
								'meta' => [
									'description' => $section->get( 'metaDesc' ),
									'keys' => $this->metaKeys( $section ),
									'author' => $section->get( 'metaAuthor' ),
									'robots' => $section->get( 'metaRobots' ),
								],
								'owner' => $section->get( 'owner' ),
								'version' => $section->get( 'version' ),
								'validSince' => $section->get( 'validSince' ),
								'validUntil' => $section->get( 'validUntil' ),
								'url' => Sobi::Url( [ 'sid' => $section->get( 'id' ) ] )
					];
					$data[] = [
									'_complex' => 1,
									'_data' => $s,
									'_attributes' => [
										'id' => $section->get( 'id' ),
										'nid' => $section->get( 'nid' ),
									]
					];
				}
			}
			$this->assign( $data, 'sections' );
			Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
		}
		parent::display();
	}
}
