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
SPLoader::loadClass( 'base.fs.file' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:03:15 PM
 */
class SPImage extends SPFile
{

	/*** @var int */
	private $type = 0;
	/*** @var string */
	private $temp = null;
	/*** @var resource */
	private $image = null;
	/*** @var array */
	private $exif = [];
	/*** @var array */
	static $imgFunctions = [
			IMAGETYPE_GIF => 'imagecreatefromgif',
			IMAGETYPE_JPEG => 'imagecreatefromjpeg',
			IMAGETYPE_PNG => 'imagecreatefrompng',
			IMAGETYPE_JPEG2000 => 'imagecreatefromjpeg'
	];
	protected $transparency = true;

	public function setTransparency( $transparency )
	{
		$this->transparency = $transparency;
	}

	/**
	 * @param int $sections
	 * @param bool $array
	 * @return array|bool
	 */
	public function exif( $sections = 0, $array = true )
	{
		if ( function_exists( 'exif_read_data' ) && $this->_filename ) {
			if ( in_array( strtolower( SPFs::getExt( $this->_filename ) ), [ 'jpg', 'jpeg', 'tiff' ] ) ) {
				$this->exif = exif_read_data( $this->_filename, $sections, $array );
			}
			return $this->exif;
		}
		else {
			return false;
		}
	}


	/**
	 * Resample image
	 * @param $width
	 * @param $height
	 * @param $x
	 * @param $y
	 * @throws SPException
	 * @return bool
	 */
	public function crop( $width, $height, $x = 0, $y = 0 )
	{
//		if ( !( function_exists( 'imagecrop' ) ) ) {
//			throw new SPException( 'Function "imagecrop" is not available on this server' );
//		}
		if ( !$this->_content ) {
			$this->read();
		}
		list( $wOrg, $hOrg, $imgType ) = getimagesize( $this->_filename );
		$this->type = $imgType;
		$currentImg = $this->createImage( $imgType );
		if ( function_exists( 'imagecrop' ) ) {
			$this->image = imagecrop( $currentImg, [ 'x' => $x, 'y' => $y, 'width' => $width, 'height' => $height ] );
		}
		else {
			// imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h )
			$this->image = imagecreatetruecolor( $width, $height );
			imagecopy( $this->image, $currentImg, 0, 0, $x, $y, $width, $height );
		}
		if ( $imgType == IMAGETYPE_GIF || $imgType == IMAGETYPE_PNG ) {
			$this->transparency( $this->image );
		}
		$this->storeImage();
	}

	/**
	 * Resample image
	 * @param $width
	 * @param $height
	 * @param bool $always - even if smaller as given values
	 * @throws SPException
	 * @return bool
	 */
	public function resample( $width, $height, $always = true )
	{
		if ( !( $width && $height ) ) {
			throw new SPException( SPLang::e( 'INVALID_VALUES_FOR_RESAMPLE', $width, $height ) );
		}
		if ( !$this->_content ) {
			$this->read();
		}
		list( $wOrg, $hOrg, $imgType ) = getimagesize( $this->_filename );

		/* if not an image */
		if ( !$wOrg || !$hOrg || !$imgType ) {
			throw new SPException( SPLang::e( 'CANNOT_GET_IMG_INFO', $this->_filename ) );
		}

		/* if not always and image is smaller */
		if ( !$always && ( ( $wOrg <= $width ) && ( $hOrg <= $height ) ) ) {
			return true;
		}

		$orgRatio = $wOrg / $hOrg;

		if ( ( $width / $height ) > $orgRatio ) {
			$width = $height * $orgRatio;
		}
		else {
			$height = $width / $orgRatio;
		}

		/* create new image */
		$this->image = imagecreatetruecolor( $width, $height );
		$currentImg = $this->createImage( $imgType );

		$this->type = $imgType;

		/* save the transparency */
		if ( $imgType == IMAGETYPE_GIF || $imgType == IMAGETYPE_PNG ) {
			$this->transparency( $currentImg );
		}

		/* resample image */
		imagecopyresampled( $this->image, $currentImg, 0, 0, 0, 0, $width, $height, $wOrg, $hOrg );
		$this->storeImage();
	}

	/**
	 * Rotate image
	 * @param $angle
	 * @param $backgroundColor
	 * @param bool $ignoreTransparent
	 * @return bool
	 */
	public function rotate( $angle, $backgroundColor, $ignoreTransparent = false )
	{
		if ( !( $this->type ) ) {
			list( $wOrg, $hOrg, $this->type ) = getimagesize( $this->_filename );
		}
		$currentImg = $this->createImage( $this->type );
		$this->image = imagerotate( $currentImg, $angle, $backgroundColor, $ignoreTransparent );
		$this->storeImage();
	}

	/**
	 * @return bool
	 */
	public function fixRotation()
	{
		$return = false;
		if ( isset( $this->exif[ 'IFD0' ][ 'Orientation' ] ) ) {
			switch ( $this->exif[ 'IFD0' ][ 'Orientation' ] ) {
				case 3:
					$return = true;
					$this->rotate( 180, 0 );
					break;
				case 6:
					$return = true;
					$this->rotate( -90, 0 );
					break;
				case 8:
					$return = true;
					$this->rotate( 90, 0 );
					break;
			}
		}
		return $return;
	}

	/**
	 * Small work-around
	 * The imageTYPE function is not very suitable for OO code
	 * @return void
	 */
	private function storeImage()
	{
		$st = preg_replace( '/[^0-9]/', null, microtime( true ) * 10000 );
		$this->temp = SPLoader::path( 'tmp.img.' . $st, 'front', false, 'var', false );
		if ( !( SPLoader::dirPath( 'tmp.img', 'front', true ) ) ) {
			SPFs::mkdir( SPLoader::dirPath( 'tmp.img', 'front', false ) );
		}
		switch ( $this->type ) {
			case IMAGETYPE_GIF:
				imagegif( $this->image, $this->temp );
				break;
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				imagejpeg( $this->image, $this->temp, Sobi::Cfg( 'image.jpeg_quality', 100 ) );
				break;
			case IMAGETYPE_PNG:
				imagealphablending( $this->image, true );
				imagepng( $this->image, $this->temp, Sobi::Cfg( 'image.png_compression', 9 ) );
				break;
		}
		$this->_content = file_get_contents( $this->temp );
		if ( $this->image ) {
			imagedestroy( $this->image );
		}
	}

	/**
	 * @author Radek Suski
	 * @author Claudio F. images with transparent color are processed in the right way
	 * resampling image to adjusted size
	 * @param $img
	 * @return void
	 */
	private function transparency( &$img )
	{
		if ( !( $this->transparency ) ) {
			return true;
		}
		$index = imagecolortransparent( $img );
		/* If we have a specific transparent color */
		if ( $index >= 0 ) {
			/* Get the original image's transparent color's RGB values */
			$transparency = imagecolorsforindex( $img, $index );
			/* Allocate the same color in the new image resource */
			$index = imagecolorallocate( $this->image, $transparency[ 'red' ], $transparency[ 'green' ], $transparency[ 'blue' ] );
			/* Completely fill the background of the new image with allocated color. */
			imagefill( $this->image, 0, 0, $index );
			/* Set the background color for new image to transparent */
			imagecolortransparent( $this->image, $index );
		}
		/** Mon, Aug 3, 2015 09:32:49
		 * It doesn't make much sense. If the uploaded PNG file is not transparent in original
		 * why do we need to add the transparency?
		 * Let's see if we screw something with that
		 *
		 * * Fri, Aug 26, 2016 10:03:04
		 * Bringing it back because there is no real way to detect transparency in an image
		 * We are going to use https://github.com/kosinix/grafika in next version probably
		 */
		/* Always make a transparent background color for PNGs that don't have one allocated already */
		else {
			/* Turn off transparency blending (temporarily) */
			imagealphablending( $this->image, false );
			/* Create a new transparent color for image */
			$color = imagecolorallocatealpha( $this->image, 0, 0, 0, 127 );
			/* Completely fill the background of the new image with allocated color. */
			imagefill( $this->image, 0, 0, $color );
			/* Restore transparency blending */
			imagesavealpha( $this->image, true );
		}
	}

	/**
	 * @param $imgType
	 * @return mixed
	 * @throws SPException
	 */
	protected function createImage( $imgType )
	{
		/* create image object from the current file */
		if ( isset( self::$imgFunctions[ $imgType ] ) ) {
			$function = self::$imgFunctions[ $imgType ];
			$currentImg = $function( $this->_filename );
		}
		if ( !isset( self::$imgFunctions[ $imgType ] ) || !isset( $currentImg ) ) {
			throw new SPException( SPLang::e( 'CREATE_IMAGE_MISSING_HANDLER', $this->_filename, $imgType ) );
		}
		return $currentImg;
	}
}
