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

/**
 * @author Radek Suski
 * @version 1.0
 * @created 20-Jun-2009 19:56:57
 */
class SPJoomlaLang
{
	/**
	 * @var string
	 */
	protected $_domain = 'sobi_pro';
	/**
	 * @var string
	 */
	protected $_lang = null;
	/**
	 * @var string
	 */
	protected $_sDomain = null;
	/**
	 * @var bool
	 */
	protected $_loaded = false;
	/**
	 * @var string
	 */
	const defDomain = 'sobi_pro';
	/**
	 * @var string
	 */
	const defLang = 'en-GB';
	/**
	 * @var string
	 */
	const encoding = 'UTF-8';

	/**
	 * Translate given string
	 *
	 * @internal param string $message
	 * @internal param array $params
	 * @return string
	 */
	public static function _()
	{
		return self::getInstance()->_txt( func_get_args() );
	}

	/**
	 * Translate given string
	 *
	 * @internal param string $message
	 * @internal param array $params
	 * @return string
	 */
	protected function _txt()
	{
		if ( !( $this->_loaded ) ) {
			$this->_load();
		}
		$a = func_get_args();
		if ( is_array( $a[ 0 ] ) ) {
			$a = $a[ 0 ];
		}
		if ( ( strpos( $a[ 0 ], "'" ) !== false && strpos( $a[ 0 ], "'" ) == 0 ) ) {
			$a[ 0 ] = substr( substr( $a[ 0 ], 0, -1 ), 1 );
		}
		$in = $a[ 0 ];
		$over = $this->tplOverride( $a[ 0 ] );
		if ( !( $over ) ) {
			$a[ 0 ] = 'SP.' . $a[ 0 ];
			$m = call_user_func_array( [ 'JText', '_' ], [ $a[ 0 ] ] );
			if ( $m == $a[ 0 ] || $m == 'SP.' ) {
				$m = $in;
			}
			$test = $this->tplOverride( $m );
			if ( $test ) {
				$m = $test;
			}
		}
		else {
			$m = $a[ 0 ] = $over;
		}

		/* if there were some parameters */
		if ( count( $a ) > 1 ) {
			if ( is_array( $a[ 1 ] ) ) {
				foreach ( $a[ 1 ] as $k => $v ) {
					$m = str_replace( "var:[$k]", $v, $m );
				}
			}
			else {
				$a[ 0 ] = $m;
				$m = call_user_func_array( 'sprintf', $a );
			}
		}
		if ( strstr( $m, 'translate:' ) ) {
			$matches = [];
			preg_match( '/translate\:\[([a-zA-Z0-9\.\_\-]*)\]/', $m, $matches );
			$m = str_replace( $matches[ 0 ], $this->_txt( $matches[ 1 ], null, false ), $m );
		}
		if ( strstr( $m, '[JS]' ) || strstr( $in, '[JS]' ) ) {
			$m = str_replace( "\n", '\n', $m );
		}
		$m = str_replace( '_QQ_', '"', $m );
		return str_replace( [ '[JS]', '[MSG]', '[URL]' ], null, $m );
	}

	protected function tplOverride( $term )
	{
		if ( !( class_exists( 'Sobi' ) ) || !( Sobi::Section() ) ) {
			return false;
		}
		static $xdef = null;
		static $custom = false;
		static $lang = null;
		/* try this once */
		if ( !( $custom ) ) {
			if ( Sobi::Cfg( 'section.template' ) ) {
				$custom = true;
				$template = SPLoader::translatePath( 'usr.templates.' . Sobi::Cfg( 'section.template' ) . '.translation', 'front', true, 'xml' );
				/* if the template provide it */
				if ( $template ) {
					$xml = new DOMDocument();
					$xml->load( $template );
					$xdef = new DOMXPath( $xml );
				}
				$lang = Sobi::Lang( false );
			}
		}
		if ( $xdef instanceof DOMXPath ) {
			$term = strip_tags( preg_replace( '/[^a-z0-9\-\_\+\.\, ]/i', null, $term ) );
			/* case we had more params */
			/* yeah - neo the xpath master -- lovin' it ;) */
			$transNode = $xdef->query( "/translation/term[@value=\"{$term}\"]/value[@lang='{$lang}']" );
			if ( isset( $transNode->length ) && $transNode->length ) {
				return $transNode->item( 0 )->nodeValue;
			}
			else {
				$transNode = $xdef->query( "/translation/term[@value=\"{$term}\"]/value[@default='true']" );
				if ( isset( $transNode->length ) && $transNode->length ) {
					return $transNode->item( 0 )->nodeValue;
				}

			}
		}
		return false;
	}

