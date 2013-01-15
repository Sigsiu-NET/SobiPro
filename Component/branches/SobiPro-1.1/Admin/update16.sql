-- @version: $Id$
-- @package: SobiPro Component for Joomla!

-- @author
-- Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
-- Email: sobi[at]sigsiu.net
-- Url: http://www.Sigsiu.NET

-- @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
-- @license GNU/GPL Version 3
-- This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
-- See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

-- This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

-- $Date$
-- $Revision$
-- $Author$
-- $HeadURL$

UPDATE #__sobipro_permissions SET value =  '*' WHERE  pid = 18;

CREATE TABLE IF NOT EXISTS `#__sobipro_user_group` (
  `description` text,
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `groupName` varchar(150) NOT NULL,
  PRIMARY KEY (`gid`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=5000 ;
