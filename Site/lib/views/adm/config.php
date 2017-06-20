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

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:41:10 PM
 */
class SPConfigAdmView extends SPAdmView implements SPView
{
	/**
	 * @var bool
	 */
	protected $_fout = true;
	/**
	 * @var SPConfigAdmCtrl
	 */
	private $_ctrl = true;

	public function setCtrl( &$ctrl )
	{
		$this->_ctrl =& $ctrl;
	}

	/**
	 * @param string $title
	 * @return string|void
	 */
	public function setTitle( $title )
	{
		$title = Sobi::Txt( $title, [ 'section' => $this->get( 'section.name' ) ] );
		Sobi::Trigger( 'setTitle', $this->name(), [ &$title ] );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title' );
	}

	/**
	 * Enter description here...
	 *
	 */
// not longer used
//	protected function fields()
//	{
//		SPLoader::loadClass( 'html.tabs' );
//		SPFactory::header()->addCssFile( 'tabs', true );
//		$t = new SPHtml_Tabs( true, null );
//		$tabs = $this->get( 'fields' );
//		if ( count( $tabs ) ) {
//			$t->startPane( 'fields_' . $this->get( 'task' ) );
//			foreach ( $tabs as $tab => $keys ) {
//				$t->startTab( Sobi::Txt( $tab ), str_replace( ' ', '_', $tab ) );
//				echo '<table  class="admintable" style="width: 100%;">';
//				$c = 0;
//				foreach ( $keys as $key => $params ) {
//					$class = $c % 2;
//					$c++;
//					$params = explode( '|', $params );
//					$p = [];
//					/* at first we need the label */
//					$label = Sobi::Txt( array_shift( $params ) );
//					$label2 = null;
//					if ( strstr( $label, ':' ) ) {
//						$label = explode( ':', $label );
//						$label2 = $label[ 1 ];
//						$label = $label[ 0 ];
//					}
//					/* get the field type */
//					$p[ 0 ] = array_shift( $params );
//
//					if ( preg_match( '/^section.*/', $key ) ) {
//						/* put the field name */
//						$p[ 1 ] = $key;
//						/* get the current value */
//						$p[ 2 ] = $this->get( $key );
//					}
//					elseif ( !( strstr( $key, 'spacer' ) ) ) {
//						/* put the field name */
//						$p[ 1 ] = 'spcfg_' . $key;
//						/* get the current value */
//						$p[ 2 ] = Sobi::Cfg( $key, '' );
//					}
//					if ( ( strstr( $key, 'spacer' ) ) ) {
//						if ( $key == 'spacer_pby' ) {
//							$this->pby();
//						}
//						else {
//							echo "<tr class=\"row{$class}\">";
//							echo '<th colspan="2" class="spConfigTableHeader">';
//							$this->txt( $label );
//							echo '</th>';
//							echo '</tr>';
//						}
//					}
//					else {
//						if ( strstr( $key, '_array' ) && count( $p[ 2 ] ) && $p[ 2 ] ) {
//							$p[ 2 ] = implode( ',', $p[ 2 ] );
//						}
//						/* and all other parameters */
//						if ( count( $params ) ) {
//							foreach ( $params as $param ) {
//								$p[ ] = $param;
//							}
//						}
//						echo "<tr class=\"row{$class}\">";
//						echo '<td class="key" style="min-width:200px;">';
//						$this->txt( $label );
//						echo '</td>';
//						echo '<td>';
//						$this->parseField( $p );
//						if ( $label2 ) {
//							$this->txt( $label2 );
//						}
//						echo '</td>';
//						echo '</tr>';
//					}
//				}
//				echo '</table>';
//				$t->endTab();
//			}
//			$t->endPane();
//		}
//	}

// not longer used
//	private function parseField( $params )
//	{
//		if ( strstr( $params[ 0 ], 'function:' ) ) {
//			$params[ 0 ] = str_replace( 'function:', null, $params[ 0 ] );
//			switch ( $params[ 0 ] ) {
//				case 'name_fields_list':
//					$params = $this->namesFields( $params );
//					break;
//				case 'entries_ordering':
//					$params = $this->namesFields( $params, true );
//					break;
//				case 'templates_list':
//					$params = $this->templatesList( $params, true );
//					break;
//				case 'alpha_field_list':
//					$params = $this->alphaFieldList( $params );
//					break;
//				case 'alpha_fields_list':
//					$params = $this->alphaFieldList( $params, true );
//					break;
//			}
//		}
//		else {
//			if ( $params[ 0 ] == 'select' ) {
//				$selected = $params[ 2 ];
//				$params[ 2 ] = $params[ 3 ];
//				$params[ 3 ] = $selected;
//			}
//		}
//		call_user_func_array( [ $this, 'field' ], $params );
//	}

// not longer used
//	public function alphaFieldList( $params = null, $add = false )
//	{
//		$fields = $this->_ctrl->getNameFields( true, Sobi::Cfg( 'alphamenu.field_types' ) );
//		if ( count( $fields ) ) {
//			foreach ( $fields as $fid => $field ) {
//				$fData[ $fid ] = $field->get( 'name' );
//			}
//		}
//		if ( $add ) {
//			$selected = Sobi::Cfg( 'alphamenu.extra_fields_array' );
//			$p = [ 'select', $params[ 1 ], $fData, $selected, $add, $params[ 3 ] ];
//		}
//		else {
//			$selected = Sobi::Cfg( 'alphamenu.primary_field', SPFactory::config()->nameField()->get( 'id' ) );
//			$p = [ 'select', $params[ 1 ], $fData, $selected, $add, $params[ 3 ] ];
//		}
//		return $p;
//	}

// not longer used
//	private function pby()
//	{
//		echo "<tr>";
//		echo '<td colspan="2"><div style="margin-left: 150px;">';
//		echo Sobi::Txt( 'GB.CFG.PBY_EXPL', [ 'image' => Sobi::Cfg( 'media_folder_live' ) . '/donate.png' ] );
//		echo '</div></td>';
//		echo '</tr>';
//	}

