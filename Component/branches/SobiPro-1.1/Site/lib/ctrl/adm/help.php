<?php
/**
 * @version: $Id: help.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/help.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 23-Jul-2010 13:11:48
 */
class SPHelp extends SPController
{
	/**
	 * @var string
	 */
	protected $_defTask = 'message';

	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'message':
				$this->screen();
			default:
				break;
		}
	}
	private function screen()
	{
		$view =& SPFactory::View( 'view', true );
		$view->setTemplate( 'config.help' );
		if( SPLoader::path( 'etc.repos.sobipro_core.repository', 'front', true, 'xml' ) ) {
			$repository = SPFactory::Instance( 'services.installers.repository' );
			$repository->loadDefinition( SPLoader::path( "etc.repos.sobipro_core.repository", 'front', true, 'xml' ) );
			try {
				$repository->connect();
			}
			catch ( SPException $x ) {
				$view->assign( SPLang::e( 'An error has occurred. %s', $x->getMessage() ), 'message' );
			}
			try {
				$response = $repository->help( $repository->get( 'token' ), SPRequest::cmd( 'mid' ) );
				$view->assign( $response, 'message' );
			}
			catch ( SPException $x ) {
				$view->assign( SPLang::e( 'An error has occurred. %s', $x->getMessage() ), 'message' );
			}
		}
		else {
			$view->assign( Sobi::Txt( 'MSG.HELP_ADD_CORE_REPO' ), 'message' );
		}
		$view->display();
	}
}
?>