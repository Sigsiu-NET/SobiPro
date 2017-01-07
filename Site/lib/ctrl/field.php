<?php
/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
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

/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Mar-2009 11:23:22 AM
 */
class SPFieldCtrl extends SPController
{
	/** @var string */
	protected $nid = '';
	/** @var int */
	protected $fid = '';
	/** @var SPField */
	protected $field = null;

	public function __construct()
	{
	}

	public function execute()
	{
		$method = explode( '.', $this->_task );
		$this->nid = 'field_' . $method[ 0 ];
		$method = 'Proxy' . ucfirst( $method[ 1 ] );
		$this->fid = SPFactory::db()
				->select( 'fid', 'spdb_field', [ 'nid' => $this->nid, 'section' => Sobi::Section() ] )
				->loadResult();
		$this->field = SPFactory::Model( 'field' );
		$this->field->init( $this->fid );
		$this->field->$method();
		return true;
	}
}
