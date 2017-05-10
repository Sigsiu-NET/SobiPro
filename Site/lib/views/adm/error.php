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
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jun-2010 17:09:48
 */
class SPAdmError extends SPAdmView
{
	public function display()
	{
		switch ( $this->get( 'task' ) ) {
			case 'list':
				$this->errors();
				$this->determineTemplate( 'config', 'errors' );
				break;
			case 'details':
				$this->details();
				$this->determineTemplate( 'config','error' );
				break;
		}
		parent::display();
	}

	private function errors()
	{
		$errors = $this->get( 'errors' );
		$levels = $this->get( 'levels' );
		$icons = [
			'error' => 'shield error',
			'warning' => 'shield warning',
			'notice' => 'shield notice',
		];
		/* create the header */
		if( count( $errors ) ) {
			foreach ( $errors as $i => $error ) {
				$error[ 'errFile' ] = str_replace( SOBI_ADM_PATH, null, $error[ 'errFile' ]  );
				$error[ 'errFile' ] = str_replace( SOBI_PATH, null, $error[ 'errFile' ]  );
				$error[ 'errFile' ] = str_replace( SOBI_ROOT, null, $error[ 'errFile' ]  );
				$error[ 'errFile' ] = $error[ 'errFile' ].': '.$error[ 'errLine' ];
				if( $error[ 'errReq' ] ) {
					$error[ 'errReq' ] = "<a href=\"{$error[ 'errReq' ]}\" target=\"_blank\">{$error[ 'errReq' ]}</a>";
				}
				$level = $levels[ $error[ 'errNum' ] ];
				switch ( $error[ 'errNum' ] ) {
					case E_ERROR:
					case E_CORE_ERROR:
					case E_COMPILE_ERROR:
					case E_USER_ERROR:
					case E_RECOVERABLE_ERROR:
						$error[ 'errNum' ] = "<i class=\"icon-{$icons[ 'error' ]}\" title=\"{$level}\"></i><br/>{$level}";
						break;
					case E_WARNING:
					case E_CORE_WARNING:
					case E_COMPILE_WARNING:
					case E_USER_WARNING:
						$error[ 'errNum' ] = "<i class=\"icon-{$icons[ 'warning' ]}\"title=\"{$level}\"></i><br/>{$level}";
						break;
					case E_NOTICE:
					case E_USER_NOTICE:
					case E_STRICT:
					case E_USER_WARNING:
					case E_DEPRECATED:
					case E_USER_DEPRECATED:
						$error[ 'errNum' ] = "<i class=\"icon-{$icons[ 'notice' ]}\" title=\"{$level}\"></i><br/>{$level}";
						break;
				}
				$error[ 'errMsg' ] = str_replace( SOBI_ROOT, null, $error[ 'errMsg' ]  );
				$error[ 'errMsg' ] = str_replace( 'href=\'function.', 'target="_blank" href=\'http://php.net/manual/en/function.', $error[ 'errMsg' ] );
				$dh = Sobi::Url( [ 'task' => 'error.details', 'eid' => $error[ 'eid' ] ] );
				$errors[ $i ] = $error;
			}
		}
//		Sobi::Error( 'H', date( DATE_RFC1123 ), SPC::ERROR );
		$this->assign( $errors, 'errors' );
	}

	private function details()
	{
		$levels = $this->get( 'levels' );
		$error = $this->get( 'error' );
		if( $error->errReq ) {
			$error->errReq = "<a href=\"{$error->errReq}\" target\"_blank\">{$error->errReq}</a>";
		}
		if( $error->errRef ) {
			$error->errRef = "<a href=\"{$error->errRef}\" target\"_blank\">{$error->errRef}</a>";
		}
		if( $error->errNum ) {
			$error->errNum = $levels[ $error->errNum ];
		}
		if( $error->errBacktrace ) {
			$error->errBacktrace = '<pre>'.SPConfig::debOut( $error->errBacktrace, false, true ).'</pre>';
		}
		if( $error->errCont ) {
			$error->errCont = '<pre>'.SPConfig::debOut( $error->errCont, false, true ).'</pre>';
		}
		$error->errMsg = str_replace( 'href=\'function.', 'target="_blank" href=\'http://php.net/manual/en/function.', $error->errMsg );
		$this->assign( $error, 'error' );
	}
}
