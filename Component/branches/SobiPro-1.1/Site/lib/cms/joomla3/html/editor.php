<?php
/**
 * @version: $Id$
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

/**
 * @author Radek Suski
 * @version 1.0
 * @created 21-Jan-2009 5:51:03 PM
 */
class SPCMSEditor
{
	/**
	 * @param    string    The control name
	 * @param    string    The contents of the text area
	 * @param    string    The width of the text area (px or %)
	 * @param    string    The height of the text area (px or %)
	 * @param    boolean    True and the editor buttons will be displayed
	 * @param    array    Associative array of editor parameters
	 * @return string
	 */
	public function display( $name, $html, $width, $height, $buttons )
	{
		if ( SPRequest::cmd( 'format' ) != 'raw' ) {
			// public function display($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
			$editor = JEditor::getInstance( JFactory::getConfig()->get( 'editor' ) );
//			JFactory::getEditor()->display( $name, $html, $width, $height, '75', '20', $buttons, $params );
			return $editor->display( $name, $html, $width, $height, 75, 20, $buttons );
		}
	}
}
