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
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( '_JEXEC' ) || exit( 'Restricted access' );

class com_sobiproInstallerScript
{
	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	function preflight( $route, JAdapterInstance $adapter )
	{
		// Installing component manifest file version
		$this->release = $adapter->get( "manifest" )->version;

		// Show the essential information at the install/update back-end
		echo '<h3>Installing SobiPro version ' . $this->release . ' ...</h3>';

	}

	/**
	 * method to update the component
	 * @param JAdapterInstance $adapter
	 * @return void
	 */
	function update( JAdapterInstance $adapter )
	{
		if ( file_exists( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ) ) ) ) {
			JFolder::delete( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ) ) );
		}
		if ( file_exists( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) ) ) {
			JFolder::delete( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ) ) );
		}
		if ( file_exists( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'media' ) ) ) ) {
			JFolder::delete( implode( '/', array( JPATH_ROOT, 'components', 'com_sobipro', 'media' ) ) );
		}


        $srcpath = JPATH_ROOT . '/media/sobipro/icons';

        if (file_exists ($srcpath)) {
            $files = scandir($srcpath);

            $dest = JPATH_ROOT . '/media/sobipro/images';
            if ( count( $files ) ) {
                foreach ( $files as $file ) {
                    if ( $file != '.' && $file != '..' ) {
                        if ( is_dir( $srcpath . '/' . $file ) ) {
                            JFolder::move( $srcpath . '/' . $file, $dest . '/' . $file );
                        }
                        elseif ( !( file_exists( $dest . '/' . $file ) ) ) {
                            JFile::move( $srcpath . '/' . $file, $dest . '/' . $file );
                        }
                    }
                }
            }
        }

        $db = JFactory::getDBO();
		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_view_cache` (  `cid` int(11) NOT NULL AUTO_INCREMENT,  `section` int(11) NOT NULL,  `sid` int(11) NOT NULL,  `fileName` varchar(100) NOT NULL,  `task` varchar(100) NOT NULL,  `site` int(11) NOT NULL,  `request` varchar(255) NOT NULL,  `language` varchar(15) NOT NULL,  `template` varchar(150) NOT NULL,  `configFile` text NOT NULL,  `userGroups` varchar(200) NOT NULL,  `created` datetime NOT NULL,PRIMARY KEY (`cid`),KEY `sid` (`sid`),KEY `section` (`section`),KEY `language` (`language`),KEY `task` (`task`),KEY `request` (`request`),KEY `site` (`site`),KEY `userGroups` (`userGroups`));' );
		$db->query();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_view_cache_relation` (`cid` int(11) NOT NULL,`sid` int(11) NOT NULL,PRIMARY KEY (`cid`,`sid`));' );
		$db->query();

		$db->setQuery( "UPDATE #__sobipro_permissions SET value =  '*' WHERE  pid = 18;" );
		$db->query();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_user_group` (`description` text,`gid` int(11) NOT NULL AUTO_INCREMENT,`enabled` int(11) NOT NULL,`pid` int(11) NOT NULL,`groupName` varchar(150) NOT NULL,PRIMARY KEY (`gid`) ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=5000 ;' );
		$db->query();

		try {
			$db->setQuery( 'DELETE FROM `#__sobipro_permissions` WHERE `pid` = 5;' );
			$db->query();
		} catch ( Exception $x ) {
		}

		$db->setQuery( 'SHOW INDEX FROM  #__sobipro_permissions' );
		$cols = $db->loadAssocList();
		$skip = false;
		foreach ( $cols as $col ) {
			if ( $col[ 'Key_name' ] == 'uniquePermission' ) {
				$skip = true;
				continue;
			}
		}
		if ( !( $skip ) ) {
			$db->setQuery( 'ALTER TABLE `#__sobipro_permissions` ADD UNIQUE  `uniquePermission` (  `subject` ,  `action` ,  `value` ,  `site` );' );
			$db->query();
		}

		try {
			$db->setQuery( 'SHOW INDEX FROM  #__sobipro_field_data' );
			$cols = $db->loadAssocList();
			$skip = false;
			foreach ( $cols as $col ) {
				if ( $col[ 'Key_name' ] == 'baseData' ) {
					$skip = true;
					continue;
				}
			}
			if ( !( $skip ) ) {
				try {
					$db->setQuery( 'ALTER TABLE #__sobipro_field_data ENGINE = MYISAM;;' );
					$db->query();
				} catch ( Exception $x ) {
				}
				$db->setQuery( 'ALTER TABLE  `#__sobipro_field_data` ADD FULLTEXT  `baseData` (`baseData`);' );
				$db->query();
			}
		} catch ( Exception $x ) {
		}

		$db->setQuery( 'SHOW INDEX FROM  #__sobipro_language' );
		$cols = $db->loadAssocList();
		$skip = false;
		foreach ( $cols as $col ) {
			if ( $col[ 'Key_name' ] == 'sValue' ) {
				$skip = true;
				continue;
			}
		}
		if ( !( $skip ) ) {
			try {
				$db->setQuery( 'ALTER TABLE #__sobipro_language ENGINE = MYISAM;;' );
				$db->query();
			} catch ( Exception $x ) {
			}
			$db->setQuery( 'ALTER TABLE  `#__sobipro_language` ADD FULLTEXT  `sValue` (`sValue`);' );
			$db->query();
		}

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES (NULL, 'section', 'search', '*', 'front', 1), (NULL, 'entry', 'delete', 'own', 'front', 1),(NULL, 'entry', 'delete', '*', 'front', 1);" );
		$db->query();

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES ('category', 'Category', 'special', 11);" );
		$db->query();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_field_url_clicks` (  `date` datetime NOT NULL,  `uid` int(11) NOT NULL,  `sid` int(11) NOT NULL,  `fid` varchar(50) NOT NULL,  `ip` varchar(15) NOT NULL,  `section` int(11) NOT NULL,  `browserData` text NOT NULL,  `osData` text NOT NULL,  `humanity` int(3) NOT NULL,  PRIMARY KEY (`date`,`sid`,`fid`,`ip`,`section`) );' );
		$db->query();

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES ('category', 'Category', '1.1', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');" );
		$db->query();


		echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border: 1px solid #e0e0e0; border-radius: 5px; height: 900px; min-width: 1000px; width: 99%; margin-bottom: 50px; padding-left: 10px;"></iframe>';
	}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install( JAdapterInstance $adapter )
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
		echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border: 1px solid #e0e0e0; border-radius: 5px; height: 900px; min-width: 1000px; width: 99%; margin-bottom: 50px; padding-left: 10px;"></iframe>';
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall( JAdapterInstance $adapter )
	{
		$db = JFactory::getDBO();
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
