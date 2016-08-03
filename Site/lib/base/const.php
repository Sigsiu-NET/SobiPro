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
	const INFO_MSG = 'info';
	const SUCCESS_MSG = 'success';
	const GLOBAL_SETTING = 2;
	const SHOW = 1;
	const NO = 0;
	const DEFAULT_TEMPLATE = 'b3-default3';
}
