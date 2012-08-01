<?php
/**
 * @version: $Id: registry.php 2608 2012-07-16 10:31:30Z Radek Suski $
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
 * $Date: 2012-07-16 12:31:30 +0200 (Mon, 16 Jul 2012) $
 * $Revision: 2608 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/registry.php $
 */
defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
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
	private $store = array( array() );

	/**
	 * Storing variable
	 *
	 * @param string $label
	 * @param mixed $object
	 */
	public function set ( $label, &$object )
	{
		$this->store[ 0 ][ $label ] =& $object;
	}

	/**
	 * Deleting stored variabel
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
	 * @return void
	 */
	public function & loadDBSection( $section )
	{
		static $loaded = array();
		if( !( in_array( $section, $loaded ) ) ) {
			/* @var Spdb $db */
			$db =& SPFactory::db();
			try {
				$db->select( '*', 'spdb_registry', array( 'section' => $section ), 'value' );
				$keys = $db->loadObjectList();
			}
			catch ( SPException $x ) {
				Sobi::Error( __FUNCTION__, SPLang::e( 'Cannot load registry section. Db reports %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			if( count( $keys ) ) {
				foreach ( $keys as $section ) {
					$this->store[ 0 ][ $section->section ][ $section->key ] = array( 'value' => $section->value, 'params' => $section->params, 'description' => $section->description, 'options' => $section->options );
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
		$db =& SPFactory::db();
		foreach ( $values as $i => $value ) {
			$value[ 'section' ] = $section;
			$value[ 'params' ] = isset( $value[ 'params' ] ) ? $value[ 'params' ] : null;
			$value[ 'description' ] = isset( $value[ 'description' ] ) ? $value[ 'description' ] : null;
			$value[ 'options' ] = isset( $value[ 'options' ] ) ? $value[ 'options' ] : null;
			$values[ $i ] = $value;
		}
		Sobi::Trigger( 'Registry', 'SaveDb', array( &$values ) );
		try {
			$db->delete( 'spdb_registry', array( 'section' => $section ) );
			$db->insertArray( 'spdb_registry', $values );
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
		array_unshift( $this->store, array() );
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
