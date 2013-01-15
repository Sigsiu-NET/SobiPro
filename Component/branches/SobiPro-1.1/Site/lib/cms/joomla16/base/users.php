<?php
/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 03-Feb-2009 5:14:11 PM
 */
class SPUsers
{

	public static function getGroupsField ()
	{
		$db = &JFactory::getDbo();
		$db->setQuery( '
				 SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level
				 FROM #__usergroups AS a
				 LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt
				 GROUP BY a.id
				 ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();
		// Check for a database error.
		if ( $db->getErrorNum() ) {
			JError::raiseNotice( 500, $db->getErrorMsg() );
			return null;
		}
		for ( $i = 0, $n = count( $options ); $i < $n; $i ++ ) {
			$options[ $i ]->text = str_repeat( '- ', $options[ $i ]->level ) . $options[ $i ]->text;
		}
		$gids = array();
		foreach( $options as $k => $v ) {
			$gids[] = get_object_vars( $v );
		}
		$gids[ 0 ] = array( 'value' => 0, 'text' => Sobi::Txt( 'ACL.REG_VISITOR' ), 'level' => 0 );
		return $gids;
	}
}
