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

	public function __construct( $table = true )
	{
		$this->table = $table;
	}

	public function parse( $data )
	{
		$this->openElement( $data );
		if ( is_array( $data[ 'content' ] ) && count( $data[ 'content' ] ) && !( is_string( $data[ 'content' ] ) ) ) {
			foreach ( $data[ 'content' ] as $element ) {
				$this->parse( $element );
			}
		}
		else {
			$this->parseElement( $data );
		}
		$this->closeElement( $data );
	}

	private function parseElement( $element )
	{
		switch ( $element[ 'type' ] ) {
			case 'field':
				if( $this->table ) {
					echo '<tr>' . "\n";
					echo '<td>' . "\n";
				}
				echo '<div class="control-group">' . "\n";
				echo "<label for=\"{$element[ 'id' ]}\">{$element[ 'label' ]}</label>\n";
				if( $this->table ) {
					echo '</td>' . "\n";
				}
				$class = 'controls';
				if ( count( $element[ 'adds' ][ 'before' ] ) ) {
					$class .= ' input-prepend';
				}
				if ( count( $element[ 'adds' ][ 'after' ] ) ) {
					$class .= ' input-append';
				}
				if( $this->table ) {
					echo '<td>' . "\n";
				}
				echo "<div class=\"{$class}\">\n";
				if ( count( $element[ 'adds' ][ 'before' ] ) ) {
					foreach ( $element[ 'adds' ][ 'before' ] as $o ) {
						echo "<span class=\"add-on\">{$o}</span>";
					}
				}
				echo $element[ 'content' ] . "\n";
				if ( count( $element[ 'adds' ][ 'after' ] ) ) {
					foreach ( $element[ 'adds' ][ 'after' ] as $o ) {
						echo "<span class=\"add-on\">{$o}</span>";
					}
				}
				if( $this->table ) {
					echo '</td>' . "\n";
					echo '</tr>' . "\n";
				}
				echo '</div>' . "\n";
				echo '</div>' . "\n";
				break;
			default:
//				SPConfig::debOut( $element );
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
					echo '<div class="tab-pane active" id="' . $data[ 'id' ] . '">' . "\n";
					$this->activeTab = true;
				}
				else {
					echo '<div class="tab-pane" id="' . $data[ 'id' ] . '">' . "\n";
				}
				break;
			case 'fieldset':
				if( $this->table ) {
					echo '<table class="table table-striped table-bordered table-condensed">' . "\n";
					echo '<tbody>' . "\n";
				}
				echo '<fieldset class="form-horizontal control-group">' . "\n";
				echo '<label class="control-label">' . $data[ 'label' ] . '</label>' . "\n";
				break;
			case 'div':
			case 'span':
			case 'p':
				$a = null;
				if ( count( $data[ 'attributes' ] ) ) {
					foreach ( $data[ 'attributes' ] as $att => $value ) {
						$a .= " {$att}=\"{$value}\"";
					}
				}
				echo "<{$data['type']}{$a}>";
			default:
//
				break;
		}
	}

	public function closeElement( $data )
	{
		switch ( $data[ 'type' ] ) {
			case 'tabs':
				$this->tabsContentOpen = false;
			case 'tab':
				echo '</div>' . "\n";
				break;
			case 'fieldset':
				if( $this->table ) {
					echo '</tbody>' . "\n";
					echo '</table>' . "\n";
				}
				echo '</fieldset>' . "\n";
				break;
			case 'div':
				echo "</{$data['type']}>";
				break;
		}
	}

	public function tabsHeader( $element )
	{
		$this->tabsContentOpen = true;
		$this->activeTab = false;
		echo "\n" . '<ul class="nav nav-tabs">' . "\n";
		$active = false;
		foreach ( $element[ 'content' ] as $tab ) {
			if ( !( $active ) ) {
				$active = true;
				echo "<li class=\"active\" ><a href=\"#{$tab[ 'id' ]}\">{$tab[ 'label' ]}</a></li>\n";
			}
			else {
				echo "<li><a href=\"#{$tab[ 'id' ]}\">{$tab[ 'label' ]}</a> </li>\n";
			}
		}
		echo '</ul>' . "\n";
		echo "\n" . '<div class="tab-content">' . "\n";
	}
}
