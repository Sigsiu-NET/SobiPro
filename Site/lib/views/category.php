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

SPLoader::loadView( 'section' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:15:43 PM
 */
class SPCategoryView extends SPSectionView implements SPView
{
	public function chooser()
	{
		$pid = $this->get( 'category.parent' );
		$path = null;
		if( !$pid ) {
			$pid = SPRequest::sid();
		}
		$this->assign( $pid, 'parent' );
		$id = $this->get( 'category.id' );
		$id = $id ? $id : $pid;
		if( $id ) {
			$path = $this->parentPath( $id );
		}
		$this->assign( $path, 'parent_path' );
		$url = Sobi::Url( [ 'task' => 'category.parents', 'out' => 'json', 'format' => 'raw' ], true );
		$this->assign( $url, 'parent_ajax_url' );
		/* @TODO  */
		$tpl = str_replace( implode( '/', [ 'usr', 'templates', 'category' ] ), 'views/tpl/', $this->_template.'.php' );
		Sobi::Trigger( 'Display', $this->name(), [ &$this ] );
		include( $tpl );
		Sobi::Trigger( 'AfterDisplay', $this->name() );
	}

	public function icon()
	{
		/* @TODO  */
		$tpl = str_replace( implode( '/', [ 'usr', 'templates', 'category' ] ), 'views/tpl/', $this->_template.'.php' );
//		$tpl = str_replace( implode( DS, array( 'usr', 'templates', 'category' ) ), DS.'views'.DS .'tpl'.DS, $this->_template.'.php' );
		include( $tpl );
	}
}
