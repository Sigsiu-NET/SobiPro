<?php
/**
 * @version: $Id: chbxgroup.php 2075 2011-12-15 14:03:18Z Radek Suski $
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
 * $Date: 2011-12-15 15:03:18 +0100 (Thu, 15 Dec 2011) $
 * $Revision: 2075 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/fields/chbxgroup.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.radio' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 27-Nov-2009 17:10:15
 */
class SPField_ChbxGr extends SPField_Radio implements SPFieldInterface
{
	/**
	 * @var string
	 */
	protected $cssClass = '';
	/**
	 * @var bool
	 */
	protected $multi = true;
	/**
	 * @var string
	 */
	protected $dType = 'predefined_multi_data_multi_choice';

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
		$class = $this->required ? $this->cssClass . ' required' : $this->cssClass;
		$params = array( 'class' => $class );
		$values = array();
		if ( count( $this->options ) ) {
			foreach ( $this->options as $option ) {
				$values[ $option[ 'id' ] ] = $option[ 'label' ];
			}
		}
		$selected = $this->getRaw();
		if ( is_array( $selected ) && !( is_string( $selected ) ) && count( $selected ) ) {
			$selected = array_merge( $selected, array_keys( $selected ) );
		}
		$list = SPHtml_Input::checkBoxGroup( $this->nid, $values, $this->nid, $selected, $params, $this->labelSite, true );
		$field = null;
		if ( count( $list ) ) {
			$c = 0;
			foreach ( $list as $box ) {
				$box = '<div style="float:left; width:' . $this->optWidth . 'px;">' . $box . '</div>';
				$field .= "\n" . $box;
				if ( !( ( ++$c ) % $this->optInLine ) ) {
					$field .= "\n<div style=\"clear:both;\"></div>\n";
				}
			}
			$field = "<div id=\"{$this->nid}\" class=\"{$class}\">{$field}\n<div style=\"clear:both;\"></div>\n</div>";
		}
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	/**
	 * Returns meta description
	 */
	public function metaDesc()
	{
		return $this->metaKeys();
	}

	/**
	 * Returns meta keys
	 */
	public function metaKeys()
	{
		$data = $this->getRaw();
		return ( $this->addToMetaKeys && is_array( $data ) && count( $data ) ) ? implode( ', ', $this->getRaw() ) : null;
	}

	/**
	 * Get field specific values if these are in an other table
	 * @param $sid - id of the entry
	 * @param $fullData - the database row form the spdb_field_data table
	 * @param $rawData - raw data of the field content
	 * @param $fData - full formated data of the field content
	 * @return void
	 */
	public function loadData( $sid, &$fullData, &$rawData, &$fData )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		static $lang = null;
		if ( !( $lang ) ) {
			$lang = Sobi::Lang( false );
		}
		$table = $db->join(
			array(
				array( 'table' => 'spdb_field_option_selected', 'as' => 'sdata', 'key' => 'fid' ),
				array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ),
				array( 'table' => 'spdb_language', 'as' => 'ldata', 'key' => array( 'sdata.optValue', 'ldata.sKey' ) ),
			)
		);
		try {
			$db->select(
				'*, sdata.copy as scopy',
				$table,
				array(
					'sdata.fid' => $this->id,
					'sdata.sid' => $sid,
					'fdata.sid' => $sid,
					'ldata.oType' => 'field_option',
					'ldata.fid' => $this->id,
				),
				'scopy', 0, 0, true /*, 'sdata.optValue' */
			);
			$data = $db->loadObjectList();
			if ( $data && count( $data ) ) {
				$order = SPFactory::cache()->getVar( 'order_' . $this->nid );
				if ( !( $order ) ) {
					$db->select( 'optValue', 'spdb_field_option', array( 'fid' => $this->id ), 'optPos' );
					$order = $db->loadResultArray();
					SPFactory::cache()->addVar( $order, 'order_' . $this->nid );
				}
				$rawData = array();
				$sRawData = array();
				$copied = false;
				// check which version the user may see
				$copy = $this->checkCopy();
				foreach ( $data as $selected ) {
					// if there was at least once copy
					if ( $selected->scopy ) {
						$copied = true;
					}
				}
				// check what we should show
				$remove = ( int )$copied && $copy;
				foreach ( $data as $selected ) {
					if ( $selected->scopy == $remove ) {
						if ( !( isset( $rawData[ $selected->optValue ] ) ) || $selected->language == $lang ) {
							$rawData[ $selected->optValue ] = $selected->sValue;
						}
					}
				}
				foreach ( $order as $opt ) {
					if ( isset( $rawData[ $opt ] ) ) {
						$sRawData[ ] = $rawData[ $opt ];
					}
				}
				$fData = implode( "</li>\n\t<li>", $sRawData );
				$fData = "<ul id=\"{$this->nid}\" class=\"{$this->cssClass}\">\n\t<li>{$fData}</li>\n</ul>\n";
				$fullData->baseData = $fData;
			}
		} catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_GET_SELECTED_OPTION', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/* (non-PHPdoc)
	 * @see Site/opt/fields/SPField_Select#fetchData($request)
	 */
	protected function fetchData( $request )
	{
		if ( is_array( $request ) && count( $request ) ) {
			$selected = array();
			foreach ( $request as $opt ) {
				/* check if such option exist at all */
				if ( !( isset( $this->optionsById[ $opt ] ) ) ) {
					throw new SPException( SPLang::e( 'FIELD_NO_SUCH_OPT', $opt, $this->name ) );
				}
				$selected[ ] = preg_replace( '/^[a-z0-9]\.\-\_/ei', null, $opt );
			}
			return $selected;
		}
		else {
			return array();
		}
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$baseData = $this->getRaw();
		$list = array();
		$struct = array();
		$order = SPFactory::cache()->getVar( 'order_' . $this->nid );
		if ( !( $order ) ) {
			$order = SPFactory::db()->select( 'optValue', 'spdb_field_option', array( 'fid' => $this->id ), 'optPos' )->loadResultArray();
			SPFactory::cache()->addVar( $order, 'order_' . $this->nid );
		}
		if ( is_array( $baseData ) && count( $baseData ) ) {
			$this->cssClass = ( strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData' );
			$this->cssClass = $this->cssClass . ' ' . $this->nid;
			$this->cleanCss();
			foreach ( $order as $opt ) {
				if ( isset( $baseData[ $opt ] ) ) {
					$list[ ] = array( '_tag' => 'li', '_value' => SPLang::clean( $baseData[ $opt ] ), '_class' => $opt, /*'_id' => trim( $this->nid.'_'.strtolower( $opt ) )*/ );
				}
			}
			foreach ( $this->options as $opt ) {
				$struct[ ] = array(
					'_complex' => 1,
					'_data' => $opt[ 'label' ],
					'_attributes' => array( 'selected' => ( isset( $baseData[ $opt[ 'id' ] ] ) ? 'true' : 'false' ), 'id' => $opt[ 'id' ], 'position' => $opt[ 'position' ] )
				);
			}
			$data = array(
				'ul' => array(
					'_complex' => 1,
					'_data' => $list,
					'_attributes' => array( /* 'id' => $this->nid, */
						'class' => $this->cssClass ) )
			);
		}
		if ( count( $list ) ) {
			return array(
				'_complex' => 1,
				'_data' => $data,
				'_attributes' => array( 'lang' => $this->lang, 'class' => $this->cssClass ),
				'_options' => $struct,
			);
		}
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return array( 'optInLine', 'labelSite', 'optWidth', 'searchMethod' );
	}
}

?>
