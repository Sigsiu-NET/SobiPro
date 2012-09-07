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
	protected $thTd = 'th';
	protected $_out = array();
	protected $loopOpen = false;
	protected $_tickerIcons = array( 0 => 'remove', 1 => 'ok' );

	public function __construct( $table = false )
	{
		$this->table = $table;
	}

	public function parse( $data )
	{
		/////SPConfig::debOut($data);
		$this->openElement( $data );
		$this->parseElement( $data );
		if ( is_array( $data[ 'content' ] ) && count( $data[ 'content' ] ) && !( is_string( $data[ 'content' ] ) ) ) {
			foreach ( $data[ 'content' ] as $element ) {
				$this->parse( $element );
			}
		}
		$this->closeElement( $data );
		echo implode( "\n\t", $this->_out );
		$this->_out = array();
	}

	private function parseElement( $element )
	{
		switch ( $element[ 'type' ] ) {
			case 'field':
				if ( $this->table ) {
					$this->_out[ ] = '<tr>';
					$this->_out[ ] = '<td>';
				}
				$this->_out[ ] = '<div class="control-group">';
				if ( isset( $element[ 'label' ] ) && strlen( $element[ 'label' ] ) ) {
					$this->_out[ ] = "<label class=\"control-label\" for=\"{$element[ 'id' ]}\">{$element[ 'label' ]}</label>\n";
				}
				if ( $this->table ) {
					$this->_out[ ] = '</td>';
				}
				$this->_out[ ] = "<div class=\"controls\">\n";
				$class = null;

				if ( $element[ 'args' ][ 'type' ] == 'output' ) {
					$this->_out[ ] = "<div class=\"spOutput\">";
					$outclass = $element[ 'args' ][ 'params' ][ 'class' ];
					if ( $outclass ) {
						$this->_out[ ] = "<span class=\"{$outclass}\">\n";
					}
					else {
						$this->_out[ ] = "<span>\n";
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
			default:
//				SPConfig::debOut( $element['type'] );
				break;
		}
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
				$this->_out[ ] = '<fieldset class="form-horizontal control-group">';
				if ( isset( $data[ 'label' ] ) && $data[ 'label' ] ) {
					$this->_out[ ] = '<div class="control-group spFieldGroup"><label class="control-label">' . $data[ 'label' ] . '</label></div>';
				}
				break;
			case 'table':
				$data[ 'attributes' ][ 'class' ] = 'table table-striped';
			case 'div':
			case 'span':
			case 'p':
				$a = null;
				if ( count( $data[ 'attributes' ] ) ) {
					foreach ( $data[ 'attributes' ] as $att => $value ) {
						$a .= " {$att}=\"{$value}\"";
					}
				}
				$this->_out[ ] = "<{$data['type']}{$a}>";
				break;
			case 'head':
				$this->thTd = 'th';
				$this->_out[ ] = '<thead>';
				$this->_out[ ] = '<tr>';
				break;
			case 'cell':
				$this->proceedCell( $data, $this->thTd );
				break;
			case 'header':
				$this->_out[ ] = '<div id="SPAdmNavBar">';
				break;
			case 'loop':
				$this->_out[ ] = '<tbody>';
				$this->loopOpen = true;
				break;
			case 'loop-row':
				$this->_out[ ] = '<tr>';
				break;
			case 'message':
				$attr = array();
				if ( count( $data[ 'attributes' ] ) ) {
					foreach ( $data[ 'attributes' ] as $n => $v ) {
						$attr[ ] = "{$n}=\"{$v}\"";
					}
				}
				$attr = implode( ' ', $attr );
				$this->_out[ ] = "<div {$attr}></div>";
				break;
			default:
//				SPConfig::debOut( $data[ 'type' ] );
				break;
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
			case 'div':
			case 'span':
			case 'p':
			case 'table':
				$this->_out[ ] = "</{$data['type']}>";
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
				$this->_out[ ] = '</tbody>';
				$this->loopOpen = false;
				break;
			case 'loop-row':
				$this->_out[ ] = '</tr>';
				break;

		}
	}

	public function proceedCell( $cell, $span = null )
	{
		if ( isset( $cell[ 'attributes' ][ 'class' ] ) ) {
			$c = 'SpCell' . ucfirst( $cell[ 'attributes' ][ 'class' ] );
			$this->_out[ ] = "\n<{$span} class=\"{$c}\">\n";
		}
		else {
			$this->_out[ ] = "\n<{$span}>\n";
		}
		switch ( $cell[ 'attributes' ][ 'type' ] ) {
			case 'link':
				$this->_out[ ] = "<a href=\"{$cell['link']}\" >";
			/** no break here - continue */
			case 'text':
				if ( isset( $cell[ 'attributes' ][ 'label' ] ) && $cell[ 'attributes' ][ 'label' ] ) {
					$this->_out[ ] = $cell[ 'attributes' ][ 'label' ];
				}
				else {
					$this->_out[ ] = $cell[ 'content' ];
				}
			/** no break here - continue */
			case 'link':
				$this->_out[ ] = "</a>";
				break;
			case 'ordering':
				if ( isset( $cell[ 'attributes' ][ 'label' ] ) ) {
					$this->_out[ ] = $cell[ 'attributes' ][ 'label' ];
					$this->_out[ ] = '<button class="btn sp-mini-bt" name="spReorder" rel="' . $cell[ 'attributes' ][ 'rel' ] . '">';
					$this->_out[ ] = '<i class="icon-check"></i>';
					$this->_out[ ] = '</button>';
				}
				else {
					$this->_out[ ] = SPHtml_Input::text( $cell[ 'attributes' ][ 'name' ], $cell[ 'content' ], array( 'class' => 'input-mini sp-input-micro' ) );
				}
				break;
			case 'checkbox':
				if ( isset( $cell[ 'attributes' ][ 'rel' ] ) && $cell[ 'attributes' ][ 'rel' ] ) {
					$this->_out[ ] = '<input type="checkbox" name="spToggle" value="1" rel="' . $cell[ 'attributes' ][ 'rel' ] . '">';
				}
				else {
					$this->_out[ ] = '<input type="checkbox" name="' . $cell[ 'attributes' ][ 'name' ] . '[]" value="' . $cell[ 'content' ] . '">';
				}
				break;
			case 'ticker':
				if ( isset( $cell[ 'link' ] ) && $cell[ 'link' ] ) {
					$this->_out[ ] = "<a href=\"{$cell['link']}\" >";
				}
				$icons = array();
				if ( isset( $cell[ 'attributes' ][ 'icons' ] ) && $cell[ 'attributes' ][ 'icons' ] ) {
					$icons = json_decode( str_replace( "'", '"', $cell[ 'attributes' ][ 'icons' ] ), true );
				}
				if ( !( count( $icons ) ) ) {
					$icons = $this->_tickerIcons;
				}
				$this->_out[ ] = '<i class="icon-' . $icons[ $cell[ 'content' ] ] . '"></i>';
				if ( isset( $cell[ 'link' ] ) && $cell[ 'link' ] ) {
					$this->_out[ ] = "</a>";
				}
				break;
		}
		if ( isset( $cell[ 'childs' ] ) && count( $cell[ 'childs' ] ) ) {
			foreach ( $cell[ 'childs' ] as $child ) {
				$this->proceedCell( $child, 'div' );
			}
		}
		$this->_out[ ] = "\n</{$span}>\n";
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
}
