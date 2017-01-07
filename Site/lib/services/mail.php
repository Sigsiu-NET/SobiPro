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
SPLoader::loadClass( 'cms.base.mail' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 19-Sep-2010 13:00:06
 */
class SPMail extends SPMailInterface
{
	/**
	 * @param array $recipient Recipient e-mail address
	 * @param string $subject E-mail subject
	 * @param string $body Message body
	 * @param bool $html - HTML mail or plain text
	 * @param array $replyto Reply to email address
	 * @param array $cc CC e-mail address
	 * @param array $bcc BCC e-mail address
	 * @param string $attachment Attachment file name
	 * @param array $cert - pem certificate
	 * @param array $from - array( from, fromname )
	 * @internal param array $replytoname Reply to name
	 * @return boolean True on success
	 */
	public static function SpSendMail( $recipient, $subject, $body, $html = false, $replyto = null, $cc = null, $bcc = null, $attachment = null, $cert = null, $from = null )
	{
		$from = is_array( $from ) ? $from : [ Sobi::Cfg( 'mail.from' ), Sobi::Cfg( 'mail.fromname' ) ];
		$mail = new self();
		$mail->setSender( $from );
		$mail->setSubject( $subject );
		$mail->setBody( $body );
		if ( $html ) {
			$mail->IsHTML( true );
		}
		if( $cert ) {
			$mail->Sign( $cert[ 'certificate' ] , $cert[ 'key' ], $cert[ 'password' ] );
		}
		$mail->addRecipient( $recipient );
		$mail->addCC( $cc );
		$mail->addBCC( $bcc );
		$mail->addAttachment( $attachment );
		$mail->addReplyTo( $replyto );
		return $mail->Send();
	}
}
