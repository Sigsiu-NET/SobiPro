<?php
/**
 * @version: $Id: editor.php 1577 2011-07-02 12:02:03Z Radek Suski $
 * @package: SobiPro Bridge
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
 * $Date: 2011-07-02 14:02:03 +0200 (Sat, 02 Jul 2011) $
 * $Revision: 1577 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla15/html/editor.php $
 */

/**
 * @author Radek Suski
 * @version 1.0
 * @created 21-Jan-2009 5:51:03 PM
 */
class SPCMSEditor
{
	/**
	 * @param	string	The control name
	 * @param	string	The contents of the text area
	 * @param	string	The width of the text area (px or %)
	 * @param	string	The height of the text area (px or %)
	 * @param	boolean	True and the editor buttons will be displayed
	 * @param	array	Associative array of editor parameters
	 */
	public function display( $name, $html, $width, $height, $buttons = false, $params = array() )
	{
		if( SPRequest::cmd( 'format' ) != 'raw' ) {
			return JFactory::getEditor()->display( $name, $html, $width, $height, 0, 0, $buttons, $params );
		}
	}
}
?>