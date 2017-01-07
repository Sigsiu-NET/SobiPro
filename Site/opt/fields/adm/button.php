<?php
/**
 *  @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 *  @license GNU/GPL Version 3
 *  This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 *  as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 *  See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.button' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Sep-2009 11:37:11
 */
class SPField_ButtonAdm extends SPField_Button
{
	public function onFieldEdit()
	{
		$this->allowedProtocols = is_array( $this->allowedProtocols ) ? implode( ',', $this->allowedProtocols ) : null;
	}

	public function save( &$attr )
	{
		if ( isset( $attr[ 'allowedProtocols' ] ) && $attr[ 'allowedProtocols' ] && is_string( $attr[ 'allowedProtocols' ] ) ) {
			$attr[ 'allowedProtocols' ] = explode( ',', $attr[ 'allowedProtocols' ] );
			if ( count( $attr[ 'allowedProtocols' ] ) ) {
				foreach ( $attr[ 'allowedProtocols' ] as $ap => $apvalue ) {
					$attr[ 'allowedProtocols' ][ $ap ] = trim( $apvalue );
				}
			}
		}
		elseif ( !( is_array( $attr[ 'allowedProtocols' ] ) ) ) {
			$attr[ 'allowedProtocols' ] = [];
		}
		$myAttr = $this->getAttr();
		$properties = [];
		if ( count( $myAttr ) ) {
			foreach ( $myAttr as $property ) {
				$properties[ $property ] = isset( $attr[ $property ] ) ? ( $attr[ $property ] ) : null;
			}
		}
		$attr[ 'params' ] = $properties;
	}

}
