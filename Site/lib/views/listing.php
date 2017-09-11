<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'section' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 17-Aug-2009 10:27:00
 */
class SPListingView extends SPSectionView implements SPView
{
	private function view()
	{
		$type = $this->key( 'template_type', 'xslt' );
		if( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if( $type == 'xslt' ) {
			$visitor = $this->get( 'visitor' );
			$current = $this->get( 'section' );
			$categories = $this->get( 'categories' );
			$entries = $this->get( 'entries' );
			$data = [];
			$data[ 'id' ] = $current->get( 'id' );
			$data[ 'section' ] = [
					'_complex' => 1,
					'_data' => Sobi::Section( true ),
					'_attributes' => [ 'id' => Sobi::Section(), 'lang' => Sobi::Lang( false ) ]
			];
			$data[ 'name' ] = [
							'_complex' => 1,
							'_data' => $this->get( 'listing_name' ),
							'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
			];
			if ( $development = (Sobi::Cfg( 'template.development', true ) && !defined( 'SOBIPRO_ADM' )) ) {
				$data[ 'development' ] = $development;
			}
			if( Sobi::Cfg( 'category.show_desc' ) ) {
				$desc = $current->get( 'description' );
				if( Sobi::Cfg( 'category.parse_desc' ) ) {
					Sobi::Trigger( 'prepare', 'Content', [ &$desc, $current ] );
				}
				$data[ 'description' ] = [
								'_complex' => 1,
								'_cdata' => 1,
								'_data' => $desc,
								'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
				];
			}
			$data[ 'meta' ] = [
									'description' => $current->get( 'metaDesc' ),
									'keys' => $this->metaKeys( $current ),
									'author' => $current->get( 'metaAuthor' ),
									'robots' => $current->get( 'metaRobots' ),
			];
			$data[ 'entries_in_line' ] = $this->get( '$eInLine' );
			$data[ 'categories_in_line' ] = $this->get( '$cInLine' );
			$this->menu( $data );
			$this->alphaMenu( $data );
			$data[ 'visitor' ] = $this->visitorArray( $visitor );
			if( count( $categories ) ) {
				foreach ( $categories as $category ) {
					if( is_numeric( $category ) ) {
						$category = SPFactory::Category( $category );
					}
					$data[ 'categories' ][] = [
						'_complex' => 1,
						'_attributes' => [ 'id' => $category->get( 'id' ),  'nid' => $category->get( 'nid' ) ],
						'_data' => $this->category( $category )
					];
					unset( $category );
				}
			}
			if( count( $entries ) ) {
                $this->loadNonStaticData( $entries );
				$manager = Sobi::Can( 'entry', 'edit', '*', Sobi::Section() ) ? true : false;
				foreach ( $entries as $eid ) {
					$en = $this->entry( $eid, $manager );
					$data[ 'entries' ][] = [
						'_complex' => 1,
						'_attributes' => [ 'id' => $en[ 'id' ] ],
						'_data' => $en
					];
				}
				$this->navigation( $data );
			}
			$this->_attr = $data;
		}
		// general listing trigger
		Sobi::Trigger( 'Listing', ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
		// specific lisitng trigger
		Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
	}

	/**
	 * @param string $type
	 * @param null $out
	 */
	public function display( $type = 'listing', $out = null )
	{
		$this->_type = $type;
		switch ( $type ) {
			case 'listing':
				$this->view();
				break;
		}
		parent::display( $type, $out );
	}
}
