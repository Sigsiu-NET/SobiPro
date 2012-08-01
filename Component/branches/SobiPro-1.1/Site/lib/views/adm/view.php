<?php
/**
 * @version: $Id: view.php 1887 2011-09-19 18:00:20Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-09-19 20:00:20 +0200 (Mon, 19 Sep 2011) $
 * $Revision: 1887 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/view.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'interface' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Jan-2009 2:44:34 PM
 */
class SPAdmView extends SPObject implements SPView
{
	/**
	 * @var array
	 */
	protected $_attr = array();
	/**
	 * @var array
	 */
	private $_config = array();
	/**
	 * @var string
	 */
	protected $_template = null;
	/**
	 * @var string
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
	 */
	public function __construct()
	{
		SPLoader::loadClass( 'helpers.adm.lists' );
		SPLoader::loadClass( 'mlo.input' );
		Sobi::Trigger( 'Create', $this->name(), array( &$this ) );
	}

	/**
	 *
	 * @param var
	 * @param label
	 */
	public function assign( &$var, $label )
	{
		$this->_attr[ $label ] =& $var;
	}

	/**
	 *
	 * @param var
	 * @param label
	 */
	public function addHidden( $var, $label )
	{
		$this->_hidden[ $label ] = $var;
	}

	/**
	 * @param string $path
	 */
	public function loadConfig( $path )
	{
		if( strlen( $path ) ) {
			$this->_config =& SPLoader::loadIniFile( $path, true, true, true );
		}
		Sobi::Trigger( 'beforeLoadConfig', $this->name(), array( &$this->_config ) );
		if( isset( $this->_config[ 'css_files' ] ) ) {
			foreach ( $this->_config[ 'css_files' ] as $file ) {
				$this->loadCSSFile( $file );
			}
			unset( $this->_config[ 'css_files' ] );
		}
		if( isset( $this->_config[ 'js_files' ] ) ) {
			foreach ( $this->_config[ 'js_files' ] as $file ) {
				$this->loadJsFile( $file );
			}
			unset( $this->_config[ 'js_files' ] );
		}
		if( $this->key( 'site_title' ) ) {
			$this->setTitle( $this->key( 'site_title' ) );
		}
		if( isset( $this->_config[ 'toolbar' ] ) ) {
			/* in case we are adding new entry/category/field we have to remove the 'duplicate' button
			 and the separators after and before*/
			if( $this->get( 'task' ) == 'add' || $this->get( 'task' ) == 'new' ) {
				$previous = null;
				$next = false;
				foreach ( $this->_config[ 'toolbar' ] as $key => $value ) {
					$previous = $key;
					if( $key == 'duplicate' ) {
						if( $next && isset( $this->_config[ 'toolbar' ][ $key ] )) {
							unset( $this->_config[ 'toolbar' ][ $key ] );
							break;
						}
						unset( $this->_config[ 'toolbar' ][ 'duplicate' ] );
						if( $previous && isset( $this->_config[ 'toolbar' ][ $previous ] ) ) {
							unset( $this->_config[ 'toolbar' ][ $previous ] );
						}
						$next = true;
					}
				}
			}
			SPLoader::loadClass( 'cms.html.admin_menu' );
			foreach ( $this->_config[ 'toolbar' ] as $type => $settings ) {
				$type = preg_replace( '/\_{1}[a-zA-Z0-9]$/', null, $type );
				$cfg = $this->parseMenu( explode( '|', $settings ) );

				call_user_func_array( array( 'SPAdmMenu', $type ), $cfg );
			}
			unset( $this->_config[ 'toolbar' ] );
		}
		if( !( isset( $this->_config[ 'submenu' ] ) ) ) {
			$this->_config[ 'submenu' ] = SPLoader::loadIniFile( 'etc.adm.submenu', false );
		}
		if( isset( $this->_config[ 'submenu' ] ) ) {
			SPLoader::loadClass( 'cms.html.admin_menu' );
			foreach ( $this->_config[ 'submenu' ] as $type => $settings ) {
				$type = preg_replace( '/\_{1}[a-zA-Z0-9]$/', null, $type );
				$cfg = $this->parseMenu( explode( '|', $settings ) );
				call_user_func_array( array( 'SPAdmMenu', 'addSubMenuEntry' ), $cfg );
			}
			unset( $this->_config[ 'submenu' ] );
		}
		if( isset( $this->_config[ 'hidden' ] ) ) {
			foreach ( $this->_config[ 'hidden' ] as $name => $defValue ) {
				$this->addHidden( SPRequest::string( $name, $defValue ), $name );
			}
		}
		Sobi::Trigger( 'afterLoadConfig', $this->name(), array( &$this->_config ) );
	}

