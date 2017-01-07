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
 * @created 10-Jan-2009 4:41:10 PM
 */
abstract class SPTooltip
{
	/**
	 * legacy function
	 * @deprecated
	 * @param string $tooltip
	 * @param string $title
	 * @param string $image
	 * @param int $width
	 * @param string $text
	 * @param string $href
	 * @param string $target
	 * @return string
	 * @internal param bool $link
	 * @internal param string $position
	 */
	public static function toolTip( $tooltip, $title = null, $image = null, $width = null, $text = null, $href = null, $target = '_blank' )
	{
		$class = SPFactory::config()->key( 'html.tooltip_class', 'editlinktip hasTip' );
		while ( strstr( $text, "\'" ) ) {
			$text = stripcslashes( $text );
		}
		while ( strstr( $text, "\'" ) ) {
			$text = stripcslashes( $text );
		}
		while ( strstr( $title, "\'" ) ) {
			$title = stripcslashes( $title );
		}
		if ( !$text || $image ) {
			$tip = "<img src=\"{$image}\" alt=\"{$title}\"/>";
			$tip = "<span class=\"{$class}\" title=\"{$title}::{$tooltip}\">{$tip}</span>";
		}
		else {
			$tip = "<span class=\"{$class}\" title=\"{$title}::{$tooltip}\">{$text}</span>";
		}
		if( $href ) {
			$tip = "<a href=\"{$href}\" target=\"{$target}\">{$tip}</a>";
		}
		return $tip;
	}

	/**
	 * @param string $tooltip
	 * @param string $title
	 * @param null $img
	 * @return string
	 */
	public static function _( $tooltip, $title, $img = null )
	{
		Sobi::Trigger( 'Tooltip', 'Show', [ &$tooltip, &$title ] );
		return self::toolTip( $tooltip, $title, $img, null, $title );
	}
}
