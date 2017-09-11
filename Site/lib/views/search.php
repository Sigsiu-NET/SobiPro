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
 * @created 29-March-2010 13:08:21
 */
class SPSearchView extends SPSectionView implements SPView
{

	public function display()
	{
		$this->_type = 'search';
		$type = $this->key( 'template_type', 'xslt' );
		if ( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if ( $type == 'xslt' ) {
			$searchData = [];
			$fields = $this->get( 'fields' );
			$visitor = $this->get( 'visitor' );
			$p = $this->get( 'priorities' );
			$priorities = [];
			if ( is_array( $p ) && count( $p ) ) {
				foreach ( $p as $priority => $eids ) {
					if ( is_array( $eids ) && count( $eids ) ) {
						foreach ( $eids as $sid ) {
							$priorities[ $sid ] = $priority;
						}
					}
				}
			}
			$entries = $this->get( 'entries' );
			$searchData[ 'section' ] = [
				'_complex'    => 1,
				'_data'       => Sobi::Section( true ),
				'_attributes' => [ 'id' => Sobi::Section(), 'lang' => Sobi::Lang( false ) ]
			];
			$section = SPFactory::Section( Sobi::Section() );
			$searchData[ 'name' ] = [
				'_complex'    => 1,
				'_data'       => $section->get( 'sfTitle' ),
				'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
			];
			$css_debug = '';
			if ( $development = ( Sobi::Cfg( 'template.development', true ) && !defined( 'SOBIPRO_ADM' ) ) ) {
				$searchData[ 'development' ] = $development;
				$css_debug = ' development';
			}
			$searchData[ 'description' ] = [
				'_complex'    => 1,
				'_data'       => $section->get( 'sfDesc' ),
				'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
			];

			$searchPhrase = $this->get( 'search_for' );
			$phrase = $this->get( 'search_phrase' );
			$searchPhrase = strlen( $searchPhrase ) ? $searchPhrase : Sobi::Txt( 'SH.SEARCH_FOR_BOX' );

			SPFactory::header()->addJsCode( 'var spSearchDefStr = "' . Sobi::Txt( 'SH.SEARCH_FOR_BOX' ) . '"' );
			if ( $this->get( '$eInLine' ) ) {
				$searchData[ 'entries_in_line' ] = $this->get( '$eInLine' );
			}
			if ( $this->get( '$eCount' ) >= 0 ) {
				$searchData[ 'message' ] = Sobi::Txt( 'SH.SEARCH_FOUND_RESULTS', [ 'count' => $this->get( '$eCount' ) ] );
			}
			$this->menu( $searchData );
			$this->alphaMenu( $searchData );
			$fData = [];
			if ( Sobi::Cfg( 'search.show_searchbox', true ) ) {
				$fData[ 'searchbox' ] = [
					'_complex'    => 1,
					'_data'       => [
						'label' => [
							'_complex'    => 1,
							'_data'       => Sobi::Txt( 'SH.SEARCH_FOR' ),
							'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
						],
						'data'  => [
							'_complex' => 1,
							'_xml'     => 1,
							'_data'    => SPHtml_Input::text( 'sp_search_for', $searchPhrase, [ 'class' => Sobi::Cfg( 'search.form_box_def_css', 'SPSearchBox' ), 'id' => 'SPSearchBox' ] ),
						],
					],
					'_attributes' => [ 'position' => 1, 'css_class' => 'SPSearchBox' ]
				];
			}
			if ( Sobi::Cfg( 'search.top_button', true ) ) {
				$fData[ 'top_button' ] = [
					'_complex'    => 1,
					'_data'       => [
						'label' => [
							'_complex'    => 1,
							'_data'       => Sobi::Txt( 'SH.SEARCH_START' ),
							'_attributes' => [ 'lang' => Sobi::Lang() ]
						],
						'data'  => [
							'_complex' => 1,
							'_xml'     => 1,
							'_data'    => SPHtml_Input::submit( 'search', Sobi::Txt( 'SH.START' ), [ 'id' => 'top_button' ] ),
						],
					],
					'_attributes' => [ 'position' => 1, 'css_class' => 'SPSearchButton' ]
				];
			}
			if ( Sobi::Cfg( 'search.show_phrase', true ) ) {
				$fData[ 'phrase' ] = [
					'_complex'    => 1,
					'_data'       => [
						'label' => [
							'_complex'    => 1,
							'_data'       => Sobi::Txt( 'SH.FIND_ENTRIES_THAT_HAVE' ),
							'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
						],
						'data'  => [
							'_complex' => 1,
							'_xml'     => 1,
							'_data'    => SPHtml_Input::radioList(
								'spsearchphrase',
								[
									'all'   => Sobi::Txt( 'SH.FIND_ENTRIES_THAT_HAVE_ALL_WORDS' ),
									'any'   => Sobi::Txt( 'SH.FIND_ENTRIES_THAT_HAVE_ANY_WORDS' ),
									'exact' => Sobi::Txt( 'SH.FIND_ENTRIES_THAT_HAVE_EXACT_PHRASE' ),
								],
								'spsearchphrase',
								strlen( $phrase ) ? $phrase : Sobi::Cfg( 'search.form_searchphrase_def', 'all' ),
								null,
								'right'
							)
						],
					],
					'_attributes' => [ 'position' => 1, 'css_class' => 'SPSearchPhrase' ]
				];
			}
			if ( count( $fields ) ) {
				foreach ( $fields as $field ) {
					$data = $field->searchForm();
					$suffix = $field->get( 'searchMethod' ) != 'range' ? $field->get( 'suffix' ) : null;
					if ( strlen( $data ) ) {
						$fData[ $field->get( 'nid' ) ] = [
							'_complex'    => 1,
							'_data'       => [
								'label' => [
									'_complex'    => 1,
									'_data'       => $field->get( 'name' ),
									'_attributes' => [ 'lang' => Sobi::Lang() ]
								],
								'data'  => [
									'_complex' => 1,
									'_xml'     => 1,
									'_data'    => $data,
								],
							],
							'_attributes' => [ 'id'         => $field->get( 'id' ),
							                   'type'       => $field->get( 'type' ),
							                   'suffix'     => $suffix,
							                   'position'   => $field->get( 'position' ),
							                   'css_search' => $field->get( 'cssClassSearch' ) . $css_debug,
							                   'debug'      => $development,
							                   'width'      => $field->get( 'bsSearchWidth' ),
							                   'css_class'  => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' )
							]
						];
					}
				}
			}
			if ( Sobi::Cfg( 'search.bottom_button', false ) ) {
				$fData[ 'bottom_button' ] = [
					'_complex'    => 1,
					'_data'       => [
						'label' => [
							'_complex'    => 1,
							'_data'       => Sobi::Txt( 'SH.SEARCH_START' ),
							'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
						],
						'data'  => [
							'_complex' => 1,
							'_xml'     => 1,
							'_data'    => SPHtml_Input::submit( 'search', Sobi::Txt( 'SH.START' ) ),
						],
					],
					'_attributes' => [ 'position' => 1, 'css_class' => 'SPSearchButton' ]
				];
			}
			$searchData[ 'fields' ] = $fData;
			if ( count( $entries ) ) {
				$this->loadNonStaticData( $entries );
				$manager = Sobi::Can( 'entry', 'edit', '*', Sobi::Section() ) ? true : false;
				foreach ( $entries as $entry ) {
					$en = $this->entry( $entry, $manager );
					$searchData[ 'entries' ][] = [
						'_complex'    => 1,
						'_attributes' => [ 'id' => $en[ 'id' ], 'search-priority' => isset( $priorities[ $en[ 'id' ] ] ) ? $priorities[ $en[ 'id' ] ] : 'undefined' ],
						'_data'       => $en
					];
				}
				$this->navigation( $searchData );
			}
			$searchData[ 'visitor' ] = $this->visitorArray( $visitor );
			$this->_attr = $searchData;
		}
		Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
		parent::display( $this->_type );
	}
}
