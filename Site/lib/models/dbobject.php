<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
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
 * @created 10-Jan-2009 5:24:28 PM
 */
abstract class SPDBObject extends SPObject
{
	/**
	 * @var bool
	 */
	protected $approved = false;
	/**
	 * @var bool
	 */
	protected $confirmed = false;
	/**
	 * @var int
	 */
	protected $counter = 0;
	/**
	 * @var int
	 */
	protected $section = 0;
	/**
	 * @var bool
	 */
	protected $cout = false;
	/**
	 * @var string
	 */
	protected $coutTime = null;
	/**
	 * @var string
	 */
	protected $createdTime = null;
	/**
	 * @var string
	 */
	protected $defURL = null;
	/**
	 * @var int database object id
	 */
	protected $id = 0;
	/**
	 * @var string
	 */
	protected $nid = null;
	/**
	 * @var string
	 */
	protected $metaDesc = null;
	/**
	 * @var string
	 */
	protected $metaKeys = null;
	/**
	 * @var string
	 */
	protected $metaAuthor = null;
	/**
	 * @var string
	 */
	protected $metaRobots = null;
	/**
	 * @var string
	 */
	protected $name = null;
	/**
	 * @var array
	 */
	protected $options = [];
	/**
	 * @var string
	 */
	protected $oType = null;
	/**
	 * @var int
	 */
	protected $owner = 0;
	/**
	 * @var string
	 */
	protected $ownerIP = null;
	/**
	 * @var array
	 */
	protected $params = [];
	/**
	 * @var int
	 */
	protected $parent = 0;
	/**
	 * @var string
	 */
	protected $query = null;
	/**
	 * @var int
	 */
	protected $state = 0;
	/**
	 * @var string
	 */
	protected $stateExpl = null;
	/**
	 * @var string
	 */
	protected $template = null;
	/**
	 * @var string
	 */
	protected $updatedTime = null;
	/**
	 * @var int
	 */
	protected $updater = 0;
	/**
	 * @var string
	 */
	protected $updaterIP = null;
	/**
	 * @var string
	 */
	protected $validSince = null;
	/**
	 * @var string
	 */
	protected $validUntil = null;
	/**
	 * @var int
	 */
	protected $version = 0;
	/**
	 * @var array
	 */
	protected $properties = [];

	/**
	 * @param string $name
	 * @param array $data
	 */
	public function setProperty( $name, $data )
	{
		$this->properties[ $name ] = $data;
	}

	/**
	 * list of adjustable properties and the corresponding request method for each property.
	 * If a property isn't declared here, it will be ignored in the getRequest method
	 * @var array
	 */
	private static $types = [
			'approved' => 'bool',
			'state' => 'int',
			'confirmed' => 'bool',
			'counter' => 'int',
			'createdTime' => 'timestamp',
			'defURL' => 'string',
			'metaAuthor' => 'string',
			'metaDesc' => 'string',
			'metaKeys' => 'string',
			'metaRobots' => 'string',
			'name' => 'string',
			'nid' => 'cmd',
			'owner' => 'int',
			'ownerIP' => 'ip',
			'parent' => 'int',
			'stateExpl' => 'string',
			'validSince' => 'timestamp',
			'validUntil' => 'timestamp',
			'params' => 'arr',
	];
	/**
	 * @var array
	 */
	private static $translatable = [ 'nid', 'metaDesc', 'metaKeys' ];

	/**
	 * @return \SPDBObject
	 */
	public function __construct()
	{
		$this->validUntil = SPFactory::config()->date( SPFactory::db()->getNullDate(), 'date.db_format' );
		$this->createdTime = SPFactory::config()->date( gmdate( 'U' ), 'date.db_format', null, true );
		$this->validSince = SPFactory::config()->date( gmdate( 'U' ), 'date.db_format', null, true );
		$this->ownerIP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$this->updaterIP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$this->updater = Sobi::My( 'id' );
		$this->owner = Sobi::My( 'id' );
		$this->updatedTime = SPFactory::config()->date( time(), 'date.db_format' );
		Sobi::Trigger( 'CreateModel', $this->name(), [ &$this ] );
	}


