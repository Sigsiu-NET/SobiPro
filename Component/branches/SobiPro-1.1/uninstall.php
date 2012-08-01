<?php
/**
 * @version: $Id: uninstall.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/uninstall.php $
 */
defined( '_JEXEC' ) || exit( 'Restricted access' );
if( !( JRequest::getVar( 'spconfirm', 0 ) ) ) {
?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr" id="minwidth" >
	<head>
	    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	    <title>Extension Manager : Components</title>
	    <link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
	    <link href="templates/khepri/css/template.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	    <div style="width:500px; margin: 100px;">
	    <form action="index.php" method="post" name="adminForm">
	    <table class="admintable" style="width: 100%">
	        <tr class="row0">
	            <th colspan="2" class="spConfigTableHeader"><?php echo JText::_( 'SobiPro un-installation options' );?><br/>&nbsp;</th>
	        </tr>
	        <tr class="row1">
	            <td class="key" style="min-width: 300px;">
	                <?php echo JText::_( 'Remove SobiPro Database' );?>
	            </td>
	            <td>
	                <input name="spdb" value="1" checked="checked" type="checkbox" class="inputbox"/>
	            </td>
	        </tr>
	        <tr class="row0">
	            <td class="key" style="min-width: 200px;">
	                <?php echo JText::_( 'Backup SobiPro Templates' );?>
	            </td>
	            <td>
	                <input name="sptpl" value="1"  type="checkbox" class="inputbox"/>
	            </td>
	        </tr>
	    </table>
<?php
    foreach( $_REQUEST as $k => $v ) {
        echo "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
    }
    echo "<input type=\"hidden\" name=\"spconfirm\" value=\"1\" />\n";
    echo '<input type="submit" value="'.JText::_( 'Uninstall >>' ).'" style="float:right;"/>';
    echo '</form>';
    echo '</div></body></html>';
    exit();
}
else {
    if( JRequest::getVar( 'spdb', 0 ) ) {
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
        JFolder::delete( implode( DS, array( JPATH_ROOT, 'administrator', 'modules', 'mod_spmenu' ) ) );
    }
    if( JRequest::getVar( 'sptpl', 0 ) ) {
        JFolder::move(
            implode( DS, array( JPATH_ROOT, 'components', 'com_sobipro', 'usr', 'templates' ) ),
            implode( DS, array( JPATH_ROOT, 'administrator', 'backups', 'sobipro' ) )
        );
    }
    JFolder::delete( implode( DS, array( JPATH_ROOT, 'images', 'sobipro' ) ) );
//    JFolder::delete( implode( DS, array( JPATH_ROOT, 'media', 'sobipro' ) ) );
}