<?php
/**
 * @version: $Id: install.php 2998 2013-01-16 17:09:18Z Sigrid Suski $
 * @package: SobiPro Component for Joomla!
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * $Date: 2013-01-16 18:09:18 +0100 (Wed, 16 Jan 2013) $
 * $Revision: 2998 $
 * $Author: Sigrid Suski $
 */

defined( '_JEXEC' ) or die();
class plgSystemSpHeader extends JPlugin
{
	public function onAfterDispatch()
	{
		// if the class exists it means something initialised it so we can send the header
		if ( class_exists( 'SPFactory' ) ) {
			SPFactory::header()->sendHeader();
		}
	}
}
