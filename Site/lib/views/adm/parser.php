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
class SPTplParser
{
	/** @var bool */
	protected $tabsContentOpen = false;
	/** @var bool */
	protected $activeTab = false;
	/** @var bool */
	protected $table = true;
	/** @var bool */
	protected $loopTable = true;
	/** @var string */
	protected $thTd = 'th';
	/** @var array */
	protected $_out = [];
	/** @var bool */
	protected $loopOpen = false;
	/** @var array */
	protected $_tickerIcons = [
			0 => 'remove',
			1 => 'ok',
			-1 => 'stop',
			-2 => 'pause'
	];
	/** @var string */
	protected $_checkedOutIcon = 'lock';
	/** @var string */
	static $newLine = "\n";
	/** @var array */
	protected $html = [ 'div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'a', 'button', 'url', 'img', 'table', 'ul', 'li', 'pre', 'label', 'tr', 'th', 'td', 'code', 'i' ];
	/** @var array */
	protected $internalAttributes = [ 'condition' ];


	public function __construct( $table = false )
	{
		$this->table = $table;
	}

	public function parse( $data )
	{
		if ( is_array( $data ) && !( is_string( $data ) ) ) {
			foreach ( $this->internalAttributes as $attribute ) {
				if ( isset( $data[ 'attributes' ][ $attribute ] ) ) {
					unset( $data[ 'attributes' ][ $attribute ] );
				}
			}
			if ( isset( $data[ 'type' ] ) ) {
				$this->openElement( $data );
				$this->parseElement( $data );
			}
			if ( isset( $data[ 'content' ] ) && !( is_string( $data[ 'content' ] ) ) && is_array( $data[ 'content' ] ) && count( $data[ 'content' ] ) ) {
				foreach ( $data[ 'content' ] as $element ) {
					$this->parse( $element );
				}
			}
//			elseif ( isset( $data[ 'childs' ] ) && !( is_string( $data[ 'childs' ] ) ) && is_array( $data[ 'childs' ] ) && count( $data[ 'childs' ] ) ) {
//				foreach ( $data[ 'childs' ] as $element ) {
//					$this->parse( $element );
//				}
//			}
			if ( isset( $data[ 'type' ] ) ) {
				$this->closeElement( $data );
			}
		}
		echo implode( "", $this->_out );
		$this->_out = [];
	}

	protected function parseElement( $element )
	{
		if ( !( isset( $element[ 'attributes' ] ) ) ) {
			$element[ 'attributes' ] = [];
		}
		switch ( $element[ 'type' ] ) {
			case 'field':
				if ( isset( $element[ 'attributes' ] ) && $this->istSet( $element[ 'attributes' ], 'stand-alone', 'true' ) ) {
					$this->_out[] = $element[ 'content' ];
					break;
				}
				if ( $this->table ) {
					$this->_out[] = '<tr>';
					$this->_out[] = '<td>';
				}
				$this->_out[] = '<div class="control-group">';
				if ( isset( $element[ 'label' ] ) && strlen( $element[ 'label' ] ) ) {
					if ( !( isset( $element[ 'id' ] ) ) ) {
						$element[ 'id' ] = $this->istSet( $element[ 'attributes' ], 'id' ) ? $element[ 'attributes' ][ 'id' ] : SPLang::nid( $element[ 'label' ] );
					}
					if ( isset( $element[ 'help-text' ] ) && $element[ 'help-text' ] ) {
						$element[ 'label' ] = '<a href="#" rel="popover" data-title="' . htmlspecialchars( $element[ 'label' ], ENT_COMPAT ) . '" data-content="' . htmlspecialchars( $element[ 'help-text' ], ENT_COMPAT ) . '">' . $element[ 'label' ] . '</a>';
					}
					$add = null;
					if ( $this->istSet( $element, 'revisions-change' ) ) {
						$i = strlen( $element[ 'revisions-change' ] ) > 5 ? $element[ 'revisions-change' ] : $element[ 'id' ];
						$add = '&nbsp;<a data-fid="' . $i . '" href="#" class="btn btn-mini btn-warning ctrl-revision-compare">&nbsp;<i class="icon-resize-horizontal"></i></a>';
					}
					$this->_out[] = "<label class=\"control-label\" for=\"{$element['id']}\">{$element['label']}{$add}</label>\n";
				}
				if ( $this->table ) {
					$this->_out[] = '</td>';
				}
				$warn = '';
				if ( isset( $element[ 'attributes' ][ 'warn' ] ) && strlen( $element[ 'attributes' ][ 'warn' ] ) ) {
					$warn = ' ' . $element[ 'attributes' ][ 'warn' ];
					//unset($element[ 'attributes' ][ 'warn' ]);
				}
				$this->_out[] = "<div class=\"controls{$warn}\">\n";
				$class = null;

				if ( $element[ 'args' ][ 'type' ] == 'output' ) {
					if ( $this->istSet( $element, 'id' ) ) {
						$this->_out[] = "<div class=\"spOutput\" id=\"{$element['id']}\">";
					}
					else {
						$this->_out[] = "<div class=\"spOutput\">";
					}
					$outclass = null;
					if ( isset( $element[ 'args' ][ 'params' ][ 'class' ] ) ) {
						$outclass = $element[ 'args' ][ 'params' ][ 'class' ];
					}
					elseif ( isset( $element[ 'attributes' ][ 'class' ] ) ) {
						$outclass = $element[ 'attributes' ][ 'class' ];
					}
					$id = null;
					if ( isset( $element[ 'args' ][ 'params' ][ 'id' ] ) ) {
						$id = ' id="' . $element[ 'args' ][ 'params' ][ 'id' ] . '" ';
					}
					if ( $outclass ) {
						$this->_out[] = "<span class=\"{$outclass}\"{$id}>\n";
					}
					else {
						$this->_out[] = "<span{$id}>\n";
					}
					if ( isset( $element[ 'attributes' ][ 'icons' ] ) && $element[ 'attributes' ][ 'icons' ] ) {
						$icons = json_decode( str_replace( "'", '"', $element[ 'attributes' ][ 'icons' ] ), true );
						$element[ 'content' ] = (int)$element[ 'content' ];
						$icon = ( isset( $icons[ $element[ 'content' ] ] ) && $icons[ $element[ 'content' ] ] ) ? $icons[ $element[ 'content' ] ] : $this->_tickerIcons[ $element[ 'content' ] ];
						$element[ 'content' ] = '<i class="icon-' . $icon . '"></i>';
					}
				}
				if ( count( $element[ 'adds' ][ 'before' ] ) ) {
					$class .= 'input-prepend ';
				}
				if ( count( $element[ 'adds' ][ 'after' ] ) ) {
					$class .= 'input-append ';
				}
				if ( $class ) {
					$this->_out[] = "<div class=\"{$class}\">\n";
				}
				if ( $this->table ) {
					$this->_out[] = '<td>';
				}
				if ( count( $element[ 'adds' ][ 'before' ] ) ) {
					foreach ( $element[ 'adds' ][ 'before' ] as $o ) {
						$this->_out[] = "<span class=\"add-on\">{$o}</span>";
					}
				}
				/** here is the right content output */
				$this->_out[] = $element[ 'content' ];
				if ( count( $element[ 'adds' ][ 'after' ] ) ) {
					foreach ( $element[ 'adds' ][ 'after' ] as $o ) {
						if ( $this->istSet( $o, 'element', 'button' ) ) {
							if ( ( isset( $o[ 'icon' ] ) && $o[ 'icon' ] ) ) {
								$o[ 'label' ] = '<i class="icon-' . $o[ 'icon' ] . '"></i>' . $o[ 'label' ];
							}
							$data = null;
							foreach ( $o as $a => $v ) {
								if ( !( strstr( $a, 'data-' ) ) ) {
									continue;
								}
								$data .= ' ' . $a . '="' . $v . '" ';
							}
							$this->_out[] = "<button class=\"{$o['class']}\" {$data} type=\"button\">{$o['label']}</button>";
						}
						else {
							$this->_out[] = "<span class=\"add-on\">{$o}</span>";
						}
					}
				}
				if ( $element[ 'args' ][ 'type' ] == 'output' ) {
					$this->_out[] = "</div></span>\n";
				}
				if ( $class ) {
					$this->_out[] = "</div>\n";
				}
				if ( $this->table ) {
					$this->_out[] = '</td>';
					$this->_out[] = '</tr>';
				}
				$this->_out[] = '</div>';
				$this->_out[] = '</div>';
				break;
			case 'header':
				$this->_out[] = '<div class="span6 spScreenSubHead"><i class="icon-' . $element[ 'attributes' ][ 'icon' ] . ' icon-2x"></i>';
//				$this->_out[] = '<div class="span6 spScreenSubHead spicon-48-' . $element[ 'attributes' ][ 'icon' ] . '">';
				$this->_out[] = '<div class="title">' . $element[ 'attributes' ][ 'label' ] . '</div>';
				$this->_out[] = '</div>';
				break;
			case 'url':
				if ( isset( $element[ 'attributes' ][ 'image' ] ) ) {
					$this->_out[] = "<img src=\"{$element['attributes']['image']}\" alt=\"{$element['attributes']['label']}\" />";
					$this->closeElement( $element );
					$this->openElement( $element );
				}
				if ( isset( $element[ 'attributes' ][ 'icon' ] ) ) {
					$this->_out[] = "<i class=\"icon-{$element['attributes']['icon']}\"></i>";
					$this->closeElement( $element );
					$this->openElement( $element );
				}
				$this->_out[] = $element[ 'content' ];
				break;
			case 'text':
			case 'menu':
			case 'toolbar':
				$this->_out[] = $element[ 'content' ];
				break;
		}
	}

	protected function istSet( $element, $index, $value = null )
	{
		if ( !( isset( $element[ $index ] ) ) ) {
			return false;
		}
		if ( $value ) {
			return $element[ $index ] == $value;
		}

		return true;

	}

	public function openElement( $data )
	{
		switch ( $data[ 'type' ] ) {
			case 'tabs':
				$this->tabsHeader( $data );
				break;
			case 'tab':
				if ( !( $this->activeTab ) ) {
					$this->_out[] = '<div class="tab-pane active" id="' . $data[ 'id' ] . '">';
					$this->activeTab = true;
				}
				else {
					$this->_out[] = '<div class="tab-pane" id="' . $data[ 'id' ] . '">';
				}
				break;
			case 'fieldset':
				if ( $this->table ) {
					$this->_out[] = '<table class="table table-striped table-bordered table-condensed">';
					$this->_out[] = '<tbody>';
				}
				$formType = isset( $data[ 'attributes' ][ 'type' ] ) && $data[ 'attributes' ][ 'type' ] ? $data[ 'attributes' ][ 'type' ] : 'horizontal';
				$this->_out[] = '<div class="form-' . $formType . '">';
				if ( isset( $data[ 'label' ] ) && $data[ 'label' ] ) {
					$this->_out[] = '<div class="control-group spFieldGroup"><label class="control-label">' . $data[ 'label' ] . '</label></div>';
				}
				break;
			case 'head':
				$this->thTd = 'th';
				$this->_out[] = '<thead>';
				$this->_out[] = '<tr>';
				break;
			case 'cell':
				$this->proceedCell( $data, $this->loopTable ? $this->thTd : 'div' );
				break;
			case 'header':
				$this->_out[] = '<div class="SPAdmNavBar">';
				break;
			case 'loop':
				$this->loopTable = true;
				if ( $this->istSet( $data[ 'attributes' ], 'table' ) ) {
					$this->loopTable = $data[ 'attributes' ][ 'table' ] == 'false' ? false : true;
				}
				if ( $this->loopTable ) {
					$this->_out[] = '<tbody>';
				}
				$this->loopOpen = true;
				break;
			case 'loop-row':
				if ( $this->loopTable ) {
					$this->_out[] = '<tr>';
				}
				break;
			case 'message':
				$this->message( $data );
				break;
			case 'tooltip':
			case 'popover':
				$this->tooltip( $data );
				break;
			case 'pagination':
				$this->_out[] = $data[ 'content' ];
				break;
			case 'table':
				if ( isset( $data[ 'attributes' ][ 'class' ] ) && $data[ 'attributes' ][ 'class' ] ) {
//				if ( ( $data[ 'attributes' ][ 'class' ] ) ) {
					$data[ 'attributes' ][ 'class' ] = 'table ' . $data[ 'attributes' ][ 'class' ];
				}
				else {
					$data[ 'attributes' ][ 'class' ] = 'table table-striped table-hover';
				}
			//break; no break by intention
			default:
				if ( in_array( $data[ 'type' ], $this->html ) ) {
					$tag = $data[ 'type' ];
					if ( $data[ 'type' ] == 'url' ) {
						$tag = 'a';
					}
					$a = null;
					if ( count( $data[ 'attributes' ] ) ) {
						foreach ( $data[ 'attributes' ] as $att => $value ) {
							if ( in_array( $att, [ 'type', 'image', 'label' ] ) ) {
								continue;
							}
							$a .= " {$att}=\"{$value}\"";
						}
					}
					$this->_out[] = "<{$tag}{$a}>";
				}
				break;
		}
	}

	protected function tooltip( $data )
	{
		if ( !( isset( $data[ 'attributes' ][ 'href' ] ) ) ) {
			/** in case it get through the params */
			if ( isset( $data[ 'href' ] ) ) {
				$data[ 'attributes' ][ 'href' ] = $data[ 'href' ];
				$data[ 'attributes' ][ 'target' ] = '_blank';
			}
			else {
				$data[ 'attributes' ][ 'href' ] = '#';
			}
		}
		$data[ 'attributes' ][ 'rel' ] = $data[ 'type' ];
		if ( $data[ 'type' ] == 'tooltip' ) {
			$data[ 'attributes' ][ 'title' ] = htmlspecialchars( $data[ 'content' ], ENT_COMPAT );
			$data[ 'attributes' ][ 'rel' ] = 'sp-tooltip';
		}
		elseif ( $data[ 'type' ] == 'popover' ) {
			$data[ 'attributes' ][ 'data-title' ] = htmlspecialchars( $data[ 'title' ], ENT_COMPAT );
			$data[ 'attributes' ][ 'data-content' ] = htmlspecialchars( $data[ 'content' ], ENT_COMPAT );
		}
		$el = '<a ';
		foreach ( $data[ 'attributes' ] as $tag => $val ) {
			$el .= "{$tag}=\"{$val}\" ";
		}
		if ( $this->istSet( $data, 'icon' ) ) {
			$el .= "><i class=\"icon-{$data['icon']}\"></i></a>";
		}
		else {
			$el .= ">{$data['title']}</a>";
		}
		$this->_out[] = $el;
	}

	protected function message( $data )
	{
		$class = isset( $data[ 'attributes' ][ 'class' ] ) && $data[ 'attributes' ][ 'class' ] ? $data[ 'attributes' ][ 'class' ] : null;
		if ( $this->istSet( $data[ 'attributes' ], 'label' ) ) {
			$type = isset( $data[ 'attributes' ][ 'type' ] ) && $data[ 'attributes' ][ 'type' ] ? 'alert-' . $data[ 'attributes' ][ 'type' ] : null;
			$icon = null;
			if ( isset( $data[ 'attributes' ][ 'icon' ] ) && ( $data[ 'attributes' ][ 'icon' ] == 'true' ) ) {
				if ( $type == 'alert-success' ) {
					$icon = 'icon-thumbs-up';
				}
				elseif ( $type == 'alert-info' ) {
					$icon = 'icon-lightbulb';
				}
				else {
					$icon = 'icon-thumbs-down';
				}
				$icon = "<i class=\"{$icon}\"></i> ";
			}
			$this->_out[] = "<div class=\"alert {$type} {$class}\">";
			if ( isset( $data[ 'attributes' ][ 'dismiss-button' ] ) && $data[ 'attributes' ][ 'dismiss-button' ] == 'true' ) {
				$this->_out[] = '<button type="button" class="close" data-dismiss="alert">×</button>';
			}
			$this->_out[] = $icon . $data[ 'attributes' ][ 'label' ];
			$this->_out[] = '</div>';
		}
		else {
			$attr = [];
			if ( isset( $data[ 'attributes' ][ 'type' ] ) ) {
				unset( $data[ 'attributes' ][ 'type' ] );
			}
			if ( count( $data[ 'attributes' ] ) ) {
				foreach ( $data[ 'attributes' ] as $n => $v ) {
					$attr[] = "{$n}=\"{$v}\"";
				}
			}
			$attr = implode( ' ', $attr );
			$messages = SPFactory::message()->getMessages();
			if ( count( $messages ) ) {
				foreach ( $messages as $type => $texts ) {
					if ( count( $texts ) ) {
						$this->_out[] = "<div class=\"alert alert-{$type} spSystemAlert\">";
						$this->_out[] = '<button type="button" class="close" data-dismiss="alert">×</button>';
						foreach ( $texts as $text ) {
							$this->_out[] = "<div>{$text}</div>";
						}
						$this->_out[] = '</div>';
					}
				}
			}
			$this->_out[] = "<div {$attr}></div>";
		}
	}

	public function closeElement( $data )
	{
		switch ( $data[ 'type' ] ) {
			case 'tabs':
				$this->tabsContentOpen = false;
			case 'tab':
				$this->_out[] = '</div>';
				break;
			case 'fieldset':
				if ( $this->table ) {
					$this->_out[] = '</tbody>';
					$this->_out[] = '</table>';
				}
				$this->_out[] = '</div>';
				break;
			case 'head':
				$this->thTd = 'td';
				$this->_out[] = '</tr>';
				$this->_out[] = '</thead>';
				break;
			case 'header':
				$this->_out[] = '</div>';
				break;
			case 'loop':
				if ( $this->loopTable ) {
					$this->_out[] = '</tbody>';
				}
				$this->loopTable = true;
				$this->loopOpen = false;
				break;
			case 'loop-row':
				if ( $this->loopTable ) {
					$this->_out[] = '</tr>';
				}
				break;
			case 'link':
				$data[ 'type' ] = 'a';
			default:
				if ( in_array( $data[ 'type' ], $this->html ) ) {
					$tag = $data[ 'type' ];
					if ( $data[ 'type' ] == 'url' ) {
						$tag = 'a';
					}
					$this->_out[] = "</{$tag}>";
				}
				break;


		}
	}

	public function proceedCell( $cell, $span = null )
	{
		$span = $this->istSet( $cell[ 'attributes' ], 'element' ) ? $cell[ 'attributes' ][ 'element' ] : $span;
		if ( $cell[ 'type' ] == 'text' ) {
			return $this->parseElement( $cell );
		}
		if ( $cell[ 'type' ] == 'tooltip' || $cell[ 'type' ] == 'popover' ) {
			return $this->tooltip( $cell );
		}
		$data = null;
		if ( isset( $cell[ 'attributes' ] ) && count( $cell[ 'attributes' ] ) ) {
			foreach ( $cell[ 'attributes' ] as $att => $value ) {
				if ( !( strstr( $att, 'data-' ) ) ) {
					continue;
				}
				$data .= ' ' . $att . '="' . $value . '" ';
			}
		}
		if ( isset( $cell[ 'attributes' ][ 'class' ] ) ) {
			$c = $cell[ 'attributes' ][ 'class' ];
			$this->_out[] = "\n<{$span} {$data} class=\"{$c}\">\n";
		}
		else {
			$this->_out[] = "\n<{$span} {$data}>\n";
		}
		$type = isset( $cell[ 'attributes' ][ 'type' ] ) ? $cell[ 'attributes' ][ 'type' ] : 'text';
		switch ( $type ) {
			/** no break here - continue */
			case 'text':
			case 'link':
				if ( $type == 'link' ) {
					$class = null;
					$target = null;
					if ( $this->istSet( $cell[ 'attributes' ], 'link-class' ) ) {
						$class = "class=\"{$cell['attributes']['link-class']}\" ";
					}
					if ( $this->istSet( $cell[ 'attributes' ], 'target' ) ) {
						$target = "target=\"{$cell['attributes']['target']}\" ";
					}
					$this->_out[] = "<a href=\"{$cell['link']}\"{$class}{$target} >";
				}
				if ( $this->istSet( $cell[ 'attributes' ], 'icon' ) ) {
					$this->_out[] = '<i class="icon-' . $cell[ 'attributes' ][ 'icon' ] . '"></i>';
				}
				if ( $type == 'text' ) {
					if ( $this->istSet( $cell[ 'attributes' ], 'dateFormat' ) ) {
						$date = strtotime( $cell[ 'content' ] );
						$date = date( $cell[ 'attributes' ][ 'dateFormat' ], ( $date ) );
						$cell[ 'content' ] = $date;
					}
				}
				if ( $this->istSet( $cell[ 'attributes' ], 'label' ) ) {
					$this->_out[] = $cell[ 'attributes' ][ 'label' ];
				}
				if ( $this->istSet( $cell, 'label' ) ) {
					$class = null; //if label in cell directly (with optional class) add a span as it could be a label/value pair
					if ( $this->istSet( $cell[ 'attributes' ], 'class' ) ) {
						$class = "class=\"{$cell['attributes']['class']}Label\"";
					}
					$this->_out[] = "<span {$class}>{$cell['label']}</span>";
				}
				if ( $this->istSet( $cell[ 'content' ], 'element', 'button' ) ) {
					$this->renderButton( $cell[ 'content' ] );
				}
//				if ( $type == 'text' ) {
//					$class = null; //if text in cell directly (with optional class) add a span
//					if ( $this->istSet( $cell[ 'attributes' ], 'class' ) ) {
//						$class = "class=\"{$cell['attributes']['class']}\"";
//					}
//					$this->_out[] = "<span {$class}>{$cell[ 'content' ]}</span>";
//				}
				else {
					$this->_out[] = $cell[ 'content' ];
				}
				/** no break here - continue */
				if ( $type == 'link' ) {
					$this->_out[] = "</a>";
				}
				break;
			case 'image':
				$this->_out[] = "<img src=\"{$cell['link']}\" />";
				break;
			case 'ordering':
				if ( isset( $cell[ 'attributes' ][ 'label' ] ) ) {
					$this->_out[] = $cell[ 'attributes' ][ 'label' ];
					$this->_out[] = '<button class="btn btn-mini" name="spReorder" rel="' . $cell[ 'attributes' ][ 'rel' ] . '">';
					$this->_out[] = '<i class="icon-reorder"></i>';
					$this->_out[] = '</button>';
				}
				else {
					$this->_out[] = SPHtml_Input::text( $cell[ 'attributes' ][ 'name' ], $cell[ 'content' ], [ 'class' => 'input-mini input-micro spSubmit' ] );
				}
				break;
			case 'checkbox':
				$cell = $this->checkbox( $cell );
				break;
			case 'ticker':
				$cell = $this->ticker( $cell );
				break;
		}
		if ( isset( $cell[ 'childs' ] ) && count( $cell[ 'childs' ] ) ) {
			foreach ( $cell[ 'childs' ] as $child ) {
				$this->proceedCell( $child, 'div' );
			}
		}
		$this->_out[] = "\n</{$span}>\n";
	}

	public function ticker( $cell )
	{
		$index = $cell[ 'content' ];
		$linkClass = isset( $cell[ 'attributes' ][ 'link-class' ] ) ? ' ' . $cell[ 'attributes' ][ 'link-class' ] : null;
		$aOpen = false;
		$now = gmdate( 'U' );
		/** is expired ? */
		if ( isset( $cell[ 'attributes' ][ 'valid-until' ] ) && $cell[ 'attributes' ][ 'valid-until' ] && strtotime( $cell[ 'attributes' ][ 'valid-until' ] . ' UTC' ) < $now && strtotime( $cell[ 'attributes' ][ 'valid-until' ] ) > 0 ) {
			$index = -1;
			$aOpen = true;
			$txt = Sobi::Txt( 'ROW_EXPIRED', $cell[ 'attributes' ][ 'valid-until' ] );
			$this->_out[] = '<a href="#" rel="sp-tooltip" data-original-title="' . $txt . '" class="expired">';
		}
		/** is pending ? */
		elseif ( ( isset( $cell[ 'attributes' ][ 'valid-since' ] ) && $cell[ 'attributes' ][ 'valid-since' ] && strtotime( $cell[ 'attributes' ][ 'valid-since' ] . ' UTC' ) > $now ) && $index == 1 ) {
			$index = -2;
			$aOpen = true;
			$txt = Sobi::Txt( 'ROW_PENDING', $cell[ 'attributes' ][ 'valid-since' ] );
			$this->_out[] = '<a href="#" rel="sp-tooltip" data-original-title="' . $txt . '" class="pending' . $linkClass . '">';
		}
		elseif ( $index < 0 ) {
			$txt = 'Locked';
			if ( $this->istSet( $cell[ 'attributes' ], 'status-text' ) ) {
				$txt = json_decode( str_replace( "'", '"', $cell[ 'attributes' ][ 'status-text' ] ), true );
				$txt = Sobi::Txt( $txt[ $index ] );
			}
			$aOpen = true;
			$this->_out[] = '<a href="#" rel="sp-tooltip" data-original-title="' . $txt . '" class="pending' . $linkClass . '">';
		}
		elseif ( isset( $cell[ 'link' ] ) && $cell[ 'link' ] ) {
			$cell[ 'link' ] = $cell[ 'link' ] . '&t=' . microtime( true );
			$aOpen = true;
			$this->_out[] = "<a href=\"{$cell['link']}\" class=\"{$linkClass}\" >";
		}
		$icons = [];
		if ( isset( $cell[ 'attributes' ][ 'icons' ] ) && $cell[ 'attributes' ][ 'icons' ] ) {
			$icons = json_decode( str_replace( "'", '"', $cell[ 'attributes' ][ 'icons' ] ), true );
		}
		if ( !( count( $icons ) ) ) {
			$icons = $this->_tickerIcons;
		}
		$icon = ( isset( $icons[ $index ] ) && $icons[ $index ] ) ? $icons[ $index ] : $this->_tickerIcons[ $index ];
		$this->_out[] = '<i class="icon-' . $icon . '"></i>';
		if ( $aOpen ) {
			$this->_out[] = "</a>";

			return $cell;
		}

		return $cell;
	}

	public function checkbox( $cell )
	{
		/** First let's check if it is not checked out */
		if ( isset( $cell[ 'attributes' ][ 'checked-out-by' ] ) && isset( $cell[ 'attributes' ][ 'checked-out-time' ] ) && $cell[ 'attributes' ][ 'checked-out-by' ] && $cell[ 'attributes' ][ 'checked-out-by' ] != Sobi::My( 'id' ) && strtotime( $cell[ 'attributes' ][ 'checked-out-time' ] ) > gmdate( 'U' ) ) {
			if ( isset( $cell[ 'attributes' ][ 'checked-out-ico' ] ) && $cell[ 'attributes' ][ 'checked-out-ico' ] ) {
				$icon = $cell[ 'attributes' ][ 'checked-out-ico' ];
			}
			else {
				$icon = $this->_checkedOutIcon;
			}
			$user = SPUser::getInstance( $cell[ 'attributes' ][ 'checked-out-by' ] );
			$txt = Sobi::Txt( 'CHECKED_OUT', $user->get( 'name' ), $cell[ 'attributes' ][ 'checked-out-time' ] );
			$this->_out[] = '<a href="#" rel="sp-tooltip" data-original-title="' . $txt . '" class="checkedout">';
			$this->_out[] = '<i class="icon-' . $icon . '"></i>';
			$this->_out[] = '</a>';

			return $cell;
		}
		elseif ( $this->istSet( $cell[ 'attributes' ], 'locked', true ) ) {
			$icon = $this->istSet( $cell[ 'attributes' ], 'locked-icon' ) ? $cell[ 'attributes' ][ 'locked-icon' ] : $this->_checkedOutIcon;
			$text = $this->istSet( $cell[ 'attributes' ], 'locked-text' ) ? $cell[ 'attributes' ][ 'locked-text' ] : $this->_checkedOutIcon;
			$this->_out[] = '<a href="#" rel="sp-tooltip" data-original-title="' . $text . '" class="checkedout">';
			$this->_out[] = '<i class="icon-' . $icon . '"></i>';
			$this->_out[] = '</a>';

			return $cell;
		}
		$type = $this->istSet( $cell[ 'attributes' ], 'input-type' ) ? $cell[ 'attributes' ][ 'input-type' ] : 'checkbox';
		if ( isset( $cell[ 'attributes' ][ 'rel' ] ) && $cell[ 'attributes' ][ 'rel' ] ) {
			$this->_out[] = '<input type="' . $type . '" name="spToggle" value="1" rel="' . $cell[ 'attributes' ][ 'rel' ] . '"/>';

			return $cell;
		}
		else {
			$label = false;
			$checked = null;
			$id = null;
			if ( isset( $cell[ 'label' ] ) ) {
				$id = uniqid( SPLang::nid( strtolower( $cell[ 'label' ] ) ) );
				$label = true;
				$this->_out[] = '<label for="' . $id . '" class="checkbox">';
				$id = ' id="' . $id . '" ';
			}

			if ( $this->istSet( $cell[ 'attributes' ], 'checked' ) ) {
				$checked = 'checked="checked" ';
			}
			$multiple = $this->istSet( $cell[ 'attributes' ], 'multiple', 'false' ) ? null : '[]';
			$this->_out[] = '<input type="' . $type . '" class="' . $type . '" name="' . $cell[ 'attributes' ][ 'name' ] . $multiple . '" value="' . $cell[ 'content' ] . '"' . $checked . $id . '/>';
			if ( $label ) {
				$this->_out[] = $cell[ 'label' ] . '</label>';
			}
			return $cell;
		}
	}

	public function tabsHeader( $element )
	{
		$this->tabsContentOpen = true;
		$this->activeTab = false;
		$this->_out[] = "\n" . '<ul class="nav nav-tabs responsive">';
		$active = false;
		if ( isset( $element[ 'content' ] ) && count( $element[ 'content' ] ) ) {
			foreach ( $element[ 'content' ] as $tab ) {
				if ( !( $active ) ) {
					$active = true;
					$this->_out[] = "<li class=\"active\" ><a href=\"#{$tab['id']}\">{$tab['label']}</a></li>\n";
				}
				else {
					$this->_out[] = "<li><a href=\"#{$tab['id']}\">{$tab['label']}</a> </li>\n";
				}
			}
		}
		$this->_out[] = '</ul>';
		$this->_out[] = "\n" . '<div class="tab-content responsive">';
	}

	public function renderButton( $button, $list = false )
	{
		$rel = null;
		$class = isset( $button[ 'class' ] ) ? ' ' . $button[ 'class' ] : null;
		if ( !( isset( $button[ 'task' ] ) ) || !( $button[ 'task' ] ) ) {
			$href = null;
			$rel = null;
		}
		else {
			$rel = $button[ 'task' ];
			$href = null;
		}
		$label = $button[ 'label' ];
		$target = ( isset( $button[ 'target' ] ) && $button[ 'target' ] ) ? " target=\"{$button['target']}\"" : null;
		if ( isset( $button[ 'buttons' ] ) && count( $button[ 'buttons' ] ) ) {
			$this->_out[] = '<div class="btn-group">';
			$this->_out[] = "<a href=\"{$href}\" class=\"btn{$class}\"{$target} rel=\"{$rel}\">";
			if ( !( isset( $button[ 'icon' ] ) && $button[ 'icon' ] ) ) {
				$icon = 'cog';
			}
			else {
				$icon = $button[ 'icon' ];
			}
			if ( $icon != 'none' ) {
				$this->_out[] = '<i class="icon-' . $icon . '"></i>' . $label;
			}
			else {
				$this->_out[] = $label;
			}
			$this->_out[] = '</a>';
			$this->_out[] = '<button class="btn dropdown-toggle" data-toggle="dropdown"><span class="icon-caret-down"></span>&nbsp;</button>';
			$this->_out[] = '<div class="dropdown-menu" id="' . SPLang::nid( $button[ 'task' ] ) . '">';
			$this->_out[] = '<ul class="nav nav-stacked SpDropDownBt">';
			if ( isset( $button[ 'buttons' ] ) && count( $button[ 'buttons' ] ) ) {
				foreach ( $button[ 'buttons' ] as $bt ) {
					$this->renderButton( $bt, true );
				}
			}
			$this->_out[] = '</ul>';
			$this->_out[] = '</div>';
			$this->_out[] = '</div>';
		}
		elseif ( !( $list ) ) {
			if ( $rel || $href ) {
				$this->_out[] = "<a href=\"{$href}\" rel=\"{$rel}\" class=\"btn{$class}\"{$target}>";
			}
			else {
				if ( isset( $button[ 'rel' ] ) ) {
					$r = " rel=\"{$button['rel']}\" ";
				}
				$this->_out[] = "<div class=\"btn{$class}\"{$r}{$target}>";
			}
			if ( !( isset( $button[ 'icon' ] ) && $button[ 'icon' ] ) ) {
				$icon = 'cog';
			}
			else {
				$icon = $button[ 'icon' ];
			}
			$this->_out[] = '<i class="icon-' . $icon . '"></i>' . $label;
			if ( $rel || $href ) {
				$this->_out[] = '</a>';
			}
			else {
				$this->_out[] = '</div>';
			}
		}
		else {
			if ( $button[ 'element' ] == 'nav-header' ) {
				$this->_out[] = '<li class="nav-header">' . $button[ 'label' ] . '</li>';
			}
			else {
				$this->_out[] = '<li><a href="' . $href . $target . '" rel="' . $rel . '">';
				if ( !( isset( $button[ 'icon' ] ) && $button[ 'icon' ] ) ) {
					$icon = 'cog';
				}
				else {
					$icon = $button[ 'icon' ];
				}
				$this->_out[] = '<i class="icon-' . $icon . '"></i>' . $label;
				$this->_out[] = '</a></li>';
			}
		}
	}
}
