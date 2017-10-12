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

use Sobi\Input\Input;

defined( '_JEXEC' ) or die();

if ( !( class_exists( 'JElement' ) ) ) {
	/**
	 * @version        $Id$
	 * @package        Joomla.Framework
	 * @subpackage    Parameter
	 * @copyright    Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
	 * @license        GNU/GPL, see LICENSE.php
	 * Joomla! is free software. This version may have been modified pursuant
	 * to the GNU General Public License, and as distributed it includes or
	 * is derivative of works licensed under the GNU General Public License or
	 * other free or open source software licenses.
	 * See COPYRIGHT.php for copyright notices and details.
	 */

// Check to ensure this file is within the rest of the framework
	defined( 'JPATH_BASE' ) or die();

	/**
	 * Parameter base class
	 *
	 * The JElement is the base class for all JElement types
	 *
	 * @abstract
	 * @package     Joomla.Framework
	 * @subpackage        Parameter
	 * @since        1.5
	 */
	class JElement extends JObject
	{
		/**
		 * element name
		 *
		 * This has to be set in the final
		 * renderer classes.
		 *
		 * @access    protected
		 * @var        string
		 */
		var $_name = null;

		/**
		 * reference to the object that instantiated the element
		 *
		 * @access    protected
		 * @var        object
		 */
		var $_parent = null;

		/**
		 * Constructor
		 *
		 * @access protected
		 *
		 * @param null $parent
		 */
		function __construct( $parent = null )
		{
			$this->_parent = $parent;
		}

		/**
		 * get the element name
		 *
		 * @access    public
		 * @return    string    type of the parameter
		 */
		function getName()
		{
			return $this->_name;
		}

		function render( &$xmlElement, $value, $control_name = 'params' )
		{
			$name = $xmlElement->attributes( 'name' );
			$label = $xmlElement->attributes( 'label' );
			$descr = $xmlElement->attributes( 'description' );
			//make sure we have a valid label
			$label = $label ? $label : $name;
			$result[ 0 ] = $this->fetchTooltip( $label, $descr, $xmlElement, $control_name, $name );
			$result[ 1 ] = $this->fetchElement( $name, $value, $xmlElement, $control_name );
			$result[ 2 ] = $descr;
			$result[ 3 ] = $label;
			$result[ 4 ] = $value;
			$result[ 5 ] = $name;

			return $result;
		}

		function fetchTooltip( $label, $description, &$xmlElement, $control_name = '', $name = '' )
		{
			$output = '<label id="' . $control_name . $name . '-lbl" for="' . $control_name . $name . '"';
			if ( $description ) {
				$output .= ' class="hasTip" title="' . JText::_( $label ) . '::' . JText::_( $description ) . '">';
			}
			else {
				$output .= '>';
			}
			$output .= JText::_( $label ) . '</label>';

			return $output;
		}
	}
}

if ( !( defined( 'SOBI_CMS' ) ) ) {
	define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : 'joomla16' );
}

class JElementSPSection extends JElement
{
	protected $task = null;
	protected $taskName = null;
	protected $oType = null;
	protected $oTypeName = null;
	protected $oName = null;
	protected $sid = null;
	protected $section = null;
	protected $tpl = null;
	protected $cid = null;

	public static function & getInstance()
	{
		static $instance = null;
		if ( !( $instance instanceof self ) ) {
			$instance = new self();
		}

		return $instance;
	}

	// will be called for SobiPro modules in backend
	public function __construct()
	{
		static $loaded = false;
		if ( $loaded ) {
			return true;
		}
		$jConfig = JFactory::getConfig();
		defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
		require_once( JPATH_SITE . '/components/com_sobipro/lib/sobi.php' );
		if ( method_exists( $jConfig, 'getValue' ) ) {
			Sobi::Init( JPATH_SITE, JFactory::getConfig()->getValue( 'config.language' ) );
		}
		else {
			Sobi::Init( JPATH_SITE, JFactory::getConfig()->get( 'config.language' ) );
		}
		if ( !( defined( 'SOBI_CMS' ) ) ) {
			//define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : ( version_compare( JVERSION, '1.6.0', 'ge' ) ? 'joomla16' : 'joomla15' ) );
			define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : 'joomla16' );
		}
		SPLoader::loadClass( 'mlo.input' );
		define( 'SOBIPRO_ADM', true );
		define( 'SOBI_ADM_PATH', JPATH_ADMINISTRATOR . '/components/com_sobipro' );
		$adm = str_replace( JPATH_ROOT, null, JPATH_ADMINISTRATOR );
		define( 'SOBI_ADM_FOLDER', $adm );
		define( 'SOBI_ADM_LIVE_PATH', $adm . '/components/com_sobipro' );
		SPLang::load( 'com_sobipro.sys' );

