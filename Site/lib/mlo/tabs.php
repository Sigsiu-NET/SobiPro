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

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Jan-2009 9:35:15 AM
 * @deprecated
 */
final class SPHtml_Tabs
{
	/**
	 * @var bool
	 */
	var $useCookies = true;
	/**
	 * @var string
	 */
	var $prefix = null;
	/**
	 * @var string
	 */
	var $class = null;

	/**
	 * Set/add CSS class
	 * @deprecated
	 * @param string $class
	 */
	public function setClass ( $class )
	{
		$this->class = ' '.$class;
	}

	/**
	 * Set the ID prefix
	 * @deprecated
	 * @param string $prefix
	 */
	public function setPrefix ( $prefix )
	{
		$this->prefix = $prefix;
	}

	/**
	 * @param bool $useCookies
	 * @param string $cssFile - separate CSS file
	 * @param string $prefix
	 * @deprecated
	 */
	public function __construct( $useCookies = true, $cssFile = 'tabs', $prefix = null )
	{
		$this->useCookies = $useCookies ? 1 : 0;
		$this->prefix = $prefix;
		if( $cssFile ) {
			SPFactory::header()->addCssFile( $cssFile );
		}
		SPFactory::header()->addJsFile( 'tabs' );
	}

	/**
	 * creates a tab pane and creates JS obj
	 * @param string The Tab Pane Name
	 * @param bool $return
	 * @deprecated
	 */
	public function startPane( $id, $return = false )
	{
		$r = null;
		$r .= "<div class=\"tab-page{$this->prefix}{$this->class}\" id=\"{$id}\">";
		$r .= "<script type=\"text/javascript\">\n";
		$r .= "	var SobiTabPane{$this->prefix} = new WebFXTabPane( document.getElementById( \"{$id}\" ), {$this->useCookies} )\n";
		$r .= "</script>\n";
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ), [ &$r ] );
		$this->out( $r, $return );
	}

	/**
	 * Ends Tab Pane
	 * @deprecated
	 * @param bool $return
	 */
	public function endPane( $return = false )
	{
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ) );
		$this->out( '</div>', $return );
	}

	/**
	 * Creates a tab with title text and starts that tabs page
	 * @param $tabText
	 * @param $paneid
	 * @param bool $return
	 * @internal param $tabText - This is what is displayed on the tab
	 * @internal param $paneid - This is the parent pane to build this tab on
	 * @deprecated
	 */
	public function startTab( $tabText, $paneid, $return = false )
	{
		$r = null;
		$r .= "<div class=\"tab-page{$this->prefix}{$this->class}\" id=\"{$paneid}\">";
		$r .= "<h2 class=\"tab{$this->prefix}{$this->class}\">".$tabText."</h2>";
		$r .= "<script type=\"text/javascript\">\n";
		$r .= "  SobiTabPane{$this->prefix}.addTabPage( document.getElementById( \"{$paneid}\" ) );";
		$r .= "</script>";
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ), [ &$r ] );
		$this->out( $r, $return );
	}

	/*
	 * Ends a tab page
	 * @deprecated
	 */
	public function endTab( $return = false )
	{
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ) );
		$this->endPane( $return );
	}

	/**
	 * @param $r
	 * @param bool $return
	 * @return string
	 * @deprecated
	 */
	private function out( $r, $return )
	{
		if( $return ) {
			return $r;
		}
		else {
			echo $r;
		}
	}
}
