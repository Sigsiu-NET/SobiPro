<?php
/**
 * @version: $Id$
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:15:43 PM
 */
class SPSectionView extends SPFrontView implements SPView
{
	protected function category( $category )
	{
		$cat = array();
		if ( is_numeric( $category ) ) {
			$cat = $this->cachedCategory( $category );
		}
		if ( !( is_array( $cat ) ) || !( count( $cat ) ) ) {
			if ( is_numeric( $category ) ) {
				$category = SPFactory::Category( $category );
			}
			$cat[ 'id' ] = $category->get( 'id' );
			$cat[ 'nid' ] = $category->get( 'nid' );
			$cat[ 'name' ] = array(
				'_complex' => 1,
				'_data' => $category->get( 'name' ),
				'_attributes' => array( 'lang' => Sobi::Lang( false ) )
			);
			if ( Sobi::Cfg( 'list.cat_desc', false ) ) {
				$cat[ 'description' ] = array(
					'_complex' => 1,
					'_cdata' => 1,
					'_data' => $category->get( 'description' ),
					'_attributes' => array( 'lang' => Sobi::Lang( false ) )
				);
			}
			$showIntro = $category->get( 'showIntrotext' );
			if ( $showIntro == SPC::GLOBAL_SETTING ) {
				$showIntro = Sobi::Cfg( 'category.show_intro', true );
			}
			if ( $showIntro ) {
				$cat[ 'introtext' ] = array(
					'_complex' => 1,
					'_cdata' => 1,
					'_data' => $category->get( 'introtext' ),
					'_attributes' => array( 'lang' => Sobi::Lang( false ) )
				);
			}
			$showIcon = $category->get( 'showIcon' );
			if ( $showIcon == SPC::GLOBAL_SETTING ) {
				$showIcon = Sobi::Cfg( 'category.show_icon', true );
			}
			if ( $showIcon && $category->get( 'icon' ) ) {
				if ( SPFs::exists( Sobi::Cfg( 'images.category_icons' ) . DS . $category->get( 'icon' ) ) ) {
					$cat[ 'icon' ] = Sobi::FixPath( Sobi::Cfg( 'images.category_icons_live' ) . $category->get( 'icon' ) );
				}
			}
			$cat[ 'url' ] = Sobi::Url( array( 'title' => Sobi::Cfg( 'sef.alias', true ) ? $category->get( 'nid' ) : $category->get( 'name' ), 'sid' => $category->get( 'id' ) ) );
			$cat[ 'position' ] = $category->get( 'position' );
			$cat[ 'author' ] = $category->get( 'owner' );
			if ( $category->get( 'state' ) == 0 ) {
				$cat[ 'state' ] = 'unpublished';
			}
			else {
				if ( strtotime( $category->get( 'validUntil' ) ) != 0 && strtotime( $category->get( 'validUntil' ) ) < time() ) {
					$cat[ 'state' ] = 'expired';
				}
				elseif ( strtotime( $category->get( 'validSince' ) ) != 0 && strtotime( $category->get( 'validSince' ) ) > time() ) {
					$cat[ 'state' ] = 'pending';
				}
				else {
					$cat[ 'state' ] = 'published';
				}
			}
			if ( Sobi::Cfg( 'list.cat_meta', false ) ) {
				$cat[ 'meta' ] = array(
					'description' => $category->get( 'metaDesc' ),
					'keys' => $this->metaKeys( $category ),
					'author' => $category->get( 'metaAuthor' ),
					'robots' => $category->get( 'metaRobots' ),
				);
			}
			if ( Sobi::Cfg( 'list.subcats', true ) ) {
				/* @todo we have to change this method in this way that it can be sorted and limited */
				$subcats = $category->getChilds( 'category', false, 1, true, Sobi::Cfg( 'list.subcats_ordering', 'name' ) );
				$sc = array();
				if ( count( $subcats ) ) {
					foreach ( $subcats as $id => $name ) {
						$sc[ ] = array(
							'_complex' => 1,
							'_data' => $name[ 'name' ],
							'_attributes' => array( 'lang' => Sobi::Lang( false ), 'id' => $id, 'url' => Sobi::Url( array( 'title' => Sobi::Cfg( 'sef.alias', true ) ? $name[ 'alias' ] : $name[ 'name' ], 'sid' => $id, ) ) )
						);
					}
				}
				$cat[ 'subcategories' ] = $sc;
			}
			SPFactory::cache()->addObj( $cat, 'category_struct', $category->get( 'id' ) );
			unset( $category );
		}
		$cat[ 'counter' ] = $this->getNonStaticData( $cat[ 'id' ], 'counter' );
		Sobi::Trigger( 'List', ucfirst( __FUNCTION__ ), array( &$cat ) );
		return $cat;
	}

