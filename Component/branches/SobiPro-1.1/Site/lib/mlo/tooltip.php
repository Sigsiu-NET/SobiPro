<?php
/**
 * @version: $Id: tooltip.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/mlo/tooltip.php $
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
?>