	/**
	 * @param array $cfg
	 * @return array
	 */
	public function parseMenu( $cfg )
	{
		if( count( $cfg ) ) {
			foreach ( $cfg as $i => $key ) {
				if( strstr( $key, 'var:[' ) ) {
					preg_match( '/var\:\[([a-zA-Z0-9\.\_\-]*)\]/', $key, $matches );
					$key = str_replace( $matches[ 0 ], $this->get( $matches[ 1 ] ), $key );
				}
				if( strstr( $key, '->' ) ) {
					$key 		= explode( '->', $key );
					$callback 	= trim( $key[ 0 ] );
					$params 	= isset( $key[ 1 ] ) ? trim( $key[ 1 ] ) : null;
					if( strstr( $callback, '.' ) ) {
						$callback = explode( '.', $callback );
						$class = trim( $callback[ 0 ] );
						if( !class_exists( $class ) ) {
							$class 	= 'SP'.ucfirst( $class );
						}
						$method	= isset( $callback[ 1 ] ) ? trim( $callback[ 1 ] ) : null;
						if( $method && class_exists( $class ) && method_exists( $class, $method ) ) {
							$cfg[ $i ] = call_user_func_array( array( $class, $method ), array( $params ) );
						}
						else {
							Sobi::Error( 'Function from INI', SPLang::e( 'Function %s::%s does not exists.', $class, $method ), SPC::WARNING, 0, __LINE__, __FILE__ );
						}
					}
					else {
						if( function_exists( $callback ) ) {
							$cfg[ $i ] = call_user_func_array( $callback, $params );
						}
						else {
							Sobi::Error( 'Function from INI', SPLang::e( 'Function %s does not exists.', $callback ), SPC::WARNING, 0, __LINE__, __FILE__ );
						}
					}
				}
				else {
					$cfg[ $i ] = trim( $key );
				}
			}
		}
		return $cfg;
	}

	/**
	 *
	 * @param path
	 */
	public function loadCSSFile( $path )
	{
		Sobi::Trigger( 'loadCSSFile', $this->name(), array( &$path ) );
		$adm = true;
		if( strstr( $path, '|' ) ) {
			$path = explode( '|', $path );
			$adm = $path[1];
			$path = $path[0];
		}
		SPFactory::header()->addCSSFile( $path, $adm );
	}

	/**
	 *
	 * @param path
	 */
	public function loadJsFile( $path )
	{
		Sobi::Trigger( 'loadJsFile', $this->name(), array( &$path ) );
		$adm = true;
		if( strstr( $path, '|' ) ) {
			$path = explode( '|', $path );
			$adm = $path[1];
			$path = $path[0];
		}
		SPFactory::header()->addJsFile( $path, $adm );
	}

	public function parseTemplate()
	{
	}

