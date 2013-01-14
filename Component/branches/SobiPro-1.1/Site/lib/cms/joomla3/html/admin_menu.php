<?php
/**
 * @version: $Id: admin_menu.php 691 2011-02-01 10:25:59Z Radek Suski $
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
 * $Date: 2011-02-01 11:25:59 +0100 (Tue, 01 Feb 2011) $
 * $Revision: 691 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla16/html/admin_menu.php $
 */
require_once dirname(__FILE__).'/../../joomla_common/html/admin_menu.php';
/**
 * @author Radek Suski
 * @version 1.0
 * @since 1.0
 * @created 15-Jan-2009 2:04:36 PM
 */
abstract class SPAdmMenu extends SPJoomlaAdmMenu
{
	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin)
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function help( $alt )
	{
		$bar =& JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'link', 'help', $alt, 'http://sobipro.sigsiu.net/help_screen/'.Sobi::Reg( 'help_task', Sobi::Reg( 'task', SPRequest::task() ) ) );
		SPFactory::header()->addJsCode( '
			window.addEvent( "domready",
				function() {
					var spHelpLink = $$( "#toolbar-help a" )[ 0 ];
					spHelpLink.target = "_blank";
				}
			);'
		);
	}
}
?>