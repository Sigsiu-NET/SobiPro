<?php
/**
 * @version: $Id: listing.php 2076 2011-12-15 18:04:51Z Radek Suski $
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
 * $Date: 2011-12-15 19:04:51 +0100 (Thu, 15 Dec 2011) $
 * $Revision: 2076 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/listing.php $
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
				$listing = new $class();
				$listing->setTask( $this->_task );
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

	public function entries( $eOrder, $eLimit = null, $eLimStart = null, $count = false, $conditions = array(), $entriesRecursive = false, $pid = -1 )
	{
		return $this->getEntries( $eOrder, $eLimit, $eLimStart, $count, $conditions, $entriesRecursive, $pid );
	}
}
