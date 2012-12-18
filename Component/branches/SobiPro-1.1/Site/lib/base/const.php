<?php
/**
 * @version: $Id: const.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/base/const.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 05-May-2010 19:58:10
 */
abstract class SPC
{
	const FS_APP = FILE_APPEND;
	const WARNING = E_USER_WARNING;
	const NOTICE = E_USER_NOTICE;
	const ERROR = E_USER_ERROR;
	const NO_VALUE = -90001;
	const ERROR_MSG = 'error';
	const WARN_MSG = 'warning';
	const NOTICE_MSG = 'warning';
	const GLOBAL_SETTING = 2;
	const SHOW = 1;
	const NO = 0;
}
