<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.inbox' );

/**
 * @author  Radek Suski
 * @version 1.0
 * @created 28-Nov-2009 20:06:23
 */
class SPField_Image extends SPField_Inbox implements SPFieldInterface
{
	/** * @var bool */
	protected $keepOrg = true;
	/** * @var bool */
	protected $resize = true;
	/** * @var bool */
	protected $crop = false;
	/** * @var double */
	protected $maxSize = 2097152;
	/** * @var int */
	protected $resizeWidth = 500;
	/** * @var int */
	protected $resizeHeight = 500;
	/** * @var string */
	protected $imageName = 'img_{orgname}';
	/** * @var string */
	protected $imageFloat = '';
	/** * @var bool */
	protected $generateThumb = true;
	/** * @var string */
	protected $thumbFloat = '';
	/** * @var string */
	protected $float = '';
	/** * @var string */
	protected $thumbName = 'thumb_{orgname}';
	/** @var int */
	protected $thumbWidth = 400;
	/** @var int */
	protected $thumbHeight = 400;
	/** * @var string */
	protected $inVcard = 'thumb';
	/** * @var string */
	protected $inDetails = 'image';
	/** * @var string */
	protected $inCategory = 'image';
	/** * @var string */
	protected $savePath = 'images/sobipro/entries/{id}/';
	/** * @var string */
	protected $cssClass = 'spClassImage';
	/** * @var string */
	protected $cssClassView = 'spClassViewImage';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditImage';
	/** * @var string */
	protected $dType = 'special';
	/** * @var int */
	protected $bsWidth = 10;
	/** @var bool */
	static private $CAT_FIELD = true;
	/** @var bool */
	protected $detectTransparency = true;
	/*** @var bool */
	protected $suggesting = false;

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return [ 'width', 'savePath', 'inDetails', 'inVcard', 'thumbHeight', 'thumbWidth', 'thumbName', 'keepOrg', 'resize', 'maxSize', 'resizeWidth', 'resizeHeight', 'imageName', 'generateThumb', 'thumbFloat', 'imageFloat', 'itemprop', 'crop', 'cssClassView', 'cssClassEdit', 'showEditLabel', 'inCategory', 'float', 'detectTransparency' ];
	}

	public function compareRevisions( $revision, $current )
	{
		if ( isset( $revision[ 'image' ] ) ) {
			$rev = basename( $revision[ 'image' ] );
		}
		if ( isset( $current[ 'image' ] ) ) {
			$cur = basename( $current[ 'image' ] );
		}

		return [ 'current' => $cur, 'revision' => $rev ];
	}

	/**
	 * Shows the field in the edit entry or add entry form
	 *
	 * @param bool $return return or display directly
	 *
	 * @return string
	 */
	public function field( $return = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$this->suffix = ''; //clear if any
		$class = $this->required ? $this->cssClass . ' required' : $this->cssClass;
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( $this->bsWidth ) {
				$width = SPHtml_Input::_translateWidth( $this->bsWidth );
				$class .= ' ' . $width;
			}
		}
		$show = null;
		$field = null;
		static $js = false;
		$params = [ 'id' => $this->nid, 'class' => $class ];
		if ( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}
		$files = $this->getExistingFiles();

		if ( is_array( $files ) && count( $files ) ) {
			if ( isset( $files[ 'ico' ] ) ) {
				$show = $files[ 'ico' ];
			}
			elseif ( isset( $files[ 'thumb' ] ) ) {
				$show = $files[ 'thumb' ];
			}
		}
		/** Mon, Jun 20, 2016 13:45:41 - we do not need the style for it */
