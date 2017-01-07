<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 *
 * See http://www.gnu.org/licenses/gpl.html and httsp://www.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.info' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 12-Sep-2009 10:16:47 PM
 */
class SPField_InfoAdm extends SPField_Info
{
	//Speichern der übersetzbaren Elemente
	public function save( &$attr )
	{
		$attr = $this->saveAttr( $attr );
		parent::save( $attr );
	}

	public function saveNew( &$attr, $fid = 0 )
	{
		$attr = $this->saveAttr( $attr );
		if ( $fid ) {
			$this->fid = $fid;
		}
	}

	/**
	 * @param $attr
	 * @return mixed
	 * @throws SPException
	 */
	protected function saveAttr( &$attr )
	{
		if ( $this->nid ) {
			$this->nid = $attr[ 'nid' ];
		}
		if ( !( isset( $attr[ 'viewInfo' ] ) ) ) {
			$attr[ 'viewInfo' ] = $this->viewInfo;
			$attr[ 'entryInfo' ] = $this->entryInfo;
		}
		$data = [
				'key' => $this->nid . '-viewInfo',
				'value' => $attr[ 'viewInfo' ],
				'type' => 'field_information',
				'fid' => $this->fid,
				'id' => Sobi::Section(),
				'section' => Sobi::Section()
		];
		SPLang::saveValues( $data );
		$data = [
				'key' => $this->nid . '-entryInfo',
				'value' => $attr[ 'entryInfo' ],
				'type' => 'field_information',
				'fid' => $this->fid,
				'id' => Sobi::Section(),
				'section' => Sobi::Section()
		];
		SPLang::saveValues( $data );

		$attr[ 'required' ] = 0;
		$attr[ 'fee' ] = 0;
		$attr[ 'isFree' ] = 1;
		return $attr;
	}

	public function exportField()
	{
		$data = [];
		$data[ ] = [ 'attributes' => [ 'name' => 'viewInfo' ], 'value' => $this->viewInfo ];
		$data[ ] = [ 'attributes' => [ 'name' => 'entryInfo' ], 'value' => $this->entryInfo ];
		return $data;
	}

	public function importField( $data, $nid )
	{
		if ( count( $data ) ) {
			$this->nid = $nid;
			foreach ( $data as $set ) {
				$attr = $set[ 'attributes' ][ 'name' ];
				$this->$attr = $set[ 'value' ];
			}
			$viewInfo = [
					'key' => $this->nid . '-viewInfo',
					'value' => $this->viewInfo,
					'type' => 'field_information',
					'fid' => $this->fid,
					'id' => Sobi::Section(),
					'section' => Sobi::Section()
			];
			SPLang::saveValues( $viewInfo );
			$entryInfo = [
					'key' => $this->nid . '-entryInfo',
					'value' => $this->entryInfo,
					'type' => 'field_information',
					'fid' => $this->fid,
					'id' => Sobi::Section(),
					'section' => Sobi::Section()
			];
			SPLang::saveValues( $entryInfo );
		}
	}
}
