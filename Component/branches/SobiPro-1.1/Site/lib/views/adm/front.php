<?php
/**
 * @version: $Id: front.php 1678 2011-07-19 09:29:38Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-07-19 11:29:38 +0200 (Tue, 19 Jul 2011) $
 * $Revision: 1678 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/front.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Jan-2009 14:44:34
 */
class SPAdmPanelView extends SPAdmView
{
	/**
	 *
	 */
	public function display()
	{
		$sections =& $this->get( 'sections' );
		$_sections = array();
		if ( count( $sections ) ) {
			foreach ( $sections as $section ) {
				$name = $section->get( 'name' );
				$id = $section->get( 'id' );
				$url = Sobi::Url( array( 'sid' => $id ) );
				$_section = array();
				$_section[ 'id' ] = $id;
				$_section[ 'nid' ] = $section->get( 'nid' );
				$_section[ 'name' ] = "<a href=\"{$url}\">{$name}</a>";
//				$_section[ 'entries_counter' ] 		= $section->countChilds( 'entry' );
//				$_section[ 'categories_counter' ] 	= $section->countChilds( 'category' );
				$_section[ 'state' ] = SPLists::state( $section );
				$_section[ 'checkbox' ] = SPLists::checkedOut( $section, 'sid' );
				$_section[ 'createdTime' ] = $section->get( 'createdTime' );
				$_section[ 'metaDesc' ] = $section->get( 'metaDesc' );
				$_section[ 'metaKey' ] = $section->get( 'metaKey' );
				$_section[ 'description' ] = $section->get( 'description' );
				$_section[ 'url' ] = $url;
				$_sections[ ] = $_section;
			}
		}
		$this->set( $_sections, 'sections' );
		parent::display();
	}
}
