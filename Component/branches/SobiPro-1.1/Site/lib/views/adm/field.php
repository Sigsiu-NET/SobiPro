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

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * Design:
 * @author Radek Suski
 * @version 1.2
 * @created 10-Jan-2009 4:41:41 PM
 */
class SPFieldAdmView extends SPAdmView
{
	private $_templates = array();

	/**
	 */
	public function display()
	{
		switch ( trim( $this->get( 'task' ) ) ) {
			case 'list':
				parent::display();
				break;
			case 'edit':
			case 'add':
				$this->displayForm();
				break;
			default:
				parent::display();
				break;
		}
	}


	/**
	 * @param string $title
	 * @return string|void
	 */
	public function setTitle( $title )
	{
		if ( strstr( SPRequest::task(), '.add' ) ) {
			$title = str_replace( 'EDIT', 'ADD', $title );
		}
		$title = Sobi::Txt( $title, array( 'field' => $this->get( 'field.name' ), 'field_type' => $this->get( 'field.fieldType' ) ) );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title' );
	}

	/**
	 * @param string $template
	 */
	public function setTemplate( $template )
	{
		if ( !$this->_template ) {
			$this->_template = $template;
		}
		$this->_templates[ ] = $template;
		Sobi::Trigger( 'setTemplate', $this->name(), array( &$this->_templates ) );
	}

	/**
	 *
	 */
	public function displayForm()
	{
		Sobi::Trigger( 'Display', $this->name(), array( &$this ) );
		$action = $this->key( 'action' );

		echo '<div class="SobiPro" id="SobiPro">' . "\n";
		echo $action ? "\n<form action=\"{$action}\" method=\"post\" name=\"adminForm\" id=\"SPAdminForm\" enctype=\"multipart/form-data\" accept-charset=\"utf-8\" >\n" : null;
		foreach ( $this->_templates as $tpl ) {
			$template = SPLoader::path( $tpl, 'adm.template' );
			if ( !$template ) {
				$tpl = SPLoader::translatePath( $tpl, 'adm.template', false );
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_LOAD_TEMPLATE_AT', $tpl ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			else {
				include( $template );
			}
		}

		if ( count( $this->_hidden ) ) {
			$this->_hidden[ SPFactory::mainframe()->token() ] = 1;
			foreach ( $this->_hidden as $name => $value ) {
				echo "\n<input type=\"hidden\" name=\"{$name}\" id=\"SP_{$name}\" value=\"{$value}\"/>";
			}
		}
		echo $action ? "\n</form>\n" : null;
		echo '</div>';
		Sobi::Trigger( 'AfterDisplay', $this->name() );
	}
}
