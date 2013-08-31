<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.inbox' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Nov-2009 20:06:23
 */
class SPField_Image extends SPField_Inbox implements SPFieldInterface
{
	/**
	 * @var bool
	 */
	protected $keepOrg = true;
	/**
	 * @var bool
	 */
	protected $resize = true;
	/**
	 * @var double
	 */
	protected $maxSize = 2097152;
	/**
	 * @var int
	 */
	protected $resizeWidth = 500;
	/**
	 * @var int
	 */
	protected $resizeHeight = 500;
	/**
	 * @var string
	 */
	protected $imageName = 'img_{orgname}';
	/**
	 * @var string
	 */
	protected $imageFloat = '';
	/**
	 * @var bool
	 */
	protected $generateThumb = true;
	/**
	 * @var string
	 */
	protected $thumbFloat = '';
	/**
	 * @var string
	 */
	protected $thumbName = 'thumb_{orgname}';
	/**
	 * @var int
	 */
	protected $thumbWidth = 200;
	/**
	 * @var int
	 */
	protected $thumbHeight = 200;
	/**
	 * @var string
	 */
	protected $inVcard = 'thumb';
	/**
	 * @var string
	 */
	protected $inDetails = 'image';
	/**
	 * @var string
	 */
	protected $savePath = 'images/sobipro/entries/{id}/';
	/**
	 * @var string
	 */
	protected $cssClass = "";
	/**
	 * @var string
	 */
	protected $dType = 'special';

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return array( 'width', 'savePath', 'inDetails', 'inVcard', 'thumbHeight', 'thumbWidth', 'thumbName', 'keepOrg', 'resize', 'maxSize', 'resizeWidth', 'resizeHeight', 'imageName', 'generateThumb', 'thumbFloat', 'imageFloat' );
	}

	public function compareRevisions( $revision, $current )
	{
		if ( isset( $revision[ 'image' ] ) ) {
			$rev = basename( $revision[ 'image' ] );
		}
		if ( isset( $current[ 'image' ] ) ) {
			$cur = basename( $current[ 'image' ] );
		}
		return array( 'current' => $cur, 'revision' => $rev );
	}

	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$class = $this->required ? $this->cssClass . ' required' : $this->cssClass;
		$show = null;
		$field = null;
		static $js = false;
		$params = array( 'id' => $this->nid, 'class' => $class );
		if ( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}

		$files = SPConfig::unserialize( $this->getRaw() );
		if ( is_array( $files ) && count( $files ) ) {
			if ( isset( $files[ 'ico' ] ) ) {
				$show = $files[ 'ico' ];
			}
			elseif ( isset( $files[ 'thumb' ] ) ) {
				$show = $files[ 'thumb' ];
			}
		}
		if ( $show ) {
			$img = Sobi::Cfg( 'live_site' ) . $show;
			$field .= "\n<div id=\"{$this->nid}_img_preview\" class=\"spEditImage\">";
			$field .= "\n\t<img src=\"{$img}\" alt=\"{$this->name}\"/>";
			$field .= SPHtml_Input::checkbox( $this->nid . '_delete', 1, Sobi::Txt( 'FD.IMG_DELETE_CURRENT_IMAGE' ), $this->nid . '_delete', false, array( 'class' => $this->cssClass ) );
			$field .= "\n</div>\n";
		}
		if ( !( $js ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
			SPFactory::header()->addJsCode( 'SobiPro.jQuery( document ).ready( function () { SobiPro.jQuery( ".spFileUpload" ).SPFileUploader(); } );' );
			$js = true;
		}
		$field .= SPHtml_Input::fileUpload( $this->nid, 'image/*' );
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	private function parseName( $entry, $name, $pattern )
	{
		$placeHolders = array( '/{id}/', '/{orgname}/', '/{entryname}/' );
		$replacements = array( $entry->get( 'id' ), $name, $entry->get( 'nid' ) );
		return preg_replace( $placeHolders, $replacements, $pattern );
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsId
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' )
	{
		$save = array();
		if ( $this->verify( $entry, $request ) ) {
			// check if we are using the ajax upload - then we don't need to play with temp data
			$check = SPRequest::string( $this->nid, null, $request );
			if ( !( $check ) ) {
				/* save the file to temporary folder */
				$data = SPRequest::file( $this->nid, 'tmp_name' );
				if ( $data ) {
					$temp = str_replace( '.', '-', $tsId );
					$path = SPLoader::dirPath( "tmp.edit.{$temp}.images", 'front', false );
					$path .= '/' . SPRequest::file( $this->nid, 'name' );
					$fileClass = SPLoader::loadClass( 'base.fs.file' );
					$file = new $fileClass();
					$file->upload( $data, $path );
					$save[ $this->nid ] = $path;
				}
			}
			else {
				$save[ $this->nid ] = $check;
			}
			$save[ $this->nid . '_delete' ] = SPRequest::bool( $this->nid . '_delete' );
		}
		return $save;
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @throws SPException
	 * @return bool
	 */
	private function verify( $entry, $request )
	{
		if ( strtolower( $request ) == 'post' || strtolower( $request ) == 'get' ) {
			$data = SPRequest::file( $this->nid, 'tmp_name' );
		}
		else {
			$data = SPRequest::file( $this->nid, 'tmp_name', $request );
		}
		$del = SPRequest::bool( $this->nid . '_delete', false, $request );
		$dexs = strlen( $data );
		if ( $this->required && !( $dexs ) ) {
			$files = $this->getRaw();
			if ( !( strlen( $files ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
			}
		}

		$fileSize = SPRequest::file( $this->nid, 'size' );
		if ( $fileSize > $this->maxSize ) {
			throw new SPException( SPLang::e( 'FIELD_IMG_TOO_LARGE', $this->name, $fileSize, $this->maxSize ) );
		}

		/* check if there was an adminField */
		if ( $this->adminField && ( $dexs || $del ) ) {
			if ( !( Sobi:: Can( 'entry.adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->name ) );
			}
		}

		/* check if it was free */
		if ( !( $this->isFree ) && $this->fee && $dexs ) {
			SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
		}

		/* check if it was editLimit */
		if ( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
		}

		/* check if it was editable */
		if ( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs && $entry->get( 'version' ) > 1 ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
		}
		return true;
	}

	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @param string $request
	 * @throws SPException
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$del = SPRequest::bool( $this->nid . '_delete', false, $request );
		static $store = null;
		$cache = false;
		if ( $store == null ) {
			$store = SPFactory::registry()->get( 'requestcache_stored' );
		}
		if ( is_array( $store ) && isset( $store[ $this->nid ] ) ) {
			if ( !( strstr( $store[ $this->nid ], 'file://' ) ) ) {
				$data = $store[ $this->nid ];
				$cache = true;
				$orgName = SPRequest::file( $this->nid, 'name', $request );
			}
			else {
				SPRequest::set( $this->nid, $store[ $this->nid ] );
				$orgName = SPRequest::file( $this->nid, 'name' );
				$data = SPRequest::file( $this->nid, 'tmp_name' );
			}
		}
		else {
			$data = SPRequest::file( $this->nid, 'tmp_name' );
			$orgName = SPRequest::file( $this->nid, 'name' );
		}
		$files = array();
		$sPath = $this->parseName( $entry, $orgName, $this->savePath );
		$path = SPLoader::dirPath( $sPath, 'root', false );
		/* if we have an image */
		if ( $data ) {
			$fileSize = SPRequest::file( $this->nid, 'size' );
			if ( $fileSize > $this->maxSize ) {
				throw new SPException( SPLang::e( 'FIELD_IMG_TOO_LARGE', $this->name, $fileSize, $this->maxSize ) );
			}
			//			$imgClass = SPLoader::loadClass( 'base.fs.image' );
			//			if( !$this->keepOrg ) {
			//				$orgName = $this->parseName( $entry, $orgName, $this->imageName );
			//			}
			/**
			 * @var SPImage $orgImage
			 */
			if ( $cache ) {
				$orgImage = SPFactory::Instance( 'base.fs.image', $data );
				$orgImage->move( $path . $orgName );
			}
			else {
				$orgImage = SPFactory::Instance( 'base.fs.image' );
				$orgImage->upload( $data, $path . $orgName );
			}
			if ( $this->resize ) {
				$image = clone $orgImage;
				try {
					$image->resample( $this->resizeWidth, $this->resizeHeight, false );
					$files[ 'image' ] = $this->parseName( $entry, $orgName, $this->imageName );
					$image->saveAs( $path . $files[ 'image' ] );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$image->delete();
					throw new SPException( SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ) );
				}
			}
			if ( $this->generateThumb ) {
				$thumb = clone $orgImage;
				try {
					$thumb->resample( $this->thumbWidth, $this->thumbHeight, false );
					$files[ 'thumb' ] = $this->parseName( $entry, $orgName, $this->thumbName );
					$thumb->saveAs( $path . $files[ 'thumb' ] );

				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$thumb->delete();
					throw new SPException( SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ) );
				}
			}
			$ico = clone $orgImage;
			try {
				$icoSize = explode( ':', Sobi::Cfg( 'image.ico_size', '80:80' ) );
				$ico->resample( $icoSize[ 0 ], $icoSize[ 1 ], false );
				$files[ 'ico' ] = $this->parseName( $entry, strtolower( $orgName ), 'ico_{orgname}' );
				$ico->saveAs( $path . $files[ 'ico' ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
				$ico->delete();
				throw new SPException( SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ) );
			}
			if ( !$this->keepOrg ) {
				$orgImage->delete();
			}
			else {
				$files[ 'original' ] = $this->parseName( $entry, $orgName, '{orgname}' );
			}
			foreach ( $files as $i => $file ) {
				$files[ $i ] = $sPath . $file;
			}
		}
		/* otherwise deleting an image */
		elseif ( $del ) {
			$this->delImgs();
			$files = array();
		}
		else {
			return true;
		}
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$this->verify( $entry, $request );

		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */

		/* collect the needed params */
		$save = count( $files ) ? SPConfig::serialize( $files ) : null;
		$params = array();
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
		$params[ 'baseData' ] = $db->escape( $save );
		$params[ 'approved' ] = $entry->get( 'approved' );
		$params[ 'confirmed' ] = $entry->get( 'confirmed' );
		/* if it is the first version, it is new entry */
		if ( $entry->get( 'version' ) == 1 ) {
			$params[ 'createdTime' ] = $time;
			$params[ 'createdBy' ] = $uid;
			$params[ 'createdIP' ] = $IP;
		}
		$params[ 'updatedTime' ] = $time;
		$params[ 'updatedBy' ] = $uid;
		$params[ 'updatedIP' ] = $IP;
		$params[ 'copy' ] = !( $entry->get( 'approved' ) );
		if ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) {
			--$this->editLimit;
		}
		$params[ 'editLimit' ] = $this->editLimit;

		/* save it */
		try {
			$db->insertUpdate( 'spdb_field_data', $params );
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELDS_DATA_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/* (non-PHPdoc)
	  * @see Site/opt/fields/SPFieldType#deleteData($sid)
	  */
	public function deleteData( $sid )
	{
		parent::deleteData( $sid );
		$this->delImgs();
	}

	private function delImgs()
	{
		$files = SPConfig::unserialize( $this->getRaw() );
		if ( is_array( $files ) && count( $files ) ) {
			SPLoader::loadClass( 'cms.base.fs' );
			foreach ( $files as $file ) {
				if ( !( strlen( $file ) ) ) {
					continue;
				}
				$file = Sobi::FixPath( SOBI_ROOT . "/{$file}" );
				// should never happen but who knows ....
				if ( $file == SOBI_ROOT ) {
					continue;
				}
				if ( SPFs::exists( $file ) ) {
					SPFs::delete( $file );
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$files = SPConfig::unserialize( $this->getRaw() );
		if ( isset( $files[ 'original' ] ) ) {
			$files[ 'orginal' ] = $files[ 'original' ];
		}
		$float = null;
		if ( is_array( $files ) && count( $files ) ) {
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
			$this->cssClass = $this->cssClass . ' ' . $this->nid;
			$this->cleanCss();
			switch ( $this->currentView ) {
				default:
				case 'vcard':
					$img = $this->inVcard;
					break;
				case 'details':
					$img = $this->inDetails;
					break;
			}
			if ( isset( $files[ $img ] ) ) {
				$show = $files[ $img ];
			}
			elseif ( isset( $files[ 'thumb' ] ) ) {
				$show = $files[ 'thumb' ];
			}
			elseif ( isset( $files[ 'ico' ] ) ) {
				$show = $files[ 'ico' ];
			}
			if ( isset( $show ) ) {
				switch ( $img ) {
					case 'thumb':
						$float = $this->thumbFloat;
						break;
					case 'image':
						$float = $this->imageFloat;
						break;
				}
				$data = array(
					'_complex' => 1,
					'_data' => null,
					'_attributes' => array(
						'class' => $this->cssClass,
						'src' => Sobi::Cfg( 'live_site' ) . $show,
						'alt' => ''
					)
				);
				if ( $float ) {
					$data[ '_attributes' ][ 'style' ] = "float:{$float};";
				}
				return array(
					'_complex' => 1,
					'_data' => array( 'img' => $data ),
					'_attributes' => array(
						'icon' => isset( $files[ 'ico' ] ) ? $files[ 'ico' ] : null,
						'image' => isset( $files[ 'image' ] ) ? $files[ 'image' ] : null,
						'thumbnail' => isset( $files[ 'thumb' ] ) ? $files[ 'thumb' ] : null,
						'original' => isset( $files[ 'original' ] ) ? $files[ 'original' ] : null,
						'class' => $this->cssClass
					)
				);
			}
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		return $this->verify( $entry, $request );
	}
}
