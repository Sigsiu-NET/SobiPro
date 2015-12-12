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
				$errorMessage = SPLang::e( 'REPO_ERR', $x->getMessage() );
				$view->assign( $errorMessage, 'message' );
			}
			try {
				$response = $repository->help( $repository->get( 'token' ), SPRequest::cmd( 'mid' ) );
				$view->assign( $response, 'message' );
			}
			catch ( SPException $x ) {
				$errorMessage = SPLang::e( 'REPO_ERR', $x->getMessage() );
				$view->assign( $errorMessage, 'message' );
			}
		}
		else {
			$message = Sobi::Txt( 'MSG.HELP_ADD_CORE_REPO' );
			$view->assign( $message, 'message' );
		}
		$view->display();
	}
}
