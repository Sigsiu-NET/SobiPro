<?php
/**
 * @version: $Id: bank_transfer.php 641 2011-01-20 11:49:43Z Sigrid Suski $
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
 * $Date: 2011-01-20 12:49:43 +0100 (Thu, 20 Jan 2011) $
 * $Revision: 641 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/bank_transfer.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
$row = 0;
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20.8em; margin-top: 3px;">
	<fieldset class="adminform">
		<legend>
			<?php $this->txt( 'APP.BANK_TRANSFER_NAME' ); ?>
		</legend>
		<table class="admintable">
			<tr>
				<th colspan="2"  style="padding: 5px;">
					<?php $this->txt( 'APP.BANK_TRANSFER_DATA_EXPL' ); ?>
				</th>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>" style="vertical-align:top;">
				<td class="key">
					<label for="bankdata">
						<?php $this->txt( 'APP.BANK_TRANSFER_DATA' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'bankdata', 'value:bankdata', true, 550, 250, array( 'id' => 'bankdata', 'size' => 50, 'maxlength' => 255 ) ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<?php $this->trigger( 'OnEnd' ); ?>