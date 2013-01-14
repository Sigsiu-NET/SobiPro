<?php
/**
 * @version: $Id: helper.php 930 2011-03-05 12:38:11Z Radek Suski $
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
 * $Date: 2011-03-05 13:38:11 +0100 (Sat, 05 Mar 2011) $
 * $Revision: 930 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla_common/base/helper.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname( __FILE__ ) . '/../../joomla_common/base/installer.php';
/**
 * @author Radek Suski
 * @version 1.0
 * @created 05-Mar-2011 14:08:25
 */
class SPCmsInstaller extends SPJoomlaInstaller
{
    public function remove( $def )
    {
        $eid = $def->getElementsByTagName( 'id' )->item( 0 )->nodeValue;
        $name = $def->getElementsByTagName( 'name' )->item( 0 )->nodeValue;
        $type = $def->getElementsByTagName( 'type' )->item( 0 )->nodeValue;
        $id = SPFactory::db()
                ->select( 'extension_id', '#__extensions', array( 'type' => $type, 'element' => $eid ) )
                ->loadResult();
        jimport( 'joomla.installer.installer' );
        if( JInstaller::getInstance()->uninstall( $type, $id ) ) {
            SPFactory::db()->delete( 'spdb_plugins', array( 'pid' => $eid, 'type' => $type ), 1 );
            return Sobi::Txt( 'CMS_EXT_REMOVED', $name );
        }
        return array( 'msg' => Sobi::Txt( 'CMS_EXT_NOT_REMOVED', $name ), 'msgtype' => 'error' );
    }
}

?>
