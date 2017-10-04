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

class SpAdmToolbar
{
	protected $title = null;
	protected $icon = null;
	protected $class = '';
	protected $buttons = null;
	protected $output = null;
	private $icons = [
		'apply' => 'ok',
		'cancel' => 'ban-circle',
		'exit' => 'ban-circle',
		'help' => 'question',
		'save' => 'share',
		'duplicate' => 'paste',
		'new' => 'plus',
		'delete' => 'trash',
		'actions' => 'share',
		'enable' => 'ok',
		'disable' => 'remove',
		'publish' => 'ok',
		'hide' => 'remove',
		'approve' => 'thumbs-up-alt',
		'revoke' => 'thumbs-down-alt',
		'entry' => 'file-text',
		'category' => 'folder-open',
		'panel' => '',
		'config' => '',
		'acl' => '',
		'extensions.installed' => '',
		'options' => 'eye-open',
		'template.info' => '',
		'selected' => 'check',
		'not-selected' => 'check-empty',
		'rule' => 'user'
	];
	private $labels = [
		'apply' => 'SAVE_ONLY',
		'cancel' => 'CANCEL',
		'exit' => 'EXIT',
		'help' => 'HELP',
		'save' => 'SAVE_EXIT',
		'duplicate' => 'SAVE_AS_COPY',
		'new' => 'ADD_NEW',
		'delete' => 'DELETE',
		'actions' => 'ACTIONS',
		'publish' => 'PUBLISH',
		'hide' => 'UNPUBLISH',
		'enable' => 'ENABLE',
		'disable' => 'DISABLE',
		'approve' => 'APPROVE',
		'revoke' => 'REVOKE',
		'panel' => 'CONTROL_PANEL',
		'config' => 'GLOBAL_CONFIG',
		'acl' => 'ACL',
		'extensions.installed' => 'SAM',
		'options' => 'OPTIONS',
		'template.info' => 'TEMPLATE'
	];
	protected $btClass = 'btn';

//	protected $btClass = 'btn btn-large';

	private function __construct()
	{
	}

	/**
	 * return SpAdmToolbar
	 */
	public static function & getInstance()
	{
		static $toolbar = null;
		if ( !( $toolbar ) ) {
			$toolbar = new self();
		}
		return $toolbar;
	}

	public function setTitle( $arr )
	{
		$this->title = $arr[ 'title' ];
		$this->icon = $arr[ 'icon' ];
		$this->class = $arr[ 'class' ];
	}

	public function addButtons( $arr )
	{
		$this->buttons = $arr;
	}

