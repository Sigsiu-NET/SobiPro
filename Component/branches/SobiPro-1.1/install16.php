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
	$db->setQuery( 'SHOW COLUMNS FROM #__sobipro_field_data' );
	$cols = $db->loadAssocList( 'Field' );
	if( !( isset( $cols[ 'editLimit' ] ) ) ) {
		$db->setQuery( 'ALTER TABLE  `#__sobipro_field_data` ADD  `editLimit` INT( 11 );' );
		$db->query();
	}
	echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border-style:none;height:900px; width: 100%;"></iframe>';
}