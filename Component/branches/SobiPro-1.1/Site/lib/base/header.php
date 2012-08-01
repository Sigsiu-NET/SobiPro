<?php
/**
 * @version: $Id: header.php 2608 2012-07-16 10:31:30Z Radek Suski $
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
 * $Date: 2012-07-16 12:31:30 +0200 (Mon, 16 Jul 2012) $
 * $Revision: 2608 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/base/header.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 08-Jul-2008 9:43:26 AM
 */
final class SPHeader
{

    /**
     * @var array
     */
    private $head = array();
    /**
     * @var array
     */
    private $css = array();
    /**
     * @var array
     */
    private $cssFiles = array();
    /**
     * @var array
     */
    private $js = array();
    /**
     * @var array
     */
    private $links = array();
    /**
     * @var array
     */
    private $jsFiles = array();
    /**
     * @var array
     */
    private $authors = array();
    /**
     * @var array
     */
    private $robots = array();
    /**
     * @var array
     */
    private $description = array();
    /**
     * @var array
     */
    private $keywords = array();
    /**
     * @var array
     */
    private $raw = array();
    /**
     * @var int
     */
    private $count = 0;
    /**
     * @var int
     */
    private $_cache = array( 'js' => array(), 'css' => array() );

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

    /**
     * Add raw code to the site header
     * @param string $html
     * @return SPHeader
     */
    public function & add( $html )
    {
        $this->raw[ ++$this->count ] = $html;
        return $this;
    }

    public function & addMeta( $name, $content )
    {
        $this->raw[ ++$this->count ] = "<meta name=\"{$name}\" content=\"{$content}\" />";
    }

    /**
     * Add JavaScript code to the site header
     * @param string $js
     * @return SPHeader
     */
    public function & addJsCode( $js )
    {
        $this->js[ ++$this->count ] = $js;
        return $this;
    }

    /**
     * Add JavaScript file to the site header
     * @param string $file
     * @param bool $adm
     * @param bool $force
     * @param string $ext
     * @param string $params
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
                    Sobi::Reg( 'current_template' ) &&
                    /* and we are NOT including js file from the template  */
                    !( strstr( dirname( $jsFile ), Sobi::Reg( 'current_template' ) ) ) &&
                    /* but there is such file (with the same name) in the template package  */
                    SPFs::exists( Sobi::Reg( 'current_template' ) . '/js/' . basename( $jsFile ) ) &&
                    !( strstr( dirname( $jsFile ), 'templates' ) )
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

                if ( Sobi::Cfg( 'cache.include_files', true ) && !( $params || $force || $adm || defined( 'SOBIPRO_ADM' ) ) ) {
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
        return $this;
    }

