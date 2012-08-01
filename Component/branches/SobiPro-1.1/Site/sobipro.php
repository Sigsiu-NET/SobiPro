<?php
/**
 * @version: $Id: sobipro.php 2010 2011-11-24 10:07:38Z Radek Suski $
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
 * $Date: 2011-11-24 11:07:38 +0100 (Thu, 24 Nov 2011) $
 * $Revision: 2010 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/sobipro.php $
 */

define( 'SOBI_TESTS', false );
defined( '_JEXEC' ) || exit( 'Restricted access' );
defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
if( version_compare( JVERSION,'1.6.0','ge') ) {
	define( 'SOBI_CMS', 'joomla16' );
}
else {
	define( 'SOBI_CMS', 'joomla15' );
}
define( 'SOBIPRO', true );
define( 'SOBI_TASK', 'task' );
define( 'SOBI_DEFLANG', JFactory::getConfig()->getValue( 'config.language' ) );
define( 'SOBI_ACL', 'front' );
define( 'SOBI_ROOT', JPATH_ROOT );
define( 'SOBI_MEDIA', implode( DS, array( JPATH_ROOT, 'media', 'sobipro' ) ) );
define( 'SOBI_MEDIA_LIVE', JURI::root().'/media/sobipro' );
define( 'SOBI_PATH', SOBI_ROOT.DS.'components'.DS.'com_sobipro' );
define( 'SOBI_LIVE_PATH', 'components/com_sobipro' );
require_once ( SOBI_PATH.DS.'lib'.DS.'base'.DS.'fs'.DS.'loader.php' );
SPLoader::loadController( 'interface' );
SPLoader::loadClass( 'base.filter' );
SPLoader::loadClass( 'base.request' );

// Try to catch direct file calls. Like /directory/piwik.php
if( preg_match( '/\.php$/', SPRequest::task() ) || strlen( SPRequest::task() ) > 50 ) {
	JError::raiseError( 403, 'Unauthorized Access' );
}
JHtml::_( 'behavior.tooltip' );
$class = SPLoader::loadController( 'sobipro' );
$sobi = new $class( SPRequest::task() );
$sobi->execute();
?>
