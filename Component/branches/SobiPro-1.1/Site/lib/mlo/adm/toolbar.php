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
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date:$
 * $Revision:$
 * $Author:$
 */
class SpAdmToolbar
{
	protected $title = null;
	protected $icon = null;
	protected $buttons = null;
	protected $output = null;
	private $icons = array(
		'apply' => 'ok',
		'cancel' => 'ban-circle',
		'exit' => 'ban-circle',
		'help' => 'question-sign',
		'save' => 'share',
		'duplicate' => 'edit',
		'new' => 'plus-sign',
		'delete' => 'trash',
		'actions' => 'plane',
		'enable' => 'ok',
		'disable' => 'remove',
		'publish' => 'bullhorn',
		'hide' => 'off',
		'approve' => 'thumbs-up',
		'revoke' => 'thumbs-down',
	);
	private $labels = array(
		'apply' => 'SAVE_ONLY',
		'cancel' => 'CANCEL',
		'exit' => 'EXIT',
		'help' => 'HELP',
		'save' => 'SAVE_EXIT',
		'duplicate' => 'SAVE_NEW',
		'new' => 'ADD_NEW',
		'delete' => 'DELETE',
		'actions' => 'ACTIONS',
		'publish' => 'PUBLISH',
		'hide' => 'UNPUBLISH',
		'enable' => 'ENABLE',
		'disable' => 'DISABLE',
		'approve' => 'APPROVE',
		'revoke' => 'REVOKE',
	);
	protected $btClass = 'btn';

//	protected $btClass = 'btn btn-large';

	private function __construct()
	{
		SPFactory::header()->addJsFile( 'adm.interface' );
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
	}

	public function addButtons( $arr )
	{
		$this->buttons = $arr;
	}

	public function render( $options = array() )
	{
		$id = isset( $options[ 'id' ] ) ? $options[ 'id' ] : 'SPAdmToolbar';
		$this->output[ ] = '<div class="breadcrumb" id="' . $id . '">';
		$this->output[ ] = '<div id="SPRightMenuHold">';
		$this->output[ ] = '</div>';
		$this->output[ ] = '<div class="container-fluid">';
        $this->output[ ] = '<div class="row-fluid">';
        $this->output[ ] = '<div class="spicon-48-' . $this->icon . ' spScreenTitle span6">';
		$this->output[ ] = "<h4>{$this->title}</h4>";
		$this->output[ ] = '</div>';
		$this->output[ ] = '<div class="spIconBar span6">';
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
				case 'divider':
					$this->output[ ] = '<span class="divider"></span>';
					break;
				case 'buttons':
					$icon = ( isset( $button[ 'ico' ] ) && $button[ 'ico' ] ) ? $button[ 'ico' ] : $this->getIcon( $button );
					$label = ( isset( $button[ 'label' ] ) && $button[ 'label' ] ) ? $button[ 'label' ] : $this->getLabel( $button );
					$this->output[ ] = '<div class="btn-group">';
					$this->output[ ] = '<button class="' . $this->btClass . ' dropdown-toggle" data-toggle="dropdown">';
					$this->output[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;&nbsp;' . $label;
					$this->output[ ] = '<span class="caret"></span>&nbsp;</button>';
					$this->output[ ] = '<div class="dropdown-menu">';
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
		$this->output[ ] = '</div>';
		return implode( "\n", $this->output );
	}

	private function renderButton( $button, $list = false )
	{
		$rel = null;
		if ( !( isset( $button[ 'task' ] ) ) || !( $button[ 'task' ] ) ) {
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
		$target = ( isset( $button[ 'target' ] ) && $button[ 'target' ] ) ? " target=\"{$button[ 'target' ]}\"" : null;

		if ( count( $button[ 'buttons' ] ) ) {
			$this->output[ ] = '<div class="btn-group">';
			$this->output[ ] = "<a href=\"{$href}\" class=\"{$this->btClass}\"{$target} rel=\"{$rel}\">";
			if ( !( isset( $button[ 'ico' ] ) && $button[ 'ico' ] ) ) {
				$icon = $this->getIcon( $button, true );
			}
			else {
				$icon = $button[ 'ico' ];
			}
			$this->output[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;&nbsp;' . $label;
			$this->output[ ] = '</a>';
			$this->output[ ] = '<button class="' . $this->btClass . ' dropdown-toggle" data-toggle="dropdown"><span class="caret"></span>&nbsp;</button>';
			$this->output[ ] = '<div class="dropdown-menu" id="spmenu-' . SPLang::nid( $button[ 'task' ] ) . '">';
			$this->output[ ] = '<ul class="nav nav-stacked SpDropDownBt">';
			foreach ( $button[ 'buttons' ] as $bt ) {
				$this->renderButton( $bt, true );
			}
			$this->output[ ] = '</ul>';
			$this->output[ ] = '</div>';
			$this->output[ ] = '</div>';
		}
		elseif ( !( $list ) ) {
			$this->output[ ] = "<a href=\"{$href}\" rel=\"{$rel}\" class=\"{$this->btClass}\"{$target}>";
			if ( !( isset( $button[ 'ico' ] ) && $button[ 'ico' ] ) ) {
				$icon = $this->getIcon( $button );
			}
			else {
				$icon = $button[ 'ico' ];
			}
			$this->output[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;&nbsp;' . $label;
			$this->output[ ] = '</a>';
		}
		else {
			if ( $button[ 'element' ] == 'nav-header' ) {
				$this->output[ ] = '<li class="nav-header">' . $button[ 'label' ] . '</li>';
			}
			else {
				$this->output[ ] = '<li><a href="' . $href . $target . '" rel="' . $rel . '">';
				if ( !( isset( $button[ 'ico' ] ) && $button[ 'ico' ] ) ) {
					$icon = $this->getIcon( $button );
				}
				else {
					$icon = $button[ 'ico' ];
				}
				$this->output[ ] = '<i class="icon-' . $icon . '"></i>&nbsp;&nbsp;' . $label;
				$this->output[ ] = '</a></li>';
			}
		}
	}

	private function getLink( $button )
	{
		switch ( $button[ 'type' ] ) {
			case 'help':
				$link = 'http://sobipro.sigsiu.net/help_screen/' . Sobi::Reg( 'help_task', Sobi::Reg( 'task', SPRequest::task() ) );
				break;
		}
		return $link;
	}

	private function getIcon( $button, $group = false )
	{
		if ( isset( $this->icons[ $button[ 'type' ] ] ) ) {
			$icon = $this->icons[ $button[ 'type' ] ];
		}
		else {
			$icon = $group ? 'list' : 'file';
		}
		return $icon;
	}

	private function getLabel( $button )
	{
		if ( isset( $this->labels[ $button[ 'type' ] ] ) ) {
			$label = Sobi::Txt( 'TB.' . $this->labels[ $button[ 'type' ] ] );
		}
		else {
			$label = 'UNDEFINED';
		}
		return $label;
	}
}
