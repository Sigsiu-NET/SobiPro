<?php
/**
 * @version $Id: lang.php 2615 2012-07-20 17:06:33Z Radek Suski $
 * @package: SobiPro
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl-2.1.html
 * You can use, redistribute this file and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 * ===================================================
 * $Date: 2012-07-20 19:06:33 +0200 (Fri, 20 Jul 2012) $
 * $Revision: 2615 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla_common/base/lang.php $
 */
defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
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
     * @param string $message
     * @param array $params
     * @return string
     */
    public static function _()
    {
        return self::getInstance()->_txt( func_get_args() );
    }

    /**
     * Translate given string
     *
     * @param string $message
     * @param array $params
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
            $m = call_user_func_array( array( 'JText', '_' ), array( $a[ 0 ] ) );
            if ( $m == $a[ 0 ] ) {
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
            $matches = array();
            preg_match( '/translate\:\[([a-zA-Z0-9\.\_\-]*)\]/', $m, $matches );
            $m = str_replace( $matches[ 0 ], $this->_txt( $matches[ 1 ], null, false ), $m );
        }
        if ( strstr( $m, '[JS]' ) || strstr( $in, '[JS]' ) ) {
            $m = str_replace( "\n", '\n', $m );
        }
        $m = str_replace( '_QQ_', '"', $m );
        return str_replace( array( '[JS]', '[MSG]', '[URL]' ), null, $m );
    }

    protected function tplOverride( $term )
    {
        if ( !( class_exists( 'Sobi' ) ) || !( Sobi::Section() ) ) {
            return false;
        }
        static $xml = null;
        static $xdef = null;
        static $custom = false;
        static $lang = null;
        /* try this once */
        if ( !( $custom ) ) {
            if ( Sobi::Cfg( 'section.template' ) ) {
                $custom = true;
                $tmpl = SPLoader::translatePath( 'usr.templates.' . Sobi::Cfg( 'section.template' ) . '.translation', 'front', true, 'xml' );
                /* if the template provide it */
                if ( $tmpl ) {
                    $xml = DOMDocument::load( $tmpl );
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
     * Create JS freindly script
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
     * @param string $msg
     * @return string
     */
    public static function e()
    {
        static $loaded = false;
        if ( !( $loaded ) ) {
            self::getInstance()->_eload();
        }
        $a = func_get_args();
        return call_user_func_array( array( self::getInstance(), '_txt' ), $a );
    }

    protected function _eload()
    {
        JFactory::getLanguage()->load( 'com_sobipro.err', JPATH_SITE, 'en-GB' );
        if ( $this->_lang != 'en-GB' ) {
            JFactory::getLanguage()->load( 'com_sobipro.err', JPATH_SITE );
        }
    }

    protected function _load()
    {
        /* load default language file */
        if ( $this->_lang != 'en-GB' ) {
            JFactory::getLanguage()->load( 'com_sobipro', JPATH_SITE, 'en-GB' );
            JFactory::getLanguage()->load( 'com_sobipro', JPATH_BASE, 'en-GB' );
        }
        /* load front language file always */
        JFactory::getLanguage()->load( 'com_sobipro', JPATH_BASE, null, true );
        JFactory::getLanguage()->load( 'com_sobipro', JPATH_SITE );
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
        if ( $lang != 'en-GB' ) {
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
     * @return void
     */
    public static function saveValues( $values, $lang = null, $section = null )
    {
        $lang = $lang ? $lang : Sobi::Lang();
        if ( $values[ 'type' ] == 'plugin' ) {
            $values[ 'type' ] = 'application';
        }
        $data = array(
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
        );
        try {
            SPFactory::db()->replace( 'spdb_language', $data );
            if ( $lang != Sobi::DefLang() ) {
                $data[ 'language' ] = Sobi::DefLang();
                SPFactory::db()->insert( 'spdb_language', $data, true );
            }
        }
        catch ( SPException $x ) {
            throw new SPException( sprintf( 'Cannot save language data. Error: %s', $x->getMessage() ) );
        }
    }

    /**
     * Parse text and replaces placeholders
     * @param string $text
     * @param SPDBObject $obj
     * @return string
     */
    public static function replacePlaceHolders( $text, $obj = null, $html = false )
    {
        preg_match_all( '/{([a-zA-Z0-9\-_\:\.]+)}/', $text, $placeHolders );
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
                        if ( strstr( $placeHolder, 'cfg:' ) ) {
                            $replacement = Sobi::Cfg( str_replace( 'cfg:', null, $placeHolder ) );
                            break;
                        }
                        else {
                            $replacement = self::parseVal( $placeHolder, $obj, $html );
                        }
                }
                if ( $replacement && ( is_string( $replacement ) || is_numeric( $replacement ) ) ) {
                    $text = str_replace( '{' . $placeHolder . '}', ( string )$replacement, $text );
                }
            }
        }
        return $text;
    }

    /**
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
	            if ( strstr( $property, 'field_' ) && $var instanceof SPEntry  ) {
//                if ( strstr( $property, 'field_' && $var instanceof SPEntry ) ) {
                    $var = $var->getField( $property )->data();
                }
                // after an entry has been saved this attribut is being emptied
                elseif ( ( $property == 'name' ) && ( $var instanceof SPEntry ) && !( strlen( $var->get( $property ) ) ) ) {
                    $var = $var->getField( ( int )Sobi::Cfg( 'entry.name_field' ) )->data( $html );
                }
                /** For the placeholder we need for sure the full URL */
                elseif ( ( $property == 'url' ) && ( $var instanceof SPEntry ) ) {
                    $var = Sobi::Url(
                        array(
                            'title' => $var->get( 'name' ),
                            'pid' => $var->get( 'primary' ),
                            'sid' => $var->get( 'sid' )
                        ), false, true, true
                    );
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
     * @param $sid - section id
     * @param $select - what is to select, key, descritpion, params
     * @return string
     */
    public static function getValue( $key, $type, $sid = 0, $select = 'sValue', $lang = null )
    {
        $select = $select ? $select : 'sValue';
        $lang = $lang ? $lang : Sobi::Lang( false );
        if ( $type == 'plugin' ) {
            $type = 'application';
        }
        if ( !( is_array( $select ) ) ) {
            $toSselect = array( $select );
        }
        try {
            $toSselect[ ] = 'language';
            $params = array(
                'sKey' => $key,
                'oType' => $type,
                'language' => array_unique( array( $lang, Sobi::DefLang(), 'en-GB' ) )
            );
            if ( $sid ) {
                $params[ 'section' ] = $sid;
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
        }
        catch ( SPException $x ) {
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
     * @return string
     */
    public static function currency( $value, $currency = true )
    {
        $dp = html_entity_decode( Sobi::Cfg( 'payments.dec_point', ',' ), ENT_QUOTES );
        $value = number_format( $value, Sobi::Cfg( 'payments.decimal', 2 ), $dp, Sobi::Cfg( 'payments.thousands_sep', ' ' ) );
        if( $currency ) {
            $value = str_replace( array( '%value', '%currency' ), array( $value, Sobi::Cfg( 'payments.currency', 'EUR' ) ), Sobi::Cfg( 'payments.format', '%value %currency' ) );
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
        $path = $adm ? implode( DS, array( JPATH_ADMINISTRATOR, 'language', 'en-GB', 'en-GB.com_sobipro.js' ) ) : implode( DS, array( SOBI_ROOT, 'language', 'en-GB', 'en-GB.com_sobipro.js' ) );
        $def = SPLoader::loadIniFile( $path, false, false, true, true, true );
        if ( $this->_lang != 'en-GB' ) {
            $strings = SPLoader::loadIniFile( str_replace( 'en-GB', str_replace( '_', '-', $this->_lang ), $path ), false, false, true, true, true );
            if ( is_array( $strings ) && count( $strings ) ) {
                $def = array_merge( $def, $strings );
            }
        }
        return $def;
    }

    /**
     * Translate given string
     * This function is used mostly from the admin templates and the config ini-files interpreter
     *
     * @param string $message
     * @param array $params
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
     * @param string $path
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
        $lang = str_replace( '-', '_', $lang );
        $this->_lang = $lang;
    }

    /**
     * Used for XML nodes creation
     * Cretes singular form from plural
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
        $entities = array( 'auml' => '&#228;', 'ouml' => '&#246;', 'uuml' => '&#252;', 'szlig' => '&#223;', 'Auml' => '&#196;', 'Ouml' => '&#214;', 'Uuml' => '&#220;', 'nbsp' => '&#160;', 'Agrave' => '&#192;', 'Egrave' => '&#200;', 'Eacute' => '&#201;', 'Ecirc' => '&#202;', 'egrave' => '&#232;', 'eacute' => '&#233;', 'ecirc' => '&#234;', 'agrave' => '&#224;', 'iuml' => '&#239;', 'ugrave' => '&#249;', 'ucirc' => '&#251;', 'uuml' => '&#252;', 'ccedil' => '&#231;', 'AElig' => '&#198;', 'aelig' => '&#330;', 'OElig' => '&#338;', 'oelig' => '&#339;', 'angst' => '&#8491;', 'cent' => '&#162;', 'copy' => '&#169;', 'Dagger' => '&#8225;', 'dagger' => '&#8224;', 'deg' => '&#176;', 'emsp' => '&#8195;', 'ensp' => '&#8194;', 'ETH' => '&#208;', 'eth' => '&#240;', 'euro' => '&#8364;', 'half' => '&#189;', 'laquo' => '&#171;', 'ldquo' => '&#8220;', 'lsquo' => '&#8216;', 'mdash' => '&#8212;', 'micro' => '&#181;', 'middot' => '&#183;', 'ndash' => '&#8211;', 'not' => '&#172;', 'numsp' => '&#8199;', 'para' => '&#182;', 'permil' => '&#8240;', 'puncsp' => '&#8200;', 'raquo' => '&#187;', 'rdquo' => '&#8221;', 'rsquo' => '&#8217;', 'reg' => '&#174;', 'sect' => '&#167;', 'THORN' => '&#222;', 'thorn' => '&#254;', 'trade' => '&#8482;' );
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
        $str = str_replace( '&', Sobi::Txt( 'URL_AND' ), $str );
        static $from = null;
        static $to = null;
        if ( !( $from ) && !( $to ) ) {
            $from = array();
            $to = array();
            if ( strlen( Sobi::Cfg( 'browser.url_replace_from' ) ) && strlen( Sobi::Cfg( 'browser.url_replace_with' ) ) ) {
                $t = explode( ',', Sobi::Cfg( 'browser.url_replace_with' ) );
                if ( count( $t ) ) {
                    foreach ( $t as $s ) {
                        $to[ ] = $s;
                        if ( function_exists( 'mb_convert_case' ) ) {
                            $to[ ] = mb_convert_case( $s, MB_CASE_UPPER, 'UTF-8' );
                        }
                    }
                    $f = explode( ',', Sobi::Cfg( 'browser.url_replace_from' ) );
                    if ( count( $f ) ) {
                        foreach ( $f as $s ) {
                            $ex = preg_split( '//u', $s, -1, PREG_SPLIT_NO_EMPTY );
                            if ( count( $ex ) ) {
                                $si = implode( '|', $ex );
                            }
                            $from[ ] = "/{$si}/u";
                            if ( function_exists( 'mb_convert_case' ) ) {
                                $s = mb_convert_case( $s, MB_CASE_UPPER, 'UTF-8' );
                                $ex = preg_split( '//u', $s, -1, PREG_SPLIT_NO_EMPTY );
                                if ( count( $ex ) ) {
                                    $si = implode( '|', $ex );
                                }
                                $from[ ] = "/{$si}/u";
                            }
                        }
                    }
                }
            }
        }
        if ( count( $from ) && count( $to ) ) {
            $str = preg_replace( $from, $to, $str );
        }
        $str = preg_replace( array( '/\s+/', Sobi::Cfg( 'browser.url_filter', '/[^A-Za-z0-9\p{L}\-\_]/iu' ) ), array( '-', null ), $str );
        return str_replace( '--', '-', str_replace( '--', '-', $str ) );
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
            $pieces[ $i ] = preg_replace( '/[^a-z0-9_]/', null, strtolower( $pieces[ $i ] ) );
            if ( $i > 0 ) {
                $pieces[ $i ] = ucfirst( $pieces[ $i ] );
            }
            $txt .= $pieces[ $i ];
        }
        return $txt;
    }

    /**
     * @param string $txt
     * @return string
     */
    public static function nid( $txt )
    {
        return trim( strtolower( str_replace( '__', '_', preg_replace( '/[^a-z0-9\_]/i', '_', preg_replace( '/\W/', '_', $txt ) ) ) ) );
    }

    /**
     * Translating language depend attributes of objects
     *
     * @param array $sids - array with ids of objects to translate
     * @param array $fields - (optional) array (or string) with properties names to translate. If not given, translates all
     * @param string $type - (optional) type of object (section, category, entry). If not given, translates all
     * @param string $lang - (optional) specific language. If not given, use currently set language
     * @return array
     */
    public static function translateObject( $sids, $fields = null, $type = null, $lang = null )
    {
        /** @todo multiple attr does not work because the id is the object id  */
        /* @var Spdb $db */
        $db =& SPFactory::db();
        $fields = is_array( $fields ) ? $fields : ( strlen( $fields ) ? array( $fields ) : null );
        $lang = $lang ? $lang : Sobi::Lang( false );
        $params = array( 'id' => $sids, 'language' => array( $lang, Sobi::DefLang(), 'en-GB' ) );
        $result = array();
        if ( $type ) {
            $params[ 'oType' ] = $type;
        }
        if ( $fields && count( $fields ) ) {
            $params[ 'sKey' ] = $fields;
        }
        try {
            $db->select( 'id, sKey AS label, sValue AS value, language', 'spdb_language', $params, "FIELD( language, '{$lang}', '" . Sobi::DefLang() . "' )" );
            $labels = $db->loadAssocList();
            if ( count( $labels ) ) {
                foreach ( $labels as $label ) {
                    if ( !( isset( $result[ $label[ 'id' ] ] ) ) || $label[ 'language' ] == Sobi::Lang() ) {
                        $result[ $label[ 'id' ] ] = $label;
                    }
                }
            }
        }
        catch ( SPError $x ) {
            Sobi::Error( 'language', SPLang::e( 'CANNOT_TRANSLATE_OBJECT', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __CLASS__ );
        }
        return $result;
    }
}
