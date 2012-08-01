<?php
/**
 * @version: $Id: soap.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/services/soap.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 22-Jun-2010 15:55:21
 */
class SPSoapClient extends SoapClient
{
	const UA = 'SobiPro Soap Client';
	const URI = 'http://www.Sigsiu.NET';

	public function __construct( $wdsl, $options = array() )
	{
		if( !( isset( $options[ 'user_agent' ] ) ) ) {
			$options[ 'user_agent' ] = self::UA;
		}
		if( !( isset( $options[ 'uri' ] ) ) ) {
			$options[ 'uri' ] = self::URI;
		}
		$options[  'trace' ] = 1;
		$options[  'exceptions' ] = 0;
		parent::SoapClient( $wdsl, $options );
	}
}
?>