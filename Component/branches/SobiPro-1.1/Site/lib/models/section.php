<?php
/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadModel( 'datamodel' );
SPLoader::loadModel( 'dbobject' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:11:46 PM
 */
final class SPSection extends SPDBObject implements SPDataModel
{
	/**
	 * @var bool
	 */
	protected $approved = true;
	/**
	 * @var bool
	 */
	protected $confirmed = true;
	/**
	 * @var int
	 */
	protected $state = 1;
	/**
	 * @var string
	 */
	protected $oType = 'section';
	/**
	 * @var string
	 */
	protected $description = null;
	/**
	 */
//	protected $_dbTable = 'spdb_section';
	/**
	 * @var array
	 */
	private static $types = array( 'description' => 'html' );
	/**
	 * @var array
	 */
	private static $translatable = array( 'description', 'name', 'metaKeys', 'metaDesc' );

	/**
	 */
	public function delete()
	{
		$childs = $this->getChilds( 'all', true );
		Sobi::Trigger( 'Section', ucfirst( __FUNCTION__ ), array( &$this->id ) );
		if ( count( $childs ) ) {
			Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'SEC.DEL_WARN' ), SPC::ERROR_MSG, true );
		}
		else {
			Sobi::Trigger( 'delete', $this->name(), array( &$this ) );
			/* @var SPdb $db */
			$db =& SPFactory::db();
			try {
				$db->delete( 'spdb_relations', "id = {$this->id} OR pid = {$this->id}" );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			try {
				$db->delete( 'spdb_config', array( 'section' => $this->id ) );
				$db->delete( 'spdb_plugin_section', array( 'section' => $this->id ) );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			try {
				$fids = $db->select( 'fid', 'spdb_field', array( 'section' => $this->id ) )->loadResultArray();
				if ( count( $fids ) ) {
					foreach ( $fids as $fid ) {
						try {
							$db->select( '*', $db->join( array( array( 'table' => 'spdb_field', 'as' => 'sField', 'key' => 'fieldType' ), array( 'table' => 'spdb_field_types', 'as' => 'sType', 'key' => 'tid' ) ) ), array( 'fid' => $fid ) );
							$f = $db->loadObject();
						} catch ( SPException $x ) {
							Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
						}
						$field =& SPFactory::Model( 'field', true );
						$field->extend( $f );
						$field->delete();
					}
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			parent::delete();
			Sobi::Trigger( 'afterDelete', $this->name(), array( &$this ) );
		}
	}

	/**
	 */
	public function save( $update = false, $init = true )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		/* check nid */
		if ( !$update ) {
			$c = 1;
			while ( $c ) {
				/* section name id has to be unique */
				try {
					$db->select( 'COUNT(nid)', 'spdb_object', array( 'oType' => 'section', 'nid' => $this->nid ) );
					$c = $db->loadResult();
					if ( $c > 0 ) {
						$this->nid = $this->nid . '_' . rand( 0, 1000 );
					}
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
				}
			}
		}
		SPFactory::registry()->set( 'current_section', $this->id );
		$db->transaction();
		parent::save();
		/* case adding new section, define the default title field */
		if ( !$update && $init ) {
			$field = SPFactory::Model( 'field', true );
			$fid = $field->saveNew(
				array(
					'name' => 'Name',
					'nid' => 'field_name',
					'showIn' => 'both',
					'fieldType' => 'inbox',
					'enabled' => 1,
					'required' => 1,
					'editable' => 1,
					'section' => $this->id,
					'inSearch' => 1,
					'searchMethod' => 'general',
					'isFree' => 1,
					'editLimit' => -1,
					'withLabel' => 1
				)
			);
			$field = SPFactory::Model( 'field', true );
			$field->saveNew(
				array(
					'name' => 'Category',
					'nid' => 'field_category',
					'showIn' => 'hidden',
					'fieldType' => 'category',
					'enabled' => 1,
					'required' => 1,
					'editable' => 1,
					'section' => $this->id,
					'inSearch' => 1,
					'searchMethod' => 'select',
					'isFree' => 1,
					'editLimit' => -1,
					'withLabel' => 1,
					'method' => 'select',
					'isPrimary' => true
				)
			);
			SPFactory::config()
					->saveCfg( 'entry.name_field', $fid )
					->saveCfg( 'list.entries_ordering', 'field_name' );

			SPFactory::Controller( 'acl', true )->addNewRule( $this->get( 'name' ), array( $this->id ), array( 'section.access.valid', 'category.access.valid', 'entry.access.valid', 'entry.add.own' ), array( 'visitor', 'registered' ), 'Default permissions for the section "' . $this->get( 'name' ) . '"' );
		}
		/* insert relation */
		try {
			$db->insertUpdate( 'spdb_relations', array( 'id' => $this->id, 'pid' => 0, 'oType' => 'section', 'position' => 1, 'validSince' => $this->validSince, 'validUntil' => $this->validUntil ) );
		} catch ( SPException $x ) {
			$db->rollback();
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		/* if there was no errors, commit the database changes */
		$db->commit();
//		if( !$update ) {
//			SPFactory::mainframe()->msg( Sobi::Txt( 'SEC.CREATED' ) );
//		}
		SPFactory::cache()->cleanSection();
		/* trigger plugins */
		Sobi::Trigger( 'afterSave', $this->name(), array( &$this ) );
	}

	/**
	 * @return array
	 */
	protected function types()
	{
		return self::$types;
	}

	public function & getInstance( $id = 0 )
	{
		static $instances = array();
		$id = $id ? $id : Sobi::Reg( 'current_section' );
		if ( !isset( $instances[ $id ] ) || !( $instances[ $id ] instanceof self ) ) {
			$instances[ $id ] = new self();
			$instances[ $id ]->extend( SPFactory::object( $id ) );
		}
		return $instances[ $id ];
	}

	/**
	 * @return array
	 */
	protected function translatable()
	{
		return self::$translatable;
	}
}
