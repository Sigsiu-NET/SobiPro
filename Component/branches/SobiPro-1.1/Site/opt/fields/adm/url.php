<?php
/**
 * @version: $Id: url.php 551 2011-01-11 14:34:26Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/fields/adm/url.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.url' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Sep-2009 11:37:11
 */
class SPField_UrlAdm extends SPField_Url {
	/**
	 * @var string
	 */
	public $cssClass = "inputbox";

	public function onFieldEdit()
	{
		$this->allowedProtocols =  is_array( $this->allowedProtocols ) ?  implode( ',', $this->allowedProtocols ) : null;
	}
	public function save( &$attr )
	{
		if( isset( $attr[ 'allowedProtocols' ] ) && $attr[ 'allowedProtocols' ]) {
			$attr[ 'allowedProtocols' ] = explode( ',', $attr[ 'allowedProtocols' ] );
            if (count($attr[ 'allowedProtocols' ] )) {
                foreach ($attr[ 'allowedProtocols' ] as $ap => $apvalue) {
                    $attr[ 'allowedProtocols' ][$ap] = trim($apvalue);
                }
            }
		}
        else {
            $attr[ 'allowedProtocols' ] = array();
        }
		$myAttr = $this->getAttr();
		$properties = array();
		if( count( $myAttr ) ) {
			foreach ( $myAttr as $property ) {
				$properties[ $property ] = isset( $attr[ $property ] ) ? ( $attr[ $property ] ) : null;
			}
		}
		$attr[ 'params' ] = $properties;
	}
}
