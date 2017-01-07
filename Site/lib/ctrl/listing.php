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

SPLoader::loadController( 'section' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:08:36 PM
 */
class SPListingCtrl extends SPSectionCtrl
{
	public function execute()
	{
		SPRequest::set( 'task', $this->_type . '.' . $this->_task );
		if ( strstr( $this->_task, '.' ) ) {
			$task = explode( '.', $this->_task );
			$class = SPLoader::loadClass( 'opt.listing.' . $task[ 0 ], false, null, true );
		}
		else {
			$class = SPLoader::loadClass( 'opt.listing.' . $this->_task, false, null, true );
		}
		if ( $class ) {
			$imp = class_implements( $class );
			if ( is_array( $imp ) && in_array( 'SPListing', $imp ) ) {
				/** @noinspection PhpIncludeInspection $compatibility */
				$listing = new $class();
				if ( !( isset( $class::$compatibility ) ) ) {
					define( 'SOBI_LEGACY_LISTING', true );
					if ( strstr( $this->_task, '.' ) ) {
						$t = explode( '.', $this->_task );
						$listing->setTask( $t[ 0 ] );
					}
					else {
						$listing->setTask( $this->_task );
					}
				}
				else {
					$listing->setTask( $this->_task );
				}
				return $listing->execute();
			}
			else {
				Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND Wrong class definition', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
			}
		}
		else {
			/* case parent didn't registered this task, it was an error */
			if ( !( parent::execute() ) && $this->name() == __CLASS__ ) {
				Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
			}
		}
	}

	public function entries( $eOrder, $eLimit = null, $eLimStart = null, $count = false, $conditions = [], $entriesRecursive = false, $pid = -1 )
	{
		return $this->getEntries( $eOrder, $eLimit, $eLimStart, $count, $conditions, $entriesRecursive, $pid );
	}
}
