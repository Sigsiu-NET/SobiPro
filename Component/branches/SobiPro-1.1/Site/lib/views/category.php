<?php
/**
 * @version: $Id: category.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/category.php $
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
		$this->assign( Sobi::Url( array( 'task' => 'category.parents', 'out' => 'json', 'format' => 'raw' ), true ), 'parent_ajax_url' );
		/* @TODO  */
		$tpl = str_replace( implode( DS, array( 'usr', 'templates', 'category' ) ), DS.'views'.DS .'tpl'.DS, $this->_template.'.php' );
		Sobi::Trigger( 'Display', $this->name(), array( &$this ) );
		$action = $this->key( 'action' );
		include( $tpl );
		Sobi::Trigger( 'AfterDisplay', $this->name() );
	}

	public function icon()
	{
		/* @TODO  */
		$tpl = str_replace( implode( DS, array( 'usr', 'templates', 'category' ) ), DS.'views'.DS .'tpl'.DS, $this->_template.'.php' );
		$action = $this->key( 'action' );
		include( $tpl );
	}
}
