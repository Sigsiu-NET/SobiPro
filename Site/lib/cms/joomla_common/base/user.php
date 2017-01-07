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

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:51:03 PM
 */
class SPJoomlaUser extends JUser
{
	/**
	 * @var array
	 */
	protected $_permReq = [];
	/**
	 * @var array
	 */
	protected $_permissions = [];
	/**
	 * @var array
	 */
	protected $_availablePerm = [];
	/**
	 * @var array
	 */
	protected $_pmap = [];
	/**
	 * @var array
	 */
	protected $_prules = [];
	/**
	 * @var array
	 */
	protected $_prequest = [];

	protected $_special = [ 'txt.js', 'progress' ];


	/* get all parent groups */
	protected function parentGids()
	{
//		$hold = array();
		if ( count( $this->gid ) ) {
			foreach ( $this->gid as $gid ) {
				if ( $gid >= 5000 ) {
					$gids = [];
					while ( $gid > 5000 ) {
						try {
							$gid = SPFactory::db()->select( 'pid', 'spdb_user_group', [ 'gid' => $gid, 'enabled' => 1 ] )->loadResult();
							$gids[ ] = $gid;
						} catch ( SPException $x ) {
							Sobi::Error( 'permissions', SPLang::e( 'Cannot load additional gids. %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
						}
					}
					/** */
					$cgids = JFactory::getACL()->get_group_parents( $gid, 'ARO', 'RECURSE' );
					$gids = array_merge( $gids, $cgids );
				}
				else {
					$gids = JFactory::getACL()->get_group_parents( $gid, 'ARO', 'RECURSE' );
				}
				if ( is_array( $gids ) && count( $gids ) ) {
					$this->gid = array_merge( $gids, $this->gid );
				}
			}
		}
		// PHP 5.2.9 bug work-around
		if ( defined( 'PHP_VERSION_ID' ) && version_compare( PHP_VERSION, '5.2.9' ) < 0 ) {
			$this->gid = array_unique( $this->gid, SORT_STRING );
		}
		else {
			$this->gid = array_unique( $this->gid );
		}
	}

	protected function spGroups()
	{
		try {
			$db =& SPFactory::db();
			$valid = $db->valid( 'rel.validUntil', 'rel.validSince', 'grp.enabled' );
			$join = [
					[ 'table' => 'spdb_user_group', 'as' => 'grp', 'key' => 'gid' ],
					[ 'table' => 'spdb_users_relation', 'as' => 'rel', 'key' => 'gid' ]
			];
			$gids = $db->select( 'rel.gid', $db->join( $join ), [ '@VALID' => $valid, 'uid' => $this->id ] )->loadResultArray();
			if ( count( $gids ) ) {
				$this->gid = array_merge( $gids, $this->gid );
			}
		} catch ( SPException $x ) {
			Sobi::Error( 'permissions', sprintf( 'Cannot load additional gids. %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
	}

	public static function groups( $gids )
	{
		$groups = [];
		if ( $gids instanceof self ) {
			$gids = $gids->get( 'gid' );
		}
		if ( count( $gids ) ) {
			$groups = array_flip( $gids );
			$r = SPFactory::db()->select( [ 'groupName', 'gid' ], 'spdb_user_group', [ 'gid' => $gids ] )->loadAssocList( 'gid' );
			if ( count( $r ) ) {
				foreach ( $r as $gid => $data ) {
					if ( isset( $groups[ $gid ] ) ) {
						$groups[ $gid ] = $data[ 'groupName' ];
					}
				}
			}
			if ( count( $r ) < count( $groups ) ) {
				$r = SPFactory::db()->select( [ 'name', 'id' ], '#__core_acl_aro_groups', [ 'id' => $gids ] )->loadAssocList( 'id' );
				if ( count( $r ) ) {
					foreach ( $r as $gid => $data ) {
						if ( isset( $groups[ $gid ] ) ) {
							$groups[ $gid ] = $data[ 'name' ];
						}
					}
				}
			}
		}
		return $groups;
	}

	public static function availableGroups()
	{
		$groups = [ 0 => 'visitor' ];
		$r = SPFactory::db()->select( [ 'groupName', 'gid' ], 'spdb_user_group' )->loadAssocList( 'gid' );
		if ( count( $r ) ) {
			foreach ( $r as $gid => $data ) {
				$groups[ $gid ] = $data[ 'groupName' ];
			}
		}
		$r = SPFactory::db()->select( [ 'name', 'id' ], '#__core_acl_aro_groups' )->loadAssocList( 'id' );
		if ( count( $r ) ) {
			foreach ( $r as $gid => $data ) {
				$groups[ $gid ] = $data[ 'name' ];
			}
		}
		return $groups;
	}

	/**
	 * check permission for an action
	 * @param $subject
	 * @param string $action - e.g. edit
	 * @param string $value
	 * @param null $section
	 * @internal param string $ownership - e.g. own, all or global
	 * @return bool - true if authorized
	 */
	public function can( $subject, $action = 'access', $value = 'valid', $section = null )
	{
		if ( strstr( $subject, '.' ) ) {
			$subject = explode( '.', $subject );
			$action = $subject[ 1 ];
			if ( isset( $subject[ 2 ] ) ) {
				$value = $subject[ 2 ];
			}
			$subject = $subject[ 0 ];
		}
		if ( !( $section ) ) {
			$section = Sobi::Section();
		}
		$can = $this->authorisePermission( $section, $subject, $action, $value );
		if ( SPFactory::registry()->__isset( 'plugins' ) ) {
			Sobi::Trigger( 'Authorise', 'Permission', [ &$can, $section, $subject, $action, $value ] );
		}
		return $can;
	}

	/**
	 * @see #can
	 * @param string $section
	 * @param string $subject
	 * @param string $action
	 * @param string $value
	 * @return bool
	 */
	public function authorisePermission( $section, $subject, $action, $value )
	{
		if ( $this->isAdmin() ) {
			return true;
		}
		// native joomla ACL
		if ( $subject == 'cms' ) {
			return JFactory::getUser()->authorise( 'core.' . $action, 'com_sobipro' );
		}
		/* translate automatic created request */
		switch ( $action ) {
			case 'cancel':
				return true;
				break;
			case 'save':
			case 'submit':
				$id = SPRequest::int( 'sid', SPRequest::int( 'rid' ) );
				$action = $id ? 'edit' : 'add';
			case 'enable':
			case 'hide':
			case 'disable':
				$action = 'manage';
				break;
			case 'apply':
				$action = 'edit';
			case 'details':
			case 'view':
				$action = 'access';
		}
		if ( in_array( $subject, [ 'acl', 'config', 'extensions' ] ) ) {
			$action = 'manage';
			$section = 0;
		}
		if ( !$section ) {
			$value = 'global';
			if ( in_array( SPRequest::task(), $this->_special ) ) {
				return true;
			}
		}
		/* admin panel or site front */
		$site = SOBI_ACL;
		/* initialise */
		$auth = false;
		/* if not initialised */
		if ( !( isset( $this->_permissions[ $section ] ) ) || !count( $this->_permissions[ $section ] ) ) {
			$this->getPermissions( $section );
		}

		/* if already requested, return the answer */
		$i = "[{$site}][{$section}][{$action}][{$subject}][{$value}]";
		if ( isset( $this->_prequest[ $i ] ) ) {
			return $this->_prequest[ $i ];
		}
		if ( isset( $this->_permissions[ $section ] ) ) {
			if ( isset( $this->_permissions[ $section ][ $subject ] ) ) {
				if ( isset( $this->_permissions[ $section ][ $subject ][ $action ] ) ) {
					if ( isset( $this->_permissions[ $section ][ $subject ][ $action ][ $value ] ) ) {
						$auth = $this->_permissions[ $section ][ $subject ][ $action ][ $value ];
					}
					elseif ( isset( $this->_permissions[ $section ][ $subject ][ $action ][ '*' ] ) ) {
						$auth = $this->_permissions[ $section ][ $subject ][ $action ][ '*' ];
					}
				}
				elseif ( isset( $this->_permissions[ $section ][ $subject ][ '*' ] ) ) {
					$auth = $this->_permissions[ $section ][ $subject ][ '*' ];
				}
			}
			elseif ( isset( $this->_permissions[ $section ][ '*' ] ) ) {
				$auth = $this->_permissions[ $section ][ '*' ];
			}
		}

		// @@ just for tests
//		$a = ( $auth ) ? 'GRANTED' : 'DENIED';//var_export( debug_backtrace( false ), true ) ;
//		SPConfig::debOut("{$action} {$subject} {$value} === {$a} ");

		/* store the answer for future request */
		$this->_prequest[ $i ] = $auth;
		return $auth;
	}

	/**
	 * Checks if the current user is an admin
	 * @return bool
	 */
//	public function isAdmin()
//	{
//		return ( ( $this->usertype == 'Super Administrator' || $this->usertype == 'Administrator' ) && $this->id );
//	}

	/**
	 * Enter description here...
	 * @param null $sid
	 * @return bool
	 */
	protected function getPermissions( $sid = null )
	{
		$sid = $sid ? $sid : Sobi::Section();
		if ( isset( $this->_permissions[ $sid ] ) ) {
			return true;
		}
		/* if it is for super admin - always true */
		if ( $this->isAdmin() ) {
			return true;
		}
		/* @var SPdb $db */
		$db =& SPFactory::db();

		/* first thing we need is all rules id for the group where the user is assigned to */
		$join = [
				[ 'table' => 'spdb_permissions_groups', 'as' => 'spgr', 'key' => 'rid' ],
				[ 'table' => 'spdb_permissions_rules', 'as' => 'sprl', 'key' => 'rid' ]
		];
		$gids = implode( ', ', $this->gid );
		$valid = $db->valid( 'sprl.validUntil', 'sprl.validSince', 'state' );
		$valid .= "AND spgr.gid in( {$gids} ) ";
		$db->dselect( 'sprl.rid', $db->join( $join ), [ '@VALID' => $valid ] );
		try {
			$this->_prules = $db->loadResultArray();
		} catch ( SPException $x ) {
			Sobi::Error( 'permissions', SPLang::e( 'CANNOT_GET_PERMISSIONS', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
		}
		/* if we have the rules ids we need to get permission for this section and global permission */
		if ( count( $this->_prules ) ) {
			try {
				$db->select( 'pid', 'spdb_permissions_map', [ 'sid' => $sid, 'rid' => $this->_prules ] );
				$permissions = $db->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( 'permissions', SPLang::e( 'CANNOT_GET_USERS_DATA', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
			}
		}
		/* get all available permissions */
		try {
			$db->select( '*', 'spdb_permissions', [ 'site' => SOBI_ACL, 'published' => 1 ] );
			$this->_availablePerm = $db->loadAssocList( 'pid' );
		} catch ( SPException $x ) {
			Sobi::Error( 'permissions', SPLang::e( 'CANNOT_GET_PERMISSIONS', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
		$this->_permissions[ $sid ] = [];
		/* create permissions array */
		if ( count( $permissions ) ) {
			foreach ( $permissions as $perm ) {
				if ( isset( $this->_availablePerm[ $perm ] ) ) {
					if ( !( isset( $this->_permissions[ $sid ][ $this->_availablePerm[ $perm ][ 'subject' ] ] ) ) ) {
						$this->_permissions[ $sid ][ $this->_availablePerm[ $perm ][ 'subject' ] ] = [];
					}
					$this->_permissions[ $sid ][ $this->_availablePerm[ $perm ][ 'subject' ] ][ $this->_availablePerm[ $perm ][ 'action' ] ][ $this->_availablePerm[ $perm ][ 'value' ] ] = true;
				}
			}
		}
	}

	/**
	 * Getting base data from Joomla! users table.
	 * Often there are only a few information needed so it does not make
	 * sense to instance the big object just to get these data
	 * @param array $id
	 * @return array
	 */
	public static function getBaseData( $id )
	{
		if ( is_int( $id ) ) {
			$ids = [ $id ];
		}
		else {
			$ids = $id;
		}
		if ( !count( $ids ) ) {
			return false;
		}
		try {
			$data = SPFactory::db()
					->select( '*', '#__users', [ 'id' => $ids ] )
					->loadObjectList( 'id' );
		} catch ( SPException $x ) {
			Sobi::Error( 'user', SPLang::e( 'CANNOT_GET_USERS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
		if ( is_int( $id ) ) {
			return isset( $data[ $id ] ) ? $data[ $id ] : null;
		}
		else {
			return $data;
		}
	}

	/**
	 * @return SPUser
	 */
	public static function & getCurrent()
	{
		static $user = false;
		if ( !$user || !( $user instanceof SPUser ) ) {
			$uid = JFactory::getUser()->get( 'id' );
			$user = new SPUser( $uid );
		}
		return $user;
	}

	/**
	 * Sets the value of a user state variable.
	 * @param    string $key - The path of the state.
	 * @param    string $value - The value of the variable.
	 * @return    mixed    The previous state, if one existed.
	 */
	public function setUserState( $key, &$value )
	{
		return JFactory::getApplication()->setUserState( "com_sobipro.{$key}", $value );
	}

	/**
	 * Gets the value of a user state variable.
	 * @param    string $key - The key of the user state variable.
	 * @param    string $request - The name of the variable passed in a request.
	 * @param    string $default - The default value for the variable if not found. Optional.
	 * @param    string $type - Filter for the variable.
	 * @return    mixed
	 */
	public function & getUserState( $key, $request, $default = null, $type = 'none' )
	{
		$r = JFactory::getApplication()->getUserStateFromRequest( "com_sobipro.{$key}", $request, $default, $type );
		SPRequest::set( $request, $r );
		return $r;
	}

	/**
	 * Sets the value of a user data.
	 * @param    string $key - The path of the state.
	 * @param    string $value - The value of the variable.
	 * @return    mixed    The previous state, if one existed.
	 */
	public function setUserData( $key, &$value )
	{
		return self::setUserState( $key, $value );
	}

	/**
	 * Gets the value of a user data stored in session
	 * @param    string $key - The key of the user state variable.
	 * @param    string $default - The default value for the variable if not found. Optional.
	 * @return    mixed
	 */
	public function & getUserData( $key, $default = null )
	{
		$r = JFactory::getApplication()->getUserState( "com_sobipro.{$key}", $default );
		return $r;
	}

	/**
	 * @param int $id
	 * @return string
	 */
	public static function userUrl( $id )
	{
		return 'index.php?option=com_users&amp;task=edit&amp;cid[]=' . $id;
	}
}
