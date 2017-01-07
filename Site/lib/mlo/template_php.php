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

SPLoader::loadClass( 'mlo.template');

/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Oct-2009 09:08:04 AM
 */
class SPTemplatePHP implements SPTemplate
{
	/**
	 * @var SPFrontView
	 */
	private $_proxy = null;
	/**
	 * @var array
	 */
	private $_data = null;
	/**
	 * @var string
	 */
	private $_tpl = null;
	/**
	 * @var string
	 */
	private $_type = 'root';
	/**
	 * @var DOMDocument
	 */
	private $_xml = null;

	public function display()
	{
		$template = SPLoader::loadTemplate( $this->_tpl, 'php' );
		if( $template ) {
			include( $template );
		}
		else {
			throw new SPException( SPLang::e( 'CANNOT_LOAD_TEMPLATE_FILE_AT', SPLoader::loadTemplate( $this->_tpl, 'php', false ) ) );
		}
	}

	/**
	 * @param string $type the listing type to set
	 */
	public function setType( $type )
	{
		$this->_type = $type;
	}

	/** (non-PHPdoc)
	 * @var SPFrontView $proxy
	 * @see Site/lib/mlo/SPTemplate#setProxy()
	 */
	public function setProxy( &$proxy )
	{
		$this->_proxy =& $proxy;
	}

	/** (non-PHPdoc)
	 * @var array $data
	 * @see Site/lib/mlo/SPTemplate#setData()
	 */
	public function setData( $data )
	{
		$this->_data =& $data;
	}

	/** (non-PHPdoc)
	 * @var string $template
	 * @see Site/lib/mlo/SPTemplate#setTemplate()
	 */
	public function setTemplate( $template )
	{
		$this->_tpl = $template;
	}

	/**
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	public function __call( $method, $params )
	{
		return call_user_func_array( [ $this->_proxy, $method ], $params );
	}
}