	protected function cachedCategory( $category )
	{
		$cat = SPFactory::cache()->getObj( 'category_struct', $category );
		if ( count( $cat ) ) {
			return $cat;
		}
		else {
			return false;
		}
	}

	protected function cachedEntry( $entry, $manager, $noId = false )
	{
		static $nonStatic = null;
		if ( !( $nonStatic ) ) {
			$nonStatic = explode( ',', Sobi::Cfg( 'cache.non_static', 'counter, id' ) );
			if ( count( $nonStatic ) ) {
				foreach ( $nonStatic as $i => $v ) {
					$v = trim( $v );
					if ( $v == 'id' ) {
						unset( $nonStatic[ $i ] );
					}
					else {
						$nonStatic[ $i ] = $v;
					}
				}
			}
		}
		$en = SPFactory::cache()->getObj( 'entry_struct', $entry );
		if ( is_array( $en ) && count( $en ) ) {
			if ( strstr( SPRequest::task(), 'search' ) || $noId || ( Sobi::Cfg( 'section.force_category_id', false ) && SPRequest::sid() == Sobi::Section() ) ) {
				$en[ 'url' ] = Sobi::Url( $en[ 'url_array' ] );
			}
			else {
				$en[ 'url_array' ][ 'pid' ] = SPRequest::sid();
				$en[ 'url' ] = Sobi::Url( $en[ 'url_array' ] );
			}
			if ( $manager || ( ( Sobi::My( 'id' ) && ( Sobi::My( 'id' ) == $en[ 'author' ] ) && Sobi::Can( 'entry', 'edit', 'own', Sobi::Section() ) ) ) ) {
				$en[ 'edit_url' ] = Sobi::Url( $en[ 'edit_url_array' ] );
			}
			else {
				if ( isset( $en[ 'edit_url' ] ) ) {
					unset( $en[ 'edit_url' ] );
				}
			}
			unset( $en[ 'url_array' ] );
			unset( $en[ 'edit_url_array' ] );
			foreach ( $nonStatic as $v ) {
				$en[ $v ] = $this->getNonStaticData( $entry, $v );
			}
			if ( isset( $en[ 'fields' ] ) && count( $en[ 'fields' ] ) ) {
				$this->validateFields( $en[ 'fields' ] );
			}
			return $en;
		}
		else {
			return false;
		}
	}

