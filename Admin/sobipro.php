<?php
/**
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( '_JEXEC' ) || exit( 'Restricted access' );
define( 'SOBI_TESTS', true );
defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
//define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : ( version_compare( JVERSION, '1.6.0', 'ge' ) ? 'joomla16' : 'joomla15'  ) );
define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : 'joomla16' );
define( 'SOBIPRO', true );
define( 'SOBIPRO_ADM', true );
define( 'SOBI_TASK', 'task' );
//if( SOBI_CMS == 'joomla15') {
//	define( 'SOBI_DEFLANG', JComponentHelper::getParams( 'com_languages' )->get( 'site', JFactory::getConfig()->getValue( 'config.language' ) ) );
//}
//else {
	define( 'SOBI_DEFLANG', JComponentHelper::getParams( 'com_languages' )->get( 'site', 'en-GB' ) );
//	define( 'SOBI_DEFLANG', JComponentHelper::getParams( 'com_languages' )->get( 'site', JFactory::getConfig()->get( 'language', JFactory::getConfig()->get( 'config.language' ) ) ) );
//}
define( 'SOBI_ACL', 'adm' );
define( 'SOBI_ROOT', JPATH_ROOT );
define( 'SOBI_MEDIA', implode( DS, array( JPATH_ROOT, 'media', 'sobipro' ) ) );
define( 'SOBI_MEDIA_LIVE', JURI::root().'/media/sobipro' );
define( 'SOBI_PATH', SOBI_ROOT .  '/components/com_sobipro' );
define( 'SOBI_ADM_PATH', JPATH_ADMINISTRATOR.DS.'components/com_sobipro' );
$adm = str_replace( JPATH_ROOT, null, JPATH_ADMINISTRATOR );
define( 'SOBI_ADM_LIVE_PATH', $adm . '/components/com_sobipro' );
define( 'SOBI_ADM_FOLDER', $adm  );
define( 'SOBI_LIVE_PATH', 'components/com_sobipro' );
require_once ( SOBI_PATH.'/lib/base/fs/loader.php' );
SPLoader::loadController( 'interface' );
SPLoader::loadClass( 'base.filter' );
SPLoader::loadClass( 'base.request' );
JHtml::_( 'behavior.tooltip' );
JHTML::_( 'behavior.modal' );
$class = SPLoader::loadController( 'adm.sobipro' );
$sobi = new $class( SPRequest::task() );
$sobi->execute();
