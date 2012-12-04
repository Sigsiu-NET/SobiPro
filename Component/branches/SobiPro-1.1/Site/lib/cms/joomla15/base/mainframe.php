<?php
/**
 * @version: $Id: mainframe.php 666 2011-01-28 19:16:48Z Radek Suski $
 * @package: SobiPro J!Bridge
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-01-28 20:16:48 +0100 (Fri, 28 Jan 2011) $
 * $Revision: 666 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla15/base/mainframe.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname(__FILE__).'/../../joomla_common/base/mainframe.php';
/**
 * Interface between SobiPro and the used CMS
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:50:43 PM
 */
final class SPMainFrame extends SPJoomlaMainFrame implements SPMainfrmaInterface {}
