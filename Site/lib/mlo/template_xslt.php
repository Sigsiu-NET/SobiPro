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

use Sobi\Input\Input;

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadClass( 'mlo.template' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Oct-2009 09:08:04 AM
 */
class SPTemplateXSLT implements SPTemplate
{
	/*** @var SPFrontView */
	private $_proxy = null;
	/*** @var array */
	private $_data = [];
	/*** @var string */
	private $_tpl = null;
	/*** @var string */
	private $_type = 'root';
	/*** @var DOMDocument */
	private $_xml = null;
	/*** @var array */
	private $_cacheData = [];

	/**
	 * @param array $cacheData
	 */
	public function setCacheData( $cacheData )
	{
		$this->_cacheData = $cacheData;
	}

	public function __construct()
	{
	}

	/**
	 * @param string $out - output type
	 * @param array $functions - array with PHP function to register
	 * @throws SPException
	 * @return mixed|string
	 */
	public function display( $out = 'html', $functions = [] )
	{
		$class = SPLoader::loadClass( 'helpers.template' );
		$methods = get_class_methods( $class );
		if ( count( $methods ) ) {
			foreach ( $methods as $method ) {
				$functions[] = $class . '::' . $method;
			}
		}
		/* standard function registered via the core ini file */
		$stdFunctions = SPLoader::loadIniFile( 'etc.template_functions' );
		if ( count( $stdFunctions ) ) {
			foreach ( $stdFunctions as $class => $fns ) {
				if ( strstr( $class, '.' ) ) {
					$class = SPLoader::loadClass( $class, false, 'sp-root' );
				}
				if ( count( $fns ) ) {
					foreach ( $fns as $method => $state ) {
						if ( $state ) {
							$functions[] = ( $class == 'functions' ) ? $method : $class . '::' . $method;
						}
					}
				}
			}
		}
		Sobi::Trigger( 'TemplateEngine', 'RegisterFunctions', [ &$functions ] );
		$this->createXML();
		if ( Input::Cmd( 'xml' ) && Sobi::Cfg( 'debug.xml_raw', false ) && ( !( Sobi::Cfg( 'debug.xml_ip', null ) ) || ( Sobi::Cfg( 'debug.xml_ip' ) == SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ) ) ) ) {
			SPFactory::mainframe()->cleanBuffer();
			echo $this->_xml->saveXML();
			exit();
		}
		elseif ( Input::Cmd( 'xml' ) ) {
			Sobi::Error( 'Debug', 'You have no permission to access this site', SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$template = SPLoader::loadTemplate( $this->_tpl, 'xsl' );
		if ( !( $template ) ) {
			$template = SPLoader::loadTemplate( $this->_tpl, 'xslt' );
		}
		if ( Sobi::Cfg( 'cache.xml_enabled' ) ) {
			SPFactory::cache()->addView( $this->_xml, $template, $this->_cacheData );
		}
		if ( $template ) {
			try {
				$tplDoc = new DOMDocument();
				if ( !( $tplDoc->load( $template ) ) ) {
					Sobi::Error( 'template', SPLang::e( 'CANNOT_PARSE_TEMPLATE_FILE', $template ), SPC::ERROR, 500, __LINE__, __FILE__ );
				}
			} catch ( DOMException $x ) {
				Sobi::Error( 'template', SPLang::e( 'CANNOT_LOAD_TEMPLATE_FILE', $template, $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			Sobi::Trigger( 'TemplateEngine', 'LoadStyle', [ &$tplDoc ] );
			$processor = new XSLTProcessor();
			$processor->setParameter( 'block', 'xmlns', 'http://www.w3.org/1999/xhtml' );
			$processor->registerPHPFunctions( $functions );
			SPException::catchErrors( SPC::WARNING );
			try {
				$processor->importStylesheet( $tplDoc );
			} catch ( SPException $x ) {
				Sobi::Error( 'template', SPLang::e( 'CANNOT_PARSE_TEMPLATE_FILE', $template ) . $x->getMessage(), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			SPException::catchErrors( 0 );
			if ( $out == 'html' ) {
				return $this->cleanOut( $processor->transformToXml( $this->_xml ) );
			}
			else {
				$doc = $processor->transformToDoc( $this->_xml );
				$doc->formatOutput = true;
				return $doc->saveXML();
			}
		}
		else {
			throw new SPException( SPLang::e( 'CANNOT_LOAD_TEMPLATE_FILE_AT', SPLoader::loadTemplate( $this->_tpl, 'xsl', false ) ) );
		}
	}

	private function cleanOut( $out )
	{
		// @todo: it should be removed in the right way
		$out = str_replace( 'xmlns:php="http://php.net/xsl" ', null, $out );
		$out = str_replace( 'xmlns:php="http://php.net/xsl"', null, $out );
		$out = preg_replace( "/<!DOCTYPE [^>]+>/", '', $out );
		$out = str_replace( '&amp;amp;', '&amp;', $out );
		$out = preg_replace( "/<\?xml [^>]+>/", '', $out );
		return $out;
	}

	/**
	 * @param string $type the listing type to set
	 */
	public function setType( $type )
	{
		$this->_type = $type;
	}

	/**
	 * @param DOMDocument $XML the listing type to set
	 */
	public function setXML( $XML )
	{
		$this->_xml = $XML;
	}

	private function createXML()
	{
		if ( !( $this->_xml ) ) {
			$this->_xml = new DOMDocument( Sobi::Cfg( 'xml.version', '1.0' ), Sobi::Cfg( 'xml.encoding', 'UTF-8' ) );
			$this->_xml->formatOutput = true;
			if ( count( $this->_data ) ) {
				$e = $this->_xml->createElement( $this->_type );
				foreach ( $this->_data as $root => $data ) {
					$this->createNode( $data, $e, $root );
				}
				$this->_xml->appendChild( $e );
			}
		}
		Sobi::Trigger( 'TemplateEngine', ucfirst( __FUNCTION__ ), [ &$this->_xml ] );
	}

	private static function entities( $txt )
	{
		$entities = [ 'auml' => '&#228;', 'ouml' => '&#246;', 'uuml' => '&#252;', 'szlig' => '&#223;', 'Auml' => '&#196;', 'Ouml' => '&#214;', 'Uuml' => '&#220;', 'nbsp' => '&#160;', 'Agrave' => '&#192;', 'Egrave' => '&#200;', 'Eacute' => '&#201;', 'Ecirc' => '&#202;', 'egrave' => '&#232;', 'eacute' => '&#233;', 'ecirc' => '&#234;', 'agrave' => '&#224;', 'iuml' => '&#239;', 'ugrave' => '&#249;', 'ucirc' => '&#251;', 'uuml' => '&#252;', 'ccedil' => '&#231;', 'AElig' => '&#198;', 'aelig' => '&#330;', 'OElig' => '&#338;', 'oelig' => '&#339;', 'angst' => '&#8491;', 'cent' => '&#162;', 'copy' => '&#169;', 'Dagger' => '&#8225;', 'dagger' => '&#8224;', 'deg' => '&#176;', 'emsp' => '&#8195;', 'ensp' => '&#8194;', 'ETH' => '&#208;', 'eth' => '&#240;', 'euro' => '&#8364;', 'half' => '&#189;', 'laquo' => '&#171;', 'ldquo' => '&#8220;', 'lsquo' => '&#8216;', 'mdash' => '&#8212;', 'micro' => '&#181;', 'middot' => '&#183;', 'ndash' => '&#8211;', 'not' => '&#172;', 'numsp' => '&#8199;', 'para' => '&#182;', 'permil' => '&#8240;', 'puncsp' => '&#8200;', 'raquo' => '&#187;', 'rdquo' => '&#8221;', 'rsquo' => '&#8217;', 'reg' => '&#174;', 'sect' => '&#167;', 'THORN' => '&#222;', 'thorn' => '&#254;', 'trade' => '&#8482;' ];
		foreach ( $entities as $ent => $repl ) {
			$txt = preg_replace( '/&' . $ent . ';?/m', $repl, $txt );
		}
		/* html entities to compatible XML within textareas */
		if ( strstr( $txt, '<textarea>' ) ) {
			$txt = preg_replace_callback( '/(<textarea.*>)(.*)(<\/textarea>)/s', 'SPTemplateXSLT::entities', $txt );
		}
		return trim( $txt );
	}

	public function XML()
	{
		$this->createXML();
		echo $this->_xml->saveXML();
	}

	/**
	 * @param $data
	 * @param $parent
	 * @param $name
	 * @return void
	 */
	private function createNode( $data, &$parent, $name = null )
	{
		if ( $name ) {
			$e = $this->_xml->createElement( $this->elName( $name ) );
		}
		else {
			$e = $parent;
		}
		/* not complex data - without attributes aso */
		if ( is_array( $data ) && !( isset( $data[ '_complex' ] ) ) ) {
			foreach ( $data as $label => $values ) {
				/* half-complex data - just to define custom child tag */
				if ( is_array( $values ) && isset( $values[ '_value' ] ) ) {
					if ( isset( $values[ '_class' ] ) || isset( $values[ '_id' ] ) ) {
						$label = isset( $values[ '_tag' ] ) ? $values[ '_tag' ] : $label;
						$attr = [];
						if ( isset( $values[ '_class' ] ) ) {
							$attr[ 'class' ] = $values[ '_class' ];
						}
						if ( isset( $values[ '_id' ] ) ) {
							$attr[ 'id' ] = $values[ '_id' ];
						}
						$values = [
								'_complex' => 1,
								'_data' => $values[ '_value' ],
								'_attributes' => $attr
						];
					}
					else {
						$label = isset( $values[ '_tag' ] ) ? $values[ '_tag' ] : $label;
						$values = $values[ '_value' ];
					}
				}
				if ( !$label || is_integer( $label ) ) {
					$label = SPLang::singular( $name );
				}
				$this->createNode( $values, $e, $label );
			}
		}
		/* complex data with attributes aso */
		elseif ( is_array( $data ) && isset( $data[ '_complex' ] ) ) {
			if ( isset( $data[ '_xml' ] ) && $data[ '_xml' ] ) {
				$_t = new DOMDocument();
				$_t->formatOutput = true;

				/* html entities to compatible XML within textareas */
//				$data[ '_data' ] = preg_replace_callback( '/(<textarea.*>)(.*)(<\/textarea>)/s', 'SPTemplateXSLT::entities', $data[ '_data' ] );
				try {
					/* im trying to catch this damn error already :( */
					/* assuming the XML-Structure was ok */
					if ( @$_t->loadXML( '<span>' . $this->entities( $data[ '_data' ] ) . '</span>' ) ) {
						$nodes = $_t->firstChild->childNodes;
					}
					/* if not; try to repair with tidy */
					elseif ( ( class_exists( 'tidy' ) ) && @$_t->loadXML( '<span>' . $this->repairHtml( $data[ '_data' ] ) . '</span>' ) ) {
						$nodes = $_t->firstChild->childNodes;
					}
					/* if repair failed too .... */
					else {
						/* pass as escaped html text to the template */
						$data[ '_attributes' ][ 'escaped' ] = 'true';
						if ( !( @$_t->loadXML( '<span>' . htmlentities( $data[ '_data' ] ) . '</span>' ) ) ) {
							/* in case even this failed - pass as CDATA section */
							$e->appendChild( $this->_xml->createCDATASection( $data[ '_data' ] ) );
							$nodes = null;
						}
						else {
							$nodes = $_t->firstChild->childNodes;
						}
					}
				} catch ( DOMException $x ) {
					Sobi::Error( 'template', $x->getMessage(), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
				if ( count( $nodes ) ) {
					foreach ( $nodes as $node ) {
						if ( $node instanceof DOMNode ) {
							$node = $this->_xml->importNode( $node, true );
							$e->appendChild( $node );
						}
						else {
							Sobi::Error( 'template', 'Cannot parse data', SPC::WARNING, 0, __LINE__, __FILE__ );
						}
					}
				}
			}
			elseif ( isset( $data[ '_cdata' ] ) && $data[ '_cdata' ] ) {
				$e->appendChild( $this->_xml->createCDATASection( $data[ '_data' ] ) );
			}
			elseif ( isset( $data[ '_data' ] ) && is_array( $data[ '_data' ] ) ) {
				$this->createNode( $data[ '_data' ], $e );
			}
			elseif ( isset( $data[ '_data' ] ) ) {
				//$data[ '_attributes' ][ 'escaped' ] = 'true';
				$e->appendChild( $this->_xml->createTextNode( ( $data[ '_data' ] ) ) );
			}
			if ( isset( $data[ '_attributes' ] ) && is_array( $data[ '_attributes' ] ) && count( $data[ '_attributes' ] ) ) {
				foreach ( $data[ '_attributes' ] as $an => $av ) {
					$an = SPLang::varName( $an );
					// legacy for 1.0
					// data- is allowed html5 attribute but data_ will not be valid
					if ( strstr( $an, '-' ) && !( strstr( $an, 'data-' ) ) ) {
						$a = $this->_xml->createAttribute( str_replace( '-', '_', $an ) );
						$a->appendChild( $this->_xml->createTextNode( $av ) );
						$e->appendChild( $a );
					}
					$a = $this->_xml->createAttribute( $an );
					$a->appendChild( $this->_xml->createTextNode( $av ) );
					$e->appendChild( $a );
				}
			}
		}
		elseif ( $name ) {
			$e = $this->_xml->createElement( $this->elName( $name ), SPLang::entities( $data, true ) );
		}
		if ( $name ) {
			$parent->appendChild( $e );
		}
	}

	private function repairHtml( $node )
	{
		if ( class_exists( 'tidy' ) ) {
			$c = [
					'clean' => true,
					'output-xhtml' => true,
					'show-body-only' => true,
					'input-xml' => true
			];
			$tidy = new tidy();
			$tidy->parseString( $node, $c, 'utf8' );
			$tidy->cleanRepair();
			$node = tidy_get_output( $tidy );
			$tidy->diagnose();
			//			SPConfig::debOut( $tidy->errorBuffer );
			return $node;
		}
		return "Error - cannot repair";
	}

	private function elName( $str )
	{
		return preg_replace( '/^\_{1}([a-zA-Z0-9\-\_\.]{1,})\_[0-9]{1,}$/i', '\1', $str );
	}

	/** (non-PHPdoc)
	 * @var SPFrontView $proxy
	 * @see Site/lib/mlo/SPTemplate#setProxy()
	 */
	public function setProxy( &$proxy )
	{
		Sobi::Trigger( 'TemplateEngine', ucfirst( __FUNCTION__ ), [ &$proxy ] );
		$this->_proxy =& $proxy;
	}

	/** (non-PHPdoc)
	 * @var array $data
	 * @see Site/lib/mlo/SPTemplate#setData()
	 */
	public function setData( $data )
	{
		Sobi::Trigger( 'TemplateEngine', ucfirst( __FUNCTION__ ), [ &$data ] );
		$this->_data =& $data;
	}

	/** (non-PHPdoc)
	 * @var string $template
	 * @see Site/lib/mlo/SPTemplate#setTemplate()
	 */
	public function setTemplate( $template )
	{
		Sobi::Trigger( 'TemplateEngine', ucfirst( __FUNCTION__ ), [ &$template ] );
		$this->_tpl = $template;
	}

	/**
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	public function __call( $method, $params )
	{
		Sobi::Trigger( 'TemplateEngine', ucfirst( $method ), [ &$method, &$params ] );
		return call_user_func_array( [ $this->_proxy, $method ], $params );
	}
}
