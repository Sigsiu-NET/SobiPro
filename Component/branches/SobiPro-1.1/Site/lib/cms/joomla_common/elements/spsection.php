<?php
/**
 * @version: $Id: spsection.php 1446 2011-05-29 13:21:34Z Radek Suski $
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
 * $Date: 2011-05-29 15:21:34 +0200 (Sun, 29 May 2011) $
 * $Revision: 1446 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla_common/elements/spsection.php $
 */

defined('_JEXEC') or die();
defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
defined ( 'SOBI_CMS' ) || define( 'SOBI_CMS', 'joomla15' );
define( 'SOBIPRO', true );
define( 'SOBIPRO_ADM', true );
define( 'SOBI_TASK', 'task' );
define( 'SOBI_DEFLANG', JFactory::getLanguage()->getDefault() );
define( 'SOBI_ACL', 'adm' );
define( 'SOBI_ROOT', JPATH_ROOT );
define( 'SOBI_PATH', SOBI_ROOT . DS . 'components' . DS . 'com_sobipro' );
define( 'SOBI_ADM_PATH', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_sobipro' );
$adm = str_replace( JPATH_ROOT, null, JPATH_ADMINISTRATOR );
define( 'SOBI_ADM_FOLDER', $adm  );
define( 'SOBI_ADM_LIVE_PATH', $adm . '/components/com_sobipro' );
define( 'SOBI_LIVE_PATH', JURI::base().'components/com_sobipro/' );

class JElementSPSection extends JElement
{
	public static function getInstance()
	{
		static $instance = null;
		if( !( $instance instanceof self ) ) {
			$instance = new self();
		}
		return $instance;
	}

	public function __construct()
	{
		static $loaded = false;
		if( $loaded ) {
			return true;
		}
		require_once ( SOBI_PATH . DS . 'lib' . DS . 'base' . DS . 'fs' . DS . 'loader.php' );
		/* load all needed classes */
		SPLoader::loadController( 'interface' );
		SPLoader::loadClass( 'base.filter' );
		SPLoader::loadClass( 'base.request' );
		SPLoader::loadClass( 'base.const' );
		SPLoader::loadClass( 'base.factory' );
		SPLoader::loadClass( 'base.object' );
		SPLoader::loadClass( 'base.filter' );
		SPLoader::loadClass( 'base.request' );
		SPLoader::loadClass( 'sobi' );
		SPLoader::loadClass( 'base.config' );
		SPLoader::loadClass( 'base.exception' );
		SPLoader::loadClass( 'cms.base.lang' );
		SPLoader::loadClass( 'mlo.input' );
		SPFactory::config()->set( 'live_site', JURI::root() );
		SPFactory::config()->set( 'language', JFactory::getConfig()->getValue( 'language' ) );
		SPFactory::config()->addTable( 'spdb_config', 0 );
		$head =& SPFactory::header();
		$ccurl = Sobi::Url( array( 'task' => 'category.chooser', 'out' => 'html', 'treetpl' => 'rchooser' ), true );
		$ccmsg = Sobi::Txt( 'JS_SELECT_CATEGORY' );
		$cemsg = Sobi::Txt( 'JS_PLEASE_SELECT_SECTION_FIRST' );
		JHTML::_( 'behavior.modal' );
		/* load admin html files */
		SPFactory::header()->addJsFile( 'sobipro' );
		SPFactory::header()->addJsFile( 'adm.sobipro' );

		$head->addJsCode( '
			window.addEvent( "domready", function() {
					var semaphor = 0;
					var spApply = $$( "#toolbar-apply a" )[ 0 ];
					var spSave = $$( "#toolbar-save a" )[ 0 ];
					spApplyFn = spApply.onclick;
					spApply.onclick = null;
					spSaveFn = spSave.onclick;
					spSave.onclick = null;
					try {
						var spSaveNew = $$( "#toolbar-save-new a" )[ 0 ];
						spSaveNewFn = spSaveNew.onclick;
						spSaveNew.onclick = null;
						spSaveNew.addEvent( "click", function() {
							if( SPValidate() ) {
								spSaveNewFn();
							}
						} );
					} catch( e ) {}

					function SPValidate()
					{
						if( $( "sid" ).value == 0 || $( "sid" ).value == "" ) {
							alert( "'.Sobi::Txt( 'JS_YOU_HAVE_TO_AT_LEAST_SELECT_A_SECTION' ).'" );
							return false;
						}
						else {
							return true;
						}
					}
					spApply.addEvent( "click", function() {
						if( SPValidate() ) {
							spApplyFn();
						}
					} );
					spSave.addEvent( "click", function() {
						if( SPValidate() ) {
							spSaveFn();
						}
					} );
					$( "spsection" ).addEvent( "change", function( event ) {
						sid = $( "spsection" ).options[ $( "spsection" ).selectedIndex ].value;
						$( "sid" ).value = sid;
						semaphor = 0;
					} );
					if( $( "sp_category" ) != null ) {
						$( "sp_category" ).addEvent( "click", function( ev ) {
							if( semaphor == 1 ) {
								return false;
							}
							semaphor = 1;
							new Event( ev ).stop();
							if( $( "sid" ).value == 0 ) {
								alert( "' . $cemsg . '" );
								semaphor = 0;
								return false;
							}
							else {
								url = "' . $ccurl . '&sid=" + $( "sid" ).value
								try {
									SqueezeBox.open( $( "sp_category" ), { handler: "iframe", size: { x: 700, y: 500 }, url: url });
								}
								catch( x ) {
									SqueezeBox.fromElement( $( "sp_category" ), { url: url, handler: "iframe", size: { x: 700, y: 500 } } );
								}
							}
						} );
					}
				} );
				function SP_close()
				{
					$( "sbox-btn-close" ).fireEvent( "click" );
					semaphor = 0;
				}
		' );
		$head->send();
		parent::__construct();
		$loaded = true;
	}
	public function fetchTooltip( $label, $description, &$node, $control_name, $name )
	{
		if( $label == 'cid' ) {
			$opt = JRequest::getVar( 'url', array() );
			if( isset( $opt[ 'task' ] ) ) {
				return null;
			}
			$label = JText::_( 'SOBI_SELECT_CATEGORY' );
		}
		return parent::fetchTooltip( $label, $node->attributes( 'msg' ), $node, $control_name, $name );
	}

	private function getCat( $name )
	{
		$opt = JRequest::getVar( 'url', array() );
		if( isset( $opt[ 'task' ] ) ) {
			return null;
		}
		return SPHtml_Input::button( 'sp_category', Sobi::Txt( 'SELECT_CATEGORY' ), array( 'id'=>'sp_category', 'class' => 'inputbox', 'style' => 'border: 1px solid silver;' ) );
	}

	public function fetchElement( $name )
	{
		static $sid = 0;
		$selected = 0;
		$db =& SPFactory::db();
		if( !( $sid ) ) {
			$cid = JRequest::getVar( 'cid', JRequest::getVar( 'id', array() ) );
			$sid = 0;
			if( count( $cid ) && is_numeric( $cid[ 0 ] ) ) {
				$model =& JModel::getInstance( 'MenusModelItem' );
				$table =& $model->getItem();
				if( strstr( $table->get( 'link' ), 'sid' ) ) {
					$sid = explode( 'sid=', $table->get( 'link' ) );
					$sid = preg_replace( '/[^0-9_]/i', '', $sid[ 1 ] );
				}
				$section = & SPFactory::object( $sid );
				if ( $section->oType == 'section' ) {
					$selected = $section->id;
				}
				else {
					$path = array();
					$id = $sid;
					while ( $id > 0 ) {
						try {
							$db->select( 'pid', 'spdb_relations', array( 'id' => $id ) );
							$id = $db->loadResult();
							if( $id ) {
								$path[] = ( int ) $id;
							}
						}
						catch ( SPException $x ) {}
					}
					$path = array_reverse( $path );
					$selected = $path[ 0 ];
				}
			}
		}
		if( $name == 'sid'  ) {
			$params = array( 'id' => 'sid', 'size' => 5, 'class' => 'text_area', 'style' => 'text-align: center;', 'readonly' => 'readonly' );
			return SPHtml_Input::text( 'urlparams[sid]', $sid, $params );
		}
		if( $name == 'cid' ) {
			return $this->getCat( $name );
		}
		$sections = array();
		$sout = array();
		try {
			$db->select( '*', 'spdb_object', array( 'oType' => 'section' ), 'id' );
			$sections = $db->loadObjectList();
		}
		catch ( SPException $x ) {
			trigger_error( 'sobipro|admin_panel|cannot_get_section_list|500|'.$x->getMessage(), SPC::WARNING );
		}
		if( count( $sections ) ) {
			SPLoader::loadClass( 'models.datamodel' );
			SPLoader::loadClass( 'models.dbobject' );
			SPLoader::loadModel( 'section' );
			$sout[] = Sobi::Txt( 'SELECT_SECTION' );
			foreach ( $sections as $section ) {
				if( Sobi::Can( 'section', 'access', 'valid', $section->id ) ) {
					$s = new SPSection();
					$s->extend( $section );
					$sout[ $s->get( 'id' ) ] = $s->get( 'name' );
				}
			}
		}
		$params = array( 'id' => 'spsection', 'class' => 'text_area required' );
		$field = SPHtml_Input::select( 'sid', $sout, $selected, false, $params );
		return "<div style=\"margin-top: 2px;\">{$field}</div>";
	}
}
?>