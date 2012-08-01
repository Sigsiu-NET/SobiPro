<?php
/**
 * @version: $Id: user.php 670 2011-01-28 19:53:57Z Radek Suski $
 * @package: SobiPro Bridge
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-01-28 20:53:57 +0100 (Fri, 28 Jan 2011) $
 * $Revision: 670 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla15/base/user.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname(__FILE__).'/../../joomla_common/base/user.php';

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:51:03 PM
 */
class SPUser extends SPJomlaUser
{
	public function __construct( $id = 0 )
	{
		parent::__construct( $id );
		if( is_numeric( $this->gid ) ) {
			$this->gid = array( $this->gid );
		}
		$this->spGroups();
		/* include default visitor permissions */
		$this->gid[] = 0;
		$this->parentGids();
		Sobi::Trigger( 'UserGroup', 'Appoint', array( $id, &$this->gid ) );
	}
}
?>