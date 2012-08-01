<?php
/**
 * @version: $Id: init.php 2612 2012-07-20 13:37:23Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-07-20 15:37:23 +0200 (Fri, 20 Jul 2012) $
 * $Revision: 2612 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/plugins/bank_transfer/init.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 27-Nov-2009 17:10:15
 */
class SPPBankTransfer extends SPPlugin
{
	/* (non-PHPdoc)
	 * @see Site/lib/plugins/SPPlugin#provide($action)
	 */
	public function provide( $action )
	{
		return
			$action == 'PaymentMethodView' ||
			$action == 'AppPaymentMessageSend'
		;
	}

	public function AppPaymentMessageSend( &$methods, $entry, &$payment )
	{
		return $this->PaymentMethodView( $methods, $entry, $payment );
	}

	public static function admMenu( &$links )
	{
		$links[ Sobi::Txt( 'APP.BANK_TRANSFER' ) ] = 'bank_transfer';
	}

	/**
	 * This function have to add own string into the given array
	 * Basically: $methods[ $this->id ] = "Some String To Output";
	 * Optionaly the value can be also SobiPro Arr2XML array.
	 * Check the documentation for more information
	 * @param array $methods
	 * @param SPEntry $entry
	 * @param array $payment
	 * @return void
	 */
	public function PaymentMethodView( &$methods, $entry, &$payment )
	{
		$bankdata = SPLang::getValue( 'bankdata', 'plugin', Sobi::Section() );
		$bankdata = SPLang::replacePlaceHolders( $bankdata, array( 'entry' => $entry ) );
		$methods[ $this->id ] = array(
			'content' => $bankdata,
			'title' => Sobi::Txt( 'APP.PBT.PAY_TITLE' )
		);
	}
}
?>
