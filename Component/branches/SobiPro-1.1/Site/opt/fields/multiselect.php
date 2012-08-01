<?php
/**
 * @version: $Id: multiselect.php 2075 2011-12-15 14:03:18Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/fields/multiselect.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.select' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 26-Nov-2009 14:33:03
 */
class SPField_MultiSelect extends SPField_Select implements SPFieldInterface
{
	/**
	 * @var bool
	 */
	protected $multi = true;
	/**
	 * @var int
	 */
	protected $size =  10;
	/**
	 * @var string
	 */
	protected $dType = 'predefined_multi_data_multi_choice';


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
		if( !( $lang ) ) {
			$lang = Sobi::Lang( false );
		}
		$table = $db->join(
			array(
				array( 'table' => 'spdb_field_option_selected', 'as' => 'sdata', 'key' => 'fid' ),
				array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ),
				array( 'table' => 'spdb_language', 'as' => 'ldata', 'key' => array( 'sdata.optValue','ldata.sKey' ) ),
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
				'scopy', 0,  0, true /*, 'sdata.optValue' */
			);
			$data = $db->loadObjectList();
			$order = SPFactory::cache()->getVar( 'order_'.$this->nid );
			if( !( $order ) ) {
				$db->select( 'optValue', 'spdb_field_option', array( 'fid' => $this->id ), 'optPos' );
				$order = $db->loadResultArray();
				SPFactory::cache()->addVar( $order, 'order_'.$this->nid );
			}
			// check which version the user may see
			$copy = $this->checkCopy();
			if( $data && count( $data ) ) {
				$rawData = array();
				$sRawData = array();
				$copied = false;
				foreach ( $data as $selected ) {
					// if there was at least once copy
					if( $selected->scopy ) {
						$copied = true;
					}
				}
				// check what we should show
				$remove = ( int ) $copied && $copy;
				foreach ( $data as $selected ) {
					if(  $selected->scopy == $remove ) {
						// if not already set or the language fits better
						if( !( isset( $rawData[ $selected->optValue ] ) ) || $selected->language == $lang )	{
							$rawData[ $selected->optValue ] = $selected->sValue;
						}
					}
				}
				foreach ( $order as $opt ) {
					if( isset( $rawData[ $opt ] ) ) {
						$sRawData[] = $rawData[ $opt ];
					}
				}
				$fData = implode( "</li>\n\t<li>", $sRawData );
				$fData = "<ul id=\"{$this->nid}\" class=\"{$this->cssClass}\">\n\t<li>{$fData}</li>\n</ul>\n";
				$fullData->baseData = $fData;
			}
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_SELECTED_OPTIONS', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * Returns meta description
	 */
	public function metaDesc()
	{
		return ( $this->addToMetaDesc && count( $this->getRaw() ) ) ? implode( ', ', $this->getRaw() ) : null;
	}

	/**
	 * Returns meta keys
	 */
	public function metaKeys()
	{
		return ( $this->addToMetaKeys && count( $this->getRaw() ) ) ? implode( ', ', $this->getRaw() ) : null;
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$baseData = $this->getRaw();
		$list = array();
		$order = SPFactory::cache()->getVar( 'order_'.$this->nid );
		if( !( $order ) ) {
			$order = SPFactory::db()->select( 'optValue', 'spdb_field_option', array( 'fid' => $this->id ), 'optPos' )->loadResultArray();
			SPFactory::cache()->addVar( $order, 'order_'.$this->nid );
		}
		if( is_array( $baseData ) && count( $baseData ) ) {
			$this->cssClass = ( strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData' );
			$this->cssClass = $this->cssClass.' '.$this->nid;
			$this->cleanCss();
			foreach ( $order as $opt ) {
				if( isset( $baseData[ $opt ] ) ) {
					$list[] = array( '_tag' => 'li', '_value' => SPLang::clean( $baseData[ $opt ] ), '_class' => $opt,/* '_id' => trim( $this->nid.'_'.strtolower( $opt ) )*/ );
				}
			}
			foreach ( $this->options as $opt ) {
				$struct[] = array(
					'_complex' => 1,
					'_data' => $opt[ 'label' ],
					'_attributes' => array( 'selected' => ( isset( $baseData[ $opt[ 'id' ] ] ) ? 'true' : 'false' ), 'id' => $opt[ 'id' ], 'position' => $opt[ 'position' ] )
				);
			}
			$data = array(
				'ul' => array(
				'_complex' => 1,
				'_data' => $list,
				'_attributes' => array( /* 'id' => $this->nid, */ 'class' => $this->cssClass ) )
			);
		}
		if( count( $list ) ) {
			return array(
				'_complex' => 1,
				'_data' => $data,
				'_attributes' => array( 'lang' => $this->lang , 'class' => $this->cssClass),
				'_options' => $struct,
			);
		}
	}

	/* (non-PHPdoc)
	 * @see Site/opt/fields/SPField_Select#fetchData($request)
	 */
	protected function fetchData( $request )
	{
		if( is_array( $request ) && count( $request ) ) {
			$selected = array();
			foreach ( $request as $opt ) {
				/* check if such option exist at all */
				if( !( isset( $this->optionsById[ $opt ] ) ) ) {
					throw new SPException( SPLang::e( 'FIELD_NO_SUCH_OPT', $opt, $this->name ) );
				}
				$selected[] = preg_replace( '/^[a-z0-9]\.\-\_/ei', null, $opt );
			}
			return $selected;
		}
		else {
			return array();
		}
	}

	/**
	 * Static function to create the right SQL-Query if a entries list should be sorted by this field
	 * @param string $tables - table or tables join
	 * @param array $conditions - array with conditions
	 * @param string $oPrefix
	 * @param string $eOrder
	 * @param string $eDir
	 * @return void
	 */
	public static function sortBy()
	{
		return false;
	}

	protected function required( &$values )
	{
		return false;
	}
}
?>
