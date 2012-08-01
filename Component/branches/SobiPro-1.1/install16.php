<?php
/**
 * @version: $Id: install16.php 1797 2011-08-09 09:49:05Z Radek Suski $
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
 * $Date: 2011-08-09 11:49:05 +0200 (Tue, 09 Aug 2011) $
 * $Revision: 1797 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/install16.php $
 */

defined( '_JEXEC' ) || exit( 'Restricted access' );

function com_install()
{
	if( !( file_exists( implode( DS, array( JPATH_ROOT, 'images', 'sobipro' ) ) ) ) ) {
		JFolder::create( implode( DS, array( JPATH_ROOT, 'images', 'sobipro' ) ) );
	}
	if( file_exists( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ) ) ) ) {
		JFolder::move(
			implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ) ),
			implode( DS, array( JPATH_ROOT, 'images', 'sobipro', 'entries' ) )
		);
	}
	JFolder::move(
		implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'install', 'mod_spmenu16' ) ),
		implode( DS, array( JPATH_ROOT, 'administrator', 'modules', 'mod_spmenu' ) )
	);
	if( file_exists( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ) ) ) ) {
		JFolder::delete( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ) ) );
	}
	if( file_exists( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) ) ) {
		JFolder::delete( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) );
	}
	if( file_exists( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'media' ) ) ) ) {
		JFolder::delete( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'media' ) ) );
	}
	$db =& JFactory::getDBO();
	$db->setQuery( "DELETE FROM `#__extensions` WHERE (`element` = 'mod_spmenu') " );
	$db->query();
	$db->setQuery( "INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES (NULL, 'SobiPro Admin Menu', 'module', 'mod_spmenu', '', 1, 1, 1, 0, 'a:11:{s:6:\"legacy\";b:0;s:4:\"name\";s:18:\"SobiPro Admin Menu\";s:4:\"type\";s:6:\"module\";s:12:\"creationDate\";s:16:\"2 September 2010\";s:6:\"author\";s:26:\"Sigrid Suski & Radek Suski\";s:9:\"copyright\";s:39:\"Copyright (C) 2006-2010 Sigsiu.NET GmbH\";s:11:\"authorEmail\";s:18:\"sobi[at]sigsiu.net\";s:9:\"authorUrl\";s:21:\"http://www.Sigsiu.NET\";s:7:\"version\";s:3:\"1.0\";s:11:\"description\";s:0:\"\";s:5:\"group\";s:0:\"\";}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);" );
	$db->query();
	$db->setQuery( "INSERT INTO `#__modules` ( `id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES (NULL, 'SobiPro Admin Menu', '', '', 1, 'menu', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_spmenu', 1, 1, '', 1, '*');" );
	$db->query();
	$id = $db->insertid();
	$db->setQuery( "INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES ({$id}, 0 )" );
	$db->query();
	$db->setQuery( 'SHOW COLUMNS FROM #__sobipro_field_data' );
	$cols = $db->loadAssocList( 'Field' );
	if( !( isset( $cols[ 'editLimit' ] ) ) ) {
		$db->setQuery( 'ALTER TABLE  `#__sobipro_field_data` ADD  `editLimit` INT( 11 );' );
		$db->query();
	}
	echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border-style:none;height:900px; width: 100%;"></iframe>';
}