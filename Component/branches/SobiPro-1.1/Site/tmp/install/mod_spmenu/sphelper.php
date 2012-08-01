<?php
/**
 * @version: $Id: sphelper.php 966 2011-03-09 11:18:51Z Radek Suski $
 * @package: SobiPro Component for Joomla!
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-03-09 12:18:51 +0100 (Wed, 09 Mar 2011) $
 * $Revision: 966 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/tmp/install/mod_spmenu/sphelper.php $
 */

defined('_JEXEC') or die('Restricted access');

class modSpMenuHelper
{
	private static $loaded = false;
	public function renderMenu()
	{
		if( self::$loaded || !file_exists( implode( DS, array( JPATH_SITE, 'components', 'com_sobipro', 'lib', 'base', 'fs', 'loader.php' ) ) ) ) {
			return true;
		}
		self::$loaded = true;
		self::init();
		$menu = self::build();
		// for some reason the "class" index has to be within quotes. Otherwise IE screwing it up
		echo
		'
			<script type="text/javascript">
				var SPMenu = new Element( "li", { "class": "node" } );
				SPMenu.innerHTML = \''.$menu.'\';
				$( "menu" ).adopt( SPMenu );
			</script>
		';
	}

	private function build()
	{
		$out = '<a>SobiPro</a>';
		$out .= '<ul>';
		$out .= self::sections();
		$out .= '<li class="separator"><span></span></li>';
		$out .= self::node( Sobi::Txt( 'GB.CFG.GLOBAL_CONFIG' ), '&task=config', 'icon-16-config' );
		$out .= self::node( Sobi::Txt( 'ACL' ), '&task=acl', 'icon-16-user' );
		$out .= self::node( Sobi::Txt( 'EX.MANAGER' ), '&task=extensions', 'icon-16-install' );
		$out .= '</ul>';
		return $out;
	}

	private function sections()
	{
		$sections = self::getSections();
		$out = '<li class="node">';
		$out .= '<a class="icon-16-SobiPro_16" href="index.php?option=com_sobipro">'.Sobi::Txt( 'SECTIONS' ).'</a>';
		$out .= '<ul id="menu-banner" class="menu-component">';
		if( count( $sections ) ) {
			foreach ( $sections as $section ) {
				$out .= self::node( $section->get( 'name' ), '&sid='.$section->get( 'id' ) );
			}
		}
		$out .= '</ul>';
		$out .= '</li>';
		return $out;
	}

	private function getSections()
	{
		$sa = array();
		$db =& SPFactory::db();
		try {
			$db->select( '*', 'spdb_object', array( 'oType' => 'section' ) );
			$sections = $db->loadObjectList();
		}
		catch ( SPException $x ) {}
		if( count( $sections ) ) {
			SPLoader::loadClass( 'models.datamodel' );
			SPLoader::loadClass( 'models.dbobject' );
			SPLoader::loadModel( 'section' );
			foreach ( $sections as $section ) {
				if( Sobi::Can( 'section', 'access', 'valid', $section->id ) ) {
					$s = new SPSection();
					$s->extend( $section );
					$sa[] = $s;
				}
			}
		}
		return $sa;
	}

	private function node( $name, $url, $class = "icon-16-SobiPro_16" )
	{
		$url = 'index.php?option=com_sobipro'.$url;
		$out = '<li>';
		$out .= '<a class="'.$class.'" href="'.$url.'">'.addslashes( $name ).'</a>';
		$out .= '</li>';
		return $out;
	}

	private function init()
	{
		defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
		if( !( defined( 'SOBIPRO' ) ) ) {
			define( 'SOBI_CMS', 'joomla15' );
			define( 'SOBIPRO', true );
			define( 'SOBIPRO_ADM', true );
			define( 'SOBI_TASK', 'task' );
			define( 'SOBI_DEFLANG', JFactory::getLanguage()->getDefault() );
			define( 'SOBI_ACL', 'adm' );
			define( 'SOBI_ROOT', JPATH_ROOT );
			define( 'SOBI_PATH', SOBI_ROOT . DS . 'components' . DS . 'com_sobipro' );
			define( 'SOBI_ADM_PATH', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_sobipro' );
			$adm = str_replace( JPATH_ROOT, null, JPATH_ADMINISTRATOR );
			define( 'SOBI_ADM_LIVE_PATH', $adm . '/components/com_sobipro' );
			define( 'SOBI_LIVE_PATH', JURI::base().'components/com_sobipro/' );
			require_once ( SOBI_PATH . DS . 'lib' . DS . 'base' . DS . 'fs' . DS . 'loader.php' );
			/* load all needed classes */
			SPLoader::loadController( 'interface' );
			SPLoader::loadClass( 'base.filter' );
			SPLoader::loadClass( 'base.request' );
			SPLoader::loadClass( 'base.const' );
			SPLoader::loadClass( 'base.factory' );
			SPLoader::loadClass( 'base.object' );
			SPLoader::loadClass( 'base.filter' );
			SPLoader::loadClass( 'base.request' );
			SPLoader::loadClass( 'sobi' );
			SPLoader::loadClass( 'base.config' );
			SPLoader::loadClass( 'base.exception' );
			SPLoader::loadClass( 'cms.base.lang' );
			SPLoader::loadClass( 'mlo.input' );
			SPFactory::config()->set( 'live_site', JURI::root() );
		}
	}
}
?>