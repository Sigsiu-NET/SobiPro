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
 * @created 10-Jan-2009 4:41:27 PM
 */
class SPEntryAdmView extends SPAdmView
{
	/**
	 * @param string $title
	 * @return string
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'entry.name' );
		Sobi::Trigger( 'setTitle', $this->name(), [ &$title ] );
		$title = Sobi::Txt( $title, [ 'entry_name' => $name ] );
		$this->set( $name, 'entry_name' );
		$title = parent::setTitle( $title );
		return $title;
	}

	/**
	 *
	 */
	public function _display()
	{
		SPLoader::loadClass( 'html.tooltip' );
		switch ( $this->get( 'task' ) ) {
			case 'edit':
				$languages = $this->languages();
				$this->assign( $languages, 'languages-list' );
			case 'add':
				$this->edit();
				break;
		}
		parent::display();
	}

	/**
	 */
	private function edit()
	{
		$id = $this->get( 'entry.id' );
		if( $id ) {
			$this->addHidden( $id, 'entry.id' );
		}
		$sid = SPRequest::int( 'pid' ) ? SPRequest::int( 'pid' ) : SPRequest::sid();
		$catChooserUrl = Sobi::Url( [ 'task' => 'category.chooser', 'sid' => $sid, 'out' => 'html', 'multiple' => 1 ], true );
		$this->assign( $catChooserUrl, 'cat_chooser_url' );
	}
}
