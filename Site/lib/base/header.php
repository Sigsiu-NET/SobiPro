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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
 */

use Sobi\Input\Input;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 08-Jul-2008 9:43:26 AM
 */
final class SPHeader
{

	/*** @var array */
	private $head = [];
	/*** @var array */
	private $css = [];
	/*** @var array */
	private $cssFiles = [];
	/*** @var array */
	private $js = [];
	/*** @var array */
	private $links = [];
	/*** @var array */
	private $jsFiles = [];
	/*** @var array */
	private $author = [];
	/*** @var array */
	private $title = [];
	/*** @var array */
	private $robots = [];
	/*** @var array */
	private $description = [];
	/*** @var array */
	private $keywords = [];
	/*** @var array */
	private $raw = [];
	/*** @var int */
	private $count = 0;
	/*** @var array */
	private $_cache = [ 'js' => [], 'css' => [] ];
	/** @var array */
	private $_store = [];
	/** @var array */
	private $_checksums = [];

	/**
	 * @return SPHeader
	 */
	public static function & getInstance()
	{
		static $head = null;
		if ( !$head || !( $head instanceof SPHeader ) ) {
			$head = new SPHeader();
		}

		return $head;
	}

	public function & initBase( $adm = false )
	{
		if ( $adm ) {
//			$this->addCssFile( [ 'bootstrap.bootstrap', 'admicons', 'adm.sobipro' ] )
			$this->addCssFile( [ 'adm.sobiadmin' ] )
				->addJsFile( [ 'sobipro', 'adm.sobipro', 'jquery', 'jqnc', 'bootstrap', 'adm.interface', 'adm.responsive-tabs' ] );
		}
		else {
			if ( !defined( 'SOBIPRO_ADM' ) ) {
				$this->addCssFile( [ 'sobipro' ] );
			}
			$this->addJsFile( [ 'sobipro', 'jquery', 'jqnc' ] );
			if ( Sobi::Cfg( 'template.bootstrap3-load', true ) && !defined( 'SOBIPRO_ADM' ) ) {
				if ( Sobi::Cfg( 'template.bootstrap3-source', true ) ) { //true=local, false=CDN
					$this->addCssFile( 'b3bootstrap.b3bootstrap' )
						->addJsFile( 'b3bootstrap' );
				}
				else {
					$this->addHeadLink( Sobi::Cfg( 'template.bs3_css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' ), null, null, 'stylesheet' )
						->addJsUrl( Sobi::Cfg( 'template.bs3_js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' ) );
				}
			}
			else {
				if ( !defined( 'SOBIPRO_ADM' ) ) {
					$this->addCssFile( 'bootstrap.bootstrap' );
				}
				$this->addJsFile( 'bootstrap' );
			}
			$fonts = Sobi::Cfg( 'template.icon_fonts_arr', [] );
			if ( count( $fonts ) ) {
				foreach ( $fonts as $font ) {
					if ( $font == 'font-awesome-3-local' ) {
						$this->addCssFile( 'sobifont' );
					}
					elseif ( Sobi::Cfg( 'icon-fonts.' . $font ) ) {
						$this->addHeadLink( Sobi::Cfg( 'icon-fonts.' . $font ), null, null, 'stylesheet' );
					}
				}
			}
		}
		if ( SOBI_CMS != 'joomla3' ) {
			$this->addJsFile( 'jquery-migrate' );
		}

		return $this;
	}

	protected function store( $args, $id )
	{
		if ( isset( $args[ 'this' ] ) ) {
			unset( $args[ 'this' ] );
		}
		$this->_store[ $id ][] = $args;

	}

	/**
	 * Add raw code to the site header
	 *
	 * @param string $html
	 *
	 * @return SPHeader
	 */
	public function & add( $html )
	{
		$checksum = md5( $html );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->raw[ ++$this->count ] = $html;
			$this->store( get_defined_vars(), __FUNCTION__ );
		}

