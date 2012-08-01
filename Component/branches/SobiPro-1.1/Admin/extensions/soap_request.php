<?php
/**
 * @version: $Id: soap_request.php 626 2011-01-19 18:13:09Z Sigrid Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/soap_request.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<table style="width: 70%; vertical-align: top; margin-top: 6px;" class="adminlist">
	<thead>
		<tr>
			<th colspan="2">
				<?php $this->txt( 'EX.SOAP_RESP_HEAD' ); ?>
			</th>
		</tr>
	</thead>
	<tr>
		<th colspan="2" style="padding: 5px;">
			<?php $this->show( 'message' ); ?>
		</th>
	</tr>
	<?php
		$cols = $this->get( 'request' );
		foreach ( $cols as $name => $col ) {
	?>
		<tr>
			<td class="key" width="25%">
				<?php $this->txt( $col[ 'label' ] ); ?>
			</td>
			<td>
				<?php $this->field( $col[ 'type' ], $name, ( isset( $col[ 'value' ] ) ? $col[ 'value' ] : null), $col[ 'params' ] ); ?>
				<?php if( $col[ 'required' ] ) { ?>
					&nbsp;<?php $this->txt( 'EX.SOAP_RESP_REQ' ); ?>
				<?php } else { ?>
					&nbsp;<?php $this->txt( 'EX.SOAP_RESP_OPT' ); ?>
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
		<tr>
			<td></td>
			<td>
				<?php $this->field( 'button', 'send', 'translate:[EX.SOAP_RESP_SEND_BT]', array( 'class' => 'text_area', 'id' => 'spaddrepo', 'onclick' => 'SP_RepoCallback()' ) ); ?>
			</td>
		</tr>
</table>
