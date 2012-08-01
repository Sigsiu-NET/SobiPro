<?php
/**
 * @version: $Id: spsid.php 717 2011-02-02 19:49:02Z Radek Suski $
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
 * $Date: 2011-02-02 20:49:02 +0100 (Wed, 02 Feb 2011) $
 * $Revision: 717 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/views/elements/spsection.php $
 */

require_once dirname(__FILE__).'/spsection.php';

class JFormFieldSPSid extends JFormFieldSPSection
{
	protected $type = 'spsid';
}
?>