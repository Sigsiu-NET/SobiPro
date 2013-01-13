<?php
/**
 * @version: $Id: sobipro.php 1776 2011-08-08 11:16:09Z Radek Suski $
 * @package: SobiPro Component for Joomla!
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-08-08 13:16:09 +0200 (Mon, 08 Aug 2011) $
 * $Revision: 1776 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/sobipro.php $
 */
defined( '_JEXEC' ) || exit( 'Restricted access' );
define( 'SOBI_TESTS', false );
defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : ( version_compare( JVERSION, '1.6.0', 'ge' ) ? 'joomla16' : 'joomla15'  ) );
define( 'SOBIPRO', true );
define( 'SOBIPRO_ADM', true );
define( 'SOBI_TASK', 'task' );
if( JVERSION == 'joomla15') {
	define( 'SOBI_DEFLANG', JComponentHelper::getParams( 'com_languages' )->get( 'site', JFactory::getConfig()->getValue( 'config.language' ) ) );
}
else {
	define( 'SOBI_DEFLANG', JComponentHelper::getParams( 'com_languages' )->get( 'site', JFactory::getConfig()->get( 'config.language' ) ) );
}
define( 'SOBI_ACL', 'adm' );
define( 'SOBI_ROOT', JPATH_ROOT );
define( 'SOBI_MEDIA', implode( DS, array( JPATH_ROOT, 'media', 'sobipro' ) ) );
define( 'SOBI_MEDIA_LIVE', JURI::root().'/media/sobipro' );
define( 'SOBI_PATH', SOBI_ROOT . DS . 'components' . DS . 'com_sobipro' );
define( 'SOBI_ADM_PATH', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sobipro' );
$adm = str_replace( JPATH_ROOT, null, JPATH_ADMINISTRATOR );
define( 'SOBI_ADM_LIVE_PATH', $adm . '/components/com_sobipro' );
define( 'SOBI_ADM_FOLDER', $adm  );
define( 'SOBI_LIVE_PATH', 'components/com_sobipro' );
require_once ( SOBI_PATH.DS.'lib'.DS.'base'.DS.'fs'.DS.'loader.php' );
SPLoader::loadController( 'interface' );
SPLoader::loadClass( 'base.filter' );
SPLoader::loadClass( 'base.request' );
JHtml::_( 'behavior.tooltip' );
JHTML::_( 'behavior.modal' );
$class = SPLoader::loadController( 'adm.sobipro' );
$sobi = new $class( SPRequest::task() );
$sobi->execute();