		$head = SPFactory::header();
		if ( SOBI_CMS == 'joomla3' ) {
			$head->initBase( true );
			$head->addJsFile( [ 'sobipro', 'jqnc', 'adm.sobipro', 'adm.jmenu' ] );
		}
		else {
			$head->initBase( true );
			$head->addJsFile( [ 'sobipro', 'jquery', 'adm.sobipro', 'adm.jmenu', 'jquery-migrate' ] );
		}

		if ( SOBI_CMS != 'joomla3' ) {
			$head->addCssFile( 'bootstrap.bootstrap' )
				->addJsFile( [ 'bootstrap' ] )
				->addCSSCode( '
							#jform_request_SOBI_SELECT_SECTION-lbl { margin-top: 8px; }
                            #jform_request_cid-lbl { margin-top: 8px; }
                            #jform_request_eid-lbl { margin-top: 18px; }
                            #jform_request_sid-lbl { margin-top: 20px; }
                            #jform_request_sptpl-lbl { margin-top: 8px; }
                            .typeahead-width { width: 320px; }
					' );
		}
		else {
			$head->addCSSCode( '
                .typeahead-width { width: 70%; }
            ' );
		}
		// Joomla! 1.5
		$this->cid = Input::Arr( 'cid' );
		// Joomla! 1.6+
		if ( !( count( $this->cid ) && is_numeric( $this->cid[ 0 ] ) ) ) {
			$this->cid = Input::Int( 'id' );
		}
		$this->determineTask();
		$strings = [
			'objects' => [
				'entry'    => Sobi::Txt( 'OTYPE_ENTRY' ),
				'category' => Sobi::Txt( 'OTYPE_CATEGORY' ),
				'section'  => Sobi::Txt( 'OTYPE_SECTION' ),
			],
			'labels'  => [
				'category' => Sobi::Txt( 'SOBI_SELECT_CATEGORY' ),
				'entry'    => Sobi::Txt( 'SOBI_SELECT_ENTRY' )
			],
			'task'    => $this->task
		];
		$strings = json_encode( $strings );

		$head->addJsCode( "SPJmenuFixTask( '{$this->taskName}' );" )
			->addJsFile( 'bootstrap.typeahead' )
			->addJsCode( "var SPJmenuStrings = {$strings}" );
		if ( $this->task != 'list.date' ) {
			if ( SOBI_CMS == 'joomla3' ) {
				$head->addJsCode( 'SobiPro.jQuery( document ).ready( function () { SobiPro.jQuery( "#spCalendar" ).parent().parent().css( "display", "none" ); } );' );
			}
			else {
				$head->addJsCode( 'SobiPro.jQuery( document ).ready( function () { SobiPro.jQuery( "#spCalendar" ).parent().css( "display", "none" ); } );' );
			}
		}
		else {
			$head->addCSSCode( '.SobiProCalendar .chzn-container {width: 100px!important; } ' );
			$head->addCSSCode( '.SobiProCalendar select {width: inherit;} ' );
		}
		parent::__construct();
		$loaded = true;
	}

	protected function determineTask()
	{
		$link = $this->getLink();
		$query = [];
		parse_str( $link, $query );
		$this->task = isset( $query[ 'task' ] ) ? $query[ 'task' ] : null;
		if ( $this->task ) {
			$def = SPFactory::LoadXML( SOBI_PATH . '/metadata.xml' );
			$xdef = new DOMXPath( $def );
			$nodes = $xdef->query( "//option[@value='{$this->task}']" );
			if ( count( $nodes ) ) {
				$this->taskName = 'SobiPro - ' . JText::_( $nodes->item( 0 )->attributes->getNamedItem( 'name' )->nodeValue );
			}
		}
		else {
			$this->taskName = JText::_( 'SP.SOBI_SECTION' );
		}
	}

	protected function getLink()
	{
		static $link = null;
		$data = JFactory::getApplication()->getUserState( 'com_menus.edit.item.data' );
		if ( !( $link ) ) {
			if ( is_array( $data ) && $data[ 'id' ] == $this->cid ) {
				$link = $data[ 'link' ];
			}
			elseif ( SOBI_CMS == 'joomla3' ) {
				$model = JModelLegacy::getInstance( 'MenusModelItem' )
					->getItem();
				$link = $model->link;
			}
			else {
				$link = JModel::getInstance( 'MenusModelItem' )
					->getItem()
					->get( 'link' );
			}
		}

		return str_replace( 'amp;', null, $link );
	}

	public function fetchTooltip( $label, $description, &$node, $control_name = '', $name = '' )
	{
		switch ( $label ) {
			case 'cid':
				if ( $this->task ) {
					return '&nbsp;';
				}
				$label = JText::_( 'SP.SOBI_SELECT_CATEGORY' );
				break;
			case 'SOBI_SELECT_DATE':
				if ( $this->task != 'list.date' ) {
					return null;
				}
				$label = JText::_( 'SP.SOBI_SELECT_ENTRY' );
				break;

		}

		return parent::fetchTooltip( $label, $node->attributes( 'msg' ), $node, $control_name, $name );
	}

	protected function getCat()
	{
		$params = [
			'id'    => 'sp_category',
			'class' => $this->oType == 'category' ? 'btn input-medium btn-primary' : 'btn input-medium',
			'style' => 'width: 300px'
		];
		if ( $this->task && $this->task != 'entry.add' ) {
			$params[ 'disabled' ] = 'disabled';
		}

		return
			'<div class="SobiPro">' .
			SPHtml_Input::button( 'sp_category', $this->oType == 'category' ? $this->oName : Sobi::Txt( 'SOBI_SELECT_CATEGORY' ), $params ) .
			'<div class="spCategoryChooser spModalIframe narrow modal hide" id="spCat">
					<div class="modal-header"><button class="close" data-dismiss="modal">×</button>
						<h3>' . Sobi::Txt( 'SOBI_SELECT_CATEGORY' ) . '</h3>
                </div>
                <div class="modal-body">
                    <div id="spCatsChooser"></div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">' . Sobi::Txt( 'SOBI_CLOSE_WINDOW' ) . '</a>
                    <a href="#" id="spCatSelect" class="btn btn-primary" data-dismiss="modal">' . Sobi::Txt( 'SOBI.JMENU_SAVE' ) . '</a>
                </div>
            </div>
            <input type="hidden" name="selectedCat" id="selectedCat" value=""/>
            <input type="hidden" name="selectedCatName" id="selectedCatName" value=""/>
        </div>';
	}

	private function getEntry()
	{
		$params = [
			'id'    => 'sp_entry',
			'class' => $this->oType == 'entry' ? 'btn input-large btn-primary' : 'btn input-medium',
			'style' => 'margin-top: 10px; width: 300px'
		];
		if ( $this->task ) {
			$params[ 'disabled' ] = 'disabled';
		}

		return
			'<div class="SobiPro">' .
			SPHtml_Input::button(
				'sp_entry',
				$this->oType == 'entry' ? $this->oName : Sobi::Txt( 'SOBI_SELECT_ENTRY' ),
				$params
			) .
			'<div class="modal hide" id="spEntry" overflow: visible;">
					<div class="modal-header"><button class="close" data-dismiss="modal">×</button>
						<h3>' . Sobi::Txt( 'SOBI_SELECT_ENTRY' ) . '</h3>
                </div>
                <div class="modal-body" style="overflow-y: visible;">

                    <label>' . Sobi::Txt( 'SOBI_SELECT_ENTRY_TYPE_TITLE' ) . '</label><input type="text" data-provide="typeahead" autocomplete="off" id="spEntryChooser" class="span6" style="width: 95%" placeholder="' . Sobi::Txt( 'SOBI_SELECT_ENTRY_TYPE' ) . '">

                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">' . Sobi::Txt( 'SOBI_CLOSE_WINDOW' ) . '</a>
                    <a href="#" id="spEntrySelect" class="btn btn-primary" data-dismiss="modal">' . Sobi::Txt( 'SOBI.JMENU_SAVE' ) . '</a>
                </div>
            </div>
            <input type="hidden" name="selectedEntry" id="selectedEntry" value=""/>
            <input type="hidden" name="selectedEntryName" id="selectedEntryName" value=""/>
        </div>';
	}

	public function fetchElement( $name )
	{
		static $sid = 0;
		$selected = 0;
		$db = SPFactory::db();
		if ( !( $sid ) ) {
			$sid = 0;
			if ( $this->cid ) {
				$link = $this->getLink();
				if ( strstr( $link, 'sid' ) ) {
					$query = [];
					parse_str( $link, $query );
					$sid = $query[ 'sid' ];
					if ( isset( $query[ 'sptpl' ] ) ) {
						$this->tpl = $query[ 'sptpl' ];
					}
				}
				if ( $sid ) {
					$section = SPFactory::object( $sid );
					if ( $section->oType == 'section' ) {
						$selected = $section->id;
						$this->section = $selected;
					}
					else {
						$path = [];
						$id = $sid;
						while ( $id > 0 ) {
							try {
								$db->select( 'pid', 'spdb_relations', [ 'id' => $id ] );
								$id = $db->loadResult();
								if ( $id ) {
									$path[] = ( int ) $id;
								}
							}
							catch ( SPException $x ) {
							}
						}
						$path = array_reverse( $path );
						$selected = $path[ 0 ];
						$this->section = $selected;
					}
				}
				else {
					// just to not repeating
					$sid = -1;
				}
			}
		}
		$this->sid = $sid;
		$this->determineObjectType( $sid );
		switch ( $name ) {
			case 'sid':
				$params = [ 'id' => 'sid', 'class' => 'input-mini', 'style' => 'text-align: center; margin-top: 10px; margin-left: 10px;', 'readonly' => 'readonly' ];

				return '<div class="SobiPro" id="jform_request_sid">'
					. SPHtml_Input::text( 'type', $this->oTypeName, [ 'id' => 'otype', 'class' => 'input-medium', 'style' => 'text-align: center; margin-top: 10px;', 'readonly' => 'readonly' ] )
					. SPHtml_Input::text( 'urlparams[sid]', $sid, $params )
					. '</div>';
				break;
			case 'cid':
				return $this->getCat();
				break;
			case 'eid':
				return $this->getEntry();
				break;
			case 'did':
			case 'date':
				return $this->getCalendar();
				break;
			case 'tpl':
			case 'sptpl':
				return $this->getTemplates();
				break;
		}
		$sections = [];
		$sout = [];
		try {
			$sections = $db->select( '*', 'spdb_object', [ 'oType' => 'section' ], 'id' )
				->loadObjectList();
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), $x->getMessage(), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		if ( count( $sections ) ) {
			SPLoader::loadClass( 'models.datamodel' );
			SPLoader::loadClass( 'models.dbobject' );
			SPLoader::loadModel( 'section' );
			$sout[] = Sobi::Txt( 'SOBI_SELECT_SECTION' );
			foreach ( $sections as $section ) {
				if ( Sobi::Can( 'section', 'access', 'valid', $section->id ) ) {
					$s = new SPSection();
					$s->extend( $section );
					$sout[ $s->get( 'id' ) ] = $s->get( 'name' );
				}
			}
		}
		$params = [ 'id' => 'spsection', 'class' => 'required' ];
		$field = SPHtml_Input::select( 'sid', $sout, $selected, false, $params );

		return "<div class=\"SobiPro\" style=\"margin-top: 2px;\">{$field}</div>";
	}

	protected function getCalendar()
	{
		if ( $this->task == 'list.date' ) {
			$link = $this->getLink();
			$query = [];
			parse_str( $link, $query );
			$selected = [ 'year' => null, 'month' => null, 'day' => null ];
			if ( isset( $query[ 'date' ] ) ) {
				$date = explode( '.', $query[ 'date' ] );
				$selected[ 'year' ] = isset( $date[ 0 ] ) && $date[ 0 ] ? $date[ 0 ] : null;
				$selected[ 'month' ] = isset( $date[ 1 ] ) && $date[ 1 ] ? $date[ 1 ] : null;
				$selected[ 'day' ] = isset( $date[ 2 ] ) && $date[ 2 ] ? $date[ 2 ] : null;
			}
			else {
				$query[ 'date' ] = '';
			}
			$months = [ null => Sobi::Txt( 'FMN.HIDDEN_OPT' ) ];
			$monthsNames = Sobi::Txt( 'JS_CALENDAR_MONTHS' );
			$monthsNames = explode( ',', $monthsNames );
			$years = [ null => Sobi::Txt( 'FD.SEARCH_SELECT_LABEL' ) ];
			for ( $i = 1; $i < 12; $i++ ) {
				$months[ $i ] = $monthsNames[ $i - 1 ];
			}
			$days = [ null => Sobi::Txt( 'FMN.HIDDEN_OPT' ) ];

			for ( $i = 1; $i < 32; $i++ ) {
				$days[ $i ] = $i;
			}
			$exYears = SPFactory::db()
				->dselect( 'EXTRACT( YEAR FROM createdTime )', 'spdb_object' )
				->loadResultArray();
			if ( count( $exYears ) ) {
				foreach ( $exYears as $year ) {
					$years[ $year ] = $year;
				}
			}

			return
				'<div class="SobiPro SobiProCalendar">' .
				SPHtml_Input::select( 'sp_year', $years, $selected[ 'year' ] ) .
				SPHtml_Input::select( 'sp_month', $months, $selected[ 'month' ] ) .
				SPHtml_Input::select( 'sp_day', $days, $selected[ 'day' ] ) .
				'<input type="hidden" name="urlparams[date]" id="selectedDate" value="' . trim( $query[ 'date' ] ) . '"/>
				</div>';

		}
		else {
			SPFactory::header()->addJsCode( 'SobiPro.jQuery( document ).ready( function () { SobiPro.jQuery( "#spCalendar" ).parent().css( "display", "none" ); } );' );

			return '<span id="spCalendar"></span>';
		}
	}

	protected function getTemplates()
	{
		$selected = $this->tpl;
		$templates = [];
		$name = $this->tpl ? 'urlparams[sptpl]' : 'urlparams[-sptpl-]';
		$templates[ '' ] = Sobi::Txt( 'SELECT_TEMPLATE_OVERRIDE' );
		$template = SPFactory::db()
			->select( 'sValue', 'spdb_config', [ 'section' => $this->section, 'sKey' => 'template', 'cSection' => 'section' ] )
			->loadResult();
		$templateDir = $this->templatePath( $template );
		$this->listTemplates( $templates, $templateDir, $this->oType );
		$params = [ 'id' => 'sptpl' ];
		$field = SPHtml_Input::select( $name, $templates, $selected, false, $params );

		return "<div class=\"SobiPro\" style=\"margin-top: 2px;\">{$field}</div>";
	}

	protected function templatePath( $tpl )
	{
		$file = explode( '.', $tpl );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$template = SPLoader::path( $file, 'root', false, null );
		}
		else {
			$template = SOBI_PATH . '/usr/templates/' . str_replace( '.', '/', $tpl );
		}

		return $template;
	}

