<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( '_JEXEC' ) || exit( 'Restricted access' );

if( !( file_exists( implode( DS, array( JPATH_ROOT, 'images', 'sobipro' ) ) ) ) ) {
	JFolder::create( implode( DS, array( JPATH_ROOT, 'images', 'sobipro' ) ) );
}
if( file_exists( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ) ) ) ) {
	JFolder::move(
		implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ) ),
		implode( DS, array( JPATH_ROOT, 'images', 'sobipro', 'entries' ) )
	);
}
if( file_exists( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) ) ) {
	JFolder::delete( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) );
}
JFolder::delete( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ) ) );
JFolder::delete( implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'media' ) ) );
$db = JFactory::getDBO();
$db->setQuery( 'SHOW COLUMNS FROM #__sobipro_field_data' );
$cols = $db->loadAssocList( 'Field' );
if( !( isset( $cols[ 'editLimit' ] ) ) ) {
	$db->setQuery( 'ALTER TABLE  `#__sobipro_field_data` ADD  `editLimit` INT( 11 );' );
	$db->query();
}
$lang = JComponentHelper::getParams( 'com_languages' )->get( 'site', JFactory::getConfig()->getValue( 'config.language' ) );
$db->setQuery( "UPDATE IGNORE #__sobipro_language SET language = '{$lang}' WHERE language = ''" );
$db->query();
$db->setQuery( "UPDATE IGNORE #__sobipro_field_data SET lang = '{$lang}' WHERE lang = ''" );
$db->query();
echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border-style:none;height:900px; width: 100%;"></iframe>';
