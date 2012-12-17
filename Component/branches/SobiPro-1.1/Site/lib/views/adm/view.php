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
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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
 * @version 1.1
 * @created Thu, Aug 9, 2012 23:24:38
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
	 * @var array
	 */
	protected $_output = array();
	/**
	 * @var bool
	 */
	protected $_native = false;
	/**
	 * @var DOMDocument
	 */
	protected $_xml = false;
	/**
	 * @var bool
	 */
	protected $_compatibility = false;

	/**
	 */
	public function __construct()
	{
		SPLoader::loadClass( 'helpers.adm.lists' );
		SPLoader::loadClass( 'mlo.input' );
		SPFactory::header()->addJsFile( 'adm.interface' );
		Sobi::Trigger( 'Create', $this->name(), array( &$this ) );
	}

	/**
	 *
	 * @param var
	 * @param label
	 * @return SPAdmView
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
	 * @return SPAdmView
	 */
	public function & addHidden( $var, $label )
	{
		$this->_hidden[ $label ] = $var;
		$this->_attr[ 'request' ][ $label ] = $var;
		return $this;
	}

	/**
	 * @param string $path
	 */
	public function loadDefinition( $path )
	{
		$path = SPLoader::translatePath( $path, 'adm', true, 'xml', false );
		$this->_xml = DOMDocument::load( $path );
		$this->parseDefinition( $this->_xml->getElementsByTagName( 'definition' ) );
	}

	public function & determineTemplate( $type, $template )
	{
		if ( SPLoader::translatePath( $type . '.definitions.' . $template, 'adm', true, 'xml' ) ) {
			$nid = '-' . Sobi::Section( 'nid' );
			/** Case we have also override  */
			if ( SPLoader::translatePath( $type . '.definitions.' . $template . $nid, 'adm', true, 'xml' ) ) {
				$this->loadDefinition( $type . '.definitions.' . $template . $nid );
			}
			elseif ( SPLoader::translatePath( $type . '.definitions.' . $template . '_override', 'adm', true, 'xml' ) ) {
				$this->loadDefinition( $type . '.definitions.' . $template . '_override' );
			}
			else {
				$this->loadDefinition( $type . '.definitions.' . $template );
			}
			if ( SPLoader::translatePath( $type . '.templates.' . $template . $nid, 'adm' ) ) {
				$this->setTemplate( $type . '.templates.' . $template . $nid );
			}
			elseif ( SPLoader::translatePath( $type . '.templates.' . $template . '_override', 'adm' ) ) {
				$this->setTemplate( $type . '.templates.' . $template . '_override' );
			}
			elseif ( SPLoader::translatePath( $type . '.templates.' . $template, 'adm' ) ) {
				$this->setTemplate( $type . '.templates.' . $template );
			}
			else {
				$this->setTemplate( 'default' );
			}
		}
		else {
			$this->loadConfig( "{$type}.{$template}" );
			$this->setTemplate( "{$type}.{$template}" );
		}
		return $this;
	}


	/**
	 * @param DOMNodeList $xml
	 * @return void
	 */
	private function parseDefinition( DOMNodeList $xml )
	{
		/** @var DOMNode $node */
		foreach ( $xml as $node ) {
			if ( strstr( $node->nodeName, '#' ) ) {
				continue;
			}
			if ( !( $this->xmlCondition( $node ) ) ) {
				continue;
			}
			switch ( $node->nodeName ) {
				case 'header':
					$this->xmlHeader( $node->childNodes );
					break;
				case 'config':
					$this->xmlConfig( $node->childNodes );
					break;
				case 'toolbar':
					$this->xmlToolbar( $node );
					break;
				case 'body':
					$this->xmlBody( $node->childNodes, $this->_output[ 'data' ] );
					break;
				case 'definition':
					$this->parseDefinition( $node->childNodes );
					break;
			}
		}
	}

	public function getData()
	{
		return $this->_output;
	}

	public function toolbar()
	{
		return SPFactory::AdmToolbar()->render();
	}

	public function & getParser()
	{
		static $parser = null;
		if ( !( $parser ) ) {
			$parser = SPFactory::Instance( 'views.adm.parser' );
		}
		return $parser;
	}

	/**
	 * @param DOMNode $xml
	 * @return void
	 */
	private function xmlToolbar( $xml )
	{
		$title = $xml
				->attributes
				->getNamedItem( 'title' )
				->nodeValue;
		$icon = $xml
				->attributes
				->getNamedItem( 'icon' )
				->nodeValue;
		SPFactory::AdmToolbar()->setTitle( array( 'title' => $this->parseValue( $title ), 'icon' => $icon ) );
		$buttons = array();
		foreach ( $xml->childNodes as $node ) {
			if ( strstr( $node->nodeName, '#' ) ) {
				continue;
			}
			/** @var DOMNode $node */
			switch ( $node->nodeName ) {
				case 'button':
					$buttons[ ] = $this->xmlButton( $node );
					break;
				case 'divider':
					$buttons[ ] = array( 'element' => 'divider' );
					break;
				case 'group':
					$group = array( 'element' => 'group', 'buttons' => array() );
					foreach ( $node->attributes as $attr ) {
						if ( $attr->nodeName == 'label' ) {
							$group[ $attr->nodeName ] = Sobi::Txt( $attr->nodeValue );
						}
						else {
							$group[ $attr->nodeName ] = $attr->nodeValue;
						}
					}
					foreach ( $node->childNodes as $bt ) {
						if ( strstr( $bt->nodeName, '#' ) ) {
							continue;
						}
						$group[ 'buttons' ][ ] = $this->xmlButton( $bt );
					}
					$buttons[ ] = $group;
					break;
				case 'buttons':
					$group = array( 'element' => 'buttons', 'buttons' => array(), 'label' => $node->attributes->getNamedItem( 'label' ) ? Sobi::Txt( $node->attributes->getNamedItem( 'label' )->nodeValue ) : '' );
					foreach ( $node->attributes as $attr ) {
						if ( $attr->nodeName == 'label' ) {
							continue;
						}
						$group[ $attr->nodeName ] = $attr->nodeValue;
					}
					/** it has to have child nodes or these childs are defined in value  */
					if ( $node->hasChildNodes() ) {
						foreach ( $node->childNodes as $bt ) {
							if ( strstr( $bt->nodeName, '#' ) ) {
								continue;
							}
							if ( $bt->nodeName == 'nav-header' ) {
								$group[ 'buttons' ][ ] = array( 'element' => 'nav-header', 'label' => Sobi::Txt( $bt->attributes->getNamedItem( 'label' )->nodeValue ) );
							}
							else {
								$group[ 'buttons' ][ ] = $this->xmlButton( $bt );
							}
						}
					}
					else {
						$group[ 'buttons' ] = $this->get( $node->attributes->getNamedItem( 'buttons' )->nodeValue );
					}
					$buttons[ ] = $group;
					break;
			}
		}
		SPFactory::AdmToolbar()->addButtons( $buttons );
	}

	/**
	 * @param DOMNode $xml
	 * @return bool
	 */
	private function xmlCondition( $xml )
	{
		if ( $xml->hasAttributes() && $xml->attributes->getNamedItem( 'condition' ) && $xml->attributes->getNamedItem( 'condition' )->nodeValue ) {
			return $this->get( $xml->attributes->getNamedItem( 'condition' )->nodeValue );
		}
		elseif ( $xml->hasAttributes() && $xml->attributes->getNamedItem( 'invert-condition' ) && $xml->attributes->getNamedItem( 'invert-condition' )->nodeValue ) {
			return !( $this->get( $xml->attributes->getNamedItem( 'invert-condition' )->nodeValue ) );
		}
		else {
			return true;
		}
	}

	/**
	 * @param DOMNode $xml
	 * @param $attributes
	 * @return void
	 */
	private function xmlButton( $xml, $attributes = array() )
	{
		$button = array(
			'type' => null,
			'task' => null,
			'label' => null,
			'icon' => null,
			'target' => null,
			'buttons' => null,
			'element' => 'button'
		);
		if ( $xml->attributes->length ) {
			/** @var DOMElement $attr */
			foreach ( $xml->attributes as $attr ) {
				if ( $attr->nodeName == 'label' ) {
					$button[ $attr->nodeName ] = Sobi::Txt( $attr->nodeValue );
				}
				else {
					$button[ $attr->nodeName ] = $attr->nodeValue;
				}
			}
			if ( $xml->hasChildNodes() ) {
				foreach ( $xml->childNodes as $node ) {
					if ( strstr( $node->nodeName, '#' ) ) {
						continue;
					}
					$button[ 'buttons' ][ ] = $this->xmlButton( $node, $attributes );
				}
			}
		}
		if ( count( $attributes ) ) {
			$button = array_merge( $button, $attributes );
		}
		return $button;
	}

	protected function parseValue( $key, $i = -1 )
	{
		if ( strstr( $key, 'var:[' ) ) {
			preg_match( '/var\:\[([a-zA-Z0-9\.\_\-]*)\]/', $key, $matches );
			$key = str_replace( $matches[ 0 ], $this->get( $matches[ 1 ], $i ), $key );
		}
		else {
			$key = Sobi::Txt( $key );
		}
//		if ( strstr( $key, 'var:[' ) ) {
//			preg_match( '/var\:\[([a-zA-Z0-9\.\_\-]*)\]/', $key, $matches );
//			$key = str_replace( $matches[ 0 ], $this->get( $matches[ 1 ], $i ), $key );
//		}
		return $key;
	}

	/**
	 * @param DOMNodeList $xml
	 * @return void
	 */
	private function xmlBody( $xml, &$output )
	{
		foreach ( $xml as $node ) {
			if ( strstr( $node->nodeName, '#' ) ) {
				continue;
			}
			if ( !( $this->xmlCondition( $node ) ) ) {
				continue;
			}
			$element = array(
				'label' => null,
				'type' => $node->nodeName,
				'content' => null,
				'attributes' => null
			);
			$attributes = $node->attributes;
			if ( $attributes->length ) {
				/** @var DOMElement $attribute */
				foreach ( $attributes as $attribute ) {
					if ( $attribute->nodeName == 'label' ) {
						$element[ 'attributes' ][ $attribute->nodeName ] = Sobi::Txt( $attribute->nodeValue );
						$element[ 'attributes' ][ $attribute->nodeName ] = $this->parseValue( $element[ 'attributes' ][ $attribute->nodeName ] );
					}
					else {
						$element[ 'attributes' ][ $attribute->nodeName ] = $this->parseValue( $attribute->nodeValue );
					}
				}
			}

			/** @var DOMNode $node */
			switch ( $node->nodeName ) {
				case 'tab':
				case 'fieldset':
					$element[ 'label' ] = $node->attributes->getNamedItem( 'label' ) ? Sobi::Txt( $node->attributes->getNamedItem( 'label' )->nodeValue ) : null;
					$element[ 'id' ] = $node->attributes->getNamedItem( 'label' ) ? SPLang::nid( $node->attributes->getNamedItem( 'label' )->nodeValue ) : null;
					if ( $node->hasChildNodes() ) {
						$this->xmlBody( $node->childNodes, $element[ 'content' ] );
					}
					else {
						$element[ 'content' ] = $node->nodeValue;
					}
					break;
				case 'url':
					$element[ 'label' ] = $node->attributes->getNamedItem( 'label' ) ? Sobi::Txt( $node->attributes->getNamedItem( 'label' )->nodeValue ) : null;
					$element[ 'link' ] = $this->xmlUrl( $node );
					$element[ 'attributes' ][ 'href' ] = $element[ 'link' ];
					if ( $node->attributes->getNamedItem( 'value' ) ) {
						$content = $this->get( $node->attributes->getNamedItem( 'value' )->nodeValue );
						if ( !( $content ) ) {
							$content = $node->attributes->getNamedItem( 'value' )->nodeValue;
						}
						$element[ 'content' ] = $content;
					}
					if ( !( $element[ 'content' ] ) ) {
						$element[ 'content' ] = $element[ 'label' ];
					}
					break;
				case 'text':
					$element[ 'content' ] = $this->xmlText( $node );
					break;
				case 'field':
					$this->xmlField( $node, $element );
					break;
				case 'loop':
					$this->xmlLoop( $node, $element );
					break;
				case 'tooltip':
					$this->xmlToolTip( $node, $element );
					break;
				case 'pagination':
					$this->xmlPagination( $node, $element );
					break;
				case 'message':
					if ( $node->attributes->getNamedItem( 'parse' ) && $node->attributes->getNamedItem( 'parse' )->nodeValue ) {
						$element[ 'attributes' ][ 'label' ] = $this->get( $node->attributes->getNamedItem( 'parse' )->nodeValue . '.label' );
						$element[ 'attributes' ][ 'type' ] = $this->get( $node->attributes->getNamedItem( 'parse' )->nodeValue . '.type' );
					}
					break;
				case 'file':
					$this->xmlFile( $node, $element );
					break;
				case 'menu':
					$element[ 'content' ] = $this->menu( true );
					break;
				default:
					if ( $node->hasChildNodes() ) {
						$this->xmlBody( $node->childNodes, $element[ 'content' ] );
					}
					elseif ( !( strstr( $node->nodeName, '#' ) ) ) {
						$element[ 'content' ] = $node->nodeValue;
					}
				/** No break here */
				case 'cells':
					if ( $node->attributes->getNamedItem( 'value' ) ) {
						$customCells = $this->get( $node->attributes->getNamedItem( 'value' )->nodeValue );
						if ( count( $customCells ) ) {
							foreach ( $customCells as $cell ) {
								$element[ 'content' ][ ] = array(
									'label' => isset( $cell[ 'label' ] ) ? $cell[ 'label' ] : null,
									'type' => 'cell',
									'content' => $cell[ 'content' ],
									'attributes' => $element[ 'attributes' ]
								);
							}
						}
					}
					break;

			}
			$output[ ] = $element;
		}
	}

	private function xmlFile( $node, &$element )
	{
		$type = $node->attributes->getNamedItem( 'type' )->nodeValue;
		$translatable = $node->attributes->getNamedItem( 'translatable' ) ? $node->attributes->getNamedItem( 'translatable' )->nodeValue : false;
		$admin = $node->attributes->getNamedItem( 'start-path' ) ? $node->attributes->getNamedItem( 'start-path' )->nodeValue : 'front';
		$filename = $node->attributes->getNamedItem( 'filename' )->nodeValue;
		$path = explode( '.', $filename );
		$filename = array_pop( $path );
		$dirPath = implode( '.', $path );
		$element[ 'type' ] = 'text';
		if ( $translatable ) {
			$file = SPLoader::path( $dirPath . '.' . Sobi::Lang() . '.' . $filename, $admin, true, $type );
			if ( !( $file ) ) {
				$file = SPLoader::path( $dirPath . '.en-GB.' . $filename, $admin, true, $type );
			}
			if ( $file ) {
				$element[ 'content' ] = SPFs::read( $file );
			}
			else {
				$element[ 'content' ] = SPLoader::path( $dirPath . '.' . Sobi::Lang() . '.' . $filename, $admin, false, $type );
			}
		}
	}


	private function xmlToolTip( $node, &$element, $subject = null, $index = -1 )
	{
		foreach ( $node->attributes as $attribute ) {
			$element[ $attribute->nodeName ] = Sobi::Txt( $attribute->nodeValue );
		}
		foreach ( $node->childNodes as $param ) {
			if ( strstr( $param->nodeName, '#' ) ) {
				continue;
			}
			$element[ $param->attributes->getNamedItem( 'name' )->nodeValue ] = $this->xmlParams( $param, $subject, $index );
		}
		$unsets = array( 'type', 'title', 'content' );
		foreach ( $unsets as $unset ) {
			if ( isset( $element[ 'attributes' ][ $unset ] ) ) {
				unset( $element[ 'attributes' ][ $unset ] );
			}
		}
	}

	private function xmlPagination( $node, &$element )
	{
		$args = array();
		/** @var DOMElement $attribute */
		foreach ( $node->attributes as $attribute ) {
			$args[ $attribute->nodeName ] = $attribute->nodeValue;
		}
		foreach ( $node->childNodes as $param ) {
			if ( strstr( $param->nodeName, '#' ) ) {
				continue;
			}
			$args[ $param->attributes->getNamedItem( 'name' )->nodeValue ] = $this->xmlParams( $param );
		}
		/** @var $pagination SPPagination */
		$pagination = SPFactory::Instance( 'views.adm.pagination' );
		foreach ( $args as $var => $val ) {
			$pagination->set( $var, $val );
		}
		$element[ 'content' ] = $pagination->display( true );
	}


	/**
	 * @param DOMNode $node
	 * @return string
	 */
	private function xmlText( $node )
	{
		$value = null;
		if ( $node->attributes->getNamedItem( 'value' ) ) {
			if ( $node->attributes->getNamedItem( 'parse' ) && $node->attributes->getNamedItem( 'parse' )->nodeValue == 'true' ) {
				$value = $this->get( $node->attributes->getNamedItem( 'value' )->nodeValue );
			}
			else {
				$args = array( $node->attributes->getNamedItem( 'value' )->nodeValue );
				if ( $node->hasChildNodes() ) {
					foreach ( $node->childNodes as $param ) {
						if ( strstr( $param->nodeName, '#' ) ) {
							continue;
						}
						if ( $param->attributes->getNamedItem( 'value' ) ) {
							if ( $param->attributes->getNamedItem( 'parse' ) && $param->attributes->getNamedItem( 'parse' )->nodeValue == 'true' ) {
								$args[ ] = $this->get( $param->attributes->getNamedItem( 'value' )->nodeValue );
							}
							else {
								$args[ ] = $param->attributes->getNamedItem( 'value' )->nodeValue;
							}
						}
						else {
							$args[ ] = $param->nodeValue;
						}
					}
				}
				$value = call_user_func_array( array( 'SPLang', '_' ), $args );
			}
		}
		return $value;
	}

	/**
	 * @param DOMNode $node
	 * @param array $element
	 * @return void
	 */
	private function xmlLoop( $node, &$element )
	{
		$subject = $node->attributes->getNamedItem( 'subject' )->nodeValue;
		static $count = 0;
		if ( $subject == 'entry.fields' ) {
			return $this->xmlFields( $element );
		}
		elseif ( strstr( $subject, '.' ) ) {
			$tempSubject = $this->get( $subject );
			$this->assign( $tempSubject, 'temporary' . ++$count );
			$subject = 'temporary' . $count;
		}
		$objectsCount = $this->count( $subject );
		$objects = array();
		for ( $i = 0; $i < $objectsCount; $i++ ) {
			$row = array();
			/** @var DOMNode $cell */
			foreach ( $node->childNodes as $cell ) {
				if ( strstr( $cell->nodeName, '#' ) ) {
					continue;
				}
				$this->xmlCell( $cell, $subject, $i, $row );
			}
			$a = array();
			if ( $node->hasAttributes() ) {
				/** @var DOMElement $attribute */
				foreach ( $node->attributes as $attribute ) {
					$a[ $attribute->nodeName ] = $attribute->nodeValue;
				}
			}
			$objects[ ] = array(
				'label' => null,
				'type' => 'loop-row',
				'content' => $row,
				'attributes' => $a
			);
		}
		$element[ 'content' ] = $objects;
	}

	private function xmlFields( &$element )
	{
		$fields = $this->get( 'fields' );
		$objects = array();
		foreach ( $fields as $i => $field ) {
			$output = $field->field( true );
			if ( !( $output ) ) {
				continue;
			}
			$adds = null;
			$suffix = $field->get( 'suffix' );
			if ( $suffix ) {
				$adds = array( $suffix );
			}
			$objects[ $i ] = array(
				'label' => $field->get( 'name' ),
				'type' => 'field',
				'content' => $output,
				'args' => array( 'type' => $field->get( 'type' ) ),
				'adds' => array( 'before' => null, 'after' => $adds ),
				'help-text' => $field->get( 'description' )
			);
			if ( !( $field->get( 'showLabel' ) ) ) {
				$objects[ $i ][ 'label' ] = null;
			}
		}
		$element[ 'content' ] = $objects;
	}

	/**
	 * @param DOMNode $cell
	 * @param string $subject
	 * @param integer $i
	 * @param array $objects
	 * @return void
	 */
	private function xmlCell( $cell, $subject, $i, &$objects )
	{
		$element = array(
			'label' => null,
			'type' => $cell->nodeName,
			'content' => null,
			'attributes' => null,
		);

		/** @var DOMElement $attribute */
		foreach ( $cell->attributes as $attribute ) {
			switch ( $attribute->nodeName ) {
				case 'label':
					$element[ 'label' ] = Sobi::Txt( $attribute->nodeValue );
					break;
				case 'value':
					$element[ 'content' ] = $this->get( $subject . '.' . $attribute->nodeValue, $i );
				case 'checked-out-by':
				case 'checked-out-time':
				case 'valid-since':
				case 'valid-until':
					$element[ 'attributes' ][ $attribute->nodeName ] = $this->get( $subject . '.' . $attribute->nodeValue, $i );
					break;
				default:
					$element[ 'attributes' ][ $attribute->nodeName ] = $this->parseValue( str_replace( 'var:[', 'var:[' . $subject . '.', $attribute->nodeValue ), $i );
					break;
			}
		}
		if ( $cell->nodeName == 'cells' ) {
			$customCells = $this->get( $subject . '.' . $cell->attributes->getNamedItem( 'value' )->nodeValue, $i );
			if ( count( $customCells ) ) {
				$a = $element[ 'attributes' ];
				$a[ 'type' ] = 'text';
				foreach ( $customCells as $customCell ) {
					$objects[ ] = array(
						'label' => null,
						'type' => 'cell',
						'content' => $customCell,
						'attributes' => $a,
					);
				}
			}
		}
		elseif ( $cell->nodeName == 'text' ) {
			$element[ 'content' ] = $this->xmlText( $cell );
		}
		elseif ( $cell->nodeName == 'field' ) {
			$this->xmlField( $cell, $element, $element[ 'content' ] );
		}
		if ( $cell->hasChildNodes() ) {
			/** @var DOMNode $child */
			foreach ( $cell->childNodes as $child ) {
				if ( strstr( $child->nodeName, '#' ) ) {
					continue;
				}
				/** @var DOMNode $param */
				switch ( $child->nodeName ) {
					case 'url':
						$element[ 'link' ] = $this->xmlUrl( $child, $subject, $i );
						break;
					case 'tooltip':
						$this->xmlToolTip( $child, $element, $subject, $i );
						break;
					case 'button':
						$attributes = array();
						foreach ( $child->attributes as $attribute ) {
							$attributes[ $attribute->nodeName ] = $this->parseValue( str_replace( 'var:[', 'var:[' . $subject . '.', $attribute->nodeValue ), $i );
						}
						$element[ 'content' ] = $this->xmlButton( $child, $attributes );
						break;
					default:
						$this->xmlCell( $child, $subject, $i, $element[ 'childs' ] );
						break;
				}
			}
		}
		$objects[ ] = $element;
	}

	private function xmlUrl( $node, $subject = null, $index = -1 )
	{
		$url = array();
		$link = null;
		foreach ( $node->childNodes as $param ) {
			if ( strstr( $param->nodeName, '#' ) ) {
				continue;
			}
			$url[ $param->attributes->getNamedItem( 'name' )->nodeValue ] = $this->xmlParams( $param, $subject, $index );
		}
		if ( $node->attributes->getNamedItem( 'type' ) && $node->attributes->getNamedItem( 'type' )->nodeValue == 'intern' ) {
			$link = Sobi::Url( $url );
		}
		else {
			$link = $node->attributes->getNamedItem( 'host' )->nodeValue;
			if ( !( strstr( $link, '://' ) ) ) {
				if ( $subject ) {
					$link = $this->get( $subject . '.' . $link, $index );
				}
				else {
					$link = $this->get( $link, $index );
				}
			}
			if ( count( $url ) ) {
				$link .= http_build_query( $url );
			}
		}
		return $link;
	}

	/**
	 * @param DOMNode $param
	 * @param string $subject
	 * @param integer $index
	 * @return mixed
	 */
	private function xmlParams( $param, $subject = null, $index = -1 )
	{
		$value = null;
		if ( !( $param->hasChildNodes() ) ) {
			if ( $param->attributes->getNamedItem( 'parse' ) && $param->attributes->getNamedItem( 'parse' )->nodeValue == 'true' ) {
				$currentSubject = $subject ? $subject . '.' : null;
				/** wee need to skip sometimes, and sometimes override the current subject
				 * i.e getting section id which is not a part of the object*/
				if ( $param->attributes->getNamedItem( 'subject' ) ) {
					if ( $param->attributes->getNamedItem( 'subject' )->nodeValue == 'skip' ) {
						$currentSubject = null;
					}
					else {
						$currentSubject = $param->attributes->getNamedItem( 'subject' )->nodeValue . '.';
					}
				}
				if ( $currentSubject ) {
					$value = $this->get( $currentSubject . $param->attributes->getNamedItem( 'value' )->nodeValue, $index );
				}
				else {
					$value = $this->get( $param->attributes->getNamedItem( 'value' )->nodeValue );
				}
			}
			else {
				$value = isset( $param->attributes->getNamedItem( 'value' )->nodeValue ) ? $param->attributes->getNamedItem( 'value' )->nodeValue : null;
			}
		}
		else {
			$value = array();
			foreach ( $param->childNodes as $node ) {
				if ( strstr( $node->nodeName, '#' ) ) {
					continue;
				}
				if ( isset( $node->attributes->getNamedItem( 'name' )->nodeValue ) && $node->attributes->getNamedItem( 'name' )->nodeValue ) {
					$value[ $node->attributes->getNamedItem( 'name' )->nodeValue ] = $this->xmlParams( $node, $subject, $index );
				}
				else {
					$value[ ] = $this->xmlParams( $node, $subject, $index );
				}
			}
		}
		return $value;
	}

	/**
	 * @param DOMNode $node
	 * @param array $element
	 * @param mixed $value
	 * @return void
	 */
	private function xmlField( $node, &$element, $value = null )
	{
		if ( !( $this->xmlCondition( $node ) ) ) {
			return;
		}
		/** process all attributes  */
		$attributes = $node->attributes;
		$params = array();
		$args = array( 'type' => null, 'name' => null, 'value' => $value );
		$adds = array( 'before' => null, 'after' => null );
		$xml = array();
		if ( $attributes->length ) {
			/** @var DOMElement $attribute */
			foreach ( $attributes as $attribute ) {
				$xml[ $attribute->nodeName ] = $attribute->nodeValue;
				switch ( $attribute->nodeName ) {
					case 'name':
						$args[ 'id' ] = SPLang::nid( $attribute->nodeValue );
						$element[ 'id' ] = $args[ 'id' ];
						$params[ 'id' ] = $args[ 'id' ];
					case 'name':
					case 'type':
					case 'width':
					case 'height':
					case 'prefix':
						$args[ $attribute->nodeName ] = $attribute->nodeValue;
						break;
					case 'editor':
					case 'multi':
						$args[ $attribute->nodeName ] = $attribute->nodeValue == 'true' ? true : false;
						break;
					case 'selected':
					case 'value':
						if ( $value ) {
							break;
						}
					/** no break here */
					case 'values':
						$args[ $attribute->nodeName ] = $this->get( $attribute->nodeValue );
						break;
					case 'value-parsed':
						$args[ 'value' ] = $attribute->nodeValue;
						break;
					case 'label':
					case 'header':
						$element[ $attribute->nodeName ] = Sobi::Txt( $attribute->nodeValue );
						$args[ $attribute->nodeName ] = Sobi::Txt( $attribute->nodeValue );
						break;
					case 'placeholder':
						$params[ $attribute->nodeName ] = Sobi::Txt( $attribute->nodeValue );
						break;
					default:
						if ( strstr( $attribute->nodeValue, 'var:[' ) ) {
							$params[ $attribute->nodeName ] = $this->parseValue( $attribute->nodeValue );
						}
						else {
							$params[ $attribute->nodeName ] = ( $attribute->nodeValue );
						}
						$args[ $attribute->nodeName ] = $params[ $attribute->nodeName ];
						break;
				}
			}
		}
		if ( $node->hasChildNodes() ) {
			foreach ( $node->childNodes as $child ) {
				if ( strstr( $child->nodeName, '#' ) ) {
					continue;
				}
				/** @var DOMNode $child */
				switch ( $child->nodeName ) {
					case 'values':
						if ( $child->childNodes->length ) {
							$values = array();
							/** @var DOMNode $value */
							foreach ( $child->childNodes as $value ) {
								if ( strstr( $value->nodeName, '#' ) ) {
									continue;
								}
								/** select list with groups e.g. */
								if ( $value->nodeName == 'values' ) {
									$group = array();
									if ( $value->hasChildNodes() ) {
										foreach ( $value->childNodes as $groupNode ) {
											if ( strstr( $groupNode->nodeName, '#' ) ) {
												continue;
											}
											$group[ $groupNode->attributes->getNamedItem( 'value' )->nodeValue ] = Sobi::Txt( $groupNode->attributes->getNamedItem( 'label' )->nodeValue );
										}
									}
									$values[ Sobi::Txt( $value->attributes->getNamedItem( 'label' )->nodeValue ) ] = $group;
								}
								else {
									$vv = $value->attributes->getNamedItem( 'value' )->nodeValue;
									$vl = $value->attributes->getNamedItem( 'label' ) ? $value->attributes->getNamedItem( 'label' )->nodeValue : $vv;
									$xml[ 'childs' ][ $child->nodeName ][ $vv ] = $vl;
									$values[ $vv ] = Sobi::Txt( $vl );
								}
							}
						}
						$args[ 'values' ] = $values;
						break;
					case 'value':
						if ( $child->childNodes->length ) {
							/** @var DOMNode $value */
							foreach ( $child->childNodes as $value ) {
								if ( strstr( $value->nodeName, '#' ) ) {
									continue;
								}
								switch ( $value->nodeName ) {
									case 'url':
										$params = array();
										$content = 'no content given';
										foreach ( $value->attributes as $a ) {
											switch ( $a->nodeName ) {
												case 'type':
												case 'host':
													break;
												case 'content':
													$v = $this->get( trim( $a->nodeValue ) );
													if ( !( $v ) ) {
														$v = Sobi::Txt( trim( $a->nodeValue ) );
													}
													$content = $v;
													break;
												case 'uri':
													$params[ 'href' ] = $this->get( trim( $a->nodeValue ) );
												default:
													$params[ $a->nodeName ] = $a->nodeValue;
													break;
											}
										}
										if ( !( isset( $params[ 'href' ] ) ) ) {
											$params[ 'href' ] = $this->xmlUrl( $value );
										}
										$link = '<a ';
										foreach ( $params as $k => $v ) {
											$link .= $k . '="' . $v . '" ';
										}
										$link .= '>' . $content . '</a>';
										$args[ 'value' ] = $link;
										break;
								}
							}
						}
						break;
					case 'attribute':
						$name = $child->attributes->getNamedItem( 'name' )->nodeValue;
						$value = $this->get( $child->attributes->getNamedItem( 'value' )->nodeValue );
						if ( in_array( $name, array( 'disabled', 'readonly' ) ) && !( $value ) ) {
							continue;
						}
						if ( $name == 'label' ) {
							$element[ $name ] = $value;
						}
						else {
							$params[ $name ] = $value;
							$args[ $name ] = $value;
						}
						break;
					case 'add':
						if ( $child->childNodes->length ) {
							/** @var DOMNode $value */
							foreach ( $child->childNodes as $value ) {
								if ( strstr( $value->nodeName, '#' ) ) {
									continue;
								}
								if ( $value->nodeName == 'call' ) {
									$v = $this->xmlCall( $value );
								}
								elseif ( $value->nodeName == 'text' ) {
									$v = $value->nodeValue;
								}
								$adds[ $child->attributes->getNamedItem( 'where' )->nodeValue ][ ] = $v;
							}
						}
						break;
				}
			}
		}
		$args[ 'params' ] = $params;
		$element[ 'args' ] = $args;
		$element[ 'adds' ] = $adds;
		$element[ 'request' ] = $xml;
		switch ( $args[ 'type' ] ) {
			case 'output':
				$element[ 'content' ] = $args[ 'value' ];
				break;
			case 'custom':
				$field = $this->get( $args[ 'fid' ] );
				if ( $field && $field instanceof SPField ) {
					$element[ 'label' ] = $field->get( 'name' );
					if ( count( $params ) ) {
						foreach ( $params as $k => $p ) {
							if ( $k == 'class' ) {
								$k = 'cssClass';
							}
							$field->set( $k, $p );
						}
					}
					$element[ 'content' ] = $field->field( true );
				}
				break;
			default:
				if ( method_exists( 'SPHtml_input', $args[ 'type' ] ) ) {
					$method = new ReflectionMethod( 'SPHtml_input', $args[ 'type' ] );
					$methodArgs = array();
					$methodParams = $method->getParameters();
					foreach ( $methodParams as $param ) {
						if ( isset( $args[ $param->name ] ) ) {
							$methodArgs[ ] = $args[ $param->name ];
						}
						elseif ( $param->name == 'value' && !( isset( $args[ 'value' ] ) ) && isset( $args[ 'name' ] ) ) {
							$methodArgs[ ] = $this->get( $args[ 'name' ] );
						}
						elseif ( $param->isDefaultValueAvailable() ) {
							$methodArgs[ ] = $param->getDefaultValue();
						}
						else {
							$methodArgs[ ] = null;
						}
					}
					$element[ 'content' ] = call_user_func_array( array( 'SPHtml_input', $args[ 'type' ] ), $methodArgs );
				}
				else {
					Sobi::Error( $this->name(), SPLang::e( 'METHOD_DOES_NOT_EXISTS', $params[ 0 ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
				break;
		}
	}

	private function xmlCall( $value )
	{
		$function = $value->attributes->getNamedItem( 'function' )->nodeValue;
		$r = false;
		if ( $value->hasChildNodes() ) {
			$params = array();
			foreach ( $value->childNodes as $p ) {
				if ( strstr( $p->nodeName, '#' ) ) {
					continue;
				}
				if ( $p->attributes->length && $p->attributes->getNamedItem( 'value' ) ) {
					$v = $this->get( $p->attributes->getNamedItem( 'value' )->nodeValue );
					if ( $v ) {
						$params[ ] = $v;
					}
					elseif ( $p->attributes->getNamedItem( 'default' ) ) {
						$params[ ] = $p->attributes->getNamedItem( 'default' )->nodeValue;
					}
				}
				else {
					$params[ ] = $p->nodeValue;
				}
			}
			if ( strstr( $function, '::' ) ) {
				$function = explode( '::', $function );
				if ( class_exists( $function[ 0 ] ) && method_exists( $function[ 0 ], $function[ 1 ] ) ) {
					$r = call_user_func_array( array( $function[ 0 ], $function[ 1 ] ), $params );
				}
				else {
					Sobi::Error( $this->name(), SPLang::e( 'METHOD_DOES_NOT_EXISTS', $function[ 0 ] . '::' . $function[ 1 ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
			else {
				if ( function_exists( $function ) ) {
					$r = call_user_func_array( $function, $params );
				}
				else {
					Sobi::Error( $this->name(), SPLang::e( 'METHOD_DOES_NOT_EXISTS', $function ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
		}
		return $r;
	}

	/**
	 * @param DOMNodeList $xml
	 * @return void
	 */
	private function xmlConfig( $xml )
	{
		foreach ( $xml as $node ) {
			/** @var DOMNode $node */
			switch ( $node->nodeName ) {
				case 'hidden':
					$hidden = $node->childNodes;
					foreach ( $hidden as $field ) {
						/** @var DOMNode $field */
						if ( !( strstr( $field->nodeName, '#' ) ) ) {
							if ( $field->attributes->getNamedItem( 'const' ) && $field->attributes->getNamedItem( 'const' )->nodeValue ) {
								$this->addHidden( $field->attributes->getNamedItem( 'const' )->nodeValue, $field->attributes->getNamedItem( 'name' )->nodeValue );
							}
							else {
								$value = null;
								$name = $field->attributes->getNamedItem( 'name' )->nodeValue;
								if ( $field->attributes->getNamedItem( 'value' ) && $field->attributes->getNamedItem( 'value' )->nodeValue ) {
									$value = $this->get( $field->attributes->getNamedItem( 'value' )->nodeValue );
								}
								else {
									$value = $field->attributes->getNamedItem( 'default' )->nodeValue;
								}
								$this->addHidden( SPRequest::string( $name, $value ), $name );
							}
						}
					}
					break;
				default:
					if ( !( strstr( $node->nodeName, '#' ) ) ) {
						$this->_config[ 'general' ][ $node->nodeName ] = $node->attributes->getNamedItem( 'value' )->nodeValue;
					}
					break;
			}
		}
	}

	/**
	 * @param DOMNodeList $xml
	 * @return void
	 */
	private function xmlHeader( $xml )
	{
		foreach ( $xml as $node ) {
			/** @var DOMNode $node */
			switch ( $node->nodeName ) {
				case 'script':
					SPFactory::header()
							->addJsCode( $node->nodeValue );
					break;
				case 'style':
					SPFactory::header()
							->addCSSCode( $node->nodeValue );
					break;
				case 'file':
					if ( $node->attributes->getNamedItem( 'type' )->nodeValue == 'style' ) {
						$this->loadCSSFile( $node->attributes->getNamedItem( 'filename' )->nodeValue, false );
					}
					elseif ( $node->attributes->getNamedItem( 'type' )->nodeValue == 'script' ) {
						$this->loadJsFile( $node->attributes->getNamedItem( 'filename' )->nodeValue, false );
					}
					break;
				case 'title':
					$this->setTitle( $node->attributes->getNamedItem( 'value' )->nodeValue );
					break;
			}
		}
	}

	/**
	 * @param string $path
	 * @deprecated since 1.1
	 */
	public function loadConfig( $path )
	{
		SPFactory::header()
				->addCssFile( 'adm.legacy' )
				->addJsFile( 'adm.legacy' );
		$this->_compatibility = true;
		if ( strlen( $path ) ) {
			$this->_config = SPLoader::loadIniFile( $path, true, true, true );
		}
		Sobi::Trigger( 'beforeLoadConfig', $this->name(), array( &$this->_config ) );
		if ( isset( $this->_config[ 'css_files' ] ) ) {
			foreach ( $this->_config[ 'css_files' ] as $file ) {
				$this->loadCSSFile( $file );
			}
			unset( $this->_config[ 'css_files' ] );
		}
		if ( isset( $this->_config[ 'js_files' ] ) ) {
			foreach ( $this->_config[ 'js_files' ] as $file ) {
				$this->loadJsFile( $file );
			}
			unset( $this->_config[ 'js_files' ] );
		}
		if ( $this->key( 'site_title' ) ) {
			$this->setTitle( $this->key( 'site_title' ) );
		}
		if ( isset( $this->_config[ 'toolbar' ] ) ) {
			/* in case we are adding new entry/category/field we have to remove the 'duplicate' button
			 and the separators after and before*/
			if ( $this->get( 'task' ) == 'add' || $this->get( 'task' ) == 'new' ) {
				$previous = null;
				$next = false;
				foreach ( $this->_config[ 'toolbar' ] as $key => $value ) {
					$previous = $key;
					if ( $key == 'duplicate' ) {
						if ( $next && isset( $this->_config[ 'toolbar' ][ $key ] ) ) {
							unset( $this->_config[ 'toolbar' ][ $key ] );
							break;
						}
						unset( $this->_config[ 'toolbar' ][ 'duplicate' ] );
						if ( $previous && isset( $this->_config[ 'toolbar' ][ $previous ] ) ) {
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
		if ( !( isset( $this->_config[ 'submenu' ] ) ) ) {
			$this->_config[ 'submenu' ] = SPLoader::loadIniFile( 'etc.adm.submenu', false );
		}
		if ( isset( $this->_config[ 'submenu' ] ) ) {
			SPLoader::loadClass( 'cms.html.admin_menu' );
			foreach ( $this->_config[ 'submenu' ] as $type => $settings ) {
				$type = preg_replace( '/\_{1}[a-zA-Z0-9]$/', null, $type );
				$cfg = $this->parseMenu( explode( '|', $settings ) );
				call_user_func_array( array( 'SPAdmMenu', 'addSubMenuEntry' ), $cfg );
			}
			unset( $this->_config[ 'submenu' ] );
		}
		if ( isset( $this->_config[ 'hidden' ] ) ) {
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
		if ( count( $cfg ) ) {
			foreach ( $cfg as $i => $key ) {
				if ( strstr( $key, 'var:[' ) ) {
					preg_match( '/var\:\[([a-zA-Z0-9\.\_\-]*)\]/', $key, $matches );
					$key = str_replace( $matches[ 0 ], $this->get( $matches[ 1 ] ), $key );
				}
				if ( strstr( $key, '->' ) ) {
					$key = explode( '->', $key );
					$callback = trim( $key[ 0 ] );
					$params = isset( $key[ 1 ] ) ? trim( $key[ 1 ] ) : null;
					if ( strstr( $callback, '.' ) ) {
						$callback = explode( '.', $callback );
						$class = trim( $callback[ 0 ] );
						if ( !class_exists( $class ) ) {
							$class = 'SP' . ucfirst( $class );
						}
						$method = isset( $callback[ 1 ] ) ? trim( $callback[ 1 ] ) : null;
						if ( $method && class_exists( $class ) && method_exists( $class, $method ) ) {
							$cfg[ $i ] = call_user_func_array( array( $class, $method ), array( $params ) );
						}
						else {
							Sobi::Error( 'Function from INI', SPLang::e( 'Function %s::%s does not exists.', $class, $method ), SPC::WARNING, 0, __LINE__, __FILE__ );
						}
					}
					else {
						if ( function_exists( $callback ) ) {
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
	public function loadCSSFile( $path, $adm = true )
	{
		Sobi::Trigger( 'loadCSSFile', $this->name(), array( &$path ) );
		if ( strstr( $path, '|' ) ) {
			$path = explode( '|', $path );
			$adm = $path[ 1 ];
			$path = $path[ 0 ];
		}
		SPFactory::header()->addCSSFile( $path, $adm );
	}

	/**
	 *
	 * @param path
	 */
	public function loadJsFile( $path, $adm = true )
	{
		Sobi::Trigger( 'loadJsFile', $this->name(), array( &$path ) );
		if ( strstr( $path, '|' ) ) {
			$path = explode( '|', $path );
			$adm = $path[ 1 ];
			$path = $path[ 0 ];
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
	 * @param string $title
	 * @return string
	 */
	public function setTitle( $title )
	{
		if ( strstr( $title, '{' ) ) {
			$title = SPFactory::config()->structuralData( 'json://' . $title );
			$task = SPRequest::task();
			$title = $title->$task;
		}
		$title = $this->parseValue( Sobi::Txt( $title ) );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title' );
		return $title;
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
		if ( strstr( $key, '.' ) ) {
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
		if ( strpos( $attr, '[JS]' ) === false ) {
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
		if ( $date == 0 ) {
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
	 * @param mixed $attr
	 * @param int $index
	 */
	protected function show( $attr, $index = -1 )
	{
		if ( strstr( $attr, 'config.' ) !== false ) {
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
	public function set( $attr, $name )
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
					if ( method_exists( $var, 'has' ) /*&& $var->has( $property )*/ ) {
						if ( method_exists( $var, 'get' ) ) {
							$var = $var->get( $property, null, true );
						}
					}
					/* otherwise try to access std object */
					elseif ( is_object( $var ) && isset( $var->$property ) ) {
						$var = $var->$property;
					}
					elseif ( $property == 'length' && is_array( $var ) ) {
						$r = count( $var );
						return $r;
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
		$tpl = SPLoader::path( $this->_template . '_override', 'adm.template' );
		if ( !( $tpl ) ) {
			$tpl = SPLoader::path( $this->_template, 'adm.template' );
		}
		if ( !$tpl ) {
			$tpl = SPLoader::translatePath( $this->_template, 'adm.template', false );
			Sobi::Error( $this->name(), SPLang::e( 'TEMPLATE_DOES_NOT_EXISTS', $tpl ), SPC::ERROR, 500, __LINE__, __FILE__ );
			exit();
		}
		Sobi::Trigger( 'Display', $this->name(), array( &$this ) );
		$action = $this->key( 'action' );
		echo '<div class="SobiPro" id="SobiPro">' . "\n";
		if ( $this->_compatibility ) {
			echo '<div class="row-fluid">' . "\n";
		}
		echo $action ? "\n<form action=\"{$action}\" method=\"post\" name=\"adminForm\" id=\"SPAdminForm\" enctype=\"multipart/form-data\" accept-charset=\"utf-8\" >\n" : null;
		include( $tpl );
		if ( count( $this->_hidden ) ) {
			$this->_hidden[ SPFactory::mainframe()->token() ] = 1;
			foreach ( $this->_hidden as $name => $value ) {
				echo "\n<input type=\"hidden\" name=\"{$name}\" id=\"SP_{$name}\" value=\"{$value}\"/>";
			}
		}
		echo '</div>' . "\n";
		echo $action ? "\n</form>\n" : null;
		if ( $this->_compatibility ) {
			echo '</div>' . "\n";
		}
		echo '</div>';
		Sobi::Trigger( 'AfterDisplay', $this->name() );
	}

	/**
	 */
	protected function menu( $return = false )
	{
		$m = $this->get( 'menu' );
		if ( $m && method_exists( $m, 'display' ) ) {
			if ( $return ) {
				return $m->display();
			}
			else {
				echo $m->display();
			}
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
	protected function parentPath( $id, $parents = false, $last = false, $offset = 2 )
	{
		static $pathArray = null;
		$path = null;
		if ( !( $pathArray ) ) {
			$pathArray = SPFactory::config()->getParentPath( $id, true, $parents );
		}
		if ( !( $last ) ) {
			if ( is_array( $pathArray ) ) {
//				if ( strstr( $this->get( 'task' ), 'edit' ) ) {
//					unset( $path[ count( $path ) - 1 ] );
//				}
				$path = implode( Sobi::Cfg( 'string.path_separator', ' > ' ), $pathArray );
			}
		}
		else {
			if ( is_array( $pathArray ) && isset( $pathArray[ count( $pathArray ) - $offset ] ) ) {
				$path = $pathArray[ count( $pathArray ) - $offset ];
			}
		}
		return SPLang::clean( $path );
	}
}
