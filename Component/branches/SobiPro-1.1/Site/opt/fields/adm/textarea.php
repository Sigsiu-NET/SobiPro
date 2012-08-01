<?php
/**
 * @version: $Id: textarea.php 2294 2012-03-12 12:15:27Z Radek Suski $
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
 * $Date: 2012-03-12 13:15:27 +0100 (Mon, 12 Mar 2012) $
 * $Revision: 2294 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/fields/adm/textarea.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.textarea' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 12-Sep-2009 10:16:47 PM
 */
class SPField_TextareaAdm extends SPField_Textarea
{
	/**
	 * @var string
	 */
	protected $cssClass = "inputbox";
}
