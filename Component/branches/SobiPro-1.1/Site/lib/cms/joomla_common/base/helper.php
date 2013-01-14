<?php
/**
 * @version: $Id: helper.php 2327 2012-03-27 16:17:04Z Sigrid Suski $
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
 * $Date: 2012-03-27 18:17:04 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2327 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla_common/base/helper.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 08-Jul-2008 9:43:25 AM
 */
class SPJoomlaCMSHelper
{
	/**
	 * @return SPJoomlaCMSHelper
	 */
	public static function & getInstance()
	{
		static $SPCMSHelper = false;
		if ( !$SPCMSHelper || !( $SPCMSHelper instanceof SPCMSHelper ) ) {
			$SPCMSHelper = new SPCMSHelper();
		}
		return $SPCMSHelper;
	}

	/**
	 * Return SobiPro version
	 * @param bool $str
	 * @return array or string
	 */
	public static function myVersion( $str = false )
	{
		static $ver = array();
		if( !isset( $ver[ $str ] ) ) {
			$def = SOBI_CMS == 'joomla15' ? 'sobipro.xml' : 'com_sobipro.xml';
			$doc = DOMDocument::load( Sobi::FixPath( SOBI_ADM_PATH.DS.$def ) );
			if( $str ) {
				$ver[ $str ] = $doc->getElementsByTagName( 'version' )->item( 0 )->nodeValue;
                $codename = $doc->getElementsByTagName( 'codename' )->item( 0 )->nodeValue;
                $ver[ $str ] = $ver[ $str ].' [ '.$codename.' ]';
			}
			else {
				$v = explode( '.', $doc->getElementsByTagName( 'version_number' )->item( 0 )->nodeValue );
				$ver[ $str ] = array( 'major' => $v[ 0 ], 'minor' => ( isset( $v[ 1 ] ) ? $v[ 1 ] : 0 ), 'build' => ( isset( $v[ 2 ] ) ? $v[ 2 ] : 0 ), 'rev' => ( isset( $v[ 3 ] ) ? $v[ 3 ] : 0 ) );
			}
		}
		return $ver[ $str ];
	}

	/**
	 * Return min or recommend Joomla! version
	 * @param $recommened
	 * @return unknown_type
	 */
	public static function minCmsVersion( $recommended = false )
	{
		return $recommended ? array( 'major' => 1, 'minor' => 5, 'build' => 26, 'rev' => 0 ) : array( 'major' => 1, 'minor' => 5, 'build' => 20, 'rev' => 0 );
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
			$cfg = new JConfig();
		}
		switch ( $setting ) {
			case 'charset':
				$r = JFactory::getDocument()->getMetaData( 'content-type', true );
				$r = explode( '=', $r );
				$r = $r[ 1 ];
				break;
			default:
				$r = isset( $cfg->$setting ) ? $cfg->$setting : false;
				break;
		}
		return $r;
	}

	/**
	 * Returns Joomla! version
	 * @param bool $version
	 * @return array
	 */
	public static function cmsVersion( $version = null )
	{
		if( ( $version ) && ( $version != 'Joomla 1.5' ) ) {
			return 'Joomla 1.5';
		}
		$version = new JVersion();
		$v = explode( '.', $version->RELEASE );
		return array( 'major' => $v[ 0 ], 'minor' => $v[ 1 ], 'build' => $version->DEV_LEVEL, 'rev' => 0 );
	}

	/**
	 * @param array $files
	 * @return bool
	 */
	public static function installerFile( $files )
	{
		foreach ( $files as $file ) {
			$def = DOMDocument::load( $file, LIBXML_NOERROR );
			if( in_array( trim( $def->documentElement->tagName ), array( 'install', 'extension' ) ) ) {
				if( $def->getElementsByTagName( 'SobiPro' )->length ) {
					if( in_array( trim( $def->documentElement->getAttribute( 'type' ) ), array( 'language', 'module', 'plugin', 'component' ) ) ) {
						return $def;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Called from App installer if the core installer has no handler for this type
	 * @todo
	 * @param $files
	 * @param $dir
	 * @return string
	 */
	public static function install( $files, $dir )
	{
		return SPFactory::Instance( 'cms.base.installer' )
				->install( self::installerFile( $files ), $files, $dir );
	}

	/**
	 * Install language file
	 * @param string $lang
	 * @param bool $force
	 * @return array
	 */
	public static function installLang( $lang, $force = true )
	{
		$log = array();
		if( count( $lang ) ) {
			foreach ( $lang as $language => $files ) {
				$language = str_replace( '_', '-', $language );
				if( count( $files ) ) {
					foreach ( $files as $file ) {
						$target = $file[ 'adm' ] ? implode( DS, array( JPATH_ADMINISTRATOR, 'language', $language ) ) : implode( DS, array( SOBI_ROOT, 'language', $language ) );
						if( $force || SPFs::exists( $target ) ) {
							$iFile = $target.'/'.trim( $file[ 'name' ] );
							$log[] = $iFile;
							SPFs::copy( Sobi::FixPath( $file[ 'path' ] ), $iFile );
						}
					}
				}
			}
		}
		return $log;
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
		return JHTML::_( 'list.users', $name, $active, $nouser, $javascript, $order, $reg );
	}

	public static function availableLanguages( $list = false )
	{
		$langs = JFactory::getLanguage()->getKnownLanguages();
		if( $list ) {
			$llist = array();
			foreach ( $langs as $i => $value ) {
				$llist[ $i ] = $value[ 'name' ];
			}
			return $llist;
		}
		return $langs;
	}

	/**
	 * Returns Joomla! depend additional path with alternative templates loaction
	 * @return array
	 */
	public function templatesPath()
	{
		SPLoader::loadClass( 'base.fs.directory_iterator' );
		$jTemplates = new SPDirectoryIterator( SPLoader::dirPath( 'templates', 'root', true ) );
		$tr = array();
		foreach ( $jTemplates as $template ) {
			if( $template->isDot()) {
				continue;
			}
			if( $template->isDir() ) {
				if( file_exists( implode( DS, array( $template->getPathname(), 'html', 'com_sobipro' ) ) ) && file_exists( implode( DS, array( $template->getPathname(), 'templateDetails.xml' ) ) ) ) {
					$data = new DOMDocument( '1.0', 'utf-8' );
					$data->load( Sobi::FixPath( $template->getPathname() . DS . 'templateDetails.xml' ) );
					$name = $data->getElementsByTagName( 'name' )->item( 0 )->nodeValue;
					$tr[ $name ] = Sobi::FixPath( implode( DS, array( $template->getPathname(), 'html', 'com_sobipro' ) ) );
				}
			}
		}
		return array( 'name' => Sobi::Txt( 'TP.TEMPLATES_OVERRIDE' ), 'icon' => Sobi::Cfg( 'live_site' ).'media/sobipro/tree/joomla.gif', 'data' => $tr );
	}
}