//		$noncropsize = "";
//		$icoSize = explode( ':', Sobi::Cfg( 'image.ico_size', '100:100' ) );
		if ( $show ) {
			$img = Sobi::Cfg( 'live_site' ) . $show;
			if ( !$this->crop ) {
//				$noncropsize = "style=\"width: {$icoSize[0]}px; height: {$icoSize[1]}px;\"";
			}
		}
		$field .= "\n<div class=\"spImageField\">";
		$field .= "\n<div>";
		$field .= "\n<div id=\"{$this->nid}_img_preview\" class=\"spEditImage\">";
		$field .= "\n<div class=\"spEditImagePreview\" >";
		if ( $show ) {
//			$field .= "\n\t<img src=\"{$img}\" alt=\"{$this->name}\" {$noncropsize} />";
			$field .= "\n\t<img src=\"{$img}\" alt=\"{$this->name}\" />";
		}
		$field .= "\n</div>";
		$field .= "\n</div>";
		$field .= "\n</div>";
		$field .= "\n<div class=\"spImageUpDelete\">";
		if ( $show ) {
			$field .= SPHtml_Input::checkbox( $this->nid . '_delete', 1, Sobi::Txt( 'FD.IMG_DELETE_CURRENT_IMAGE' ), $this->nid . '_delete', false, [ 'class' => $this->cssClass ] );
		}
		$field .= SPHtml_Input::fileUpload( $this->nid, 'image/*', null, 'spImageUpload', str_replace( 'field_', 'field.', $this->nid ) . '.upload' );
		$field .= "\n</div>";
		$field .= "\n</div>";

		if ( !( $js ) ) {
			SPFactory::header()
					->addJsFile( 'opt.field_image_edit' )
					->addJsCode( 'SobiPro.jQuery( document ).ready( function () { SobiPro.jQuery( ".spImageUpload" ).SPFileUploader(); } );' );
			$js = true;
		}
		if ( $this->crop ) {
			$modalclass = 'modal hide';

			SPFactory::header()
					->addJsFile( 'cropper' )
					->addCssFile( 'cropper' );
			$field .= SPHtml_Input::modalWindow( Sobi::Txt( 'IMAGE_CROP_HEADER' ), $this->nid . '_modal', null, $modalclass, 'CLOSE', 'SAVE' );
		}
		// avoiding multiple roots
