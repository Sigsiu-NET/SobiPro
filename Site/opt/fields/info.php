<?php
/**
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.inbox' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Sep-2009 12:52:45 PM
 */
class SPField_Info extends SPField_Inbox implements SPFieldInterface
{
	/** * @var string */
	protected $cssClass = 'spClassInfo';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditInfo';
	/** * @var string */
	protected $cssClassView = 'spClassViewInfo';
	/** @var string  */
	protected $viewInfo = '';
	/** @var string  */
	protected $entryInfo = '';
	/** * @var int */
	protected $bsWidth = 10;
	/** @var bool  */
	static private $CAT_FIELD = true;
	/*** @var bool */
	protected $suggesting = false;


	public function __construct ( &$field ) {
		parent::__construct( $field );
		$this->viewInfo = SPLang::getValue( $this->nid . '-viewInfo', 'field_information', Sobi::Section(), null, null, $this->fid );
		$this->entryInfo = SPLang::getValue( $this->nid . '-entryInfo', 'field_information', Sobi::Section(),  null, null, $this->fid );
	}


	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$data = SPLang::getValue( $this->nid . '-entryInfo', 'field_information', Sobi::Section(), null, null, $this->fid );

		$class = $this->cssClass . (strlen($this->cssClassEdit) ? ' ' . $this->cssClassEdit : '');
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ($this->bsWidth) {
				$width = SPHtml_Input::_translateWidth($this->bsWidth);
				$class .=  ' ' . $width;
			}
		}
		$field = '<div class="' . $class . '">' . $data . '</div>';
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	/**
	 * @return array
	 * Ausgabe des Feldes in DV and vCard
	 */
	public function struct()
	{
		$data = SPLang::getValue( $this->nid . '-viewInfo', 'field_information', Sobi::Section(), null, null, $this->fid );

		$attributes = [];
		if ( strlen( $data ) ) {
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
			$this->cssClass = $this->cssClass . ' ' . $this->nid;
			$this->cleanCss();
			$attributes = [
				'lang' => Sobi::Lang(),
				'class' => $this->cssClass
			];
		}
		else {
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spField';
		}

		return [
			'_complex' => 1,
			'_data' => $data,
			'_attributes' => $attributes
		];
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @throws SPException
	 * @return string
	 */
	private function verify( $entry, $request )
	{
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsId
	 * @param string $request
	 * @return array
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' )
	{
		return [];
	}

	/**
	 * Returns the parameter list
	 * @return array
	 * Speichern der nicht übersetzbaren Elemente
	 */
	protected function getAttr()
	{
		return [ 'cssClass', 'cssClassView', 'cssClassEdit', 'showEditLabel', 'bsWidth' ];
	}

	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @param string $request
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' )
	{
		return false;
	}

	/**
	 * @param $request
	 * @param $section
	 * @return bool
	 */
	public function searchData( $request, $section )
	{
		return false;
	}
}
