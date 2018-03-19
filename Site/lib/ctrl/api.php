<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

use Sobi\Input\Input;
use Sobi\Utils\StringUtils;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );


/**
 * @author Radek Suski
 * @version 1.0
 * @created Thu, Nov 2, 2017 17:40:33
 */
class SPApiCtrl extends SPController
{
	const ENTRIES_LIMIT = 25;

	/**
	 *
	 */
	public function execute()
	{
		if ( !( Sobi::Cfg( 'api.enabled' ) ) ) {
			$this->answer( [], 403, [ 'error' => 'Unauthorised access' ] );
		}
		$data = null;
		try {
			switch ( $this->_task ) {
				case 'sections':
					$this->sections();
					break;
				case 'category':
					$this->category();
					break;
				case 'entries':
					$this->entries();
					break;
				case 'entry':
					$this->entry();
					break;
				case 'fields':
					$this->fields();
					break;
			}
		} catch ( Exception $x ) {
			$this->answer( [], $x->getCode(), [ 'error' => $x->getMessage() ] );
		}
	}

	/**
	 *
	 */
	protected function fields()
	{
		$fields = [];
		$all = SPConfig::fields( Sobi::Section() );
		if ( count( $all ) ) {
			foreach ( $all as $fid => $name ) {
				/** @var SPField $field */
				$field = SPFactory::Model( 'field' );
				$field->init( $fid );
				if ( $field->get( 'enabled' ) ) {
					$fields[ $fid ] = [
							'type' => $field->get( 'fieldType' ),
							'nid' => $field->get( 'nid' ),
							'id' => $field->get( 'id' ),
							'name' => $field->get( 'name' ),
							'description' => $field->get( 'description' ),
							'default-value' => $field->get( 'defaultValue' ),
							'priority' => $field->get( 'priority' ),
							'position' => $field->get( 'position' ),
							'required' => $field->get( 'position' ),
					];
				}
				if ( method_exists( $field, 'apiData' ) ) {
					$field->apiData( $fields );
				}
			}
		}
		$this->answer( $fields );
	}

	/**
	 *
	 */
	protected function entries()
	{
		$sid = Input::Sid();
		$site = Input::Int( 'site', 'get', 1 );
		if ( $sid == Sobi::Section() ) {
			if ( !( Sobi::Can( 'section', 'access', 'valid', Sobi::Section() ) ) ) {
				throw new Exception( 'Unauthorised Access', 403 );
			}
			$category = SPFactory::Section( $sid );
		}
		else {
			/** @todo
			 * Check if it's a valid category
			 */
			$category = SPFactory::Category( $sid );
		}
		if ( $category->get( 'oType' ) != 'category' && $category->get( 'oType' ) != 'section' || !( $category->get( 'id' ) ) ) {
			throw new Exception( 'Wrong object type', 404 );
		}

		$entries = $category->getChilds( 'entry', true, 1 );
		$count = count( $entries );
		if ( count( $entries ) > self::ENTRIES_LIMIT ) {
			$from = $site == 1 ? $site - 1 : $site - 1 * self::ENTRIES_LIMIT;
			$entries = array_slice( $entries, $from, self::ENTRIES_LIMIT );
		}
		$data = [];
		foreach ( $entries as $sid ) {
			$entry = SPFactory::Entry( $sid );
			$fields = $entry->getFields();
			$fieldData = [];
			if ( count( $fields ) ) {
				foreach ( $fields as $field ) {
					$fieldData[] = [
							'name' => $field->get( 'name' ),
							'nid' => $field->get( 'nid' ),
							'data' => method_exists( $field, 'api' ) ? $field->api() : $field->data()
					];
				}
			}
			$data[] = [
					'sid' => $sid,
					'name' => $entry->get( 'name' ),
					'fields' => $fieldData
			];
		}
		$this->answer( $data, 0, [ 'count' => $count, 'limit' => self::ENTRIES_LIMIT, 'sites' => ceil( $count / self::ENTRIES_LIMIT ) ] );
	}

	/**
	 *
	 */
	protected function entry()
	{
		$sid = Input::Sid();
		$entry = SPFactory::Entry( $sid );
		if ( !( Sobi::Can( 'entry', 'access', 'valid', $sid ) ) ) {
			/** @todo
			 * Check if it's a valid entry
			 */
		}
		if ( $entry->get( 'oType' ) != 'entry' || !( $entry->get( 'id' ) ) ) {
			throw new Exception( 'Wrong object type', 404 );
		}
		$entry->loadFields( Sobi::Section() );
		$fields = $entry->getFields();

		$data = [
				'id' => $sid,
				'name' => $entry->get( 'name' ),
				'nid' => $entry->get( 'nid' ),
				'type' => $entry->get( 'oType' ),
				'description' => $entry->get( 'description' ),
				'meta-keys' => $entry->get( 'metaKeys' ),
				'meta-description' => $entry->get( 'metaDesc' ),
				'meta-author' => $entry->get( 'metaAuthor' ),
				'created-time' => $entry->get( 'createdTime' ),
				'last-updated' => $entry->get( 'updatedTime' ),
		];
		$data[ 'fields' ] = [];
		$data = $this->travelFields( $fields, $data );
		$this->answer( $data );
	}

