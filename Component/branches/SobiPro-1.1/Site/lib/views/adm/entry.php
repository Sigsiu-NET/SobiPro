<?php
/**
 * @version: $Id: entry.php 1206 2011-04-16 15:14:11Z Radek Suski $
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
 * $Date: 2011-04-16 17:14:11 +0200 (Sat, 16 Apr 2011) $
 * $Revision: 1206 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/entry.php $
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
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'entry.name' );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		$title = Sobi::Txt( $title, array( 'entry_name' => $name ) );
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
		$this->assign( Sobi::Url( array( 'task' => 'category.chooser', 'sid' => $sid, 'out' => 'html', 'multiple' => 1 ), true ), 'cat_chooser_url' );
	}
}
