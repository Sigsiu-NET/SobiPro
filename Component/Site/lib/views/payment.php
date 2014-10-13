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
		$positions = array();
		$xml = array();
		$this->menu( $xml );
		if ( count( $data[ 'positions' ] ) ) {
			foreach ( $data[ 'positions' ] as $position ) {
				$ref = $position[ 'reference' ];
				unset( $position[ 'reference' ] );
				$positions[ ] = array(
					'_complex' => 1,
					'_data' => $ref,
					'_attributes' => $position
				);
			}
			$xml[ 'positions' ] = $positions;
		}
		if ( isset( $data[ 'discount' ] ) && count( $data[ 'discount' ] ) ) {
			$xml[ 'discount' ] = array(
				'_complex' => 1,
				'_attributes' => $data[ 'discount' ]
			);
		}
		$xml[ 'summary' ] = array(
			'_complex' => 1,
			'_attributes' => $data[ 'summary' ]
		);
		Sobi::Trigger( 'PaymentView', ucfirst( __FUNCTION__ ), array( &$xml ) );
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
			$xml[ 'payment_methods' ] = array();
			foreach ( $methods as $mid => $mout ) {
				$params = array();
				if ( is_array( $mout ) ) {
					$params = $mout;
					$mout = $mout[ 'content' ];
					unset( $params[ 'content' ] );
				}
				$xml[ 'payment_methods' ][ $mid ] = array(
					'_complex' => 1,
					'_xml' => 1,
					'_data' => $mout,
					'_attributes' => $params
				);
			}
		}
		$xml[ 'visitor' ] = $this->visitorArray( $visitor );
		$this->_attr = $xml;
		Sobi::Trigger( 'PaymentView', ucfirst( __FUNCTION__ ), array( &$this->_attr ) );
	}

	private function submit()
	{
		$xml = $this->payment();
		$visitor = $this->get( 'visitor' );
		$id = $this->get( 'entry' )->get( 'id' );
		SPLoader::loadClass( 'mlo.input' );
		if ( $id ) {
			$saveUrl = Sobi::Url( array( 'task' => 'entry.save', 'pid' => Sobi::Reg( 'current_section' ), 'sid' => $id ), false, false );
			$backUrl = Sobi::Url( array( 'task' => 'entry.edit', 'pid' => Sobi::Reg( 'current_section' ), 'sid' => $id ) );
		}
		else {
			$saveUrl = Sobi::Url( array( 'task' => 'entry.save', 'pid' => Sobi::Reg( 'current_section' ) ), false, false );
			$backUrl = Sobi::Url( array( 'task' => 'entry.add', 'pid' => Sobi::Reg( 'current_section' ) ) );
		}
		$xml[ 'buttons' ][ 'save_button' ] = array(
			'_complex' => 1,
			'_data' => array(
				'data' => array(
					'_complex' => 1,
					'_xml' => 1,
					'_data' =>
					SPHtml_Input::button(
						'save',
						Sobi::Txt( 'EN.PAYMENT_SAVE_ENTRY_BT' ),
						array( 'href' => $saveUrl )
					)
				),
			)
		);
		$xml[ 'buttons' ][ 'back_button' ] = array(
			'_complex' => 1,
			'_data' => array(
				'data' => array(
					'_complex' => 1,
					'_xml' => 1,
					'_data' =>
					SPHtml_Input::button(
						'back',
						Sobi::Txt( 'EN.PAYMENT_BACK_BT' ),
						array( 'href' => $backUrl )
					)
				),
			)
		);
		$xml[ 'buttons' ][ 'cancel_button' ] = array(
			'_complex' => 1,
			'_data' => array(
				'data' => array(
					'_complex' => 1,
					'_xml' => 1,
					'_data' => SPHtml_Input::submit( 'save', Sobi::Txt( 'EN.CANCEL_BT' ) ),
					'_data' =>
					SPHtml_Input::button(
						'cancel',
						Sobi::Txt( 'EN.CANCEL_BT' ),
						array( 'href' => Sobi::Url( array( 'task' => 'entry.cancel', 'pid' => Sobi::Reg( 'current_section' ) ) ) )
					)
				),
			)
		);
		$xml[ 'save_url' ] = $saveUrl;
		$xml[ 'visitor' ] = $this->visitorArray( $visitor );
		$this->_attr = $xml;
		Sobi::Trigger( 'PaymentView', ucfirst( __FUNCTION__ ), array( &$this->_attr ) );
	}
}
