<?php
/**
 * @version: $Id: textarea.php 2294 2012-03-12 12:15:27Z Radek Suski $
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
 * $Date: 2012-03-12 13:15:27 +0100 (Mon, 12 Mar 2012) $
 * $Revision: 2294 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/fields/textarea.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.inbox' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Sep-2009 12:52:45 PM
 */
class SPField_Textarea extends SPField_Inbox implements SPFieldInterface
{
	/**
	 * @var int
	 */
	protected $maxLength =  0;
	/**
	 * @var int
	 */
	protected $width = 550;
	/**
	 * @var int
	 */
	protected $height = 100;
	/**
	 * @var string
	 */
	protected $cssClass = "";
	/**
	 * @var bool
	 */
	protected $editor = false;
	/**
	 * @var bool
	 */
	protected $allowHtml = true;

	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		if( !( $this->enabled ) ) {
			return false;
		}
		$class =  $this->required ? $this->cssClass.' required' : $this->cssClass;

		if( $this->maxLength ) {
			if( !( $this->editor ) ) {
				SPFactory::header()->addJsCode( "SobiPro.onReady( function ()
				{
					function SPtxtLimit()
					{
						if( SP_id( '{$this->nid}' ).value.length > {$this->maxLength} ) {
							alert( SobiPro.Txt( 'FD_TEXTAREA_LIMIT' ).replace( 'var:[max_length]', '{$this->maxLength}' ) );
							SP_id( '{$this->nid}' ).value = SP_id( '{$this->nid}' ).value.substr( 0, $this->maxLength );
						}
					}
					try {
						SP_id( '{$this->nid}' ).addEventListener( 'keypress', SPtxtLimit, false ); }
					catch ( e ) {
						SP_id( '{$this->nid}' ).attachEvent( 'keypress', SPtxtLimit );
					}
				});" );
			}
		}
		$params = array( 'id' => $this->nid, 'class' => $class );
		if( $this->maxLength ) {
			$params[ 'maxlength' ] = $this->maxLength;
		}
		$field = SPHtml_Input::textarea( $this->nid, $this->getRaw(), $this->editor, $this->width, $this->height, $params );
		if( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$data = $this->data();
		$attributes = array();
		if( strlen( $data ) ) {
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
			$this->cssClass = $this->cssClass.' '.$this->nid;
			$this->cleanCss();
			$attributes = array(
				'lang' => Sobi::Lang(),
				'class' => $this->cssClass
			);
		}
		else {
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spField';
		}
		if( !( $this->editor || $this->allowHtml ) ) {
			$data = nl2br( $data );
		}
		return array(
			'_complex' => 1,
			'_data' => $data,
			'_attributes' => $attributes
		);
}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return string
	 */
	private function verify( $entry, $request )
	{
		$data = SPRequest::raw( $this->nid, null, $request );
		$dexs = strlen( $data );
		/* check if it was required */
		if( $this->required && !( $dexs ) ) {
			throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
		}
		if( $dexs ) {
			/* check if there was an adminField */
			if( $this->adminField ) {
				if( !( Sobi:: Can( 'entry.adm_fields.edit' ) ) ) {
					throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->get( 'name' ) ) );
				}
			}
			/* check if it was free */
			if( !( $this->isFree ) && $this->fee ) {
				SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
			}
			/* check if it was editLimit */
			if( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
			}
			/* check if it was editable */
			if( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $entry->get( 'version' ) > 1 ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
			}
		}
		$data = SPRequest::string( $this->nid, null, true, $request );
		$this->setData( $data );
		return $data;
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsid
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsid = null, $request = 'POST' )
	{
		$data = $this->verify( $entry, $request );
		if( strlen( $data ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return array();
		}
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return array( 'maxLength', 'width', 'height', 'editor', 'allowHtml' );
	}

	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' )
	{
		if( !( $this->enabled ) ) {
			return false;
		}

		$data = $this->verify( $entry, $request );
		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */
		/* @var SPdb $db */
		$db =& SPFactory::db();

		if( $this->allowHtml ) {
			/* filter data */
			if( count( $this->allowedAttributes ) ) {
				SPRequest::setAttributesAllowed( $this->allowedAttributes );
			}
			if( count( $this->allowedTags ) ) {
				SPRequest::setTagsAllowed( $this->allowedTags );
			}
			$data = SPRequest::string( $this->nid, null, $this->allowHtml, $request );
			SPRequest::resetFilter();
			if( !( $this->editor ) && $this->maxLength && ( strlen( $data ) > $this->maxLength ) ) {
				$data = substr( $data, 0, $this->maxLength );
			}
		}
		else {
			$data = strip_tags( $data );
		}

		/* collect the needed params */
		$params = array();
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
		$params[ 'params' ] = null;
		$params[ 'options' ] = null;
		$params[ 'baseData' ] = $data;
		$params[ 'approved' ] = $entry->get( 'approved' );
		$params[ 'confirmed' ] = $entry->get( 'confirmed' );
		/* if it is the first version, it is new entry */
		if( $entry->get( 'version' ) == 1 ) {
			$params[ 'createdTime' ] = $time;
			$params[ 'createdBy' ] = $uid;
			$params[ 'createdIP' ] = $IP;
		}
		$params[ 'updatedTime' ] = $time;
		$params[ 'updatedBy' ] = $uid;
		$params[ 'updatedIP' ] = $IP;
		$params[ 'copy' ] = !( $entry->get( 'approved' ) );
		if( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) {
			--$this->editLimit;
		}
		$params[ 'editLimit' ] = $this->editLimit;

		/* save it */
		try {
			$db->insertUpdate( 'spdb_field_data', $params );
		}
		catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		/* if it wasn't edited in the default language, we have to try to insert it also for def lang */
		if( Sobi::Lang() != Sobi::DefLang() ) {
			$params[ 'lang' ] = Sobi::DefLang();
			try {
				$db->insert( 'spdb_field_data', $params, true, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
	}

	/**
	 * @return bool
	 */
	public function searchData( $request, $section )
	{
		return false;
	}
}
