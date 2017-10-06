<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

use Sobi\FileSystem\FileSystem;

defined( '_JEXEC' ) or die();

class plgSystemSpHeader extends JPlugin
{
	/** @var bool */
	protected $showMessage = false;
	/** @var null */
	protected $message = null;

	public function onBeforeCompileHead()
	{
		// if the class exists it means something initialised it so we can send the header
		if ( class_exists( 'SPFactory' ) ) {
			SPFactory::header()->sendHeader();
		}
	}

	public function onAfterRender( $context = null )
	{
		$app = JFactory::getApplication();
		if ( ( JFactory::getApplication()->input->getWord( 'option' ) == 'com_cpanel' ) && ( ( method_exists( $app, 'isClient' ) && $app->isClient( 'administrator' ) ) || $app->isAdmin() ) ) {
			if ( file_exists( JPATH_ROOT . '/components/com_sobipro/tmp/message.json' ) ) {
				$data = json_decode( file_get_contents( JPATH_ROOT . '/components/com_sobipro/tmp/message.json' ), true );
				if ( $data[ 'btn-text' ] ) {
					$btn = ': <button class="btn btn-primary" onclick="document.location=\'index.php?option=com_sobipro&amp;task=extensions.browse\'">
					         ' . $data[ 'btn-text' ] . '
					     </button>
				';
				}
				else {
					$btn = null;
				}
				$content = '<div class="alert alert-error alert-joomlaupdate">
	       						<span class="label label-important">' . $data[ 'count' ] . '</span>
	       							' . $data[ 'text' ] . $btn . '
	   						</div>';
				$buffer = JFactory::getApplication()->getBody();
				preg_match( '|\<div id="system-message-container"\>(.+?)\<\/div\>|s', $buffer, $matches );
				if ( count( $matches ) && $matches[ 0 ] ) {
					$buffer = str_replace( $matches[ 0 ], $content . $matches[ 0 ], $buffer );
					JFactory::getApplication()->setBody( $buffer );
				}
			}
		}
	}

	public function onUserAfterLogin( $options )
	{
		$app = JFactory::getApplication();
		if ( ( method_exists( $app, 'isClient' ) && $app->isClient( 'administrator' ) ) || $app->isAdmin() ) {
			require_once( JPATH_ROOT . '/components/com_sobipro/lib/sobi.php' );
			Sobi::Initialise();
			require_once( JPATH_ROOT . '/components/com_sobipro/lib/ctrl/adm/extensions.php' );
			if ( Sobi::Can( 'cms.apps' ) && Sobi::Cfg( 'extensions.check_updates', true ) ) {
				$ctrl = new SPExtensionsCtrl();
				try {
					$updates = $ctrl->updates( false );
				} catch ( Exception $x ) {
					$message = [ 'count' => 1, 'text' => Sobi::Txt( 'UPDATE.SSL_ERROR' ), 'btn-text' => null ];
					$message = json_encode( $message );
					FileSystem::Write( JPATH_ROOT . '/components/com_sobipro/tmp/message.json', $message );
					return true;
				}
				$apps = [];
				if ( count( $updates ) ) {
					foreach ( $updates as $update ) {
						if ( $update[ 'update' ] == 'true' ) {
							$apps[] = $update[ 'name' ];
						}
					}
				}
				if ( count( $apps ) ) {
					$count = count( $apps );
					if ( count( $apps ) > 3 ) {
						$apps = array_slice( $apps, 0, 3 );
						$apps[] = '...';
					}
					$text = Sobi::Txt( 'UPDATE.APPS_OUTDATED', implode( ', ', $apps ) );
					$message = [ 'count' => $count, 'text' => $text, 'btn-text' => Sobi::Txt( 'UPDATE.APPS_UPDATE' ) ];
					$message = json_encode( $message );
					FileSystem::Write( JPATH_ROOT . '/components/com_sobipro/tmp/message.json', $message );
				}
				else {
					FileSystem::Delete( JPATH_ROOT . '/components/com_sobipro/tmp/message.json' );
				}
			}
			else {
				FileSystem::Delete( JPATH_ROOT . '/components/com_sobipro/tmp/message.json' );
			}
		}
	}
}
