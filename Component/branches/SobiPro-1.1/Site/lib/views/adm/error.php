<?php
/**
 * @version: $Id: error.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/error.php $
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
				break;
			case 'details':
				$this->details();
				break;
		}
		parent::display();
	}

	private function errors()
	{
		$errors = $this->get( 'errors' );
		$levels = $this->get( 'levels' );
		$icons = array(
			'error' => Sobi::Cfg( 'list_icons.err_err' ),
			'warning' => Sobi::Cfg( 'list_icons.err_warn' ),
			'notice' => Sobi::Cfg( 'list_icons.err_notice' ),
			'details' => Sobi::Cfg( 'list_icons.err_details' ),
		);
		/* create the header */
		if( count( $errors ) ) {
			foreach ( $errors as $i => $error ) {
				$error[ 'errFile' ] = str_replace( SOBI_ADM_PATH, null, $error[ 'errFile' ]  );
				$error[ 'errFile' ] = str_replace( SOBI_PATH, null, $error[ 'errFile' ]  );
				$error[ 'errFile' ] = str_replace( SOBI_ROOT, null, $error[ 'errFile' ]  );
				if( $error[ 'errReq' ] ) {
					$error[ 'errReq' ] = "<a href=\"{$error[ 'errReq' ]}\" target\"_blank\">{$error[ 'errReq' ]}</a>";
				}
				$level = $levels[ $error[ 'errNum' ] ];
				switch ( $error[ 'errNum' ] ) {
					case E_ERROR:
					case E_CORE_ERROR:
					case E_COMPILE_ERROR:
					case E_USER_ERROR:
					case E_RECOVERABLE_ERROR:
						$error[ 'errNum' ] = "<img src=\"{$icons[ 'error' ]}\" alt=\"{$level}\" title=\"{$level}\"/><br/>{$level}";
						break;
					case E_WARNING:
					case E_CORE_WARNING:
					case E_COMPILE_WARNING:
					case E_USER_WARNING:
						$error[ 'errNum' ] = "<img src=\"{$icons[ 'warning' ]}\" alt=\"{$level}\" title=\"{$level}\"/><br/>{$level}";
						break;
					case E_NOTICE:
					case E_USER_NOTICE:
					case E_STRICT:
					case E_USER_WARNING:
					case E_DEPRECATED:
					case E_USER_DEPRECATED:
						$error[ 'errNum' ] = "<img src=\"{$icons[ 'notice' ]}\" alt=\"{$level}\" title=\"{$level}\"/><br/>{$level}";
						break;
				}
				$error[ 'errMsg' ] = str_replace( SOBI_ROOT, null, $error[ 'errMsg' ]  );
				$error[ 'errMsg' ] = str_replace( 'href=\'function.', 'target="_blank" href=\'http://php.net/manual/en/function.', $error[ 'errMsg' ] );
				$dh = Sobi::Url( array( 'task' => 'error.details', 'eid' => $error[ 'eid' ] ) );
				$error[ 'details' ] = "<a href=\"{$dh}\"><img src=\"{$icons[ 'details' ]}\"/></a>";
				$errors[ $i ] = $error;
			}
		}
		$pn = SPFactory::Instance(
			'helpers.adm.pagenav',
			 $this->get( '$eLimit' ),
			 $this->get( '$eCount' ),
			 $this->get( '$eLimStart' ),
			 'SPEntriesPageNav',
			 'elimit',
			 'SPEntriesPageLimit'
		);
		$this->assign( $pn->display( true ), 'page_nav' );
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
		$error->errMsg = str_replace( 'href=\'function.', 'target="_blank" href=\'http://php.net/manual/en/function.', $error->errMsg );
		$this->assign( $error, 'error' );
	}
}
?>