    /**
     * Add external JavaScript file to the site header
     * @param string $file
     * @param string $params
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
            $params = $params ? '?' . $params : null;
            $file = "\n<script type=\"text/javascript\" src=\"{$file}{$params}\"></script>";
            if ( !in_array( $file, $this->jsFiles ) ) {
                $this->jsFiles[ ++$this->count ] = $file;
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
     * @return SPHeader
     */
    public function & addJsVarFile( $script, $id, $params, $adm = false )
    {
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
        if ( Sobi::Cfg( 'cache.include_files', true ) && !( $adm || defined( 'SOBIPRO_ADM' ) ) ) {
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
        return $this;
    }

    /**
     * Add CSS code to the site header
     * @param string $css
     * @return SPHeader
     */
    public function & addCSSCode( $css )
    {
        $this->css[ ++$this->count ] = $css;
        return $this;
    }

    /**
     * Add CSS file to the site header
     * @param string $file file name
     * @param bool $adm
     * @param bool $force
     * @param string $ext
     * @param string $params
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
            $cssFile = SPLoader::CssFile( $file, $adm, true, false, $ext );
            $index = ++$this->count;
            // if this is a template CSS file - ensure it will be loaded after all others CSS files
            if ( Sobi::Reg( 'current_template' ) && ( strstr( dirname( $cssFile ), Sobi::Reg( 'current_template' ) ) ) ) {
                $index *= 100;
            }
            if ( $cssFile ) {
                $override = false;
                if (
                    /* If there is already template defined */
                    Sobi::Reg( 'current_template' ) &&
                    /* and we are NOT including css file from the template  */
                    !( strstr( dirname( $cssFile ), Sobi::Reg( 'current_template' ) ) ) &&
                    /* but there is such file (with the same name) in the template package  */
                    SPFs::exists( Sobi::Reg( 'current_template' ) . '/css/' . basename( $cssFile ) ) &&
                    !( strstr( dirname( $cssFile ), 'templates' ) )
                ) {
                    $cssFile = explode( '.', basename( $cssFile ) );
                    $ext = $cssFile[ count( $cssFile ) - 1 ];
                    unset( $cssFile[ count( $cssFile ) - 1 ] );
                    $f = implode( '.', $cssFile );
                    $cssFile = SPLoader::CssFile( 'absolute.' . Sobi::Reg( 'current_template' ) . '/css/' . $f, $adm, true, true, $ext );
                    $override = true;
                    $index *= 100;
                }
                else {
                    $cssFile = SPLoader::CssFile( $file, $adm, true, true, $ext );
                }
                if ( Sobi::Cfg( 'cache.include_files', true ) && !( $media || $params || $force || $adm || defined( 'SOBIPRO_ADM' ) ) ) {
                    if ( !( $override ) ) {
                        $cssFile = SPLoader::CssFile( $file, $adm, true, false, $ext );
                    }
                    if ( !in_array( $file, $this->_cache[ 'css' ] ) || $force ) {
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
        return $this;
    }

    /**
     * Add alternate link to the site header
     * @param string $href
     * @param string $relation
     * @param string $relType
     * @param array $params
     * @return void
     * @return SPHeader
     */
    public function & addHeadLink( $href, $type = null, $title = null, $rel = 'alternate', $relType = 'rel', $params = null )
    {
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
        $this->links[ ] = "<link href=\"{$href}\" {$relType}=\"{$rel}\" {$type}{$params}{$title}/>";
        return $this;
    }

    public function & addCanonical( $url )
    {
        return $this->addHeadLink( $url, null, null, 'canonical' );
    }

    /**
     * Set Site title
     * @param string $title
     * @return SPHeader
     */
    public function & setTitle( $title )
    {
        SPFactory::mainframe()->setTitle( SPLang::clean( $title ) );
        return $this;
    }

    /**
     * Add meta dscription to the site header
     * @param string $desc
     * @return SPHeader
     */
    public function & addDescription( $desc )
    {
        if ( strlen( $desc ) ) {
            $this->description[ ] = strip_tags( str_replace( '"', "'", SPLang::entities( $desc, true ) ) );
        }
        return $this;
    }

    /**
     * Gets meta keys and met description from the given object
     *  and adds to the site header
     * @param SPDBObject $obj
     * @return SPHeader
     */
    public function & objMeta( $obj )
    {
        if ( $obj->get( 'metaDesc' ) ) {
            $this->addDescription( $obj->get( 'metaDesc' ) );
        }
        if ( $obj->get( 'metaKeys' ) ) {
            $this->addKeyword( $obj->get( 'metaKeys' ) );
        }
        if ( $obj->get( 'metaAuthor' ) ) {
            $this->authors[ ] = $obj->get( 'metaAuthor' );
        }
        if ( $obj->get( 'metaRobots' ) ) {
            $this->robots[ ] = $obj->get( 'metaRobots' );
        }
        if ( ( $obj->get( 'oType' ) != 'section' ) && Sobi::Cfg( 'meta.always_add_section' ) ) {
            $this->objMeta( SPFactory::currentSection() );
        }
        if ( $obj->get( 'oType' ) == 'entry' ) {
            $fields = $obj->getFields();
            if ( count( $fields ) ) {
                foreach ( $fields as $field ) {
                    $this->addDescription( $field->metaDesc() );
                    $this->addKeyword( $field->metaKeys() );
                }
            }
        }
        return $this;
    }

    /**
     * Add a keywords to the site header
     * @param string $key
     * @return SPHeader
     */
    public function & addKeyword( $keys )
    {
        if ( strlen( $keys ) ) {
            $keys = explode( Sobi::Cfg( 'string.meta_keys_separator', ',' ), $keys );
            if ( !empty( $keys ) ) {
                $this->count++;
                foreach ( $keys as $key ) {
                    $this->keywords[ ] = strip_tags( trim( SPLang::entities( $key, true ) ) );
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
        $this->keywords = array();
        $this->authors = array();
        $this->robots = array();
        $this->description = array();
        $this->cssFiles = array();
        $this->jsFiles = array();
        $this->css = array();
        $this->js = array();
        $this->raw = array();
        $this->head = array();
        return $this;
    }

    private function _cssFiles()
    {
        if ( Sobi::Cfg( 'cache.include_files', true ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
            if ( count( $this->_cache[ 'css' ] ) ) {
                /*
                 * create the right checksum
                 */
                $check = array( 'section' => Sobi::Section() );
                foreach ( $this->_cache[ 'css' ] as $file ) {
                    $check[ $file ] = filemtime( $file );
                }
                $check = md5( serialize( $check ) );
                if ( !( SPFs::exists( SOBI_PATH . "/var/css/{$check}.css" ) ) ) {
                    $cssContent = "\n/* Created at: " . date( SPFactory::config()->key( 'date.log_format', 'D M j G:i:s T Y' ) ) . " */\n";
                    foreach ( $this->_cache[ 'css' ] as $file ) {
                        $fName = str_replace( Sobi::FixPath( SOBI_ROOT ), null, $file );
                        $cssContent .= "\n/**  \n========\nFile: {$fName}\n========\n*/\n";
                        $fc = SPFs::read( $file );
                        preg_match_all( '/.*url\(.*/', $fc, $matches );

                        // we have to replace url relative path
                        $fPath = str_replace( Sobi::FixPath( SOBI_ROOT . DS ), SPFactory::config()->get( 'live_site' ), $file );
                        $fPath = str_replace( '\\', '/', $fPath );
                        $fPath = explode( '/', $fPath );
                        if ( count( $matches[ 0 ] ) ) {
                            foreach ( $matches[ 0 ] as $url ) {
                                // if it is already absolute - skip or from root
                                if ( preg_match( '|http(s)?://|', $url ) || preg_match( '|url\(["\s]*/|', $url ) ) {
                                    continue;
                                }
                                $c = preg_match_all( '|\.\./|', $url, $c ) + 1;
                                $tempFile = array_reverse( $fPath );
                                for ( $i = 0; $i < $c; $i++ ) {
                                    unset( $tempFile[ $i ] );
                                }
                                $rPath = Sobi::FixPath( implode( '/', array_reverse( $tempFile ) ) );
                                $rurl = preg_replace( '|(url\(["\s]*)([^a-zA-Z0-9]*)|', '\1' . $rPath . '/', $url );
                                $fc = str_replace( $url, $rurl, $fc );
                            }
                        }
                        // and add to content
                        $cssContent .= $fc;
                    }
                    SPFs::write( SOBI_PATH . "/var/css/{$check}.css", $cssContent );
                }
                $cfile = SPLoader::CssFile( 'front.var.css.' . $check, false, true, true );
                $this->cssFiles[ ++$this->count ] = "<link rel=\"stylesheet\" href=\"{$cfile}\" type=\"text/css\" />";
            }
        }
        return $this->cssFiles;
    }

    private function _jsFiles()
    {
        if ( Sobi::Cfg( 'cache.include_files', true ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
            if ( count( $this->_cache[ 'js' ] ) ) {
                $compression = Sobi::Cfg( 'cache.compress_js', false );
                $comprLevel = Sobi::Cfg( 'cache.compress_level', 0 );
                $check = array( 'section' => Sobi::Section(), 'compress_level' => $comprLevel, 'compress_js' => $compression );
                foreach ( $this->_cache[ 'js' ] as $file ) {
                    $check[ $file ] = filemtime( $file );
                }
                $check = md5( serialize( $check ) );
                if ( !( SPFs::exists( SOBI_PATH . "/var/js/{$check}.js" ) ) ) {
                    $noCompress = explode( ',', Sobi::Cfg( 'cache.js_compress_exceptions' ) );
                    $jsContent = "\n/* Created at: " . date( SPFactory::config()->key( 'date.log_format', 'D M j G:i:s T Y' ) ) . " */\n";
                    foreach ( $this->_cache[ 'js' ] as $file ) {
                        $fName = str_replace( SOBI_ROOT, null, $file );
                        $jsContent .= "\n// ========\n// File: {$fName}\n// ========\n\n";
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
     * Send the header via the mainframe interface
     */
    public function send()
    {
        if ( count( $this->js ) ) {
            $jsCode = null;
            foreach ( $this->js as $js ) {
                $jsCode .= "\n\t" . str_replace( "\n", "\n\t", $js );
            }
            $this->js = array( "\n<script type=\"text/javascript\">\n/*<![CDATA[*/{$jsCode}\n/*]]>*/\n</script>\n" );
        }
        if ( count( $this->css ) ) {
            $cssCode = null;
            foreach ( $this->css as $css ) {
                $cssCode .= "\n\t" . str_replace( "\n", "\n\t", $css );
            }
            $this->css = array( "<style type=\"text/css\">\n{$cssCode}\n</style>" );
        }
        $this->head[ 'keywords' ] = $this->keywords;
        $this->head[ 'authors' ] = $this->authors;
        $this->head[ 'robots' ] = $this->robots;
        $this->head[ 'description' ] = $this->description;
        $this->head[ 'css' ] = $this->_cssFiles();
        $this->head[ 'js' ] = $this->_jsFiles();
        $this->head[ 'links' ] = $this->links;
        $this->head[ 'css' ] = array_merge( $this->head[ 'css' ], $this->css );
        $this->head[ 'js' ] = array_merge( $this->head[ 'js' ], $this->js );
        $this->head[ 'raw' ] = $this->raw;
        Sobi::Trigger( 'Header', 'Send', array( &$this->head ) );
        SPFactory::mainframe()->addHead( $this->head );
        $this->reset();
    }
}
