<?php
/**
 * @version: $Id: url.php 2330 2012-03-27 19:49:40Z Radek Suski $
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
 * $Date: 2012-03-27 21:49:40 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2330 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/opt/fields/url.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.inbox' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Sep-2009 11:36:48
 */
class SPField_Url extends SPField_Inbox implements SPFieldInterface
{
	/**
	 * @var bool
	 */
	protected $ownLabel =  true;
	/**
	 * @var int
	 */
	protected $labelWidth =  350;
	/**
	 * @var string
	 */
	protected $labelsLabel = "Website Title";
	/**
	 * @var int
	 */
	protected $labelMaxLength =  150;
	/**
	 * @var int
	 */
	protected $maxLength =  150;
	/**
	 * @var int
	 */
	protected $width =  350;
	/**
	 * @var string
	 */
	protected $cssClass = "";
	/**
	 * @var bool
	 */
	protected $validateUrl =  false;
	/**
	 * @var array
	 */
	protected $allowedProtocols =  array( 'http', 'https', 'ftp' );
	/**
	 * @var bool
	 */
	protected $newWindow =  true;
    /**
   	 * @var string
   	 */
   	protected $dType = 'special';

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
		$field = null;
		$fdata = Sobi::Reg( 'editcache' );
		if( $fdata && is_array( $fdata ) ) {
			$raw = $this->fromCache( $fdata );
		}
		else {
			$raw = SPConfig::unserialize( $this->getRaw() );
		}
		if( $this->ownLabel ) {
			$fieldTitle = null;
			$params = array( 'id' => $this->nid, 'size' => $this->labelWidth, 'class' => $this->cssClass.'Title' );
			if( $this->labelMaxLength ) {
				$params[ 'maxlength' ] = $this->labelMaxLength;
			}
			if( $this->labelWidth ) {
				$params[ 'style' ] = "width: {$this->labelWidth}px;";
			}
			if( strlen( $this->labelsLabel ) ) {
				$this->labelsLabel = SPLang::clean( $this->labelsLabel );
				$fieldTitle .= "<label for=\"{$this->nid}\" class=\"{$this->cssClass}Title\">{$this->labelsLabel}</label>\n";
			}
			$fieldTitle .= SPHtml_Input::text( $this->nid, ( ( is_array( $raw ) && isset( $raw[ 'label' ] ) ) ? SPLang::clean( $raw[ 'label' ] ) : null ), $params );
		}
		$protocols = array();
		if( count( $this->allowedProtocols ) ) {
			foreach ( $this->allowedProtocols as $protocol ) {
				$protocols[ $protocol ] = $protocol;
			}
		}
		else {
			$protocols = array( 'http' => 'http', 'https' => 'https' );
		}
		$params = array( 'id' => $this->nid.'_protocol', 'size' => 1, 'class' => $this->cssClass.'Protocol' );
		$field .= SPHtml_Input::select( $this->nid.'_protocol', $protocols, ( ( is_array( $raw ) && isset( $raw[ 'protocol' ] ) ) ? $raw[ 'protocol' ] : 'http' ), false, $params );
		$field .= '<span class="spFieldUrlProtocol">://</span>';
		$class =  $this->required ? $this->cssClass.' required' : $this->cssClass;
		$this->nid .= '_url';
		$params = array( 'id' => $this->nid, 'size' => $this->width, 'class' => $class );
		if( $this->maxLength ) {
			$params[ 'maxlength' ] = $this->maxLength;
		}
		if( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}
		$field .= SPHtml_Input::text( $this->nid, ( ( is_array( $raw ) && isset( $raw[ 'url' ] ) ) ? $raw[ 'url' ] : null ), $params );

