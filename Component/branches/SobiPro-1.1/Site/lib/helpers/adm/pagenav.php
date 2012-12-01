<?php
/**
 * @version: $Id: pagenav.php 1636 2011-07-14 15:01:46Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-07-14 17:01:46 +0200 (Thu, 14 Jul 2011) $
 * $Revision: 1636 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/helpers/adm/pagenav.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Sigrid Suski
 * @version 1.0
 * @created 04-Mar-2009 03:05:59 PM
 * @deprecated
 */
final class SPAdmPageNav
{
	/**
	 * @var int
	 */
	private $limit = 0;
	/**
	 * @var int
	 */
	private $count = 0;
	/**
	 * @var int
	 */
	private $current = 0;
	/**
	 * @var string
	 */
	private $func = null;
	/**
	 * @var string
	 */
	private $boxFunc = null;
	/**
	 * @var string
	 */
	private $box = null;
	/**
	 * @var array
	 */
	private $limits = array( 5, 10, 15, 25, 50, 100, 0 );

	/**
	 * @param int $limit
	 * @param int $count
	 * @param int $current
	 * @param string $func
	 * @param array $limits
	 * @deprecated
	 */
	public function __construct( $limit, $count, $current, $func, $box, $boxFunc, $limits = array( 5, 10, 15, 25, 50 ) )
	{
		$this->limit 	= $limit;
		$this->count 	= $count;
		$this->current 	= $current ? $current : 1;
		$this->limits 	= is_array( $limits ) && count( $limits ) ? $limits : $this->limits;
		$this->func		= $func;
		$this->boxFunc	= $boxFunc;
		$this->box		= $box;
		SPLoader::loadClass( 'mlo.input' );
	}

	/**
	 * @deprecated
	 */
	public function display( $return = false )
	{
		$pn 	= null;
		$pages 	= $this->limit > 0 ? ceil( $this->count / $this->limit ) : 0;
		$sid 	= SPRequest::sid() ? SPRequest::sid() : Sobi::Section();
		$pn .= '<div style="text-align:center;"><div class="pagination">';
		$pn .= '<div class="limit">';
		$pn .= Sobi::Txt( 'PN.DISPLAY' );
		$box = array();
		foreach ( $this->limits as $v ) {
			if( $v ) {
				$box[ $v ] = $v;
			}
			else {
				$box[ -1 ] = Sobi::Txt( 'PN.ALL' );
			}
		}
		$pn .= SPHtml_Input::select( $this->box, $box, $this->limit, false, array( 'onchange' => "{$this->boxFunc}( {$sid} )" ) );
		$pn .= '</div>';
		if( $pages > 1 ) {
			if( $this->current == 1 ) {
				$pn .= '<div class="button2-right off"><div class="start"><span>';
				$pn .= Sobi::Txt( 'PN.START' );
				$pn .= '</span></div></div>';
				$pn .= '<div class="button2-right off"><div class="prev"><span>';
				$pn .= Sobi::Txt( 'PN.PREVIOUS' );
				$pn .= '</span></div></div>';
			}
			else {
				$link = " onclick=\"{$this->func}( 1, {$sid} )\" ";
				$txt = Sobi::Txt( 'PN.START' );
				$pn .= "<div class=\"button2-right\"><div class=\"start\"><a href=\"#\"{$link} title=\"{$txt}\">{$txt}</a></div></div>";
				$prevpage = $this->current - 1;
				$txt = Sobi::Txt( 'PN.PREVIOUS' );
				$link = " onclick=\"{$this->func}( {$prevpage}, {$sid} )\" ";
				$pn .= "<div class=\"button2-right\"><div class=\"start\"><a href=\"#\"{$link} title=\"{$txt}\">{$txt}</a></div></div>";
			}
			$pn .= '<div class="button2-left"><div class="page">';
			for ( $page = 1; $page <= $pages; $page++ ) {
				if( $pages > 1000 && ( $page%1000 != 0 ) ) {
					continue;
				}
				elseif( $pages > 100 && ( $page%100 != 0 ) ) {
					continue;
				}
				elseif( $pages > 20 && ( $page%5 != 0 ) ) {
					continue;
				}
				$link = " onclick=\"{$this->func}( {$page}, {$sid} )\" ";
				if ( $page == $this->current ) {
					$pn .= '<span>'.$page.'</span>';
				}
				else {
					$pn .= "<a href=\"#\"{$link}\" title=\"{$page}\">{$page}</a>";
				}
			}
			$pn .= '</div></div>';
			if ( $this->current == $pages ) {
				$pn .= '<div class="button2-left off"><div class="next"><span>';
				$pn .= Sobi::Txt( 'PN.NEXT' );
				$pn .= '</span></div></div>';
				$pn .= '<div class="button2-left off"><div class="end"><span>';
				$pn .= Sobi::Txt( 'PN.END' );
				$pn .= '</span></div></div>';
			}
			else {
				$nextpage = $this->current + 1;
				$link = " onclick=\"{$this->func}( {$nextpage}, {$sid} )\" ";
				$txt = Sobi::Txt( 'PN.NEXT' );
				$pn .= "<div class=\"button2-left\"><div class=\"next\"><a href=\"#\"{$link}title=\"{$txt}\">{$txt}</a></div></div>";
				$link = " onclick=\"{$this->func}( {$pages}, {$sid} )\" ";
				$txt = Sobi::Txt( 'PN.END' );
				$pn .= "<div class=\"button2-left\"><div class=\"end\"><a href=\"#\"{$link}title=\"{$txt}\">{$txt}</a></div></div>";
			}
			$pn .= "<div class=\"limit\">";
			$pn .= Sobi::Txt( 'PN.CURRENT_SITE', array( 'current' => $this->current, 'pages' => $pages ) );
			$pn .= '</div></div>';
		}
		$pn .= '</div><br/>';

		if( $return ) {
			return $pn;
		}
		else {
			echo $pn;
		}
	}
}
?>
