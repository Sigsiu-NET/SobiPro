<?php
/**
 * @version: $Id: sobipro.php 3005 2013-01-17 14:50:57Z Radek Suski $
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date: 2013-01-17 15:50:57 +0100 (Do, 17 Jan 2013) $
 * $Revision: 3005 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/branches/SobiPro-1.1/Admin/sobipro.php $
 */

defined( '_JEXEC' ) || exit( 'Restricted access' );

class com_sobiproInstallerScript
{
    /**
     * Called on installation
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install(JAdapterInstance $adapter)
    {
        if ( !( file_exists( implode( '/', array( JPATH_ROOT, 'images', 'sobipro' ) ) ) ) ) {
            JFolder::create( implode( '/', array( JPATH_ROOT, 'images', 'sobipro' ) ) );
        }
        if ( file_exists( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ) ) ) ) {
            JFolder::move(
                implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ) ),
                implode( '/', array( JPATH_ROOT, 'images', 'sobipro', 'entries' ) )
            );
        }
        if ( file_exists( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ) ) ) ) {
            JFolder::delete( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ) ) );
        }
        if ( file_exists( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) ) ) {
            JFolder::delete( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) );
        }
        if ( file_exists( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'media' ) ) ) ) {
            JFolder::delete( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'media' ) ) );
        }
        $db = JFactory::getDBO();
        $db->setQuery( 'SHOW COLUMNS FROM #__sobipro_field_data' );
        $cols = $db->loadAssocList( 'Field' );
        if ( !( isset( $cols[ 'editLimit' ] ) ) ) {
            $db->setQuery( 'ALTER TABLE  `#__sobipro_field_data` ADD  `editLimit` INT( 11 );' );
            $db->query();
        }
        echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border-style:none;height:900px; width: 100%;"></iframe>';
    }

    /**
     * Called on uninstallation
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     */
    public function uninstall(JAdapterInstance $adapter)
    {
        $db =& JFactory::getDBO();
        $query = "show tables like '" . $db->getPrefix() . "sobipro_%'";
        $db->setQuery( $query );
        $tables = $db->loadColumn();
        foreach ( $tables as $table ) {
            $db->setQuery( "DROP TABLE {$table};" );
            $db->query();
        }
        JFolder::delete( implode( '/', array( JPATH_ROOT, 'images', 'sobipro' ) ) );
    }
}
