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
 * @version 1.1
 * @created Thu, Nov 8, 2012 13:17:49
 */
class SPUserCtrl extends SPController
{
	/**
	 * @var string
	 */
	protected $_type = 'user';

	/**
	 */
	public function execute()
	{
		$r = false;
		switch ( $this->_task ) {
			case 'search':
				$this->search();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !parent::execute() ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				else {
					$r = true;
				}
				break;
		}
		return $r;
	}

	protected function search()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
//		$selected = SPRequest::int( 'selected', 0 );
		$ssid = SPRequest::base64( 'ssid' );
		$query = SPRequest::string( 'q', null );
		$session = SPFactory::user()->getUserState( 'userSelector', null, [] );
		$setting = $session[ $ssid ];
		/* get the site to display */
		$site = SPRequest::int( 'site', 1 );
		$eLim = Sobi::Cfg( 'user_selector.entries_limit', 18 );
		$eLimStart = ( ( $site - 1 ) * $eLim );
		$params = [];
		if ( $query ) {
			$q = '%' . $query . '%';
			$params = SPFactory::db()->where( [ 'name' => $q, 'username' => $q, 'email' => $q ], 'OR' );
		}
		try {
			$count = SPFactory::db()
					->select( 'COUNT(*)', '#__users', $params, $setting[ 'ordering' ] )
					->loadResult();
			$data = SPFactory::db()
					->select( [ 'id', 'name', 'username', 'email', 'registerDate', 'lastvisitDate' ], '#__users', $params, $setting[ 'ordering' ], $eLim, $eLimStart )
					->loadAssocList();
		} catch ( SPException $x ) {
			echo $x->getMessage();
			exit;
		}
		$response = [ 'sites' => ceil( $count / $eLim ), 'site' => $site ];
		if ( count( $data ) ) {
			$replacements = [];
			preg_match_all( '/\%[a-z]*/', $setting[ 'format' ], $replacements );
			$placeholders = [];
			if ( isset( $replacements[ 0 ] ) && count( $replacements[ 0 ] ) ) {
				foreach ( $replacements[ 0 ] as $placeholder ) {
					$placeholders[ ] = str_replace( '%', null, $placeholder );
				}
			}
			if ( count( $replacements ) ) {
				foreach ( $data as $index => $user ) {
					$txt = $setting[ 'format' ];
					foreach ( $placeholders as $attribute ) {
						if ( isset( $user[ $attribute ] ) ) {
							$txt = str_replace( '%' . $attribute, $user[ $attribute ], $txt );
						}
					}
					$data[ $index ][ 'text' ] = $txt;
				}
			}
			$response[ 'users' ] = $data;
		}
		SPFactory::mainframe()->cleanBuffer();
		echo json_encode( $response );
		exit;
	}
}
