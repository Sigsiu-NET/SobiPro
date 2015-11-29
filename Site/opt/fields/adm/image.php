<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 *
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.image' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Nov-2009 20:06:47
 */
class SPField_ImageAdm extends SPField_Image
{
	public function save( &$attr )
	{
		if ( ( $attr[ 'resize' ] || $attr[ 'crop' ] ) && !( $attr[ 'resizeWidth' ] && $attr[ 'resizeHeight' ] ) ) {
			throw new SPException( SPLang::e( 'IMG_FIELD_RESIZE_NO_SIZE' ) );
		}
		parent::save( $attr );
	}
}
