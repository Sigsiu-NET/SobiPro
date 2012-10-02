<?php
/**
 * @version: $Id: radio.php 1434 2011-05-28 13:05:13Z Radek Suski $
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
 * $Date: 2011-05-28 15:05:13 +0200 (Sat, 28 May 2011) $
 * $Revision: 1434 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/fields/radio.php $
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
	/**
	 * @var int
	 */
	protected $optInLine = 3;
	/**
	 * @var int
	 */
	protected $optWidth = 150;
	/**
	 * @var string
	 */
	protected $labelSite = 'right';
	/**
	 * @var string
	 */
	public $cssClass = "";
	/**
	 * @var string
	 */
	protected $searchMethod = 'general';
	/**
	 * @var string
	 */
	protected $dType = 'predefined_multi_data_single_choice';
	/**
	 * @var string
	 */
	protected $defSel = '';


	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		if( !( $this->enabled ) ) {
			return false;
		}
		$class =  $this->required ? $this->cssClass.' required' : $this->cssClass;
		$field = $this->getField( $class );
		if( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	private function getField( $class, $selected = null )
	{
		$params = array( 'class' => $class );
		$selected = $selected ? $selected : $this->getRaw();
		$selected = $selected ? $selected : $this->defSel;
		$list = SPHtml_Input::radioList( $this->nid, $this->getValues(), $this->nid, $selected, $params, $this->labelSite, true );
		$field = null;
		if( count( $list ) ) {
			$c = 0;
			foreach ( $list as $radio ) {
				$radio = '<div style="float:left; width:'.$this->optWidth.'px;" >'.$radio.'</div>';
				$field .= "\n".$radio;
				if( !( ( ++$c ) % $this->optInLine ) ) {
					$field .= "\n<div style=\"clear:both;\"></div>\n";
				}
			}
			$field = "<div id=\"{$this->nid}\" class=\"{$class}\">{$field}</div>";
		}
		return $field;
	}

	private function getValues()
	{
		$values = array();
		if( count( $this->options ) ) {
			foreach ( $this->options as $option ) {
				$values[ $option[ 'id' ] ] = $option[ 'label' ];
			}
		}
		return $values;
	}

	/**
	 * Shows the field in the search form
	 * @param bool $return return or display directly
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
				$list = SPHtml_Input::checkBoxGroup( $this->nid, $data, $this->nid, $this->_selected, array( 'class' => $this->cssClass.' '.Sobi::Cfg( 'search.form_checkbox_def_css', 'SPSearchChbx' ) ), $this->labelSite, true );
				if( count( $list ) ) {
					$c = 0;
					foreach ( $list as $box ) {
						$box = '<div style="float:left; width:'.$this->optWidth.'px;">'.$box.'</div>';
						$field .= "\n".$box;
						if( !( ( ++$c ) % $this->optInLine ) ) {
							$field .= "\n<div style=\"clear:both;\"></div>\n";
						}
					}
					$field = "<div id=\"{$this->nid}\" >{$field}</div>";
					$field .= "\n<div style=\"clear:both;\"></div>\n";
				}
				break;
			case 'radio':
				$field = $this->getField( $this->cssClass.' '.Sobi::Cfg( 'search.form_radio_def_css', 'SPSearchRadio' ), $this->_selected );
				$field .= "\n<div style=\"clear:both;\"></div>\n";
				break;
			case 'select':
			case 'mselect':
				$params = array( 'id' => $this->nid, 'size' => ( $this->searchMethod == 'mselect'  ? $this->optInLine : 1 ), 'class' => $this->cssClass.' '.Sobi::Cfg( 'search.form_list_def_css', 'SPSearchSelect' ) );
				$data = array_merge( array( '' => Sobi::Txt( 'FD.SEARCH_SELECT_LIST', array( 'name' => $this->name ) ) ), $data );
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
		return array( 'optInLine', 'labelSite', 'optWidth', 'searchMethod', 'defSel' );
	}
}
?>
