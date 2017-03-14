<?php
/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
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
	const URI = 'https://www.Sigsiu.NET';

	public function __construct( $wdsl = null, $options = [] )
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