	/**
	 *
	 */
	protected function category()
	{
		$sid = Input::Sid();
		$fields = [];
		if ( $sid == Sobi::Section() ) {
			if ( !( Sobi::Can( 'section', 'access', 'valid', Sobi::Section() ) ) ) {
				throw new Exception( 'Unauthorised Access', 403 );
			}
			$category = SPFactory::Section( $sid );
		}
		else {
			$category = SPFactory::Category( $sid );
			if ( !( Sobi::Can( 'category', 'access', 'valid', $sid ) ) ) {
				/** @todo
				 * Check if it's a valid category
				 */
//				throw new Exception( 'Unauthorised Access' );
			}
			$category->loadFields( Sobi::Section() );
			$fields = $category->getFields();
		}
		if ( $category->get( 'oType' ) != 'category' && $category->get( 'oType' ) != 'section' || !( $category->get( 'id' ) ) ) {
			throw new Exception( 'Wrong object type', 404 );
		}
		$childs = $category->getChilds( 'category', false, true, true );
		$data = [
				'id' => $sid,
				'name' => $category->get( 'name' ),
				'nid' => $category->get( 'nid' ),
				'type' => $category->get( 'oType' ),
				'description' => $category->get( 'description' ),
				'icon' => SPFactory::config()->structuralData( 'json://' . $category->get( 'icon' ) ),
				'meta-keys' => $category->get( 'metaKeys' ),
				'meta-description' => $category->get( 'metaDesc' ),
				'meta-author' => $category->get( 'metaAuthor' ),
				'created-time' => $category->get( 'createdTime' ),
				'last-updated' => $category->get( 'updatedTime' ),
		];
		$data[ 'fields' ] = [];
		$data = $this->travelFields( $fields, $data[ 'fields' ] );
		$data[ 'childs' ] = [];
		if ( count( $childs ) ) {
			foreach ( $childs as $cid => $category ) {
				$data[ 'childs' ][] = [
						'id' => $cid,
						'name' => $category[ 'name' ],
						'nid' => $category[ 'alias' ]
				];
			}
		}
		$this->answer( $data );
	}

	/**
	 *
	 */
	protected function sections()
	{
		$data = [];
		$sections = [];
		try {
			$sections = SPFactory::db()
					->select( 'id', 'spdb_object', [ 'oType' => 'section' ], 'id' )
					->loadResultArray();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		if ( count( $sections ) ) {
			$sections = SPLang::translateObject( $sections, 'name' );
			$data = [];
			foreach ( $sections as $section ) {
				if ( Sobi::Can( 'section', 'access', 'valid', $section[ 'id' ] ) ) {
					$data[] = [
							'id' => $section[ 'id' ],
							'name' => StringUtils::Clean( $section[ 'value' ] ),
					];
				}
			}
		}
		$this->answer( $data );
	}

	/**
	 * @param array $data
	 * @param int $code
	 * @param array $header
	 *
	 *
	 * @since version
	 */
	protected function answer( $data, $code = 0, $header = [] )
	{
		Sobi::Trigger( 'Api', ucfirst( $this->_task ), [ &$data ] );
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader( 'application/json', $code );
		exit( json_encode( [ 'header' => $header, 'data' => $data ], JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_LINE_TERMINATORS ) );
	}

	/**
	 * @param array $fields
	 * @param array $data
	 */
	protected function travelFields( $fields, $data )
	{
		if ( count( $fields ) ) {
			foreach ( $fields as $field ) {
				$data[ 'fields' ][] = [
						'type' => $field->get( 'fieldType' ),
						'nid' => $field->get( 'nid' ),
						'id' => $field->get( 'id' ),
						'name' => $field->get( 'name' ),
						'data' => $field->data(),
						'raw-data' => $field->getRaw(),
						'description' => $field->get( 'description' ),
						'default-value' => $field->get( 'defaultValue' ),
						'priority' => $field->get( 'priority' ),
						'position' => $field->get( 'position' ),
				];
			}
		}
		$this->answer( $data );
	}
}

