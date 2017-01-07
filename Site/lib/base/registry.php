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
/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:24:59 PM
 */
final class SPRegistry
{
	/**
	 * @var array
	 */
	private $store = [ [] ];

	/**
	 * Storing variable
	 *
	 * @param string $label
	 * @param mixed $object
	 * @return $this
	 */
	public function & set ( $label, &$object )
	{
		$this->store[ 0 ][ $label ] =& $object;
		return $this;
	}

	/**
	 * Deleting stored variable
	 *
	 * @param string $label
	 */
	public function __unset ( $label )
	{
		if ( isset( $this->store[ 0 ][ $label ] ) ) {
			unset( $this->store[ 0 ][ $label ] );
		}
		else {
			Sobi::Error( 'registry', SPLang::e( 'ENTRY_DOES_NOT_EXIST', $label ), SPC::NOTICE, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * Returns copy of stored object
	 *
	 * @param string $label
	 * @param mixed $default
	 * @return mixed
	 */
	public function get ( $label, $default = null )
	{
		if( strstr( $label, '.' ) ) {
			return $this->parseVal( $label );
		}
		else {
			return isset( $this->store[ 0 ][ $label ] ) ? $this->store[ 0 ][ $label ] : $default;
		}
	}

	/**
	 * Returns reference to a stored object
	 *
	 * @param string $label
	 * @return mixed
	 */
	public function & __get ( $label )
	{
		return $this->store[ 0 ][ $label ];
	}

	/**
	 * checking if variable is already stored
	 *
	 * @param string $label
	 * @return bool
	 */
	public function __isset ( $label )
	{
		return isset( $this->store[ 0 ][ $label ] ) ? true : false;
	}

	/**
	 * Restoring saved registry
	 *
	 */
	public function restore ()
	{
		array_shift( $this->store );
	}

	/**
	 * @param $label
	 * @param null $default
	 * @return mixed|null
	 */
	private function parseVal ( $label, $default = null )
	{
		$label = explode( '.', $label );
		$var =& $this->store[ 0 ];
		foreach ( $label as $part ) {
			if( isset( $var[ $part ] ) ) {
				$var =& $var[ $part ];
			}
			else {
				return $default;
			}
		}
		return $var;
	}

	/**
	 * @param string $section
	 * @return SPRegistry
	 */
	public function & loadDBSection( $section )
	{
		static $loaded = [];
		if( !( in_array( $section, $loaded ) ) ) {
			try {
				$keys = SPFactory::db()
						->select( '*', 'spdb_registry', [ 'section' => $section ], 'value' )
						->loadObjectList();
			}
			catch ( SPException $x ) {
				Sobi::Error( __FUNCTION__, SPLang::e( 'Cannot load registry section. Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			if( count( $keys ) ) {
				foreach ( $keys as $section ) {
					$this->store[ 0 ][ $section->section ][ $section->key ] = [ 'value' => $section->value, 'params' => $section->params, 'description' => $section->description, 'options' => $section->options ];
				}
			}
			$loaded[] = $section;
		}
		return $this;
	}


	/**
	 * Saves whole section in the db registry
	 * @param array $values
	 * @param string $section
	 * @return void
	 */
	public function saveDBSection( $values, $section )
	{
		foreach ( $values as $i => $value ) {
			$value[ 'section' ] = $section;
			$value[ 'params' ] = isset( $value[ 'params' ] ) ? $value[ 'params' ] : null;
			$value[ 'description' ] = isset( $value[ 'description' ] ) ? $value[ 'description' ] : null;
			$value[ 'options' ] = isset( $value[ 'options' ] ) ? $value[ 'options' ] : null;
			$values[ $i ] = $value;
		}
		Sobi::Trigger( 'Registry', 'SaveDb', [ &$values ] );
		try {
			SPFactory::db()->delete( 'spdb_registry', [ 'section' => $section ] );
			SPFactory::db()->insertArray( 'spdb_registry', $values );
		}
		catch ( SPException $x ) {
			Sobi::Error( __FUNCTION__, SPLang::e( 'Cannot save registry section. Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * saving copy of current registry state
	 *
	 */
	public function save ()
	{
		array_unshift( $this->store, [] );
		if ( ! count( $this->store ) ) {
			Sobi::Error( 'registry', SPLang::e( 'Registry lost' ), SPC::NOTICE, 0, __LINE__, __CLASS__ );
		}
	}

	/**
	 * Singleton
	 *
	 * @return SPRegistry
	 */
	public static function & getInstance ()
	{
		static $registry = null;
		if ( ! $registry || ! ( $registry instanceof SPRegistry ) ) {
			$registry = new SPRegistry( );
		}
		return $registry;
	}
}
