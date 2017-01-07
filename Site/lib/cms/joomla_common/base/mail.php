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
jimport( 'joomla.mail.mail' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 19-Sep-2010 13:00:06
 */
class SPJoomlaMail extends JMail
{
	public function __construct()
	{
		$cfg = SPFactory::config();
		$this->setSender( [ $cfg->get( 'mail.mailfrom' ), $cfg->get( 'mail.fromname' ) ] );
		switch ( $cfg->get( 'mail.mailer' ) ) {
			case 'smtp':
				$this->useSMTP(
					( int ) ( $cfg->get('mail.smtpauth' ) != 0 ),
					$cfg->get( 'mail.smtphost' ),
					$cfg->get( 'mail.smtpuser' ),
					$cfg->get( 'mail.smtppass' ),
					$cfg->get( 'mail.smtpsecure' ),
					$cfg->get( 'mail.smtpport' )
				);
				break;
			case 'sendmail':
				$this->IsSendmail();
				break;
			default:
				$this->IsMail();
				break;
		}
	}
}
