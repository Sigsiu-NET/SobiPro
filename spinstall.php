<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( '_JEXEC' ) || exit( 'Restricted access' );

class com_sobiproInstallerScript
{
	/**
	 * Runs just before any installation action is preformed on the component.
	 * Verifications and pre-requisites should run in this function.
	 *
	 * @param  string $type - Type of PreFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function preflight( $type, $parent )
	{
		if ( $parent instanceof JInstallerAdapterComponent ) {
			$this->installPlugins( $parent->getParent()->get( 'paths' ) );
		}
		elseif ( $parent instanceof JInstallerComponent ) {
			$this->installPlugins( $parent->getParent->get( '_paths' ) );
		}

		if ( $type != 'uninstall' ) {
			// Installing component manifest file version
			$this->release = $parent->get( 'manifest' )->version;
			// Show the essential information at the install/update back-end
			echo '<h2>Installing SobiPro version ' . $this->release . ' ...</h2>';
		}

	}

	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @param  string $type - Type of PostFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
//	function postflight($type, $parent)
//	{
//		echo '<p>' . JText::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
//
//	}


	/**
	 * This method is called after a component is updated.
	 *
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function update( $parent )
	{
		if ( file_exists( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ] ) ) ) {
			JFolder::delete( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ] ) );
		}
		if ( file_exists( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ] ) ) ) {
			JFolder::delete( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ] ) );
		}
		if ( file_exists( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'media' ] ) ) ) {
			JFolder::delete( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'media' ] ) );
		}

		if ( !( file_exists( implode( '/', [ JPATH_ROOT, 'images', 'sobipro', 'categories' ] ) ) ) ) {
			JFolder::create( implode( '/', [ JPATH_ROOT, 'images', 'sobipro', 'categories' ] ) );
		}
		$srcpath = JPATH_ROOT . '/media/sobipro/icons';
		if ( file_exists( $srcpath ) ) {
			$files = scandir( $srcpath );

			$dest = JPATH_ROOT . '/images/sobipro/categories';
			if ( count( $files ) ) {
				foreach ( $files as $file ) {
					if ( $file != '.' && $file != '..' ) {
						if ( is_dir( $srcpath . '/' . $file ) ) {
							JFolder::copy( $srcpath . '/' . $file, $dest . '/' . $file, '', true );
						}
						elseif ( !( file_exists( $dest . '/' . $file ) ) ) {
							JFile::copy( $srcpath . '/' . $file, $dest . '/' . $file );
						}
					}
				}
			}
		}
		$srcpath = JPATH_ROOT . '/media/sobipro/images';
		if ( file_exists( $srcpath ) ) {
			$files = scandir( $srcpath );

			$dest = JPATH_ROOT . '/images/sobipro/categories';
			if ( count( $files ) ) {
				foreach ( $files as $file ) {
					if ( $file != '.' && $file != '..' ) {
						if ( is_dir( $srcpath . '/' . $file ) ) {
							JFolder::copy( $srcpath . '/' . $file, $dest . '/' . $file, '', true );
						}
						elseif ( !( file_exists( $dest . '/' . $file ) ) ) {
							JFile::copy( $srcpath . '/' . $file, $dest . '/' . $file );
						}
					}
				}
			}
		}

		$db = JFactory::getDBO();
		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_view_cache` (  `cid` INT(11) NOT NULL AUTO_INCREMENT,  `section` INT(11) NOT NULL,  `sid` INT(11) NOT NULL,  `fileName` VARCHAR(100) NOT NULL,  `task` VARCHAR(100) NOT NULL,  `site` INT(11) NOT NULL,  `request` VARCHAR(255) NOT NULL,  `language` VARCHAR(15) NOT NULL,  `template` VARCHAR(150) NOT NULL,  `configFile` TEXT NOT NULL,  `userGroups` VARCHAR(200) NOT NULL,  `created` DATETIME NOT NULL,PRIMARY KEY (`cid`),KEY `sid` (`sid`),KEY `section` (`section`),KEY `language` (`language`),KEY `task` (`task`),KEY `request` (`request`),KEY `site` (`site`),KEY `userGroups` (`userGroups`));' );
		$db->execute();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_view_cache_relation` (`cid` INT(11) NOT NULL,`sid` INT(11) NOT NULL,PRIMARY KEY (`cid`,`sid`));' );
		$db->execute();

		$db->setQuery( "UPDATE #__sobipro_permissions SET value =  '*' WHERE  pid = 18;" );
		$db->execute();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_user_group` (`description` TEXT,`gid` INT(11) NOT NULL AUTO_INCREMENT,`enabled` INT(11) NOT NULL,`pid` INT(11) NOT NULL,`groupName` VARCHAR(150) NOT NULL,PRIMARY KEY (`gid`) ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=5000 ;' );
		$db->execute();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_counter` ( `sid` INT(11)  NOT NULL, `counter` INT(11) NOT NULL, `lastUpdate` DATETIME NOT NULL, PRIMARY KEY (`sid`) );' );
		$db->execute();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_history` ( `revision` VARCHAR(150) NOT NULL, `changedAt` DATETIME NOT NULL, `uid` INT(11) NOT NULL, `userName` VARCHAR(150) NOT NULL, `userEmail` VARCHAR(150) NOT NULL, `change` VARCHAR(150) NOT NULL, `site` ENUM(\'site\',\'adm\') NOT NULL, `sid` INT(11) NOT NULL, `changes` TEXT NOT NULL, `params` TEXT NOT NULL, `reason` TEXT NOT NULL, `language` VARCHAR(50) NOT NULL, PRIMARY KEY (`revision`) ) DEFAULT CHARSET=utf8;' );
		$db->execute();

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_config` ( `sKey` , `sValue` , `section` , `critical` , `cSection` ) VALUES ( 'engb_preload',  '1',  '0', NULL ,  'lang' )" );
		$db->execute();

		$db->setQuery( "UPDATE #__sobipro_field_option_selected SET `optValue` = REPLACE (`optValue`, '_', '-')" );
		$db->execute();

		$db->setQuery( "UPDATE #__sobipro_field_option SET `optValue` = REPLACE (`optValue`, '_', '-')" );
		$db->execute();

		$db->setQuery( "UPDATE #__sobipro_language SET  `sKey` = REPLACE(  `sKey` ,  '_',  '-' ) WHERE  `oType` =  'field_option'" );
		$db->execute();
		try {
			$db->setQuery( 'DELETE FROM `#__sobipro_permissions` WHERE `pid` = 5;' );
			$db->execute();
		}
		catch ( Exception $x ) {
		}

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES (89, 'section', 'access', '*', 'adm', 1), (90, 'section', 'configure', '*', 'adm', 1), (91, 'section', 'delete', '*', 'adm', 0), (92, 'category', 'edit', '*', 'adm', 1), (93, 'category', 'add', '*', 'adm', 1), (94, 'category', 'delete', '*', 'adm', 1), (95, 'entry', 'edit', '*', 'adm', 1), (96, 'entry', 'add', '*', 'adm', 1), (97, 'entry', 'delete', '*', 'adm', 1), (98, 'entry', 'approve', '*', 'adm', 1), (99, 'entry', 'publish', '*', 'adm', 1), (86, 'entry', '*', '*', 'adm', 1), (87, 'category', '*', '*', 'adm', 1), (88, 'section', '*', '*', 'adm', 1);" );
		$db->execute();

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
			$db->execute();
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
					$db->execute();
				}
				catch ( Exception $x ) {
				}
				$db->setQuery( 'ALTER TABLE  `#__sobipro_field_data` ADD FULLTEXT  `baseData` (`baseData`);' );
				$db->execute();
			}
		}
		catch ( Exception $x ) {
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
				$db->execute();
			}
			catch ( Exception $x ) {
			}
			$db->setQuery( 'ALTER TABLE  `#__sobipro_language` ADD FULLTEXT  `sValue` (`sValue`);' );
			$db->execute();
		}

		$db->setQuery( 'SHOW INDEX FROM  #__sobipro_history' );
		$cols = $db->loadAssocList();
		$skip = false;
		foreach ( $cols as $col ) {
			if ( $col[ 'Key_name' ] == 'changeAction' ) {
				$skip = true;
				continue;
			}
		}
		if ( !( $skip ) ) {
			try {
				$db->setQuery( 'ALTER TABLE #__sobipro_history CHANGE `change` `changeAction` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;' );
				$db->execute();
			}
			catch ( Exception $x ) {
			}
		}

		$db->setQuery( 'SHOW INDEX FROM #__sobipro_category' );
		$cols = $db->loadAssocList();
		$skip = false;
		foreach ( $cols as $col ) {
			if ( $col[ 'Key_name' ] == 'allFields' ) {
				$skip = true;
				continue;
			}
		}
		if ( !( $skip ) ) {
			try {
				$db->setQuery( 'ALTER TABLE `#__sobipro_category` ADD `allFields` TINYINT(2) NOT NULL AFTER `showIcon`, ADD `entryFields` TEXT NOT NULL AFTER `allFields`;' );
				$db->execute();
				$db->setQuery( 'UPDATE `#__sobipro_category` SET `allFields` = 1' );
				$db->execute();
			}
			catch ( Exception $x ) {
			}
		}


		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_registry` (`section`, `key`, `value`, `params`, `description`, `options`) VALUES ('rejections-templates', 'rejection-of-a-new-entry', 'Rejection of a new entry', 'YTo0OntzOjE3OiJ0cmlnZ2VyLnVucHVibGlzaCI7YjoxO3M6MTc6InRyaWdnZXIudW5hcHByb3ZlIjtiOjA7czo5OiJ1bnB1Ymxpc2giO2I6MTtzOjc6ImRpc2NhcmQiO2I6MDt9', '', ''), ('rejections-templates', 'rejection-of-changes', 'Rejection of changes', 'YTo0OntzOjE3OiJ0cmlnZ2VyLnVucHVibGlzaCI7YjowO3M6MTc6InRyaWdnZXIudW5hcHByb3ZlIjtiOjE7czo5OiJ1bnB1Ymxpc2giO2I6MDtzOjc6ImRpc2NhcmQiO2I6MTt9', '', '');" );
		$db->execute();

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES (NULL, 'section', 'search', '*', 'front', 1), (NULL, 'entry', 'delete', 'own', 'front', 1),(NULL, 'entry', 'delete', '*', 'front', 1);" );
		$db->execute();


		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES ('category', 'Category', 'special', 11);" );
		$db->execute();
		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES ('info', 'Information', 'free_single_simple_data', 6);" );
		$db->execute();
		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES ('button', 'Button', 'special', 5);" );
		$db->execute();

		$db->setQuery( 'CREATE TABLE IF NOT EXISTS `#__sobipro_field_url_clicks` (  `date` DATETIME NOT NULL,  `uid` INT(11) NOT NULL,  `sid` INT(11) NOT NULL,  `fid` VARCHAR(50) NOT NULL,  `ip` VARCHAR(15) NOT NULL,  `section` INT(11) NOT NULL,  `browserData` TEXT NOT NULL,  `osData` TEXT NOT NULL,  `humanity` INT(3) NOT NULL,  PRIMARY KEY (`date`,`sid`,`fid`,`ip`,`section`) );' );
		$db->execute();

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES ('category', 'Category', '1.4', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');" );
		$db->execute();
		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES ('info', 'Information', '1.4', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');" );
		$db->execute();
		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES ('button', 'Button', '1.4', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');" );
		$db->execute();

		$db->setQuery( "CREATE TABLE IF NOT EXISTS `#__sobipro_crawler` ( `url` VARCHAR(255) NOT NULL,`crid` INT(11) NOT NULL AUTO_INCREMENT,`state` TINYINT(1) NOT NULL, PRIMARY KEY (`crid`), UNIQUE KEY `url` (`url`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;" );
		$db->execute();

		$db->setQuery( "CREATE TABLE IF NOT EXISTS `#__sobipro_crawler` ( `url` VARCHAR(255) NOT NULL,`crid` INT(11) NOT NULL AUTO_INCREMENT,`state` TINYINT(1) NOT NULL, PRIMARY KEY (`crid`), UNIQUE KEY `url` (`url`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;" );
		$db->execute();

		$db->setQuery( "INSERT IGNORE INTO `#__sobipro_language` (`sKey`, `sValue`, `section`, `language`, `oType`, `fid`, `id`, `params`, `options`, `explanation`) VALUES ('rejection-of-a-new-entry', 'Entry {entry.name} has been rejected as it does not comply with the rules.\n\n<br/>Rejected by {user.name}\n<br/>at {date%d F Y H:i:s}\n', 0, 'en-GB', 'rejections-templates', 0, 1, '', '', ''), ('rejection-of-changes', 'Changes in {entry.name} discarded as these changes violating rules.\n\n<br/>Rejected by {user.name}\n<br/>at {date%d F Y H:i:s}\n', 0, 'en-GB', 'rejections-templates', 0, 1, '', '', '');" );
		$db->execute();

		$db->setQuery( "UPDATE `#__sobipro_registry` SET `params` = 'L15bXHdcLi1dK0BbXHdcLi1dK1wuW2EtekEtWl17MiwyNH0kLw==' WHERE `key` = 'email' " );
		$db->execute();

//		$db->setQuery( 'SELECT pid FROM `#__sobipro_permissions` WHERE subject = "section" AND  action = "search";' );
//		$pid = $db->loadResult();
//
//		$db->setQuery( 'SELECT rid FROM #__sobipro_permissions_rules' );
//		$rids = $db->loadRowList();
//		if ( count( $rids ) ) {
//			$insert = array();
//			foreach ( $rids as $rid ) {
//
//			}
//		}
		JFile::move( JPATH_ROOT . '/components/com_sobipro/etc/repos/sobipro_core/repository.1.4.xml', JPATH_ROOT . '/components/com_sobipro/etc/repos/sobipro_core/repository.xml' );
		$this->installFramework();

		echo '<div class="alert alert-info" style="margin-top: 20px;"><h3>Thank you for updating SobiPro!</h3><p>SobiPro is checking your system now, please see if there are errors or warnings. If the system check reports errors, your SobiPro installation will probably not work. If you see warnings, some functionality of SobiPro can be disturbed or malfunction. In these cases you should take a look to the <a href="https://www.sigsiu.net/sobipro/requirements"><strong>Requirements for SobiPro</strong></a> page on our website.</p>
<p>You can install languages directly from our <a href="index.php?option=com_sobipro&task=extensions.browse"><strong>Repository</strong></a> or download them from our <a href="https://www.sigsiu.net/center/languages"><strong>website</strong></a> and install it in the <a href="index.php?option=com_sobipro&task=extensions.installed"><strong>SobiPro Application Manager</strong></a>.</p></div>';

		echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border: 1px solid #e0e0e0; border-radius: 5px; height: 900px; min-width: 1000px; width: 99%; margin-bottom: 50px; padding-left: 10px; padding-top: 10px;"></iframe>';
	}

	/**
	 * This method is called after a component is installed.
	 *
	 * @param  \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function install( $parent )
	{
		if ( !( file_exists( implode( '/', [ JPATH_ROOT, 'images', 'sobipro' ] ) ) ) ) {
			JFolder::create( implode( '/', [ JPATH_ROOT, 'images', 'sobipro' ] ) );
		}
		if ( !( file_exists( implode( '/', [ JPATH_ROOT, 'images', 'sobipro', 'categories' ] ) ) ) ) {
			JFolder::create( implode( '/', [ JPATH_ROOT, 'images', 'sobipro', 'categories' ] ) );

			if ( file_exists( JPATH_ROOT . '/components/com_sobipro/tmp/install/image.png' ) ) {
				JFile::move( JPATH_ROOT . '/components/com_sobipro/tmp/install/image.png', JPATH_ROOT . '/images/sobipro/categories/image.png' );
			}
		}
		if ( file_exists( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ] ) ) ) {
			JFolder::move(
				implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'tmp', 'SampleData', 'entries' ] ),
				implode( '/', [ JPATH_ROOT, 'images', 'sobipro', 'entries' ] )
			);
		}
		if ( file_exists( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ] ) ) ) {
			JFolder::delete( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'locale' ] ) );
		}
		if ( file_exists( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ] ) ) ) {
			JFolder::delete( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'services', 'installers', 'schemas', 'application.xsd' ] ) );
		}
		if ( file_exists( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'media' ] ) ) ) {
			JFolder::delete( implode( '/', [ JPATH_ROOT, 'components', 'com_sobipro', 'media' ] ) );
		}
		JFile::move( JPATH_ROOT . '/components/com_sobipro/etc/repos/sobipro_core/repository.1.4.xml', JPATH_ROOT . '/components/com_sobipro/etc/repos/sobipro_core/repository.xml' );
		$db = JFactory::getDBO();
		$db->setQuery( 'SHOW COLUMNS FROM #__sobipro_field_data' );
		$cols = $db->loadAssocList( 'Field' );
		if ( !( isset( $cols[ 'editLimit' ] ) ) ) {
			$db->setQuery( 'ALTER TABLE  `#__sobipro_field_data` ADD  `editLimit` INT( 11 );' );
			$db->execute();
		}
		$this->installFramework();
		echo '<div class="alert alert-info" style="margin-top: 20px;"><h3>Welcome to SobiPro!</h3><p>SobiPro is checking your system now, please see if there are errors or warnings. If the system check reports errors, your SobiPro installation will probably not work. If you see warnings, some functionality of SobiPro can be disturbed or malfunction. In these cases you should take a look to the <a href="https://www.sigsiu.net/sobipro/requirements"><strong>Requirements for SobiPro</strong></a> page on our website.</p>
<p>You can install languages directly from our <a href="index.php?option=com_sobipro&task=extensions.browse"><strong>Repository</strong></a> or download them from our <a href="https://www.sigsiu.net/center/languages"><strong>website</strong></a> and install it in the <a href="index.php?option=com_sobipro&task=extensions.installed"><strong>SobiPro Application Manager</strong></a>.</p></div>';

		echo '<iframe src="index.php?option=com_sobipro&task=requirements&init=1&tmpl=component" style="border: 1px solid #e0e0e0; border-radius: 5px; height: 900px; min-width: 1000px; width: 99%; margin-bottom: 50px; padding-left: 10px; padding-top: 10px;"></iframe>';
	}

	protected function installPlugins( $source )
	{
		$source = $source[ 'source' ];
		$plugins = [ 'Header' ];
		$path = $source . '/Plugins';
		$installer = new JInstaller;
		$db = JFactory::getDBO();
		foreach ( $plugins as $plugin ) {
			$dir = $path . '/' . $plugin;
			$installer->install( $dir );
			$db->setQuery( "UPDATE #__extensions SET enabled =  '1' WHERE  element = 'sp{$plugin}';" );
			$db->execute();
		}
	}

	/**
	 * This method is called after a component is uninstalled.
	 *
	 * @param  \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function uninstall( $parent )
	{
		echo '<h2>Un-Installing SobiPro ...</h2>';

		$db = JFactory::getDBO();
		$query = "show tables like '" . $db->getPrefix() . "sobipro_%'";
		$db->setQuery( $query );
		$tables = $db->loadColumn();
		foreach ( $tables as $table ) {
			$db->setQuery( "DROP TABLE {$table};" );
			$db->execute();
		}
		JFolder::delete( implode( '/', [ JPATH_ROOT, 'images', 'sobipro' ] ) );

		echo '<p style="margin-bottom: 50px;"><strong>Done! All SobiPro files and database tables have been removed from your system.</strong></p>';

	}

	protected function installFramework()
	{
		//Sobi Framework installation
		if ( !( file_exists( JPATH_ROOT . '/libraries/sobi' ) ) ) {
			JFolder::create( JPATH_ROOT . '/libraries/sobi' );
		}
		$files = scandir( JPATH_ROOT . '/libraries/sobi' );
		if ( count( $files ) ) {
			foreach ( $files as $file ) {
				if ( strstr( $file, '.tar.gz' ) || strstr( $file, '.php' ) ) {
					JFile::delete( JPATH_ROOT . '/libraries/sobi/' . $file );
				}
			}
		}
//		if ( file_exists( JPATH_ROOT . '/libraries/sobi/Sobi.phar.tar.gz' ) ) {
//			JFile::delete( JPATH_ROOT . '/libraries/sobi/Sobi.phar.tar.gz' );
//		}
		JFile::copy( JPATH_ROOT . '/components/com_sobipro/Sobi.phar.tar.gz', JPATH_ROOT . '/libraries/sobi/Sobi-1.0.2.phar.tar.gz' );
		JFile::delete( JPATH_ROOT . '/components/com_sobipro/Sobi.phar.tar.gz' );
		// I am guessing that this was what cached the PHAR file. Let's see...
		if ( function_exists( 'opcache_reset' ) ) {
			opcache_reset();
		}
	}
}


