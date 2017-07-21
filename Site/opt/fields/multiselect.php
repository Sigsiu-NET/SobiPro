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
class SPField_MultiSelect extends SPField_Select implements SPFieldInterface
{
	/*** @var bool */
	protected $multi = true;
	/*** @var int */
	protected $size = 10;
	/*** @var string */
	protected $dType = 'predefined_multi_data_multi_choice';
	/** * @var string */
	protected $cssClass = 'spClassMSelect';
	/** * @var string */
	protected $cssClassView = 'spClassViewMSelect';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditMSelect';
	/** * @var string */
	protected $cssClassSearch = 'spClassSearchMSelect';
	/** @var bool  */
	static private $CAT_FIELD = true;
	/*** @var bool */
	protected $suggesting = false;

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
				[
						[ 'table' => 'spdb_field_option_selected', 'as' => 'sdata', 'key' => 'fid' ],
						[ 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ],
						[ 'table' => 'spdb_language', 'as' => 'ldata', 'key' => [ 'sdata.optValue', 'ldata.sKey' ] ],
				]
		);
		try {
			$db->select(
					'*, sdata.copy as scopy',
					$table,
					[
							'sdata.fid' => $this->id,
							'sdata.sid' => $sid,
							'fdata.sid' => $sid,
							'ldata.oType' => 'field_option',
							'ldata.fid' => $this->id,
					],
					'scopy', 0, 0, true /*, 'sdata.optValue' */
			);
			$data = $db->loadObjectList();
			$order = SPFactory::cache()->getVar( 'order_' . $this->nid );
			if ( !( $order ) ) {
				$db->select( 'optValue', 'spdb_field_option', [ 'fid' => $this->id ], 'optPos' );
				$order = $db->loadResultArray();
				SPFactory::cache()->addVar( $order, 'order_' . $this->nid );
			}
			// check which version the user may see
			$copy = $this->checkCopy();
			if ( $data && count( $data ) ) {
				$rawData = [];
				$sRawData = [];
				$copied = false;
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
						// if not already set or the language fits better
						if ( !( isset( $rawData[ $selected->optValue ] ) ) || $selected->language == $lang ) {
							$rawData[ $selected->optValue ] = $selected->sValue;
						}
					}
				}
				foreach ( $order as $id => $opt ) {
					if ( isset( $rawData[ $opt ] ) ) {
						$sRawData[ ] = $rawData[ $opt ];
						$this->_selected[ $id ] = $opt;
					}
				}
				$fData = implode( "</li>\n\t<li>", $sRawData );
				$fData = "<ul id=\"{$this->nid}\" class=\"{$this->cssClass}\">\n\t<li>{$fData}</li>\n</ul>\n";
				$fullData->baseData = $fData;
			}
		} catch ( SPException $x ) {
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
		$list = [];
		$order = SPFactory::cache()->getVar( 'order_' . $this->nid );
		if ( !( $order ) ) {
			$order = SPFactory::db()->select( 'optValue', 'spdb_field_option', [ 'fid' => $this->id ], 'optPos' )->loadResultArray();
			SPFactory::cache()->addVar( $order, 'order_' . $this->nid );
		}
		$this->cssClass = ( strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData' );
		$this->cssClass = $this->cssClass . ' ' . $this->nid;
		$this->cleanCss();
		foreach ( $order as $opt ) {
			if ( isset( $baseData[ $opt ] ) ) {
				$list[ ] = [ '_tag' => 'li', '_value' => SPLang::clean( $baseData[ $opt ] ), '_class' => $opt, /* '_id' => trim( $this->nid.'_'.strtolower( $opt ) )*/ ];
			}
		}
		foreach ( $this->options as $opt ) {
			if ( isset( $opt[ 'options' ] ) && is_array( $opt[ 'options' ] ) ) {
				foreach ( $opt[ 'options' ] as $sub ) {
					$struct[ ] = [
							'_complex' => 1,
							'_data' => $sub[ 'label' ],
							'_attributes' => [ 'group' => $opt[ 'id' ], 'selected' => ( isset( $baseData[ $sub [ 'id' ] ] ) ? 'true' : 'false' ), 'id' => $sub[ 'id' ], 'position' => $sub[ 'position' ] ]
					];
//						$group[ ] = array(
//							'_complex' => 1,
//							'_data' => $sub[ 'label' ],
//							'_tag' => 'option',
//							'_attributes' => array( 'selected' => ( isset( $baseData[ $sub[ 'id' ] ] ) ? 'true' : 'false' ), 'id' => $sub[ 'id' ], 'position' => $sub[ 'position' ] )
//						);
				}
			}
			else {
				$struct[ ] = [
						'_complex' => 1,
						'_data' => $opt[ 'label' ],
						'_attributes' => [ 'selected' => ( isset( $baseData[ $opt[ 'id' ] ] ) ? 'true' : 'false' ), 'id' => $opt[ 'id' ], 'position' => $opt[ 'position' ] ]
				];
			}
		}
		$data = [
				'ul' => [
						'_complex' => 1,
						'_data' => $list,
						'_attributes' => [ 'class' => $this->cssClass ] ]
		];
		return [
				'_complex' => 1,
				'_data' => count( $list ) ? $data : null,
				'_attributes' => [ 'lang' => $this->lang, 'class' => $this->cssClass ],
				'_options' => $struct,
		];

	}

	/* (non-PHPdoc)
	 * @see Site/opt/fields/SPField_Select#fetchData($request)
	 */
	protected function fetchData( $data, $request = 'post' )
	{
		if ( is_array( $data ) && count( $data ) ) {
			$selected = [];
			foreach ( $data as $opt ) {
				/* check if such option exist at all */
				if ( !( isset( $this->optionsById[ $opt ] ) ) ) {
					throw new SPException( SPLang::e( 'FIELD_NO_SUCH_OPT', $opt, $this->name ) );
				}
				$selected[ ] = preg_replace( '/^[a-z0-9]\.\-\_/i', null, $opt );
			}
			return $selected;
		}
		else {
			return [];
		}
	}

	/**
	 * Static function to create the right SQL-Query if a entries list should be sorted by this field
	 * @param string $tables
	 * @param array $conditions
	 * @param string $oPrefix
	 * @param string $eOrder
	 * @param string $eDir
	 * @return bool
	 */
	public static function sortBy( &$tables, &$conditions, &$oPrefix, &$eOrder, $eDir )
	{
		return false;
	}

	protected function required( &$values )
	{
		return false;
	}
}
