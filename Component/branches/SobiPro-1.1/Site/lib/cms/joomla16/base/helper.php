<?php
/**
 * @version: $Id: helper.php 2193 2012-01-28 12:34:01Z Radek Suski $
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
 * $Date: 2012-01-28 13:34:01 +0100 (Sat, 28 Jan 2012) $
 * $Revision: 2193 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla16/base/helper.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname(__FILE__).'/../../joomla_common/base/helper.php';
/**
 * @author Radek Suski
 * @version 1.0
 * @created 08-Jul-2008 9:43:25 AM
 */
class SPCMSHelper extends SPJoomlaCMSHelper
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
	 * @return unknown_type
	 */
	public static function minCmsVersion( $recommended = false )
	{
		return $recommended ? array( 'major' => 2, 'minor' => 5, 'build' => 0 ) : array( 'major' => 1, 'minor' => 7, 'build' => 3 );
	}

	/**
	 * Returns Joomla! version
	 * @param bool $version
	 * @return array
	 */
	public static function cmsVersion( $version = null )
	{
		if( ( $version ) && !( in_array( $version, array( 'Joomla 1.6', 'Joomla 1.7' ) ) ) ) {
			return 'Joomla 1.6+';
		}
		$version = new JVersion();
		$v = explode( '.', $version->RELEASE );
		return array( 'major' => $v[ 0 ], 'minor' => $v[ 1 ], 'build' => $version->DEV_LEVEL, 'rev' => 0 );
	}

	/**
	 * Returns specified Joomla! configuration setting
	 * @param string $setting
	 * @return string
	 */
	public static function cmsSetting( $setting )
	{
		static $cfg;
		if( !$cfg ) {
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
		$s = array( 'id' => str_replace( '.', '_', $name ), 'name' => $name, 'value' => $active );
		$f = new SPFormFieldUser();
		$f->setup( $s );
		return $f->input;
	}
}
