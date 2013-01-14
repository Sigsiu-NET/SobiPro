<?php
/**
 * @version: $Id: user.php 1515 2011-06-22 15:34:34Z Radek Suski $
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
 * $Date: 2011-06-22 17:34:34 +0200 (Wed, 22 Jun 2011) $
 * $Revision: 1515 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla16/base/user.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
jimport( 'joomla.form.helper' );
JFormHelper::loadFieldClass( 'user' );
/**
 * @property mixed input
 */
class SPFormFieldUser extends JFormFieldUser
{
	public function setup( $data )
	{
		foreach ( $data as $k => $v ) {
			$this->$k = $v;
		}
	}
}
?>
