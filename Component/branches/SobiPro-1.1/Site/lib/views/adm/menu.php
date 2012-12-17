<?php
/**
 * @version: $Id: menu.php 1598 2011-07-06 08:52:26Z Radek Suski $
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
 * $Date: 2011-07-06 10:52:26 +0200 (Wed, 06 Jul 2011) $
 * $Revision: 1598 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/helpers/adm/menu.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:41:10 PM
 */
final class SPAdmSiteMenu
{
	private $_sections = array();
	private $_sid = 0;
	private $_view = array();
	private $_task = null;
	private $_open = null;
	private $_custom = array();

	public function __construct( $task = null, $sid = 0 )
	{
		SPFactory::header()
				->addCSSFile( 'menu', true )
				->addJsFile( 'menu', true );
		Sobi::LoadLangFile( 'menu', true, true );
		$this->_task = $task ? $task : SPRequest::task();
		$this->_sid = $sid;
		SPFactory::registry()->set( 'adm_menu', $this );
	}

	public function addSection( $name, $section )
	{
		Sobi::Trigger( 'addSection', 'SPAdmSiteMenu', array( $name, $section ) );
		if ( $name == 'AMN.APPS_HEAD' || $name == 'AMN.APPS_SECTION_HEAD' ) {
			$p = SPFactory::Controller( 'extensions', true );
			$links = $p->appsMenu();
			if ( is_array( $links ) ) {
				$section = array_merge( $section, $links );
			}
		}
		elseif ( $name == 'AMN.APPS_SECTION_TPL' && Sobi::Section() && Sobi::Cfg( 'section.template', 'default' ) ) {
			$p = SPFactory::Controller( 'template', true );
			$this->_custom[ $name ][ 'after' ][ ] = $p->getTemplateTree( Sobi::Cfg( 'section.template', 'default' ) );
		}
		$this->_sections[ $name ] =& $section;
	}

	public function addCustom( $section, $html, $before = false )
	{
		$i = $before ? 'before' : 'after';
		Sobi::Trigger( 'addCustom', 'SPAdmSiteMenu', array( $html, $section ) );
		$this->_custom[ $section ][ $i ][ ] = $html;
	}

	/**
	 * @return string
	 */
	public function display()
	{
		$this->_view[ ] = "\n <!-- Sobi Pro - admin side menu start -->";
		$this->_view[ ] = "\n<div id=\"SPaccordionTabs\" class=\"SPmenuTabs\">";
		$this->_view[ ] = '<div id="SPMenuCtrl">';
		$this->_view[ ] = ' <button class="btn btn-mini btn-sobipro" id="SPMenuCtrlBt" type="button">-</button>';
		$this->_view[ ] = '</div>';
		$media = Sobi::Cfg( 'img_folder_live' );
		$this->_view[ ] = "\n<div class='well well-small'><a href=\"http://www.Sigsiu.NET\" target=\"_blank\" title=\"Sigsiu.NET Software Development\"><img src=\"{$media}/sobipro-menu.png\" alt=\"Sigsiu.NET Software Development\" style=\"border-style:none;\" /></a></div>\n";
		$fs = null;
		if ( count( $this->_sections ) ) {
			if ( $this->_task == 'section.view' ) {
				$this->_task = 'section.entries';
			}
			$this->_view[ ] = '<div class="accordion" id="SpMenu">';
			foreach ( $this->_sections as $section => $list ) {
				$sid = SPLang::nid( $section );
				$in = false;
				if ( !$fs ) {
					$fs = $sid;
				}
				if ( !( $this->_open ) && array_key_exists( $this->_task, $list ) ) {
					$this->_open = $sid;
					$in = ' in';
				}
				if ( $this->_open && $section == $this->_open ) {
					$in = ' in';
				}
				if ( $this->_open && array_key_exists( $this->_task, $list ) ) {
					$in = ' in';
				}
				$this->_view[ ] = '<div class="accordion-group">';
				$this->_view[ ] = '<div class="accordion-heading">';
				$this->_view[ ] = '<a class="accordion-toggle" data-toggle="collapse" data-parent="#SpMenu" href="#' . $sid . '">';
				$this->_view[ ] = Sobi::Txt( $section );
				$this->_view[ ] = '</a>';
				$this->_view[ ] = '</div>';
				$this->_view[ ] = '<div id="' . $sid . '" class="accordion-body collapse' . $in . '">';
				$this->_view[ ] = '<div class="accordion-inner">';
				$this->_view[ ] = $this->section( $list, $section );
				$this->_view[ ] = '</div>';
				$this->_view[ ] = '</div>';
				$this->_view[ ] = '</div>';
			}
			$this->_view[ ] = '</div>';
		}
		$this->_view[ ] = "\n</div>\n";
		$this->_view[ ] = '<div class="brand" style="display: inherit;">© <a href="http://www.sigsiu.net">Sigsiu.NET GmbH</a></div>';
		$this->_view[ ] = "\n<!-- Sobi Pro - admin side menu end -->\n";
		return implode( "\n", $this->_view );
	}

	public function setOpen( $open )
	{
		$this->_open = $open;
	}

	private function section( $section, $tab )
	{
		$v = null;
		if ( isset( $this->_custom[ $tab ][ 'before' ] ) && is_array( $this->_custom[ $tab ][ 'before' ] ) ) {
			foreach ( $this->_custom[ $tab ][ 'before' ] as $html ) {
				$v .= "\n\t\t\t{$html}";
			}
		}
		if ( count( $section ) ) {
			$v .= "\n\t\t\t<ul>";
			foreach ( $section as $pos => $label ) {
				if ( !( SPFactory::user()->can( $pos ) ) ) {
					continue;
				}
				if ( strlen( $label ) < 3 ) {
					$label = str_replace( '.', '_', $pos );
				}
				$label = Sobi::Txt( $label );
				if ( $this->_sid ) {
					$url = Sobi::Url( array( 'task' => $pos, 'pid' => $this->_sid ) );
				}
				else {
					$url = Sobi::Url( array( 'task' => $pos ) );
				}
				if ( SPRequest::task() == $pos ) {
					$v .= "\n\t\t\t\t<li><a href=\"{$url}\" class=\"SPMenuActive\">{$label}</a></li>";
				}
				else {
					$v .= "\n\t\t\t\t<li><a href=\"{$url}\">{$label}</a></li>";
				}
			}
			$v .= "\n\t\t\t</ul>";
		}
		if ( isset( $this->_custom[ $tab ][ 'after' ] ) && is_array( $this->_custom[ $tab ][ 'after' ] ) ) {
			foreach ( $this->_custom[ $tab ][ 'after' ] as $html ) {
				$v .= "\n\t\t\t{$html}";
			}
		}
		return $v;
	}
}
