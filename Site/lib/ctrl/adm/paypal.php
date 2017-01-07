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
SPLoader::loadController( 'config', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 06-Aug-2010 15:38:15
 */
class SPPaymentPP extends SPConfigAdmCtrl
{
	/**
	 * @var string
	 */
	protected $_defTask = 'config';

	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'config':
				$this->screen();
				Sobi::ReturnPoint();
				break;
			case 'save':
				$this->save( false );
				break;
			default:
				Sobi::Error( 'SPPaymentBt', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
				break;
		}
	}

	protected function save( $apply, $clone = false )
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$this->validate( 'extensions.paypal', [ 'task' => 'paypal', 'pid' => Sobi::Section() ] );
		SPFactory::registry()->saveDBSection(
				[
						[ 'key' => 'ppurl', 'value' => SPRequest::string( 'ppurl' ) ],
						[ 'key' => 'ppemail', 'value' => SPRequest::string( 'ppemail' ) ],
						[ 'key' => 'ppcc', 'value' => SPRequest::string( 'ppcc' ) ],
						[ 'key' => 'pprurl', 'value' => SPRequest::string( 'pprurl' ) ],
				], 'paypal_' . Sobi::Section()
		);
		$data = [
				'key' => 'ppexpl',
				'value' => SPRequest::string( 'ppexpl', null, true ),
				'type' => 'application',
				'id' => Sobi::Section(),
				'section' => Sobi::Section()
		];
		try {
			SPLang::saveValues( $data );
			$data[ 'key' ] = 'ppsubject';
			$data[ 'value' ] = SPRequest::string( 'ppsubject', true );
			SPLang::saveValues( $data );
		} catch ( SPException $x ) {
			$message = SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() );
			Sobi::Error( 'SPPaymentBt', $message, SPC::WARNING, 0, __LINE__, __FILE__ );
			$this->response( Sobi::Back(), $message, false, 'error' );
		}
		$this->response( Sobi::Back(), Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' ), false, 'success' );
	}

	private function screen()
	{
		$data = SPFactory::registry()
				->loadDBSection( 'paypal_' . Sobi::Section() )
				->get( 'paypal_' . Sobi::Section() );
		if ( !( count( $data ) ) ) {
			$data = SPFactory::registry()
					->loadDBSection( 'paypal' )
					->get( 'paypal' );
		}
		$ppexpl = SPLang::getValue( 'ppexpl', 'application', Sobi::Section() );
		$ppsubj = SPLang::getValue( 'ppsubject', 'application', Sobi::Section() );
		if ( !( strlen( $ppsubj ) ) ) {
			$ppsubj = SPLang::getValue( 'ppsubject', 'application' );
		}
		$this->getView( 'paypal' )
				->assign( $data[ 'ppurl' ][ 'value' ], 'ppurl' )
				->assign( $data[ 'ppemail' ][ 'value' ], 'ppemail' )
				->assign( $data[ 'pprurl' ][ 'value' ], 'pprurl' )
				->assign( $data[ 'ppcc' ][ 'value' ], 'ppcc' )
				->assign( $ppexpl, 'ppexpl' )
				->assign( $ppsubj, 'ppsubject' )
				->determineTemplate( 'extensions', 'paypal' )
				->display();
	}
}
