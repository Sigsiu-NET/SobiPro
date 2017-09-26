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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

use Sobi\FileSystem\FileSystem;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 08-Jul-2008 9:43:25 AM
 */
class SPJoomlaCMSHelper
{
	/**
	 * @return SPJoomlaCMSHelper
	 */
	public static function & getInstance()
	{
		static $SPCMSHelper = false;
		if ( !$SPCMSHelper || !( $SPCMSHelper instanceof self ) ) {
			$SPCMSHelper = new self();
		}
		return $SPCMSHelper;
	}

	/**
	 * Return SobiPro version
	 * @param bool $str
	 * @return array or string
	 */
	public static function myVersion( $str = false )
	{
		static $ver = [];
		if ( !isset( $ver[ $str ] ) ) {
			//$def = SOBI_CMS == 'joomla15' ? 'sobipro.xml' : 'com_sobipro.xml';
			$def = 'com_sobipro.xml';
			$doc = new DOMDocument();
			$doc->load( FileSystem::FixPath( JPATH_ADMINISTRATOR . '/components/com_sobipro/' . $def ) );
			if ( $str ) {
				$ver[ $str ] = $doc->getElementsByTagName( 'version' )->item( 0 )->nodeValue;
				$codename = $doc->getElementsByTagName( 'codename' )->item( 0 )->nodeValue;
				$ver[ $str ] = $ver[ $str ] . ' [ ' . $codename . ' ]';
			}
			else {
				$v = explode( '.', $doc->getElementsByTagName( 'version_number' )->item( 0 )->nodeValue );
				$ver[ $str ] = [ 'major' => $v[ 0 ], 'minor' => ( isset( $v[ 1 ] ) ? $v[ 1 ] : 0 ), 'build' => ( isset( $v[ 2 ] ) ? $v[ 2 ] : 0 ), 'rev' => ( isset( $v[ 3 ] ) ? $v[ 3 ] : 0 ) ];
			}
		}
		return $ver[ $str ];
	}

	/**
	 * Return min or recommend Joomla! version
	 * @param bool $recommended
	 * @return array
	 */
	public static function minCmsVersion( $recommended = false )
	{
		return $recommended ? [ 'major' => 1, 'minor' => 5, 'build' => 26, 'rev' => 0 ] : [ 'major' => 1, 'minor' => 5, 'build' => 20, 'rev' => 0 ];
	}

	/**
	 * Returns specified Joomla! configuration setting
	 * @param string $setting
	 * @return string
	 */
	public static function cmsSetting( $setting )
	{
		static $cfg;
		if ( !$cfg ) {
			$cfg = new JConfig();
		}
		switch ( $setting ) {
			case 'charset':
				$r = JFactory::getDocument()->getCharset();
				break;
			default:
				$r = isset( $cfg->$setting ) ? $cfg->$setting : false;
				break;
		}
		return $r;
	}

	/**
	 * Returns Joomla! version
	 * @param bool $version
	 * @return array
	 */
	public static function cmsVersion( $version = null )
	{
		if ( ( $version ) && ( $version != 'Joomla 1.5' ) ) {
			return 'Joomla 1.5';
		}
		$version = new JVersion();
		$v = explode( '.', $version->RELEASE );
		return [ 'major' => $v[ 0 ], 'minor' => $v[ 1 ], 'build' => $version->DEV_LEVEL, 'rev' => 0 ];
	}

