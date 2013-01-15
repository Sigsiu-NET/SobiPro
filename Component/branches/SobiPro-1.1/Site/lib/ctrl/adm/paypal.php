<?php
/**
 * @version: $Id: paypal.php 2614 2012-07-20 13:50:40Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-07-20 15:50:40 +0200 (Fri, 20 Jul 2012) $
 * $Revision: 2614 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/paypal.php $
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
				$this->save();
				break;
			default:
				Sobi::Error( 'SPPaymentBt', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
				break;
		}
	}

	protected function save()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$this->validate( 'extensions.paypal', array( 'task' => 'paypal', 'pid' => Sobi::Section() ) );
		SPFactory::registry()->saveDBSection(
			array(
				array( 'key' => 'ppurl', 'value' => SPRequest::string( 'ppurl' ) ),
				array( 'key' => 'ppemail', 'value' => SPRequest::string( 'ppemail' ) ),
				array( 'key' => 'ppcc', 'value' => SPRequest::string( 'ppcc' ) ),
				array( 'key' => 'pprurl', 'value' => SPRequest::string( 'pprurl' ) ),
			), 'paypal_' . Sobi::Section()
		);
		$data = array(
			'key' => 'ppexpl',
			'value' => SPRequest::string( 'ppexpl', null, true ),
			'type' => 'application',
			'id' => Sobi::Section(),
			'section' => Sobi::Section()
		);
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
				->assign( $tile, 'title' )
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
