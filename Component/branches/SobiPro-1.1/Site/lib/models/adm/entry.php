<?php
/**
 * @version: $Id: entry.php 2086 2011-12-21 12:25:44Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-12-21 13:25:44 +0100 (Wed, 21 Dec 2011) $
 * $Revision: 2086 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/models/entry.php $
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
		if( $this->section == Sobi::Section() || !( $this->section ) ) {
			$nameField = Sobi::Cfg( 'entry.name_field' );
		}
		else {
			$nameField = SPFactory::db()
				->select( 'sValue', 'spdb_config', array( 'section' => $this->section, 'sKey' => 'name_field', 'cSection' => 'entry' ) )
				->loadResult();
		}
		return $nameField;
	}

	/**
	 * @param int $sid
	 * @return void
	 */
	public function loadFields( $sid = 0, $enabled = false )
	{
		$sid = $sid ? $sid : $this->section;
		/* @var SPdb $db */
		$db =& SPFactory::db();

		static $fields = array();
		static $lang = null;
		$lang = $lang ? $lang : Sobi::Lang( false );
		if( !isset( $fields[ $sid ] ) ) {
			/* get fields */
	        try {
	        	$fields[ $sid ] = $db
                        ->select( '*', 'spdb_field', array( 'section' => $sid, $db->argsOr( array( 'admList' => 1, 'fid' => $this->nameField() ) ) ), 'position' )
                        ->loadObjectList();
	        	Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$fields ) );
	        }
	        catch ( SPException $x ) {
	        	Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
	        }
		}
		$nameField = $this->nameField();

		if( !( $this->_loaded ) ) {
	        if( count( $fields[ $sid ] ) ) {
	        	$fmod = SPLoader::loadModel( 'field', defined( 'SOBIPRO_ADM' ) );
	        	/* if it is an entry - prefetch the basic fields data */
	        	if( $this->id ) {
					$noCopy = $this->checkCopy();
	        		/* in case the entry is approved, or we are aditing an entry, or the user can see unapproved changes */
	        		if( $this->approved || $noCopy ) {
	        			$ordering = 'copy.desc';
	        		}
	        		/* otherweise - if the entry is not approved, get the non-copies first */
	        		else {
	        			$ordering = 'copy.asc';
	        		}
			        try {
			        	$db->select( '*', 'spdb_field_data', array( 'sid' => $this->id ), $ordering );
			        	$fdata = $db->loadObjectList();
			        	$fieldsdata = array();
						if( count( $fdata ) ) {
			        		foreach ( $fdata as $data ) {
			        			/* if it has been already set - check if it is not better language choose */
			        			if( isset( $fieldsdata[ $data->fid ] ) ) {
			        				/*
			        				 * I know - the whole thing could be shorter
			        				 * but it is better to understand and debug this way
			        				 */
			        				if( $data->lang == $lang ) {
			        					if( $noCopy ) {
			        						if( !( $data->copy ) ) {
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
			        				if( $noCopy ) {
			        					if( !( $data->copy ) ) {
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
			        	SPFactory::registry()->set( 'fields_data_'.$this->id, $fieldsdata );
			        }
			        catch ( SPException $x ) {
			        	Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			        }
	        	}
	        	foreach ( $fields[ $sid ] as $f ) {
	        		/* @var SPField $field */
	        		$field = new $fmod();
	        		$field->extend( $f );
	        		$field->loadData( $this->id );
	        		$this->fields[] = $field;
					$this->fieldsNids[ $field->get( 'nid' ) ] = $this->fields[ count( $this->fields ) -1 ];
					$this->fieldsIds[ $field->get( 'fid' ) ] = $this->fields[ count( $this->fields ) -1 ];
	        		/* case it was the name field */
	        		if( $field->get( 'fid' ) == $nameField ) {
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
			in_array( SPRequest::task(), array( 'entry.approve', 'entry.edit', 'entry.save', 'entry.submit' ) ) ||
		    Sobi::Can( 'entry.see.unapproved_any' ) ||
		    ( $this->owner == Sobi::My( 'id' ) && Sobi::Can( 'entry.manage.own' ) ) ||
		    Sobi::Can( 'entry.manage.*' )
		);
	}
}
?>