	public function render( $options = [] )
	{
		if ( !( count( $this->buttons ) ) ) {
			return null;
		}
		$id = isset( $options[ 'id' ] ) ? $options[ 'id' ] : 'SPAdmToolbar';
		$this->output[ ] = '<div id="SpSpinner" class="SobiPro hide" style="position:fixed; top:50%; left:50%; font-size: 35px;"><i class="icon-spinner icon-spin icon-large"></i></div>';
		$this->output[ ] = '<div class="breadcrumb ' . $this->class . '" id="' . $id . '">';
		$this->output[ ] = '<div class="row-fluid">';
//		$this->output[ ] = '<div class="spScreenTitle span5">';
//		$this->output[ ] = '<h4><i class="icon-' . $this->icon . ' icon-large"></i> ' .  $this->title . '</h4>';
//		$this->output[ ] = '</div>';
		$this->output[ ] = '<div class="spIconBar span12">';
		$this->output[ ] = '<div id="SPRightMenuHold" class="spHoldCtrl">';
		$this->output[ ] = '</div>';
		$this->output[ ] = '<div class="nav nav-pills pull-right">';
		$this->output[ ] = '<div class="">';
		foreach ( $this->buttons as $button ) {
			switch ( $button[ 'element' ] ) {
				case 'group':
					$this->output[ ] = '<div class="btn-group">';
					foreach ( $button[ 'buttons' ] as $bt ) {
						$this->renderButton( $bt );
					}
					$this->output[ ] = '</div>';
					break;
				case 'button':
					$this->renderButton( $button );
					break;
				case 'button-legacy':
					$type = $button[ 'type' ] == 'addNew' ? 'new' : $button[ 'type' ];
					$this->output[ ] = '<span id="toolbar-' . strtolower( $type ) . '">';
					$this->renderButton( $button );
					$this->output[ ] = '</span>';
					break;
				case 'divider':
					$this->output[ ] = '<span class="divider"></span>';
					break;
				case 'buttons':
					$icon = ( isset( $button[ 'icon' ] ) && $button[ 'icon' ] ) ? $button[ 'icon' ] : $this->getIcon( $button );
					$label = ( isset( $button[ 'label' ] ) && $button[ 'label' ] ) ? $button[ 'label' ] : $this->getLabel( $button );
					$class = isset( $button[ 'dropdown-class' ] ) ? ' ' . $button[ 'dropdown-class' ] : null;
					$this->output[ ] = '<div class="btn-group">';
					$this->output[ ] = '<a class="' . $this->btClass . ' dropdown-toggle" data-toggle="dropdown">';
					$this->output[ ] = '<i class="icon-' . $icon . '"></i>' . $label;
					$this->output[ ] = '<span class="caret"></span></a>';
					$this->output[ ] = "<div class=\"dropdown-menu{$class}\">";
					$this->output[ ] = '<ul class="nav nav-stacked SpDropDownBt">';
					foreach ( $button[ 'buttons' ] as $bt ) {
						$this->renderButton( $bt, true );
					}
					$this->output[ ] = '</ul>';
					$this->output[ ] = '</div>';
					$this->output[ ] = '</div>';
					break;
			}
		}
		$this->output[ ] = '</div>';
		$this->output[ ] = '</div>';
		$this->output[ ] = '</div>';
		$this->output[ ] = '</div>';
		$this->output[ ] = '</div>';
		return implode( "\n", $this->output );
	}