	/**
	 * @param string $attr
	 * @param mixed $default
	 * @return mixed
	 */
	public function param( $attr, $default = null )
	{
		if ( isset( $this->params[ $attr ] ) ) {
			if ( is_string( $this->params[ $attr ] ) ) {
				return stripslashes( $this->params[ $attr ] );
			}
			else {
				return $this->params[ $attr ];
			}
		}
		else {
			return $default;
		}
	}


	/**
	 * @param string $attr
	 * @param $value
	 * @return mixed
	 */
	public function & setParam( $attr, $value )
	{
		$this->params[ $attr ] = $value;
		return $this;
	}


	public function formatDatesToEdit()
	{
		if ( $this->validUntil ) {
			$this->validUntil = SPFactory::config()->date( $this->validUntil, 'date.db_format' );
		}
		$this->createdTime = SPFactory::config()->date( $this->createdTime, 'date.db_format' );
		$this->validSince = SPFactory::config()->date( $this->validSince, 'date.db_format' );
		$this->updatedTime = SPFactory::config()->date( $this->updatedTime, 'date.db_format' );
	}

	public function formatDatesToDisplay()
	{
		if ( $this->validUntil ) {
			$this->validUntil = SPFactory::config()->date( $this->validUntil, 'date.publishing_format' );
		}
		$this->createdTime = SPFactory::config()->date( $this->createdTime, 'date.publishing_format' );
		$this->validSince = SPFactory::config()->date( $this->validSince, 'date.publishing_format' );
		$this->updatedTime = SPFactory::config()->date( $this->updatedTime, 'date.publishing_format' );
	}
	/**
	 * @param int $state
	 * @param string $reason
	 */
	public function changeState( $state, $reason = null )
	{
		try {
			SPFactory::db()->update( 'spdb_object', [ 'state' => ( int )$state, 'stateExpl' => $reason ], [ 'id' => $this->id ] );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		SPFactory::cache()
				->purgeSectionVars()
				->deleteObj( $this->type(), $this->id )
				->deleteObj( 'category', $this->parent );
	}

	/**
	 * Checking in current object
	 */
	public function checkIn()
	{
		if ( $this->id ) {
			$this->cout = 0;
			$this->coutTime = null;
			try {
				SPFactory::db()->update( 'spdb_object', [ 'coutTime' => $this->coutTime, 'cout' => $this->cout ], [ 'id' => $this->id ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
	}

	/**
	 * checking out current object
	 */
	public function checkOut()
	{
		if ( $this->id ) {
			/* @var SPdb $db */
			$config =& SPFactory::config();
			$this->cout = Sobi::My( 'id' );
			$this->coutTime = $config->date( ( time() + $config->key( 'editing.def_cout_time', 3600 ) ), 'date.db_format' );
			try {
				SPFactory::db()->update( 'spdb_object', [ 'coutTime' => $this->coutTime, 'cout' => $this->cout ], [ 'id' => $this->id ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
	}

	/**
	 */
	public function delete()
	{
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ $this->id ] );
		try {
			SPFactory::db()->delete( 'spdb_object', [ 'id' => $this->id ] );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		try {
			SPFactory::db()->delete( 'spdb_relations', [ 'id' => $this->id, 'oType' => $this->type() ] );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		try {
			SPFactory::db()->delete( 'spdb_language', [ 'id' => $this->id, 'oType' => $this->type() ] );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * @param string $type
	 * @param bool $recursive
	 * @param int $state
	 * @param bool $name
	 * @return array
	 */
	public function getChilds( $type = 'entry', $recursive = false, $state = 0, $name = false )
	{
		static $lang = null;
		if ( !( $lang ) ) {
			$lang = Sobi::Lang( false );
		}
		$childs = SPFactory::cache()->getVar( 'childs_' . $lang . $type . ( $recursive ? '_recursive' : '' ) . ( $name ? '_full' : '' ) . $state, $this->id );
		if ( $childs ) {
			return $childs == SPC::NO_VALUE ? [] : $childs;
		}
		$db = SPFactory::db();
		$childs = [];
		try {
			$cond = [ 'pid' => $this->id ];
			if ( $state ) {
				$cond[ 'so.state' ] = $state;
				$cond[ 'so.approved' ] = $state;
				$tables = $db->join(
						[
								[ 'table' => 'spdb_object', 'as' => 'so', 'key' => 'id' ],
								[ 'table' => 'spdb_relations', 'as' => 'sr', 'key' => 'id' ]
						]
				);
				$db->select( [ 'sr.id', 'sr.oType' ], $tables, $cond );
			}
			else {
				$db->select( [ 'id', 'oType' ], 'spdb_relations', $cond );
			}
			$results = $db->loadAssocList( 'id' );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_CHILDS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if ( $recursive && count( $results ) ) {
			foreach ( $results as $cid ) {
				$this->rGetChilds( $results, $cid, $type );
			}
		}
		if ( count( $results ) ) {
			if ( $type == 'all' ) {
				foreach ( $results as $id => $r ) {
					$childs[ $id ] = $r[ 'id' ];
				}
			}
			else {
				foreach ( $results as $id => $r ) {
					if ( $r[ 'oType' ] == $type ) {
						$childs[ $id ] = $id;
					}
				}
			}
		}
		if ( $name && count( $childs ) ) {
			$names = SPLang::translateObject( $childs, [ 'name', 'alias' ], $type );
			if ( is_array( $names ) && !empty( $names ) ) {
				foreach ( $childs as $i => $id ) {
					$childs[ $i ] = [ 'name' => $names[ $id ][ 'value' ], 'alias' => $names[ $id ][ 'alias' ] ];
				}
			}
		}
		if ( !$state ) {
			SPFactory::cache()->addVar( $childs, 'childs_' . $lang . $type . ( $recursive ? '_recursive' : '' ) . ( $name ? '_full' : '' ) . $state, $this->id );
		}
		return $childs;
	}

	/**
	 * @param array $results
	 * @param string $type
	 * @param int $id
	 */
	private function rGetChilds( &$results, $id, $type = 'entry' )
	{
		if ( is_array( $id ) ) {
			$id = $id[ 'id' ];
		}
		/* @var SPdb $db */
		$db =& SPFactory::db();
		try {
			$cond = [ 'pid' => $id ];
			/** Tue, Mar 25, 2014 12:46:08 - it's a recursive function so we need entries and categories
			 *  See Issue #1211
			 *  Thanks Marcel
			 * */
//			if ( $type ) {
//				$cond[ 'oType' ] = $type;
//			}
			/** Mon, Apr 18, 2016 13:10:03  but if we are looking for categories only that's perfectly OK */
			if ( $type == 'category' ) {
				$cond[ 'oType' ] = $type;
			}
			$r = $db->select( [ 'id', 'oType' ], 'spdb_relations', $cond )
					->loadAssocList( 'id' );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_CHILDS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if ( count( $r ) ) {
			foreach ( $r as $id => $rs ) {
				$results[ $id ] = $rs;
				$this->rGetChilds( $results, $id, $type );
			}
		}
	}

	/**
	 */
	protected function createAlias()
	{
		/* check nid */
		$c = 1;
		static $add = 0;
		$suffix = null;
		if ( !( strlen( $this->nid ) ) ) {
			$this->nid = strtolower( SPLang::nid( $this->name, true ) );
		}
		while ( $c ) {
			try {
				$condition = [ 'oType' => $this->oType, 'nid' => $this->nid . $suffix ];
				if ( $this->id ) {
					$condition[ '!id' ] = $this->id;
				}
				$c = SPFactory::db()
						->select( 'COUNT( nid )', 'spdb_object', $condition )
						->loadResult();
				if ( $c > 0 ) {
					$suffix = '-' . ++$add;
				}
			} catch ( SPException $x ) {
			}
		}
		return $this->nid . $suffix;
	}

	/**
	 * Gettin data from request for this object
	 * @param string $prefix
	 * @param string $request
	 */
	public function getRequest( $prefix = null, $request = 'POST' )
	{
		$prefix = $prefix ? $prefix . '_' : null;
		/* get data types of my  properties */
		$properties = get_object_vars( $this );
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ) . 'Start', [ &$properties ] );
		/* and of the parent properties */
		$types = array_merge( $this->types(), self::$types );
		foreach ( $properties as $property => $values ) {
			/* if this is an internal variable */
			if ( substr( $property, 0, 1 ) == '_' ) {
				continue;
			}
			/* if no data type has been declared */
			if ( !isset( $types[ $property ] ) ) {
				continue;
			}
			/* if there was no data for this property ( not if it was just empty ) */
			if ( !( SPRequest::exists( $prefix . $property, $request ) ) ) {
				continue;
			}
			/* if the declared data type has not handler in request class */
			if ( !method_exists( 'SPRequest', $types[ $property ] ) ) {
				Sobi::Error( $this->name(), SPLang::e( 'Method %s does not exist!', $types[ $property ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
				continue;
			}
			/* now we get it ;) */
			$method = $types[ $property ];
			$this->$property = SPRequest::$method( $prefix . $property, null, $request );
		}
		/* trigger plugins */
		Sobi::Trigger( 'getRequest', $this->name(), [ &$this ] );
	}

	public function countChilds( $type = null, $state = 0 )
	{
		return count( $this->getChilds( $type, true, $state ) );
	}

	/**
	 * @return string
	 */
	public function type()
	{
		return $this->oType;
	}

	public function countVisit( $reset = false )
	{
		$count = true;
		Sobi::Trigger( 'CountVisit', ucfirst( $this->type() ), [ &$count, $this->id ] );
		if ( $this->id && $count ) {
			try {
				SPFactory::db()->insertUpdate( 'spdb_counter', [ 'sid' => $this->id, 'counter' => ( $reset ? '0' : ++$this->counter ), 'lastUpdate' => 'FUNCTION:NOW()' ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_INC_COUNTER_DB', $x->getMessage() ), SPC::ERROR, 0, __LINE__, __FILE__ );
			}
		}
	}

	/**
	 * @param string $request
	 */
	public function save( $request = 'post' )
	{
		$this->version++;
		/* get current data */
		$this->updatedTime = SPRequest::now();
		$this->updaterIP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$this->updater = Sobi::My( 'id' );
		$this->nid = SPLang::nid( $this->nid, true );
		if ( !( $this->nid ) ) {
			$this->nid = SPLang::nid( $this->name, true );
		}
		/* get THIS class properties */
		$properties = get_class_vars( __CLASS__ );

		/* if new object */
		if ( !$this->id ) {
			/** @var the notification App is using it to recognise if it is a new entry or an update */
			$this->createdTime = $this->updatedTime;
			$this->owner = $this->owner ? $this->owner : $this->updater;
			$this->ownerIP = $this->updaterIP;
		}

		/* just a security check to avoid mistakes */
		else {
			/** Fri, Dec 19, 2014 19:33:52
			 * When storing it we should actually get already UTC unix time stamp
			 * so there is not need to remove it again
			 */
//			$this->createdTime = $this->createdTime && is_numeric( $this->createdTime ) ? gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $this->createdTime - SPFactory::config()->getTimeOffset() ) : $this->createdTime;
			$this->createdTime = $this->createdTime && is_numeric( $this->createdTime ) ? gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $this->createdTime ) : $this->createdTime;
			$obj = SPFactory::object( $this->id );
			if ( $obj->oType != $this->oType ) {
				Sobi::Error( 'Object Save', sprintf( 'Serious security violation. Trying to save an object which claims to be an %s but it is a %s. Task was %s', $this->oType, $obj->oType, SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
				exit;
			}

		}
		if ( is_numeric( $this->validUntil ) ) {
//			$this->validUntil = $this->validUntil ? gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $this->validUntil - SPFactory::config()->getTimeOffset() ) : null;
			$this->validUntil = $this->validUntil ? gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $this->validUntil ) : null;
		}
		if ( is_numeric( $this->validSince ) ) {
			$this->validSince = $this->validSince ? gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $this->validSince ) : null;
		}

		/* @var SPdb $db */
		$db = SPFactory::db();
		$db->transaction();

		/* get database columns and their ordering */
		$cols = $db->getColumns( 'spdb_object' );
		$values = [];

		/*
		 * @todo: manage own is not implemented yet
		 */
		//$this->approved = Sobi::Can( $this->type(), 'manage', 'own' );
		/* if not published, check if user can manage own and if yes, publish it */
		if ( !( $this->state ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
			$this->state = Sobi::Can( $this->type(), 'publish', 'own' );
		}
		if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
			$this->approved = Sobi::Can( $this->type(), 'publish', 'own' );
		}
//		elseif ( defined( 'SOBIPRO_ADM' ) ) {
//			$this->approved = Sobi::Can( $this->type(), 'publish', 'own' );
//		}

		/* and sort the properties in the same order */
		foreach ( $cols as $col ) {
			$values[ $col ] = array_key_exists( $col, $properties ) ? $this->$col : '';
		}

		/* trigger plugins */
		Sobi::Trigger( 'save', $this->name(), [ &$this ] );
		/* try to save */
		try {
			/* if new object */
			if ( !$this->id ) {
				$db->insert( 'spdb_object', $values );
				$this->id = $db->insertid();
			}
			/* if update */
			else {
				$db->update( 'spdb_object', $values, [ 'id' => $this->id ] );
			}
		} catch ( SPException $x ) {
			$db->rollback();
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_OBJECT_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}

		/* get translatable properties */
		$attributes = array_merge( $this->translatable(), self::$translatable );
		$labels = [];
		$defLabels = [];
		foreach ( $attributes as $attr ) {
			if ( $this->has( $attr ) ) {
				$labels[ ] = [ 'sKey' => $attr, 'sValue' => $this->$attr, 'language' => Sobi::Lang(), 'id' => $this->id, 'oType' => $this->type(), 'fid' => 0 ];
				if ( Sobi::Lang() != Sobi::DefLang() ) {
					$defLabels[ ] = [ 'sKey' => $attr, 'sValue' => $this->$attr, 'language' => Sobi::DefLang(), 'id' => $this->id, 'oType' => $this->type(), 'fid' => 0 ];
				}
			}
		}

		/* save translatable properties */
		if ( count( $labels ) ) {
			try {
				if ( Sobi::Lang() != Sobi::DefLang() ) {
					$db->insertArray( 'spdb_language', $defLabels, false, true );
				}
				$db->insertArray( 'spdb_language', $labels, true );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_OBJECT_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		$db->commit();
		$this->checkIn();
	}

	/**
	 * Dummy function
	 */
	public function update()
	{
		return $this->save();
	}

	/**
	 * @param stdClass $obj
	 * @param bool $cache
	 */
	public function extend( $obj, $cache = false )
	{
		if ( !empty( $obj ) ) {
			foreach ( $obj as $k => $v ) {
				$this->_set( $k, $v );
			}
		}
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$obj ] );
		$this->loadTable( $cache );
		$this->validUntil = SPFactory::config()->date( $this->validUntil, 'Y-m-d H:i:s' );
	}

	/**
	 * @param int $id
	 * @return \SPDBObject
	 * stdClass $obj
	 */
	public function & init( $id = 0 )
	{
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ) . 'Start', [ &$id ] );
		$this->id = $id ? $id : $this->id;
		if ( $this->id ) {
			try {
				$obj = SPFactory::object( $this->id );
				if ( !empty( $obj ) ) {
					/* ensure that the id was right */
					if ( $obj->oType == $this->oType ) {
						$this->extend( $obj );
					}
					else {
						$this->id = 0;
					}
				}
				$this->createdTime = SPFactory::config()->date( $this->createdTime );
				$this->validSince = SPFactory::config()->date( $this->validSince );
				if ( $this->validUntil ) {
					$this->validUntil = SPFactory::config()->date( $this->validUntil );
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_OBJECT_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			/** Wed, Feb 3, 2016 14:24:09 The extend method calls already the loadTable method */
//			$this->loadTable();
		}
		return $this;
	}

	/**
	 * @param $id
	 * @return SPDBObject
	 */
	public function load( $id )
	{
		return $this->init( $id );
	}

	/**
	 */
	public function loadTable()
	{
		if ( $this->has( '_dbTable' ) && $this->_dbTable ) {
			try {
				$db = SPFactory::db();
				$obj = $db->select( '*', $this->_dbTable, [ 'id' => $this->id ] )
						->loadObject();
				$counter = $db->select( 'counter', 'spdb_counter', [ 'sid' => $this->id ] )
						->loadResult();
				if ( $counter !== null ) {
					$this->counter = $counter;
				}
				Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$obj ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			if ( !empty( $obj ) ) {
				foreach ( $obj as $k => $v ) {
					$this->_set( $k, $v );
				}
			}
			else {
				Sobi::Error( $this->name(), SPLang::e( 'NO_ENTRIES' ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		$this->translate();
	}

	/**
	 */
	protected function translate()
	{
		$attributes = array_merge( $this->translatable(), self::$translatable );
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ) . 'Start', [ &$attributes ] );
		$labels = SPLang::translateObject( $this->id, $attributes );
		foreach ( $labels[ $this->id ] as $k => $label ) {
			if ( in_array( $k, $attributes ) ) {
				$this->_set( $k, $label );
			}
		}

//		$db =& SPFactory::db();
//		try {
//			$labels = $db
//					->select( 'sValue, sKey', 'spdb_language', array( 'id' => $this->id, 'sKey' => $attributes, 'language' => Sobi::Lang(), 'oType' => $this->type() ) )
//					->loadAssocList( 'sKey' );
//			/* get labels in the default lang first */
//			if ( Sobi::Lang( false ) != Sobi::DefLang() ) {
//				$dlabels = $db
//						->select( 'sValue, sKey', 'spdb_language', array( 'id' => $this->id, 'sKey' => $attributes, 'language' => Sobi::DefLang(), 'oType' => $this->type() ) )
//						->loadAssocList( 'sKey' );
//				if ( count( $dlabels ) ) {
//					foreach ( $dlabels as $k => $v ) {
//					foreach ( $dlabels as $k => $v ) {
//						if ( !( isset( $labels[ $k ] ) ) || !( $labels[ $k ] ) ) {
//							$labels[ $k ] = $v;
//						}
//					}
//				}
//			}
//		} catch ( SPException $x ) {
//			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
//		}
//		if ( count( $labels ) ) {
//			foreach ( $labels as $k => $v ) {
//				$this->_set( $k, $v[ 'sValue' ] );
//			}
//		}
		Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$labels ] );
	}

	/**
	 * @param string $var
	 * @param mixed $val
	 */
	protected function _set( $var, $val )
	{
		if ( $this->has( $var ) ) {
			if ( is_array( $this->$var ) && is_string( $val ) && strlen( $val ) > 2 ) {
				try {
					$val = SPConfig::unserialize( $val, $var );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( '%s.', $x->getMessage() ), SPC::NOTICE, 0, __LINE__, __FILE__ );
				}
			}
			$this->$var = $val;
		}
	}

	/**
	 * @return bool
	 */
	public function isCheckedOut()
	{
		if (
				$this->cout &&
				$this->cout != Sobi::My( 'id' ) &&
				strtotime( $this->coutTime ) > time()
		) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @param string $var
	 * @param mixed $val
	 * @return \SPObject|void
	 */
	public function & set( $var, $val )
	{
		static $types = [];
		if ( !count( $types ) ) {
			$types = array_merge( $this->types(), self::$types );
		}
		if ( $this->has( $var ) && isset( $types[ $var ] ) ) {
			if ( is_array( $this->$var ) && is_string( $val ) && strlen( $val ) > 2 ) {
				try {
					$val = SPConfig::unserialize( $val, $var );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( '%s.', $x->getMessage() ), SPC::NOTICE, 0, __LINE__, __FILE__ );
				}
			}
			$this->$var = $val;
		}
		return $this;
	}

	/**
	 * @return array
	 */
	protected function translatable()
	{
		return [];
	}

}
