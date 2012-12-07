<?php
/**
 * @version: $Id: template.php 898 2011-03-01 18:29:11Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-03-01 19:29:11 +0100 (Tue, 01 Mar 2011) $
 * $Revision: 898 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/template.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jun-2010 17:09:48
 */
class SPAdmTemplateView extends SPAdmView
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
			$title = Sobi::Txt( $title, array( 'path' => $name ) );
		}
		else {
			$title = Sobi::Txt( $title, array( 'template' => $name ) );
		}
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title' );
	}

	private function edit()
	{
		$jsFiles = array( 'codemirror.codemirror' );
		$ext = $this->get( 'file_ext' );
		$mode = null;
		switch ( strtolower( $ext ) ) {
			case 'xsl':
			case 'xml':
				$jsFiles[] = 'codemirror.mode.xml.xml';
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