	/**
	 * Removes slashes from string
	 * @param string $txt
	 * @return string
	 */
	public static function clean( $txt )
	{
		while ( strstr( $txt, "\'" ) || strstr( $txt, '\"' ) || strstr( $txt, '\\\\' ) ) {
			$txt = stripslashes( $txt );
		}
		return $txt;
	}

	/**
	 * Create JS friendly script
	 * @param string $txt
	 * @return string
	 */
	public static function js( $txt )
	{
		return addslashes( $txt );
	}

	/**
	 * Error messages
	 *
	 * @return string
	 */
	public static function e()
	{
		static $loaded = false;
		if ( !( $loaded ) ) {
			self::getInstance()->_eload();
		}
		$a = func_get_args();
		return call_user_func_array( [ self::getInstance(), '_txt' ], $a );
	}

	protected function _eload()
	{
		if ( $this->_lang != 'en-GB' && Sobi::Cfg( 'lang.engb_preload', true ) ) {
			JFactory::getLanguage()->load( 'com_sobipro.err', JPATH_SITE, 'en-GB' );
		}
		JFactory::getLanguage()->load( 'com_sobipro.err', JPATH_SITE );
	}

	protected function _load()
	{
		/* load default language file */
		if ( $this->_lang != 'en-GB' && Sobi::Cfg( 'lang.engb_preload', true ) ) {
			JFactory::getLanguage()->load( 'com_sobipro', JPATH_SITE, 'en-GB' );
			JFactory::getLanguage()->load( 'com_sobipro', JPATH_BASE, 'en-GB' );
		}
		/* load front language file always */
		JFactory::getLanguage()->load( 'com_sobipro', JPATH_BASE, $this->_lang, true );
		JFactory::getLanguage()->load( 'com_sobipro', JPATH_SITE, $this->_lang, true );
		$this->_loaded = true;
	}

	/**
	 * Load additional language file
	 * @param $file
	 * @param $lang
	 * @return void
	 */
	public static function load( $file, $lang = null )
	{
		// at first always load the default language file
		if ( $lang != 'en-GB' && Sobi::Cfg( 'lang.engb_preload', true ) ) {
			self::load( $file, 'en-GB' );
		}
		// to load the lang files we are always need the current user language (multilang mode switch ignored here)
		if ( JPATH_SITE != JPATH_BASE ) {
			JFactory::getLanguage()->load( $file, JPATH_SITE, $lang, true );
		}
		JFactory::getLanguage()->load( $file, JPATH_BASE, $lang, true );
	}

