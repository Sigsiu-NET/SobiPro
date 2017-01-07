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
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 14-Jan-2009 14:44:34
 */
class SPAdmJoomlaMenuView extends SPAdmView
{
	public function functions()
	{
		$functions = $this->get( 'functions' );
		$out = [];
		$section = SPRequest::int( 'section' );
		$out[ ] = '<form action="index.php" method="post">';
		$out[ ] = SPHtml_Input::select( 'function', $functions, null, false, [ 'id' => 'SobiProFunctions' ] );
		$out[ ] = '<input type="hidden" name="option" value="com_sobipro">';
		$out[ ] = '<input type="hidden" name="task" value="menu">';
		$out[ ] = '<input type="hidden" name="tmpl" value="component">';
		$out[ ] = '<input type="hidden" name="format" value="html">';
		$out[ ] = '<input type="hidden" name="mid" value="' . SPRequest::int( 'mid' ) . '">';
		$out[ ] = '<input type="hidden" name="section" value="' . $section . '">';
		$out[ ] = '</form>';
		echo implode( "\n", $out );
	}
}
