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
SPLoader::loadView( 'config', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jun-2010 17:09:48
 */
class SPAdmTemplateView extends SPConfigAdmView
{
	public function display()
	{
		switch ( $this->get( 'task' ) ) {
			case 'edit':
				$this->edit();
				break;
		}
		parent::display();
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'template_name' );
		if ( !( strlen( $name ) ) ) {
			$name = $this->get( 'file_name' );
			$title = Sobi::Txt( $title, [ 'path' => $name ] );
		}
		else {
			$title = Sobi::Txt( $title, [ 'template' => $name ] );
		}
		Sobi::Trigger( 'setTitle', $this->name(), [ &$title ] );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title' );
	}

	private function edit()
	{
		$jsFiles = [ 'codemirror.codemirror' ];
		$ext = $this->get( 'file_ext' );
		$mode = null;
		switch ( strtolower( $ext ) ) {
			case 'xsl':
			case 'xml':
				$jsFiles[] = 'codemirror.mode.xml.xml';
				break;
			case 'less':
				$jsFiles[] = 'codemirror.mode.less.less';
				break;
			case 'css':
				$jsFiles[] = 'codemirror.mode.css.css';
				break;
			case 'js':
				$jsFiles[] = 'codemirror.mode.javascript.javascript';
				break;
			case 'php':
				$jsFiles[] = 'codemirror.mode.clike.clike';
				$jsFiles[] = 'codemirror.mode.php.php';
				$jsFiles[] = 'codemirror.mode.htmlmixed.htmlmixed';
				$jsFiles[] = 'codemirror.mode.xml.xml';
				$jsFiles[] = 'codemirror.mode.javascript.javascript';
				$jsFiles[] = 'codemirror.mode.css.css';
				$mode = 'application/x-httpd-php';
				break;
			case 'ini':
				$jsFiles[] = 'codemirror.mode.properties.properties';
				break;
		}
		SPFactory::header()
				->addJsFile( $jsFiles )
				->addCssFile( 'codemirror.codemirror' )
				->addJsCode( 'SobiPro.jQuery( document ).ready( function () { SPInitTplEditor( "'.$mode.'") } );' );
	}
}