	/**
	 * Save language depend data into the database
	 * @param $values - values array
	 * @param $lang - language
	 * @param $section - section
	 * @throws SPException
	 * @return void
	 */
	public static function saveValues( $values, $lang = null, $section = null )
	{
		$lang = $lang ? $lang : Sobi::Lang();
		if ( $values[ 'type' ] == 'plugin' ) {
			$values[ 'type' ] = 'application';
		}
		$data = [
				'sKey' => $values[ 'key' ],
				'sValue' => $values[ 'value' ],
				'section' => isset( $values[ 'section' ] ) ? $values[ 'section' ] : null,
				'language' => $lang,
				'oType' => $values[ 'type' ],
				'fid' => isset( $values[ 'fid' ] ) ? $values[ 'fid' ] : 0,
				'id' => isset( $values[ 'id' ] ) ? $values[ 'id' ] : 0,
				'params' => isset( $values[ 'params' ] ) ? $values[ 'params' ] : null,
				'options' => isset( $values[ 'options' ] ) ? $values[ 'options' ] : null,
				'explanation' => isset( $values[ 'explanation' ] ) ? $values[ 'explanation' ] : null,
		];
		try {
			SPFactory::db()->replace( 'spdb_language', $data );
			if ( $lang != Sobi::DefLang() ) {
				$data[ 'language' ] = Sobi::DefLang();
				SPFactory::db()->insert( 'spdb_language', $data, true );
			}
		} catch ( SPException $x ) {
			throw new SPException( sprintf( 'Cannot save language data. Error: %s', $x->getMessage() ) );
		}
	}

	/**
	 * Parse text and replaces placeholders
	 * @param string $text
	 * @param SPDBObject $obj
	 * @param bool $html
	 * @param bool $dropEmpty
	 * @return string
	 */
	public static function replacePlaceHolders( $text, $obj = null, $html = false, $dropEmpty = false )
	{
		preg_match_all( '/{([a-zA-Z0-9\-_\:\.\%\s]+)}/', $text, $placeHolders );
		if ( count( $placeHolders[ 1 ] ) ) {
			foreach ( $placeHolders[ 1 ] as $placeHolder ) {
				$replacement = null;
				switch ( $placeHolder ) {
					case 'section':
					case 'section.id':
					case 'section.name':
						$replacement = Sobi::Section( ( $placeHolder == 'section' || $placeHolder == 'section.name' ) );
						break;
					/*
					 * eat own dog food is so true. Isn't it?!
					 */
					case 'token':
						$replacement = SPFactory::mainframe()->token();
						break;
					default:
						if ( strstr( $placeHolder, 'date%' ) ) {
							$date = explode( '%', $placeHolder );
							$replacement = date( $date[ 1 ] );
							break;
						}
						if ( strstr( $placeHolder, 'cfg:' ) ) {
							$replacement = Sobi::Cfg( str_replace( 'cfg:', null, $placeHolder ) );
							break;
						}
						else {
							if ( strstr( $placeHolder, 'messages' ) ) {
								$obj = SPFactory::registry()->get( 'messages' );
							}
							$replacement = self::parseVal( $placeHolder, $obj, $html );
						}
				}
				if ( $replacement && ( is_string( $replacement ) || is_numeric( $replacement ) ) ) {
					$text = str_replace( '{' . $placeHolder . '}', ( string )$replacement, $text );
				}
				elseif ( $dropEmpty ) {
					$text = str_replace( '{' . $placeHolder . '}', null, $text );
				}
			}
		}
		return $text;
	}

