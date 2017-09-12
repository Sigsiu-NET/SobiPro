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

SPLoader::loadView( 'view' );

/**
 * @author Radek Suski
 * @version 1.1
 * @created 10-Jan-2009 5:15:02 PM
 */
class SPEntryView extends SPFrontView implements SPView
{

	public function display()
	{
		$this->_task = $this->get( 'task' );
		switch ( $this->get( 'task' ) ) {
			case 'edit':
			case 'add':
				$this->edit();
				break;
			case 'details':
				$this->details();
				break;
		}
		parent::display();
	}

	protected function edit()
	{
		SPLoader::loadClass( 'html.tooltip' );
		$this->_type = 'entry_form';
		$id = $this->get( 'entry.id' );
		if ( $id ) {
			$this->addHidden( $id, 'entry.id' );
		}

		$type = $this->key( 'template_type', 'xslt' );
		if ( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if ( $type == 'xslt' ) {
			$data = $this->entryData( false );
			$fields = $this->get( 'fields' );
			$f = [];
			$css_debug = '';
			if ( $development = ( Sobi::Cfg( 'template.development', true ) && !defined( 'SOBIPRO_ADM' ) ) ) {
				$css_debug = ' development';
			}
			if ( count( $fields ) ) {
				foreach ( $fields as $field ) {
					if ( $field->enabled( 'form' ) ) {
						$pf = null;
						$pfm = null;
						if ( !( $field->get( 'isFree' ) ) && $field->get( 'fee' ) && !( Sobi::Can( 'entry.payment.free' ) ) ) {
							$pf = SPLang::currency( $field->get( 'fee' ) );
							$pfm = Sobi::Txt( 'EN.FIELD_NOT_FREE_MSG', [ 'fee' => $pf, 'fieldname' => $field->get( 'name' ) ] );
						}
						$f[ $field->get( 'nid' ) ] = [
							'_complex'    => 1,
							'_data'       => [
								'label'       => [
									'_complex'    => 1,
									'_data'       => $field->get( 'name' ),
									'_attributes' => [ 'lang' => Sobi::Lang( false ), 'show' => $field->__get( 'showEditLabel' ) ]
								],
								'data'        => [ '_complex' => 1, '_xml' => 1, '_data' => $field->field( true ) ],
								'description' => [ '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ), ],
								'fee'         => $pf,
								'fee_msg'     => $pfm
							],
							'_attributes' => [ 'id'        => $field->get( 'id' ),
							                   'type'      => $field->get( 'type' ),
							                   'suffix'    => $field->get( 'suffix' ),
							                   'position'  => $field->get( 'position' ),
							                   'required'  => $field->get( 'required' ),
							                   'css_edit'  => $field->get( 'cssClassEdit' ) . $css_debug,
							                   'width'     => $field->get( 'bsWidth' ),
							                   'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' )
							]
						];
					}
				}
			}
			$f[ 'save_button' ] = [
				'_complex' => 1,
				'_data'    => [
					'data' => [
						'_complex' => 1,
						'_xml'     => 1,
						'_data'    => SPHtml_Input::submit( 'save', Sobi::Txt( 'EN.SAVE_ENTRY_BT' ) ),
					],
				]
			];
			$f[ 'cancel_button' ] = [
				'_complex' => 1,
				'_data'    => [
					'data' => [
						'_complex' => 1,
						'_xml'     => 1,
						'_data'    => SPHtml_Input::button( 'cancel', Sobi::Txt( 'EN.CANCEL_BT' ), [ 'data-role' => 'cancel', 'class' => 'sobipro-cancel' ] ),
					],
				]
			];

			$data[ 'entry' ][ '_data' ][ 'fields' ] = [
				'_complex'    => 1,
				'_data'       => $f,
				'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
			];

			$this->_attr = $data;
			Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
		}
	}

	protected function details()
	{
		$this->_type = 'entry_details';
		$type = $this->key( 'template_type', 'xslt' );
		if ( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if ( $type == 'xslt' ) {
			$this->_attr = $this->entryData();
			SPFactory::header()->addCanonical( $this->_attr[ 'entry' ][ '_data' ][ 'url' ] );
			Sobi::Trigger( 'EntryView', ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
		}
	}

	protected function entryData( $getFields = true )
	{
		/** @var SPEntry $entry */
		$entry = $this->get( 'entry' );
		$visitor = $this->get( 'visitor' );
		$data = [];
		$section = SPFactory::Section( Sobi::Section() );
		$data[ 'section' ] = [
			'_complex'    => 1,
			'_data'       => Sobi::Section( true ),
			'_attributes' => [ 'id' => Sobi::Section(), 'lang' => Sobi::Lang( false ) ]
		];
		$data[ 'name' ] = [
			'_complex'    => 1,
			'_data'       => $section->get( 'efTitle' ),
			'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
		];
		$data[ 'description' ] = [
			'_complex'    => 1,
			'_data'       => $section->get( 'efDesc' ),
			'_attributes' => [ 'lang' => Sobi::Lang( false ) ]
		];

		$en = [];
		if ( $development = ( Sobi::Cfg( 'template.development', true ) && !defined( 'SOBIPRO_ADM' ) ) ) {
			$en[ 'development' ] = $development;
		}
		$en[ 'name' ] = [
			'_complex'    => 1,
			'_data'       => $entry->get( 'name' ),
			'_attributes' => [ 'lang' => Sobi::Lang( false ), 'type' => 'inbox', 'alias' => $entry->get( 'nameField' ) ]
		];
		$en[ 'created_time' ] = $entry->get( 'createdTime' );
		$en[ 'updated_time' ] = $entry->get( 'updatedTime' );
		$en[ 'valid_since' ] = $entry->get( 'validSince' );
		$en[ 'valid_until' ] = $entry->get( 'validUntil' );
		$en[ 'author' ] = $entry->get( 'owner' );
		$en[ 'counter' ] = $entry->get( 'counter' );
		$en[ 'approved' ] = $entry->get( 'approved' );

		$this->fixTimes( $en );

		//       $mytime = date( 'Y-m-d H:i:s', time());
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
		$en[ 'url' ] = Sobi::Url( [ 'pid' => $entry->get( 'parent' ), 'sid' => $entry->get( 'id' ), 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ) ], true, true, true );

		if ( Sobi::Can( 'entry', 'edit', '*' ) || ( ( Sobi::My( 'id' ) && Sobi::My( 'id' ) == $entry->get( 'owner' ) ) && Sobi::Can( 'entry', 'edit', 'own' ) ) ) {
			$en[ 'edit_url' ] = Sobi::Url( [ 'task' => 'entry.edit', 'sid' => $entry->get( 'id' ) ] );
		}
		if ( Sobi::Can( 'entry', 'manage', '*' ) ) {
			$en[ 'approve_url' ] = Sobi::Url( [ 'task' => ( $entry->get( 'approved' ) ? 'entry.unapprove' : 'entry.approve' ), 'sid' => $entry->get( 'id' ) ] );
		}
		if ( ( $entry->get( 'owner' ) == Sobi::My( 'id' ) && Sobi::Can( 'entry', 'delete', 'own' ) ) || Sobi::Can( 'entry', 'delete', '*' ) ) {
			$en[ 'delete_url' ] = Sobi::Url( [ 'task' => 'entry.delete', 'sid' => $entry->get( 'id' ) ] );
		}
		if ( Sobi::Can( 'entry', 'publish', '*' ) || ( ( Sobi::My( 'id' ) == $entry->get( 'owner' ) && Sobi::Can( 'entry', 'publish', 'own' ) ) ) ) {
			$en[ 'publish_url' ] = Sobi::Url( [ 'task' => ( $entry->get( 'state' ) ? 'entry.unpublish' : 'entry.publish' ), 'sid' => $entry->get( 'id' ) ] );
		}
		$cats = $entry->get( 'categories' );
		$categories = [];
		if ( count( $cats ) ) {
			$cn = SPLang::translateObject( array_keys( $cats ), [ 'name', 'alias' ], 'category' );
		}
		$primaryCat = $entry->get( 'parent' );
		foreach ( $cats as $cid => $cat ) {
			$cAttr = [ 'lang'     => Sobi::Lang( false ),
			           'id'       => $cat[ 'pid' ],
			           'alias'    => $cat [ 'alias' ],
			           'position' => $cat[ 'position' ],
			           'url'      => Sobi::Url( [ 'sid'   => $cat[ 'pid' ],
			                                      'title' => Sobi::Cfg( 'sef.alias', true ) ? $cat[ 'alias' ] : $cat[ 'name' ] ] )
			];
			if ( $cat[ 'pid' ] == $primaryCat ) {
				$cAttr[ 'primary' ] = 'true';
			}
			$categories[] = [
				'_complex'    => 1,
				'_data'       => SPLang::clean( isset( $cn[ $cid ][ 'value' ] ) ? $cn[ $cid ][ 'value' ] : $cn[ $cid ][ 'name' ] ),
				'_attributes' => $cAttr
			];
		}
		$en[ 'categories' ] = $categories;
		$en[ 'meta' ] = [
			'description' => $entry->get( 'metaDesc' ),
			'keys'        => $this->metaKeys( $entry ),
			'author'      => $entry->get( 'metaAuthor' ),
			'robots'      => $entry->get( 'metaRobots' ),
		];
		if ( $getFields ) {
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
				$en[ 'fields' ] = $this->fieldStruct( $fields, 'details' );
			}
		}
		$this->menu( $data );
		$this->alphaMenu( $data );
		$data[ 'entry' ] = [
			'_complex'    => 1,
			'_data'       => $en,
			'_attributes' => [ 'id' => $entry->get( 'id' ), 'nid' => $entry->get( 'nid' ), 'version' => $entry->get( 'version' ) ]
		];
		$data[ 'visitor' ] = $this->visitorArray( $visitor );

		return $data;
	}

}
