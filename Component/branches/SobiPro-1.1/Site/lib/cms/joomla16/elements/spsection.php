<?php
/**
 * @version: $Id: spsection.php 743 2011-02-04 19:35:59Z Radek Suski $
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
 * $Date: 2011-02-04 20:35:59 +0100 (Fri, 04 Feb 2011) $
 * $Revision: 743 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/views/elements/spsection.php $
 */

JLoader::import( 'joomla.html.parameter.element' );
defined ( 'SOBI_CMS' ) || define( 'SOBI_CMS', 'joomla16' );
require_once dirname(__FILE__).'/../../joomla_common/elements/spsection.php';

class JFormFieldSPSection extends JFormField
{
	protected $type = 'spsection';

	protected function getInput()
	{
		return str_replace(
			'urlparams', 'jform[request]',
			JElementSPSection::getInstance()->fetchElement(
				preg_replace( '/^sp/', null, $this->type )
			)
		);
	}
}
?>