	/**
	 * @param $label
	 * @param $obj
	 * @param bool $html
	 * @return mixed|string
	 */
	protected static function parseVal( $label, $obj, $html = false )
	{
		if ( strstr( $label, '.' ) ) {
			$properties = explode( '.', $label );
		}
		else {
			$properties[ 0 ] = $label;
		}
		$var =& $obj;
		foreach ( $properties as $property ) {
			if ( ( $var instanceof SPDBObject ) || ( method_exists( $var, 'get' ) ) ) {
				if ( strstr( $property, 'field_' ) && $var instanceof SPEntry ) {
					$field = $var->getField( $property );
					if ( $field && method_exists( $field, 'data' ) ) {
						$var = $field->data();
					}
					else {
						return null;
					}
				}
				// after an entry has been saved this attribute is being emptied
				elseif ( ( $property == 'name' ) && ( $var instanceof SPEntry ) && !( strlen( $var->get( $property ) ) ) ) {
					$var = $var->getField( ( int )Sobi::Cfg( 'entry.name_field' ) )->data( $html );
				}
				/** For the placeholder we need for sure the full URL */
				elseif ( ( $property == 'url' ) && ( $var instanceof SPEntry ) ) {
					$var = Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $var->get( 'nid' ) : $var->get( 'name' ), 'pid' => $var->get( 'primary' ), 'sid' => $var->get( 'id' ) ], false, true, true );
				}
				else {
					$var = $var->get( $property );
				}
			}
			elseif ( is_array( $var ) && isset( $var[ $property ] ) ) {
				$var = $var[ $property ];
			}
			elseif ( $var instanceof stdClass ) {
				$var = $var->$property;
			}
		}
		return $var;
	}

	/**
	 * Gets a translatable values from the language DB
	 * @param $key - key to get
	 * @param $type - type of object/field/plugin etc
	 * @param int $sid - section id
	 * @param string $select - what is to select, key, descritpion, params
	 * @param null $lang
	 * @param int $fid
	 * @return string
	 */
	public static function getValue( $key, $type, $sid = 0, $select = 'sValue', $lang = null, $fid = 0 )
	{
		$select = $select ? $select : 'sValue';
		$lang = $lang ? $lang : Sobi::Lang( false );
		if ( $type == 'plugin' ) {
			$type = 'application';
		}
		if ( !( is_array( $select ) ) ) {
			$toSselect = [ $select ];
		}
		try {
			$toSselect[ ] = 'language';
			$params = [
					'sKey' => $key,
					'oType' => $type,
					'language' => array_unique( [ $lang, Sobi::DefLang(), 'en-GB' ] )
			];
			if ( $sid ) {
				$params[ 'section' ] = $sid;
			}
			if ( $fid ) {
				$params[ 'fid' ] = $fid;
			}
			$r = SPFactory::db()->select( $toSselect, 'spdb_language', $params )->loadAssocList( 'language' );
			if ( isset( $r[ $lang ] ) ) {
				$r = $r[ $lang ][ $select ];
			}
			elseif ( isset( $r[ Sobi::DefLang() ] ) ) {
				$r = $r[ Sobi::DefLang() ][ $select ];
			}
			elseif ( isset( $r[ 'en-GB' ] ) ) {
				$r = $r[ 'en-GB' ][ $select ];
			}
			elseif ( isset( $r[ 0 ] ) ) {
				$r = $r[ 0 ][ $select ];
			}
			else {
				$r = null;
			}
		} catch ( SPException $x ) {
			Sobi::Error( 'language', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __CLASS__ );
		}
		return $r;
	}

	/**
	 * Singleton
	 *
	 * @return SPLang
	 */
	public static function & getInstance()
	{
		static $instance = null;
		if ( !( $instance instanceof self ) ) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Returns correctly formatted currency amount
	 * @param double $value - amount
	 * @param bool $currency
	 * @return string
	 */
	public static function currency( $value, $currency = true )
	{
		$dp = html_entity_decode( Sobi::Cfg( 'payments.dec_point', ',' ), ENT_QUOTES );
		$value = number_format( $value, Sobi::Cfg( 'payments.decimal', 2 ), $dp, Sobi::Cfg( 'payments.thousands_sep', ' ' ) );
		if ( $currency ) {
			$value = str_replace( [ '%value', '%currency' ], [ $value, Sobi::Cfg( 'payments.currency', 'EUR' ) ], Sobi::Cfg( 'payments.format', '%value %currency' ) );
		}
		return $value;
	}

	/**
	 * Load java script language file
	 * @param $adm
	 * @return string
	 */
	public static function jsLang( $adm = false )
	{
		return self::getInstance()->_jsLang( $adm );
	}

	protected function _jsLang( $adm )
	{
		$front = [];
		if ( $adm ) {
			$front = $this->_jsLang( false );
		}
		$path = $adm ? implode( DS, [ JPATH_ADMINISTRATOR, 'language', 'en-GB', 'en-GB.com_sobipro.js' ] ) : implode( DS, [ SOBI_ROOT, 'language', 'en-GB', 'en-GB.com_sobipro.js' ] );
		if ( $this->_lang != 'en-GB' && Sobi::Cfg( 'lang.engb_preload', true ) ) {
			$strings = SPLoader::loadIniFile( str_replace( 'en-GB', str_replace( '_', '-', $this->_lang ), $path ), false, false, true, true, true );
			$def = SPLoader::loadIniFile( $path, false, false, true, true, true );
			if ( is_array( $strings ) && count( $strings ) ) {
				$def = array_merge( $front, $def, $strings );
			}
		}
		else {
			$def = SPLoader::loadIniFile( str_replace( 'en-GB', str_replace( '_', '-', $this->_lang ), $path ), false, false, true, true, true );
		}
		return $def;
	}

	/**
	 * Translate given string
	 * This function is used mostly from the admin templates and the config ini-files interpreter
	 *
	 * @internal param string $message
	 * @internal param array $params
	 * @return string
	 */
	public static function txt()
	{
		return self::getInstance()->_txt( func_get_args() );
	}

	/**
	 * Register new language domain.
	 *
	 * @param string $domain
	 * @internal param string $path
	 * @return string
	 */
	protected function _registerDomain( $domain )
	{
		$domain = trim( $domain );
		if ( $domain != 'admin' && $domain != 'site' ) {
			$lang =& JFactory::getLanguage();
			$lang->load( 'com_sobipro.' . $domain );
		}
	}

	/**
	 * Register new language domain.
	 *
	 * @param string $domain
	 * @param string $path
	 * @return string
	 */
	public static function registerDomain( $domain, $path = null )
	{
		return self::getInstance()->_registerDomain( $domain, $path );
	}

	/**
	 * Set the used language/locale
	 *
	 * @param string $lang
	 * @return bool
	 */
	public static function setLang( $lang )
	{
		return self::getInstance()->_setLang( $lang );
	}

	/**
	 * Set the used language/locale
	 *
	 * @param string $lang
	 * @return bool
	 */
	protected function _setLang( $lang )
	{
//		$lang = str_replace( '-', '_', $lang );
		$this->_lang = $lang;
	}

	/**
	 * Used for XML nodes creation
	 * Creates singular form from plural
	 * @param string $txt
	 * @return string
	 */
	public static function singular( $txt )
	{
		/* entries <=> entry */
		if ( substr( $txt, -3 ) == 'ies' ) {
			$txt = substr( $txt, 0, -3 ) . 'y';
		}
		/* buses <=> bus */
		elseif ( substr( $txt, -3 ) == 'ses' ) {
			$txt = substr( $txt, 0, -3 );
		}
		/* sections <=> section */
		elseif ( substr( $txt, -1 ) == 's' ) {
			$txt = substr( $txt, 0, -1 );
		}
		return $txt;
	}

	/**
	 * Replaces HTML entities to valid XML entities
	 * @param $txt
	 * @param $amp
	 * @return unknown_type
	 */
	public static function entities( $txt, $amp = false )
	{
		$txt = str_replace( '&', '&#38;', $txt );
		if ( $amp ) {
			return $txt;
		}
		//		$txt = htmlentities( $txt, ENT_QUOTES, 'UTF-8' );
		$entities = [ 'auml' => '&#228;', 'ouml' => '&#246;', 'uuml' => '&#252;', 'szlig' => '&#223;', 'Auml' => '&#196;', 'Ouml' => '&#214;', 'Uuml' => '&#220;', 'nbsp' => '&#160;', 'Agrave' => '&#192;', 'Egrave' => '&#200;', 'Eacute' => '&#201;', 'Ecirc' => '&#202;', 'egrave' => '&#232;', 'eacute' => '&#233;', 'ecirc' => '&#234;', 'agrave' => '&#224;', 'iuml' => '&#239;', 'ugrave' => '&#249;', 'ucirc' => '&#251;', 'uuml' => '&#252;', 'ccedil' => '&#231;', 'AElig' => '&#198;', 'aelig' => '&#330;', 'OElig' => '&#338;', 'oelig' => '&#339;', 'angst' => '&#8491;', 'cent' => '&#162;', 'copy' => '&#169;', 'Dagger' => '&#8225;', 'dagger' => '&#8224;', 'deg' => '&#176;', 'emsp' => '&#8195;', 'ensp' => '&#8194;', 'ETH' => '&#208;', 'eth' => '&#240;', 'euro' => '&#8364;', 'half' => '&#189;', 'laquo' => '&#171;', 'ldquo' => '&#8220;', 'lsquo' => '&#8216;', 'mdash' => '&#8212;', 'micro' => '&#181;', 'middot' => '&#183;', 'ndash' => '&#8211;', 'not' => '&#172;', 'numsp' => '&#8199;', 'para' => '&#182;', 'permil' => '&#8240;', 'puncsp' => '&#8200;', 'raquo' => '&#187;', 'rdquo' => '&#8221;', 'rsquo' => '&#8217;', 'reg' => '&#174;', 'sect' => '&#167;', 'THORN' => '&#222;', 'thorn' => '&#254;', 'trade' => '&#8482;' ];
		foreach ( $entities as $ent => $repl ) {
			$txt = preg_replace( '/&' . $ent . ';?/m', $repl, $txt );
		}
		return $txt;
	}

	/**
	 * Creates URL saf string
	 * @param string $str
	 * @return string
	 */
	public static function urlSafe( $str )
	{
		// copy of Joomla! stringURLUnicodeSlug
		// we don't want to have it lowercased
		// @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
		// Replace double byte whitespaces by single byte (East Asian languages)
		$str = preg_replace( '/\xE3\x80\x80/', ' ', $str );
		// Remove any '-' from the string as they will be used as concatenator.
		// Would be great to let the spaces in but only Firefox is friendly with this
		$str = str_replace( '-', ' ', $str );
		// Replace forbidden characters by whitespaces
		$str = preg_replace( '/[:\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]/', "\x20", $str );
		// Delete all '?'
		$str = str_replace( '?', '', $str );
		// Remove any duplicate whitespace and replace whitespaces by hyphens
		$str = preg_replace( '/\x20+/', '-', $str );
		$str = preg_replace( [ '/\s+/', Sobi::Cfg( 'browser.url_filter', '/[^A-Za-z0-9\p{L}\-\_]/iu' ) ], [ '-', null ], $str );
		$str = trim( $str, '_-\[\]\(\)' );
		return $str;
	}

	/**
	 * Creates alias/nid suitable string
	 * @param string $txt
	 * @return string
	 */
	public static function varName( $txt )
	{
		$pieces = explode( ' ', $txt );
		$txt = null;
		for ( $i = 0; $i < count( $pieces ); $i++ ) {
			$pieces[ $i ] = SPLang::nid( $pieces[ $i ] );
			// preg_replace( '/[^a-z0-9_]/', null, strtolower( $pieces[ $i ] ) );
			if ( $i > 0 ) {
				$pieces[ $i ] = ucfirst( $pieces[ $i ] );
			}
			$txt .= $pieces[ $i ];
		}
		return $txt;
	}

	/**
	 * @param string $txt
	 * @param bool $unicode
	 * @param bool $forceUnicode
	 * @return string
	 */
	public static function nid( $txt, $unicode = false, $forceUnicode = false )
	{
		$txt = trim( str_replace( [ '.', '_' ], '-', $txt ) );
		return ( Sobi::Cfg( 'sef.unicode' ) && $unicode ) || $forceUnicode ?
				self::urlSafe( $txt ) :
				trim( preg_replace( '/(\s|[^A-Za-z0-9\-])+/', '-', JFactory::getLanguage()->transliterate( $txt ) ), '_-\[\]\(\)' );
	}

	/**
	 * Translating language depend attributes of objects
	 *
	 * @param array $sids - array with ids of objects to translate
	 * @param array $fields - (optional) array (or string) with properties names to translate. If not given, translates all
	 * @param string $type - (optional) type of object (section, category, entry). If not given, translates all
	 * @param string $lang - (optional) specific language. If not given, use currently set language
	 * @param string $ident
	 * @return array
	 */
	public static function translateObject( $sids, $fields = [], $type = null, $lang = null, $ident = 'id' )
	{
		/** @todo multiple attr does not work because the id is the object id */
		$fields = is_array( $fields ) ? $fields : ( strlen( $fields ) ? [ $fields ] : null );
		$lang = $lang ? $lang : Sobi::Lang( false );
		// we don't need to specify the language as we want to have all of them and then order it right
		// when an object name has been entered in a particular language but this language isn't used later
		// we won't have any label for this certain object
		// Wed, Dec 18, 2013 09:57:04
		//$params = array( 'id' => $sids, 'language' => array( $lang, Sobi::DefLang(), 'en-GB' ) );
		static $store = [];
		$params = [ $ident => $sids ];
		$result = [];
		if ( $type ) {
			$params[ 'oType' ] = $type;
		}
		if ( in_array( 'alias', $fields ) ) {
			$fields[ ] = 'nid';
		}
		if ( $fields && count( $fields ) ) {
			$params[ 'sKey' ] = $fields;
		}
		if ( isset( $store[ $lang ][ json_encode( $params ) ][ $ident ] ) ) {
			return $store[ $lang ][ json_encode( $params ) ][ $ident ];
		}
		try {

			$labels = SPFactory::db()
					->select( $ident . ' AS id, sKey AS label, sValue AS value, language', 'spdb_language', $params, "FIELD( language, '{$lang}', '" . Sobi::DefLang() . "' )" )
					->loadAssocList();
			if ( count( $labels ) ) {
				$aliases = [];
				if ( in_array( 'alias', $fields ) ) {
					$aliases = SPFactory::db()
							->select( [ 'nid', 'id' ], 'spdb_object', [ 'id' => $sids ] )
							->loadAssocList( 'id' );
				}
				foreach ( $labels as $label ) {
					if ( $label[ 'label' ] == 'nid' && ( !( isset( $result[ $label[ 'id' ] ][ 'alias' ] ) ) || $label[ 'language' ] == $lang ) ) {
						$result[ $label[ 'id' ] ][ 'alias' ] = $label[ 'value' ];
					}
					else {
						if ( !( isset( $result[ $label[ 'id' ] ] ) ) || $label[ 'language' ] == Sobi::Lang() ) {
							$result[ $label[ 'id' ] ] = $label;
						}
					}
					if ( in_array( 'nid', $fields ) ) {
						if ( !( isset( $result[ $label[ 'id' ] ][ 'alias' ] ) ) ) {
							$result[ $label[ 'id' ] ][ 'alias' ] = isset( $aliases[ $label[ 'id' ] ] ) ? $aliases[ $label[ 'id' ] ][ 'nid' ] : null;
						}
					}
				}
				foreach ( $labels as $label ) {
					if ( !( isset( $result[ $label[ 'id' ] ][ $label[ 'label' ] ] ) ) || $label[ 'language' ] == Sobi::Lang() ) {
						$result[ $label[ 'id' ] ][ $label[ 'label' ] ] = $label[ 'value' ];
					}
				}
			}
			$store[ $lang ][ json_encode( $params ) ][ $ident ] = $result;
		} catch ( SPError $x ) {
			Sobi::Error( 'language', SPLang::e( 'CANNOT_TRANSLATE_OBJECT', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
		}
		return $result;
	}
}
