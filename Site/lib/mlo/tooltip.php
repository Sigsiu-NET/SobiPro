<?php
/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
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
	 * @param int $width
	 * @param string $image
	 * @param string $text
	 * @param string $href
	 * @param bool $link
	 * @param string $position
	 * @return string
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
	 * @return string
	 */
	public static function _( $tooltip, $title, $img = null )
	{
		Sobi::Trigger( 'Tooltip', 'Show', array( &$tooltip, &$title ) );
		return self::toolTip( $tooltip, $title, $img, null, $title );
	}
}