		if( $this->ownLabel ) {
			$field = "\n<div class=\"spFieldUrlLabel\">{$fieldTitle}</div>\n<div class=\"spFieldUrl\">{$field}</div>";
		}
		if( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	private function fromCache( $cache )
	{
		$data = array();
		if( isset( $cache ) && isset( $cache[ $this->nid ] ) ) {
			$data[ 'label' ] = $cache[ $this->nid ];
		}
		if( isset( $cache ) && isset( $cache[ $this->nid.'_url' ] ) ) {
			$data[ 'url' ] = $cache[ $this->nid.'_url' ];
		}
		if( isset( $cache ) && isset( $cache[ $this->nid.'_protocol' ] ) ) {
			$data[ 'protocol' ] = $cache[ $this->nid.'_protocol' ];
		}
		return $data;
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return array( 'ownLabel', 'labelWidth', 'labelMaxLength', 'labelsLabel', 'validateUrl', 'allowedProtocols', 'newWindow', 'maxLength', 'width' );
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$data = SPConfig::unserialize( $this->getRaw() );
		if( isset( $data[ 'url' ] ) && strlen( $data[ 'url' ] ) ) {
			if( $data[ 'protocol' ] == 'relative' ) {
				$url = $data[ 'url' ];
			}
			else {
				$url = $data[ 'protocol' ].'://'.$data[ 'url' ];
			}
			if( !( isset( $data[ 'label' ] ) && strlen( $data[ 'label' ] )  ) ) {
				$data[ 'label' ] = $url;
			}
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
			$this->cssClass = $this->cssClass.' '.$this->nid;
			$this->cleanCss();
			if( strlen( $url ) ) {
				$attributes = array( 'href' => $url, 'class' => $this->cssClass);
				if( $this->newWindow ) {
					$attributes[ 'target' ] = '_blank';
				}
				$data = array(
					'_complex' => 1,
					'_data' => SPLang::clean( $data[ 'label' ] ),
					'_attributes' => $attributes
				);
				return array (
					'_complex' => 1,
					'_data' => array( 'a' => $data ),
					'_attributes' => array( 'lang' => Sobi::Lang(false), 'class' => $this->cssClass )
				);
			}
		}
	}

	public function cleanData( $html )
	{
		$data = SPConfig::unserialize( $this->getRaw() );
		$url = null;
		if( isset( $data[ 'url' ] ) && strlen( $data[ 'url' ] ) ) {
			if( $data[ 'protocol' ] == 'relative' ) {
				$url = Sobi::Cfg( 'live_site' ).$data[ 'url' ];
			}
			else {
				$url = $data[ 'protocol' ].'://'.$data[ 'url' ];
			}
		}
		return $url;
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsid
	 * @param string $request
	 * @return array
	 */
	public function submit( &$entry, $tsid = null, $request = 'POST' )
	{
		if( count( $this->verify( $entry, SPFactory::db(), $request ) ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return array();
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param SPdb $db
	 * @param string $request
	 * @return array
	 */
	private function verify( $entry, &$db, $request )
	{
		$save = array();
		if( $this->ownLabel ) {
			$save[ 'label' ] = SPRequest::raw( $this->nid, null, $request );
			/* check if there was a filter */
			if( $this->filter && strlen( $save[ 'label' ] ) ) {
				$registry =& SPFactory::registry();
				$registry->loadDBSection( 'fields_filter' );
				$filters = $registry->get( 'fields_filter' );
				$filter = isset( $filters[ $this->filter ] ) ? $filters[ $this->filter ] : null;
				if( !( count( $filter ) ) ) {
					throw new SPException( SPLang::e( 'FIELD_FILTER_ERR', $this->filter ) );
				}
				else {
					if( !( preg_match( base64_decode( $filter[ 'params' ] ), $save[ 'label' ] ) ) ) {
						throw new SPException( str_replace( '$field', $this->name, SPLang::e( $filter[ 'description' ] ) ) );
					}
				}
			}
		}
		$data = SPRequest::raw( $this->nid.'_url', null, $request );
		$save[ 'protocol' ] = $db->escape( SPRequest::word( $this->nid.'_protocol', $request ) );
		$dexs = strlen( $data );
		$data = $db->escape( $data );
		$data = preg_replace( '/([a-z]{1,5}\:\/\/)/i', null, $data );
		$save[ 'url' ] = $data;
		/* check if it was required */
		if( $this->required && !( $dexs ) ) {
			throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
		}
		/* check if there was an adminField */
		if( $this->adminField && $dexs ) {
			if( !( Sobi:: Can( 'entry.adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->name ) );
			}
		}
		/* check if it was free */
		if( !( $this->isFree ) && $this->fee && $dexs ) {
			SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
		}
		/* check if it should contains unique data */
		if( $this->uniqueData && $dexs ) {
			$matches = $this->searchData( $data,Sobi::Reg( 'current_section' ) );
			if( count ( $matches ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_UNIQUE', $this->name ) );
			}
		}
		/* check if it was editLimit */
		if( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
		}
		/* check if it was editable */
		if( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs && $entry->get( 'version' ) > 1 ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
		}

		/* check the response code */
		if( $dexs && $this->validateUrl ) {
			$rclass = SPLoader::loadClass( 'services.remote' );
			$err = 0;
			$response = 0;
			try {
				$connection = new $rclass();
				$connection->setOptions(
					array(
						'url' => $save[ 'protocol' ].'://'.$data,
						'connecttimeout' => 10,
						'header' => false,
						'returntransfer' => true
					)
				);
				$connection->exec();
				$response = $connection->info( 'response_code' );
				$err = $connection->error( false );
				$errTxt = $connection->error();
                $connection->close();
				if( $err ) {
					Sobi::Error( $this->name(), SPLang::e( 'FIELD_URL_CANNOT_VALIDATE', $errTxt ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
			catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'FIELD_URL_CANNOT_VALIDATE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			if( $err || ( $response != 200 ) ) {
				$response = $err ? $errTxt : $response;
				Sobi::Error( $this->name(), SPLang::e( 'FIELD_URL_ERR', $save[ 'protocol' ].'://'.$data, $response ), SPC::WARNING, 0, __LINE__, __FILE__ );
                throw new SPException( SPLang::e( 'FIELD_URL_ERR', $save[ 'protocol' ].'://'.$data, $response ) );
			}
		}
		if( !( $dexs ) ) {
			$save = null;
		}
		return $save;
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

		/* @var SPdb $db */
		$db =& SPFactory::db();
		$save = $this->verify( $entry, $db, $request );

		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */
		/* collect the needed params */
		$params = array();
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
		$params[ 'baseData' ] = $db->escape( SPConfig::serialize( $save ) );
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
			/* Notices:
			 * If it was new entry - insert
			 * If it was an edit and the field wasn't filled before - insert
			 * If it was an edit and the field was filled before - update
			 *     " ... " and changes are not autopublish it should be insert of the copy .... but
			 * " ... " if a copy already exist it is update again
			 * */
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
}
