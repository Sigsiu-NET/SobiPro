<?php
/**
 * @version: $Id$
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

include_once JPATH_ADMINISTRATOR . '/components/com_menus/tables/menu.php';

class JFormFieldNative extends JFormField
{
	protected $params = [];
	static $sid = 0;
	static $functionsLabel = 0;
	static $section = 0;
	static $mid = 0;

	public function __construct()
	{
		$this->initialise();
		parent::__construct();
	}

	private function initialise()
	{
		static $loaded = false;
		if ( !( $loaded ) || true ) {
			defined( 'DS' ) || define( 'DS', '/' );
			require_once( JPATH_SITE . '/components/com_sobipro/lib/sobi.php' );
			Sobi::Initialise();
			if ( SOBI_CMS == 'joomla3' ) {
				SPFactory::header()
						->initBase( true )
						->addJsFile( [ 'sobipro', 'jqnc', 'adm.sobipro', 'adm.jnmenu', 'jquery-base64' ] );
			}
			else {
				SPFactory::header()
						->initBase( true )
						->addJsFile( [ 'sobipro', 'jquery', 'adm.sobipro', 'adm.jnmenu', 'jquery-migrate', 'jquery-base64' ] )
						->addCSSCode( '#toolbar-box { display: block }' );
			}
			$loaded = true;
			SPLoader::loadClass( 'mlo.input' );
			SPLoader::loadClass( 'models.datamodel' );
			SPLoader::loadClass( 'models.dbobject' );
			SPLoader::loadModel( 'section' );
			$model = JModelLegacy::getInstance( 'MenusModelItem' )
					->getItem();

			self::$mid = $model->id;
			if ( isset( $model->params[ 'SobiProSettings' ] ) && strlen( $model->params[ 'SobiProSettings' ] ) ) {
				$this->params = json_decode( base64_decode( $model->params[ 'SobiProSettings' ] ) );
			}
			$jsString = json_encode(
					[
							'component' => Sobi::Txt( 'SOBI_NATIVE_TASKS' ),
							'buttonLabel' => Sobi::Txt( 'SOBI_SELECT_FUNCTIONALITY' )
					]
			);
			SPFactory::header()
					->addJsCode( "SpStrings = {$jsString}; " );
		}
	}

	protected function getInput()
	{
		if ( !( self::$sid ) ) {
			self::$sid = isset( $this->params->sid ) ? $this->params->sid : $this->value;
			$link = $this->form->getValue( 'link' );
			if ( !( self::$sid ) && strlen( $link ) ) {
				parse_str( $link, $request );
				if ( isset( $request[ 'sid' ] ) && $request[ 'sid' ] ) {
					self::$sid = $request[ 'sid' ];
				}
			}
			$path = SPFactory::config()->getParentPath( self::$sid );
			self::$section = $path[ 0 ];
			$this->getFunctionsLabel();
		}
		return $this->fieldname == 'sid' ? $this->loadSection() : $this->loadAdvanced();
	}

	protected function getFunctionsLabel()
	{
		if ( isset( $this->params->interpreter ) ) {
			$interpreter = explode( '.', $this->params->interpreter );
			$function = array_pop( $interpreter );
			$obj = SPFactory::Instance( implode( '.', $interpreter ) );
			self::$functionsLabel = $obj->$function( self::$sid, self::$section );
		}
		elseif ( isset( $this->params->text ) ) {
			if ( isset( $this->params->loadTextFile ) ) {
				SPLang::load( $this->params->loadTextFile );
			}
			self::$functionsLabel = Sobi::Txt( $this->params->text );
		}
	}

	protected function loadSection()
	{
		$sections = [];
		$sectionsOutput = [];
		try {
			$sections = SPFactory::db()
					->select( '*', 'spdb_object', [ 'oType' => 'section' ], 'id' )
					->loadObjectList();
		} catch ( SPException $x ) {
			Sobi::Error( $this->name(), $x->getMessage(), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		if ( count( $sections ) ) {
			$sectionsOutput[ ] = Sobi::Txt( 'SOBI_SELECT_SECTION' );
			foreach ( $sections as $section ) {
				if ( Sobi::Can( 'section', 'access', 'valid', $section->id ) ) {
					$s = new SPSection();
					$s->extend( $section );
					$sectionsOutput[ $s->get( 'id' ) ] = $s->get( 'name' );
				}
			}
		}
		$params = [ 'id' => 'SobiSection', 'class' => 'required' ];
		return SPHtml_Input::select( 'section', $sectionsOutput, self::$section, false, $params );
	}

	protected function loadAdvanced()
	{
		$label = self::$functionsLabel ? self::$functionsLabel : JText::_( 'SP.SOBI_SELECT_FUNCTIONALITY' );
		return
				'<div class="SobiPro">' .
				'	<div id="SobiProSelector" class="btn btn-primary" data-mid="' . self::$mid . '">' .
				'		<i class="icon-expand"></i>&nbsp;<span id="SobiProSelectedFunction">' . $label . '</span>' .
				'	</div>' .
				'   <div class="modal hide" id="SobiProModal" >' .
				'       <div class="modal-header"><button class="close" data-dismiss="modal">×</button>' .
				'           <h3>' . Sobi::Txt( 'SOBI_SELECT_FUNCTIONALITY' ) . '</h3>' .
				'       </div>' .
				'       <div class="modal-body">' .
				'       <i class="icon-spinner icon-spin icon-large"></i>' .
				'       </div>' .
				'       <div class="modal-footer">' .
				'           <a href="#" class="btn btn-danger pull-left ctrl-clear" data-dismiss="modal">' . Sobi::Txt( 'SOBI_MENU_CLEAR' ) . '</a>' .
				'           <a href="#" class="btn" data-dismiss="modal">' . Sobi::Txt( 'SOBI_CLOSE_WINDOW' ) . '</a>' .
				'           <a href="#" class="btn btn-primary ctrl-save" data-dismiss="modal">' . Sobi::Txt( 'SOBI.JMENU_SAVE' ) . '</a>' .
				'       </div>' .
				'   </div>' .
				'   <input type="hidden" id="selectedSid" name="jform[request][sid]" value="' . self::$sid . '"/>' .
				'</div>';
	}
}