	protected function entry( $entry, $manager, $noId = false )
	{
		$en = array();
		if ( is_numeric( $entry ) ) {
			$en = $this->cachedEntry( $entry, $manager, $noId );
		}
		if ( !( is_array( $en ) ) || !( count( $en ) ) ) {
			if ( is_numeric( $entry ) ) {
				$entry = SPFactory::Entry( $entry );
			}
			$en[ 'id' ] = $entry->get( 'id' );
			$en[ 'nid' ] = $entry->get( 'nid' );
			$en[ 'name' ] = array(
				'_complex' => 1,
				'_data' => $entry->get( 'name' ),
				'_attributes' => array( 'lang' => Sobi::Lang( false ) )
			);
			$en[ 'url_array' ] = array( 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ), 'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) );
			if ( strstr( SPRequest::task(), 'search' ) || $noId || ( Sobi::Cfg( 'section.force_category_id', false ) && SPRequest::sid() == Sobi::Section() ) ) {
				$en[ 'url' ] = Sobi::Url( array( 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ), 'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) ) );
			}
			else {
				$en[ 'url' ] = Sobi::Url( array( 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ), 'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) ) );
			}
			if ( Sobi::Cfg( 'list.entry_meta', true ) ) {
				$en[ 'meta' ] = array(
					'description' => $entry->get( 'metaDesc' ),
					'keys' => $this->metaKeys( $entry ),
					'author' => $entry->get( 'metaAuthor' ),
					'robots' => $entry->get( 'metaRobots' ),
				);
			}
			if ( $manager || ( ( Sobi::My( 'id' ) && ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) && Sobi::Can( 'entry', 'edit', 'own', Sobi::Section() ) ) ) ) {
				$en[ 'edit_url' ] = Sobi::Url( array( 'task' => 'entry.edit', 'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) ) );
			}
			else {
				if ( isset( $en[ 'edit_url' ] ) ) {
					unset( $en[ 'edit_url' ] );
				}
			}
			$en[ 'edit_url_array' ] = array( 'task' => 'entry.edit', 'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) );
			$en[ 'created_time' ] = $entry->get( 'createdTime' );
			$en[ 'updated_time' ] = $entry->get( 'updatedTime' );
			$en[ 'valid_since' ] = $entry->get( 'validSince' );
			$en[ 'valid_until' ] = $entry->get( 'validUntil' );
			if ( $entry->get( 'state' ) == 0 ) {
				$en[ 'state' ] = 'unpublished';
			}
			else {
				if ( strtotime( $entry->get( 'validUntil' ) ) != 0 && strtotime( $entry->get( 'validUntil' ) ) < time() ) {
					$en[ 'state' ] = 'expired';
				}
				elseif ( strtotime( $entry->get( 'validSince' ) ) != 0 && strtotime( $entry->get( 'validSince' ) ) > time() ) {
					$en[ 'state' ] = 'pending';
				}
				else {
					$en[ 'state' ] = 'published';
				}
			}
			$en[ 'author' ] = $entry->get( 'owner' );
			$en[ 'counter' ] = $entry->get( 'counter' );
			$en[ 'approved' ] = $entry->get( 'approved' );
			//		$en[ 'confirmed' ] = $entry->get( 'confirmed' );
			if ( Sobi::Cfg( 'list.entry_cats', true ) ) {
				$cats = $entry->get( 'categories' );
				$categories = array();
				if ( count( $cats ) ) {
					$cn = SPLang::translateObject( array_keys( $cats ), array( 'name', 'alias' ) );
				}
				foreach ( $cats as $cid => $cat ) {
					$categories[ ] = array(
						'_complex' => 1,
						'_data' => SPLang::clean( $cn[ $cid ][ 'value' ] ),
						'_attributes' => array( 'lang' => Sobi::Lang( false ), 'id' => $cat[ 'pid' ], 'position' => $cat[ 'position' ], 'url' => Sobi::Url( array( 'sid' => $cat[ 'pid' ], 'title' => Sobi::Cfg( 'sef.alias', true ) ? $cat[ 'alias' ] : $cat[ 'name' ] ) ) )
					);
				}
				$en[ 'categories' ] = $categories;
			}
			$fields = $entry->getFields();
			if ( count( $fields ) ) {
//				foreach ( $fields as $field ) {
//					if ( $field->enabled( 'vcard' ) && $field->get( 'id' ) != Sobi::Cfg( 'entry.name_field' ) ) {
//						$struct = $field->struct();
//						$options = null;
//						if ( isset( $struct[ '_options' ] ) ) {
//							$options = $struct[ '_options' ];
//							unset( $struct[ '_options' ] );
//						}
//						$f[ $field->get( 'nid' ) ] = array(
//							'_complex' => 1,
//							'_data' => array(
//								'label' => array(
//									'_complex' => 1,
//									'_data' => $field->get( 'name' ),
//									'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
//								),
//								'data' => $struct,
//							),
//							'_attributes' => array( 'id' => $field->get( 'id' ), 'type' => $field->get( 'type' ), 'suffix' => $field->get( 'suffix' ), 'position' => $field->get( 'position' ), 'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' ) )
//						);
//						if ( Sobi::Cfg( 'list.field_description', false ) ) {
//							$f[ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = array( '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) );
//						}
//						if ( $options ) {
//							$f[ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
//						}
//						if ( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
//							foreach ( $struct[ '_xml_out' ] as $k => $v )
//								$f[ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
//						}
//					}
//				}
				$en[ 'fields' ] = $this->fieldStruct( $fields, 'vcard' );
			}
			SPFactory::cache()
					->addObj( $entry, 'entry', $entry->get( 'id' ) )
					->addObj( $en, 'entry_struct', $entry->get( 'id' ) );
			unset( $en[ 'url_array' ] );
			unset( $en[ 'edit_url_array' ] );
			unset( $entry );
		}
		$en[ 'counter' ] = $this->getNonStaticData( $en[ 'id' ], 'counter' );
		/*
		   * this is te special case:
		   * no matter what task we currently have - if someone called this we need the data for the V-Card
		   * Soe we have to trigger all these plugins we need and therefore also fake the task
		   */
		$task = 'list.custom';
		SPFactory::registry()->set( 'task', $task );
		Sobi::Trigger( 'List', ucfirst( __FUNCTION__ ), array( &$en ) );
		return $en;
	}

	protected function navigation( &$data )
	{
		$navigation = $this->get( 'navigation' );
		if ( count( $navigation ) ) {
			$data[ 'navigation' ] = array( '_complex' => 1, '_data' => $navigation, '_attributes' => array( 'lang' => Sobi::Lang( false ) ) );
		}
	}

	private function view()
	{
		$type = $this->key( 'template_type', 'xslt' );
		if ( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if ( $type == 'xslt' ) {
			$visitor = $this->get( 'visitor' );
			$current = $this->get( $this->_type );
			$categories = $this->get( 'categories' );
			$entries = $this->get( 'entries' );
			$cUrl = array( 'title' => Sobi::Cfg( 'sef.alias', true ) ? $current->get( 'nid' ) : $current->get( 'name' ), 'sid' => $current->get( 'id' ) );
			if ( SPRequest::int( 'site', 0 ) ) {
				$cUrl[ 'site' ] = SPRequest::int( 'site', 0 );
			}
			SPFactory::header()->addCanonical( Sobi::Url( $cUrl, true, true, true ) );
			$data = array();
			$data[ 'id' ] = $current->get( 'id' );
			$data[ 'counter' ] = $current->get( 'counter' );
			$data[ 'section' ] = array(
				'_complex' => 1,
				'_data' => Sobi::Section( true ),
				'_attributes' => array( 'id' => Sobi::Section(), 'lang' => Sobi::Lang( false ) )
			);
			$data[ 'name' ] = array(
				'_complex' => 1,
				'_data' => $current->get( 'name' ),
				'_attributes' => array( 'lang' => Sobi::Lang( false ) )
			);
			if ( Sobi::Cfg( 'category.show_desc' ) || $current->get( 'oType' ) == 'section' ) {
				$desc = $current->get( 'description' );
				if ( Sobi::Cfg( 'category.parse_desc' ) ) {
					Sobi::Trigger( 'prepare', 'Content', array( &$desc, $current ) );
				}
				$data[ 'description' ] = array(
					'_complex' => 1,
					'_cdata' => 1,
					'_data' => $desc,
					'_attributes' => array( 'lang' => Sobi::Lang( false ) )
				);
			}
			$showIcon = $current->get( 'showIcon' );
			if ( $showIcon == SPC::GLOBAL_SETTING ) {
				$showIcon = Sobi::Cfg( 'category.show_icon', true );
			}
			if ( $showIcon && $current->get( 'icon' ) ) {
				if ( SPFs::exists( Sobi::Cfg( 'images.category_icons' ) . DS . $current->get( 'icon' ) ) ) {
					$data[ 'icon' ] = Sobi::FixPath( Sobi::Cfg( 'images.category_icons_live' ) . $current->get( 'icon' ) );
				}
			}
			$data[ 'meta' ] = array(
				'description' => $current->get( 'metaDesc' ),
				'keys' => $this->metaKeys( $current ),
				'author' => $current->get( 'metaAuthor' ),
				'robots' => $current->get( 'metaRobots' ),
			);
			$data[ 'entries_in_line' ] = $this->get( '$eInLine' );
			$data[ 'categories_in_line' ] = $this->get( '$cInLine' );
			$data[ 'number_of_subcats' ] = Sobi::Cfg( 'list.num_subcats' );
			$this->menu( $data );
			$this->alphaMenu( $data );
			$data[ 'visitor' ] = $this->visitorArray( $visitor );
			if ( count( $categories ) ) {
				$this->loadNonStaticData( $categories );
				foreach ( $categories as $category ) {
					$cat = $this->category( $category );
					$data[ 'categories' ][ ] = array(
						'_complex' => 1,
						'_attributes' => array( 'id' => $cat[ 'id' ], 'nid' => $cat[ 'nid' ] ),
						'_data' => $cat
					);
				}
			}
			if ( count( $entries ) ) {
				$this->loadNonStaticData( $entries );
				$manager = Sobi::Can( 'entry', 'edit', '*', Sobi::Section() ) ? true : false;
				foreach ( $entries as $eid ) {
					$en = $this->entry( $eid, $manager );
					$data[ 'entries' ][ ] = array(
						'_complex' => 1,
						'_attributes' => array( 'id' => $en[ 'id' ], 'nid' => $en[ 'nid' ] ),
						'_data' => $en
					);
				}
				$this->navigation( $data );
			}
			$this->_attr = $data;
		}
		Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), array( &$this->_attr ) );
	}

	/**
	 *
	 */
	public function display( $type = 'section', $out = null )
	{
		$this->_type = $type;
		switch ( $this->get( 'task' ) ) {
			case 'view':
				$this->view();
				break;
		}
		parent::display( $out );
	}
}