		return $this;
	}

	/**
	 * @deprecated @see SPHeader::meta
	 *
	 * @param string $name
	 * @param string $content
	 * @param array $attributes
	 *
	 * @return SPHeader
	 */
	public function & addMeta( $name, $content, $attributes = [] )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$custom = null;
			if ( count( $attributes ) ) {
				foreach ( $attributes as $attribute => $value ) {
					$custom .= $attribute . '="' . $value . '"';
				}
			}
			if ( strlen( $name ) ) {
				$name = " name=\"{$name}\" ";
			}
			$this->raw[ ++$this->count ] = "<meta{$name} content=\"{$content}\" {$custom}/>";
		}

		return $this;
	}

	/**
	 * Add JavaScript code to the site header
	 *
	 * @param $content
	 * @param string $name
	 * @param array $attributes
	 *
	 * @internal param string $js
	 * @return SPHeader
	 */
	public function & meta( $content, $name = null, $attributes = [] )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$custom = null;
			if ( strlen( $name ) ) {
				$name = "name=\"{$name}\" ";
			}
			if ( count( $attributes ) ) {
				foreach ( $attributes as $attr => $value ) {
					$custom .= $attr . '="' . $value . '"';
				}
			}
			$this->raw[ ++$this->count ] = "<meta {$name}content=\"{$content}\" {$custom}/>";
		}

		return $this;
	}

	/**
	 * Add JavaScript code to the site header
	 *
	 * @param string $js
	 *
	 * @return SPHeader
	 */
	public function & addJsCode( $js )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$this->js[ ++$this->count ] = $js;
		}

		return $this;
	}

	/**
	 * Add JavaScript file to the site header
	 *
	 * @param $script
	 * @param bool $adm
	 * @param string $params
	 * @param bool $force
	 * @param string $ext
	 *
	 * @return SPHeader
	 */
	public function & addJsFile( $script, $adm = false, $params = null, $force = false, $ext = 'js' )
	{
		if ( is_array( $script ) && count( $script ) ) {
			foreach ( $script as $f ) {
				$this->addJsFile( $f, $adm, $params, $force, $ext, $params );
			}
		}
		else {
			$checksum = md5( json_encode( get_defined_vars() ) );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				if ( SOBI_CMS == 'joomla3' ) {
					if ( $script == 'jquery' ) {
						JHtml::_( 'jquery.framework' );

						return $this;
					}
					if ( $script == 'bootstrap' ) {
						JHtml::_( 'bootstrap.framework' );

						return $this;
					}
				}
				$jsFile = SPLoader::JsFile( $script, $adm, true, false, $ext );
				if ( $jsFile ) {
					$override = false;
					$index = ++$this->count;
					// if this is a template JavaScript file - ensure it will be loaded after all others JavaScript files
					if ( Sobi::Reg( 'current_template' ) && ( strstr( dirname( $jsFile ), Sobi::Reg( 'current_template' ) ) ) ) {
						$index *= 100;
					}
					if (
						/* If there is already template defined */
							Sobi::Reg( 'current_template' )
							&& /* and we are NOT including js file from the template  */
							!( strstr( dirname( $jsFile ), Sobi::Reg( 'current_template' ) ) )
							&& /* but there is such file (with the same name) in the template package  */
							SPFs::exists( Sobi::Reg( 'current_template' ) . '/js/' . basename( $jsFile ) )
							&& !( strstr( dirname( $jsFile ), 'templates' ) )
					) {
						$jsFile = explode( '.', basename( $jsFile ) );
						$ext = $jsFile[ count( $jsFile ) - 1 ];
						unset( $jsFile[ count( $jsFile ) - 1 ] );
						$f = implode( '.', $jsFile );
						$jsFile = Sobi::FixPath( SPLoader::JsFile( 'absolute.' . Sobi::Reg( 'current_template' ) . '/js/' . $f, $adm, true, true, $ext ) );
						$override = true;
						$index *= 100;
					}
					else {
						$jsFile = SPLoader::JsFile( $script, $adm, true, true, $ext );
					}
					if ( Sobi::Cfg( 'cache.include_js_files', false ) && !( $params || $force || $adm || defined( 'SOBIPRO_ADM' ) ) ) {
						if ( !( $override ) ) {
							$jsFile = SPLoader::JsFile( $script, $adm, true, false, $ext );
						}
						if ( !in_array( $jsFile, $this->_cache[ 'js' ] ) || $force ) {
							$this->_cache[ 'js' ][ $index ] = $jsFile;
							ksort( $this->_cache[ 'js' ] );
						}
					}
					else {
						$params = $params ? '?' . $params : null;
						$file = "\n<script type=\"text/javascript\" src=\"{$jsFile}{$params}\"></script>";
						if ( !in_array( $file, $this->jsFiles ) || $force ) {
							$this->jsFiles[ $index ] = $file;
							ksort( $this->jsFiles );
						}
					}
					if ( $script == 'jquery' ) {
						$this->addJsFile( 'jqnc' );
					}
				}
				else {
					$file = SPLoader::JsFile( $script, $adm, false, true, $ext );
					Sobi::Error( 'add_js_file', SPLang::e( 'FILE_DOES_NOT_EXIST', $file ), SPC::NOTICE, 0, __LINE__, __CLASS__ );
				}
			}
		}

		return $this;
	}

	/**
	 * Add external JavaScript file to the site header
	 *
	 * @param string $file
	 * @param string $params
	 *
	 * @return SPHeader
	 */
	public function & addJsUrl( $file, $params = null )
	{
		if ( is_array( $file ) && count( $file ) ) {
			foreach ( $file as $f ) {
				$this->addJsUrl( $f );
			}
		}
		else {
			$checksum = md5( json_encode( get_defined_vars() ) );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				$params = $params ? '?' . $params : null;
				$file = "\n<script type=\"text/javascript\" src=\"{$file}{$params}\"></script>";
				if ( !in_array( $file, $this->jsFiles ) ) {
					$this->jsFiles[ ++$this->count ] = $file;
				}
			}
		}

		return $this;
	}

	/**
	 * Creates temporary (variable) JavaScript file
	 *
	 * @param string $script
	 * @param string $id
	 * @param string $params
	 * @param bool $adm
	 *
	 * @return SPHeader
	 */
	public function & addJsVarFile( $script, $id, $params, $adm = false )
	{
		$this->store( get_defined_vars(), __FUNCTION__ );
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$varFile = SPLoader::translatePath( "var.js.{$script}_{$id}", 'front', true, 'js' );
			if ( !$varFile ) {
				$file = SPLoader::JsFile( $script, $adm, true, false );
				if ( $file ) {
					SPLoader::loadClass( 'base.fs.file' );
					$file = new SPFile( $file );
					$fc =& $file->read();
					foreach ( $params as $k => $v ) {
						$fc = str_replace( "__{$k}__", $v, $fc );
					}
					$fc = str_replace( '__CREATED__', date( SPFactory::config()->key( 'date.log_format', 'D M j G:i:s T Y' ) ), $fc );
					$varFile = SPLoader::translatePath( "var.js.{$script}_{$id}", 'front', false, 'js' );
					$file->saveAs( $varFile );
				}
				else {
					Sobi::Error( __FUNCTION__, SPLang::e( 'CANNOT_LOAD_FILE_AT', $file ), SPC::NOTICE, 0, __LINE__, __FILE__ );
				}
			}
			if ( Sobi::Cfg( 'cache.include_js_files', false ) && !( $adm || defined( 'SOBIPRO_ADM' ) ) ) {
				$this->_cache[ 'js' ][ ++$this->count ] = $varFile;
			}
			else {
				$varFile = str_replace( SOBI_ROOT, SPFactory::config()->get( 'live_site' ), $varFile );
				$varFile = str_replace( '\\', '/', $varFile );
				$varFile = preg_replace( '|(\w)(//)(\w)|', '$1/$3', $varFile );
				$varFile = "\n<script type=\"text/javascript\" src=\"{$varFile}\"></script>";
				if ( !in_array( $varFile, $this->jsFiles ) ) {
					$this->jsFiles[ ++$this->count ] = $varFile;
				}
			}
		}

		return $this;
	}

	/**
	 * Add CSS code to the site header
	 *
	 * @param string $css
	 *
	 * @return SPHeader
	 */
	public function & addCSSCode( $css )
	{
		$checksum = md5( $css );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$this->css[ ++$this->count ] = $css;
		}

		return $this;
	}

	/**
	 * Add CSS file to the site header
	 *
	 * @param string $file file name
	 * @param bool $adm
	 * @param null $media
	 * @param bool $force
	 * @param string $ext
	 * @param string $params
	 *
	 * @return SPHeader
	 */
	public function & addCssFile( $file, $adm = false, $media = null, $force = false, $ext = 'css', $params = null )
	{
		if ( is_array( $file ) && count( $file ) ) {
			foreach ( $file as $f ) {
				$this->addCssFile( $f, $adm, $media, $force, $ext, $params );
			}
		}
		else {
			$checksum = md5( json_encode( get_defined_vars() ) );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				$cssFile = SPLoader::CssFile( $file, $adm, true, false, $ext );
				$index = ++$this->count;
				// if this is a template CSS file - ensure it will be loaded after all others CSS files
				if ( Sobi::Reg( 'current_template' ) && ( strstr( dirname( $cssFile ), Sobi::Reg( 'current_template' ) ) ) ) {
					$index *= 100;
				}
				if ( $file == 'bootstrap.bootstrap' ) {
					if ( !( Sobi::Cfg( 'template.bootstrap-disabled' ) ) || defined( 'SOBIPRO_ADM' ) ) {
						/** we want bootstrap loaded as the very first because we have to override some things */
						$index = -100;
					}
					else {
						/** Not nice but it's just easier like this :/ */
						return $this;
					}
				}
				elseif ( $file == 'icons' && !( defined( 'SOBIPRO_ADM' ) ) ) {
					$fonts = Sobi::Cfg( 'template.icon_fonts_arr', [] );
					if ( !( in_array( 'font-awesome-3-local', $fonts ) ) ) {
						return $this;
					}
				}
				if ( $cssFile ) {
					$override = false;
					if (
						/* If there is already template defined */
							Sobi::Reg( 'current_template' )
							&& /* and we are NOT including css file from the template  */
							!( strstr( dirname( $cssFile ), Sobi::Reg( 'current_template' ) ) )
							&& /* but there is such file (with the same name) in the template package  */
							SPFs::exists( Sobi::Reg( 'current_template' ) . '/css/' . basename( $cssFile ) )
							&& !( strstr( dirname( $cssFile ), 'templates' ) )
					) {
						$cssFile = explode( '.', basename( $cssFile ) );
						$ext = $cssFile[ count( $cssFile ) - 1 ];
						unset( $cssFile[ count( $cssFile ) - 1 ] );
						$f = implode( '.', $cssFile );
						$cssFile = SPLoader::CssFile( 'absolute.' . Sobi::Reg( 'current_template' ) . '/css/' . $f, $adm, true, !( Sobi::Cfg( 'cache.include_css_files', false ) ), $ext );
						$override = true;
						$index *= 100;
					}
					else {
						$cssFile = SPLoader::CssFile( $file, $adm, true, true, $ext );
					}
					if ( Sobi::Cfg( 'cache.include_css_files', false ) && !( $params || $force || $adm || defined( 'SOBIPRO_ADM' ) ) ) {
						if ( !( $override ) ) {
							$cssFile = SPLoader::CssFile( $file, $adm, true, false, $ext );
						}
						if ( !in_array( $cssFile, $this->_cache[ 'css' ] ) || $force ) {
							$this->_cache[ 'css' ][ $index ] = $cssFile;
							ksort( $this->_cache[ 'css' ] );
						}
					}
					else {
						$params = $params ? '?' . $params : null;
						$media = $media ? "media=\"{$media}\"" : null;
						$file = "<link rel=\"stylesheet\" href=\"{$cssFile}{$params}\" type=\"text/css\" {$media} />";
						if ( !in_array( $file, $this->cssFiles ) || $force ) {
							$this->cssFiles[ $index ] = $file;
							ksort( $this->cssFiles );
						}
					}
				}
				else {
					$file = SPLoader::CssFile( $file, $adm, false, false, $ext );
					Sobi::Error( 'add_css_file', SPLang::e( 'FILE_DOES_NOT_EXIST', $file ), SPC::NOTICE, 0, __LINE__, __CLASS__ );
				}
			}
		}

		return $this;
	}

	/**
	 * Add alternate link to the site header
	 *
	 * @param string $href
	 * @param string $type
	 * @param string $title
	 * @param string $rel
	 * @param string $relType
	 * @param array $params
	 *
	 * @return SPHeader
	 */
	public function & addHeadLink( $href, $type = null, $title = null, $rel = 'alternate', $relType = 'rel', $params = null )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$title = $title ? " title=\"{$title}\" " : null;
			if ( $params && count( $params ) ) {
				$arr = SPLoader::loadClass( 'types.array' );
				$p = new $arr();
				$params = $p->toString( $params );
			}
			if ( $type ) {
				$type = "type=\"{$type}\" ";
			}
			$href = preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $href );
			$title = preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $title );
			$this->links[] = "<link href=\"{$href}\" {$relType}=\"{$rel}\" {$type}{$params}{$title}/>";
			$this->links = array_unique( $this->links );
		}

		return $this;
	}

	public function & addCanonical( $url )
	{
		$checksum = md5( $url );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );

			return $this->addHeadLink( $url, null, null, 'canonical' );
		}
	}

	/**
	 * Set Site title
	 *
	 * @param string $title
	 * @param array $site
	 *
	 * @return SPHeader
	 */
	public function & addTitle( $title, $site = [] )
	{
		if ( count( $site ) && $site[ 0 ] > 1 ) {
			if ( !( is_array( $title ) ) ) {
				$title = [ $title ];
			}
			if ( $site[ 1 ] > 1 ) { // no page counter when on page 1
				$title[] = Sobi::Txt( 'SITES_COUNTER', $site[ 1 ], $site[ 0 ] );
			}
		}
		if ( is_array( $title ) ) {
			foreach ( $title as $segment ) {
				$this->addTitle( $segment );
			}
		}
		else {
			$checksum = md5( $title );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$args = get_defined_vars();
				unset( $args[ 'site' ] );
				$this->store( $args, __FUNCTION__ );
				$this->title[] = $title;
			}
		}

		return $this;
	}

	/**
	 * Add meta description to the site header
	 *
	 * @param string $desc
	 *
	 * @return SPHeader
	 */
	public function & addDescription( $desc )
	{
		if ( is_string( $desc ) ) {
			$checksum = md5( $desc );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				if ( strlen( $desc ) ) {
					$this->description[] = strip_tags( str_replace( '"', "'", SPLang::entities( $desc, true ) ) );
				}
			}
		}

		return $this;
	}

	/**
	 * Set Site title
	 *
	 * @param string $title
	 *
	 * @return SPHeader
	 */
	public function & setTitle( $title )
	{
		if ( defined( 'SOBIPRO_ADM' ) ) {
			SPFactory::mainframe()->setTitle( SPLang::clean( $title ) );
		}
		if ( is_array( $title ) ) {
			$this->title = $title;
		}
		else {
			$this->title = [ SPLang::clean( $title ) ];
		}

		return $this;
	}

	/**
	 * Gets meta keys and met description from the given object
	 *  and adds to the site header
	 *
	 * @param SPDBObject $obj
	 *
	 * @return SPHeader
	 */
	public function & objMeta( $obj )
	{
		$task = Input::Task();
		if ( Sobi::Cfg( 'meta.always_add_section' ) ) {
			if ( ( ( strpos( $task, 'search' ) != false ) ) || ( ( $obj->get( 'oType' ) != 'section' ) ) ) {
				$this->objMeta( SPFactory::currentSection() );
			}
		}
		if ( $obj->get( 'metaDesc' ) ) {
			$separator = Sobi::Cfg( 'meta.separator', '.' );
			$desc = $obj->get( 'metaDesc' );
			$desc .= $separator;
			$this->addDescription( $desc );
		}
		if ( $obj->get( 'metaKeys' ) ) {
			$this->addKeyword( $obj->get( 'metaKeys' ) );
		}
		if ( $obj->get( 'metaAuthor' ) ) {
			$this->addAuthor( $obj->get( 'metaAuthor' ) );
		}
		if ( $obj->get( 'metaRobots' ) ) {
			$this->addRobots( $obj->get( 'metaRobots' ) );
		}
		if ( $obj->get( 'oType' ) == 'entry' || $obj->get( 'oType' ) == 'category' ) {
			$fields = $obj->getFields();
			if ( count( $fields ) ) {
				$fields = array_reverse( $fields );
				foreach ( $fields as $field ) {
					$separator = $field->get( 'metaSeparator' );
					$desc = $field->metaDesc();
					if ( is_string( $desc ) && strlen( $desc ) ) {
						$desc .= $separator;
					}
					$this->addDescription( $desc );
					$this->addKeyword( $field->metaKeys() );
				}
			}
		}

		return $this;
	}

	public function addRobots( $robots )
	{
		$this->robots = [ $robots ];
	}

	public function addAuthor( $author )
	{
		$checksum = md5( $author );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$this->author[] = $author;
		}
	}

	/**
	 * Add a keywords to the site header
	 *
	 * @param $keys
	 *
	 * @internal param string $key
	 * @return SPHeader
	 */
	public function & addKeyword( $keys )
	{
		if ( is_string( $keys ) ) {
			$checksum = md5( $keys );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				if ( strlen( $keys ) ) {
					$keys = explode( Sobi::Cfg( 'string.meta_keys_separator', ',' ), $keys );
					if ( !empty( $keys ) ) {
						$this->count++;
						foreach ( $keys as $key ) {
							$this->keywords[] = strip_tags( trim( SPLang::entities( $key, true ) ) );
						}
					}
				}
			}
		}

		return $this;
	}

	public function getData( $index )
	{
		if ( isset( $this->$index ) ) {
			return $this->$index;
		}
	}

	public function & reset()
	{
		$this->keywords = [];
		$this->author = [];
		$this->robots = [];
		$this->description = [];
		$this->cssFiles = [];
		$this->jsFiles = [];
		$this->css = [];
		$this->js = [];
		$this->raw = [];
		$this->head = [];
		$this->_store = [];

		return $this;
	}

	private function _cssFiles()
	{
		if ( Sobi::Cfg( 'cache.include_css_files', false ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
			if ( count( $this->_cache[ 'css' ] ) ) {
				/* * create the right checksum */
				$check = [ 'section' => Sobi::Section() ];
				foreach ( $this->_cache[ 'css' ] as $file ) {
					if ( file_exists( $file ) ) {
						$check[ $file ] = filemtime( $file );
					}
				}
				$check = md5( serialize( $check ) );
				if ( !( SPFs::exists( SOBI_PATH . "/var/css/{$check}.css" ) ) ) {
					$cssContent = "/* Created at: " . date( SPFactory::config()->key( 'date.log_format', 'D M j G:i:s T Y' ) ) . " */\n";
					foreach ( $this->_cache[ 'css' ] as $file ) {
						$fName = str_replace( Sobi::FixPath( SOBI_ROOT ), null, $file );
						$cssContent .= "/** ==== File: {$fName} ==== */\n";
						$fc = SPFs::read( $file );
						preg_match_all( '/[^\(]*url\(([^\)]*)/', $fc, $matches );
						// we have to replace url relative path
						$fPath = str_replace( Sobi::FixPath( SOBI_ROOT . '/' ), SPFactory::config()->get( 'live_site' ), $file );
						$fPath = str_replace( '\\', '/', $fPath );
						$fPath = explode( '/', $fPath );
						if ( count( $matches[ 1 ] ) ) {
							foreach ( $matches[ 1 ] as $url ) {
								// if it is already absolute - skip or from root
								if ( preg_match( '|http(s)?://|', $url ) || preg_match( '|url\(["\s]*/|', $url ) ) {
									continue;
								}
								elseif ( strpos( $url, '/' ) === 0 ) {
									continue;
								}
								$c = preg_match_all( '|\.\./|', $url, $c ) + 1;
								$tempFile = array_reverse( $fPath );
								for ( $i = 0; $i < $c; $i++ ) {
									unset( $tempFile[ $i ] );
								}
								$rPath = Sobi::FixPath( implode( '/', array_reverse( $tempFile ) ) );
								if ( $c > 1 ) {
									//WHY?!!
									//$realUrl = Sobi::FixPath( str_replace( '..', $rPath, $url ) );
									$realUrl = Sobi::FixPath( $rPath . '/' . str_replace( '../', null, $url ) );
								}
								else {
									$realUrl = Sobi::FixPath( $rPath . '/' . $url );
								}
								$realUrl = str_replace( [ '"', "'", ' ' ], null, $realUrl );
								$fc = str_replace( $url, $realUrl, $fc );
							}
						}
						// and add to content
						$cssContent .= $fc;
						$cssContent .= "\n";
					}
					SPFs::write( SOBI_PATH . "/var/css/{$check}.css", $cssContent );
				}
				$cfile = SPLoader::CssFile( 'front.var.css.' . $check, false, true, true );
				$this->cssFiles[ ++$this->count ] = "<link rel=\"stylesheet\" href=\"{$cfile}\" media=\"all\" type=\"text/css\" />";
			}
		}

		return $this->cssFiles;
	}

	private function _jsFiles()
	{
		if ( Sobi::Cfg( 'cache.include_js_files', false ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
			if ( count( $this->_cache[ 'js' ] ) ) {
				$compression = Sobi::Cfg( 'cache.compress_js', false );
				$comprLevel = Sobi::Cfg( 'cache.compress_level', 0 );
				$check = [ 'section' => Sobi::Section(), 'compress_level' => $comprLevel, 'compress_js' => $compression ];
				foreach ( $this->_cache[ 'js' ] as $file ) {
					$check[ $file ] = filemtime( $file );
				}
				$check = md5( serialize( $check ) );
				if ( !( SPFs::exists( SOBI_PATH . "/var/js/{$check}.js" ) ) ) {
					$noCompress = explode( ',', Sobi::Cfg( 'cache.js_compress_exceptions' ) );
					$jsContent = "/* Created at: " . date( SPFactory::config()->key( 'date.log_format', 'D M j G:i:s T Y' ) ) . " */\n";
					foreach ( $this->_cache[ 'js' ] as $file ) {
						$fName = str_replace( SOBI_ROOT, null, $file );
						$jsContent .= "// ==== File: {$fName} ==== \n";
						if ( $compression && !( in_array( basename( $file ), $noCompress ) ) ) {
							$compressor = SPFactory::Instance( 'env.jspacker', SPFs::read( $file ), $comprLevel, false, true );
							$jsContent .= $compressor->pack();
						}
						else {
							$jsContent .= SPFs::read( $file );
						}
						$jsContent .= ";\n";
					}
					SPFs::write( SOBI_PATH . "/var/js/{$check}.js", $jsContent );
				}
				$cfile = SPLoader::JsFile( 'front.var.js.' . $check, false, true, true );
				$this->jsFiles[ ++$this->count ] = "\n<script type=\"text/javascript\" src=\"{$cfile}\"></script>";
			}
		}

		return $this->jsFiles;
	}

	/**
	 * @deprecated
	 */
	public function send()
	{
	}

	/**
	 * Send the header via the mainframe interface
	 */
	public function sendHeader()
	{
		if ( count( $this->_store ) ) {
			if ( count( $this->js ) ) {
				$jsCode = null;
				foreach ( $this->js as $js ) {
					$jsCode .= "\n\t" . str_replace( "\n", "\n\t", $js );
				}
				$this->js = [ "\n<script type=\"text/javascript\">\n/*<![CDATA[*/{$jsCode}\n/*]]>*/\n</script>\n" ];
			}
			if ( count( $this->css ) ) {
				$cssCode = null;
				foreach ( $this->css as $css ) {
					$cssCode .= "\n\t" . str_replace( "\n", "\n\t", $css );
				}
				$this->css = [ "<style type=\"text/css\">\n{$cssCode}\n</style>" ];
			}
			// Thu, May 8, 2014 13:10:19 - changed order of meta keys and meta description
			// See #1231
			$this->head[ 'keywords' ] = array_reverse( $this->keywords );
			$this->head[ 'author' ] = $this->author;
			$this->head[ 'robots' ] = $this->robots;
			$this->head[ 'description' ] = array_reverse( $this->description );
			$this->head[ 'links' ] = $this->links;
			$this->head[ 'css' ] = $this->_cssFiles();
			$this->head[ 'js' ] = $this->_jsFiles();
			$this->head[ 'css' ] = array_merge( $this->head[ 'css' ], $this->css );
			$this->head[ 'js' ] = array_merge( $this->head[ 'js' ], $this->js );
			$this->head[ 'raw' ] = $this->raw;
			Sobi::Trigger( 'Header', 'Send', [ &$this->head ] );
			SPFactory::mainframe()->addHead( $this->head );
			if ( count( $this->title ) ) {
				SPFactory::mainframe()->setTitle( $this->title );
			}
			SPFactory::cache()->storeView( $this->_store );
			$this->reset();
		}
	}
}
