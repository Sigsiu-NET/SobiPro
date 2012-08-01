<?php
/**
 * @version: $Id: pagenav_xslt.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/helpers/pagenav_xslt.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Sigrid Suski
 * @version 1.0
 * @created 04-Mar-2009 03:05:59 PM
 */
final class SPPageNavXSLT
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
	 * @var array
	 */
	private $url = array();

	/**
	 * @param int $limit - number of entries to show on a page
	 * @param int $count - number off all entries
	 * @param int $current - current site to display
	 * @param string $url - URL for th navigation
	 */
	public function __construct( $limit, $count, $current, $url )
	{
		$this->limit 	= $limit;
		$this->count 	= $count;
		$this->current 	= $current ? $current : 1;
		$this->url		= $url;
	}

	/**
	 * Returns SobiPro Arr2XML array with the navigation.
	 * @return array
	 */
	public function get()
	{
		$pn 	= array();
		$pages 	= $this->limit > 0 ? ceil( $this->count / $this->limit ) : 0;
		if( $pages > 1 ) {
			if( $this->current == 1 ) {
				$pn[] = Sobi::Txt( 'PN.START' );
				$pn[] = Sobi::Txt( 'PN.PREVIOUS' );
			}
			else {
				$pn[] = array(
					'_complex' => 1,
					'_data' => Sobi::Txt( 'PN.START' ),
					'_attributes' => array( 'url' => Sobi::Url( array_merge( $this->url, array( 'site' => 1 ) ) )  )
				);
				$pn[] = array(
					'_complex' => 1,
					'_data' => Sobi::Txt( 'PN.PREVIOUS' ),
					'_attributes' => array( 'url' => Sobi::Url( array_merge( $this->url, array( 'site' => ( $this->current - 1 ) ) ) )  )
				);
			}
			for ( $page = 1; $page <= $pages; $page++ ) {
				$_attributes = array();
				if ( $page == $this->current ) {
					$_attributes[ 'selected' ] = 1;
				}
				else {
					$_attributes[ 'url' ] =  Sobi::Url( array_merge( $this->url, array( 'site' => $page ) ) );
				}
				$pn[] = array(
					'_complex' => 1,
					'_data' => $page,
					'_attributes' => $_attributes
				);
			}
			if ( $this->current == $pages ) {
				$pn[] = Sobi::Txt( 'PN.NEXT' );
				$pn[] = Sobi::Txt( 'PN.END' );
			}
			else {
				$pn[] = array(
					'_complex' => 1,
					'_data' => Sobi::Txt( 'PN.NEXT' ),
					'_attributes' => array( 'url' => Sobi::Url( array_merge( $this->url, array( 'site' => ( $this->current + 1 ) ) ) )  )
				);
				$pn[] = array(
					'_complex' => 1,
					'_data' => Sobi::Txt( 'PN.END' ),
					'_attributes' => array( 'url' => Sobi::Url( array_merge( $this->url, array( 'site' => $pages ) ) )  )
				);
			}
		}
		return array(
			'current_site_txt' => Sobi::Txt( 'PN.CURRENT_SITE', array( 'current' => $this->current, 'pages' => $pages ) ),
			'current_site' => $this->current,
			'all_sites' => $pages,
			'entries' => $this->count,
			'sites' => $pn,
		);
	}
}
?>