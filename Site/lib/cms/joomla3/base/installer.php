<?php
/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
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
				->select( 'extension_id', '#__extensions', [ 'type' => $type, 'element' => $eid ] )
				->loadResult();
		jimport( 'joomla.installer.installer' );
		if ( JInstaller::getInstance()->uninstall( $type, $id ) ) {
			SPFactory::db()->delete( 'spdb_plugins', [ 'pid' => $eid, 'type' => $type ], 1 );
			return Sobi::Txt( 'CMS_EXT_REMOVED', $name );
		}
		return [ 'msg' => Sobi::Txt( 'CMS_EXT_NOT_REMOVED', $name ), 'msgtype' => 'error' ];
	}

	/**
	 * @param DOMDocument $def
	 * @param string $dir
	 * @return array | string
	 */
	protected function installExt( $def, $dir )
	{
		if ( $def->firstChild->nodeName == 'install' ) {
			$content = $def->saveXML();
			// I know, I know ....
			$content = str_replace( [ '<install', '</install>' ], [ '<extension', '</extension>' ], $content );
			SPFs::write( $dir . '/temp.xml', $content );
			$def = new DOMDocument();
			$def->load( $dir . '/temp.xml' );
		}
		return parent::installExt( $def, $dir );
	}
}