	private function renderButton( $button, $list = false )
	{
		$rel = null;
		$onclick = null;
		$title = '';
		$class = isset( $button[ 'class' ] ) ? ' ' . $button[ 'class' ] : null;
		if ( isset( $button[ 'type' ] ) && $button[ 'type' ] == 'url' ) {
			$rel = null;
			$href = $this->getLink( $button );
		}
		elseif ( ( !( isset( $button[ 'task' ] ) ) || !( $button[ 'task' ] ) ) ) {
			$href = $this->getLink( $button );
		}
		else {
			$rel = $button[ 'task' ];
			$href = '#';
		}
		if ( !( isset( $button[ 'label' ] ) ) || !( $button[ 'label' ] ) ) {
			$label = $this->getLabel( $button );
		}
		else {
			$label = $button[ 'label' ];
		}
		if ( isset( $button[ 'confirm' ] ) ) {
			$title = ' title="' . Sobi::Txt( $button[ 'confirm' ] ) . '" ';
		}

		if ( $button[ 'element' ] == 'button-legacy' ) {
			$class .= ' legacy';
			$onclick = 'onclick="Joomla.submitform(\'' . $rel . '\');"';
			// damn SqueezeBox - download field license window
			$rel = null;
		}

		$target = ( isset( $button[ 'target' ] ) && $button[ 'target' ] ) ? " target=\"{$button['target']}\"" : null;
		if ( isset( $button[ 'buttons' ] ) && count( $button[ 'buttons' ] ) ) {
			$this->output[ ] = '<div class="btn-group multi">';
			$this->output[ ] = "<a href=\"{$href}\" class=\"{$this->btClass}{$class}\"{$target} rel=\"{$rel}\">";
			if ( !( isset( $button[ 'icon' ] ) && $button[ 'icon' ] ) ) {
				$icon = $this->getIcon( $button, true );
			}
			else {
				$icon = $button[ 'icon' ];
			}
			$this->output[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;' . $label;
			$this->output[ ] = '</a>';
			$this->output[ ] = '<a class="' . $this->btClass . ' dropdown-toggle" data-toggle="dropdown"><span class="icon-caret-down"></span></a>';
			$this->output[ ] = '<div class="dropdown-menu" id="spmenu-' . SPLang::nid( $button[ 'task' ] ) . '">';
			$this->output[ ] = '<ul class="nav nav-stacked SpDropDownBt">';
			foreach ( $button[ 'buttons' ] as $bt ) {
				$this->renderButton( $bt, true );
			}
			$this->output[ ] = '</ul>';
			$this->output[ ] = '</div>';
			$this->output[ ] = '</div>';
		}
		/** Single std button */
		elseif ( !( $list ) ) {
			$this->output[ ] = "<a href=\"{$href}\" rel=\"{$rel}\" class=\"{$this->btClass}{$class}\"{$target}{$onclick}{$title}>";
			if ( !( isset( $button[ 'icon' ] ) && $button[ 'icon' ] ) ) {
				$icon = $this->getIcon( $button );
			}
			else {
				$icon = $button[ 'icon' ];
			}
			$this->output[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;' . $label;
			$this->output[ ] = '</a>';
		}
		/** dropdown */
		else {
			if ( $button[ 'element' ] == 'nav-header' ) {
				$this->output[ ] = '<li class="nav-header">' . $button[ 'label' ] . '</li>';
			}
			else {
				if ($button[ 'type' ] == 'help') {
					$this->output[] = '<li class="divider"></li>';
				}
				$this->output[ ] = '<li><a href="' . $href . '"' . $target . $title . ' rel="' . $rel . '">';
				if ( !( isset( $button[ 'icon' ] ) && $button[ 'icon' ] ) ) {
					$icon = $this->getIcon( $button );
				}
				else {
					$icon = $button[ 'icon' ];
				}
				if ( isset( $button[ 'selected' ] ) ) {
					if ( !( $button[ 'selected' ] ) ) {
						$icon = $this->icons[ 'not-selected' ];
					}
					else {
						$icon = $this->icons[ 'selected' ];
//						$this->output[ ] = '<i class="icon-' . $this->icons[ 'selected' ] . '"></i>&nbsp;&nbsp;';
					}
				}
				if ($icon) {
					$this->output[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;&nbsp;' . $label;
				}
				else {
					$this->output[ ] = $label;
				}
				$this->output[ ] = '</a></li>';
			}
		}
	}

	private function getLink( $button )
	{
		$link = '#';
		if ( isset( $button[ 'type' ] ) ) {
			switch ( $button[ 'type' ] ) {
				case 'help':
					$link = 'https://www.sigsiu.net/help_screen/' . Sobi::Reg( 'help_task', SPRequest::task() );
					break;
				case 'url':
					if ( isset( $button[ 'sid' ] ) && $button[ 'sid' ] == 'true' ) {
						$link = Sobi::Url( [ 'task' => $button[ 'task' ], 'sid' => SPRequest::sid( 'request', SPRequest::int( 'pid' ), true ) ] );
					}
					else {
						$link = Sobi::Url( $button[ 'task' ] ? $button[ 'task' ] : $button[ 'url' ] );
					}
					break;
			}
		}
		return $link;
	}

	private function getIcon( $button, $group = false )
	{
		if ( $button[ 'type' ] == 'url' ) {
			$button[ 'type' ] = $button[ 'task' ];
			return $this->getIcon( $button );
		}
		if ( isset( $this->icons[ $button[ 'type' ] ] ) ) {
			$icon = $this->icons[ $button[ 'type' ] ];
		}
		else {
			$icon = $group ? 'list' : '';
		}
		return $icon;
	}

	private function getLabel( $button )
	{
		if ( $button[ 'type' ] == 'url' ) {
			$button[ 'type' ] = $button[ 'task' ];
			return $this->getLabel( $button );
		}
		if ( isset( $this->labels[ $button[ 'type' ] ] ) ) {
			$label = Sobi::Txt( 'TB.' . $this->labels[ $button[ 'type' ] ] );
		}
		else {
			$label = Sobi::Txt( 'TB.' . $button[ 'type' ] );
		}
		return $label;
	}
}
