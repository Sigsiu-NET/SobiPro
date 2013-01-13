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
	 * @return string
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'section.name' );
		if ( $name ) {
			Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
			$title = Sobi::Txt( $title, array( 'category_name' => $name ) );
			$this->set( $name, 'category_name' );
			$this->set( $name, 'section_name' );
			$this->set( $title, 'site_title' );
		}
		$title = parent::setTitle( $title );
		return $title;
	}

	/**
	 *
	 */
	public function display()
	{
		switch ( $this->get( 'task' ) ) {
			case 'view':
				$this->listSection();
				$this->determineTemplate( 'section', 'list' );
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
		$parentPath = $this->parentPath( SPRequest::sid() );
		$this->assign( $parentPath, 'current_path' );
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
			$this->get( 'entries_field' ) . '.asc' => Sobi::Txt( 'EMN.ORDER_BY_NAME_ASC' ),
			$this->get( 'entries_field' ) . '.desc' => Sobi::Txt( 'EMN.ORDER_BY_NAME_DESC' ),
			'state.asc' => Sobi::Txt( 'EMN.ORDER_BY_STATE_ASC' ),
			'state.desc' => Sobi::Txt( 'EMN.ORDER_BY_STATE_DESC' ),
			'approved.asc' => Sobi::Txt( 'EMN.ORDER_BY_APPROVAL_ASC' ),
			'approved.desc' => Sobi::Txt( 'EMN.ORDER_BY_APPROVAL_DESC' ),
		);
		if ( $this->get( 'task' ) == 'view' ) {
			$entriesOrdering[ 'position.asc' ] = Sobi::Txt( 'EMN.ORDER_BY_ORDER_ASC' );
			$entriesOrdering[ 'position.desc' ] = Sobi::Txt( 'EMN.ORDER_BY_ORDER_DESC' );
		}
		$customFields = array();
		$customHeader = array();
		if ( count( $f ) ) {
			/* @var SPField $fit */
			foreach ( $f as $field ) {
				$entriesOrdering[ Sobi::Txt( 'EMN.ORDER_BY_FIELD' ) ][ $field->get( 'nid' ) . '.asc' ] = '\'' . $field->get( 'name' ) . '\' ' . Sobi::Txt( 'EMN.ORDER_BY_FIELD_ASC' );
				$entriesOrdering[ Sobi::Txt( 'EMN.ORDER_BY_FIELD' ) ][ $field->get( 'nid' ) . '.desc' ] = '\'' . $field->get( 'name' ) . '\' ' . Sobi::Txt( 'EMN.ORDER_BY_FIELD_DESC' );
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
				$catPosition = $sentry->getCategories();
				if ( SPRequest::sid() && isset( $catPosition[ SPRequest::sid() ] ) ) {
					$sentry->position = $catPosition[ SPRequest::sid() ][ 'position' ];
				}
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
				$entry[ 'valid' ] = $sentry->get( 'valid' ) ? 'valid' : 'invalid';
				$entry[ 'object' ] =& $sentry;
				$entry[ 'name' ] = $sentry->get( 'name' );
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
				$entries[ ] = $entry;
			}
		}
		$this->assign( $entries, 'entries' );
	}
}