	public function templatesList( $params = null )
	{
		$cms = SPFactory::CmsHelper()->templatesPath();
		$dir = new SPDirectoryIterator( SPLoader::dirPath( 'usr.templates' ) );
		$templates = [];
		foreach ( $dir as $file ) {
			if ( $file->isDir() ) {
				if ( $file->isDot() || in_array( $file->getFilename(), [ 'common', 'front' ] ) ) {
					continue;
				}
				if ( SPFs::exists( $file->getPathname() . DS . 'template.xml' ) && ( $tname = $this->templateName( $file->getPathname() . DS . 'template.xml' ) ) ) {
					$templates[ $file->getFilename() ] = $tname;
				}
				else {
					$templates[ $file->getFilename() ] = $file->getFilename();
				}
			}
		}
		if ( is_array( $cms ) && isset( $cms[ 'name' ] ) && isset( $cms[ 'data' ] ) && is_array( $cms[ 'data' ] ) && count( $cms[ 'data' ] ) ) {
			$templates[ $cms[ 'name' ] ] = [];
			foreach ( $cms[ 'data' ] as $name => $path ) {
				$templates[ $cms[ 'name' ] ][ $name ] = [];
				$dir = new SPDirectoryIterator( $path );
				foreach ( $dir as $file ) {
					if ( $file->isDot() ) {
						continue;
					}
					$fpath = 'cms:' . str_replace( SOBI_ROOT . DS, null, $file->getPathname() );
					$fpath = str_replace( DS, '.', $fpath );
					if ( SPFs::exists( $file->getPathname() . DS . 'template.xml' ) && ( $tname = $this->templateName( $file->getPathname() . DS . 'template.xml' ) ) ) {
						$templates[ $cms[ 'name' ] ][ $name ][ $fpath ] = $tname;
					}
					else {
						$templates[ $cms[ 'name' ] ][ $name ][ $fpath ] = $file->getFilename();
					}
				}
			}
		}
		if ( $params ) {
			$p = [ 'select', 'spcfg_' . $params[ 1 ], $templates, Sobi::Cfg( 'section.template', SPC::DEFAULT_TEMPLATE ), false, $params[ 3 ] ];
		}
		else {
			$p = $templates;
		}
		return $p;
	}

