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

SPLoader::loadController( 'config', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 29-Jan-2009 12:10:24 PM
 */
final class SPAclCtrl extends SPConfigAdmCtrl
{
	/*** @var string */
	protected $_type = 'acl';
	/*** @var string */
	protected $_defTask = 'list';
	/*** @var array */
	private $_perms = [];

	/**
	 */
	public function __construct()
	{
		if ( !( Sobi::Can( 'cms.admin' ) ) && !( Sobi::Can( 'cms.apps' ) ) ) {
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
				$this->response( Sobi::Url( 'acl' ) );
				break;
			case 'save':
			case 'apply':
				$this->save( $this->_task == 'apply' );
				break;
			case 'delete':
				$this->delete();
				break;
			case 'toggle.enabled':
				$this->toggle();
				break;
			case 'section':
				$this->listSectionRules();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !( Sobi::Trigger( 'Execute', $this->name(), [ &$this ] ) ) ) {
					Sobi::Error( 'ACL', SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	private function toggle()
	{
		$state = SPFactory::db()
			->select( 'state', 'spdb_permissions_rules', [ 'rid' => SPRequest::int( 'rid' ) ] )
			->loadResult();

		return $this->state( !( $state ) );
	}

	/**
	 * @param $subject
	 * @param $action
	 * @param $value
	 * @param string $site
	 *
	 * @internal param
	 * @return void
	 */
	public function removePermission( $subject, $action, $value, $site = 'front' )
	{
		Sobi::Trigger( 'Acl', __FUNCTION__, [ &$subject, &$action, &$value, &$site ] );
		try {
			SPFactory::db()->delete( 'spdb_permissions', [ 'subject' => $subject, 'action' => $action, 'value' => $value, 'site' => $site ] );
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
	 * @param $published
	 *
	 * @return bool
	 */
	public function addPermission( $subject, $action, $value, $site = 'front', $published = 1 )
	{
		Sobi::Trigger( 'Acl', __FUNCTION__, [ &$subject, &$action, &$value, &$site, &$published ] );
		if ( !( count( $this->_perms ) ) ) {
			$this->loadPermissions();
		}
		if (
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
				$db->insert( 'spdb_permissions', [ null, $subject, $action, $value, $site, $published ] );
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
			if ( !( isset( $this->_perms[ $permission->site ] ) ) ) {
				$this->_perms[ $permission->site ] = [];
			}
			if ( !( isset( $this->_perms[ $permission->site ][ $permission->subject ] ) ) ) {
				$this->_perms[ $permission->site ][ $permission->subject ] = [];
			}
			if ( !( isset( $this->_perms[ $permission->site ][ $permission->subject ][ $permission->action ] ) ) ) {
				$this->_perms[ $permission->site ][ $permission->subject ][ $permission->action ] = [];
			}
			$this->_perms[ $permission->site ][ $permission->subject ][ $permission->action ][] = $permission->value;
		}
	}

	public function addNewRule( $name, $sections, $perms, $groups, $note = null )
	{
		SPLoader::loadClass( 'cms.base.users' );
		$db =& SPFactory::db();
		try {
			$db->insertUpdate( 'spdb_permissions_rules', [ 'rid' => 'NULL', 'name' => $name, 'nid' => SPLang::nid( $name ), 'validSince' => $db->getNullDate(), 'validUntil' => $db->getNullDate(), 'note' => $note, 'state' => 1 ] );
			$rid = $db->insertid();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_CREATE_RULE_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		$affectedGroups = [];
		$gids = SPUser::availableGroups();
		foreach ( $gids as $id => $group ) {
			if ( in_array( $group, $groups ) || in_array( strtolower( $group ), $groups ) ) {
				$affectedGroups[] = [ 'rid' => $rid, 'gid' => $id ];
			}
		}
		try {
			$db->insertArray( 'spdb_permissions_groups', $affectedGroups );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		if ( !( count( $this->_perms ) ) ) {
			$this->loadPermissions();
		}
		$map = [];
		foreach ( $perms as $perm ) {
			$perm = explode( '.', $perm );
			$site = ( isset( $perm[ 3 ] ) ) ? $perm[ 3 ] : 'front';
			$pid = $db
				->select( 'pid', 'spdb_permissions', [ 'subject' => $perm[ 0 ], 'action' => $perm[ 1 ], 'value' => $perm[ 2 ], 'site' => $site ] )
				->loadResult();
			$db->getQuery();
			if ( $pid ) {
				foreach ( $sections as $sid ) {
					$map[] = [ 'rid' => $rid, 'sid' => $sid, 'pid' => $pid ];
				}
			}
		}
		if ( count( $map ) ) {
			try {
				$db->insertArray( 'spdb_permissions_map', $map, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}

		return $rid;
	}

	/**
	 * Save a rule
	 *
	 * @param bool $apply
	 * @param bool $clone
	 */
	protected function save( $apply, $clone = false )
	{
		Sobi::Trigger( 'Save', 'Acl', [ &$this ] );
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$rid = SPRequest::int( 'rid', 'null' );
		$this->validate( 'acl.edit', [ 'task' => 'acl.edit', 'rid' => $rid ] );
		if ( $rid ) {
			$this->remove( $rid );
		}
		$vs = SPRequest::timestamp( 'set_validSince' );
		$vu = SPRequest::timestamp( 'set_validUntil' );
		$vs = $vs ? gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $vs ) : null;
		$vu = $vu ? gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $vu ) : null;

		$name = SPRequest::string( 'set_name' );
		$nid = SPRequest::cmd( 'set_nid' );
		$note = SPRequest::string( 'set_note' );
		$state = SPRequest::int( 'set_state', 1 );
		$gids = SPRequest::arr( 'set_groups' );
		$sids = SPRequest::arr( 'set_sections' );
		$pf = SPRequest::arr( 'set_permissions', [] );
		$pa = SPRequest::arr( 'set_adm_permissions', [] );
		// if can publish any, then can see any unpublished
		if ( in_array( 20, $pf ) ) {
			$pf[] = 14;
		}
		// if can publish own, then can see own unpublished
		if ( in_array( 21, $pf ) ) {
			$pf[] = 12;
		}
		if ( in_array( 19, $pf ) ) {
			$pf[] = 15;
		}
		$perms = array_merge( $pf, $pa );

		/* @var SPdb $db */
		$db = SPFactory::db();
		/* update or insert the rule definition */
		try {
			$db->insertUpdate( 'spdb_permissions_rules', [ 'rid' => $rid, 'name' => $name, 'nid' => $nid, 'validSince' => $vs, 'validUntil' => $vu, 'note' => $note, 'state' => $state ] );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_CREATE_RULE_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$rid = ( int ) $rid ? ( int ) $rid : $db->insertid();

		/* insert the groups ids */
		if ( count( $gids ) ) {
			foreach ( $gids as $i => $gid ) {
				$gids[ $i ] = [ 'rid' => $rid, 'gid' => $gid ];
			}
			try {
				$db->insertArray( 'spdb_permissions_groups', $gids );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}

		try {
			$admPermissions = $db->select( '*', 'spdb_permissions', [ 'site' => 'adm' ] )
				->loadResultArray();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_GET_PERMISSIONS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		/* create permission and section map */
		if ( count( $sids ) && count( $perms ) ) {
			$map = [];
			/* travel the sections */
			foreach ( $sids as $sid ) {
				foreach ( $perms as $pid ) {
					if ( in_array( $pid, $admPermissions ) ) {
						$map[] = [ 'rid' => $rid, 'sid' => $sid, 'pid' => $pid ];
					}
					else {
						$map[] = [ 'rid' => $rid, 'sid' => $sid, 'pid' => $pid ];
					}
				}
			}
			try {
				$db->insertArray( 'spdb_permissions_map', $map, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'CANNOT_INSERT_GROUPS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		SPFactory::cache()->cleanAll();
		/* trigger plugins */
		Sobi::Trigger( 'AfterSave', 'Acl', [ &$this ] );
		/* set redirect */
		$this->response( Sobi::Url( $apply ? [ 'task' => 'acl.edit', 'rid' => $rid ] : 'acl' ), Sobi::Txt( 'ACL_RULE_SAVED' ), !( $apply ), SPC::SUCCESS_MSG, [ 'sets' => [ 'rid' => $rid ] ] );
	}

	/**
	 * @return void
	 * @internal param int $rid
	 */
	private function delete()
	{
		$rids = SPRequest::arr( 'rid', [] );
		/* @var SPdb $db */
		$db = SPFactory::db();
		if ( !count( $rids ) ) {
			if ( SPRequest::int( 'rid' ) ) {
				$rids = [ SPRequest::int( 'rid' ) ];
			}
			else {
				$this->response( Sobi::Back(), Sobi::Txt( 'ACL_SELECT_RULE_FIRST' ), true, SPC::ERROR_MSG );
			}
		}
		try {
			$db->delete( 'spdb_permissions_groups', [ 'rid' => $rids ] );
			$db->delete( 'spdb_permissions_map', [ 'rid' => $rids ] );
			$db->delete( 'spdb_permissions_rules', [ 'rid' => $rids ] );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_REMOVE_RULES_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$this->response( Sobi::Url( 'acl' ), Sobi::Txt( 'ACL_RULE_DELETED' ), true, SPC::SUCCESS_MSG );
	}

	/**
	 * @param int $rid
	 */
	private function remove( $rid )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		try {
			$db->delete( 'spdb_permissions_groups', [ 'rid' => $rid ] );
			$db->delete( 'spdb_permissions_map', [ 'rid' => $rid ] );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'CANNOT_REMOVE_PERMISSIONS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * @param bool $state
	 *
	 * @return bool
	 */
	protected function state( $state )
	{
		$rid = SPRequest::int( 'rid' );
		$where = null;
		if ( !$rid ) {
			$rid = SPRequest::arr( 'rid' );
			if ( is_array( $rid ) && !empty( $rid ) ) {
				$where = [ 'rid' => $rid ];
			}
		}
		else {
			$where = [ 'rid' => $rid ];
		}
		if ( !$where ) {
			$this->response( Sobi::Back(), Sobi::Txt( 'ACL_SELECT_RULE_FIRST' ), true, SPC::ERROR_MSG );

			return false;
		}
		try {
			SPFactory::db()->update( 'spdb_permissions_rules', [ 'state' => $state ], $where );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		$this->response( Sobi::Back(), Sobi::Txt( 'ACL.MSG_STATE_CHANGED' ), true, SPC::SUCCESS_MSG );
	}

	/**
	 */
	private function edit()
	{
		if ( !( Sobi::Can( 'cms.admin' ) ) && !( Sobi::Can( 'cms.apps' ) ) ) {
			Sobi::Error( 'ACL', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 403, __LINE__, __FILE__ );
			exit();
		}
		$rid = SPRequest::int( 'rid' );
		SPLoader::loadClass( 'cms.base.users' );
		$db = SPFactory::db();
		try {
			$sections = $db
				->select( '*', 'spdb_object', [ 'oType' => 'section' ] )
				->loadObjectList();
			$admPermissions = $db
				->select( '*', 'spdb_permissions', [ 'site' => 'adm', 'published' => 1 ] )
				->loadObjectList();
			$frontPermissions = $db
				->select( '*', 'spdb_permissions', [ 'site' => 'front', 'published' => 1 ] )
				->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		/** @var $view SPAclView */
		$view = SPFactory::View( 'acl', true );
		$view->assign( $this->_task, 'task' );
		$view->assign( $sections, 'sections' );
		$view->assign( $admPermissions, 'adm_permissions' );
		$view->assign( $frontPermissions, 'permissions' );

		if ( $rid ) {
			try {
				$rule = $db
					->select( '*', 'spdb_permissions_rules', [ 'rid' => $rid ] )
					->loadAssocList( 'rid' );
				$rule = $rule[ $rid ];
				if ( $rule[ 'validSince' ] == $db->getNullDate() ) {
					$rule[ 'validSince' ] = null;
				}
				if ( $rule[ 'validUntil' ] == $db->getNullDate() ) {
					$rule[ 'validUntil' ] = null;
				}
				$view->assign( $rule[ 'name' ], 'rule' );
				$rule[ 'groups' ] = $db
					->select( 'gid', 'spdb_permissions_groups', [ 'rid' => $rid ] )
					->loadResultArray();

				$rule[ 'permissions' ] = $db
					->select( '*', 'spdb_permissions_map', [ 'rid' => $rid ] )
					->loadAssocList();
				$view->assign( $rule, 'set' );
			}
			catch ( SPException $x ) {
				Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		else {
			$rule = [
				'validUntil'  => null,
				'validSince'  => null,
				'name'        => '',
				'nid'         => '',
				'note'        => '',
				'permissions' => []
			];
			$view->assign( $rule, 'set' );
		}
		$userGroups = $this->userGroups();
		$view->assign( $userGroups, 'groups' );
		$view->display();
	}

	public function userGroups( $disabled = false )
	{
		SPLoader::loadClass( 'cms.base.users' );
		$cgids = SPUsers::getGroupsField();
		if ( $disabled ) {
			foreach ( $cgids as $g => $group ) {
				$cgids[ $g ][ 'disable' ] = true;
			}
		}
		$gids = [];
		$parents = [];
		$groups = [];
		try {
			$ids = SPFactory::db()->select( [ 'pid', 'groupName', 'gid' ], 'spdb_user_group', [ 'enabled' => 1 ] )->loadAssocList( 'gid' );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if ( count( $ids ) ) {
			$this->sortGroups( $ids, $gids, $parents );
		}
		foreach ( $cgids as $group ) {
			$groups[] = $group;
			preg_match( '/\.([&nbsp;]+)\-/', $group[ 'text' ], $nbsp );
			if ( !( isset( $nbsp[ 1 ] ) ) ) {
				$nbsp[ 1 ] = null;
			}
			if ( isset( $parents[ $group[ 'value' ] ] ) ) {
				foreach ( $parents[ $group[ 'value' ] ] as $gid => $grp ) {
					$this->addGroups( $grp, $groups, $nbsp[ 1 ] );
				}
			}
		}

		return $groups;
	}

	private function addGroups( $group, &$groups, $nbsp )
	{
		$nbsp = $nbsp . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$groups[] = [ 'value' => $group[ 'gid' ], 'text' => '.' . $nbsp . '-&nbsp;' . $group[ 'groupName' ] ];
		if ( isset( $group[ 'childs' ] ) && count( $group[ 'childs' ] ) ) {
			foreach ( $group[ 'childs' ] as $gid => $grp ) {
				$this->addGroups( $grp, $groups, $nbsp );
			}
		}
	}

	private function sortGroups( $ids, &$gids, &$parents )
	{
		foreach ( $ids as $gid => $group ) {
			if ( $group[ 'pid' ] >= 5000 ) {
				$this->getGrpChilds( $gid, $ids, $group, $gids );
				if ( !( isset( $gids[ $group[ 'pid' ] ] ) ) ) {
					$gids[ $group[ 'pid' ] ] = $ids[ $group[ 'pid' ] ];
				}
				$gids[ $group[ 'pid' ] ][ 'childs' ][ $gid ] = $group;
			}
			else {
				$gids[ $gid ] = $group;
				$gids[ $gid ][ 'childs' ] = [];
			}
		}
		if ( count( $gids ) ) {
			foreach ( $gids as $gid => $group ) {
				if ( $group[ 'pid' ] >= 5000 ) {
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
			if ( $cgroup[ 'pid' ] == $gid ) {
				if ( isset( $ids[ $gid ] ) ) {
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
		if ( !( Sobi::Can( 'cms.admin' ) ) && !( Sobi::Can( 'cms.apps' ) ) ) {
			Sobi::Error( 'ACL', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 403, __LINE__, __FILE__ );
			exit();
		}
		Sobi::ReturnPoint();
		$order = SPFactory::user()
			->getUserState( 'acl.order', 'position', 'rid.asc' );
		try {
			$rules = SPFactory::db()
				->select( '*', 'spdb_permissions_rules', null, $order )
				->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$menu = $this->createMenu( 'acl' );
		/** @var $view SPAclView */
		SPFactory::View( 'acl', true )
			->assign( $this->_task, 'task' )
			->assign( $rules, 'rules' )
			->assign( $menu, 'menu' )
			->determineTemplate( 'acl', 'list' )
			->display();
	}


	/**
	 */
	private function listSectionRules()
	{
		if ( !( Sobi::Can( 'cms.admin' ) ) && !( Sobi::Can( 'cms.apps' ) ) ) {
			Sobi::Error( 'ACL', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::WARNING, 403, __LINE__, __FILE__ );
			exit();
		}
		Sobi::ReturnPoint();
		$order = SPFactory::user()
			->getUserState( 'acl.order', 'position', 'rid.asc' );


		SPLoader::loadClass( 'cms.base.users' );
		$db = SPFactory::db();
		try {
			$admPermissions = $db
				->select( '*', 'spdb_permissions', [ 'site' => 'adm', 'published' => 1 ] )
				->loadObjectList();
			$frontPermissions = $db
				->select( '*', 'spdb_permissions', [ 'site' => 'front', 'published' => 1 ] )
				->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'ACL', SPLang::e( 'Db reports %s.', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}


		$userGroups = $this->userGroups();
		$menu = $this->createMenu( 'acl.section' );
		/** @var $view SPAclView */
		SPFactory::View( 'acl', true )
			->assign( $this->_task, 'task' )
			->assign( $admPermissions, 'adm_permissions' )
			->assign( $frontPermissions, 'permissions' )
			->assign( $userGroups, 'groups' )
//			->assign( $rules, 'rules' )
			->assign( $menu, 'menu' )
//			->determineTemplate( 'acl', 'rules' )
			->display();
	}

}
