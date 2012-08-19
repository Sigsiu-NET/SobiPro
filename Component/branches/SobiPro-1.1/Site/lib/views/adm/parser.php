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

	public function __construct( $table = true )
	{
		$this->table = $table;
	}

	public function parse( $data )
	{
		static $deep = 0;
		$deep++;
		$this->openElement( $data );
		$this->parseElement( $data );
		if ( is_array( $data[ 'content' ] ) && count( $data[ 'content' ] ) && !( is_string( $data[ 'content' ] ) ) ) {
			foreach ( $data[ 'content' ] as $element ) {
				$this->parse( $element );
			}
		}
		$this->closeElement( $data );
		$nesting = null;
//		for ( $i = 0; $i < $deep; $i++ ) {
//			$nesting .= "\t";
//		}
		echo implode( "\n" . $nesting, $this->_out );
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
				if ( isset( $element[ 'label' ] ) ) {
					$this->_out[ ] = "<label class=\"control-label\" for=\"{$element[ 'id' ]}\">{$element[ 'label' ]}</label>\n";
				}
				if ( $this->table ) {
					$this->_out[ ] = '</td>';
				}
				$class = 'controls';
				if ( count( $element[ 'adds' ][ 'before' ] ) ) {
					$class .= ' input-prepend';
				}
				if ( count( $element[ 'adds' ][ 'after' ] ) ) {
					$class .= ' input-append';
				}
				if ( $this->table ) {
					$this->_out[ ] = '<td>';
				}
				$this->_out[ ] = "<div class=\"{$class}\">\n";
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
				$this->_out[ ] = '<label class="control-label">' . $data[ 'label' ] . '</label>';
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
				$this->proceedCell( $data );
				break;
			case 'header':
				$this->_out[ ] = '<div>';
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
		}
	}

	public function proceedCell( $cell )
	{
		$this->_out[ ] = "<{$this->thTd}>";
		switch ( $cell[ 'attributes' ][ 'type' ] ) {
			case 'text':
				$this->_out[ ] = $cell[ 'attributes' ][ 'label' ];
				break;
			case 'ordering':
				$this->_out[ ] = $cell[ 'attributes' ][ 'label' ];
				$this->_out[ ] = '<div class="pull-right">';
				$this->_out[ ] = '<button class="btn sp-mini-bt">';
				$this->_out[ ] = '<i class="icon-check"></i>';
				$this->_out[ ] = '</button>';
				$this->_out[ ] = '</div>';
				break;
			case 'checkbox':
				if ( isset( $cell[ 'attributes' ][ 'rel' ] ) && $cell[ 'attributes' ][ 'rel' ] ) {
					$this->_out[ ] = '<input type="checkbox" name="spToggle" value="1" rel="'.$cell[ 'attributes' ][ 'rel' ].'">';
				}
				else {
					$this->_out[ ] = '<input type="checkbox" name="'.$cell[ 'attributes' ][ 'name' ].'[]" value="1">';
				}
				break;
		}
		$this->_out[ ] = "</{$this->thTd}>";
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
