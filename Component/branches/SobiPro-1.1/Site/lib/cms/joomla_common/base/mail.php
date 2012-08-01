<?php
/**
 * @version: $Id: mail.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla15/base/mail.php $
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
		$this->setSender( array ( $cfg->get( 'mail.mailfrom' ), $cfg->get( 'mail.fromname' ) ) );
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
?>