//		$field = "<div>{$field}</div>";
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	protected function parseName( $entry, $name, $pattern, $addExt = false )
	{
		$nameArray = explode( '.', $name );
		$ext = strtolower( array_pop( $nameArray ) );
		$name = implode( '.', $nameArray );
		$user = SPUser::getBaseData( ( int )$entry->get( 'owner' ) );
		// @todo change to the global method
		$placeHolders = [ '/{id}/', '/{orgname}/', '/{entryname}/', '/{oid}/', '/{ownername}/', '/{uid}/', '/{username}/', '/{nid}/' ];
		$replacements = [ $entry->get( 'id' ), $name, $entry->get( 'nid' ), ( isset( $user->id ) ? $user->id : null ), ( isset( $user->name ) ? SPLang::nid( $user->name ) : 'guest' ), Sobi::My( 'id' ), SPLang::nid( Sobi::My( 'name' ) ), $this->nid ];
		$fileName = preg_replace( $placeHolders, $replacements, $pattern );

		return $addExt ? $fileName . '.' . $ext : $fileName;
	}

	public function getRawData( &$data )
	{
		if ( is_string( $data ) ) {
			try {
				$data = SPConfig::unserialize( $data );
			} catch ( SPException $x ) {
				$data = null;
			}
		}
		// legacy for ImEx - have you learned a lesson Radek?
		if ( isset( $data[ 'data' ] ) && defined( 'SOBIPRO_ADM' ) ) {
			unset( $data[ 'data' ] );
		}

		return SPConfig::serialize( $data );
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 *
	 * @param SPEntry $entry
	 * @param string $tsId
	 * @param string $request
	 *
	 * @return mixed
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' )
	{
		$save = [];
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
	 *
	 * @throws SPException
	 * @return bool
	 */
	private function verify( $entry, $request )
	{
		static $store = null;
		$directory = SPRequest::string( $this->nid, null, false, $request );
		if ( strtolower( $request ) == 'post' || strtolower( $request ) == 'get' ) {
			$data = SPRequest::file( $this->nid, 'tmp_name' );
		}
		else {
			$data = SPRequest::file( $this->nid, 'tmp_name', $request );
		}
		if ( $store == null ) {
			$store = SPFactory::registry()->get( 'requestcache_stored' );
		}
		if ( is_array( $store ) && isset( $store[ $this->nid ] ) ) {
			if ( !( strstr( $store[ $this->nid ], 'file://' ) ) && !( strstr( $store[ $this->nid ], 'directory://' ) ) ) {
				$data = $store[ $this->nid ];
			}
			else {
				$directory = $store[ $this->nid ];
			}
		}
		$fileSize = SPRequest::file( $this->nid, 'size' );
		if ( $directory && strstr( $directory, 'directory://' ) ) {
			list( $data, $dirName, $files ) = $this->getAjaxFiles( $directory );
			if ( count( $files ) ) {
				foreach ( $files as $file ) {
					if ( $file == '.' ) {
						continue;
					}
					if ( $file == '..' ) {
						continue;
					}
					if ( strpos( $file, 'icon_' ) !== false ) {
						continue;
					}
					if ( strpos( $file, 'resized_' ) !== false ) {
						continue;
					}
					if ( strpos( $file, 'cropped_' ) !== false ) {
						continue;
					}
					if ( strpos( $file, '.var' ) !== false ) {
						continue;
					}
					$fileSize = filesize( $dirName . $file );
				}
			}
		}
		else {
//			$fileSize = SPRequest::file( $this->nid, 'size' );
		}
		$del = SPRequest::bool( $this->nid . '_delete', false, $request );
		$dexs = strlen( $data );
		if ( $this->required && !( $dexs ) ) {
			$files = $this->getRaw();
			if ( !( count( $files ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
			}
		}

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
	 *
	 * @param SPEntry $entry
	 * @param string $request
	 * @param bool $clone
	 *
	 * @throws SPException
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST', $clone = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$del = SPRequest::bool( $this->nid . '_delete', false, $request );
		if ( $clone ) {
			$orgSid = SPRequest::sid();
			$this->loadData( $orgSid );
			$files = $this->getExistingFiles();
			$cloneFiles = [];
			if ( isset( $files[ 'image' ] ) && file_exists( SOBI_ROOT . '/' . $files[ 'image' ] ) ) {
				return $this->cloneFiles( $entry, $request, $files, $cloneFiles );
			}
		}

		//initializations
		$fileSize = SPRequest::file( $this->nid, 'size' );
		$data = null;

		$cropped = null;
		static $store = null;
		$cache = false;
		if ( $store == null ) {
			$store = SPFactory::registry()->get( 'requestcache_stored' );
		}
		if ( is_array( $store ) && isset( $store[ $this->nid ] ) ) {
			if ( !( strstr( $store[ $this->nid ], 'file://' ) ) && !( strstr( $store[ $this->nid ], 'directory://' ) ) ) {
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
		$sPath = $this->parseName( $entry, $orgName, $this->savePath );
		$path = SPLoader::dirPath( $sPath, 'root', false );

		/** Wed, Oct 15, 2014 13:51:03
		 * Implemented a cropper with Ajax checker.
		 * This is the actual method to get those files
		 * Other methods left for BC
		 * */

		if ( !( $data ) ) {
			$directory = SPRequest::string( $this->nid, null, false, $request );
			if ( strlen( $directory ) ) {
				list( $data, $dirName, $files, $coordinates ) = $this->getAjaxFiles( $directory );
				if ( count( $files ) ) {
					foreach ( $files as $file ) {
						if ( $file == '.' ) {
							continue;
						}
						if ( $file == '..' ) {
							continue;
						}
						if ( strpos( $file, 'icon_' ) !== false ) {
							continue;
						}
						if ( strpos( $file, 'resized_' ) !== false ) {
							continue;
						}
						if ( strpos( $file, 'cropped_' ) !== false ) {
							$cropped = $dirName . $file;
							SPFs::upload( $cropped, $path . basename( $cropped ) );
							continue;
						}
						if ( strpos( $file, '.var' ) !== false ) {
							continue;
						}
						$fileSize = filesize( $dirName . $file );
						$orgName = $file;
					}
				}
				if ( strlen( $coordinates ) ) {
					$coordinates = json_decode( SPLang::clean( $coordinates ), true );
					/** @var SPImage $croppedImage */
					$croppedImage = SPFactory::Instance( 'base.fs.image', $dirName . $orgName );
					$croppedImage->crop( $coordinates[ 'width' ], $coordinates[ 'height' ], $coordinates[ 'x' ], $coordinates[ 'y' ] );
					$cropped = 'cropped_' . $orgName;
					$croppedImage->saveAs( $path . $cropped );
				}
				$data = strlen( $cropped ) ? $cropped : $dirName . $file;
			}
		}
		$files = [];
		/* if we have an image */
		if ( $data && $orgName ) {
			if ( $fileSize > $this->maxSize ) {
				throw new SPException( SPLang::e( 'FIELD_IMG_TOO_LARGE', $this->name, $fileSize, $this->maxSize ) );
			}
			if ( $cropped ) {
				SPFs::upload( $dirName . $orgName, $path . $orgName );
			}
			/**
			 * @var SPImage $orgImage
			 */
			if ( $cache ) {
				$orgImage = SPFactory::Instance( 'base.fs.image', $data );
				$orgImage->move( $path . $orgName );
			}
			else {
				$orgImage = SPFactory::Instance( 'base.fs.image' );
				$nameArray = explode( '.', $orgName );
				$ext = strtolower( array_pop( $nameArray ) );
				$nameArray[] = $ext;
				$orgName = implode( '.', $nameArray );
				if ( $cropped ) {
					// Fri, Jul 3, 2015 17:15:05
					// it has been actually uploaded at ~425
					// not sure why we are trying to upload it again
					if ( SPFs::exists( $dirName . $data ) ) {
						$orgImage->upload( $dirName . $data, $path . basename( $data ) );
					}
					else {
						$orgImage->setFile( $path . basename( $data ) );
					}
				}
				else {
					$orgImage->upload( $dirName . $orgName, $path . $orgName );
				}
			}
			$files[ 'data' ][ 'exif' ] = $orgImage->exif();
			$this->cleanExif( $files[ 'data' ][ 'exif' ] );
			if ( Sobi::Cfg( 'image_field.fix_rotation', true ) ) {
				if ( $orgImage->fixRotation() ) {
					$orgImage->save();
				}
			}
			if ( $this->resize ) {
				/** @var SPImage $image */
				$image = clone $orgImage;
				$image->setTransparency( $this->detectTransparency );
				try {
					$image->resample( $this->resizeWidth, $this->resizeHeight, false );
					$files[ 'image' ] = $this->parseName( $entry, $orgName, $this->imageName, true );
					$image->saveAs( $path . $files[ 'image' ] );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$image->delete();
					throw new SPException( SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ) );
				}
			}
			if ( $this->generateThumb ) {
				$thumb = clone $orgImage;
				$thumb->setTransparency( $this->detectTransparency );
				try {
					$thumb->resample( $this->thumbWidth, $this->thumbHeight, false );
					$files[ 'thumb' ] = $this->parseName( $entry, $orgName, $this->thumbName, true );
					$thumb->saveAs( $path . $files[ 'thumb' ] );

				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					$thumb->delete();
					throw new SPException( SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ) );
				}
			}
			$ico = clone $orgImage;
			try {
				$icoSize = explode( ':', Sobi::Cfg( 'image.ico_size', '100:100' ) );
				$ico->setTransparency( $this->detectTransparency );
				$ico->resample( $icoSize[ 0 ], $icoSize[ 1 ], false );
				$files[ 'ico' ] = $this->parseName( $entry, strtolower( $orgName ), 'ico_{orgname}_' . $this->nid, true );
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
				$files[ 'original' ] = $this->parseName( $entry, $orgName, '{orgname}', true );
			}
			foreach ( $files as $i => $file ) {
				if ( $i == 'data' ) {
					continue;
				}
				$files[ $i ] = $sPath . $file;
			}
		}
		/* otherwise deleting an image */
		elseif ( $del ) {
			$this->delImgs();
			$files = [];
		}
		else {
			return true;
		}
		$this->storeData( $entry, $request, $files );
	}

	protected function cleanExif( &$data )
	{
		// Wed, Feb 19, 2014 17:17:20
		// we need to remove junk from indexes too
		// it appears to be the easies method
		$data = json_encode( $data );
		$data = preg_replace( '/\p{Cc}+/u', null, $data );
		$data = str_replace( 'UndefinedTag:', null, $data );
		$data = json_decode( $data, true );
		if ( is_array( $data ) && count( $data ) ) {
			foreach ( $data as $index => $row ) {
				if ( is_array( $row ) ) {
					$this->cleanExif( $row );
				}
				else {
					$data[ $index ] = preg_replace( '/\p{Cc}+/u', null, $row );
				}
			}
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

	protected function convertGPS( $deg, $min, $sec, $hem )
	{
		$d = $deg + ( ( ( $min / 60 ) + ( $sec / 3600 ) / 100 ) );

		return ( $hem == 'S' || $hem == 'W' ) ? $d *= -1 : $d;
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$files = $this->getRaw();
		if ( is_string( $files ) ) {
			try {
				$files = SPConfig::unserialize( $files );
			} catch ( SPException $x ) {
				$files = null;
			}
		}
		$exifToPass = [];
		if ( isset( $files[ 'original' ] ) ) {
			$files[ 'orginal' ] = $files[ 'original' ];
		}
		if ( isset( $files[ 'data' ][ 'exif' ] ) && Sobi::Cfg( 'image_field.pass_exif', true ) ) {
			$exif = json_encode( $files[ 'data' ][ 'exif' ] );
			$exif = str_replace( 'UndefinedTag:', null, $exif );
			$exif = preg_replace( '/\p{Cc}+/u', null, $exif );
			$exif = json_decode( preg_replace( '/[^a-zA-Z0-9\{\}\:\.\,\(\)\"\'\/\\\\!\?\[\]\@\#\$\%\^\&\*\+\-\_]/', '', $exif ), true );
			if ( isset( $exif[ 'EXIF' ] ) ) {
				$tags = Sobi::Cfg( 'image_field.exif_data', [] );
				if ( count( $tags ) ) {
					foreach ( $tags as $tag ) {
						$exifToPass[ 'BASE' ][ $tag ] = isset( $exif[ 'EXIF' ][ $tag ] ) ? $exif[ 'EXIF' ][ $tag ] : 'unknown';
					}
				}
			}
			if ( isset( $exif[ 'FILE' ] ) ) {
				$exifToPass[ 'FILE' ] = $exif[ 'FILE' ];
			}
			if ( isset( $exif[ 'FILE' ] ) ) {
				$exifToPass[ 'FILE' ] = $exif[ 'FILE' ];
			}
			if ( isset( $exif[ 'IFD0' ] ) ) {
				$tags = Sobi::Cfg( 'image_field.exif_id_data', [] );
				if ( count( $tags ) ) {
					foreach ( $tags as $tag ) {
						$exifToPass[ 'IFD0' ][ $tag ] = isset( $exif[ 'IFD0' ][ $tag ] ) ? $exif[ 'IFD0' ][ $tag ] : 'unknown';
					}
				}
			}
			if ( isset( $files[ 'data' ][ 'exif' ][ 'GPS' ] ) ) {
				$exifToPass[ 'GPS' ][ 'coordinates' ][ 'latitude' ] = $this->convertGPS( $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLatitude' ][ 0 ], $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLatitude' ][ 1 ], $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLatitude' ][ 2 ], $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLatitudeRef' ] );
				$exifToPass[ 'GPS' ][ 'coordinates' ][ 'longitude' ] = $this->convertGPS( $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLongitude' ][ 0 ], $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLongitude' ][ 1 ], $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLongitude' ][ 2 ], $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLongitudeRef' ] );
				$exifToPass[ 'GPS' ][ 'coordinates' ][ 'latitude-ref' ] = isset( $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLatitudeRef' ] ) ? $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLatitudeRef' ] : 'unknown';
				$exifToPass[ 'GPS' ][ 'coordinates' ][ 'longitude-ref' ] = isset( $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLongitudeRef' ] ) ? $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPSLongitudeRef' ] : 'unknown';
				$tags = Sobi::Cfg( 'image_field.exif_gps_data', [] );
				if ( count( $tags ) ) {
					foreach ( $tags as $tag ) {
						$exifToPass[ 'GPS' ][ $tag ] = isset( $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPS' . $tag ] ) ? $files[ 'data' ][ 'exif' ][ 'GPS' ][ 'GPS' . $tag ] : 'unknown';
					}
				}

			}
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
				case 'category':
					$img = $this->inCategory;
			}
			$prefix = 'img_';
			if ( isset( $files[ $img ] ) ) {
				$show = $files[ $img ];
				$prefix = 'img_';
			}
			elseif ( isset( $files[ 'thumb' ] ) ) {
				$show = $files[ 'thumb' ];
				$prefix = 'thumb_';
			}
			elseif ( isset( $files[ 'original' ] ) ) {
				$show = $files[ 'original' ];
				$prefix = '';
			}
			elseif ( isset( $files[ 'ico' ] ) ) {
				$show = $files[ 'ico' ];
				$prefix = 'ico_';
			}

			//nur den originalen filenamen ohne extension als alt und title tag
			$info = pathinfo( $show );
			$fname = basename( $show, '.' . $info[ 'extension' ] );
			$fname = str_replace( $prefix, "", $fname );

			if ( isset( $show ) ) {
				switch ( $img ) {
					case 'thumb':
						$float = $this->thumbFloat;
						break;
					case 'image':
						$float = $this->imageFloat;
						break;
				}
				if ( $this->currentView == 'category' ) {
					$float = $this->float;
				}
				$data = [
						'_complex' => 1,
						'_data' => null,
						'_attributes' => [
								'class' => $this->cssClass,
								'src' => Sobi::FixPath( Sobi::Cfg( 'live_site' ) . $show ),
								'alt' => $fname,
								'title' => $fname
						]
				];
				if ( $float ) {
					$data[ '_attributes' ][ 'style' ] = "float:{$float};";
				}

				return [
						'_complex' => 1,
						'_data' => [ 'img' => $data ],
						'_attributes' => [
								'icon' => isset( $files[ 'ico' ] ) ? Sobi::FixPath( $files[ 'ico' ] ) : null,
								'image' => isset( $files[ 'image' ] ) ? Sobi::FixPath( $files[ 'image' ] ) : null,
								'thumbnail' => isset( $files[ 'thumb' ] ) ? Sobi::FixPath( $files[ 'thumb' ] ) : null,
								'original' => isset( $files[ 'original' ] ) ? Sobi::FixPath( $files[ 'original' ] ) : null,
								'class' => $this->cssClass
						],
						'_options' => [ 'exif' => $exifToPass ],
				];
			}
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 *
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		return $this->verify( $entry, $request );
	}

	/**
	 * */
	public function ProxyUpload()
	{
		$ident = SPRequest::cmd( 'ident', null, 'post' );
		$data = SPRequest::file( $ident, 'tmp_name' );
		$secret = md5( Sobi::Cfg( 'secret' ) );
		if ( $data ) {
			$properties = SPRequest::file( $ident );
			$orgFileName = $properties[ 'name' ];
			$extension = SPFs::getExt( $orgFileName );
			$orgFileName = str_replace( '.' . $extension, '.' . strtolower( $extension ), $orgFileName );
			if ( $properties[ 'size' ] > $this->maxSize ) {
				$this->message( [ 'type' => 'error', 'text' => SPLang::e( 'FIELD_IMG_TOO_LARGE', $this->name, $properties[ 'size' ], $this->maxSize ), 'id' => '', ] );
			}
			$dirNameHash = md5( $orgFileName . time() . $secret );
			$dirName = SPLoader::dirPath( "tmp.files.{$secret}.{$dirNameHash}", 'front', false );
			SPFs::mkdir( $dirName );
			$path = $dirName . $orgFileName;
			/** @var $file SPImage */
			$orgImage = SPFactory::Instance( 'base.fs.image' );
			if ( !( $orgImage->upload( $data, $path ) ) ) {
				$this->message( [ 'type' => 'error', 'text' => SPLang::e( 'CANNOT_UPLOAD_FILE' ), 'id' => '' ] );
			}
			if ( Sobi::Cfg( 'image_field.fix_rotation', true ) ) {
				if ( $orgImage->fixRotation() ) {
					$orgImage->save();
				}
			}
			if ( $this->crop ) {
				$croppedImage = clone $orgImage;
				list( $originalWidth, $originalHeight ) = getimagesize( $path );
				$aspectRatio = $this->resizeWidth / $this->resizeHeight;
				$width = $aspectRatio * $originalHeight > $originalWidth ? $originalWidth : $aspectRatio * $originalHeight;
				$height = $originalWidth / $aspectRatio > $originalHeight ? $originalHeight : $originalWidth / $aspectRatio;
				try {
					$croppedImage->setTransparency( $this->detectTransparency );
					$croppedImage->crop( $width, $height );
					$croppedImage->saveAs( $dirName . 'cropped_' . $orgFileName );
					$ico = SPFactory::Instance( 'base.fs.image', $dirName . 'cropped_' . $orgFileName );
				} catch ( SPException $x ) {
					$this->message( [ 'type' => 'error', 'text' => SPLang::e( 'FIELD_IMG_CANNOT_CROP', $x->getMessage() ), 'id' => '', ] );
				}
			}
			else {
				$ico = clone $orgImage;
			}
			$image = clone $orgImage;
			try {
				$previewSize = explode( ':', Sobi::Cfg( 'image.preview_size', '500:500' ) );
				$image->setTransparency( $this->detectTransparency );
				$image->resample( $previewSize[ 0 ], $previewSize[ 1 ], false );
				$image->saveAs( $dirName . 'resized_' . $orgFileName );
			} catch ( SPException $x ) {
				$image->delete();
				$this->message( [ 'type' => 'error', 'text' => SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ), 'id' => '', ] );
			}
			try {
				$icoSize = explode( ':', Sobi::Cfg( 'image.ico_size', '100:100' ) );
				$ico->resample( $icoSize[ 0 ], $icoSize[ 1 ], false );
				$ico->saveAs( $dirName . 'icon_' . $orgFileName );
			} catch ( SPException $x ) {
				$ico->delete();
				$this->message( [ 'type' => 'error', 'text' => SPLang::e( 'FIELD_IMG_CANNOT_RESAMPLE', $x->getMessage() ), 'id' => '', ] );
			}

			$path = $orgImage->getPathname();
			$type = $this->check( $path );
			$properties[ 'tmp_name' ] = $path;
			$out = SPConfig::serialize( $properties );
			SPFs::write( SPLoader::dirPath( "tmp.files.{$secret}", 'front', false ) . '/' . $orgFileName . '.var', $out );

			$response = [
					'type' => 'success',
					'text' => $this->crop ? Sobi::Txt( 'IMAGE_UPLOADED_CROP', $properties[ 'name' ], $type ) : Sobi::Txt( 'FILE_UPLOADED', $properties[ 'name' ] ),
					'id' => 'directory://' . $dirNameHash,
					'data' => [
							'name' => $properties[ 'name' ],
							'type' => $properties[ 'type' ],
							'size' => $properties[ 'size' ],
							'original' => $dirNameHash . '/' . $properties[ 'name' ],
							'icon' => $dirNameHash . '/' . 'icon_' . $orgFileName,
							'crop' => $this->crop,
							'height' => $this->resizeHeight,
							'width' => $this->resizeWidth,
					]
			];
		}
		else {
			$response = [ 'type' => 'error', 'text' => SPLang::e( 'CANNOT_UPLOAD_FILE_NO_DATA' ), 'id' => '', ];
		}
		$this->message( $response );
	}

	protected function check( $file )
	{
		$allowed = SPLoader::loadIniFile( 'etc.files' );
		$mType = SPFactory::Instance( 'services.fileinfo', $file )->mimeType();
		if ( strlen( $mType ) && !( in_array( $mType, $allowed ) ) ) {
			SPFs::delete( $file );
			$this->message( [ 'type' => 'error', 'text' => SPLang::e( 'FILE_WRONG_TYPE', $mType ), 'id' => '' ] );
		}

		return $mType;
	}

	protected function message( $response )
	{
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		echo json_encode( $response );
		exit;
	}

	/**
	 * */
	public function ProxyIcon()
	{
		$secret = md5( Sobi::Cfg( 'secret' ) );
		$file = SPRequest::string( 'file' );
		$file = explode( '/', $file );
		$dirName = SPLoader::dirPath( "tmp.files.{$secret}.{$file[0]}", 'front', true );
		$fileName = $dirName . $file[ 1 ];
		header( 'Content-Type:' . image_type_to_mime_type( exif_imagetype( $fileName ) ) );
		header( 'Content-Length: ' . filesize( $fileName ) );
		readfile( $fileName );
		exit;
	}

	/**
	 * @param $directory
	 *
	 * @return array
	 */
	private function getAjaxFiles( $directory )
	{
		$secret = md5( Sobi::Cfg( 'secret' ) );
		$coordinates = null;
		$dirNameHash = str_replace( 'directory://', null, $directory );
		if ( strstr( $dirNameHash, '::coordinates://' ) ) {
			$struct = explode( '::coordinates://', $dirNameHash );
			$dirNameHash = $struct[ 0 ];
			$coordinates = $struct[ 1 ];
		}
		$data = $dirNameHash;
		$dirName = SPLoader::dirPath( "tmp.files.{$secret}.{$dirNameHash}", 'front', false );
		$files = scandir( $dirName );

		return [ $data, $dirName, $files, $coordinates ];
	}

	/**
	 * @return mixed|null
	 */
	protected function getExistingFiles()
	{
		$files = $this->getRaw();
		if ( is_string( $files ) ) {
			try {
				$files = SPConfig::unserialize( $files );

				return $files;
			} catch ( SPException $x ) {
				return null;
			}
		}

		return $files;
	}

	/**
	 * @param $entry
	 * @param $request
	 * @param $files
	 *
	 * @return SPdb
	 * @throws SPException
	 */
	protected function storeData( &$entry, $request, $files )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		if ( get_class( $this ) == 'SPField_Image' ) {
			$this->verify( $entry, $request );
		}

		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */

		/* collect the needed params */
		$save = count( $files ) ? SPConfig::serialize( $files ) : null;
		$params = [];
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
			return $db;
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELDS_DATA_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			return $db;
		}
	}

	/**
	 * @param $entry
	 * @param $request
	 * @param $files
	 * @param $cloneFiles
	 *
	 * @return SPdb
	 */
	protected function cloneFiles( &$entry, $request, $files, $cloneFiles )
	{
		$orgName = basename( isset( $files[ 'original' ] ) ? $files[ 'original' ] : $files[ 'image' ] );
		$sPath = $this->parseName( $entry, $orgName, $this->savePath );
		if ( isset( $files[ 'original' ] ) && SPFs::exists( SOBI_ROOT . '/' . $files[ 'original' ] ) ) {
			$cloneFiles[ 'original' ] = $sPath . $this->parseName( $entry, $orgName, '{orgname}', true );
			SPFs::copy( SOBI_ROOT . '/' . $files[ 'original' ], SOBI_ROOT . '/' . $cloneFiles[ 'original' ] );
		}

		if ( isset( $files[ 'image' ] ) && SPFs::exists( SOBI_ROOT . '/' . $files[ 'image' ] ) ) {
			$cloneFiles[ 'image' ] = $sPath . $this->parseName( $entry, $orgName, $this->imageName, true );
			SPFs::copy( SOBI_ROOT . '/' . $files[ 'image' ], SOBI_ROOT . '/' . $cloneFiles[ 'image' ] );
		}

		if ( isset( $files[ 'thumb' ] ) && SPFs::exists( SOBI_ROOT . '/' . $files[ 'thumb' ] ) ) {
			$cloneFiles[ 'thumb' ] = $sPath . $this->parseName( $entry, $orgName, $this->thumbName, true );
			SPFs::copy( SOBI_ROOT . '/' . $files[ 'thumb' ], SOBI_ROOT . '/' . $cloneFiles[ 'thumb' ] );
		}

		if ( isset( $files[ 'ico' ] ) && SPFs::exists( SOBI_ROOT . '/' . $files[ 'ico' ] ) ) {
			$cloneFiles[ 'ico' ] = $sPath . $this->parseName( $entry, strtolower( $orgName ), 'ico_{orgname}', true );
			SPFs::copy( SOBI_ROOT . '/' . $files[ 'ico' ], SOBI_ROOT . '/' . $cloneFiles[ 'ico' ] );
		}

		return $this->storeData( $entry, $request, $cloneFiles );
	}

	/**
	 * @param string $data
	 * @param int $section
	 * @param bool $startWith
	 * @param bool $ids
	 * @return array
	 */
	public function searchSuggest( $data, $section, $startWith = true, $ids = false )
	{
		return [];
	}
}
