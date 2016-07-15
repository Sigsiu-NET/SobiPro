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
SPLoader::loadModel( 'datamodel' );
SPLoader::loadModel( 'dbobject' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:10:32 PM
 */
class SPCategory extends SPDBObject implements SPDataModel
{
	/*** @var string */
	protected $description = null;
	/*** @var string */
	protected $icon = null;
	/*** @var int */
	protected $showIcon = 2;
	/*** @var string */
	protected $introtext = null;
	/*** @var int */
	protected $showIntrotext = 2;
	/*** @var int */
	protected $parseDesc = 2;
	/*** @var int */
	protected $position = 0;
	/*** @var int */
	protected $section = 0;
	/*** @var string */
	protected $oType = 'category';
	/*** @var int */
	protected $parent = 0;
	/*** @var array */
	private static $types = array(
			'description' => 'html',
			'icon' => 'string',
			'showIcon' => 'string',
			'introtext' => 'string',
			'showIntrotext' => 'int',
			'parseDesc' => 'int',
			'position' => 'int'
	);
	/*** @var array */
	private static $translatable = array( 'description', 'introtext', 'name', 'metaKeys', 'metaDesc' );
	/** @var string */
	protected $_dbTable = 'spdb_category';
	/** @var array */
	protected $fields = array();

	/**
	 */
	public function save( $request = 'post' )
	{
		/* initial org settings */
		/* @var SPdb $db */
		$db = SPFactory::db();
		$this->nid = $this->createAlias();

		$this->approved = Sobi::Can( $this->type(), 'publish', 'own' );

		$db->transaction();
		parent::save();
		$properties = get_class_vars( __CLASS__ );

		/* get database columns and their ordering */
		$cols = $db->getColumns( $this->_dbTable );
		$values = array();

		/* and sort the properties in the same order */
		foreach ( $cols as $col ) {
			$values[ $col ] = array_key_exists( $col, $properties ) ? $this->$col : '';
		}
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$values ) );
		/* try to save */
		try {
			$db->insertUpdate( $this->_dbTable, $values );
		} catch ( SPException $x ) {
			$db->rollback();
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_CATEGORY_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}

		/* insert relation */
		try {
			$db->delete( 'spdb_relations', array( 'id' => $this->id, 'oType' => 'category' ) );
			if ( !$this->position ) {
				$db->select( 'MAX( position ) + 1', 'spdb_relations', array( 'pid' => $this->parent, 'oType' => 'category' ) );
				$this->position = ( int )$db->loadResult();
				if ( !$this->position ) {
					$this->position = 1;
				}
			}
			$db->insertUpdate( 'spdb_relations', array( 'id' => $this->id, 'pid' => $this->parent, 'oType' => 'category', 'position' => $this->position, 'validSince' => $this->validSince, 'validUntil' => $this->validUntil ) );
		} catch ( SPException $x ) {
			$db->rollback();
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_CATEGORY_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		$this->loadFields( Sobi::Section(), true );
		foreach ( $this->fields as $field ) {
			/* @var $field SPField */
			try {
				if ( $field->enabled( 'form' ) ) {
					$field->saveData( $this, $request );
				}
				else {
					$field->finaliseSave( $this, $request );
				}
			} catch ( SPException $x ) {
				$db->rollback();
			}
		}


		/* if there was no errors, commit the database changes */
		$db->commit();

		SPFactory::cache()
				->purgeSectionVars()
				->cleanCategories()
				->deleteObj( 'category', $this->id )
				->deleteObj( 'category', $this->parent );
		/* trigger plugins */
		Sobi::Trigger( 'afterSave', $this->name(), array( &$this ) );
	}

	/**
	 */
	public function loadTable()
	{
		parent::loadTable();
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$this->icon = SPLang::clean( $this->icon );
		try {
			$db->select( array( 'position', 'pid' ), 'spdb_relations', array( 'id' => $this->id ) );
			$r = $db->loadObject();
			Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$r ) );
			$this->position = $r->position;
			$this->parent = $r->pid;
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if ( SPRequest::task() != 'category.edit' ) {
			if ( $this->parseDesc == SPC::GLOBAL_SETTING ) {
				$this->parseDesc = Sobi::Cfg( 'category.parse_desc', true );
			}
			if ( $this->parseDesc ) {
				Sobi::Trigger( 'Parse', 'Content', array( &$this->description ) );
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Site/lib/models/SPDBObject#delete()
	 * @param bool $childs - update child entries parent
	 */
	public function delete( $childs = true )
	{
		parent::delete();
		SPFactory::cache()
				->cleanSection()
				->deleteObj( 'category', $this->id )
				->cleanCategories();
		try {
			/* get all child cats and delete these too */
			$childs = $this->getChilds( 'category', true );
			if ( count( $childs ) ) {
				foreach ( $childs as $child ) {
					$cat = new self();
					$cat->init( $child );
					$cat->delete( false );
				}
			}
			$childs[ $this->id ] = $this->id;
			SPFactory::db()->delete( 'spdb_category', array( 'id' => $this->id ) );
			if ( $childs ) {
				SPFactory::db()->update( 'spdb_object', array( 'parent' => Sobi::Section() ), array( 'parent' => $childs ) );
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DELETE_CATEGORY_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * @return array
	 */
	protected function types()
	{
		return self::$types;
	}

	/**
	 * @return array
	 */
	protected function translatable()
	{
		return self::$translatable;
	}


	public function loadFields( $sid = 0, $enabled = false )
	{
		$sid = $sid ? $sid : $this->section;
		/* @var SPdb $db */
		$db = SPFactory::db();

		static $fields = array();
		if ( !isset( $fields[ $sid ] ) ) {
			/* get fields */
			try {
				if ( $enabled ) {
					$db->select( '*', 'spdb_field', array( 'section' => $sid, 'enabled' => 1, 'adminField' => -1 ), 'position' );
				}
				else {
					$db->select( '*', 'spdb_field', array( 'section' => $sid, 'adminField' => -1 ), 'position' );
				}
				$fields[ $sid ] = $db->loadObjectList();
				Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$fields ) );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		if ( !( count( $this->fields ) ) ) {
			foreach ( $fields[ $sid ] as $f ) {
				$field = SPFactory::Model( 'field', defined( 'SOBIPRO_ADM' ) );
				$field->extend( $f );
				$field->loadData( $this->id );
				$this->fields[ $f->fid ] = $field;
			}
		}
	}
}
