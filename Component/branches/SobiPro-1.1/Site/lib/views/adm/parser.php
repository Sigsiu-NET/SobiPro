<?php
/**
 * @version: $Id$
 * @package: SobiPro
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date:$
 * $Revision:$
 * $Author:$
 */
class SPTplParser
{
	protected $tabsContentOpen = false;
	protected $activeTab = false;
	protected $table = true;
	protected $loopTable = true;
	protected $thTd = 'th';
	protected $_out = array();
	protected $loopOpen = false;
	protected $_tickerIcons = array(
		0 => 'remove',
		1 => 'ok',
		-1 => 'stop',
		-2 => 'pause'
	);
	protected $_checkedOutIcon = 'lock';
	static $newLine = "\n";
	protected $html = array( 'div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'a', 'button', 'url', 'img', 'table', 'ul', 'li', 'pre' );
	protected $internalAttributes = array( 'condition' );


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
			if ( isset( $data[ 'type' ] ) ) {
				$this->closeElement( $data );
			}
		}
		echo implode( "", $this->_out );
		$this->_out = array();
	}

	protected function parseElement( $element )
	{
		switch ( $element[ 'type' ] ) {
			case 'field':
				if ( isset( $element[ 'attributes' ] ) && $this->istSet( $element[ 'attributes' ], 'stand-alone', 'true' ) ) {
					$this->_out[ ] = $element[ 'content' ];
					break;
				}
				if ( $this->table ) {
					$this->_out[ ] = '<tr>';
					$this->_out[ ] = '<td>';
				}
				$this->_out[ ] = '<div class="control-group">';
				if ( isset( $element[ 'label' ] ) && strlen( $element[ 'label' ] ) ) {
					if ( !( isset( $element[ 'id' ] ) ) ) {
						$element[ 'id' ] = SPLang::nid( $element[ 'label' ] );
					}
					if ( isset( $element[ 'help-text' ] ) && $element[ 'help-text' ] ) {
						$element[ 'label' ] = '<a href="#" rel="popover" data-title="' . htmlspecialchars( $element[ 'label' ], ENT_COMPAT ) . '" data-content="' . htmlspecialchars( $element[ 'help-text' ], ENT_COMPAT ) . '">' . $element[ 'label' ] . '</a>';
					}
					$this->_out[ ] = "<label class=\"control-label\" for=\"{$element[ 'id' ]}\">{$element[ 'label' ]}</label>\n";
				}
				if ( $this->table ) {
					$this->_out[ ] = '</td>';
				}
				$this->_out[ ] = "<div class=\"controls\">\n";
				$class = null;

				if ( $element[ 'args' ][ 'type' ] == 'output' ) {
					$this->_out[ ] = "<div class=\"spOutput\">";
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
						$this->_out[ ] = "<span class=\"{$outclass}\"{$id}>\n";
					}
					else {
						$this->_out[ ] = "<span{$id}>\n";
					}
				}
				if ( count( $element[ 'adds' ][ 'before' ] ) ) {
					$class .= ' input-prepend';
				}
				if ( count( $element[ 'adds' ][ 'after' ] ) ) {
					$class .= ' input-append';
				}
				if ( $class ) {
					$this->_out[ ] = "<div class=\"{$class}\">\n";
				}
				if ( $this->table ) {
					$this->_out[ ] = '<td>';
				}
				if ( count( $element[ 'adds' ][ 'before' ] ) ) {
					foreach ( $element[ 'adds' ][ 'before' ] as $o ) {
						$this->_out[ ] = "<span class=\"add-on\">{$o}</span>";
					}
				}
				/** here is the right content output */
				$this->_out[ ] = $element[ 'content' ];
				if ( count( $element[ 'adds' ][ 'after' ] ) ) {
					foreach ( $element[ 'adds' ][ 'after' ] as $o ) {
						$this->_out[ ] = "<span class=\"add-on\">{$o}</span>";
					}
				}
				if ( $element[ 'args' ][ 'type' ] == 'output' ) {
					$this->_out[ ] = "</div></span>\n";
				}
				if ( $class ) {
					$this->_out[ ] = "</div>\n";
				}
				if ( $this->table ) {
					$this->_out[ ] = '</td>';
					$this->_out[ ] = '</tr>';
				}
				$this->_out[ ] = '</div>';
				$this->_out[ ] = '</div>';
				break;
			case 'header':
				$this->_out[ ] = '<div class="span6 spScreenSubHead spicon-48-' . $element[ 'attributes' ][ 'icon' ] . '">';
				$this->_out[ ] = $element[ 'attributes' ][ 'label' ];
				$this->_out[ ] = '</div>';
				break;
			case 'url':
				if ( isset( $element[ 'attributes' ][ 'image' ] ) ) {
					$this->_out[ ] = "<img src=\"{$element[ 'attributes' ][ 'image' ]}\" alt=\"{$element[ 'attributes' ][ 'label' ]}\" />";
					$this->closeElement( $element );
					$this->openElement( $element );
				}
				$this->_out[ ] = $element[ 'content' ];
				break;
			case 'text':
			case 'menu':
			case 'toolbar':
				$this->_out[ ] = $element[ 'content' ];
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
					$this->_out[ ] = '<div class="tab-pane active" id="' . $data[ 'id' ] . '">';
					$this->activeTab = true;
				}
				else {
					$this->_out[ ] = '<div class="tab-pane" id="' . $data[ 'id' ] . '">';
				}
				break;
			case 'fieldset':
				if ( $this->table ) {
					$this->_out[ ] = '<table class="table table-striped table-bordered table-condensed">';
					$this->_out[ ] = '<tbody>';
				}
				$formType = isset( $data[ 'attributes' ][ 'type' ] ) && $data[ 'attributes' ][ 'type' ] ? $data[ 'attributes' ][ 'type' ] : 'horizontal';
				$this->_out[ ] = '<fieldset class="form-' . $formType . ' control-group">';
				if ( isset( $data[ 'label' ] ) && $data[ 'label' ] ) {
					$this->_out[ ] = '<div class="control-group spFieldGroup"><label class="control-label">' . $data[ 'label' ] . '</label></div>';
				}
				break;
			case 'head':
				$this->thTd = 'th';
				$this->_out[ ] = '<thead>';
				$this->_out[ ] = '<tr>';
				break;
			case 'cell':
				$this->proceedCell( $data, $this->loopTable ? $this->thTd : 'div' );
				break;
			case 'header':
				$this->_out[ ] = '<div class="SPAdmNavBar">';
				break;
			case 'loop':
				$this->loopTable = true;
				if ( $this->istSet( $data[ 'attributes' ], 'table' ) ) {
					$this->loopTable = $data[ 'attributes' ][ 'table' ] == 'false' ? false : true;
				}
				if ( $this->loopTable ) {
					$this->_out[ ] = '<tbody>';
				}
				$this->loopOpen = true;
				break;
			case 'loop-row':
				if ( $this->loopTable ) {
					$this->_out[ ] = '<tr>';
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
				$this->_out[ ] = $data[ 'content' ];
				break;
			case 'table':
				$data[ 'attributes' ][ 'class' ] = 'table table-striped';
			default:
				if ( in_array( $data[ 'type' ], $this->html ) ) {
					$tag = $data[ 'type' ];
					if ( $data[ 'type' ] == 'url' ) {
						$tag = 'a';
					}
					$a = null;
					if ( count( $data[ 'attributes' ] ) ) {
						foreach ( $data[ 'attributes' ] as $att => $value ) {
							if ( in_array( $att, array( 'type', 'image', 'label' ) ) ) {
								continue;
							}
							$a .= " {$att}=\"{$value}\"";
						}
					}
					$this->_out[ ] = "<{$tag}{$a}>";
				}
				break;
		}
	}

	protected function tooltip( $data )
	{
		if ( !( isset( $data[ 'attributes' ][ 'href' ] ) ) ) {
			$data[ 'attributes' ][ 'href' ] = '#';
		}
		$data[ 'attributes' ][ 'rel' ] = $data[ 'type' ];
		if ( $data[ 'type' ] == 'tooltip' ) {
			$data[ 'attributes' ][ 'title' ] = htmlspecialchars( $data[ 'content' ], ENT_COMPAT );
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
			$el .= ">{$data[ 'title' ]}</a>";
		}
		$this->_out[ ] = $el;
	}

	protected function message( $data )
	{
		$class = isset( $data[ 'attributes' ][ 'class' ] ) && $data[ 'attributes' ][ 'class' ] ? $data[ 'attributes' ][ 'class' ] : null;
		if ( $this->istSet( $data[ 'attributes' ], 'label' ) ) {
			$type = isset( $data[ 'attributes' ][ 'type' ] ) && $data[ 'attributes' ][ 'type' ] ? 'alert-' . $data[ 'attributes' ][ 'type' ] : null;
            $icon = null;
            if (isset( $data[ 'attributes' ][ 'icon' ] ) && ($data[ 'attributes' ][ 'icon' ] == 'true')) {
                if ($type == 'alert-success') {
                    $icon = 'icon-thumbs-up';
                }
                else {
                    $icon = 'icon-thumbs-down';
                }
                $icon = "<i class=\"{$icon}\"></i> ";
            }
			$this->_out[ ] = "<div class=\"alert {$type} {$class}\">";
			if ( isset( $data[ 'attributes' ][ 'dismiss-button' ] ) && $data[ 'attributes' ][ 'dismiss-button' ] == 'true' ) {
				$this->_out[ ] = '<button type="button" class="close" data-dismiss="alert">×</button>';
			}
			$this->_out[ ] = $icon . $data[ 'attributes' ][ 'label' ];
			$this->_out[ ] = '</div>';
		}
		else {
			$attr = array();
			if ( count( $data[ 'attributes' ] ) ) {
				foreach ( $data[ 'attributes' ] as $n => $v ) {
					$attr[ ] = "{$n}=\"{$v}\"";
				}
			}
			$attr = implode( ' ', $attr );
			$messages = SPFactory::message()->getMessages();
			if ( count( $messages ) ) {
				foreach ( $messages as $type => $texts ) {
					if ( count( $texts ) ) {
						$this->_out[ ] = "<div class=\"alert alert-{$type} spSystemAlert\">";
						$this->_out[ ] = '<button type="button" class="close" data-dismiss="alert">×</button>';
						foreach ( $texts as $text ) {
							$this->_out[ ] = "<div>{$text}</div>";
						}
						$this->_out[ ] = '</div>';
					}
				}
			}
			$this->_out[ ] = "<div {$attr}></div>";
		}
	}

	public function closeElement( $data )
	{
		switch ( $data[ 'type' ] ) {
			case 'tabs':
				$this->tabsContentOpen = false;
			case 'tab':
				$this->_out[ ] = '</div>';
				break;
			case 'fieldset':
				if ( $this->table ) {
					$this->_out[ ] = '</tbody>';
					$this->_out[ ] = '</table>';
				}
				$this->_out[ ] = '</fieldset>';
				break;
			case 'head':
				$this->thTd = 'td';
				$this->_out[ ] = '</tr>';
				$this->_out[ ] = '</thead>';
				break;
			case 'header':
				$this->_out[ ] = '</div>';
				break;
			case 'loop':
				if ( $this->loopTable ) {
					$this->_out[ ] = '</tbody>';
				}
				$this->loopTable = true;
				$this->loopOpen = false;
				break;
			case 'loop-row':
				if ( $this->loopTable ) {
					$this->_out[ ] = '</tr>';
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
					$this->_out[ ] = "</{$tag}>";
				}
				break;


		}
	}

	public function proceedCell( $cell, $span = null )
	{
		if ( $cell[ 'type' ] == 'text' ) {
			return $this->parseElement( $cell );
		}
		if ( $cell[ 'type' ] == 'tooltip' || $cell[ 'type' ] == 'popover' ) {
			return $this->tooltip( $cell );
		}
		if ( isset( $cell[ 'attributes' ][ 'class' ] ) ) {
			$c = 'SpCell' . ucfirst( $cell[ 'attributes' ][ 'class' ] );
			$this->_out[ ] = "\n<{$span} class=\"{$c}\">\n";
		}
		else {
			$this->_out[ ] = "\n<{$span}>\n";
		}
		$type = isset( $cell[ 'attributes' ][ 'type' ] ) ? $cell[ 'attributes' ][ 'type' ] : 'text';
		switch ( $type ) {
			/** no break here - continue */
			case 'text':
			case 'link':
				if ( $type == 'link' ) {
					$class = null;
					if ( isset( $cell[ 'attributes' ][ 'class' ] ) && $cell[ 'attributes' ][ 'class' ] ) {
						$class = "class=\"{$cell[ 'attributes' ][ 'class' ]}\" ";
					}
					$this->_out[ ] = "<a href=\"{$cell['link']}\"{$class} >";
				}
				if ( $this->istSet( $cell[ 'attributes' ], 'label' ) ) {
					$this->_out[ ] = $cell[ 'attributes' ][ 'label' ];
				}
				if ( $this->istSet( $cell, 'label' ) ) {
					$this->_out[ ] = $cell[ 'label' ];
				}
				if ( isset( $cell[ 'content' ][ 'element' ] ) && $cell[ 'content' ][ 'element' ] == 'button' ) {
					$this->renderButton( $cell[ 'content' ] );
				}
				else {
					$this->_out[ ] = $cell[ 'content' ];
				}
				/** no break here - continue */
				if ( $type == 'link' ) {
					$this->_out[ ] = "</a>";
				}
				break;
			case 'ordering':
				if ( isset( $cell[ 'attributes' ][ 'label' ] ) ) {
					$this->_out[ ] = $cell[ 'attributes' ][ 'label' ];
					$this->_out[ ] = '<button class="btn sp-mini-bt" name="spReorder" rel="' . $cell[ 'attributes' ][ 'rel' ] . '">';
					$this->_out[ ] = '<i class="icon-reorder"></i>';
					$this->_out[ ] = '</button>';
				}
				else {
					$this->_out[ ] = SPHtml_Input::text( $cell[ 'attributes' ][ 'name' ], $cell[ 'content' ], array( 'class' => 'input-mini sp-input-micro spSubmit' ) );
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
		$this->_out[ ] = "\n</{$span}>\n";
	}

	public function ticker( $cell )
	{
		$index = $cell[ 'content' ];
		/** is expired ? */
		if ( isset( $cell[ 'attributes' ][ 'valid-until' ] ) && $cell[ 'attributes' ][ 'valid-until' ] && strtotime( $cell[ 'attributes' ][ 'valid-until' ] ) < time() && strtotime( $cell[ 'attributes' ][ 'valid-until' ] ) > 0 ) {
			$index = -1;
			$txt = Sobi::Txt( 'ROW_EXPIRED', $cell[ 'attributes' ][ 'valid-until' ] );
			$this->_out[ ] = '<a href="#" rel="tooltip" data-original-title="' . $txt . '" class="expired">';
		}
		/** is pending ? */
		elseif ( ( isset( $cell[ 'attributes' ][ 'valid-since' ] ) && $cell[ 'attributes' ][ 'valid-since' ] && strtotime( $cell[ 'attributes' ][ 'valid-since' ] ) > time() ) && $index == 1 ) {
			$index = -2;
			$txt = Sobi::Txt( 'ROW_PENDING', $cell[ 'attributes' ][ 'valid-since' ] );
			$this->_out[ ] = '<a href="#" rel="tooltip" data-original-title="' . $txt . '" class="pending">';
		}
		elseif ( isset( $cell[ 'link' ] ) && $cell[ 'link' ] ) {
			$this->_out[ ] = "<a href=\"{$cell['link']}\" >";
		}
		$icons = array();
		if ( isset( $cell[ 'attributes' ][ 'icons' ] ) && $cell[ 'attributes' ][ 'icons' ] ) {
			$icons = json_decode( str_replace( "'", '"', $cell[ 'attributes' ][ 'icons' ] ), true );
		}
		if ( !( count( $icons ) ) ) {
			$icons = $this->_tickerIcons;
		}
		$icon = ( isset( $icons[ $index ] ) && $icons[ $index ] ) ? $icons[ $index ] : $this->_tickerIcons[ $index ];
		$this->_out[ ] = '<i class="icon-' . $icon . '"></i>';
		if ( isset( $cell[ 'link' ] ) && $cell[ 'link' ] ) {
			$this->_out[ ] = "</a>";
			return $cell;
		}
		return $cell;
	}

	public function checkbox( $cell )
	{
		/** First let's check if it is not checked out */
		if ( isset( $cell[ 'attributes' ][ 'checked-out-by' ] ) && isset( $cell[ 'attributes' ][ 'checked-out-time' ] ) && $cell[ 'attributes' ][ 'checked-out-by' ] && $cell[ 'attributes' ][ 'checked-out-by' ] != Sobi::My( 'id' ) && strtotime( $cell[ 'attributes' ][ 'checked-out-time' ] ) > time() ) {
			if ( isset( $cell[ 'attributes' ][ 'checked-out-ico' ] ) && $cell[ 'attributes' ][ 'checked-out-ico' ] ) {
				$icon = $cell[ 'attributes' ][ 'checked-out-ico' ];
			}
			else {
				$icon = $this->_checkedOutIcon;
			}
			$user = SPUser::getInstance( $cell[ 'attributes' ][ 'checked-out-by' ] );
			$txt = Sobi::Txt( 'CHECKED_OUT', $user->get( 'name' ), $cell[ 'attributes' ][ 'checked-out-time' ] );
			$this->_out[ ] = '<a href="#" rel="tooltip" data-original-title="' . $txt . '" class="checkedout">';
			$this->_out[ ] = '<i class="icon-' . $icon . '"></i>';
			$this->_out[ ] = '</a>';
			return $cell;
		}
		if ( isset( $cell[ 'attributes' ][ 'rel' ] ) && $cell[ 'attributes' ][ 'rel' ] ) {
			$this->_out[ ] = '<input type="checkbox" name="spToggle" value="1" rel="' . $cell[ 'attributes' ][ 'rel' ] . '">';
			return $cell;
		}
		else {
			$this->_out[ ] = '<input type="checkbox" name="' . $cell[ 'attributes' ][ 'name' ] . '[]" value="' . $cell[ 'content' ] . '">';
			return $cell;
		}
	}

	public function tabsHeader( $element )
	{
		$this->tabsContentOpen = true;
		$this->activeTab = false;
		$this->_out[ ] = "\n" . '<ul class="nav nav-tabs">';
		$active = false;
		foreach ( $element[ 'content' ] as $tab ) {
			if ( !( $active ) ) {
				$active = true;
				$this->_out[ ] = "<li class=\"active\" ><a href=\"#{$tab[ 'id' ]}\">{$tab[ 'label' ]}</a></li>\n";
			}
			else {
				$this->_out[ ] = "<li><a href=\"#{$tab[ 'id' ]}\">{$tab[ 'label' ]}</a> </li>\n";
			}
		}
		$this->_out[ ] = '</ul>';
		$this->_out[ ] = "\n" . '<div class="tab-content">';
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
		$target = ( isset( $button[ 'target' ] ) && $button[ 'target' ] ) ? " target=\"{$button[ 'target' ]}\"" : null;
		if ( isset( $button[ 'buttons' ] ) && count( $button[ 'buttons' ] ) ) {
			$this->_out[ ] = '<div class="btn-group">';
			$this->_out[ ] = "<a href=\"{$href}\" class=\"btn{$class}\"{$target} rel=\"{$rel}\">";
			if ( !( isset( $button[ 'ico' ] ) && $button[ 'ico' ] ) ) {
				$icon = 'cog';
			}
			else {
				$icon = $button[ 'ico' ];
			}
			$this->_out[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;&nbsp;' . $label;
			$this->_out[ ] = '</a>';
			$this->_out[ ] = '<button class="btn dropdown-toggle" data-toggle="dropdown"><span class="icon-caret-down"></span>&nbsp;</button>';
			$this->_out[ ] = '<div class="dropdown-menu" id="' . SPLang::nid( $button[ 'task' ] ) . '">';
			$this->_out[ ] = '<ul class="nav nav-stacked SpDropDownBt">';
			foreach ( $button[ 'buttons' ] as $bt ) {
				$this->renderButton( $bt, true );
			}
			$this->_out[ ] = '</ul>';
			$this->_out[ ] = '</div>';
			$this->_out[ ] = '</div>';
		}
		elseif ( !( $list ) ) {
			if ( $rel || $href ) {
				$this->_out[ ] = "<a href=\"{$href}\" rel=\"{$rel}\" class=\"btn{$class}\"{$target}>";
			}
			else {
				if ( isset( $button[ 'rel' ] ) ) {
					$r = "rel=\"{$button[ 'rel' ]}\" ";
				}
				$this->_out[ ] = "<div class=\"btn{$class}\"{$r}{$target}>";
			}
			if ( !( isset( $button[ 'ico' ] ) && $button[ 'ico' ] ) ) {
				$icon = 'cog';
			}
			else {
				$icon = $button[ 'ico' ];
			}
			$this->_out[ ] = '&nbsp;<i class="icon-' . $icon . '"></i>&nbsp;' . $label;
			if ( $rel || $href ) {
				$this->_out[ ] = '</a>';
			}
			else {
				$this->_out[ ] = '</div>';
			}
		}
		else {
			if ( $button[ 'element' ] == 'nav-header' ) {
				$this->_out[ ] = '<li class="nav-header">' . $button[ 'label' ] . '</li>';
			}
			else {
				$this->_out[ ] = '<li><a href="' . $href . $target . '" rel="' . $rel . '">';
				if ( !( isset( $button[ 'ico' ] ) && $button[ 'ico' ] ) ) {
					$icon = 'cog';
				}
				else {
					$icon = $button[ 'ico' ];
				}
				$this->_out[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;&nbsp;' . $label;
				$this->_out[ ] = '</a></li>';
			}
		}
	}
}
