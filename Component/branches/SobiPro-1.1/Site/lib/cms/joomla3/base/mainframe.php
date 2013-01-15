<?php
/**
 * @version: $Id: mainframe.php 1508 2011-06-21 19:48:12Z Radek Suski $
 * @package: SobiPro J!Bridge
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
 * $Date: 2011-06-21 21:48:12 +0200 (Tue, 21 Jun 2011) $
 * $Revision: 1508 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla16/base/mainframe.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname( __FILE__ ) . '/../../joomla16/base/mainframe.php';
/**
 * Interface between SobiPro and the used CMS
 * @author Radek Suski
 * @version 1.0
 * @created Mon, Jan 14, 2013 06:50:55
 */
class SPJ3MainFrame extends SPJ16MainFrame implements SPMainframeInterface
{
	/**
	 * @return SPJoomlaMainFrame
	 */
	public static function & getInstance()
	{
		static $mf = false;
		if ( !( $mf ) || !( $mf instanceof self ) ) {
			$mf = new self();
		}
		return $mf;
	}

	protected function getMetaDescription( $document )
	{
		return $document->getDescription();
	}
}
