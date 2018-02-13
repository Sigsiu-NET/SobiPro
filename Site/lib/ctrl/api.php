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
	const ENTRIES_LIMIT = 5;

	public function execute()
	{
		$data = null;
		switch ( $this->_task ) {
			case 'sections':
				$data = $this->sections();
				break;
			case 'category':
				$data = $this->category();
				break;
			case 'entries':
				$data = $this->entries();
				break;
		}
		/** @var TYPE_NAME $this */
		$this->answer( $data );
	}

	protected function entries()
	{
		$sid = Input::Sid();
		$site = Input::Int( 'site', 'get', 1 );
		if ( $sid == Sobi::Section() ) {
			if ( !( Sobi::Can( 'section', 'access', 'valid', Sobi::Section() ) ) ) {
				throw new Exception( 'Unauthorised Access' );
			}
			$category = SPFactory::Section( $sid );
		}
		else {
			/** @todo
			 * Check if it's a valid category
			 */
			$category = SPFactory::Category( $sid );
		}
		$entries = $category->getChilds( 'entry', true, 1 );
		if ( count( $entries ) > self::ENTRIES_LIMIT ) {
			$from = $site == 1 ? $site - 1 : $site - 1 * self::ENTRIES_LIMIT;
			$entries = array_slice( $entries, $from, self::ENTRIES_LIMIT );
		}
		echo '';
	}

	protected function category()
	{
		$sid = Input::Sid();
		$fields = [];
		if ( $sid == Sobi::Section() ) {
			if ( !( Sobi::Can( 'section', 'access', 'valid', Sobi::Section() ) ) ) {
				throw new Exception( 'Unauthorised Access' );
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
		return $data;
	}

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
		return $data;
	}

	protected function answer( $data, $code = 0 )
	{
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader( 'application/json', $code );
		exit( json_encode( $data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_LINE_TERMINATORS ) );
	}

	protected function error()
	{

	}
}

