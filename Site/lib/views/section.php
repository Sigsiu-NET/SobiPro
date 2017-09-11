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

use Sobi\Input\Input;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:15:43 PM
 */
class SPSectionView extends SPFrontView implements SPView
{
	protected function category( $category, $fields = true )
	{
		$cat = [];
		if ( is_numeric( $category ) ) {
			$cat = $this->cachedCategory( $category );
		}
		if ( !( is_array( $cat ) ) || !( count( $cat ) ) ) {
			if ( is_numeric( $category ) ) {
				$category = SPFactory::Category( $category );
			}
			$cat[ 'id' ] = $category->get( 'id' );
			$cat[ 'nid' ] = $category->get( 'nid' );
			$cat[ 'name' ] = [
				'_complex'    => 1,
				'_data'       => $category->get( 'name' ),
				'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
			];
			if ( Sobi::Cfg( 'list.cat_desc', false ) ) {
				$cat[ 'description' ] = [
					'_complex'    => 1,
					'_cdata'      => 1,
					'_data'       => $category->get( 'description' ),
					'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
				];
			}
			$cat[ 'created_time' ] = $category->get( 'createdTime' );
			$cat[ 'updated_time' ] = $category->get( 'updatedTime' );
			$cat[ 'valid_since' ] = $category->get( 'validSince' );
			$cat[ 'valid_until' ] = $category->get( 'validUntil' );
			$this->fixTimes( $cat );

			$showIntro = $category->get( 'showIntrotext' );
			if ( $showIntro == SPC::GLOBAL_SETTING ) {
				$showIntro = Sobi::Cfg( 'category.show_intro', true );
			}
			if ( $showIntro ) {
				$cat[ 'introtext' ] = [
					'_complex'    => 1,
					'_cdata'      => 1,
					'_data'       => $category->get( 'introtext' ),
					'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
				];
			}
			$showIcon = $category->get( 'showIcon' );
			if ( $showIcon == SPC::GLOBAL_SETTING ) {
				$showIcon = Sobi::Cfg( 'category.show_icon', true );
			}
			if ( $showIcon && $category->get( 'icon' ) ) {
				if ( strstr( $category->get( 'icon' ), 'font-' ) ) {
					$icon = json_decode( str_replace( "'", '"', $category->get( 'icon' ) ), true );
					if ( $category->param( 'icon-font-add-class' ) ) {
						$icon[ 'class' ] .= ' ' . $category->param( 'icon-font-add-class' );
					}
					$cat[ 'icon' ] = [
						'_complex'    => 1,
						'_data'       => '',
						'_attributes' => $icon
					];
				}
				elseif ( SPFs::exists( Sobi::Cfg( 'images.category_icons' ) . '/' . $category->get( 'icon' ) ) ) {
					$cat[ 'icon' ] = Sobi::FixPath( Sobi::Cfg( 'images.category_icons_live' ) . $category->get( 'icon' ) );
				}
			}
			$cat[ 'url' ] = Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $category->get( 'nid' ) : $category->get( 'name' ), 'sid' => $category->get( 'id' ) ] );
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
				$cat[ 'meta' ] = [
					'description' => $category->get( 'metaDesc' ),
					'keys'        => $this->metaKeys( $category ),
					'author'      => $category->get( 'metaAuthor' ),
					'robots'      => $category->get( 'metaRobots' ),
				];
			}
			if ( $fields ) {
				$category->loadFields( Sobi::Section(), true );
				$fields = $category->get( 'fields' );
				$this->categoryFields( $cat, $fields );
			}
			if ( Sobi::Cfg( 'list.subcats', true ) ) {
				/* @todo we have to change this method in this way that it can be sorted and limited */
				$subcats = $category->getChilds( 'category', false, 1, true, Sobi::Cfg( 'list.subcats_ordering', 'name' ) );
				$sc = [];
				if ( count( $subcats ) ) {
					foreach ( $subcats as $id => $name ) {
						$sc[] = [
							'_complex'    => 1,
							'_data'       => SPLang::clean( $name[ 'name' ] ),
							'_attributes' => [ 'lang' => Sobi::Lang( false ), 'nid' => $name[ 'alias' ], 'id' => $id, 'url' => Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $name[ 'alias' ] : $name[ 'name' ], 'sid' => $id, ] ) ]
						];
					}
				}
				$cat[ 'subcategories' ] = $sc;
			}
			$id = $fields ? 'category_full_struct' : 'category_struct';
			SPFactory::cache()->addObj( $cat, $id, $category->get( 'id' ) );
			unset( $category );
		}
		$cat[ 'counter' ] = $this->getNonStaticData( $cat[ 'id' ], 'counter' );
		Sobi::Trigger( 'List', ucfirst( __FUNCTION__ ), [ &$cat ] );

		return $cat;
	}

	protected function cachedCategory( $category, $fields = false )
	{
		$id = $fields ? 'category_full_struct' : 'category_struct';
		$cat = SPFactory::cache()->getObj( $id, $category );
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
			if ( strstr( Input::Task(), 'search' ) || $noId || ( Sobi::Cfg( 'section.force_category_id', false ) && Input::Sid() == Sobi::Section() ) ) {
				$en[ 'url' ] = Sobi::Url( $en[ 'url_array' ] );
			}
			else {
				$en[ 'url_array' ][ 'pid' ] = Input::Sid();
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
		$en = [];
		if ( is_numeric( $entry ) ) {
			$en = $this->cachedEntry( $entry, $manager, $noId );
		}
		if ( !( is_array( $en ) ) || !( count( $en ) ) ) {
			$currentSid = Input::Sid();
			if ( is_numeric( $entry ) ) {
				$entry = SPFactory::Entry( $entry );
			}
			$en[ 'id' ] = $entry->get( 'id' );
			$en[ 'nid' ] = $entry->get( 'nid' );
			$en[ 'name' ] = [
				'_complex'    => 1,
				'_data'       => $entry->get( 'name' ),
				'_attributes' => [ 'lang' => Sobi::Lang( false ), 'type' => 'inbox', 'alias' => $entry->get( 'nameField' ) ]
			];
			$ownership = 'valid';
			if ( Sobi::My( 'id' ) && Sobi::My( 'id' ) == $entry->get( 'owner' ) ) {
				$ownership = 'own';
			}
			// don't ask
			Input::Set( 'sid', $entry->get( 'id' ) );
			$en[ 'acl' ] = [
				'_complex'    => 1,
				'_data'       => null,
				'_attributes' => [ 'accessible' => Sobi::Can( 'entry', 'access', $ownership ) ? 'true' : 'false' ]
			];
			Input::Set( 'sid', $currentSid );
//			Input::Set( 'sid', $currentSid );
//			$en[ 'acl' ] = array(
//					'_complex' => 1,
//					'_data' => null,
//					'_attributes' => array( 'accessible' => Sobi::Can( 'entry', 'access', $ownership ) ? 'true' : 'false' )
//			);
//			Input::Set( 'sid', $entry->get( 'id' ) );
			$en[ 'url_array' ] = [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ), 'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) ];
			if ( strstr( Input::Task(), 'search' ) || $noId || ( Sobi::Cfg( 'section.force_category_id', false ) && Input::Sid() == Sobi::Section() ) ) {
				$en[ 'url' ] = Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ), 'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) ] );
			}
			else {
				$en[ 'url' ] = Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ), 'pid' => Input::Sid(), 'sid' => $entry->get( 'id' ) ] );
			}
			if ( Sobi::Cfg( 'list.entry_meta', true ) ) {
				$en[ 'meta' ] = [
					'description' => $entry->get( 'metaDesc' ),
					'keys'        => $this->metaKeys( $entry ),
					'author'      => $entry->get( 'metaAuthor' ),
					'robots'      => $entry->get( 'metaRobots' ),
				];
			}
			if ( $manager || ( ( Sobi::My( 'id' ) && ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) && Sobi::Can( 'entry', 'edit', 'own', Sobi::Section() ) ) ) ) {
				$en[ 'edit_url' ] = Sobi::Url( [ 'task' => 'entry.edit', 'pid' => Input::Sid(), 'sid' => $entry->get( 'id' ) ] );
			}
			else {
				if ( isset( $en[ 'edit_url' ] ) ) {
					unset( $en[ 'edit_url' ] );
				}
			}
			$en[ 'edit_url_array' ] = [ 'task' => 'entry.edit', 'pid' => Input::Sid(), 'sid' => $entry->get( 'id' ) ];
			$en[ 'created_time' ] = $entry->get( 'createdTime' );
			$en[ 'updated_time' ] = $entry->get( 'updatedTime' );
			$en[ 'valid_since' ] = $entry->get( 'validSince' );
			$en[ 'valid_until' ] = $entry->get( 'validUntil' );
			$this->fixTimes( $en );
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
				$categories = [];
				if ( count( $cats ) ) {
					$cn = SPLang::translateObject( array_keys( $cats ), [ 'name', 'alias' ] );
				}
				foreach ( $cats as $cid => $cat ) {
					$categories[] = [
						'_complex'    => 1,
						'_data'       => SPLang::clean( $cn[ $cid ][ 'value' ] ),
						'_attributes' => [ 'lang' => Sobi::Lang( false ), 'id' => $cat[ 'pid' ], 'position' => $cat[ 'position' ], 'url' => Sobi::Url( [ 'sid' => $cat[ 'pid' ], 'title' => Sobi::Cfg( 'sef.alias', true ) ? $cat[ 'alias' ] : $cat[ 'name' ] ] ) ]
					];
				}
				$en[ 'categories' ] = $categories;
			}
			$fields = $entry->getFields();
			if ( count( $fields ) ) {
				$fieldsToDisplay = $this->getFieldsToDisplay( $entry );
				if ( $fieldsToDisplay ) {
					foreach ( $fields as $i => $field ) {
						if ( !( in_array( $field->get( 'id' ), $fieldsToDisplay ) ) ) {
							unset( $fields[ $i ] );
						}
					}
				}
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
		Sobi::Trigger( 'List', ucfirst( __FUNCTION__ ), [ &$en ] );

		return $en;
	}

	protected function navigation( &$data )
	{
		$navigation = $this->get( 'navigation' );
		if ( count( $navigation ) ) {
			$data[ 'navigation' ] = [ '_complex' => 1, '_data' => $navigation, '_attributes' => [ 'lang' => Sobi::Lang( false ) ] ];
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
			$orderings = $this->get( 'orderings' );
			$categories = $this->get( 'categories' );
			$entries = $this->get( 'entries' );
			$cUrl = [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $current->get( 'nid' ) : $current->get( 'name' ), 'sid' => $current->get( 'id' ) ];
			if ( Input::Int( 'site' ) ) {
				$cUrl[ 'site' ] = Input::Int( 'site' );
			}
			SPFactory::header()->addCanonical( Sobi::Url( $cUrl, true, true, true ) );
			$data = [];
			$data[ 'id' ] = $current->get( 'id' );

			if ( $current->get( 'oType' ) != 'section' ) {
				$data[ 'counter' ] = $current->get( 'counter' );
			}
			$data[ 'section' ] = [
				'_complex'    => 1,
				'_data'       => Sobi::Section( true ),
				'_attributes' => [ 'id' => Sobi::Section(), 'lang' => Sobi::Lang( false ) ]
			];
			$data[ 'name' ] = [
				'_complex'    => 1,
				'_data'       => $current->get( 'name' ),
				'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
			];
			if ( $development = ( Sobi::Cfg( 'template.development', true ) && !defined( 'SOBIPRO_ADM' ) ) ) {
				$data[ 'development' ] = $development;
			}

			$data[ 'created_time' ] = $current->get( 'createdTime' );
			$data[ 'updated_time' ] = $current->get( 'updatedTime' );
			$data[ 'valid_since' ] = $current->get( 'validSince' );
			$data[ 'valid_until' ] = $current->get( 'validUntil' );

			$data[ 'author' ] = $current->get( 'owner' );
			if ( $current->get( 'state' ) == 0 ) {
				$data[ 'state' ] = 'unpublished';
			}
			else {
				if ( strtotime( $current->get( 'validUntil' ) ) != 0 && strtotime( $current->get( 'validUntil' ) ) < time() ) {
					$data[ 'state' ] = 'expired';
				}
				elseif ( strtotime( $current->get( 'validSince' ) ) != 0 && strtotime( $current->get( 'validSince' ) ) > time() ) {
					$data[ 'state' ] = 'pending';
				}
				else {
					$data[ 'state' ] = 'published';
				}
			}
			$data[ 'url' ] = Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $current->get( 'nid' ) : $current->get( 'name' ), 'sid' => $current->get( 'id' ) ], true, true, true );

			if ( Sobi::Cfg( 'category.show_desc' ) || $current->get( 'oType' ) == 'section' ) {
				$desc = $current->get( 'description' );
				if ( Sobi::Cfg( 'category.parse_desc' ) ) {
					Sobi::Trigger( 'prepare', 'Content', [ &$desc, $current ] );
				}
				$data[ 'description' ] = [
					'_complex'    => 1,
					'_cdata'      => 1,
					'_data'       => $desc,
					'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
				];
			}
			$showIcon = $current->get( 'showIcon' );
			if ( $showIcon == SPC::GLOBAL_SETTING ) {
				$showIcon = Sobi::Cfg( 'category.show_icon', true );
			}
			if ( $showIcon && $current->get( 'icon' ) ) {
				if ( strstr( $current->get( 'icon' ), 'font-' ) ) {
					$icon = json_decode( str_replace( "'", '"', $current->get( 'icon' ) ), true );
					if ( $current->param( 'icon-font-add-class' ) ) {
						$icon[ 'class' ] .= ' ' . $current->param( 'icon-font-add-class' );
					}
					$data[ 'icon' ] = [
						'_complex'    => 1,
						'_data'       => '',
						'_attributes' => $icon
					];
				}
				if ( SPFs::exists( Sobi::Cfg( 'images.category_icons' ) . '/' . $current->get( 'icon' ) ) ) {
					$data[ 'icon' ] = Sobi::FixPath( Sobi::Cfg( 'images.category_icons_live' ) . $current->get( 'icon' ) );
				}
			}
			$data[ 'meta' ] = [
				'description' => $current->get( 'metaDesc' ),
				'keys'        => $this->metaKeys( $current ),
				'author'      => $current->get( 'metaAuthor' ),
				'robots'      => $current->get( 'metaRobots' ),
			];
			$this->categoryFields( $data );
			$data[ 'entries_in_line' ] = $this->get( '$eInLine' );
			$data[ 'categories_in_line' ] = $this->get( '$cInLine' );
			$data[ 'number_of_subcats' ] = Sobi::Cfg( 'list.num_subcats', 6 );
			$this->menu( $data );
			$this->alphaMenu( $data );
			$data[ 'visitor' ] = $this->visitorArray( $visitor );
			if ( count( $categories ) ) {
				$this->loadNonStaticData( $categories );
				foreach ( $categories as $category ) {
					$cat = $this->category( $category );
					$data[ 'categories' ][] = [
						'_complex'    => 1,
						'_attributes' => [ 'id' => $cat[ 'id' ], 'nid' => $cat[ 'nid' ] ],
						'_data'       => $cat
					];
				}
				if ( strstr( $orderings[ 'categories' ], 'name' ) && Sobi::Cfg( 'lang.multimode', false ) ) {
					usort( $data[ 'categories' ], 'self::orderByName' );
					if ( $orderings[ 'categories' ] == 'name.desc' ) {
						$data[ 'categories' ] = array_reverse( $data[ 'categories' ] );
					}
				}
			}
			if ( count( $entries ) ) {
				$this->loadNonStaticData( $entries );
				$manager = Sobi::Can( 'entry', 'edit', '*', Sobi::Section() ) ? true : false;
				foreach ( $entries as $eid ) {
					$en = $this->entry( $eid, $manager );
					$data[ 'entries' ][] = [
						'_complex'    => 1,
						'_attributes' => [ 'id' => $en[ 'id' ], 'nid' => $en[ 'nid' ] ],
						'_data'       => $en
					];
				}
				if ( strstr( $orderings[ 'entries' ], 'name' ) && Sobi::Cfg( 'lang.multimode', false ) ) {
					usort( $data[ 'entries' ], 'self::orderByName' );
					if ( $orderings[ 'entries' ] == 'name.desc' ) {
						$data[ 'entries' ] = array_reverse( $data[ 'entries' ] );
					}
				}
				$this->navigation( $data );
			}
			$this->fixTimes( $data );
			$this->_attr = $data;
		}
		Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
	}

	protected function orderByName( $from, $to )
	{
		return strcasecmp( $from[ '_data' ][ 'name' ][ '_data' ], $to[ '_data' ][ 'name' ][ '_data' ] );
	}

	protected function categoryFields( &$data, $fields = [] )
	{
		if ( !( count( $fields ) ) ) {
			$fields = $this->get( 'fields' );
		}
		$css_debug = '';
		if ( $development = ( Sobi::Cfg( 'template.development', true ) && !defined( 'SOBIPRO_ADM' ) ) ) {
			$css_debug = ' development';
		}
		if ( count( $fields ) ) {
			foreach ( $fields as $field ) {
				$field->set( 'currentView', 'category' );
				$struct = $field->struct();
				$options = null;
				if ( isset( $struct[ '_options' ] ) ) {
					$options = $struct[ '_options' ];
					unset( $struct[ '_options' ] );
				}
				$data[ 'fields' ][ $field->get( 'nid' ) ] = [
					'_complex'    => 1,
					'_data'       => [
						'label' => [
							'_complex'    => 1,
							'_data'       => $field->get( 'name' ),
							'_attributes' => [ 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) ]
						],
						'data'  => $struct,
					],
					'_attributes' => [ 'id'        => $field->get( 'id' ),
					                   'itemprop'  => $field->get( 'itemprop' ),
					                   'type'      => $field->get( 'type' ),
					                   'suffix'    => $field->get( 'suffix' ),
					                   'position'  => $field->get( 'position' ),
					                   'css_view'  => $field->get( 'cssClassView' ) . $css_debug,
					                   'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' )
					]
				];
				if ( Sobi::Cfg( 'category.field_description', false ) ) {
					$data[ 'fields' ][ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = [ '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) ];
				}
				if ( $options ) {
					$data[ 'fields' ][ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
				}
				if ( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
					foreach ( $struct[ '_xml_out' ] as $k => $v )
						$data[ 'fields' ][ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
				}
			}
		}
	}

	/**
	 * @param string $type
	 * @param null $out
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
