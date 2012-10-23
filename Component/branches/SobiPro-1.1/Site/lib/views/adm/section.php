<?php
/**
 * @version: $Id: section.php 2317 2012-03-27 10:19:39Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/section.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 16:42:13
 */

class SPSectionAdmView extends SPAdmView
{

	/**
	 * @param string $title
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'section.name' );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		$title = Sobi::Txt( $title, array( 'name' => $name ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title' );
		$this->set( $name, 'section_name' );
		$this->set( $name, 'category_name' );
	}

	/**
	 *
	 */
	public function display()
	{
		SPLoader::loadClass( 'html.tooltip' );
		switch ( $this->get( 'task' ) ) {
			case 'view':
				$this->listSection();
				$this->determineTemplate( 'section', 'category' );
				break;
			case 'entries':
				$this->listSection();
				$this->determineTemplate( 'section', 'entries' );
				break;
		}
		parent::display();
	}

	/**
	 */
	protected function listSection()
	{
		$this->assign( $this->parentPath( SPRequest::sid() ), 'current_path' );
		$this->_plgSect = '_SectionListTemplate';
		$c = $this->get( 'categories' );
		$categories = array();
		$entries = array();

		/* get users/authors data first */
		$usersData = array();
		if ( count( $c ) ) {
			foreach ( $c as $cat ) {
				$usersData[ ] = $cat->get( 'owner' );
			}
			reset( $c );
		}
		$usersData = $this->userData( $usersData );

		/* handle the categories */
		if ( count( $c ) ) {
			$catPageNav = SPLoader::loadClass( 'helpers.adm.pagenav' );
			$catPageNav = $catPageNav ? new $catPageNav( $this->get( '$cLimit' ), $this->get( '$cCount' ), $this->get( '$cLimStart' ), 'SPCatPageNav', 'climit', 'SPCatPageLimit' ) : null;
			$this->assign( $catPageNav->display( true ), 'cat_page_nav' );
			foreach ( $c as $cat ) {
				$category = array();
				/* data needed to display in the list */
				$category[ 'name' ] = $cat->get( 'name' );
				$category[ 'state' ] = $cat->get( 'state' );
				$category[ 'approved' ] = $cat->get( 'approved' );

				if ( isset( $usersData[ $cat->get( 'owner' ) ] ) ) {
					$uName = $usersData[ $cat->get( 'owner' ) ]->name;
					$uUrl = SPUser::userUrl( $usersData[ $cat->get( 'owner' ) ]->id );
					$category[ 'owner' ] = "<a href=\"{$uUrl}\">{$uName}</a>";
				}
				else {
					$category[ 'owner' ] = Sobi::Txt( 'GUEST' );
				}
				$category[ 'checkbox' ] = SPLists::checkedOut( $cat, 'c_sid' );
				$category[ 'goin_link' ] = $this->actionIcon( 'goin', Sobi::Url( array( 'sid' => $cat->get( 'id' ) ) ), $cat );
				$category[ 'edit_link' ] = $this->actionIcon( 'edit', Sobi::Url( array( 'task' => 'category.edit', 'sid' => $cat->get( 'id' ) ) ), $cat );
				$category[ 'order' ] = SPLists::position( $cat, $this->get( '$cCount' ), 'cp_sid' );

				/* the rest - case someone need */
				$category[ 'position' ] = $cat->get( 'position' );
				$category[ 'createdTime' ] = $cat->get( 'createdTime' );
				$category[ 'cout' ] = $cat->get( 'cout' );
				$category[ 'coutTime' ] = $cat->get( 'coutTime' );
				$category[ 'id' ] = $cat->get( 'id' );
				$category[ 'validSince' ] = $cat->get( 'validSince' );
				$category[ 'validUntil' ] = $cat->get( 'validUntil' );
				$category[ 'description' ] = $cat->get( 'description' );
				$category[ 'icon' ] = $cat->get( 'icon' );
				$category[ 'introtext' ] = $cat->get( 'introtext' );
				$category[ 'parent' ] = $cat->get( 'parent' );
				$category[ 'confirmed' ] = $cat->get( 'confirmed' );
				$category[ 'counter' ] = $cat->get( 'counter' );
				$category[ 'nid' ] = $cat->get( 'nid' );
				$category[ 'metaDesc' ] = $cat->get( 'metaDesc' );
				$category[ 'metaKeys' ] = $cat->get( 'metaKeys' );
				$category[ 'metaAuthor' ] = $cat->get( 'metaAuthor' );
				$category[ 'metaRobots' ] = $cat->get( 'metaRobots' );
				$category[ 'ownerIP' ] = $cat->get( 'ownerIP' );
				$category[ 'updatedTime' ] = $cat->get( 'updatedTime' );
				$category[ 'updater' ] = $cat->get( 'updater' );
				$category[ 'updaterIP' ] = $cat->get( 'updaterIP' );
				$category[ 'version' ] = $cat->get( 'version' );
				$category[ 'object' ] =& $cat;
				$categories[ ] = $category;
			}
		}
		/* re-assign the categories */
		$this->assign( $categories, 'categories' );

		/* handle the fields in this section for header */
		$f = $this->get( 'fields' );
		$entriesOrdering = array(
			Sobi::Txt( 'EMN_ORDER_BY' ) => array(),
			'e_sid.asc' => Sobi::Txt( 'EMN.ORDER_BY_ID_ASC' ),
			'e_sid.desc' => Sobi::Txt( 'EMN.ORDER_BY_ID_DESC' ),
			'name.asc' => Sobi::Txt( 'EMN.ORDER_BY_NAME_ASC' ),
			'name.desc' => Sobi::Txt( 'EMN.ORDER_BY_NAME_DESC' ),
			'state.asc' => Sobi::Txt( 'EMN.ORDER_BY_STATE_ASC' ),
			'state.desc' => Sobi::Txt( 'EMN.ORDER_BY_STATE_DESC' ),
			'approved.asc' => Sobi::Txt( 'EMN.ORDER_BY_APPROVAL_ASC' ),
			'approved.desc' => Sobi::Txt( 'EMN.ORDER_BY_APPROVAL_DESC' ),
		);
		if ( $this->get( 'task' ) == 'view' ) {
			$entriesOrdering[ 'order.asc' ] = Sobi::Txt( 'EMN.ORDER_BY_ORDER_ASC' );
			$entriesOrdering[ 'order.desc' ] = Sobi::Txt( 'EMN.ORDER_BY_ORDER_DESC' );
		}
		$customFields = array();
		$customHeader = array();
		if ( count( $f ) ) {
			/* @var SPField $fit */
			foreach ( $f as $field ) {
				$entriesOrdering[ Sobi::Txt( 'EMN.ORDER_BY_FIELD' ) ][ $field->get( 'nid' ) ] = $field->get( 'name' );
				$customFields[ ] = $field->get( 'nid' );
				$customHeader[ ] = array(
					'content' => $field->get( 'name' ),
					'attributes' => array( 'type' => 'text' ),
				);
			}
		}
		$entriesOrdering[ 'owner.desc' ] = Sobi::Txt( 'EMN.ORDER_BY_OWNER' );
		$this->assign( $customHeader, 'customHeader' );
		$this->assign( $customFields, 'custom_fields' );
		$this->assign( $entriesOrdering, 'entriesOrdering' );

		/* handle the entries */
		$e = $this->get( 'entries' );
		if ( count( $e ) ) {
			/* get users/authors data first */
			$usersData = array();
			foreach ( $e as $i => $sid ) {
				$e[ $i ] = SPFactory::EntryRow( $sid );
				$usersData[ ] = $e[ $i ]->get( 'owner' );
			}
			reset( $e );
			$usersData = $this->userData( $usersData );

			$entriesPageNav = SPLoader::loadClass( 'helpers.adm.pagenav' );
			$entriesPageNav = $entriesPageNav ? new $entriesPageNav( $this->get( '$eLimit' ), $this->get( '$eCount' ), $this->get( '$eLimStart' ), 'SPEntriesPageNav', 'elimit', 'SPEntriesPageLimit' ) : null;
			$this->assign( $entriesPageNav->display( true ), 'entries_page_nav' );
			foreach ( $e as $sentry ) {
				/* @var SPEntryAdm $sentry */
				$entry = array();
				$entry[ 'state' ] = $sentry->get( 'state' );
				$entry[ 'approved' ] = $sentry->get( 'approved' );

				if ( isset( $usersData[ $sentry->get( 'owner' ) ] ) ) {
					$uName = $usersData[ $sentry->get( 'owner' ) ]->name;
					$uUrl = SPUser::userUrl( $usersData[ $sentry->get( 'owner' ) ]->id );
					$entry[ 'owner' ] = "<a href=\"{$uUrl}\">{$uName}</a>";
				}
				else {
					$entry[ 'owner' ] = Sobi::Txt( 'GUEST' );
				}
				$entry[ 'checkbox' ] = SPLists::checkedOut( $sentry, 'e_sid' );
				$catpos = $sentry->getCategories();
				if ( SPRequest::sid() && isset( $catpos[ SPRequest::sid() ] ) ) {
					$sentry->position = $catpos[ SPRequest::sid() ][ 'position' ];
				}
				$entry[ 'order' ] = SPLists::position( $sentry, $this->get( '$cCount' ), 'ep_sid' );

				/* the rest - case someone need */
				$entry[ 'position' ] = $sentry->get( 'position' );
				$entry[ 'createdTime' ] = $sentry->get( 'createdTime' );
				$entry[ 'cout' ] = $sentry->get( 'cout' );
				$entry[ 'coutTime' ] = $sentry->get( 'coutTime' );
				$entry[ 'id' ] = $sentry->get( 'id' );
				$entry[ 'validSince' ] = $sentry->get( 'validSince' );
				$entry[ 'validUntil' ] = $sentry->get( 'validUntil' );
				$entry[ 'description' ] = $sentry->get( 'description' );
				$entry[ 'icon' ] = $sentry->get( 'icon' );
				$entry[ 'introtext' ] = $sentry->get( 'introtext' );
				$entry[ 'parent' ] = $sentry->get( 'parent' );
				$entry[ 'confirmed' ] = $sentry->get( 'confirmed' );
				$entry[ 'counter' ] = $sentry->get( 'counter' );
				$entry[ 'nid' ] = $sentry->get( 'nid' );
				$entry[ 'metaDesc' ] = $sentry->get( 'metaDesc' );
				$entry[ 'metaKeys' ] = $sentry->get( 'metaKeys' );
				$entry[ 'metaAuthor' ] = $sentry->get( 'metaAuthor' );
				$entry[ 'metaRobots' ] = $sentry->get( 'metaRobots' );
				$entry[ 'ownerIP' ] = $sentry->get( 'ownerIP' );
				$entry[ 'updatedTime' ] = $sentry->get( 'updatedTime' );
				$entry[ 'updater' ] = $sentry->get( 'updater' );
				$entry[ 'updaterIP' ] = $sentry->get( 'updaterIP' );
				$entry[ 'version' ] = $sentry->get( 'version' );
				$fields = $sentry->getFields();
				$entry[ 'fields' ] = $fields;
				$entry[ 'object' ] =& $sentry;
				/* fields data init */
				if ( count( $f ) ) {
					foreach ( $f as $field ) {
						$entry[ $field->get( 'nid' ) ] = null;
					}
				}
				/* now fill with the real data if any */
				if ( count( $fields ) ) {
					foreach ( $fields as $field ) {
						$entry[ $field->get( 'nid' ) ] = $field->data();
					}
				}
				if ( count( ( $customFields ) ) ) {
					foreach ( $customFields as $customField ) {
						$entry[ 'customFields' ][ $customField ] = $entry[ $customField ];
					}
				}
				/* in case we are showing all entries in a section */
				if ( $this->get( 'task' ) == 'entries' ) {
					$ehref = Sobi::Url( array( 'task' => 'entry.edit', 'sid' => $sentry->get( 'id' ), 'pid' => Sobi::Section() ) );
				}
				else {
					$ehref = Sobi::Url( array( 'task' => 'entry.edit', 'sid' => $sentry->get( 'id' ) ) );
				}
				if ( $sentry->get( 'valid' ) ) {
					$entry[ 'name' ] = '<a href="' . $ehref . '" title="' . Sobi::Txt( 'EN.EDIT_ENTRY_NAME', array( 'name' => $sentry->get( 'name' ) ) ) . '">' . ( strlen( $sentry->get( 'name' ) ) ? $sentry->get( 'name' ) : Sobi::Txt( 'No Name' ) ) . '</a>';
				}
				else {
					$entry[ 'name' ] = '<del><a href="' . $ehref . '" title="' . Sobi::Txt( 'EN.EDIT_ENTRY_NAME', array( 'name' => $sentry->get( 'name' ) ) ) . '">' . ( strlen( $sentry->get( 'name' ) ) ? $sentry->get( 'name' ) : Sobi::Txt( 'No Name' ) ) . '</a></del>';
				}
				$entries[ ] = $entry;
			}
		}
		$this->assign( $entries, 'entries' );
	}

