<?php
/**
 * @version: $Id: acl.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/acl.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Jan-2009 2:44:34 PM
 */
class SPAclView extends SPAdmView
{
	/**
	 *
	 */
	public function display()
	{
		switch ( $this->get( 'task' ) ) {
			case 'add':
			case 'edit':
				$this->edit();
				$this->determineTemplate( 'acl', 'edit' );
				break;
		}
		parent::display();
	}

	/**
	 */
	private function edit()
	{
		$put = array();
		$get = $this->get( 'groups' );
		foreach ( $get as $group ) {
			$put[ $group[ 'value' ] ] = $group[ 'text' ];
		}
		$this->set( $put, 'groups' );
		$put = array();
		$get = $this->get( 'sections' );
		if ( is_array( $get ) && count( $get ) ) {
			foreach ( $get as $section ) {
				$put[ ] = $section->id;
			}
			$put = Sobi::Txt( $put, 'name', 'section' );
			foreach ( $put as $id => $vals ) {
				$put[ $id ] = $vals[ 'value' ];
			}
		}
		$this->set( $put, 'sections' );
		$put = array();
		$get = $this->get( 'adm_permissions' );
		if ( is_array( $get ) && count( $get ) ) {
			foreach ( $get as $permission ) {
				if ( !isset( $put[ $permission->subject ] ) ) {
					$put[ $permission->subject ] = array();
				}
				$k = $permission->action . '_' . $permission->value;
				$put[ $permission->subject ][ $permission->pid ] = Sobi::Txt( 'permissions.' . $k );
			}
		}
		$this->set( $put, 'adm_permissions' );
		$put = array();
		$get = $this->get( 'front_permissions' );
		if ( is_array( $get ) && count( $get ) ) {
			foreach ( $get as $permission ) {
				if ( !isset( $put[ $permission->subject ] ) ) {
					$put[ $permission->subject ] = array();
				}
				$k = $permission->action . '_' . $permission->value;
				$put[ $permission->subject ][ $permission->pid ] = Sobi::Txt( 'permissions.' . $k );
			}
		}
		$this->set( $put, 'front_permissions' );
		$sections = array();
		$perms = array();
		$get = $this->get( 'selected_permissions' );
		if ( count( $get ) ) {
			foreach ( $get as $map => $keys ) {
				$sections[ ] = $keys[ 'sid' ];
				$perms[ ] = $keys[ 'pid' ];
			}
		}
		$this->set( array_unique( $perms ), 'selected_permissions' );
		$this->set( array_unique( $sections ), 'selected_sections' );
	}

	/**
	 * @param string $title
	 * @return string
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'rule.name' );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		$title = Sobi::Txt( $title, array( 'rule_name' => $name ) );
		$title = parent::setTitle( $title );
		return $title;
	}
}