	protected function listTemplates( &$arr, $path, $type )
	{
		switch ( $type ) {
			case 'entry':
			case 'entry.add':
			case 'section':
			case 'category':
			case 'search':
				$path = Sobi::FixPath( $path . '/' . $this->oType );
				break;
			case 'list.user':
			case 'list.date':
				$path = Sobi::FixPath( $path . '/listing' );
				break;
			default:
				if ( strstr( $type, 'list' ) ) {
					$path = Sobi::FixPath( $path . '/listing' );
				}
				break;
		}
		if ( file_exists( $path ) ) {
			$files = scandir( $path );
			if ( count( $files ) ) {
				foreach ( $files as $file ) {
					$stack = explode( '.', $file );
					if ( array_pop( $stack ) == 'xsl' ) {
						$arr[ $stack[ 0 ] ] = $file;
					}
				}
			}
		}
	}

	protected function determineObjectType( $sid )
	{
		if ( $this->task ) {
			$this->oTypeName = Sobi::Txt( 'TASK_' . strtoupper( $this->task ) );
			$this->oType = $this->task;
		}
		elseif ( $sid ) {
			$this->oType = SPFactory::db()
				->select( 'oType', 'spdb_object', [ 'id' => $sid ] )
				->loadResult();
			$this->oTypeName = Sobi::Txt( 'OTYPE_' . strtoupper( $this->oType ) );
		}
		switch ( $this->oType ) {
			case 'entry':
				$this->oName = SPFactory::Entry( $sid )
					->get( 'name' );
				break;
			case 'section':
				$this->oName = SPFactory::Section( $sid )
					->get( 'name' );
				break;
			case 'category':
				$this->oName = SPFactory::Category( $sid )
					->get( 'name' );
				break;
			default:
				$this->oName = null;
				break;
		}
	}
}
