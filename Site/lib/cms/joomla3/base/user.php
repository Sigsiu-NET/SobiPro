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
require_once dirname( __FILE__ ) . '/../../joomla_common/base/user.php';

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:51:03 PM
 */
class SPUser extends SPJoomlaUser
{
	public function __construct( $id = 0 )
	{
		parent::__construct( $id );
		$this->gid[] = 0;
		// this array is really a bad joke :(
		foreach ( $this->groups as $index => $value ) {
			if ( is_string( $index ) && !( is_numeric( $index ) ) ) {
				$this->gid[] = $value;
				$this->usertype = $index;
			}
			else {
				$this->gid[] = $index;
				$this->usertype = $value;
			}
		}
		$this->spGroups();
		/* include default visitor permissions */
		$this->parentGids();
		Sobi::Trigger( 'UserGroup', 'Appoint', [ $id, &$this->gid ] );
	}

	/**
	 * @param int $id
	 * @return string
	 */
	public static function userUrl( $id )
	{
		return 'index.php?option=com_users&amp;task=user.edit&amp;id=' . $id;
	}

	/* get all parent groups */
	protected function parentGids()
	{
		if ( count( $this->gid ) ) {
			foreach ( $this->gid as $gid ) {
				if ( $gid >= 5000 ) {
					$gids = [];
					while ( $gid > 5000 ) {
						try {
							$gid = SPFactory::db()->select( 'pid', 'spdb_user_group', [ 'gid' => $gid, 'enabled' => 1 ] )->loadResult();
							$gids[] = $gid;
						} catch ( SPException $x ) {
							Sobi::Error( 'permissions', SPLang::e( 'Cannot load additional gids. %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
						}
					}
					$cgids = SAccess::parentGroups( $gid );
					$cgids[] = $gid;
					$gids = array_merge( $gids, $cgids );
				}
				else {
					$gids = SAccess::parentGroups( $gid );
					$gids[] = $gid;
				}
				if ( is_array( $gids ) && count( $gids ) ) {
					foreach ( $gids as $gid ) {
						$this->gid[] = $gid;
					}
				}
			}
		}
		// PHP 5.2.9 bug work-around
		if ( defined( 'PHP_VERSION_ID' ) && version_compare( PHP_VERSION, '5.2.9' ) == 0 ) {
			$this->gid = array_unique( $this->gid, SORT_STRING );
		}
		else {
			$this->gid = array_unique( $this->gid );
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
				$r = SPFactory::db()->select( [ 'title', 'id' ], '#__usergroups', [ 'id' => $gids ] )->loadAssocList( 'id' );
				if ( count( $r ) ) {
					foreach ( $r as $gid => $data ) {
						if ( isset( $groups[ $gid ] ) ) {
							$groups[ $gid ] = $data[ 'title' ];
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
		$r = SPFactory::db()->select( [ 'title', 'id' ], '#__usergroups' )->loadAssocList( 'id' );
		if ( count( $r ) ) {
			foreach ( $r as $gid => $data ) {
				$groups[ $gid ] = $data[ 'title' ];
			}
		}
		return $groups;
	}

	/**
	 * Checks if the current user is an admin
	 * @return bool
	 */
	public function isAdmin()
	{
		return defined( 'SOBIPRO_ADM' ) && JFactory::getUser()->get( 'isRoot' ) !== null ? JFactory::getUser()->get( 'isRoot' ) : JUser::authorise( 'core.admin' );
	}

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
		/* if we have the rules ids we need to get permission for this section and global permsion */
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
}

class SAccess extends JAccess
{
	public static function parentGroups( $gid )
	{
		return self::getGroupPath( $gid );
	}
}
