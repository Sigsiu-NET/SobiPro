<?php
/**
 * @version: $Id: field.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/field.php $
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
		$this->nid = $method[ 0 ];
		$method = $method[ 1 ];
		$this->fid = SPFactory::db()
				->select( 'fid', 'spdb_field', array( 'nid' => $this->nid, 'section' => Sobi::Section() ) )
				->loadResult();
		$this->field = SPFactory::Model( 'field' );
		$this->field->init( $this->fid );
		$this->field->$method();
	}
}
