<?php
/**
 * @version: $Id: category.php 2317 2012-03-27 10:19:39Z Radek Suski $
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
 * $Date: 2012-03-27 12:19:39 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2317 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/category.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'section', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:42:13 PM
 */
class SPCategoryAdmView extends SPSectionAdmView
{

	/**
	 * @param string $title
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'category.name' );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		$title = Sobi::Txt( $title, array( 'category_name' => $name ) );
		$this->set( $name, 'category_name' );
		$title = parent::setTitle( $title );
		return $title;
	}

	/**
	 *
	 */
	public function display()
	{
		SPLoader::loadClass( 'html.tooltip' );
		switch ( $this->get( 'task' ) ) {
			case 'list':
				$this->listSection();
				break;
			case 'edit':
			case 'add':
				$this->edit();
				$this->determineTemplate( 'category', 'edit' );
				break;
			case 'chooser':
				$this->chooser();
				break;
		}
		parent::display();
	}

	/**
	 */
	private function edit()
	{
		$pid = $this->get( 'category.parent' );
		$path = null;
		if ( !$pid ) {
			$pid = SPRequest::int( 'pid' );
		}
		$this->assign( $pid, 'parent' );
		$id = $this->get( 'category.id' );
		if ( $id ) {
			$this->addHidden( $id, 'category.id' );
		}
		if ( $this->get( 'category.icon' ) && SPFs::exists( Sobi::Cfg( 'images.category_icons' ) . '/' . $this->get( 'category.icon' ) ) ) {
			$i = Sobi::FixPath( Sobi::Cfg( 'images.category_icons_live' ) . $this->get( 'category.icon' ) );
			$this->assign( $i, 'category_icon' );
		}
		else {
			$i = Sobi::FixPath( Sobi::Cfg( 'images.category_icons_live' ) . Sobi::Cfg( 'icons.default_selector_image', '48x48/image.png' ) );
			$this->assign( $i, 'category_icon' );
		}
		/* if editing - get the full path. Otherwise get the path of the parent element */
		$id = $id ? $id : $pid;
		if ( $this->get( 'category.id' ) ) {
			$path = $this->parentPath( $id );
			$parentCat = $this->parentPath( $id, false, true );
		}
		else {
			$path = $this->parentPath( SPRequest::sid() );
			$parentCat = $this->parentPath( SPRequest::sid(), false, true, 1 );
		}
		$this->assign( $path, 'parent_path' );
		$this->assign( $parentCat, 'parent_cat' );
		if ( SPRequest::sid() ) {
			$this->assign( Sobi::Url( array( 'task' => 'category.chooser', 'sid' => SPRequest::sid(), 'out' => 'html' ), true ), 'cat_chooser_url' );
		}
		elseif ( SPRequest::int( 'pid' ) ) {
			$this->assign( Sobi::Url( array( 'task' => 'category.chooser', 'pid' => SPRequest::int( 'pid' ), 'out' => 'html' ), true ), 'cat_chooser_url' );
		}
		$this->assign( Sobi::Url( array( 'task' => 'category.icon', 'out' => 'html' ), true ), 'icon_chooser_url' );
	}

	private function chooser()
	{
		$pid = $this->get( 'category.parent' );
		$path = null;
		if ( !$pid ) {
			$pid = SPRequest::sid();
		}
		$this->assign( $pid, 'parent' );
		$id = $this->get( 'category.id' );
		$id = $id ? $id : $pid;
		if ( $id ) {
			$path = $this->parentPath( $id );
		}
		$this->assign( $path, 'parent_path' );
		$this->assign( Sobi::Url( array( 'task' => 'category.parents', 'out' => 'json', 'format' => 'raw' ), true ), 'parent_ajax_url' );
	}
}
