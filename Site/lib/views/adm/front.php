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
		$_sections = [];
		if ( count( $sections ) ) {
			foreach ( $sections as $section ) {
				$name = $section->get( 'name' );
				$id = $section->get( 'id' );
				$url = Sobi::Url( [ 'sid' => $id ] );
				$_section = [];
				$_section[ 'id' ] = $id;
				$_section[ 'nid' ] = $section->get( 'nid' );
				$_section[ 'name' ] = "<a href=\"{$url}\">{$name}</a>";
//				$_section[ 'entries_counter' ] 		= $section->countChilds( 'entry' );
//				$_section[ 'categories_counter' ] 	= $section->countChilds( 'category' );
//				$_section[ 'state' ] = SPLists::state( $section );
//				$_section[ 'checkbox' ] = SPLists::checkedOut( $section, 'sid' );
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
