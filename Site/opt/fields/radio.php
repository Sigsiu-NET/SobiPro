<?php
/**
 * @package: SobiPro Component for Joomla!
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.select' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 26-Nov-2009 14:33:03
 */
class SPField_Radio extends SPField_Select implements SPFieldInterface
{
	/** * @var int */
	protected $bsWidth = 10;
	/** * @var int */
	protected $bsSearchWidth = 9;
	/** * @var int */
	protected $optInLine = 3;
	/** * @var int */
	protected $optWidth = 150;
	/** * @var string */
	protected $labelSite = 'right';
	/** * @var string */
	protected $cssClass = 'spClassRadio';
	/** * @var string */
	protected $cssClassView = 'spClassViewRadio';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditRadio';
	/** * @var string */
	protected $cssClassSearch = 'spClassSearchRadio';
	/** * @var string */
	protected $searchMethod = 'general';
	/** * @var int */
	protected $ssize = 1;
	/** * @var string */
	protected $dType = 'predefined_multi_data_single_choice';
	/** * @var string */
	protected $defSel = '';
	/** * @var string */
	protected $itemprop = '';
	/** * @var string */
	protected $metaSeparator = ' ';
	/** @var bool */
	static private $CAT_FIELD = true;
	/*** @var bool */
	protected $suggesting = false;

	/**
	 * Shows the field in the edit entry or add entry form
	 *
	 * @param bool $return return or display directly
	 *
	 * @return string
	 */
	public function field( $return = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$class = $this->required ? $this->cssClass . ' required' : $this->cssClass;
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( $this->bsWidth ) {
				$width = SPHtml_Input::_translateWidth( $this->bsWidth );
				$class .= ' ' . $width;
			}
		}
		$field = $this->getField( $class );
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	private function getField( $class, $selected = null )
	{
		$params = [ 'class' => $class ];
		$selected = $selected ? $selected : $this->getRaw();
		$selected = $selected ? $selected : $this->defSel;

		$this->labelSite = 'right';
		$list = SPHtml_Input::radioList( $this->nid, $this->getValues(), $this->nid, $selected, $params, $this->labelSite, true );
		$field = null;
		if ( count( $list ) ) {
			$c = 0;
			foreach ( $list as $radio ) {
				$field .= '<div class="spFieldRadio"';
				if ( $this->optWidth ) {
					$field .= ' style="width:' . $this->optWidth . 'px;"';
				}
				$field = $field . '>' . $radio . '</div>';
				$field .= "\n";

				//$radio = '<div style="width:'.$this->optWidth.'px;" class="spFieldRadio">'.$radio.'</div>';
				//$field .= "\n".$radio;
				if ( $this->optInLine ) {
					if ( !( ( ++$c ) % $this->optInLine ) ) {
						$field .= "\n<div class=\"clearfix\"></div>\n";
					}
				}
			}
			$field .= "\n<div class=\"clearfix\"></div>\n"; //another clear at the end
			$field = "<div id=\"{$this->nid}\" class=\"{$class}\">{$field}</div>";
		}

		return $field;
	}

	private function getValues()
	{
		$values = [];
		if ( count( $this->options ) ) {
			foreach ( $this->options as $option ) {
				$values[ $option[ 'id' ] ] = $option[ 'label' ];
			}
		}

		return $values;
	}

	/**
	 * Shows the field in the search form
	 *
	 * @param bool $return return or display directly
	 *
	 * @return string
	 */
	public function searchForm( $return = false )
	{
		$data = $this->getValues();
		$field = null;
		switch ( $this->searchMethod ) {
			default:
			case 'general':
				$field = false;
				break;
			case 'chbx':
				$list = SPHtml_Input::checkBoxGroup( $this->nid, $data, $this->nid, $this->_selected, [ 'class' => $this->cssClass . ' ' . Sobi::Cfg( 'search.form_checkbox_def_css', 'SPSearchChbx' ) ], 'right', true );
				if ( count( $list ) ) {
					$c = 0;
					foreach ( $list as $box ) {
						$box = '<div class="spFieldCheckbox" style="width:' . $this->optWidth . 'px;">' . $box . '</div>';
						$field .= "\n" . $box;
						if ( $this->optInLine ) {
							if ( !( ( ++$c ) % $this->optInLine ) ) {
								$field .= "\n<div class=\"clearfix\"></div>\n";
							}
						}
					}
					$field = "<div id=\"{$this->nid}\" >{$field}</div>";
					$field .= "\n<div class=\"clearfix\"></div>\n";
				}
				break;
			case 'radio':
				$field = $this->getField( $this->cssClass . ' ' . Sobi::Cfg( 'search.form_radio_def_css', 'SPSearchRadio' ), $this->_selected );
				$field .= "\n<div class=\"clearfix\"></div>\n";
				break;
			case 'select':
			case 'mselect':
				$label = ( $this->selectLabel ) ? Sobi::Txt( $this->selectLabel, $this->name ) : Sobi::Txt( 'FMN.SEARCH_SELECT_LIST', [ 'name' => $this->name ] );
				$params = [ 'id' => $this->nid, 'size' => $this->ssize, 'class' => $this->cssClass . ' ' . Sobi::Cfg( 'search.form_list_def_css', 'SPSearchSelect' ) ];
				$data = array_merge( [ '' => $label ], $data );
				$field = SPHtml_Input::select( $this->nid, $data, $this->_selected, ( $this->searchMethod == 'mselect' ), $params );
				break;
		}

		return $field;
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
//		return array( 'optInLine', 'labelSite', 'optWidth', 'searchMethod', 'defSel', 'itemprop', 'metaSeparator', 'cssClassView', 'cssClassSearch', 'cssClassEdit', 'showEditLabel' );
		return [ 'suggesting', 'optInLine', 'optWidth', 'searchMethod', 'defSel', 'itemprop', 'metaSeparator', 'cssClassView', 'cssClassSearch', 'cssClassEdit', 'showEditLabel', 'bsSearchWidth', 'ssize', 'selectLabel' ];
	}
}
