<?php
/**
 * @version: $Id: field.php 2155 2012-01-13 18:28:57Z Radek Suski $
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
 * $Date: 2012-01-13 19:28:57 +0100 (Fri, 13 Jan 2012) $
 * $Revision: 2155 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/field.php $
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
		SPLoader::loadClass( 'html.tooltip' );
		switch ( $this->get( 'task' ) ) {
			case 'list':
				$this->listFields();
				parent::display();
				break;
			case 'edit':
				$this->displayForm();
				break;
			default:
				parent::display();
				break;
		}
	}

	/**
	 */
	private function listFields()
	{
		$this->assign( Sobi::Section( true ), 'current_path' );
		$this->_plgSect = '_FieldsListTemplate';
		$c = $this->get( 'fields' );
		$fields = array();
		$this->assign(
		SPLists::tableHeader(
			array(
	                    'checkbox'      => SP_TBL_HEAD_SELECTION_BOX,
	                    'fid'           => SP_TBL_HEAD_SORTABLE,
	                    'name'          => SP_TBL_HEAD_SORTABLE,
	                    'fieldType'     => SP_TBL_HEAD_SORTABLE,
	                    'state'         => SP_TBL_HEAD_STATE,
	                    'showIn'        => SP_TBL_HEAD_SORTABLE,
	                    'validSince'    => SP_TBL_HEAD_SORTABLE,
	                    'validUntil'    => SP_TBL_HEAD_SORTABLE,
	                    'isFree'        => SP_TBL_HEAD_SORTABLE,
	                    'editable'      => SP_TBL_HEAD_SORTABLE,
	                    'required'      => SP_TBL_HEAD_SORTABLE,
	                    'order'         => SP_TBL_HEAD_ORDER,
			),  'field', 'p_fid', 'forder' ), 'header'
		);
		$sid = Sobi::Reg( 'current_section' );
		SPRequest::set( 'sid', $sid );
		if( count( $c ) ) {
			foreach ( $c as $f ) {
				$attr = $f->getAttributes();
				$field = array();
				foreach ( $attr as $a ) {
					$field[ $a ] = $f->get( $a );
				}
				if( $f->get( '_off' ) ) {
					$field[ 'name' ] = "<del style=\"color: red!important;font-weight: bold;\">{$field[ 'name' ]}</del>";
					$field[ 'field_type' ]	= '<del style="color: red!important;font-weight: bold;">'.Sobi::Txt( $field[ 'fieldType' ] )."<del>";
				}
				else {
					$url = Sobi::Url( array( 'task' => 'field.edit', 'fid' => $f->get( 'fid' ) , 'sid' => $sid ) );
					$field[ 'name' ] = "<a href=\"{$url}\">{$field[ 'name' ]}</a>";
					$field[ 'field_type' ]	= Sobi::Txt( $field[ 'fieldType' ] );
				}
				$field[ 'checkbox' ] 	= SPLists::checkedOut( $f, 'p_fid' );
				$field[ 'state' ] 		= SPLists::state( $f, 'fid', 'field', 'enabled' );
				$field[ 'order' ] 		= SPLists::position( $f, count( $c ), 'fid', 'field', 'sid', 'fid' );

				$field[ 'is_free' ] 	= SPLists::state( $f, 'fid', 'field', 'isFree', array( 'on' => 'setFree', 'off' => 'setFee') );
				$field[ 'required' ] 	= SPLists::state( $f, 'fid', 'field', 'required', array( 'on' => 'setRequired', 'off' => 'setNotRequired') );
				$field[ 'editable' ] 	= SPLists::state( $f, 'fid', 'field', 'editable', array( 'on' => 'setEditable', 'off' => 'setNotEditable') );
				$field[ 'show_in' ] 	= Sobi::Txt( $field[ 'showIn' ] );
				$fields[] 				= $field;
			}
		}
		$this->assign( $fields, 'fields' );
	}

	/**
	 * @param string $title
	 */
	public function setTitle( $title )
	{
		$titles = array();
		if( strstr( $title, '|' ) ) {
			$titleArr = explode( '|', $title );
			foreach ( $titleArr as $t ) {
				$t = explode( '=', $t );
				$titles[ trim( $t[ 0 ] ) ] = $t[ 1 ];
			}
			$title = $titles[ $this->get( 'task' ) ];
		}
		$name = $this->get( 'field.name' );
		$title = Sobi::Txt( $title, array( 'field' => $name ) );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title');
	}
	/**
	 * @param string $template
	 */
	public function setTemplate( $template )
	{
		if( ! $this ->_template ) {
			$this->_template = $template;
		}
		$this->_templates[] = $template;
		Sobi::Trigger( 'setTemplate', $this->name(), array( &$this->_templates ) );
	}

	/**
	 *
	 */
	public function displayForm()
	{
		Sobi::Trigger( 'Display', $this->name(), array( &$this ) );
		$action = $this->key( 'action' );

		$allowedTags = $this->get( 'field.allowedTags' );
		$allowedTags = is_array( $allowedTags ) ? implode( '|', $allowedTags ) : null;

		$allowedAttributes =  $this->get( 'field.allowedAttributes' );
		$allowedAttributes =  is_array( $allowedAttributes ) ?  implode( '|', $allowedAttributes ) : null;

		$this->assign( $allowedAttributes, 'allowedAttributes' );
		$this->assign( $allowedTags, 'allowedTags' );

		echo $action ? "\n<form action=\"{$action}\" method=\"post\" name=\"adminForm\" id=\"SPAdminForm\" enctype=\"multipart/form-data\" accept-charset=\"utf-8\" >\n" : null;
		foreach ( $this->_templates as $tpl ) {
			$template = SPLoader::path( $tpl, 'adm.template' );
			if( !$template ) {
				$tpl = SPLoader::translatePath( $tpl, 'adm.template', false );
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_LOAD_TEMPLATE_AT', $tpl ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
			else {
				include( $template );
			}
		}

		if( count( $this->_hidden ) ) {
			$this->_hidden[ SPFactory::mainframe()->token() ] = 1;
			foreach ( $this->_hidden as $name => $value ) {
				echo "\n<input type=\"hidden\" name=\"{$name}\" id=\"{$name}\" value=\"{$value}\"/>";
			}
		}
		echo $action ? "\n</form>\n" : null;
		Sobi::Trigger( 'AfterDisplay', $this->name() );
	}
}
?>
