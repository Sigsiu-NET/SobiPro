<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );
SPLoader::loadController( 'field' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:38:46 PM
 */
final class SPFieldAdmCtrl extends SPFieldCtrl
{
	/**
	 * @var string
	 */
	protected $_type = 'field';
	/*** @var string */
	protected $_fieldType = 'field';
	/*** @var array */
	private $attr = [];
	/** @var bool */
	protected $_category = false;

	/**
	 * While editing an field
	 * When adding new field - second step
	 */
	protected function edit()
	{
		$fid = SPRequest::int( 'fid' );
		$this->checkTranslation();
		/* if adding new field - call #add */
		if ( !$fid ) {
			return $this->add();
		}

		if ( $this->isCheckedOut() ) {
			SPFactory::message()->error( Sobi::Txt( 'FM.IS_CHECKED_OUT' ), false );
		}
		else {
			/* check it out */
			$this->checkOut( $fid );
		}

		/* load base data */
		$f = $this->loadField( $fid );

		$field = SPFactory::Model( 'field', true );
		$field->extend( $f );

		$groups = $this->getFieldGroup( $f->fieldType, $f->tGroup );
		$type = $f->fType . ' ( ' . $f->tGroup . ' / ' . $f->fieldType . ' )';
		$this->_fieldType = $f->fieldType;

		/* get input filters */
		$registry = SPFactory::registry();
		$registry->loadDBSection( 'fields_filter' );
		$helpTask = 'field.' . $field->get( 'fieldType' );
		$registry->set( 'help_task', $helpTask );
		$filters = $registry->get( 'fields_filter' );
		$f = [ 0 => Sobi::Txt( 'FM.NO_FILTER' ) ];
		if ( count( $filters ) ) {
			foreach ( $filters as $filter => $data ) {
				$f[ $filter ] = Sobi::Txt( $data[ 'value' ] );
			}
		}

		/* get view class */
		$view = SPFactory::View( 'field', true );
		$view->addHidden( SPRequest::int( 'fid' ), 'fid' );
		$view->addHidden( SPRequest::sid(), 'sid' );
		$view->addHidden( $this->_category, 'category-field' );
		if ( $this->_category ) {
			$view->addHidden( -1, 'field.adminField' );
			$view->addHidden( $this->_fieldType, 'field.fieldType' );
		}
		$view->assign( $groups, 'types' );
		$view->assign( $type, 'type' );
		$view->assign( $f, 'filters' );
		$view->assign( $field, 'field' );
		$view->assign( $this->_category, 'category-field' );
		$view->assign( $this->_task, 'task' );
		$languages = $view->languages();
		$view->assign( $languages, 'languages-list' );
		$field->onFieldEdit( $view );

		// 1.1 native - config and view in xml
		$this->loadTemplate( $field, $view );

		/** Legacy code for 1.0 fields */
//		if ( !( $this->loadTemplate( $field, $view ) ) ) {
//			$view->assign( $helpTask, '_compatibility' );
//			if ( SPLoader::translatePath( 'field.edit.' . $field->get( 'fieldType' ), 'adm', true, 'ini' ) ) {
//				$view->loadConfig( 'field.edit.' . $field->get( 'fieldType' ) );
//			}
//			$view->setTemplate( 'field.edit' );
//			if ( SPLoader::translatePath( 'field.edit.' . $field->get( 'fieldType' ), 'adm' ) ) {
//				$view->setTemplate( 'field.edit.' . $field->get( 'fieldType' ) );
//			}
//			SPFactory::header()->addCssFile( 'adm.legacy' );
//		}
		$view->display();
	}

	protected function loadTemplate( $field, $view )
	{
		$nid = '/' . Sobi::Section( 'nid' ) . '/';
		$disableOverrides = null;
		if ( is_array( Sobi::My( 'groups' ) ) ) {
			$disableOverrides = array_intersect( Sobi::My( 'groups' ), Sobi::Cfg( 'templates.disable-overrides', [] ) );
		}
		if ( SPLoader::translatePath( 'field.' . $field->get( 'fieldType' ), 'adm', true, 'xml' ) ) {
			/** Case we have also override  */
			/** section override */
			if ( !( $disableOverrides ) && SPLoader::translatePath( 'field.' . $nid . $field->get( 'fieldType' ), 'adm', true, 'xml' ) ) {
				$view->loadDefinition( 'field.' . $nid . $field->get( 'fieldType' ) );
			}
			/** std override */
			elseif ( SPLoader::translatePath( 'field.' . $field->get( 'fieldType' ) . '_override', 'adm', true, 'xml' ) ) {
				$view->loadDefinition( 'field.' . $field->get( 'fieldType' ) . '_override' );
			}
			else {
				$view->loadDefinition( 'field.' . $field->get( 'fieldType' ) );
			}
			if ( SPLoader::translatePath( 'field.templates.' . $field->get( 'fieldType' ) . '_override', 'adm' ) ) {
				$view->setTemplate( 'field.templates.' . $field->get( 'fieldType' ) . '_override' );
			}
			elseif ( SPLoader::translatePath( 'field.templates.' . $nid . $field->get( 'fieldType' ), 'adm' ) ) {
				$view->setTemplate( 'field.templates.' . $nid . $field->get( 'fieldType' ) );
			}
			else {
				$view->setTemplate( 'default' );
			}
			return true;
		}
		return false;
	}

	private function getFieldGroup( $fType, $group = null )
	{
		if ( !( $group ) ) {
			$group = SPFactory::db()
					->select( 'tGroup', 'spdb_field_types', [ 'tid' => $fType ] )
					->loadResult();
		}
		/* get cognate field types */
		if ( $group != 'special' ) {
			try {
				$fTypes = SPFactory::db()
						->select( '*', 'spdb_field_types', [ 'tGroup' => $group ], 'fPos' )
						->loadObjectList();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELD_TYPES_DB_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
			}

			if ( count( $fTypes ) ) {
				$pre = 'FIELD.TYPE_OPTG_';
				foreach ( $fTypes as $type ) {
					$groups[ str_replace( $pre, null, Sobi::Txt( $pre . $type->tGroup ) ) ][ $type->tid ] = $type->fType;
				}
			}
		}
		else {
			$name = SPFactory::db()
					->select( 'fType', 'spdb_field_types', [ 'tid' => $fType ] )
					->loadResult();
			$groups[ Sobi::Txt( 'FIELD.TYPE_OPTG_SPECIAL' ) ][ $fType ] = $name;
		}
		return $groups;
	}


	protected function getPlainFieldType( $fType, $group = null )
	{
		if ( !( $group ) ) {
			$group = SPFactory::db()
				->select( 'tGroup', 'spdb_field_types', [ 'tid' => $fType ] )
				->loadResult();
		}
		/* get cognate field types */
		if ( $group != 'special' ) {
			try {
				$fTypes = SPFactory::db()
					->select( '*', 'spdb_field_types', [ 'tGroup' => $group ], 'fPos' )
					->loadObjectList();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELD_TYPES_DB_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
			}

			if ( count( $fTypes ) ) {
				$pre = 'FIELD.TYPE_OPTG_';
				foreach ( $fTypes as $type ) {
					if ($type->tid == $fType) {
						$type = $type->fType . ' ( ' . $type->tGroup . ' / ' . $fType . ' )';
						break;
					}
				}
			}
		}
		else {
			$name = SPFactory::db()
				->select( 'fType', 'spdb_field_types', [ 'tid' => $fType ] )
				->loadResult();
			$type = $name . ' ( ' . $group . ' / ' . $fType . ' )';
		}
		return $type;
	}

	/**
	 * @param int $fid
	 * @return stdClass
	 */
	private function loadField( $fid )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		try {
			$db->select( '*', $db->join( [ [ 'table' => 'spdb_field', 'as' => 'sField', 'key' => 'fieldType' ], [ 'table' => 'spdb_field_types', 'as' => 'sType', 'key' => 'tid' ] ] ), [ 'fid' => $fid ] );
			$f = $db->loadObject();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		if ( $f->adminField == -1 ) {
			$this->_category = true;
		}
		return $f;
	}

	/**
	 * Just when adding new field - first step
	 */
	private function add()
	{
		if ( $this->_fieldType ) {
			$groups = $this->getFieldGroup( $this->_fieldType );
			$type = $this->getPlainFieldType( $this->_fieldType );
			$field = SPFactory::Model( 'field', true );
			$field->loadType( $this->_fieldType );
		}
		else {
			$groups = $this->getFieldTypes();
			/* create dummy field with initial values */
			$field = [
					'name' => '',
					'nid' => '',
					'notice' => '',
					'description' => '',
					'adminField' => 0,
					'enabled' => 1,
					'fee' => 0,
					'isFree' => 1,
					'withLabel' => 1,
					'version' => 1,
					'editable' => 1,
					'required' => 0,
					'priority' => 5,
					'showIn' => 'details',
					'editLimit' => '',
					'version' => 1,
					'inSearch' => 0,
					'cssClass' => '',
					'fieldType' => $this->_fieldType,
			];
		}

		/* get view class */
		$view = SPFactory::View( 'field', true );
		$task = 'add';
		$view->addHidden( SPRequest::sid(), 'sid' );
		$view->addHidden( 0, 'fid' );
		$view->addHidden( $this->_category, 'category-field' );
		if ( $this->_category ) {
			$view->addHidden( -1, 'field.adminField' );
			$view->addHidden( $this->_fieldType, 'field.fieldType' );
		}
		$view->assign( $groups, 'types' );
		$view->assign( $type, 'type' );
		$view->assign( $field, 'field' );
		$view->assign( $this->_category, 'category-field' );
		$view->assign( $task, 'task' );
		if ( $this->_fieldType ) {
			$field->onFieldEdit( $view );
		}
		$registry = SPFactory::registry();
		$registry->loadDBSection( 'fields_filter' );
		$helpTask = 'field.' . $field->get( 'fieldType' );
		$registry->set( 'help_task', $helpTask );
		$filters = $registry->get( 'fields_filter' );
		$f = [ 0 => Sobi::Txt( 'FM.NO_FILTER' ) ];
		if ( count( $filters ) ) {
			foreach ( $filters as $filter => $data ) {
				$f[ $filter ] = Sobi::Txt( $data[ 'value' ] );
			}
		}
		$view->assign( $f, 'filters' );

		if ( $this->loadTemplate( $field, $view ) ) {
			$view->display();
		}
		/** legacy */
//		elseif ( SPLoader::translatePath( 'field.edit.' . $this->_fieldType, 'adm' ) ) {
//			$view->assign( $helpTask, '_compatibility' );
//			if ( SPLoader::translatePath( 'field.edit.' . $this->_fieldType, 'adm', true, 'ini' ) ) {
//				$view->loadConfig( 'field.edit.' . $this->_fieldType );
//			}
//			$view->setTemplate( 'field.edit' );
//			if ( SPLoader::translatePath( 'field.edit.' . $this->_fieldType, 'adm' ) ) {
//				$view->setTemplate( 'field.edit.' . $this->_fieldType );
//			}
//			SPFactory::header()->addCSSCode( '#toolbar-box { display: block }' );
//			$view->display();
//		}
		else {
			Sobi::Error( $this->name(), SPLang::e( 'NO_FIELD_DEF' ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}

	}

	protected function getFieldTypes( $category = false )
	{
		static $fTypes = null;
		if ( !( $fTypes ) ) {
			/* get all existing field types */
			try {
				$fTypes = SPFactory::db()
						->select( '*', 'spdb_field_types', null, 'fPos' )
						->loadObjectList();
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
			}
			$groups = [];
		}
		if ( count( $fTypes ) ) {
			$pre = 'FIELD.TYPE_OPTG_';
			$groups = [
					Sobi::Txt( $pre . 'free_single_simple_data' ) => [],
					Sobi::Txt( $pre . 'predefined_multi_data_single_choice' ) => [],
					Sobi::Txt( $pre . 'predefined_multi_data_multi_choice' ) => [],
					Sobi::Txt( $pre . 'special' ) => [],
			];
			foreach ( $fTypes as $type ) {
				if ( $category ) {
					try {
						$class = SPLoader::loadClass( 'opt.fields.' . $type->tid );
						if ( !( property_exists( $class, 'CAT_FIELD' ) ) ) {
							continue;
						}
					} catch ( SPException $x ) {
						continue;
					}
				}
				$groups[ str_replace( $pre, null, Sobi::Txt( $pre . $type->tGroup ) ) ][ $type->tid ] = $type->fType;
			}
			foreach ( $groups as &$group ) {
				asort( $group );
			}
			return $groups;
		}
		return $groups;
	}

	/**
	 * @TODO should be moved to the model ????
	 * Adding new field
	 * Save base data and redirect to the edit function when the field type has been chosen
	 * @todo it should be moved to the model
	 * @return integer
	 */
	public function saveNew()
	{
		$field = SPFactory::Model( 'field', true );
		$this->getRequest();
		return $field->saveNew( $this->attr );
	}

	/**
	 * Get data from request
	 */
	private function getRequest()
	{
		foreach ( $_REQUEST as $k => $v ) {
			if ( strstr( $k, 'field_' ) ) {
				$value = SPRequest::raw( $k );
				$this->attr[ str_replace( 'field_', null, $k ) ] = $value;
			}
		}
	}

	/**
	 * @param int $id
	 * @return array|void
	 */
	public function delete( $id = 0 )
	{
		$fields = [];
		$m = [];
		if ( $id ) {
			$fields[] = $id;
		}
		else {
			if ( SPRequest::int( 'fid', 0 ) ) {
				$fields[] = SPRequest::int( 'fid', 0 );
			}
			else {
				$fields = SPRequest::arr( 'p_fid', [] );
			}
		}
		if ( count( $fields ) ) {
			foreach ( $fields as $id ) {
				$field = SPFactory::Model( 'field', true );
				$field->extend( $this->loadField( $id ) );
				$msg = $field->delete();
				SPFactory::message()
						->setMessage( $msg, false, SPC::SUCCESS_MSG );
				$m[] = $msg;
			}
		}
		else {
			$msg = SPLang::e( 'FMN.STATE_CHANGE_NO_ID' );
			SPFactory::message()
					->setMessage( $msg, false, SPC::ERROR_MSG );
			return;

		}
		return $m;
	}

	public function checkOut()
	{
	}

	public function isCheckedOut()
	{
	}

	protected function validateRequest( $field )
	{
		$type = SPRequest::cmd( 'field_fieldType' );
		$definition = SPLoader::path( 'field.' . $type, 'adm', true, 'xml' );
		if ( $definition ) {
			$xdef = new DOMXPath( SPFactory::LoadXML( $definition ) );
			$required = $xdef->query( '//field[@required="true"]' );
			if ( $required->length ) {
				for ( $i = 0; $i < $required->length; $i++ ) {
					$node = $required->item( $i );
					$name = $node->attributes->getNamedItem( 'name' )->nodeValue;
					if ( !( SPRequest::raw( str_replace( '.', '_', $name ) ) ) ) {
						$this->response( Sobi::Url( [ 'task' => 'field.edit', 'fid' => $field->get( 'fid' ), 'sid' => SPRequest::sid() ] ), Sobi::Txt( 'PLEASE_FILL_IN_ALL_REQUIRED_FIELDS' ), false, 'error', [ 'required' => $name ] );
					}
				}
			}
		}
	}

	/**
	 * Save existing field
	 * @param bool $clone
	 * @param bool $apply
	 */
	protected function save( $clone = false, $apply = false )
	{
		$sets = [];
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$fid = SPRequest::int( 'fid' );
		$field = SPFactory::Model( 'field', true );
		if ( $fid ) {
			$f = $this->loadField( $fid );
			$field->extend( $f );
		}
		else {
			$field->loadType( SPRequest::cmd( 'field_fieldType' ) );
		}
		$nid = SPRequest::cmd( 'field_nid' );
		if ( !( $nid ) || !( strstr( $nid, 'field_' ) ) ) {
			/** give me my spaces back!!! */
			$nid = strtolower( str_replace( '-', '_', SPLang::nid( 'field_' . SPRequest::string( 'field_name' ) ) ) );
			SPRequest::set( 'field_nid', $nid );
		}
		$this->getRequest();
		$this->validateRequest( $field );

		if ( $clone || !( $fid ) ) {
			try {
				$fid = $field->saveNew( $this->attr );
				//warum nochmal save??
				$field->save( $this->attr );
			} catch ( SPException $x ) {
				$this->response( Sobi::Url( [ 'task' => 'field.edit', 'fid' => $fid, 'sid' => SPRequest::sid() ] ), $x->getMessage(), false, SPC::ERROR_MSG );
			}
		}
		else {
			try {
				$field->save( $this->attr );
			} catch ( SPException $x ) {
				$this->response( Sobi::Url( [ 'task' => 'field.edit', 'fid' => $fid, 'sid' => SPRequest::sid() ] ), $x->getMessage(), false, SPC::ERROR_MSG );
			}
		}
		$alias = $field->get( 'nid' );
		$fieldSets = $field->get( 'sets' );
		if ( is_array( $fieldSets ) && count( $fieldSets ) ) {
			$sets = array_merge( $fieldSets, $sets );
		}
		$sets[ 'fid' ] = $field->get( 'fid' );
		$sets[ 'field.nid' ] = $alias;
		/* in case we are changing the sort by field */
		if ( Sobi::Cfg( 'list.entries_ordering' ) == $alias && $field->get( 'nid' ) != $alias ) {
			SPFactory::config()->saveCfg( 'list.entries_ordering', $field->get( 'nid' ) );
		}

		SPFactory::cache()->cleanSection();
		if ( $this->_task == 'apply' || $clone ) {
			if ( $clone ) {
				$msg = Sobi::Txt( 'FM.FIELD_CLONED' );
				$this->response( Sobi::Url( [ 'task' => 'field.edit', 'fid' => $fid, 'sid' => SPRequest::sid() ] ), $msg );
			}
			else {
				$msg = Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' );
				$this->response( Sobi::Url( [ 'task' => 'field.edit', 'fid' => $fid, 'sid' => SPRequest::sid() ] ), $msg, false, 'success', [ 'sets' => $sets ] );
			}
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' ) );
		}
	}

	/**
	 * List all fields in this section
	 */
	private function listFields()
	{
		/* @var SPdb $db */
		$ord = $this->parseFieldsOrdering( 'forder', 'position.asc' );
		SPLoader::loadClass( 'html.input' );
		Sobi::ReturnPoint();

		/* create menu */
		$sid = Sobi::Reg( 'current_section' );
		$menu = SPFactory::Instance( 'views.adm.menu', 'field.list', $sid );
		$cfg = SPLoader::loadIniFile( 'etc.adm.section_menu' );
		Sobi::Trigger( 'Create', 'AdmMenu', [ &$cfg ] );
		if ( count( $cfg ) ) {
			foreach ( $cfg as $section => $keys ) {
				$menu->addSection( $section, $keys );
			}
		}

		Sobi::Trigger( 'AfterCreate', 'AdmMenu', [ &$menu ] );
		/* create new SigsiuTree */
		$tree = SPLoader::loadClass( 'mlo.tree' );
		$tree = new $tree( Sobi::GetUserState( 'categories.order', 'corder', 'position.asc' ) );
		/* set link */
		$tree->setHref( Sobi::Url( [ 'sid' => '{sid}' ] ) );
		$tree->setId( 'menuTree' );
		/* set the task to expand the tree */
		$tree->setTask( 'category.expand' );
		$tree->init( $sid );
		/* add the tree into the menu */
		$menu->addCustom( 'AMN.ENT_CAT', $tree->getTree() );

		try {
			$results = SPFactory::db()
					->select( '*', 'spdb_field', [ 'section' => $sid ], $ord )
					->loadObjectList();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		$fields = [];
		$categoryFields = [];
		if ( count( $results ) ) {
			foreach ( $results as $result ) {
				$field = SPFactory::Model( 'field', true );
				$field->extend( $result );
				if ( $field->get( 'adminField' ) == -1 ) {
					$categoryFields[] = $field;
				}
				else {
					$fields[] = $field;
				}
			}
		}
		$fieldTypes = $this->getFieldTypes();
		$subMenu = [];
		foreach ( $fieldTypes as $type => $group ) {
			asort( $group );
			$subMenu[] = [
					'label' => $type,
					'element' => 'nav-header'
			];
			foreach ( $group as $t => $l ) {
				$subMenu[] = [
						'type' => null,
						'task' => 'field.add.' . $t,
						'label' => $l,
						'icon' => '',
						'element' => 'button'
				];
			}
		}
		$categoryFieldsTypes = $this->getFieldTypes( true );
		$cateSubMenu = [];
		foreach ( $categoryFieldsTypes as $type => $group ) {
			asort( $group );
			$cateSubMenu[] = [
					'label' => $type,
					'element' => 'nav-header'
			];
			foreach ( $group as $t => $l ) {
				$cateSubMenu[] = [
						'type' => null,
						'task' => 'field.add.' . $t . '.category',
						'label' => $l,
						'icon' => '',
						'element' => 'button'
				];
			}
		}

		$sectionName = Sobi::Section( true );
		$fieldsOrder = Sobi::GetUserState( 'fields.order', 'forder', 'position.asc' );
		SPFactory::View( 'field', true )
				->addHidden( $sid, 'sid' )
				->assign( $fields, 'fields' )
				->assign( $categoryFields, 'category-fields' )
				->assign( $cateSubMenu, 'categoryFieldTypes' )
				->assign( $subMenu, 'fieldTypes' )
				->assign( $sectionName, 'section' )
				->assign( $menu, 'menu' )
				->assign( $fieldsOrder, 'ordering' )
				->assign( $this->_task, 'task' )
				->determineTemplate( 'field', 'list' )
				->display();
	}

	/**
	 * @param string $col
	 * @param string $def
	 * @return string
	 */
	protected function parseFieldsOrdering( $col, $def )
	{
		$order = Sobi::GetUserState( 'fields.order', $col, Sobi::Cfg( 'admin.fields-order', $def ) );
		$ord = $order;
		$dir = 'asc';
		/** legacy - why the hell I called it order?! */
		$ord = str_replace( 'order', 'position', $ord );

		if ( strstr( $ord, '.' ) ) {
			$ord = explode( '.', $ord );
			$dir = $ord[ 1 ];
			$ord = $ord[ 0 ];
		}
		$ord = ( $ord == 'state' ) ? 'enabled' : $ord;
//		$ord = ( $ord == 'position' ) ? 'position' : $ord;
		if ( $ord == 'name' ) {
			/* @var SPdb $db */
			$db = SPFactory::db();
			$fields = $db
					->select( 'fid', 'spdb_language', [ 'oType' => 'field', 'sKey' => 'name', 'language' => Sobi::Lang() ], 'sValue.' . $dir )
					->loadResultArray();
			if ( !count( $fields ) && Sobi::Lang() != Sobi::DefLang() ) {
				$fields = $db
						->select( 'id', 'spdb_language', [ 'oType' => 'field', 'sKey' => 'name', 'language' => Sobi::DefLang() ], 'sValue.' . $dir )
						->loadResultArray();
			}
			if ( count( $fields ) ) {
				$fields = implode( ',', $fields );
				$ord = "field( fid, {$fields} )";
			}
			else {
				$ord = 'fid.' . $dir;
			}
		}
		else {
			$ord = $ord . '.' . $dir;
		}
		Sobi::setUserState( 'fields.order', $order );
		return $ord;
	}

	/**
	 */
	private function reorder()
	{
		$this->_reorder( SPRequest::arr( 'fid', [] ) );
		$this->_reorder( SPRequest::arr( 'cfid', [] ) );
		SPFactory::cache()->cleanSection();
		$this->response( Sobi::Url( [ 'task' => 'field.list', 'pid' => Sobi::Section() ] ), Sobi::Txt( 'NEW_FIELDS_ORDERING_HAS_BEEN_SAVED' ), true, SPC::SUCCESS_MSG );
	}

	/**
	 * @param bool $up
	 */
	private function singleReorder( $up )
	{
		$up = ( bool )$up;
		/* @var SPdb $db */
		$db = SPFactory::db();
		$fid = SPRequest::int( 'fid' );
		$category = false;
		if ( !$fid ) {
			$fid = SPRequest::int( 'cfid' );
			$category = true;
		}
		$fClass = SPLoader::loadModel( 'field', true );
		$fdata = $this->loadField( $fid );
		$field = new $fClass();
		$field->extend( $fdata );
		$eq = $up ? '<' : '>';
		$dir = $up ? 'position.desc' : 'position.asc';
		$current = $field->get( 'position' );
		try {
			$condition = [ 'position' . $eq => $current, 'section' => SPRequest::int( 'sid' ) ];
			if ( !( $category ) ) {
				$condition[ 'adminField>' ] = -1;
			}
			else {
				$condition[ 'adminField' ] = -1;
			}
			$interchange = $db
					->select( 'position, fid', 'spdb_field', $condition, $dir, 1 )
					->loadAssocList();
			if ( $interchange && count( $interchange ) ) {
				$db->update( 'spdb_field', [ 'position' => $interchange[ 0 ][ 'position' ] ], [ 'section' => SPRequest::int( 'sid' ), 'fid' => $field->get( 'fid' ) ], 1 );
				$db->update( 'spdb_field', [ 'position' => $current ], [ 'section' => SPRequest::int( 'sid' ), 'fid' => $interchange[ 0 ][ 'fid' ] ], 1 );
			}
			else {
				$current = $up ? $current-- : $current++;
				$db->update( 'spdb_field', [ 'position' => $current ], [ 'section' => SPRequest::int( 'sid' ), 'fid' => $field->get( 'fid' ) ], 1 );
			}
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		SPFactory::cache()->cleanSection();
		$this->response( Sobi::Url( [ 'task' => 'field.list', 'pid' => Sobi::Section() ] ), Sobi::Txt( 'NEW_FIELDS_ORDERING_HAS_BEEN_SAVED' ), true, SPC::SUCCESS_MSG );
	}

	/**
	 *
	 * @param task
	 * @return array
	 */
	protected function changeState( $task )
	{
		$fIds = SPRequest::arr( 'p_fid' );
		$col = 'enabled';
		$state = '0';
		$msg = null;
		if ( !$fIds ) {
			if ( SPRequest::int( 'fid' ) ) {
				$fIds = [ SPRequest::int( 'fid' ) ];
			}
			else {
				$fIds = [];
			}
		}
		if ( !( count( $fIds ) ) ) {
			return [ 'text' => Sobi::Txt( 'FMN.STATE_CHANGE_NO_ID' ), 'type' => SPC::ERROR_MSG ];
		}
		switch ( $task ) {
			case 'hide':
			case 'publish':
				$col = 'enabled';
				$state = ( $task == 'publish' ) ? 1 : 0;
				break;
			case 'setRequired':
			case 'setNotRequired':
				$col = 'required';
				$state = ( $task == 'setRequired' ) ? 1 : 0;
				break;
			case 'setEditable':
			case 'setNotEditable':
				$col = 'editable';
				$state = ( $task == 'setEditable' ) ? 1 : 0;
				break;
			case 'setFee':
			case 'setFree':
				$col = 'isFree';
				$state = ( $task == 'setFree' ) ? 1 : 0;
				break;
			/** @since 1.1 - single row only from the field list */
			case 'toggle':
				$fIds = [];
				$fid = SPRequest::int( 'fid' );
				$attribute = explode( '.', SPRequest::task() );
				/** now you know what a naming convention is for! Right? Damn!!! */
				$attribute = in_array( $attribute[ 2 ], [ 'editable', 'enabled', 'required' ] ) ? $attribute[ 2 ] : 'is' . ucfirst( $attribute[ 2 ] );
				$this->_model = SPFactory::Model( 'field', true )
						->init( $fid );
				$current = $this->_model->get( $attribute );
				try {
					SPFactory::db()
							->update( 'spdb_field', [ $attribute => !( $current ) ], [ 'fid' => $fid ], 1 );
					$msg = Sobi::Txt( 'FM.STATE_CHANGED', [ 'fid' => $fid ] );
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
					$msg = Sobi::Txt( 'FM.STATE_NOT_CHANGED', [ 'fid' => $fid ] );
				}
				break;
		}
		if ( count( $fIds ) ) {
			$msg = [];
			foreach ( $fIds as $fid ) {
				try {
					SPFactory::db()
							->update( 'spdb_field', [ $col => $state ], [ 'fid' => $fid ], 1 );
					$msg[] = [ 'text' => Sobi::Txt( 'FM.STATE_CHANGED', [ 'fid' => $fid ] ), 'type' => 'success' ];
				} catch ( SPException $x ) {
					Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
					$msg[] = [ 'text' => Sobi::Txt( 'FM.STATE_NOT_CHANGED', [ 'fid' => $fid ] ), 'type' => 'error' ];
				}
			}
		}
		SPFactory::cache()->cleanSection( Sobi::Section() );
		return $msg;
	}

	/**
	 * Route task
	 */
	public function execute()
	{
		/* parent class executes the plugins */
		$r = false;
		$task = $this->_task;
		if ( strstr( $this->_task, '.' ) ) {
			$task = explode( '.', $this->_task );
			$this->_fieldType = $task[ 1 ];
			if ( isset( $task[ 2 ] ) && $task[ 2 ] == 'category' ) {
				$this->_category = true;
			}
			$task = $task[ 0 ];
		}
		switch ( $task ) {
			case 'list':
				$r = true;
				$this->listFields();
				break;
			case 'add':
			case 'edit':
				$r = true;
				$this->edit();
				break;
			case 'cancel':
				$r = true;
				$this->response( Sobi::Back() );
				break;
			case 'addNew':
				$r = true;
				Sobi::Redirect( Sobi::Url( [ 'task' => 'field.edit', 'fid' => $this->saveNew(), 'sid' => SPRequest::sid() ] ) );
				break;
			case 'apply':
			case 'save':
				$r = true;
				$this->save();
				break;
			case 'clone':
				$r = true;
				$this->save( true );
				break;
			case 'delete':
				$r = true;
				SPFactory::cache()->cleanSection();
				$this->response( Sobi::Url( [ 'task' => 'field.list', 'pid' => Sobi::Section() ] ), $this->delete(), true );
				break;
			case 'reorder':
				$r = true;
				$this->reorder();
				break;
			case 'revisions':
				$r = true;
				$this->revisions();
				break;
			case 'up':
			case 'down':
				$r = true;
				$this->singleReorder( $this->_task == 'up' );
				break;
			case 'hide':
			case 'publish':
			case 'setRequired':
			case 'setNotRequired':
			case 'setEditable':
			case 'setNotEditable':
			case 'setFee':
			case 'setFree':
			case 'toggle':
				$r = true;
				$this->_type = 'section';
				$this->authorise( 'configure' );
				SPFactory::cache()->cleanSection();
				$this->response( Sobi::Back(), $this->changeState( $task ), true );
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !( Sobi::Trigger( 'Execute', $this->name(), [ &$this ] ) ) ) {
					$fid = SPRequest::int( 'fid' );
					$method = $this->_task;
					if ( $fid ) {
						SPLoader::loadModel( 'field', true );
						$fdata = $this->loadField( $fid );
						$field = new SPAdmField();
						$field->extend( $fdata );
						try {
							$field->$method();
						} catch ( SPException $x ) {
							Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
						}
					}
					elseif ( !( parent::execute() ) ) {
						Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
					}
				}
				break;
		}
		return $r;
	}

	/**
	 * @param $fIds
	 * @return bool
	 */
	private function _reorder( $fIds )
	{
		asort( $fIds );
		$c = 0;
		if ( !( count( $fIds ) ) ) {
			return true;
		}
		foreach ( $fIds as $fid => $pos ) {
			$c++;
			try {
				SPFactory::db()
						->update( 'spdb_field', [ 'position' => $c ], [ 'fid' => $fid ] );
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
			}
		}
	}
}