	private function templateName( $file )
	{
		$def = new DOMDocument();
		$def->load( $file );
		$xdef = new DOMXPath( $def );
		$name = $xdef->query( '/template/name' )->item( 0 )->nodeValue;
		return strlen( $name ) ? $name : false;
	}

	public function namesFields( $params = null, $ordering = false )
	{
		$fields = $this->_ctrl->getNameFields( $ordering );
		$fData = [ 0 => Sobi::Txt( 'SEC.CFG.ENTRY_TITLE_FIELD_SELECT' ) ];
		if ( count( $fields ) ) {
			foreach ( $fields as $fid => $field ) {
				if ( $ordering ) {
					try {
						$fData = $field->setCustomOrdering( $fData );
					} catch ( SPException $x ) {
						$fData[ $field->get( 'nid' ) . '.asc' ] = '\'' . $field->get( 'name' ) . '\' ' . Sobi::Txt( 'EMN.ORDER_BY_FIELD_ASC' );
						$fData[ $field->get( 'nid' ) . '.desc' ] = '\'' . $field->get( 'name' ) . '\' ' . Sobi::Txt( 'EMN.ORDER_BY_FIELD_DESC' );
					}
				}
				else {
					$fData[ $fid ] = $field->get( 'name' );
				}
			}
		}
		if ( $ordering ) {
			unset( $fData[ 0 ] );
			$fData = [
					'position.asc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_POSITION_ASCENDING' ),
					'position.desc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_POSITION_DESCENDING' ),
					'counter.asc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_POPULARITY_ASCENDING' ),
					'counter.desc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_POPULARITY_DESCENDING' ),
					'createdTime.asc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_CREATION_DATE_ASC' ),
					'createdTime.desc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_CREATION_DATE_DESC' ),
					'updatedTime.asc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_UPDATE_DATE_ASC' ),
					'updatedTime.desc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_UPDATE_DATE_DESC' ),
					'validUntil.asc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_EXPIRATION_DATE_ASC' ),
					'validUntil.desc' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_EXPIRATION_DATE_DESC' ),
					'RAND()' => Sobi::Txt( 'SECN.CFG.ENTRY_ORDER_BY_RANDOM' ),
					Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_FIELDS' ) => $fData
			];
		}
		if ( $params ) {
			$p = [ 'select', $params[ 1 ], $fData, $params[ 2 ], false ];
			if ( isset( $params[ 3 ] ) ) {
				$p[ ] = $params[ 3 ];
			}
			if ( isset( $params[ 4 ] ) ) {
				$p[ ] = $params[ 4 ];
			}
			if ( isset( $params[ 5 ] ) ) {
				$p[ ] = $params[ 5 ];
			}
			return $p;
		}
		else {
			return $fData;
		}
	}

	/**
	 *
	 * @param mixed $attr
	 * @param int $index
	 * @return mixed
	 */
	public function & get( $attr, $index = -1 )
	{
		$config = SPFactory::config();
		if ( !( $config->key( $attr, false ) ) ) {
			return parent::get( $attr, $index );
		}
		else {
			$value = $config->key( $attr );
			// WHY?! For gods' sake - write comments to your code you fraking idiot!!!!
			// Tue, Jun 4, 2013 15:21:19 : got that - we have some arrays that have to be displayed as a string while editing config
			// see also bug #894
//			if ( is_array( $attr ) ) {
//				$attr = implode( ',', $attr );
//			}
			// ...  let's fix it ;)
			if ( is_array( $value ) && strstr( $attr, '_array' ) ) {
				$value = implode( ',', $value );
			}
			return $value;
		}
	}
}
