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

use Sobi\Input\Input;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadModel( 'datamodel' );
SPLoader::loadModel( 'dbobject' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:14:27 PM
 */
class SPEntry extends SPDBObject implements SPDataModel
{
	/**
	 * @var array
	 */
	private static $types = [
			'description' => 'html',
			'icon' => 'string',
			'showIcon' => 'int',
			'introtext' => 'string',
			'showIntrotext' => 'int',
			'parseDesc' => 'int',
			'position' => 'int',
	];
	/**
	 * @var
	 */
	protected $oType = 'entry';
	/**
	 * @var array categories where the entry belongs to
	 */
	protected $categories = [];
	/**
	 * @var array
	 */
	protected $fields = [];
	/**
	 * @var array
	 */
	protected $fieldsNids = [];
	/**
	 * @var array
	 */
	protected $fieldsIds = [];
	/**
	 * @var string
	 */
	protected $nameField = null;
	/**
	 * @var array
	 */
	private $data = [];
	/**
	 * @var bool
	 */
	private $_loaded = false;
	/**
	 * @var int
	 */
	public $position = 0;
	/**
	 * @var bool
	 */
	protected $valid = true;
	/**
	 * @var int
	 */
	public $primary = 0;
	/**
	 * @var string
	 */
	public $url = '';

	public function __construct()
	{
		parent::__construct();
		if ( Sobi::Cfg( 'entry.publish_limit', 0 ) ) {
			$this->validUntil = date( 'Y-m-d H:i:s', time() + ( Sobi::Cfg( 'entry.publish_limit', 0 ) * 24 * 3600 ) );
		}
	}

	/**
	 * Full init
	 * @param bool $cache
	 */
	public function loadTable( $cache = false )
	{
		if ( $this->id && !( $cache ) ) {
			$cats = $this->getCategories( true );
			$this->section = Sobi::Section();
			if ( isset( $cats[ 0 ] ) ) {
				$sid = SPFactory::config()->getParentPath( $cats[ 0 ], false );
				$this->section = isset( $sid[ 0 ] ) && $sid[ 0 ] ? $sid[ 0 ] : Sobi::Section();
			}
			// we need to get some information from the object table
			$this->valid = $this->valid && count( $this->categories ) > 0;
			$this->loadFields( Sobi::Section(), true );
			Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$this->fields ] );
			if ( count( $this->fields ) ) {
				foreach ( $this->fields as $field ) {
					/* create field aliases */
					$this->fieldsIds[ $field->get( 'id' ) ] = $field;
					$this->fieldsNids[ $field->get( 'nid' ) ] = $field;
				}
			}
			$this->primary =& $this->parent;
			$this->url = Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $this->get( 'nid' ) : $this->get( 'name' ), 'pid' => $this->get( 'primary' ), 'sid' => $this->id ], false, true, true, true );
			Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$this->fieldsIds, &$this->fieldsNids ] );
		}
		if ( $this->id ) {
			$counter = SPFactory::db()
					->select( 'counter', 'spdb_counter', [ 'sid' => $this->id ] )
					->loadResult();
			if ( $counter !== null ) {
				$this->counter = $counter;
			}
		}
		if ( !( strlen( $this->name ) ) ) {
			$this->name = Sobi::Txt( 'ENTRY_NO_NAME' );
		}
		if ( $this->owner && $this->owner == Sobi::My( 'id' ) ) {
			$stop = true;
			SPFactory::registry()->set( 'break_cache_view', $stop );
		}
		$this->translate();
		// if the visitor can't see unapproved entries we are showing the approved version anyway
		if ( !( Sobi::Can( 'entry.access.unapproved_any' ) ) && ( SPRequest::task() != 'entry.edit' && SPRequest::task() != 'entry.submit' && SPRequest::task() != 'entry.save' ) && !( $this->approved ) && !( Sobi::Can( 'entry', 'edit', '*', Sobi::Section() ) ) ) {
			$this->approved = 1;
		}
	}

	/**
	 * Std. getter. Returns a property of the object or the default value if the property is not set.
	 * @param string $attr
	 * @param mixed $default
	 * @param boolean $object - return object instead of data
	 * @return mixed
	 */
	public function get( $attr, $default = null, $object = false )
	{
		if ( strstr( $attr, 'field_' ) ) {
			if ( isset( $this->fieldsNids[ trim( $attr ) ] ) ) {
				return $object ? $this->fieldsNids[ trim( $attr ) ] : $this->fieldsNids[ trim( $attr ) ]->data();
			}
		}
		else {
			return parent::get( $attr, $default );
		}
	}


	/**
	 * External method to publish and approve an entry
	 */
	public function publish()
	{
		SPFactory::db()
				->update( 'spdb_object', [ 'approved' => 1 ], [ 'id' => $this->id, 'oType' => 'entry' ] );
		$this->changeState( true );
		$this->approveFields( true );
	}

	/**
	 * External method to unpublish and revoke approval of an entry
	 */
	public function unpublish()
	{
		SPFactory::db()
				->update( 'spdb_object', [ 'approved' => 0 ], [ 'id' => $this->id, 'oType' => 'entry' ] );
		$this->changeState( false );
	}

	/**
	 * After an entry has been approved, all fields cp
	 * @param $approve
	 * @return void
	 */
	public function approveFields( $approve )
	{
		Sobi::Trigger( $this->name(), 'Approve', [ $this->id, $approve, &$this->fields ] );
		SPFactory::cache()->purgeSectionVars();
		SPFactory::cache()->deleteObj( 'entry', $this->id );
		foreach ( $this->fields as $field ) {
			//$field->enabled( 'form' );
			$field->approve( $this->id );
		}
		if ( $approve ) {
			$db = SPFactory::db();
			try {
				$count = $db
						->select( 'COUNT(id)', 'spdb_relations', [ 'id' => $this->id, 'copy' => '1', 'oType' => 'entry' ] )
						->loadResult();
				if ( $count ) {
					/** Thu, Jun 19, 2014 11:24:05: here is the question: why are we deleting the 1 status when the list of categories is re-generating each time anyway
					 *   So basically there should not be a situation that there is any relation which should be removed while approving an entry */
					// $db->delete( 'spdb_relations', array( 'id' => $this->id, 'copy' => '0', 'oType' => 'entry' ) );
					$db->update( 'spdb_relations', [ 'copy' => '0' ], [ 'id' => $this->id, 'copy' => '1', 'oType' => 'entry' ] );
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		SPFactory::cache()->purgeSectionVars();
		SPFactory::cache()->deleteObj( 'entry', $this->id );
		Sobi::Trigger( $this->name(), 'AfterApprove', [ $this->id, $approve ] );
	}

	/**
	 * @param bool $trigger
	 * @return array
	 */
	public function discard( $trigger = true )
	{
		$data = $this->getCurrentBaseData();
		if ( $trigger ) {
			Sobi::Trigger( 'Entry', 'Unapprove', [ $this->_model, 0 ] );
		}
		// check if the entry was ever approved.
		// if it wasn't we would delete current data and there would be no other data at all
		// See #1221 - Thu, May 8, 2014 11:18:20
		// and what if logging will be switch on first after the entry was already approved?? (Sigrid)
		$count = SPFactory::db()
				->select( 'COUNT(*)', 'spdb_history', [ 'sid' => $this->id, 'changeAction' => [ 'approve', 'approved' ] ] )
				->loadResult();
		if ( $count ) {
			// restore previous version
			foreach ( $this->fields as $field ) {
				$field->rejectChanges( $this->id );
			}
		}
		// reload fields
		$this->loadFields( $this->id );
		// store data
		foreach ( $this->fields as $field ) {
			$field->loadData( $this->id );
			$data[ 'fields' ][ $field->get( 'nid' ) ] = $field->getRaw();
		}
		if ( $count ) {
			SPFactory::db()
					->delete( 'spdb_relations', [ 'id' => $this->id, 'copy' => '1', 'oType' => 'entry' ] );
		}
		if ( $trigger ) {
			Sobi::Trigger( 'Entry', 'AfterUnapprove', [ $this->_model, 0 ] );
		}
		SPFactory::cache()
				->purgeSectionVars()
				->deleteObj( 'entry', $this->id )
				->cleanXMLRelations( $this->categories );
		return $data;
	}

	/**
	 * @param int $state
	 * @param string $reason
	 * @param bool $trigger
	 */
	public function changeState( $state, $reason = null, $trigger = true )
	{
		if ( $trigger ) {
			Sobi::Trigger( $this->name(), 'ChangeState', [ $this->id, $state ] );
		}
		try {
			SPFactory::db()
					->update( 'spdb_object', [ 'state' => ( int )$state, 'stateExpl' => $reason ], [ 'id' => $this->id ] );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		foreach ( $this->fields as $field ) {
			$field->changeState( $this->id, $state );
		}
		SPFactory::cache()
				->purgeSectionVars()
				->deleteObj( 'entry', $this->id )
				->cleanXMLRelations( $this->categories );
		if ( $trigger ) {
			Sobi::Trigger( $this->name(), 'AfterChangeState', [ $this->id, $state ] );
		}
	}

	/**
	 * @param $ident
	 * @throws SPException
	 * @return SPField
	 */
	public function & getField( $ident )
	{
		$field = null;
		if ( is_int( $ident ) ) {
			if ( isset( $this->fieldsIds[ $ident ] ) ) {
				$field =& $this->fieldsIds[ $ident ];
			}
			//  Mon, Jun 29, 2015 10:21:08
			//  Removed as for some reason Rochen's cache (I assume) behave very strange.
			//  When we unpublish a field it causes this exception because for some reason
			//  at their server the data is still being sent.
			//  On the other hand it is not really an issue if we just ignore it
//			else {
//				throw new SPException( SPLang::e( 'THERE_IS_NO_SUCH_FIELD', $ident ) );
//			}
		}
		else {
			if ( isset( $this->fieldsNids[ $ident ] ) ) {
				$field =& $this->fieldsNids[ $ident ];
			}
//			else {
//				throw new SPException( SPLang::e( 'THERE_IS_NO_SUCH_FIELD', $ident ) );
//			}
		}
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ $ident, &$this->fieldsIds, &$this->fieldsNids ] );
		return $field;
	}

	/**
	 * @param string $by
	 * @return SPField[]
	 */
	public function & getFields( $by = 'name' )
	{
		$fields =& $this->fields;
		switch ( $by ) {
			case 'name':
			case 'nid':
				$fields =& $this->fieldsNids;
				break;
			case 'id':
			case 'fid':
				$fields =& $this->fieldsIds;
				break;
		}
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$fields ] );
		return $fields;
	}

	/**
	 * @param $cid
	 * @return int
	 */
	public function getPosition( $cid )
	{
		if ( $this->id ) {
			if ( !( count( $this->categories ) ) ) {
				$this->getCategories();
			}
		}
		return isset( $this->categories[ $cid ][ 'position' ] ) ? $this->categories[ $cid ][ 'position' ] : 0;
	}

	/**
	 * Return the primary category for this entry
	 * @return array
	 */
	public function getPrimary()
	{
		if ( !( count( $this->categories ) ) ) {
			$this->getCategories();
		}
		return isset( $this->categories[ $this->primary ] ) ? $this->categories[ $this->primary ] : 0;
	}

	/**
	 * @param bool $arr
	 * @return array
	 */
	public function getCategories( $arr = false )
	{
		if ( $this->id ) {
			if ( !( count( $this->categories ) ) ) {
				/* @var SPdb $db */
				$db = SPFactory::db();
				/* get fields */
				try {
					$c = [ 'id' => $this->id, 'oType' => 'entry' ];
					if ( !( $this->approved || ( SPRequest::task() == 'entry.edit' || ( Sobi::Can( 'entry.access.unapproved_any' ) ) ) ) ) {
						$c[ 'copy' ] = '0';
					}
					$db->select( [ 'pid', 'position', 'validSince', 'validUntil' ], 'spdb_relations', $c, 'position' );
					$categories = $db->loadAssocList( 'pid' );
					/* validate categories - case some of them has been deleted */
					$cats = array_keys( $categories );
					if ( count( $cats ) ) {
						$cats = $db->select( 'id', 'spdb_object', [ 'id' => $cats ] )->loadResultArray();
					}
					if ( count( $categories ) ) {
						foreach ( $categories as $i => $c ) {
							if ( !( $this->parent ) ) {
								$this->parent = $i;
							}
							if ( !( in_array( $i, $cats ) ) ) {
								unset( $categories[ $i ] );
							}
						}
					}
					/* push the main category to the top of this array */
					if ( isset( $categories [ $this->parent ] ) ) {
						$main = $categories [ $this->parent ];
						unset( $categories[ $this->parent ] );
						$this->categories[ $this->parent ] = $main;
					}
					foreach ( $categories as $cid => $cat ) {
						$this->categories[ $cid ] = $cat;
					}
					if ( $this->categories ) {
						$labels = SPLang::translateObject( array_keys( $this->categories ), [ 'name', 'alias' ], 'category' );
						foreach ( $labels as $id => $t ) {
							$this->categories[ $id ][ 'name' ] = isset( $t[ 'value' ] ) ? $t[ 'value' ] : $t[ 'name' ];
							$this->categories[ $id ][ 'alias' ] = $t[ 'alias' ];
						}
					}
					Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$this->categories ] );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_RELATIONS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
				}
			}
			if ( $arr ) {
				return array_keys( $this->categories );
			}
			else {
				return $this->categories;
			}
		}
		else {
			return [];
		}
	}

	private function nameField()
	{
		/* get the field id of the field contains the entry name */
		if ( $this->section == Sobi::Section() || !( $this->section ) ) {
			$nameField = Sobi::Cfg( 'entry.name_field' );
		}
		else {
			$nameField = SPFactory::db()
					->select( 'sValue', 'spdb_config', [ 'section' => $this->section, 'sKey' => 'name_field', 'cSection' => 'entry' ] )
					->loadResult();
		}
		return $nameField;
	}

	public function validateCache()
	{
		static $remove = [ 'name', 'nid', 'metaDesc', 'metaKeys', 'metaRobots', 'options', 'oType', 'parent' ];
		$core = SPFactory::object( $this->id );
		foreach ( $core as $a => $v ) {
			if ( !( in_array( $a, $remove ) ) ) {
				$this->_set( $a, $v );
			}
		}
	}

	/**
	 * @param int $sid
	 * @param bool $enabled
	 * @return void
	 */
	public function loadFields( $sid = 0, $enabled = false )
	{
		$sid = $sid ? $sid : $this->section;
		/* @var SPdb $db */
		$db = SPFactory::db();

		static $fields = [];
		static $lang = null;
		$lang = $lang ? $lang : Sobi::Lang( false );
		if ( !isset( $fields[ $sid ] ) ) {
			/* get fields */
			try {
				if ( $enabled ) {
					$db->select( '*', 'spdb_field', [ 'section' => $sid, 'enabled' => 1, 'adminField>' => -1 ], 'position' );
				}
				else {
					$db->select( '*', 'spdb_field', [ 'section' => $sid, 'adminField>' => -1 ], 'position' );
				}
				$fields[ $sid ] = $db->loadObjectList();
				Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$fields ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		$nameField = $this->nameField();

		if ( !( $this->_loaded ) || !( count( $fields[ $sid ] ) ) ) {
			if ( count( $fields[ $sid ] ) ) {
				/* if it is an entry - prefetch the basic fields data */
				if ( $this->id ) {
					$noCopy = $this->checkCopy();
					/* in case the entry is approved, or we are editing an entry, or the user can see unapproved changes */
					if ( $this->approved || $noCopy ) {
						$ordering = 'copy.desc';
					}
					/* otherwise - if the entry is not approved, get the non-copies first */
					else {
						$ordering = 'copy.asc';
					}
					try {
						$fdata = $db
								->select( '*', 'spdb_field_data', [ 'sid' => $this->id ], $ordering )
								->loadObjectList();
						$fieldsdata = [];
						if ( count( $fdata ) ) {
							foreach ( $fdata as $data ) {
								/* if it has been already set - check if it is not better language choose */
								if ( isset( $fieldsdata[ $data->fid ] ) ) {
									/*
									 * I know - the whole thing could be shorter
									 * but it is better to understand and debug this way
									 */
									if ( $data->lang == $lang ) {
										if ( $noCopy ) {
											if ( !( $data->copy ) ) {
												$fieldsdata[ $data->fid ] = $data;
											}
										}
										else {
											$fieldsdata[ $data->fid ] = $data;
										}
									}
									/* set for cache other lang */
									else {
										$fieldsdata[ 'langs' ][ $data->lang ][ $data->fid ] = $data;
									}
								}
								else {
									if ( $noCopy ) {
										if ( !( $data->copy ) ) {
											$fieldsdata[ $data->fid ] = $data;
										}
									}
									else {
										$fieldsdata[ $data->fid ] = $data;
									}
								}
							}
						}
						unset( $fdata );
						SPFactory::registry()->set( 'fields_data_' . $this->id, $fieldsdata );
					} catch ( SPException $x ) {
						Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					}
				}
				foreach ( $fields[ $sid ] as $f ) {
					/* @var SPField $field */
					$field = SPFactory::Model( 'field', defined( 'SOBIPRO_ADM' ) );
					$field->extend( $f );
					if ( isset( $fieldsdata[ $f->fid ] ) ) {
						$field->loadData( $this->id );
					}
					$this->fields[] = $field;
					$this->fieldsNids[ $field->get( 'nid' ) ] = $this->fields[ count( $this->fields ) - 1 ];
					$this->fieldsIds[ $field->get( 'fid' ) ] = $this->fields[ count( $this->fields ) - 1 ];
					/* case it was the name field */
					if ( $field->get( 'fid' ) == $nameField ) {
						/* get the entry name */
						$this->name = $field->getRaw();
						/* save the nid (name id) of the field where the entry name is saved */
						$this->nameField = $field->get( 'nid' );
					}
				}
				$this->_loaded = true;
			}
		}
	}

	private function checkCopy()
	{
		return !(
				in_array( SPRequest::task(), [ 'entry.approve', 'entry.edit', 'entry.save', 'entry.submit', 'entry.payment' ] ) ||
				Sobi::Can( 'entry.access.unapproved_any' ) ||
				( $this->owner == Sobi::My( 'id' ) && Sobi::Can( 'entry.manage.own' ) ) ||
				( $this->owner == Sobi::My( 'id' ) && Sobi::Can( 'entry.access.unpublished_own' ) ) ||
				Sobi::Can( 'entry.manage.*' )
		);
	}

	/**
	 * @return array
	 */
	protected function types()
	{
		return self::$types;
	}

	/**
	 */
	public function delete()
	{
		parent::delete();
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ $this->id ] );
		foreach ( $this->fields as $field ) {
			$field->deleteData( $this->id );
		}
		/** Thu, Jul 30, 2015 10:11:57 - delete history */
		SPFactory::db()
				->delete( 'spdb_history', [ 'sid' => $this->id ] );

		/** Thu, Jul 30, 2015 10:22:38 - delete payments */
		SPFactory::payment()
				->deletePayments( $this->id );

		/** Thu, Jul 30, 2015 11:32:45 - delete counters */
		SPFactory::db()
				->delete( 'spdb_counter', [ 'sid' => $this->id ] );

		SPFactory::cache()->purgeSectionVars();
		SPFactory::cache()->deleteObj( 'entry', $this->id );
	}

	/**
	 * @param string $request
	 * @throws SPException
	 * @return void
	 */
	public function validate( $request = 'post' )
	{
		$this->loadFields( Sobi::Section() );
		foreach ( $this->fields as $field ) {
			/* @var $field SPField */
			if ( $field->enabled( 'form', !( $this->id ) ) ) {
				try {
					$field->validate( $this, $request );
				} catch ( SPException $x ) {
					$exception = new SPException( $x->getMessage() );
					$exception->setData( [ 'field' => $field->get( 'nid' ) ] );
					throw $exception;
				}
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Site/lib/models/SPDBObject#save()
	 * @param string $request
	 * @throws SPException
	 */
	public function save( $request = 'post' )
	{
		$this->loadFields( Sobi::Section(), true );
		// Thu, Feb 19, 2015 12:12:47 - it should be actually "beforeSave"
		Sobi::Trigger( $this->name(), 'Before' . ucfirst( __FUNCTION__ ), [ $this->id ] );
		/* save the base object data */
		/* @var SPdb $db */
		$db = SPFactory::db();
		$db->transaction();
		$clone = Input::Task() == 'entry.clone';

		if ( !( $this->nid ) || $clone ) {
			$this->nid = strtolower( SPLang::nid( SPRequest::string( $this->nameField, null, false, $request ), true ) );
			$this->nid = $this->createAlias();
			/** Thu, Jul 30, 2015 12:15:25 - what the hell was that? */
//			$this->name = $this->nid;
		}
		if ( !( $this->id ) && Sobi::Cfg( 'entry.publish_limit', 0 ) && !( defined( 'SOBI_ADM_PATH' ) ) ) {
			SPRequest::set( 'entry_createdTime', 0, $request );
			SPRequest::set( 'entry_validSince', 0, $request );
			SPRequest::set( 'entry_validUntil', 0, $request );
			$this->validUntil = gmdate( 'Y-m-d H:i:s', time() + ( Sobi::Cfg( 'entry.publish_limit', 0 ) * 24 * 3600 ) );
		}
		$preState = Sobi::Reg( 'object_previous_state' );
		parent::save( $request );
		$nameField = $this->nameField();
		/* get the fields for this section */
		foreach ( $this->fields as $field ) {
			/* @var $field SPField */
			try {
				if ( $field->enabled( 'form', $preState[ 'new' ] ) ) {
					$field->saveData( $this, $request, $clone );
				}
				else {
					$field->finaliseSave( $this, $request, $clone );
				}
				if ( $field->get( 'id' ) == $nameField ) {
					/* get the entry name */
					$this->name = $field->getRaw();
					/* save the nid (name id) of the field where the entry name is saved */
					$this->nameField = $field->get( 'nid' );
				}
			} catch ( SPException $x ) {
				if ( SPRequest::task() != 'entry.clone' ) {
					$db->rollback();
					throw new SPException( SPLang::e( 'CANNOT_SAVE_FIELS_DATA', $x->getMessage() ) );
				}
				else {
					Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
		}
		$values = [];
		/* get categories */
		$cats = Sobi::Reg( 'request_categories' );
		if ( !( count( $cats ) ) ) {
			$cats = SPRequest::arr( 'entry_parent', SPFactory::registry()->get( 'request_categories', [] ), $request );
		}
		/* by default it should be comma separated string */
		if ( !( count( $cats ) ) ) {
			$cats = SPRequest::string( 'entry_parent', null, $request );
			if ( strlen( $cats ) && strpos( $cats, ',' ) ) {
				$cats = explode( ',', $cats );
				foreach ( $cats as $i => $cat ) {
					$c = ( int )trim( $cat );
					if ( $c ) {
						$cats[ $i ] = $c;
					}
					else {
						unset( $cats[ $i ] );
					}
				}
			}
			elseif ( strlen( $cats ) ) {
				$cats = [ ( int )$cats ];
			}
		}
		if ( is_array( $cats ) && count( $cats ) ) {
			foreach ( $cats as $i => $v ) {
				if ( !( $v ) ) {
					unset( $cats[ $i ] );
				}
			}
		}
		if ( is_array( $cats ) && count( $cats ) ) {
			/* get the ordering in these categories */
			try {
				$db->select( 'pid, MAX(position)', 'spdb_relations', [ 'pid' => $cats, 'oType' => 'entry' ], null, 0, 0, false, 'pid' );
				$cPos = $db->loadAssocList( 'pid' );
				$currPos = $db->select( [ 'pid', 'position' ], 'spdb_relations', [ 'id' => $this->id, 'oType' => 'entry' ] )->loadAssocList( 'pid' );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			/* set the right position */
			foreach ( $cats as $i => $cat ) {
				$copy = 0;
				if ( !( $this->approved ) ) {
					$copy = isset( $this->categories[ $cats[ $i ] ] ) ? 0 : 1;
				}
				else {
					$db->delete( 'spdb_relations', [ 'id' => $this->id, 'oType' => 'entry' ] );
				}
				if ( isset( $currPos[ $cat ] ) ) {
					$pos = $currPos[ $cat ][ 'position' ];
				}
				else {
					$pos = isset( $cPos[ $cat ] ) ? $cPos[ $cat ][ 'MAX(position)' ] : 0;
					$pos++;
				}
				$values[] = [ 'id' => $this->id, 'pid' => $cats[ $i ], 'oType' => 'entry', 'position' => $pos, 'validSince' => $this->validSince, 'validUntil' => $this->validUntil, 'copy' => $copy ];
			}
			try {
				$db->insertArray( 'spdb_relations', $values, true );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		elseif ( !( count( $this->categories ) ) ) {
			throw new SPException( SPLang::e( 'MISSING_CAT' ) );
		}
		if ( $preState[ 'new' ] ) {
			$this->countVisit( true );
		}
		/* trigger possible state changes */
		if ( $preState[ 'approved' ] != $this->approved ) {
			if ( $this->approved ) {
				$this->approveFields( true );
				// it's being done by the method above - removing
				//Sobi::Trigger( $this->name(), 'AfterApprove', array( $this->id, $this->approved ) );
			}
		}
		if ( $preState[ 'state' ] != $this->state ) {
			Sobi::Trigger( $this->name(), 'AfterChangeState', [ $this->id, $this->state ] );
		}
		SPFactory::cache()->purgeSectionVars();
		SPFactory::cache()->deleteObj( 'entry', $this->id );
		if ( count( $cats ) ) {
			foreach ( $cats as $cat ) {
				SPFactory::cache()->deleteObj( 'category', $cat );
			}
		}
		if ( !( $preState[ 'new' ] ) ) {
			Sobi::Trigger( $this->name(), 'AfterUpdate', [ &$this ] );
		}
		else {
			Sobi::Trigger( $this->name(), 'After' . ucfirst( __FUNCTION__ ), [ &$this ] );
		}
	}

	/**
	 * @return array
	 */
	public function getCurrentBaseData()
	{
		$data = [];
		$data[ 'owner' ] = $this->owner;
		$data[ 'categories' ] = $this->categories;
		$data[ 'position' ] = $this->position;
		$data[ 'createdTime' ] = $this->createdTime;
		$data[ 'updatedTime' ] = $this->updatedTime;
		$data[ 'updater' ] = $this->updater;
		$data[ 'updaterIP' ] = $this->updaterIP;
		$data[ 'counter' ] = $this->counter;
		$data[ 'nid' ] = $this->nid;
		$data[ 'ownerIP' ] = $this->ownerIP;
		$data[ 'parent' ] = $this->parent;
		$data[ 'state' ] = $this->state;
		$data[ 'validSince' ] = $this->validSince;
		$data[ 'validUntil' ] = $this->validUntil;
		$data[ 'version' ] = $this->version;
		return $data;
	}

	public function setRevData( $attr, $value )
	{
		$this->$attr = $value;
	}
}
