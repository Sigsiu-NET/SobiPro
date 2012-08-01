<?php
/**
 * @version: $Id: certificate.php 626 2011-01-19 18:13:09Z Sigrid Suski $
 * @package: SobiPro Template
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
 * $Date: 2011-01-19 19:13:09 +0100 (Wed, 19 Jan 2011) $
 * $Revision: 626 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/certificate.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<table style="width: 100%; vertical-align: top; margin-top: 6px;" class="adminlist">
	<thead>
		<tr>
			<th colspan="2">
				<?php $this->txt( 'EX.SSL_VERIFY' ); ?>
			</th>
		</tr>
	</thead>
	<tr>
		<th colspan="2">
			<?php $this->txt( 'EX.SSL_TO' ); ?>
		</th>
	</tr>
	<tr>
		<td class="key" width="20%">
			<?php $this->txt( 'EX.SSL_COMMON_NAME' ); ?>
		</td>
		<td>
			<b><?php $this->show( 'certificate.subject.CN' ); ?></b>
		</td>
	</tr>
	<tr>
		<td class="key" width="20%">
			<?php $this->txt( 'EX.SSL_ORGANIZATION' ); ?>
		</td>
		<td>
			<b><?php $this->show( 'certificate.subject.O' ); ?></b>&nbsp;
			<?php $this->show( 'certificate.subject.ST' ); ?>&nbsp;
			<?php $this->show( 'certificate.subject.C' ); ?>
		</td>
	</tr>
	<tr>
		<td class="key" width="20%">
			<?php $this->txt( 'EX.SSL_OU' ); ?>
		</td>
		<td>
			<?php $this->show( 'certificate.subject.OU' ); ?>
		</td>
	</tr><!--
	<tr>
		<th colspan="2">
			<?php $this->txt( 'EX.SSL_BY' ); ?>
		</th>
	</tr>
	<tr>
		<td class="key">
			<?php $this->txt( 'EX.SSL_BY_O' ); ?>
		</td>
		<td>
			<?php $this->show( 'certificate.issuer.O' ); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php $this->txt( 'EX.SSL_BY_CN' ); ?>
		</td>
		<td>
			<?php $this->show( 'certificate.issuer.CN' ); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php $this->txt( 'EX.SSL_BY_OU' ); ?>
		</td>
		<td>
			<?php $this->show( 'certificate.issuer.OU' ); ?>
		</td>
	</tr>
	--><tr>
		<th colspan="2">
			<?php $this->txt( 'EX.SSL_CERT_INFO' ); ?>
		</th>
	</tr>
	<tr>
		<td class="key" width="20%">
			<?php $this->txt( 'EX.SSL_CERT_SN' ); ?>
		</td>
		<td>
			<?php $this->show( 'certificate.serialNumber' ); ?>
		</td>
	</tr>
	<tr>
		<td class="key" width="20%">
			<?php $this->txt( 'EX.SSL_CERT_SINCE' ); ?>
		</td>
		<td>
			<?php echo Sobi::Date( $this->get( 'certificate.validFrom_time_t' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="key" width="20%">
			<?php $this->txt( 'EX.SSL_CERT_UNTIL' ); ?>
		</td>
		<td>
			<?php echo Sobi::Date( $this->get( 'certificate.validTo_time_t' ) ); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: left; padding: 15px 0 5px 5px!important;">
			<?php $this->field( 'button', 'confirm', 'translate:[EX.SSL_CONFIRM_BT]', array( 'class' => 'text_area', 'id' => 'spaconfirmrepo', 'onclick' => 'SP_CertConf()', 'style' => 'font-size: 12px;' ) ); ?>
			&nbsp;
			<?php $this->field( 'button', 'confirm', 'translate:[EX.SSL_SKIP_BT]', array( 'class' => 'text_area', 'id' => 'spanconfirmrepo', 'onclick' => 'SP_CertNotConf()', 'style' => 'font-size: 12px;' ) ); ?>
		</td>
	</tr>
</table>
