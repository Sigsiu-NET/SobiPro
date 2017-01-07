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
SPLoader::loadModel( 'entry' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:14:27 PM
 */
class SPEntryAdm extends SPEntry implements SPDataModel
{
	/**
	 * @var bool
	 */
	private $_loaded = false;

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
		return $nameField ? $nameField : Sobi::Cfg( 'entry.name_field' );
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
		$db =& SPFactory::db();

		static $fields = [];
		static $lang = null;
		$lang = $lang ? $lang : Sobi::Lang( false );
		if ( !isset( $fields[ $sid ] ) ) {
			/* get fields */
			try {
				$fields[ $sid ] = $db
						->select( '*', 'spdb_field', [ 'section' => $sid, $db->argsOr( [ 'admList' => 1, 'fid' => $this->nameField() ] ) ], 'position' )
						->loadObjectList();
				Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), [ &$fields ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		$nameField = $this->nameField();

		if ( !( $this->_loaded ) ) {
			if ( count( $fields[ $sid ] ) ) {
				/* if it is an entry - prefetch the basic fields data */
				if ( $this->id ) {
					$noCopy = $this->checkCopy();
					/* in case the entry is approved, or we are aditing an entry, or the user can see unapproved changes */
					if ( $this->approved || $noCopy ) {
						$ordering = 'copy.desc';
					}
					/* otherweise - if the entry is not approved, get the non-copies first */
					else {
						$ordering = 'copy.asc';
					}
					try {
						$db->select( '*', 'spdb_field_data', [ 'sid' => $this->id ], $ordering );
						$fdata = $db->loadObjectList();
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
					$field->loadData( $this->id );
					$this->fields[ ] = $field;
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
		if ( !( strlen( $this->name ) ) ) {
			$this->name = Sobi::Txt( 'ENTRY_NO_NAME' );
			// well yeah - screw the pattern :-/
			SPFactory::message()
					->warning( 'ENTRIES_BASE_DATA_INCOMPLETE' )
					->setSystemMessage();
			$this->valid = false;
		}
	}

	private function checkCopy()
	{
		return !(
				in_array( SPRequest::task(), [ 'entry.approve', 'entry.edit', 'entry.save', 'entry.submit' ] ) ||
						Sobi::Can( 'entry.see.unapproved_any' ) ||
						( $this->owner == Sobi::My( 'id' ) && Sobi::Can( 'entry.manage.own' ) ) ||
						Sobi::Can( 'entry.manage.*' )
		);
	}
}
