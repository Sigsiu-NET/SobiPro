<?php
/**
 * @version: $Id: uninstall16.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/uninstall16.php $
 */
defined( '_JEXEC' ) || exit( 'Restricted access' );

function com_uninstall()
{
        $db =& JFactory::getDBO();
        $query = "show tables like '".$db->getPrefix()."sobipro_%'";
        $db->setQuery( $query );
        $tables = $db->loadResultArray();
        foreach( $tables as $table ) {
            $db->setQuery( "DROP TABLE {$table};" );
            $db->query();
        }
        $db->setQuery( "DELETE FROM `#__modules` WHERE `module` = 'mod_spmenu'" );
        $db->query();
        $db->setQuery( "DELETE FROM `#__extensions` WHERE `element` = 'mod_spmenu'" );
        $db->query();
        JFolder::delete( implode( DS, array( JPATH_ROOT, 'administrator', 'modules', 'mod_spmenu' ) ) );
        JFolder::delete( implode( DS, array( JPATH_ROOT, 'images', 'sobipro' ) ) );
}