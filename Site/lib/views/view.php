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

SPLoader::loadView( 'interface' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Jan-2009 2:44:34 PM
 */
abstract class SPFrontView extends SPObject implements SPView
{
	/**
	 * @var array
	 */
	protected $_attr = array();
	/**
	 * @var array
	 */
	protected $_config = array();
	/**
	 * @var string
	 */
	protected $_template = null;
	/**
	 * @var array
	 */
	protected $_hidden = array();
	/**
	 * @var bool
	 */
	protected $_fout = true;
	/**
	 * @var bool
	 */
	protected $_plgSect = true;
	/**
	 * @var string
	 */
	protected $_type = 'root';
	/**
	 * @var DOMDocument
	 */
	protected $_xml = null;
	/**
	 * @var string
	 */
	protected $_task = null;
	/**
	 * @var string
	 */
	protected $_templatePath = null;
	/**
	 * @var string
	 */
	protected $tTask = null;
	/**
	 * @var array
	 */
	protected $nonStaticData = null;


	/**
	 */
	public function __construct( $tTask = null )
	{
		$this->tTask = $tTask;
		Sobi::Trigger( 'Create', $this->name(), array( &$this ) );
	}

	protected function tplPath()
	{
		if ( !$this->_templatePath ) {
			$tpl = Sobi::Cfg( 'section.template', SPC::DEFAULT_TEMPLATE );
			$file = explode( '.', $tpl );
			if ( strstr( $file[ 0 ], 'cms:' ) ) {
				$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
				$file = SPFactory::mainframe()->path( implode( '.', $file ) );
				$this->_templatePath = SPLoader::dirPath( $file, 'root', false, null );
			}
			else {
				$this->_templatePath = SPLoader::dirPath( 'usr.templates.' . $tpl, 'front', false, null );
			}
		}
		SPFactory::registry()->set( 'current_template_path', $this->_templatePath );
		return $this->_templatePath;
	}

	/**
	 *
	 * @param var
	 * @param label
	 * @return \SPFrontView
	 */
	public function & assign( &$var, $label )
	{
		$this->_attr[ $label ] =& $var;
		return $this;
	}

	/**
	 *
	 * @param var
	 * @param label
	 * @return $this
	 */
	public function & addHidden( $var, $label )
	{
		$this->_hidden[ $label ] = $var;
		return $this;
	}

	/**
	 *
	 * @param path
	 */
	public function loadCSSFile( $path )
	{
		Sobi::Trigger( 'loadCSSFile', $this->name(), array( &$path ) );
		if ( SPFs::exists( $this->tplPath() . DS . 'css' . DS . $path . '.css' ) ) {
			$path = 'absolute.' . $this->tplPath() . '.css.' . $path;
			SPFactory::header()->addCSSFile( $path, false, 'all' );
		}
		else {
			SPFactory::header()->addCSSFile( $path );
		}
	}

	/**
	 *
	 * @param path
	 */
	public function loadJsFile( $path )
	{
		Sobi::Trigger( 'loadJsFile', $this->name(), array( &$path ) );
		if ( SPFs::exists( $this->tplPath() . DS . 'js' . DS . $path . '.js' ) ) {
			$path = 'absolute.' . $this->tplPath() . '.js.' . $path;
			SPFactory::header()->addJsFile( $path );
		}
		else {
			SPFactory::header()->addJsFile( $path );
		}
	}

	public function parseTemplate()
	{
	}

	/**
	 * @param string $template
	 * @return $this
	 */
	public function & setTemplate( $template )
	{
		$file = explode( '.', $template );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$this->_template = SPLoader::path( $file, 'root', false, null );
		}
		else {
			$this->_template = SOBI_PATH . '/usr/templates/' . str_replace( '.', '/', $template );
		}
		Sobi::Trigger( 'setTemplate', $this->name(), array( &$this->_template ) );
		return $this;
	}

	/**
	 *
	 * @param string $title
	 */
	public function setTitle( $title )
	{
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		SPFactory::header()->setTitle( Sobi::Txt( $title ) );
	}

	/**
	 * Returns copy of stored key
	 *
	 * @param $key
	 * @param mixed $def
	 * @param string $section
	 * @internal param string $label
	 * @return mixed
	 */
	public function key( $key, $def = null, $section = 'general' )
	{
		if ( strstr( $key, '.' ) ) {
			$key = explode( '.', $key );
			$section = $key[ 0 ];
			$key = $key[ 1 ];
		}
		return isset( $this->_config[ $section ][ $key ] ) ? $this->_config[ $section ][ $key ] : Sobi::Cfg( $key, $def, $section );
	}

	/**
	 * Returns copy of stored key
	 *
	 * @param string $section
	 * @return array
	 */
	public function csection( $section )
	{
		return isset( $this->_config[ $section ] ) ? $this->_config[ $section ] : array();
	}

	private function pb()
	{
		/** WARNING!!!
		 * This part is "encoded" not to complicate or hide anything.
		 * The "Powered By" footer can be easily disabled in the SobiPro configuration.
		 * We are not forcing anyone to display it nor violate anyone's freedom!!
		 * But for some reason it happens from time to time that some very clever people instead of disable it the right way
		 * prefer to tinker in the core code which of course lead to the famous situation "no I cannot update because I modified the code"
		 *
		 * So this actually encoded here just to protect some people from their own, well, "intelligence" ....
		 * */
		$p = "YToxOntpOjA7czoxODI6IjxkaXYgaWQ9InNvYmlQcm9Gb290ZXIiPlBvd2VyZWQgYnkgPGEgdGl0bGU9IlNvYmlQcm8gLSBKb29tbGEgRGlyZWN0b3J5IENvbXBvbmVudCB3aXRoIGNvbnRlbnQgY29uc3RydWN0aW9uIHN1cHBvcnQiIGhyZWY9Imh0dHA6Ly93d3cuc2lnc2l1Lm5ldCIgdGFyZ2V0PSJfYmxhbmsiPlNpZ3NpdS5ORVQ8L2E+PC9kaXY+Ijt9";
		if ( !( Sobi::Cfg( 'show_pb', true ) ) || SPRequest::cmd( 'method', null, 'post' ) == 'xhr' ) {
			return;
		}
		try {
			$p = SPConfig::unserialize( $p );
		} catch ( SPException $x ) {
			return;
		}
		return $p[ 0 ];
	}

	protected function jsonDisplay()
	{
		echo json_encode( $this->_attr );
	}

	/**
	 *
	 */
	public function display( $o = null )
	{
		if ( SPRequest::cmd( 'format' ) == 'json' && Sobi::Cfg( 'output.json_enabled', false ) ) {
			return $this->jsonDisplay();
		}

		$this->templateSettings();
		$type = $this->key( 'template_type', 'xslt' );
		$f = null;
		$task = SPRequest::task();
		if ( $this->key( 'functions' ) ) {
			$f = $this->registerFunctions();
		}
		$out = null;
		if ( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		$parserClass = SPLoader::loadClass( 'mlo.template_' . $type );
		if ( $parserClass ) {
			/** @var $parser SPTemplateXSLT */
			$parser = new $parserClass();
		}
		else {
			throw new SPException( SPLang::e( 'CANNOT_LOAD_PARSER', $type ) );
		}
		$this->_attr[ 'template_path' ] = Sobi::FixPath( str_replace( SOBI_ROOT, Sobi::Cfg( 'live_site' ), $this->_templatePath ) );
		$messages = SPFactory::message()->getMessages();
		if ( count( $messages ) ) {
			foreach ( $messages as $type => $content ) {
				$this->_attr[ 'messages' ][ $type ] = array_values( $content );
			}
		}
		$parser->setProxy( $this );
		$parser->setData( $this->_attr );
		$parser->setXML( $this->_xml );
		$parser->setCacheData( array( 'hidden' => $this->_hidden ) );
		$parser->setType( $this->_type );
		$parser->setTemplate( $this->_template );
		Sobi::Trigger( 'Display', $this->name(), array( $type, &$this->_attr ) );
		$o = $o ? $o : strtolower( $this->key( 'output', $this->key( 'output', 'html' ), $this->tTask ) );
		$action = $this->key( 'form.action' );
		if ( $action ) {
			$opt = SPFactory::mainframe()->form();
			if ( is_array( $opt ) && count( $opt ) ) {
				foreach ( $opt as $l => $v ) {
					$this->addHidden( $v, $l );
				}
			}
			$form = $this->csection( 'form' );
			$form[ 'method' ] = ( isset( $form[ 'method' ] ) && $form[ 'method' ] ) ? $form[ 'method' ] : 'post';
			$out .= "\n<form ";
			foreach ( $form as $p => $v ) {
				$out .= $p . '="' . $v . '" ';
			}
			$out .= ">\n";
		}
		$out .= $parser->display( $o, $f );
		$hidden = null;
		if ( count( $this->_hidden ) ) {
			$this->_hidden[ SPFactory::mainframe()->token() ] = 1;
			foreach ( $this->_hidden as $name => $value ) {
				$hidden .= "\n<input type=\"hidden\" id=\"SP_{$name}\" name=\"{$name}\" value=\"{$value}\"/>";
			}
			// xhtml strict valid
			$hidden = "<div>{$hidden}</div>";
			$out .= $hidden;
		}
		$out .= $action ? "\n</form>\n" : null;
		/* SobiPro type specific content parser */
		Sobi::Trigger( 'ContentDisplay', $this->name(), array( &$out ) );
		/* common content parser */
		$cParse = $this->key( 'parse', -1 );
		/* if it was specified in the template config file or it was set in the section config and not disabled in the template config */
		if ( !( strstr( $task, '.edit' ) || strstr( $task, '.add' ) || in_array( $task, Sobi::Cfg( 'plugins.content_disable', array() ) ) ) ) {
			if ( $cParse == 1 || ( Sobi::Cfg( 'parse_template_content', false ) && $cParse == -1 ) ) {
				Sobi::Trigger( 'Parse', 'Content', array( &$out ) );
			}
		}
		header( 'SobiPro: ' . Sobi::Section() );
		if ( $o == 'html' && ( !strlen( SPRequest::cmd( 'format' ) ) || SPRequest::cmd( 'format' ) == 'html' || SPRequest::int( 'crawl' ) ) ) {
			$out .= $this->pb();
			if ( ( SPRequest::cmd( 'dbg' ) || Sobi::Cfg( 'debug' ) ) && Sobi::My( 'id' ) ) {
				$start = Sobi::Reg( 'start' );
				$mem = $start[ 0 ];
				$time = $start[ 1 ];
				$queries = SPFactory::db()->getCount();
				$mem = number_format( memory_get_usage() - $mem );
				$time = microtime( true ) - $time;
				SPConfig::debOut( "Memory: {$mem}<br/>Time: {$time}<br/> Queries: {$queries}" );
			}
			$templateName = Sobi::Cfg( 'section.template' );
			echo "\n<!-- Start of SobiPro component 1.3.x -->\n<div id=\"SobiPro\" class=\"SobiPro {$templateName}\">\n{$out}\n</div>\n<!-- End of SobiPro component; Copyright (C) 2011-2016 Sigsiu.NET GmbH -->\n";
		}
		else {
			$this->customOutput( $out );
		}
		Sobi::Trigger( 'AfterDisplay', $this->name() );
	}

	protected function customOutput( $output )
	{
		$header = $this->key( 'output.header', false );
		if ( $this->key( 'output.clear', false ) ) {
			SPFactory::mainframe()->cleanBuffer();
		}
		if ( strlen( $header ) ) {
			header( $header );
		}
		if ( SPRequest::int( 'crawl' ) ) {
			header( 'SobiPro: ' . Sobi::Section() );
		}
		echo $output;
		if ( $this->key( 'output.close', false ) ) {
			exit;
		}
	}

	private function registerFunctions()
	{
		$functions = array();
		$package = Sobi::Reg( 'current_template' );
		if ( SPFs::exists( Sobi::FixPath( $package . '/' . $this->key( 'functions' ) ) ) ) {
			$path = Sobi::FixPath( $package . '/' . $this->key( 'functions' ) );
			ob_start();
			$content = file_get_contents( $path );
			$class = array();
			preg_match( '/\s*(class)\s+(\w+)/', $content, $class );
			if ( isset( $class[ 2 ] ) ) {
				$className = $class[ 2 ];
			}
			else {
				Sobi::Error( $this->name(), SPLang::e( 'Cannot determine class name in file %s.', str_replace( SOBI_ROOT . DS, null, $path ) ), SPC::WARNING, 0 );
				return false;
			}
			require_once( $path );
			$methods = get_class_methods( $className );
			if ( count( $methods ) ) {
				foreach ( $methods as $method ) {
					$functions[ ] = $className . '::' . $method;
				}
			}
		}
		else {
			Sobi::Error( $this->name(), SPLang::e( 'FUNCFILE_DEFINED_BUT_FILE_DOES_NOT_EXISTS', $this->_template . DS . $this->key( 'functions' ) ), SPC::WARNING, 0 );
		}
		return $functions;
	}

	/**
	 * @param mixed $attr
	 * @param mixed $vars
	 */
	public function txt( $attr, $vars = null )
	{
		echo Sobi::Txt( $attr, $vars );
	}

	protected function metaKeys( $obj )
	{
		$arr = explode( Sobi::Cfg( 'string.meta_keys_separator', ',' ), $obj->get( 'metaKeys' ) );
		if ( count( $arr ) ) {
			foreach ( $arr as $i => $v ) {
				$arr[ $i ] = trim( $v );
			}
		}
		return $arr;
	}

	/**
	 * @internal param mixed $attr
	 * @return string
	 */
	public function field()
	{
		$params = func_get_args();
		$field = null;
		if ( isset( $params[ 0 ] ) ) {
			if ( method_exists( 'SPHtml_input', $params[ 0 ] ) ) {
				foreach ( $params as $i => $param ) {
					if ( is_string( $param ) && strstr( $param, 'value:' ) ) {
						$param = str_replace( 'value:', null, $param );
						$params[ $i ] = $this->get( $param );
					}
				}
				$method = $params[ 0 ];
				array_shift( $params );
				$field = call_user_func_array( array( 'SPHtml_Input', $method ), $params );
			}
			else {
				Sobi::Error( $this->name(), SPLang::e( 'METHOD_DOES_NOT_EXISTS', $params[ 0 ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		else {
			Sobi::Error( $this->name(), SPLang::e( 'NOT_ENOUGH_PARAMETERS' ), SPC::NOTICE, 0, __LINE__, __FILE__ );
		}
		if ( $this->_fout ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	/**
	 * @param array $cfg
	 * @param string $template
	 * @return $this
	 */
	public function & setConfig( $cfg, $template )
	{
		$this->_config = $cfg;
		if ( isset( $cfg[ $template ] ) && count( $cfg[ $template ] ) ) {
			foreach ( $cfg[ $template ] as $k => $v ) {
				$this->_config[ $k ] = $v;
			}
		}
		if ( isset( $this->_config[ 'general' ][ 'css_files' ] ) ) {
			$this->_config[ 'general' ][ 'css_files' ] = explode( ',', $this->_config[ 'general' ][ 'css_files' ] );
			foreach ( $this->_config[ 'general' ][ 'css_files' ] as $file ) {
				$this->loadCSSFile( trim( $file ) );
			}
		}
		if ( isset( $this->_config[ 'general' ][ 'js_files' ] ) ) {
			$this->_config[ 'general' ][ 'js_files' ] = explode( ',', $this->_config[ 'general' ][ 'js_files' ] );
			foreach ( $this->_config[ 'general' ][ 'js_files' ] as $file ) {
				if ( trim( $file ) ) {
					$this->loadJsFile( trim( $file ) );
				}
			}
		}
		if ( $this->key( 'site_title' ) ) {
			$this->setTitle( $this->key( 'site_title' ) );
		}
		if ( isset( $this->_config[ 'hidden' ] ) ) {
			foreach ( $this->_config[ 'hidden' ] as $name => $defValue ) {
				$this->addHidden( SPRequest::string( $name, $defValue ), $name );
			}
		}
		Sobi::Trigger( 'afterLoadConfig', $this->name(), array( &$this->_config ) );
		return $this;
	}

	/**
	 * @param mixed $attr
	 * @param int $index
	 */
	public function show( $attr, $index = -1 )
	{
		if ( strstr( $attr, 'config.' ) !== false ) {
			echo $this->key( str_replace( 'config.', null, $attr ) );
		}
		else {
			echo $this->get( $attr, $index );
		}
	}

	/**
	 * @param mixed $attr
	 * @param int $index
	 * @return int
	 */
	public function count( $attr, $index = -1 )
	{
		$el =& $this->get( $attr, $index );
		return count( $el );
	}

	/**
	 *
	 * @param mixed $attr
	 * @param mixed $name
	 * @internal param int $index
	 * @return mixed
	 */
	public function & set( $attr, $name )
	{
		$this->_attr[ $name ] = $attr;
		return $this;
	}

	/**
	 *
	 * @param mixed $attr
	 * @param int $index
	 * @return mixed
	 */
	public function & get( $attr, $index = -1 )
	{
		$r = null;
		if ( strstr( $attr, '.' ) ) {
			$properties = explode( '.', $attr );
		}
		else {
			$properties[ 0 ] = $attr;
		}
		if ( isset( $this->_attr[ $properties[ 0 ] ] ) ) {
			$var = null;
			/* if array field */
			if ( $index > -1 ) {
				if ( is_array( $this->_attr[ $properties[ 0 ] ] ) && isset( $this->_attr[ $properties[ 0 ] ][ trim( $index ) ] ) ) {
					$var = $this->_attr[ $properties[ 0 ] ][ trim( $index ) ];
				}
				else {
					Sobi::Error( $this->name(), SPLang::e( 'ATTR_DOES_NOT_EXISTS', $attr ), SPC::NOTICE, 0, __LINE__, __FILE__ );
				}
			}
			else {
				$var = $this->_attr[ $properties[ 0 ] ];
			}
			/* remove first field of properties */
			array_shift( $properties );
			/* if there are still fields in array, accessing object attribute or array field */
			if ( is_array( $properties ) && count( $properties ) ) {
				foreach ( $properties as $property ) {
					$property = trim( $property );
					/* it has to be SPObject subclass to access the attribute */
					if ( method_exists( $var, 'has' ) && $var->has( $property ) ) {
						if ( method_exists( $var, 'get' ) ) {
							$var = $var->get( $property );
						}
						else {
							/*@TODO need to create error object */
							$r = '';
							return $r;
						}
					}
					/* otherwise try to access array field */
					elseif ( is_array( $var ) /*&& key_exists( $property, $var )*/ ) {
						$var = $var[ $property ];
					}
					else {
						/* nothing to show */
						Sobi::Error( $this->name(), SPLang::e( 'NO_PROPERTY_TO_SHOW', $attr ), SPC::NOTICE, 0, __LINE__, __FILE__ );
						/*@TODO need to create error object */
						$r = '';
						return $r;
					}
				}
			}
			$r = $var;
		}
		else {
			$r = null;
		}
		$r = is_string( $r ) ? Sobi::Clean( $r ) : $r;
		return $r;
	}

	protected function alphaMenu( &$data )
	{
		if ( $this->key( 'alphamenu.show', Sobi::Cfg( 'alphamenu.show' ) ) ) {
			$letters = explode( ',', $this->key( 'alphamenu.letters', Sobi::Cfg( 'alphamenu.letters' ) ) );
			$entry = SPFactory::Model( 'entry' );
			$entry->loadFields( Sobi::Section() );
			$fs = $entry->getFields( 'id' );
			$defField = true;
			if ( count( $letters ) ) {
				foreach ( $letters as $i => $letter ) {
					$letters[ $i ] = trim( $letter );
				}
			}
			$field = explode( '.', SPRequest::task( 'get' ) );
			if ( strstr( SPRequest::task( 'get' ), 'field' ) && isset( $field[ 3 ] ) ) {
				$field = $field[ 3 ];
				$defField = false;
			}
			else {
				$field = Sobi::Cfg( 'alphamenu.primary_field', SPFactory::config()->nameField()->get( 'id' ) );
				if ( isset( $fs[ $field ] ) && ( $fs[ $field ] instanceof SPObject ) ) {
					$field = $fs[ $field ]->get( 'nid' );
				}
				else {
					$field = $fs[ SPFactory::config()->nameField()->get( 'id' ) ]->get( 'nid' );
				}
			}
			if ( $this->key( 'alphamenu.verify', Sobi::Cfg( 'alphamenu.verify' ) ) ) {
				$entries = SPFactory::cache()->getVar( 'alpha_entries_' . $field );
				if ( !$entries ) {
					$alphCtrl = SPFactory::Instance( 'opt.listing.alpha' );
					$entries = array();
					foreach ( $letters as $letter ) {
						$params = array( 'letter' => $letter );
						if ( $field ) {
							$params[ 'field' ] = $field;
						}
						$alphCtrl->setParams( $params );
						$entries[ $letter ] = $alphCtrl->entries( $field );
					}
					SPFactory::cache()->addVar( $entries, 'alpha_entries_' . $field );
				}
				foreach ( $letters as $letter ) {
					$le = array( '_complex' => 1, '_data' => trim( $letter ) );

					$urlLetter =
							SPFactory::Instance( 'types.string', $letter )
									->toLower()
									->trim()
									->get();
					if ( count( $entries[ $letter ] ) ) {
						if ( !( $defField ) ) {
							$task = 'list.alpha.' . $urlLetter . '.' . $field;
						}
						else {
							$task = 'list.alpha.' . $urlLetter;
						}
						$le[ '_attributes' ] = array( 'url' => Sobi::Url( array( 'sid' => Sobi::Section(), 'task' => $task ) ) );
					}
					$l[ ] = $le;
				}
			}
			else {
				foreach ( $letters as $i => $letter ) {
					$urlLetter =
							SPFactory::Instance( 'types.string', $letter )
									->toLower()
									->trim()
									->get();
					$l[ ] = array(
							'_complex' => 1,
							'_data' => trim( $letter ),
							'_attributes' => array( 'url' => Sobi::Url( array( 'sid' => Sobi::Section(), 'task' => 'list.alpha.' . $urlLetter ) ) )
					);
				}
			}
			$fields = Sobi::Cfg( 'alphamenu.extra_fields_array' );
			$extraFields = array();
			if ( count( $fields ) ) {
				array_unshift( $fields, Sobi::Cfg( 'alphamenu.primary_field' ) );
				foreach ( $fields as $fid ) {
					if ( isset( $fs[ $fid ] ) && method_exists( $fs[ $fid ], 'get' ) ) {
						if ( $fs[ $fid ]->get( 'enabled' ) ) {
							$extraFields[ $fs[ $fid ]->get( 'nid' ) ] = $fs[ $fid ]->get( 'name' );
						}
					}
				}
				if ( count( $extraFields ) < 2 ) {
					$extraFields = array();
				}
				$extraFields = array(
						'_complex' => 1,
						'_data' => $extraFields,
						'_attributes' => array( 'current' => $field )
				);
			}
			$data[ 'alphaMenu' ] = array( '_complex' => 1, '_data' => array( 'letters' => $l, 'fields' => $extraFields ) );
		}
	}

	protected function visitorArray( $visitor )
	{
		$usertype = $visitor->get( 'usertype' );
		if ( strlen( $usertype ) == 0 )
			$usertype = 'Visitor';
		return array(
				'_complex' => 1,
				'_data' => array(
						'name' => $visitor->get( 'name' ),
						'username' => $visitor->get( 'username' ),
						'usertype' => array(
								'_complex' => 1,
								'_data' => $usertype,
								'_attributes' => array( 'gid' => implode( ', ', $visitor->get( 'gid' ) ) )
						)
				),
				'_attributes' => array( 'id' => $visitor->get( 'id' ) )
		);
	}

	/**
	 */
	public function trigger( $action )
	{
		echo Sobi::TriggerPlugin( $action, $this->_plgSect );
	}

	/**
	 * @param int $id
	 * @param bool $parents
	 * @return array
	 */
	protected function parentPath( $id, $parents = false )
	{
		$path = SPFactory::config()->getParentPath( $id, true, $parents );
		if ( is_array( $path ) ) {
			$path = implode( Sobi::Cfg( 'string.path_separator', ' > ' ), $path );
		}
		else {
			$path = null;
		}
		return SPLang::clean( $path );
	}

	protected function loadNonStaticData( $objects )
	{
		$this->nonStaticData
				= SPFactory::db()
				->select( array( 'counter', 'sid' ), 'spdb_counter', array( 'sid' => $objects ) )
				->loadAssocList( 'sid' );
	}

	protected function getNonStaticData( $id, $att )
	{
		return isset( $this->nonStaticData[ $id ][ $att ] ) ? $this->nonStaticData[ $id ][ $att ] : null;
	}


	protected function fieldStruct( $fields, $view )
	{
		$data = array();
		foreach ( $fields as $field ) {
			if ( $field->enabled( $view ) && $field->get( 'id' ) != Sobi::Cfg( 'entry.name_field' ) ) {
				$struct = $field->struct();
				$options = null;
				if ( isset( $struct[ '_options' ] ) ) {
					$options = $struct[ '_options' ];
					unset( $struct[ '_options' ] );
				}
				$data[ $field->get( 'nid' ) ] = array(
						'_complex' => 1,
						'_data' => array(
								'label' => array(
										'_complex' => 1,
										'_data' => $field->get( 'name' ),
										'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
								),
								'data' => $struct,
						),
						'_attributes' => array( 'id' => $field->get( 'id' ),
								'itemprop' => $field->get( 'itemprop' ),
								'type' => $field->get( 'type' ),
								'suffix' => $field->get( 'suffix' ),
								'position' => $field->get( 'position' ),
								'css_view' => $field->get( 'cssClassView' ),
								'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' )
						)
				);
				if ( Sobi::Cfg( 'entry.field_description', false ) ) {
					$data[ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = array( '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) );
				}
				if ( $options ) {
					$data[ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
				}
				if ( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
					foreach ( $struct[ '_xml_out' ] as $k => $v )
						$data[ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
				}
			}
		}
		$this->validateFields( $data );
		return $data;
	}

	protected function validateFields( $fields )
	{
		foreach ( $fields as $data ) {
			if ( isset( $data[ '_data' ][ 'data' ][ '_validate' ] ) && count( $data[ '_data' ][ 'data' ][ '_validate' ] ) ) {
				$class = str_replace( array( '/', '.php' ), array( '.', null ), $data[ '_data' ][ 'data' ][ '_validate' ][ 'class' ] );
				if ( $class ) {
					$method = $data[ '_data' ][ 'data' ][ '_validate' ][ 'method' ];
					$class = SPLoader::loadClass( $class );
					$class::$method( $data[ '_data' ][ 'data' ] );
				}
			}
		}
	}

	protected function fixTimes( &$data )
	{
		$fix = array( 'valid_since', 'valid_until', 'updated_time', 'created_time' );
		static $offset = null;
		if ( $offset === null ) {
			$offset = SPFactory::config()->getTimeOffset();
		}
		foreach ( $fix as $index ) {
			if ( !( isset( $data[ $index ] ) ) || !( $data[ $index ] ) ) {
				continue;
			}
			$timestamp = strtotime( $data[ $index ] . 'UTC' );
			$data[ $index ] = array(
					'_complex' => 1,
					'_data' => gmdate( Sobi::Cfg( 'db.date_format', 'Y-m-d H:i:s' ), $timestamp + $offset ),
					'_attributes' => array(
							'UTC' => $data[ $index ],
							'timestamp' => $timestamp,
							'offset' => $offset,
							'timezone' => Sobi::Cfg( 'time_offset' )
					)
			);
		}
	}

	protected function menu( &$data )
	{
		if ( Sobi::Cfg( 'general.top_menu', true ) ) {
			$data[ 'menu' ] = array(
					'front' => array(
							'_complex' => 1,
							'_data' => Sobi::Reg( 'current_section_name' ),
							'_attributes' => array(
									'lang' => Sobi::Lang( false ), 'url' => Sobi::Url( array( 'sid' => Sobi::Section() ) )
							)
					)
			);
			if ( Sobi::Can( 'section.search' ) ) {
				$data[ 'menu' ][ 'search' ] = array(
						'_complex' => 1,
						'_data' => Sobi::Txt( 'MN.SEARCH' ),
						'_attributes' => array(
								'lang' => Sobi::Lang( false ), 'url' => Sobi::Url( array( 'task' => 'search', 'sid' => Sobi::Section() ) )
						)
				);
			}
			if ( Sobi::Can( 'entry', 'add', 'own', Sobi::Section() ) ) {
				$data[ 'menu' ][ 'add' ] = array(
						'_complex' => 1,
						'_data' => Sobi::Txt( 'MN.ADD_ENTRY' ),
						'_attributes' => array(
								'lang' => Sobi::Lang( false ), 'url' => Sobi::Url( array( 'task' => 'entry.add', 'sid' => SPRequest::sid() ) )
						)
				);
			}
		}
	}

	/**
	 * @return void
	 */
	protected function templateSettings()
	{
		if ( !( isset( $this->_attr[ 'config' ] ) && count( $this->_attr[ 'config' ] ) ) && SPLoader::translatePath( "{$this->_templatePath}.config", 'absolute', true, 'json' ) ) {
			$config = json_decode( SPFs::read( SPLoader::translatePath( "{$this->_templatePath}.config", 'absolute', true, 'json' ) ), true );
			$task = SPRequest::task() == 'entry.add' ? 'entry.edit' : SPRequest::task();
			$settings = array();
			foreach ( $config as $section => $setting ) {
				$settings[ str_replace( '-', '.', $section ) ] = $setting;
			}
			if ( SPLoader::translatePath( "{$this->_templatePath}.{$task}", 'absolute', true, 'json' ) ) {
				$subConfig = json_decode( SPFs::read( SPLoader::translatePath( "{$this->_templatePath}.{$task}", 'absolute', true, 'json' ) ), true );
				if ( count( $subConfig ) ) {
					foreach ( $subConfig as $section => $subSettings ) {
						foreach ( $subSettings as $k => $v ) {
							$settings[ str_replace( '-', '.', $section ) ][ $k ] = $v;
						}
					}
				}
			}
			if ( isset( $settings[ 'general' ] ) ) {
				foreach ( $settings[ 'general' ] as $k => $v ) {
					$this->_attr[ 'config' ][ $k ] = array(
							'_complex' => 1,
							'_attributes' => array(
									'value' => $v
							)
					);
				}
			}
			if ( isset( $settings[ $task ] ) ) {
				foreach ( $settings[ $task ] as $k => $v ) {
					$this->_attr[ 'config' ][ $k ] = array(
							'_complex' => 1,
							'_attributes' => array(
									'value' => $v
							)
					);
				}
			}
		}
	}
}
