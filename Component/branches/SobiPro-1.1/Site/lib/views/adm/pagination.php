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
 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date:$
 * $Revision:$
 * $Author:$
 */
/**
 * @author Sigrid Suski
 * @version 1.1
 * @created 04-Mar-2009 03:05:59 PM
 * @updated Sat, Dec 1, 2012 13:15:30
 * @deprecated
 */
final class SPPagination extends SPObject
{
	/** @var int */
	protected $limit = 0;
	/** @var int */
	protected $count = 0;
	/** @var int */
	protected $current = 0;
	/** @var string */
	protected $set = null;
	/** @var string */
	protected $class = null;
	/** @var array */
	private $_content = array();
	/** @var array */
	protected $url = array();
	/** @var string */
	protected $inputbox = null;
    /** @var string */
    protected $type = null;

	/**
	 */
	public function display( $return = false )
	{
		$pn = null;
		$pages = $this->limit > 0 ? ceil( $this->count / $this->limit ) : 0;
		/** if we have any pages */
		if ( $pages > 1 ) {
			$this->_content[ ] = "<div class=\"{$this->class}\">";
			$this->_content[ ] = "<ul>";
			if( $this->inputbox == 'left' ) {
				$this->inputbox();
			}
            $type = $this->type;
			if ( $this->current == 1 ) {
				$this->cell( Sobi::Txt( 'PN.START'.$type ), '#', 'disabled' );
                //$this->cell( Sobi::Txt( 'PN.START' ), '#', 'disabled' );
				$this->cell( Sobi::Txt( 'PN.PREVIOUS'.$type ), '#', 'disabled' );
			}
			else {
				$this->url[ $this->set ] = 1;
				$this->cell( Sobi::Txt( 'PN.START'.$type ), Sobi::Url( $this->url ) );
				$this->url[ $this->set ] = $this->current - 1;
				$this->cell( Sobi::Txt( 'PN.PREVIOUS'.$type ), Sobi::Url( $this->url ) );
			}
			for ( $page = 1; $page <= $pages; $page++ ) {
				if ( $pages > 1000 && ( $page % 1000 != 0 ) ) {
					continue;
				}
				elseif ( $pages > 100 && ( $page % 100 != 0 ) ) {
					continue;
				}
				elseif ( $pages > 15 && ( $page % 5 != 0 ) ) {
					continue;
				}
				$this->url[ $this->set ] = $page;
				if ( $page == $this->current ) {
					$this->cell( $page, Sobi::Url( $this->url ), 'active' );
				}
				else {
					$this->cell( $page, Sobi::Url( $this->url ) );
				}
			}
			if ( $this->current == $pages ) {
				$this->cell( Sobi::Txt( 'PN.NEXT'.$type ), '#', 'disabled' );
				$this->cell( Sobi::Txt( 'PN.END'.$type ), '#', 'disabled' );
			}
			else {
				$this->url[ $this->set ] = $this->current + 1;
				$this->cell( Sobi::Txt( 'PN.NEXT'.$type ), Sobi::Url( $this->url ) );
				$this->url[ $this->set ] = $pages;
				$this->cell( Sobi::Txt( 'PN.END'.$type ), Sobi::Url( $this->url ) );
			}
            if( $this->inputbox == 'right' ) {
                $this->inputbox();
            }
            $this->_content[ ] = "</ul>";
			// close overall container
			$this->_content[ ] = "</div>";
		}
		$pn = implode( "\n", $this->_content );
		if ( $return ) {
			return $pn;
		}
		else {
			echo $pn;
		}
	}

	private function cell( $text, $href = '#', $class = null )
	{
		$class = $class ? " class=\"{$class}\"" : null;
		if( $href ) {
			$this->_content[ ] = "<li {$class}><a href=\"{$href}\">{$text}</a></li>";
		}
	}

	private function inputbox()
	{
		$this->_content[ ] = "<li class=\"pagination-page\"><div class=\"input-append pagination-page\">
		  <input class=\"spSubmit\" type=\"text\" name=\"{$this->set}\" value=\"{$this->current}\">
		  <button class=\"btn\" type=\"submit\">".Sobi::Txt( 'PN.GO' )."</button>
		</div></li>";
	}
}
