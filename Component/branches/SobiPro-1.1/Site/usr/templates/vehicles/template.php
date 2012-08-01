<?php
/**
 * @version: $Id: template.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/usr/templates/vehicles/template.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Oct-2010 10:39:33
 */
abstract class TplFunctions
{
	public static function Txt( $txt )
	{
		return Sobi::Txt( $txt );
	}

	public static function Tooltip( $tooltip, $title = null )
	{
		return SPTooltip::_( $tooltip, $title );
	}

	public static function Cfg(  $key, $def = null, $section = 'general'  )
	{
		return Sobi::Cfg( $key, $def, $section );
	}
}
?>