	/**
	 * @param array $files
	 * @return bool
	 */
	public function installerFile( $files )
	{
		foreach ( $files as $file ) {
			$def = SPFactory::LoadXML( $file, LIBXML_NOERROR );
			if ( in_array( trim( $def->documentElement->tagName ), [ 'install', 'extension' ] ) ) {
				if ( $def->getElementsByTagName( 'SobiPro' )->length ) {
					if ( in_array( trim( $def->documentElement->getAttribute( 'type' ) ), [ 'language', 'module', 'plugin', 'component' ] ) ) {
						return $def;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Called from App installer if the core installer has no handler for this type
	 * @todo
	 * @param $files
	 * @param $dir
	 * @return string
	 */
	public function install( $files, $dir )
	{
		return SPFactory::Instance( 'cms.base.installer' )
				->install( self::installerFile( $files ), $files, $dir );
	}

	/**
	 * Install language file
	 * @param string $lang
	 * @param bool $force
	 * @param bool $move
	 * @return array
	 */
	public static function installLang( $lang, $force = true, $move = false )
	{
		$log = [];
		if ( count( $lang ) ) {
			foreach ( $lang as $language => $files ) {
				$language = str_replace( '_', '-', $language );
				if ( count( $files ) ) {
					foreach ( $files as $file ) {
						$target = $file[ 'adm' ] ? implode( '/', [ JPATH_ADMINISTRATOR, 'language', $language ] ) : implode( '/', [ SOBI_ROOT, 'language', $language ] );
						if ( $force || SPFs::exists( $target ) ) {
							$iFile = $target . '/' . trim( $file[ 'name' ] );
							$log[] = $iFile;
							$move ? SPFs::move( Sobi::FixPath( $file[ 'path' ] ), $iFile ) : SPFs::copy( Sobi::FixPath( $file[ 'path' ] ), $iFile );
						}
					}
				}
			}
		}
		return $log;
	}

	/**
	 * @param $name
	 * @param $active
	 * @param $nouser
	 * @param $javascript
	 * @param $order
	 * @param $reg
	 * @return unknown_type
	 */
	public static function userSelect( $name, $active, $nouser = 0, $javascript = null, $order = 'name', $reg = 0 )
	{
		return JHTML::_( 'list.users', $name, $active, $nouser, $javascript, $order, $reg );
	}

	public static function availableLanguages( $list = false )
	{
		$langs = JFactory::getLanguage()->getKnownLanguages();
		if ( $list ) {
			$llist = [];
			foreach ( $langs as $i => $value ) {
				$llist[ $i ] = $value[ 'name' ];
			}
			return $llist;
		}
		return $langs;
	}

	/**
	 * Returns Joomla! depend additional path with alternative templates loaction
	 * @return array
	 */
	public function templatesPath()
	{
		SPLoader::loadClass( 'base.fs.directory_iterator' );
		$jTemplates = new SPDirectoryIterator( SPLoader::dirPath( 'templates', 'root', true ) );
		$tr = [];
		foreach ( $jTemplates as $template ) {
			if ( $template->isDot() ) {
				continue;
			}
			if ( $template->isDir() ) {
				if ( file_exists( implode( '/', [ $template->getPathname(), 'html', 'com_sobipro' ] ) ) && file_exists( implode( '/', [ $template->getPathname(), 'templateDetails.xml' ] ) ) ) {
					$data = new DOMDocument( '1.0', 'utf-8' );
					$data->load( Sobi::FixPath( $template->getPathname() . '/templateDetails.xml' ) );
					$name = $data->getElementsByTagName( 'name' )->item( 0 )->nodeValue;
					$tr[ $name ] = Sobi::FixPath( implode( DS, [ $template->getPathname(), 'html', 'com_sobipro' ] ) );
				}
			}
		}
		return [ 'name' => Sobi::Txt( 'TP.TEMPLATES_OVERRIDE' ), 'icon' => Sobi::Cfg( 'live_site' ) . 'media/sobipro/tree/joomla.gif', 'data' => $tr ];
	}


	public function getLanguages()
	{
		return null;
	}

	/**
	 * This method is adding new tasks to the XML files used for Joomla! menu definition
	 * @param $tasks - list of tasks to add
	 * @param $controlString - a single string to check for if it has not been already added
	 * @param $languageFile - language file where the translation for tasks can be found
	 * @param array $additionalStrings - optional list of additional strings to add to the sys ini files
	 * @param bool $force - force even if it has been already done - forcing only language files redefinition
	 * @return void
	 */
	public function updateXMLDefinition( $tasks, $controlString, $languageFile, $additionalStrings = [], $force = false )
	{
		$file = SPLoader::translatePath( 'metadata', 'front', true, 'xml' );
		$run = false;
		$strings = [];
		foreach ( $tasks as $label ) {
			$strings[] = $label;
			$strings[] = $label . '_EXPL';
		}
		if ( count( $additionalStrings ) ) {
			foreach ( $additionalStrings as $additionalString ) {
				$strings[] = $additionalString;
			}
		}
		/** check if it hasn't been already added */
		if ( !( strstr( SPFs::read( $file ), $controlString ) ) ) {
			$run = true;
			$doc = new DOMDocument();
			$doc->load( $file );
			$options = $doc->getElementsByTagName( 'options' )->item( 0 );
			foreach ( $tasks as $task => $label ) {
				$node = $doc->createElement( 'option' );
				$attribute = $doc->createAttribute( 'value' );
				$attribute->value = $task;
				$node->appendChild( $attribute );
				$attribute = $doc->createAttribute( 'name' );
				$attribute->value = 'SP.' . $label;
				$node->appendChild( $attribute );
				$attribute = $doc->createAttribute( 'msg' );
				$attribute->value = 'SP.' . $label . '_EXPL';
				$node->appendChild( $attribute );
				$options->appendChild( $node );
			}
			$doc->save( $file );
		}
		if ( $run || $force ) {
			$dirPath = SPLoader::dirPath( 'administrator.language', 'root' );
			/** @var SPDirectory $dir */
			$dir = SPFactory::Instance( 'base.fs.directory', $dirPath );
			$files = $dir->searchFile( 'com_sobipro.sys.ini', false, 2 );
			$default = [];
			$defaultLangDir = SPLoader::dirPath( "language.en-GB", 'root', true );
			$defaultLang = parse_ini_file( $defaultLangDir . 'en-GB.' . $languageFile . '.ini' );
			foreach ( $strings as $string ) {
				$default[ 'SP.' . $string ] = $defaultLang[ 'SP.' . $string ];
			}
			/** @var SPFile $file */
			$file = null;
			foreach ( $files as $file ) {
				$fileName = $file->getFileName();
				list( $language ) = explode( '.', $fileName );
				$nativeLangDir = SPLoader::dirPath( "language.{$language}", 'root', true );
				$nativeStrings = [];
				if ( $nativeLangDir ) {
					$nativeLangFile = $nativeLangDir . $language . '.' . $languageFile . '.ini';
					if ( file_exists( $nativeLangFile ) ) {
						$nativeLang = @parse_ini_file( $nativeLangFile );
						foreach ( $strings as $string ) {
							if ( isset( $nativeLang[ 'SP.' . $string ] ) ) {
								$nativeStrings[ 'SP.' . $string ] = $nativeLang[ 'SP.' . $string ];
							}
						}
					}
				}
				$add = null;
				foreach ( $strings as $string ) {
					if ( isset( $nativeStrings[ 'SP.' . $string ] ) ) {
						$add .= "\nSP.{$string}=\"{$nativeStrings['SP.' . $string]}\"";
					}
					else {
						$add .= "\nSP.{$string}=\"{$default['SP.' . $string]}\"";
					}
				}
				$add .= "\n";
				$content = SPFs::read( $file->getPathname() );
				$add = $content . $add;
				SPFs::write( $file->getPathname(), $add );
			}
		}
	}
}
