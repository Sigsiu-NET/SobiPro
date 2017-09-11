<?php
/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
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
 * @created 10-Jan-2009 5:15:02 PM
 */

class SPPaymentView extends SPFrontView implements SPView
{

	public function display()
	{
		$this->_task = $this->get( 'task' );
		switch ( $this->_task ) {
			case 'submit':
				$this->submit();
				parent::display();
				break;
			case 'payment':
				$this->save();
				parent::display();
				break;
		}
	}

	private function payment()
	{
		$this->_type = 'payment_details';
		$id = $this->get( 'entry' )->get( 'id' );

		$data = $this->get( 'pdata' );
		if ( !( $data ) ) {
			$data = SPFactory::payment()->summary( $id );
		}

		$positions = [];
		$xml = [];
		$this->menu( $xml );
		if ( $development = (Sobi::Cfg( 'template.development', true ) && !defined( 'SOBIPRO_ADM' )) ) {
			$xml[ 'development' ] = $development;
		}
		if ( count( $data[ 'positions' ] ) ) {
			foreach ( $data[ 'positions' ] as $position ) {
				$ref = $position[ 'reference' ];
				unset( $position[ 'reference' ] );
				$positions[ ] = [
					'_complex' => 1,
					'_data' => $ref,
					'_attributes' => $position
				];
			}
			$xml[ 'positions' ] = $positions;
		}
		if ( isset( $data[ 'discount' ] ) && count( $data[ 'discount' ] ) ) {
			$xml[ 'discount' ] = [
				'_complex' => 1,
				'_attributes' => $data[ 'discount' ]
			];
		}
		$xml[ 'summary' ] = [
			'_complex' => 1,
			'_attributes' => $data[ 'summary' ]
		];

		$xml[ 'section' ] = [
			'_complex' => 1,
			'_data' => Sobi::Section( true ),
			'_attributes' => [ 'id' => Sobi::Section(), 'lang' => Sobi::Lang( false ) ]
		];

		if ( ( $id ) ) {
			$entry = $this->get('entry');
			$xml['entry'] = [
				'_complex' => 1,
				'_data' => $entry->get( 'name' ),
				'_attributes' => [ 'lang' => Sobi::Lang( false ),
					 'url' => Sobi::Url( [ 'pid' => $entry->get( 'parent' ), 'sid' => $entry->get( 'id' ), 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' ) ], true, true, true ),
					 'sid' => $entry->get( 'id' ),
					 'title' => Sobi::Cfg( 'sef.alias', true ) ? $entry->get( 'nid' ) : $entry->get( 'name' )
				]
			];

		}
		Sobi::Trigger( 'PaymentView', ucfirst( __FUNCTION__ ), [ &$xml ] );
		return $xml;
	}

	private function save()
	{
		$data = $this->get( 'pdata' );
		if ( !( $data ) ) {
			$data = SPFactory::payment()->summary();
		}
		$methods = SPFactory::payment()->getMethods( $this->get( 'entry' ), $data );
		$visitor = $this->get( 'visitor' );
		$xml = $this->payment();
		if ( count( $methods ) ) {
			$xml[ 'payment_methods' ] = [];
			foreach ( $methods as $mid => $mout ) {
				$params = [];
				if ( is_array( $mout ) ) {
					$params = $mout;
					$mout = $mout[ 'content' ];
					unset( $params[ 'content' ] );
				}
				$xml[ 'payment_methods' ][ $mid ] = [
					'_complex' => 1,
					'_xml' => 1,
					'_data' => $mout,
					'_attributes' => $params
				];
			}
		}
		$xml[ 'visitor' ] = $this->visitorArray( $visitor );
		$this->_attr = $xml;
		Sobi::Trigger( 'PaymentView', ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
	}

	private function submit()
	{
		$xml = $this->payment();
		$visitor = $this->get( 'visitor' );
		$id = $this->get( 'entry' )->get( 'id' );
		SPLoader::loadClass( 'mlo.input' );
		if ( $id ) {
			$saveUrl = Sobi::Url( [ 'task' => 'entry.save', 'pid' => Sobi::Reg( 'current_section' ), 'sid' => $id ], false, false );
			$backUrl = Sobi::Url( [ 'task' => 'entry.edit', 'pid' => Sobi::Reg( 'current_section' ), 'sid' => $id ] );
		}
		else {
			$saveUrl = Sobi::Url( [ 'task' => 'entry.save', 'pid' => Sobi::Reg( 'current_section' ) ], false, false );
			$backUrl = Sobi::Url( [ 'task' => 'entry.add', 'pid' => Sobi::Reg( 'current_section' ) ] );
		}
		$xml[ 'buttons' ][ 'save_button' ] = [
			'_complex' => 1,
			'_data' => [
				'data' => [
					'_complex' => 1,
					'_xml' => 1,
					'_data' =>
					SPHtml_Input::button(
						'save',
						Sobi::Txt( 'EN.PAYMENT_SAVE_ENTRY_BT' ),
						[ 'href' => $saveUrl ]
					)
				],
			]
		];
		$xml[ 'buttons' ][ 'back_button' ] = [
			'_complex' => 1,
			'_data' => [
				'data' => [
					'_complex' => 1,
					'_xml' => 1,
					'_data' =>
					SPHtml_Input::button(
						'back',
						Sobi::Txt( 'EN.PAYMENT_BACK_BT' ),
						[ 'href' => $backUrl ]
					)
				],
			]
		];
		$xml[ 'buttons' ][ 'cancel_button' ] = [
			'_complex' => 1,
			'_data' => [
				'data' => [
					'_complex' => 1,
					'_xml' => 1,
					'_data' => SPHtml_Input::submit( 'save', Sobi::Txt( 'EN.CANCEL_BT' ) ),
					'_data' =>
					SPHtml_Input::button(
						'cancel',
						Sobi::Txt( 'EN.CANCEL_BT' ),
						[ 'href' => Sobi::Url( [ 'task' => 'entry.cancel', 'pid' => Sobi::Reg( 'current_section' ) ] ) ]
					)
				],
			]
		];
		$xml[ 'save_url' ] = $saveUrl;
		$xml[ 'visitor' ] = $this->visitorArray( $visitor );
		$this->_attr = $xml;
		Sobi::Trigger( 'PaymentView', ucfirst( __FUNCTION__ ), [ &$this->_attr ] );
	}
}
