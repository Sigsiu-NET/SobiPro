<?php
/**
 * @version: $Id: template_php.php 551 2011-01-11 14:34:26Z Radek Suski $
 * @package: SobiPro Bridge
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/mlo/template_php.php $
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
	 * @var string $tmpl
	 * @see Site/lib/mlo/SPTemplate#setTemplate()
	 */
	public function setTemplate( $tmpl )
	{
		$this->_tpl = $tmpl;
	}

	/**
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	public function __call( $method, $params )
	{
		return call_user_func_array( array( $this->_proxy, $method ), $params );
	}
}
?>