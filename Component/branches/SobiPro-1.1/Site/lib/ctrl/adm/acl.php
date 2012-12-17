<?php
/**
 * @version: $Id: acl.php 950 2011-03-07 18:52:00Z Radek Suski $
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
 * $Date: 2011-03-07 19:52:00 +0100 (Mon, 07 Mar 2011) $
 * $Revision: 950 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/acl.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'config', true );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 29-Jan-2009 12:10:24 PM
 */
final class SPAclCtrl extends SPConfigAdmCtrl
{
	/**
	 * @var string
	 */
	protected $_type = 'acl';
	/**
	 * @var string
	 */
	protected $_defTask = 'list';

	private $_perms = array();

	/**
	 */
	public function __construct()
	{
		if( !Sobi::Can( 'acl.manage' ) ) {
			Sobi::Error( 'ACL', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 403, __LINE__, __FILE__ );
			exit();
		}
		parent::__construct();
	}

	/**
	 */
	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'enable':
			case 'disable':
				$this->state( $this->_task == 'enable' );
				break;
			case 'add':
			case 'edit':
				$this->edit();
				break;
			case 'list':
				$this->listRules();
				break;
			case 'cancel':
				Sobi::Redirect( Sobi::Url( 'acl' ) );
				break;
			case 'save':
			case 'apply':
				$this->save( $this->_task == 'apply' );
				break;
			case 'delete':
				$this->delete();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if( !( Sobi::Trigger( 'Execute', $this->name(), array( &$this ) ) ) ) {
					Sobi::Error( 'ACL', SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	/**
	 * @param $subject
	 * @param $action
	 * @param $value
	 * @param $site
	 * @param $publisched
	 * @return void
	 */
	public function removePermission( $subject, $action, $value, $site = 'front' )
	{
		Sobi::Trigger( 'Acl', __FUNCTION__, array( &$subject, &$action, &$value, &$site ) );
		try {
			SPFactory::db()->delete( 'spdb_permissions', array( 'subject' => $subject, 'action' => $action, 'value' => $value, 'site' => $site ) );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'acl', SPLang::e( 'CANNOT_REMOVE_PERMISSION_DB_ERR', $subject, $action, $action, $x->getMessage() ), SPC::WARNING, 0 );
		}
	}

	/**
	 * @param $subject
	 * @param $action
	 * @param $value
	 * @param $site
	 * @param $publisched
	 * @return void
	 */
	public function addPermission( $subject, $action, $value, $site = 'front', $publisched = 1 )
	{
		Sobi::Trigger( 'Acl', __FUNCTION__, array( &$subject, &$action, &$value, &$site, &$publisched ) );
		if( !( count( $this->_perms ) ) ) {
			$this->loadPermissions();
		}
		if(
			isset( $this->_perms[ $site ][ $subject ] )
			&& isset( $this->_perms[ $site ][ $subject ][ $action ] )
			&& in_array( $value, $this->_perms[ $site ][ $subject ][ $action ] )
		) {
			return true;
		}
		else {
			$this->_perms[ $site ][ $subject ][ $action ][] = $value;
			$db =& SPFactory::db();
			try {
				$db->insert( 'spdb_permissions', array( null, $subject, $action, $value, $site, $publisched ) );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'acl', SPLang::e( 'CANNOT_ADD_NEW_PERMS', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
	}

	private function loadPermissions()
	{
		$db =& SPFactory::db();
		try {
			$db->select( '*', 'spdb_permissions' );
			$permissions = $db->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'acl', SPLang::e( 'CANNOT_GET_PERMISSION_LIST', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		foreach ( $permissions as $permission ) {
			if( !( isset( $this->_perms[ $permission->site ] ) ) ) {
				$this->_perms[ $permission->site ] = array();
			}
			if( !( isset( $this->_perms[ $permission->site ][ $permission->subject ] ) ) ) {
				$this->_perms[ $permission->site ][ $permission->subject ] = array();
			}
			if( !( isset( $this->_perms[ $permission->site ][ $permission->subject ][ $permission->action ] ) ) ) {
				$this->_perms[ $permission->site ][ $permission->subject ][ $permission->action ] = array();
			}
			$this->_perms[ $permission->site ][ $permission->subject ][ $permission->action ][] = $permission->value;
		}
	}

	public function addNewRule( $name, $sections, $perms, $groups, $note = null )
	{
		SPLoader::loadClass( 'cms.base.users' );
		$db =& SPFactory::db();
		try {
			$db->insertUpdate( 'spdb_permissions_rules', array( 'rid' => 'NULL', 'name' => $name, 'nid' => SPLang::nid( $name ), 'validSince' => $db->getNullDate(), 'validUntil' => $db->getNullDate(), 'note' => $note, 'state' => 1 ) );
			$rid = $db->insertid();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_CREATE_RULE_DB_ERR', $x->getMessage() ), SPC::WARNING , 0, __LINE__, __FILE__ );
		}

		$affectedGroups = array();
		$gids = SPUser::availableGroups();
		foreach ( $gids as $id => $group ) {
			if( in_array( $group, $groups ) || in_array( strtolower( $group ), $groups ) ) {
				$affectedGroups[] = array( 'rid' => $rid, 'gid' => $id );
			}
		}
		try {
			$db->insertArray( 'spdb_permissions_groups', $affectedGroups );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING , 0, __LINE__, __FILE__ );
		}

		if( !( count( $this->_perms ) ) ) {
			$this->loadPermissions();
		}
		$map = array();
		foreach ( $perms as $perm ) {
			$perm = explode( '.', $perm );
			$pid = $db->select( 'pid', 'spdb_permissions', array( 'subject' => $perm[ 0 ], 'action' => $perm[ 1 ], 'value' => $perm[ 2 ] ) )->loadResult();
			if( $pid ) {
				foreach ( $sections as $sid ) {
					$map[] = array( 'rid' => $rid, 'sid' => $sid, 'pid' => $pid );
				}
			}
		}
		if( count( $map ) ) {
			try {
				$db->insertArray( 'spdb_permissions_map', $map, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING , 0, __LINE__, __FILE__ );
			}
		}
		return $rid;
	}

	/**
	 * Save a rule
	 *
	 * @param bool $apply
	 */
	protected function save( $apply )
	{
		Sobi::Trigger( 'Save', 'Acl', array( &$this ) );
		if( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$rid = SPRequest::int( 'rid', 'null' );
		if( $rid ) {
			$this->remove( $rid );
		}
		$vs 	= SPRequest::datetime( 'rule_validSince' );
		$vu 	= SPRequest::datetime( 'rule_validUntil' );
		$name 	= SPRequest::string( 'rule_name' );
		$nid 	= SPRequest::cmd( 'rule_nid' );
		$note	= SPRequest::string( 'rule_note' );
		$state	= SPRequest::int( 'state', 1 );
		$gids	= SPRequest::arr( 'rule_groups' );
		$sids	= SPRequest::arr( 'rule_sections' );
		$pf		= SPRequest::arr( 'rule_front_permissions', array() );
		$pa		= SPRequest::arr( 'rule_adm_permissions', array() );
		// if can publish any, then can see any unpublished
		if( in_array( 20, $pf ) ) {
			$pf[] = 14;
		}
		// if can publish own, then can see own unpublished
		if( in_array( 21, $pf ) ) {
			$pf[] = 12;
		}
		if( in_array( 19, $pf ) ) {
			$pf[] = 15;
		}
		$perms 	= array_merge( $pf, $pa );

		/* @var SPdb $db */
		$db =& SPFactory::db();
		/* update or insert the rule definition */
		try {
			$db->insertUpdate( 'spdb_permissions_rules', array( 'rid' => $rid, 'name' => $name, 'nid' => $nid, 'validSince' => $vs, 'validUntil' => $vu, 'note' => $note, 'state' => $state ) );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_CREATE_RULE_DB_ERR', $x->getMessage() ), SPC::WARNING , 0, __LINE__, __FILE__ );
		}
		$rid = ( int ) $rid ? ( int ) $rid : $db->insertid();

		/* insert the groups ids */
		if( count( $gids ) ) {
			foreach ( $gids as $i => $gid ) {
				$gids[ $i ] = array( 'rid' => $rid, 'gid' => $gid );
			}
			try {
				$db->insertArray( 'spdb_permissions_groups', $gids );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING , 0, __LINE__, __FILE__ );
			}
		}

		try {
			$db->select( '*', 'spdb_permissions', array( 'site' => 'adm', 'value' => 'global' ) );
			$admPermissions = $db->loadResultArray();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_GET_PERMISSIONS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		/* create permisssion and section map */
		if( count( $sids ) && count( $perms ) ) {
			$map = array();
			/* travel the sections */
			foreach ( $sids as $sid ) {
				foreach ( $perms as $pid ) {
					if( in_array( $pid, $admPermissions ) ) {
						$map[] = array( 'rid' => $rid, 'sid' => 0, 'pid' => $pid );
					}
					else {
						$map[] = array( 'rid' => $rid, 'sid' => $sid, 'pid' => $pid );
					}
				}
			}
			try {
				$db->insertArray( 'spdb_permissions_map', $map, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING , 0, __LINE__, __FILE__ );
			}
		}

		/* trigger plugins */
		Sobi::Trigger( 'AfterSave', 'Acl', array( &$this ) );

		/* set redirect */
		Sobi::Redirect( Sobi::Url( $apply ? array( 'task' => 'acl.edit', 'rid' => $rid ) : 'acl' ), 'ACL rule has been saved' );
	}

	/**
	 * @param int $rid
	 */
	private function delete()
	{
		$rids = SPRequest::arr( 'rid', array() );
		/* @var SPdb $db */
		$db =& SPFactory::db();
		if( !count( $rids ) ) {
			$rids = array( SPRequest::int( 'rid' ) );
		}
		try {
			$db->delete( 'spdb_permissions_groups', array( 'rid' => $rids ) );
			$db->delete( 'spdb_permissions_map', array( 'rid' => $rids ) );
			$db->delete( 'spdb_permissions_rules', array( 'rid' => $rids ) );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_REMOVE_RULES_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		Sobi::Redirect( Sobi::Url( 'acl' ), 'ACL Rule has been deleted' );
	}

	/**
	 * @param int $rid
	 */
	private function remove( $rid )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		try {
			$db->delete( 'spdb_permissions_groups', array( 'rid' => $rid ) );
			$db->delete( 'spdb_permissions_map', array( 'rid' => $rid ) );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_REMOVE_PERMISSIONS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * @param bool $state
	 * @return bool
	 */
	protected function state( $state )
	{
		$rid = SPRequest::int( 'rid' );
		$where = null;
		if( !$rid ) {
			$rid = SPRequest::arr( 'rid' );
			if( is_array( $rid ) && !empty( $rid ) ) {
				$where = array( 'rid' => $rid );
			}
		}
		else {
			$where = array( 'rid' => $rid );;
		}
		if( !$where ) {
			SPMainFrame::setRedirect( SPMainFrame::getBack(), 'Please select rule to delete from the list', SPC::ERROR_MSG );
			return false;
		}
		try {
			SPFactory::db()->update( 'spdb_permissions_rules', array( 'state' => $state ), $where );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		Sobi::Redirect( SPMainFrame::getBack(), 'ACL.MSG_STATE_CHANGED' );
	}
	/**
	 */
	private function edit()
	{
		$rid = SPRequest::int( 'rid' );
		SPLoader::loadClass( 'html.input' );
		SPLoader::loadClass( 'cms.base.users' );
		$db = SPFactory::db();

		try {
			$db->select( '*', 'spdb_object', array( 'oType' => 'section' ) );
			$sections = $db->loadObjectList();
			$db->select( '*', 'spdb_permissions', array( 'site' => 'adm', 'published' => 1 ) );
			$admPermissions = $db->loadObjectList();
			$db->select( '*', 'spdb_permissions', array( 'site' => 'front', 'published' => 1 ) );
			$frontPermissions = $db->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}

		$class = SPLoader::loadView( 'acl', true );
		$view = new $class();
		$view->assign( $this->_task, 'task' );
		$view->assign( $sections, 'sections' );
		$view->assign( $admPermissions, 'adm_permissions' );
		$view->assign( $frontPermissions, 'front_permissions' );

		if( $rid ) {
			try {
				$db->select( '*', 'spdb_permissions_rules', array( 'rid' => $rid ) );
				$rule = $db->loadAssocList( 'rid' );
				$rule = $rule[ $rid ];
				$view->assign( $rule, 'rule' );
				$db->select( 'gid', 'spdb_permissions_groups', array( 'rid' => $rid ) );
				$selectedGroups = $db->loadResultArray();
				$db->select( '*', 'spdb_permissions_map', array( 'rid' => $rid ) );
				$selectedPermissions = $db->loadAssocList();
				$view->assign( $selectedGroups, 'selected_groups' );
				$view->assign( $selectedPermissions, 'selected_permissions' );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		else {
			$rule = array( 'validUntil' => $db->getNullDate(), 'validSince' => $db->getNullDate(), 'name' => '', 'nid' => '' , 'note' => '' );
			$view->assign( $rule, 'rule' );
		}

		$view->loadConfig( 'acl.edit' );
		$view->setTemplate( 'acl.edit' );
		$view->assign( $this->userGroups(), 'groups' );
		$view->display();
	}

	public function userGroups( $disabled = false )
	{
		SPLoader::loadClass( 'cms.base.users' );
		$cgids = SPUsers::getGroupsField();
		if( $disabled ) {
			foreach ( $cgids as $g => $group ) {
				$cgids[ $g ][ 'disable' ] = true;
			}
		}
		$gids = array();
		$parents = array();
		$groups = array();
		try {
			$ids = SPFactory::db()->select( array( 'pid', 'groupName', 'gid' ), 'spdb_user_group', array( 'enabled' => 1 ) )->loadAssocList( 'gid' );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if( count( $ids ) ) {
			$this->sortGroups( $ids, $gids, $parents );
		}
		foreach ( $cgids as $group ) {
			$groups[] = $group;
			preg_match( '/\.([&nbsp;]+)\-/', $group[ 'text' ], $nbsp );
			if( !( isset( $nbsp[ 1 ] ) ) ) {
				$nbsp[ 1 ] = null;
			}
			if( isset( $parents[ $group[ 'value' ] ] ) ) {
				foreach ( $parents[ $group[ 'value' ] ] as $gid  => $grp ) {
					$this->addGroups( $grp, $groups, $nbsp[ 1 ] );
				}
			}
		}
		return $groups;
	}

	private function addGroups( $group, &$groups, $nbsp )
	{
		$nbsp = $nbsp.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$groups[] = array( 'value' => $group[ 'gid' ], 'text' => '.'.$nbsp.'-&nbsp;'.$group[ 'groupName' ] );
		if( isset( $group[ 'childs' ] ) && count( $group[ 'childs' ] ) ) {
			foreach ( $group[ 'childs' ] as $gid => $grp ) {
				$this->addGroups( $grp, $groups, $nbsp );
			}
		}
	}

	private function sortGroups( $ids, &$gids, &$parents )
	{
		foreach ( $ids as $gid => $group ) {
			if( $group[ 'pid' ] >= 5000 ) {
				$this->getGrpChilds( $gid, $ids, $group, $gids );
				if( !( isset( $gids[ $group[ 'pid' ] ] ) ) ) {
					$gids[ $group[ 'pid' ] ] = $ids[ $group[ 'pid' ] ];
				}
				$gids[ $group[ 'pid' ] ][ 'childs' ][ $gid ] = $group;
			}
			else {
				$gids[ $gid ] = $group;
				$gids[ $gid ][ 'childs' ] = array();
			}
		}
		if( count( $gids ) ) {
			foreach ( $gids as $gid => $group ) {
				if( $group[ 'pid' ] >= 5000 ) {
					unset( $gids[ $gid ] );
				}
				else {
					$parents[ $group[ 'pid' ] ][] = $gids[ $gid ];
				}
			}
		}
	}

	private function getGrpChilds( $gid, $ids, &$group, &$gids )
	{
		foreach ( $ids as $cgid => $cgroup ) {
			if( $cgroup[ 'pid' ] == $gid ) {
				if( isset( $ids[ $gid ] ) ) {
					$this->getGrpChilds( $cgid, $ids, $cgroup, $gids );
					$group[ 'childs' ][ $cgid ] = $cgroup;
				}
			}
		}
	}
	/**
	 */
	private function listRules()
	{
		$order = SPFactory::user()->getUserState( 'acl.order', 'order', 'rid.asc' );
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$db->select( '*', 'spdb_permissions_rules', null, $order );
		try {
			$rules = $db->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$menu = SPFactory::Instance( 'helpers.adm.menu', SPRequest::task() );
		$cfg = SPLoader::loadIniFile( 'etc.adm.config_menu' );
		Sobi::Trigger( 'Create', 'AdmMenu', array( &$cfg ) );
		if( count( $cfg ) ) {
			foreach ( $cfg as $section => $keys ) {
				$menu->addSection( $section, $keys );
			}
		}
		Sobi::Trigger( 'AfterCreate', 'AdmMenu', array( &$menu ) );
		$menu->addCustom( 'GB.CFG.GLOBAL_TEMPLATES', $this->listTemplates() );
		$class = SPLoader::loadView( 'acl', true );
		$view = new $class();
		$view->assign( $this->_task, 'task' );
		$view->loadConfig( 'acl.list' );
		$view->setTemplate( 'acl.list' );
		$view->assign( $rules, 'rules' );
		$view->assign( $menu, 'menu' );
		$view->display();
	}
}
?>