	/**
	 * @param string $template
	 */
	public function setTemplate( $template )
	{
		$this->_template = $template;
		Sobi::Trigger( 'setTemplate', $this->name(), array( &$this->_template ) );
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
	 * @param string $label
	 * @param mixed $def
	 * @param string $section
	 * @return mixed
	 */
	protected function key( $key, $def = null, $section = 'general' )
	{
		if( strstr( $key, '.' ) ) {
			$key = explode( '.', $key );
			$section = $key[ 0 ];
			$key = $key[ 1 ];
		}
		return isset( $this->_config[ $section ][ $key ] ) ? $this->_config[ $section ][ $key ] : Sobi::Cfg( $key, $def, $section );
	}

	/**
	 * @param mixed $attr
	 * @param mixed $vars
	 */
	protected function txt( $attr, $vars = null )
	{
		if( strpos( $attr, '[JS]' ) === false ) {
			echo str_replace( ' ', '&nbsp;', Sobi::Txt( $attr, $vars ) );
		}
		else {
			echo Sobi::Txt( $attr, $vars );
		}
	}

	/**
	 * @param mixed $attr
	 */
	protected function date( $date, $start = true )
	{
		$config =& SPFactory::config();
		$date = $config->date( $date );
		if( $date == 0 ) {
			$date = $start ? Sobi::Txt( 'ALWAYS_VALID' ) : Sobi::Txt( 'NEVER_EXPIRES' );
		}
		return $date;
	}

	/**
	 * @param mixed $attr
	 * @return string
	 */
	protected function field()
	{
		$params = func_get_args();
		$field = null;
		if( isset( $params[ 0 ] ) ) {
			if( method_exists( 'SPHtml_input', $params[ 0 ] ) ) {
				foreach ( $params as $i => $param ) {
					if( is_string( $param ) && strstr( $param, 'value:' ) ) {
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
		if( $this->_fout ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	/**
	 * @param mixed $attr
	 * @param int $index
	 */
	protected function show( $attr, $index = -1 )
	{
		if( strstr( $attr, 'config.' ) !== false ) {
			echo $this->key( str_replace( 'config.', null, $attr ) );
		}
		else {
			echo $this->get( $attr, $index );
		}
	}

	/**
	 *
	 * @param mixed $attr
	 * @param int $index
	 * @return int
	 */
	protected function count( $attr, $index = -1 )
	{
		$el =& $this->get( $attr, $index );
		return count( $el );
	}

	/**
	 *
	 * @param mixed $attr
	 * @param int $index
	 * @return mixed
	 */
	protected function set( $attr, $name )
	{
		$this->_attr[ $name ] = $attr;
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
		if( strstr( $attr, '.' ) ) {
			$properties = explode( '.', $attr );
		}
		else {
			$properties[ 0 ] = $attr;
		}
		if( isset( $this->_attr[ $properties[ 0 ]  ] ) ) {
			$var = null;
			/* if array field */
			if( $index > -1 ) {
				if( is_array( $this->_attr[ $properties[ 0 ] ] ) && isset( $this->_attr[ $properties[ 0 ] ][ trim( $index ) ] ) ) {
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
			if( is_array( $properties ) && count( $properties ) ) {
				foreach ( $properties as $property ) {
					$property = trim( $property );
					/* it has to be SPObject subclass to access the attribute */
					if( method_exists( $var, 'has' ) /*&& $var->has( $property )*/ ) {
						if( method_exists( $var, 'get' ) ) {
							$var = $var->get( $property );
						}
					}
					/* otherwise try to access std object */
					elseif ( is_object( $var ) && isset( $var->$property ) ) {
						$var = $var->$property;
					}
					/* otherwise try to access array field */
					elseif ( is_array( $var ) && isset( $var[ $property ] ) ) {
						$var = $var[ $property ];
					}
					else {
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

	/**
	 *
	 */
	public function display()
	{
		$tpl = SPLoader::path( $this->_template.'_override', 'adm.template' );
		if( !( $tpl ) ) {
			$tpl = SPLoader::path( $this->_template, 'adm.template' );
		}
		if( !$tpl ) {
			$tpl = SPLoader::translatePath( $this->_template, 'adm.template', false );
			Sobi::Error( $this->name(), SPLang::e( 'TEMPLATE_DOES_NOT_EXISTS', $tpl ), SPC::ERROR, 500, __LINE__, __FILE__ );
			exit();
		}
		Sobi::Trigger( 'Display', $this->name(), array( &$this ) );
		$action = $this->key( 'action' );
		echo $action ? "\n<form action=\"{$action}\" method=\"post\" name=\"adminForm\" id=\"SPAdminForm\" enctype=\"multipart/form-data\" accept-charset=\"utf-8\" >\n" : null;
		include( $tpl );
		if( count( $this->_hidden ) ) {
			$this->_hidden[ SPFactory::mainframe()->token() ] = 1;
			foreach ( $this->_hidden as $name => $value ) {
				echo "\n<input type=\"hidden\" name=\"{$name}\" id=\"{$name}\" value=\"{$value}\"/>";
			}
		}
		echo $action ? "\n</form>\n" : null;
		Sobi::Trigger( 'AfterDisplay', $this->name() );
	}

	/**
	 */
	protected function menu()
	{
		$m = $this->get( 'menu' );
		if( $m && method_exists( $m, 'display' ) ) {
			echo $m->display();
		}
	}

	/**
	 * @param int $id
	 * @return SPUser
	 */
	protected function userData( $ids )
	{
		return SPUser::getBaseData( $ids );
	}

	/**
	 */
	protected function trigger( $action )
	{
		echo Sobi::TriggerPlugin( $action, $this->_plgSect );
	}

	/**
	 * @param int $id
	 * @return array
	 */
	protected function parentPath( $id, $parents = false )
	{
		$path = SPFactory::config()->getParentPath( $id, true, $parents );
		if( is_array( $path ) ) {
			if( strstr( $this->get( 'task' ), 'edit' ) ) {
				unset( $path[ count( $path ) - 1 ] );
			}
			$path = implode( Sobi::Cfg( 'string.path_separator', ' > ' ), $path );
		}
		else {
			$path = null;
		}
		return SPLang::clean( $path );
	}
}
?>