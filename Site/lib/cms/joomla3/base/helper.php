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
require_once dirname( __FILE__ ) . '/../../joomla16/base/helper.php';

/**
 * @author Radek Suski
 * @version 1.0
 * @created Mon, Jan 14, 2013 13:25:51
 */
class SPCMSHelper3 extends SPCMSHelper
{
	public static function & getInstance()
	{
		static $SPCMSHelper = false;
		if ( !$SPCMSHelper || !( $SPCMSHelper instanceof self ) ) {
			$SPCMSHelper = new self();
		}
		return $SPCMSHelper;
	}

	/**
	 * Return min or recommend Joomla! version
	 * @param $recommended
	 * @return array
	 */
	public static function minCmsVersion( $recommended = false )
	{
		$updater = JUpdater::getInstance();
		$updater->findUpdates( 700, 0 );
		$version = SPFactory::db()
				->select( 'version', '#__updates', [ 'extension_id' => 700 ] )
				->loadResult();
		$recommendedVersion = [ 'major' => 3, 'minor' => 2, 'build' => 3 ];
		if ( $version ) {
			$version = explode( '.', $version );
			$recommendedVersion = [ 'major' => $version[ 0 ], 'minor' => $version[ 1 ], 'build' => $version[ 2 ] ];
		}
		return $recommended ? $recommendedVersion : [ 'major' => 3, 'minor' => 2, 'build' => 0 ];
	}

	/**
	 * Returns Joomla! version
	 * @param bool $version
	 * @return array
	 */
	public static function cmsVersion( $version = null )
	{
		if ( ( $version ) ) {
			return 'Joomla 3.x';
		}
		$version = new JVersion();
		$v = explode( '.', $version->RELEASE );
		return [ 'major' => $v[ 0 ], 'minor' => $v[ 1 ], 'build' => $version->DEV_LEVEL, 'rev' => 0 ];
	}

	/**
	 * Returns specified Joomla! configuration setting
	 * @param string $setting
	 * @return string
	 */
	public static function cmsSetting( $setting )
	{
		static $cfg;
		if ( !$cfg ) {
			$cfg = new JConfig(); // was ein Unsinn der da macht
		}
		switch ( $setting ) {
			case 'charset':
				$r = JFactory::getDocument()->getCharset();
				break;
			default:
				$r = isset( $cfg->$setting ) ? $cfg->$setting : false;
				break;
		}
		return $r;
	}

	/**
	 * @param $name
	 * @param $active
	 * @param $nouser
	 * @param $javascript
	 * @param $order
	 * @param $reg
	 * @return unknown_type
	 */
	public static function userSelect( $name, $active, $nouser = 0, $javascript = null, $order = 'name', $reg = 0 )
	{
		require_once 'userform.php';
		$s = [ 'id' => str_replace( '.', '_', $name ), 'name' => $name, 'value' => $active ];
		$f = new SPFormFieldUser();
		$f->setupData( $s );
		return $f->input;
	}
}
