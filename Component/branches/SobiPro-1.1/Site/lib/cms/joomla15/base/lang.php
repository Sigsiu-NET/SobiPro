<?php
/**
 * @version $Id: lang.php 666 2011-01-28 19:16:48Z Radek Suski $
 * @package: SobiPro
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl-2.1.html
 * You can use, redistribute this file and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 * ===================================================
 * $Date: 2011-01-28 20:16:48 +0100 (Fri, 28 Jan 2011) $
 * $Revision: 666 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla15/base/lang.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname(__FILE__).'/../../joomla_common/base/lang.php';
/**
 * @author Radek Suski
 * @version 1.0
 * @created 20-Jun-2009 19:56:57
 */
final class SPLang extends SPJoomlaLang
{
	protected function _txt ()
	{
		$a = func_get_args();
		$m = call_user_func_array( array( 'parent', '_txt' ), $a );
		$m = str_replace('_QQ_', '"', $m );
	}
}
?>
