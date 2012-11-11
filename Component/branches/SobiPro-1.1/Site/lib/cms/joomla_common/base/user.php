<?php
/**
 * @version: $Id: user.php 2317 2012-03-27 10:19:39Z Radek Suski $
 * @package: SobiPro Bridge
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-03-27 12:19:39 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2317 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla_common/base/user.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:51:03 PM
 */
class SPJomlaUser extends JUser
{
	/**
	 * @var array
	 */
	protected $_permReq = array();
	/**
	 * @var array
	 */
	protected $_permissions = array();
	/**
	 * @var array
	 */
	protected $_availablePerm = array();
	/**
	 * @var array
	 */
	protected $_pmap = array();
	/**
	 * @var array
	 */
	protected $_prules = array();
	/**
	 * @var array
	 */
	protected $_prequest = array();

	protected $_special = array( 'txt.js', 'progress' );
	/**
	 */
	public function __destruct()
	{
//		foreach ( $this->_permReq as $p )
//			SPConfig::debOut( $p );
	}

	/* get all parent groups */
	protected function parentGids()
	{
		$hold = array();
		if( count( $this->gid ) ) {
			foreach ( $this->gid  as $gid ) {
				if( $gid >= 5000 ) {
					$gids = array();
					while ( $gid > 5000 ) {
						try {
							$gid = SPFactory::db()->select( 'pid', 'spdb_user_group', array( 'gid' => $gid, 'enabled' => 1 ) )->loadResult();
							$gids[] = $gid;
						}
						catch ( SPException $x ) {
							Sobi::Error( 'permissions', SPLang::e( 'Cannot load additional gids. %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
						}
					}
					$cgids = JFactory::getACL()->get_group_parents( $gid, 'ARO', 'RECURSE' );
					$gids = array_merge( $gids, $cgids );
				}
				else {
					$gids = JFactory::getACL()->get_group_parents( $gid, 'ARO', 'RECURSE' );
				}
				if( is_array( $gids ) && count( $gids ) ) {
					$this->gid  = array_merge( $gids, $this->gid );
				}
			}
		}
		// PHP 5.2.9 bug work-around
		if( defined( 'PHP_VERSION_ID' ) && version_compare( PHP_VERSION, '5.2.9' ) < 0 ) {
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
			$join = array(
				array( 'table' => 'spdb_user_group', 'as' => 'grp', 'key' => 'gid' ),
				array( 'table' => 'spdb_users_relation', 'as' => 'rel', 'key' => 'gid' )
			);
			$gids = $db->select( 'rel.gid', $db->join( $join ), array( '@VALID' => $valid, 'uid' => $this->id ) )->loadResultArray();
			if( count( $gids ) ) {
				$this->gid = array_merge( $gids, $this->gid );
			}
		}
		catch ( SPException $x ) {
			Sobi::Error( 'permissions', sprintf( 'Cannot load additional gids. %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
	}

	public static function groups( $gids )
	{
		$groups = array();
		if( $gids instanceof self ) {
			$gids = $gids->get( 'gid' );
		}
		if( count( $gids ) ) {
			$groups = array_flip( $gids );
			$r = SPFactory::db()->select( array( 'groupName', 'gid' ), 'spdb_user_group', array( 'gid' => $gids ) )->loadAssocList( 'gid' );
			if( count( $r ) ) {
				foreach ( $r as $gid => $data ) {
					if( isset( $groups[ $gid ] ) ) {
						$groups[ $gid ] = $data[ 'groupName' ];
					}
				}
			}
			if( count( $r ) < count( $groups ) ) {
				$r = SPFactory::db()->select( array( 'name', 'id' ), '#__core_acl_aro_groups', array( 'id' => $gids ) )->loadAssocList( 'id' );
				if( count( $r ) ) {
					foreach ( $r as $gid => $data ) {
						if( isset( $groups[ $gid ] ) ) {
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
		$groups = array( 0 => 'visitor' );
		$r = SPFactory::db()->select( array( 'groupName', 'gid' ), 'spdb_user_group' )->loadAssocList( 'gid' );
		if( count( $r ) ) {
			foreach ( $r as $gid => $data ) {
				$groups[ $gid ] = $data[ 'groupName' ];
			}
		}
		$r = SPFactory::db()->select( array( 'name', 'id' ), '#__core_acl_aro_groups' )->loadAssocList( 'id' );
		if( count( $r ) ) {
			foreach ( $r as $gid => $data ) {
				$groups[ $gid ] = $data[ 'name' ];
			}
		}
		return $groups;
	}

	/**
	 * check permission for an action
	 * @param string $action - e.g. edit
	 * @param string $ownership - e.g. own, all or global
	 * @return bool - true if authorized
	 */
	public function can( $subject, $action = 'access', $value = 'valid', $section = null  )
	{
		$can = false;
		if( strstr( $subject, '.' ) ) {
			$subject = explode( '.', $subject );
			$action = $subject[ 1 ];
			if( isset( $subject[ 2 ] ) ) {
				$value = $subject[ 2 ];
			}
			$subject = $subject[ 0 ];
		}
		if( !$section ) {
			$section = SPFactory::registry()->get( 'current_section', 0 );
		}
		$can = $this->authorise( $section, $subject, $action, $value );
		if( SPFactory::registry()->__isset( 'plugins' ) ) {
			Sobi::Trigger( 'Authorise', 'Permission', array( &$can, $section, $subject, $action, $value ) );
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
	public function authorise( $section, $subject, $action, $value )
	{
		if( $this->isAdmin() ) {
			return true;
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
		if( in_array( $subject, array( 'acl', 'config', 'extensions' ) ) ) {
			$action = 'manage';
			$section = 0;
		}
		if( !$section ) {
			$value = 'global';
			if( in_array( SPRequest::task(), $this->_special ) ) {
				return true;
			}
		}
		/* admin panel or site front */
		$site = SOBI_ACL;
		/* initialise */
		$auth = false;
		/* if not initialised */
		if( !count( $this->_permissions ) ) {
			$this->getPermissions();
		}

		/* if already requested, return the answer */
		$i = "[{$site}][{$section}][{$action}][{$subject}][{$value}]";
		if( isset( $this->_prequest[ $i ] ) ) {
			return $this->_prequest[ $i ];
		}
		if( isset( $this->_permissions[ $section ] ) ) {
			if( isset( $this->_permissions[ $section ][ $subject ] ) ) {
				if( isset( $this->_permissions[ $section ][ $subject ][ $action ] ) ) {
					if( isset( $this->_permissions[ $section ][ $subject ][ $action ][ $value ] ) ) {
						$auth = $this->_permissions[ $section ][ $subject ][ $action ][ $value ];
					}
					elseif( isset( $this->_permissions[ $section ][ $subject ][ $action ][ '*' ] ) ) {
						$auth = $this->_permissions[ $section ][ $subject ][ $action ][ '*' ];
					}
				}
				elseif( isset( $this->_permissions[ $section ][ $subject ][ '*' ] ) ) {
					$auth = $this->_permissions[ $section ][ $subject ][ '*' ];
				}
			}
			elseif( isset( $this->_permissions[ $section ][ '*' ] ) ) {
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
	public function isAdmin()
	{
		return ( ( $this->usertype == 'Super Administrator' || $this->usertype == 'Administrator' ) && $this->id );
	}

	/**
	 * Enter description here...
	 *
	 */
	protected function getPermissions( $sid = null )
	{
		$sid = $sid ? $sid : Sobi::Section();
		if( isset( $this->_permissions[ $sid ] ) ) {
			return true;
		}
		/* if it is for super admin - always true */
		if( $this->isAdmin() ) {
			return true;
		}
		/* @var SPdb $db */
		$db =& SPFactory::db();

		/* first thing we need is all rules id for the group where the user is assigned to */
		$join = array(
			array( 'table' => 'spdb_permissions_groups', 'as' => 'spgr', 'key' => 'rid' ),
			array( 'table' => 'spdb_permissions_rules', 'as' => 'sprl', 'key' => 'rid' )
		);
		$gids = implode( ', ', $this->gid );
		$valid = $db->valid( 'sprl.validUntil', 'sprl.validSince', 'state' );
		$valid .= "AND spgr.gid in( {$gids} ) ";
		$db->dselect( 'sprl.rid', $db->join( $join ), array( '@VALID' => $valid ) );
		try {
			$this->_prules = $db->loadResultArray();
		}
		catch ( SPException $x ) {
			Sobi::Error( 'permissions', SPLang::e( 'CANNOT_GET_PERMISSIONS', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
		}
		/* if we have the rules ids we need to get permission for this section and global permsion */
		if( count( $this->_prules ) ) {
			try {
				$db->select( 'pid', 'spdb_permissions_map', array( 'sid' => $sid, 'rid' => $this->_prules ) );
				$permissions = $db->loadResultArray();
			}
			catch ( SPException $x ) {
				Sobi::Error( 'permissions', SPLang::e( 'CANNOT_GET_USERS_DATA', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
			}
		}
		/* get all available permissions */
		try {
			$db->select( '*', 'spdb_permissions', array( 'site' => SOBI_ACL, 'published' => 1 ) );
			$this->_availablePerm = $db->loadAssocList( 'pid' );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'permissions', SPLang::e( 'CANNOT_GET_PERMISSIONS', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
		$this->_permissions[ $sid ] = array();
		/* create permissions array */
		if( count( $permissions ) ) {
			foreach ( $permissions as $perm ) {
				if( isset( $this->_availablePerm[ $perm ] ) ) {
					if( !( isset( $this->_permissions[ $sid ][ $this->_availablePerm[ $perm ][ 'subject' ] ] ) ) ) {
						$this->_permissions[ $sid ][ $this->_availablePerm[ $perm ][ 'subject' ] ] = array();
					}
					$this->_permissions[ $sid ][ $this->_availablePerm[ $perm ][ 'subject' ] ][ $this->_availablePerm[ $perm ][ 'action' ] ][ $this->_availablePerm[ $perm ][ 'value' ] ] = true;
				}
			}
		}
	}

	/**
	 * Getting base data from Joomla! users table.
	 * Offten there are only a few informations needed so it does not make
	 * sense to instance the big object just to get these data
	 * @param array $id
	 * @return array
	 */
	public static function getBaseData( $id )
	{
		if( is_int( $id ) ) {
			$ids = array( $id );
		}
		else {
			$ids = $id;
		}
		if( !count( $ids ) ) {
			return false;
		}
		try {
			$data = SPFactory::db()
					->select( '*', '#__users', array( 'id' => $ids ) )
					->loadObjectList( 'id' );
		}
		catch ( SPException $x ) {
			Sobi::Error( 'user', SPLang::e( 'CANNOT_GET_USERS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
		if( is_int( $id ) ) {
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
	 * @param	string	$key 	- The path of the state.
	 * @param	string	$value 	- The value of the variable.
	 * @return	mixed	The previous state, if one existed.
	 */
	public function setUserState(  $key, $value )
	{
		return JFactory::getApplication()->setUserState( "com_sobipro.{$key}", $value );
	}

	/**
	 * Gets the value of a user state variable.
	 * @param	string $key 	- The key of the user state variable.
	 * @param	string $request - The name of the variable passed in a request.
	 * @param	string $default - The default value for the variable if not found. Optional.
	 * @param	string $type	- Filter for the variable.
	 * @return	mixed
	 */
	public function getUserState( $key, $request, $default = null, $type = 'none' )
	{
        $r = JFactory::getApplication()->getUserStateFromRequest( "com_sobipro.{$key}", $request, $default, $type );
        SPRequest::set( $request, $r );
		return $r;
	}

	/**
	 * @param int $id
	 * @return string
	 */
	public static function userUrl( $id )
	{
		return 'index.php?option=com_users&amp;task=edit&amp;cid[]='.$id;
	}
}
