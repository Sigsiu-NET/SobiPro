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
SPLoader::loadClass( 'opt.fields.category' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Aug-2012 14:11:54
 */
class SPField_CategoryAdm extends SPField_Category
{
	/**
	 * @var string
	 */
	public $cssClass = "inputbox";

	public function save( &$attr )
	{
		parent::save( $attr );
		if ( $attr[ 'method' ] == 'fixed' ) {
			if ( !( $attr[ 'fixedCid' ] ) ) {
				throw new SPException( SPLang::e( 'FIELD_FIXED_CID_MISSING' ) );
			}
			else {
				$cids = explode( ',', $attr[ 'fixedCid' ] );
				if ( count( $cids ) ) {
					foreach ( $cids as $cid ) {
						$catId = (int)$cid;
						if ( !( $catId ) ) {
							throw new SPException( SPLang::e( 'FIELD_FIXED_CID_INVALID', $cid ) );
						}
						if ( $catId == Sobi::Section() ) {
							throw new SPException( SPLang::e( 'FIELD_FIXED_CID_INVALID', $cid ) );
						}
						else {
							$parents = SPFactory::config()->getParentPath( $catId );
							if ( !( isset( $parents[ 0 ] ) ) || $parents[ 0 ] != Sobi::Section() ) {
								throw new SPException( SPLang::e( 'FIELD_FIXED_CID_INVALID_SECTION', $catId ) );
							}
						}
					}
				}
				else {
					throw new SPException( SPLang::e( 'FIELD_FIXED_CID_MISSING' ) );
				}
			}
		}
	}
}
