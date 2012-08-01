<?php
/**
 * @version: $Id: txt.php 609 2011-01-14 08:52:29Z Radek Suski $
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
 * $Date: 2011-01-14 09:52:29 +0100 (Fri, 14 Jan 2011) $
 * $Revision: 609 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/txt.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'txt' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 15-Jul-2010 18:17:28
 */
class SPJsTxtAdm extends SPJsTxt
{
	protected function js()
	{
		$lang = SPLang::jsLang( true );
		if( count( $lang ) ) {
			foreach ( $lang as $term => $text ) {
				unset( $lang[ $term ] );
				$term = str_replace( 'SP.JS_', null, $term );
				$lang[ $term ] = $text;
			}
		}
		if( !( SPRequest::int( 'deb' ) ) ) {
			SPFactory::mainframe()->cleanBuffer();
			header( 'Content-type: text/javascript' );
		}
		echo 'SobiPro.setLang( '.json_encode( $lang ).' );';
		exit;
	}
}
?>