	private function actionIcon( $action, $url, $obj )
	{
		if (
			/* if the caction is 'edit' ... */
			$action == 'edit' &&
			/* ... but object is checked out ... */
			$obj->get( 'cout' )
			/* ... by an other user ... */
			&& $obj->get( 'cout' ) != Sobi::My( 'id' )
			/* ... and the time isn't expired */
			&& strtotime( $obj->get( 'coutTime' ) ) > time()
		) {
			$user =& SPUser::getInstance( $obj->get( 'cout' ) );
			$uname = $user->get( 'name' );
			$img = Sobi::Cfg( 'list_icons.checked_out' );
			$s = Sobi::Txt( $obj->get( 'oType' ) . '_checked_out' );
			$a = Sobi::Txt( $obj->get( 'oType' ) . '_checked_out_by', array( 'user' => $uname, 'time' => $obj->get( 'coutTime' ) ) );
			return SPTooltip::toolTip( $a, $s, $img );
		}
		$type = $obj->type();
		$icon = Sobi::Cfg( "list_icons.{$type}_{$action}" );
		$s = Sobi::Txt( "{$type}_{$action}", $obj->get( 'name' ) );
		$a = Sobi::Txt( "{$type}_{$action}_expl", $obj->get( 'name' ) );
		$icon = SPTooltip::toolTip( $a, $s, $icon );
		return "<span class=\"SectionListIcons\"><a href=\"{$url}\">{$icon}</a></span>";
	}
}
