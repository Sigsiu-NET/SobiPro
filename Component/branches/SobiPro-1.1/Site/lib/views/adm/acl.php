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
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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
		SPLoader::loadClass( 'html.tooltip' );
		switch ( $this->get( 'task' ) ) {
			case 'list':
				$this->listRules();
				break;
			case 'add':
			case 'edit':
				$this->edit();
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
		if( is_array( $get ) && count( $get ) ) {
			foreach ( $get as $section ) {
				$put[] = $section->id;
			}
			$put = Sobi::Txt( $put, 'name', 'section' );
			foreach ( $put as $id => $vals ) {
				$put[ $id ] = $vals[ 'value' ];
			}
		}
		$this->set( $put, 'sections' );
		$put = array();
		$get = $this->get( 'adm_permissions' );
		if( is_array( $get ) && count( $get ) ) {
			foreach ( $get as $permission ) {
				if( !isset( $put[ $permission->subject ] ) ) {
					$put[ $permission->subject ] = array();
				}
				$k = $permission->action.'_'.$permission->value;
				$put[ $permission->subject ][ $permission->pid ] = Sobi::Txt( 'permissions.'.$k );
			}
		}
		$this->set( $put, 'adm_permissions' );
		$put = array();
		$get = $this->get( 'front_permissions' );
		if( is_array( $get ) && count( $get ) ) {
			foreach ( $get as $permission ) {
				if( !isset( $put[ $permission->subject ] ) ) {
					$put[ $permission->subject ] = array();
				}
				$k = $permission->action.'_'.$permission->value;
				$put[ $permission->subject ][ $permission->pid ] = Sobi::Txt( 'permissions.'.$k );
			}
		}
		$this->set( $put, 'front_permissions' );

		$sections = array();
		$perms = array();
		$get = $this->get( 'selected_permissions' );
		if( count( $get ) ) {
			foreach ( $get as $map => $keys ) {
				$sections[] =  $keys[ 'sid' ];
				$perms[] = $keys[ 'pid' ];
			}
		}
		$this->set( array_unique( $perms ), 'selected_permissions' );
		$this->set( array_unique( $sections ), 'selected_sections' );
	}
	/**
	 *
	 */
	private function listRules()
	{
		$rules = $this->get( 'rules' );
		$_rules = array();
		if( count( $rules ) ) {
			/* get icons */
			$up 		= /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.unpublished' );
			$pu 		= /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.published' );
			foreach ( $rules as $rule ) {
				$id = $rule->rid;
				$state = $rule->state ? 1 : 0;
				$img = $state == 1 ? $pu : $up;
				$action = $state ? 'acl.disable' : 'acl.enable';
				$name = strlen( $rule->name ) ? $rule->name : $rule->nid;

				/* translate alternative text */
				$s = Sobi::Txt( 'acl.state_head' );
				$a = Sobi::Txt( 'state_'.( $state ? 'on' : 'off' ) );
				$img = SPTooltip::toolTip( $a, $s, $img );

				/* if user has permission for this action */
				if( Sobi::Can( 'acl.manage' ) ) {
					$surl = Sobi::Url( array( 'task' => $action, 'rid' => $id ) );
					$img = "<a href=\"{$surl}\" title=\"{$a}\">{$img}</a>";
				}

				$url = Sobi::Url( array( 'task' => 'acl.edit', 'rid' => $id ) );
				$_rule = array();
				$_rule[ 'id' ] = $id;
				$_rule[ 'nid' ] = $rule->nid;
				$_rule[ 'name' ] = "<a href=\"{$url}\">{$name}</a>";
				$_rule[ 'state' ] = $img;
				$_rule[ 'checkbox' ] = "<input type=\"checkbox\" name=\"rid[]\" value=\"{$id}\" onclick=\"SPCheckListElement( this )\" />";
				$_rule[ 'validSince' ] = $this->date( $rule->validSince );
				$_rule[ 'validUntil' ] = $this->date( $rule->validUntil, false );
				$_rule[ 'note' ] = $rule->note;
				$_rule[ 'url' ] = $url;
				$_rule[ 'perms_count' ] = '@TODO: 99';
				$_rule[ 'group_count' ] = '@TODO: 5';
				$_rules[] = $_rule;
			}
		}
		$this->set( $_rules, 'rules' );
		$this->assign(
			SPLists::tableHeader(
				array(
							'checkbox' 		=> 2,
							'rid' 			=> 1,
							'name' 			=> 1,
							'state' 		=> 1,
							'validSince' 	=> 1,
							'validUntil' 	=> 1,
							'perms_count' 	=> 0,
							'group_count' 	=> 0
				), 'acl', 'rid'
			),
			'header'
		);
	}

	/**
	 * @param string $title
	 */
	public function setTitle( $title )
	{
		$task = $this->get( 'task' );
		if( $task != 'list' ) {
			$title .= '_'.$task;
		}
		$name = $this->get( 'rule.name' );
		$title = Sobi::Txt( $title, array( 'rule' => $name ) );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title');
	}
}
?>