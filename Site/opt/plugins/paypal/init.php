<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2018 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

use Sobi\Input\Input;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 27-Nov-2009 17:10:15
 */
class SPPPaypal extends SPPlugin
{
	/* (non-PHPdoc)
	  * @see Site/lib/plugins/SPPlugin#provide($action)
	  */
	public function provide( $action )
	{
		return
			$action == 'PaymentMethodView'
			|| $action == 'AppPaymentMessageSend';
	}

	public static function admMenu( &$links )
	{
		$links[ Sobi::Txt( 'APP.PAYPAL' ) ] = 'paypal';
	}

	public function AppPaymentMessageSend( &$methods, $entry, &$payment, $html = false )
	{
		return $this->PaymentMethodView( $methods, $entry, $payment, !( $html ) );
	}

	/**
	 * This function have to add own string into the given array
	 * Basically: $methods[ $this->id ] = "Some String To Output";
	 * Optionally the value can be also SobiPro Arr2XML array.
	 * Check the documentation for more information
	 *
	 * @param array $methods
	 * @param SPEntry $entry
	 * @param array $payment
	 * @param bool $message
	 *
	 * @return void
	 */
	public function PaymentMethodView( &$methods, $entry, &$payment, $message = false )
	{
		$data = SPFactory::registry()
			->loadDBSection( 'paypal_' . Sobi::Section() )
			->get( 'paypal_' . Sobi::Section() );
		if ( !( count( $data ) ) ) {
			$data = SPFactory::registry()
				->loadDBSection( 'paypal' )
				->get( 'paypal' );
		}
		$cfg = SPLoader::loadIniFile( 'etc.paypal' );
		$rp = $cfg[ 'general' ][ 'replace' ];
		$to = ( $cfg[ 'general' ][ 'replace' ] == ',' ) ? '.' : ',';
		$vat       = Sobi::Cfg( 'payments.vat', 0 );

		if ($vat) {
			$amount = str_replace( $rp, $to, $payment[ 'summary' ][ 'sum_brutto' ] );
		}
		else {
			$amount = str_replace( $rp, $to, $payment[ 'summary' ][ 'sum_amount' ] );
		}

		//compatibility for existing sites
		if ( array_key_exists( 'ppcancel', $data ) ) {
			$ppcancel = SPLang::replacePlaceHolders( $data[ 'ppcancel' ][ 'value' ], $entry );
		}
		else {
			$ppcancel = $data[ 'pprurl' ][ 'value' ];
		}
		if ( array_key_exists( 'pploc', $data ) ) {
			$pploc = $data[ 'pploc' ][ 'value' ];
		}
		else {
			$pploc = 0;
		}

		$values = [ 'entry'    => $entry,
		            'amount'   => preg_replace( '/[^0-9\.,]/', null, $amount ),
		            'ppurl'    => SPLang::replacePlaceHolders( $data[ 'ppurl' ][ 'value' ], $entry ),
		            'ppemail'  => SPLang::replacePlaceHolders( $data[ 'ppemail' ][ 'value' ], $entry ),
		            'pprurl'   => SPLang::replacePlaceHolders( $data[ 'pprurl' ][ 'value' ], $entry ),
		            'ppcancel' => $ppcancel,
		            'ppcc'     => SPLang::replacePlaceHolders( $data[ 'ppcc' ][ 'value' ], $entry ),
		            'ppbn'     => SPLang::replacePlaceHolders( $data[ 'ppemail' ][ 'value' ], $entry ) . '_BuyNow_WPS_' . substr( Sobi::Lang(), 3, 2 ),
		            'pplang'   => str_replace( '-', '_', Sobi::Lang() ),
		            'pplc'     => substr( Sobi::Lang(), 3, 2 ),
		            'pploc'    => $pploc ];
		$expl = SPLang::replacePlaceHolders(
			SPLang::getValue( 'ppexpl', 'plugin', Sobi::Section() ),
			$values
		);
		$subject = SPLang::replacePlaceHolders(
			SPLang::getValue( 'ppsubject', 'plugin', Sobi::Section() ),
			$values
		);
		$values[ 'expl' ] = $expl;
		$values[ 'subject' ] = $subject;
		$values[ 'ip' ] = Input::Ip4();
		$methods[ $this->id ] = [ 'content' => ( $message ? $this->raw( $cfg, $values ) : $this->content( $cfg, $values ) ),
		                          'title'   => Sobi::Txt( 'APP.PPP.PAY_TITLE' ) ];
	}

	/**
	 * @param array $config
	 * @param array $values
	 *
	 * @return string
	 */
	private
	function raw( $config, $values )
	{
		$out = "\n";
		$out .= $values[ 'expl' ];
		$out .= Sobi::Txt( 'APP.PPP.PAY_TITLE' ) . ': ';
		$out .= $config[ 'message' ][ 'url' ];
		array_shift( $config[ 'message' ] );
		$v = [];
		foreach ( $config[ 'message' ] as $field => $value ) {
			$v[] = $field . '=' . urlencode( SPLang::replacePlaceHolders( $value, $values ) );
		}
		$out .= implode( '&', $v );

		return SPLang::clean( $out );
	}

	/**
	 * @param array $config
	 * @param array $values
	 *
	 * @return string
	 */
	private
	function content( $config, $values )
	{
		$out = "\n";
		$out .= $values[ 'expl' ];
		$out .= "\n";
		$out .= '<form action="' . $values[ 'ppurl' ] . '" method="post">' . "\n";
		foreach ( $config[ 'fields' ] as $field => $value ) {
			$out .= '<input name="' . $field . '" value="' . SPLang::replacePlaceHolders( $value, $values ) . '" type="hidden"/>' . "\n";
		}
		if ( $values[ 'pploc' ] == 1 ) {
			$img = SPLang::replacePlaceHolders( $config[ 'general' ][ 'image_localized' ], $values );
		}
		else {
			$img = SPLang::replacePlaceHolders( $config[ 'general' ][ 'image' ], $values );
		}
		$out .= '<input src="' . $img . '" name="submit" alt="Buy Now" type="image" border="0" />' . "\n";
		$out .= '</form>' . "\n";

		return $out;
//        return SPLang::clean($out);   //destroys form output
	}
}
