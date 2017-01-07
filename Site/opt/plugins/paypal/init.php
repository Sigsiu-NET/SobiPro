<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

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
                $action == 'PaymentMethodView' ||
                $action == 'AppPaymentMessageSend';
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
	 * @param array $methods
	 * @param SPEntry $entry
	 * @param array $payment
	 * @param bool $message
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
        $amount = str_replace( $rp, $to, $payment[ 'summary' ][ 'sum_brutto' ] );
        $values = [
            'entry' => $entry,
            'amount' => preg_replace( '/[^0-9\.,]/', null, $amount ),
            'ppurl' => SPLang::replacePlaceHolders( $data[ 'ppurl' ][ 'value' ], $entry ),
            'ppemail' => SPLang::replacePlaceHolders( $data[ 'ppemail' ][ 'value' ], $entry ),
            'pprurl' => SPLang::replacePlaceHolders( $data[ 'pprurl' ][ 'value' ], $entry ),
            'ppcc' => SPLang::replacePlaceHolders( $data[ 'ppcc' ][ 'value' ], $entry ),
        ];
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
        $values[ 'ip' ] = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
        $methods[ $this->id ] = [
            'content' => ( $message ? $this->raw( $cfg, $values ) : $this->content( $cfg, $values ) ),
            'title' => Sobi::Txt( 'APP.PPP.PAY_TITLE' )
        ];
    }

    /**
     * @param array $config
     * @param array $values
     * @return string
     */
    private function raw( $config, $values )
    {
        $out = "\n";
        $out .= $values[ 'expl' ];
        $out .= Sobi::Txt( 'APP.PPP.PAY_TITLE' ) . ': ';
        $out .= $config[ 'message' ][ 'url' ];
        array_shift( $config[ 'message' ] );
        $v = [];
        foreach ( $config[ 'message' ] as $field => $value ) {
            $v[ ] = $field . '=' . urlencode( SPLang::replacePlaceHolders( $value, $values ) );
        }
        $out .= implode( '&', $v );
        return SPLang::clean($out);
    }

    /**
     * @param array $config
     * @param array $values
     * @return string
     */
    private function content( $config, $values )
    {
        $out = "\n";
        $out .= $values[ 'expl' ];
        $out .= "\n";
        $out .= '<form action="' . $values[ 'ppurl' ] . '" method="post">' . "\n";
        foreach ( $config[ 'fields' ] as $field => $value ) {
            $out .= '<input name="' . $field . '" value="' . SPLang::replacePlaceHolders( $value, $values ) . '" type="hidden"/>' . "\n";
        }
        $img = SPLang::replacePlaceHolders( $config[ 'general' ][ 'image' ] );
        $out .= '<input src="' . $img . '" name="submit" alt="" type="image"/>' . "\n";
        $out .= '</form>' . "\n";
        return SPLang::clean($out);
    }
}
