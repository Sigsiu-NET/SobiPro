<?php
/**
 * @version: $Id: paypal.php 641 2011-01-20 11:49:43Z Sigrid Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/paypal.php $
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
			<?php $this->txt( 'APP.PPP_NAME' ); ?>
		</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>" style="vertical-align:top;">
				<td class="key">
					<label for="bankdata">
						<?php $this->txt( 'APP.PPP_EXPL' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'ppexpl', 'value:ppexpl', true, 550, 200, array( 'id' => 'ppexpl', 'size' => 50, 'maxlength' => 255 ) ); ?>
				</td>
			</tr>

			<tr class="row<?php echo ++$row%2; ?>" style="vertical-align:top;">
				<td class="key">
					<label for="bankdata">
						<?php $this->txt( 'APP.PPP_SUBJECT' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'ppsubject', 'value:ppsubject', array( 'id' => 'ppsubject', 'size' => 75, 'maxlength' => 255, 'class' => 'inputbox required' ) ); ?>
				</td>
			</tr>

			<tr class="row<?php echo ++$row%2; ?>" style="vertical-align:top;">
				<td class="key">
					<label for="bankdata">
						<?php $this->txt( 'APP.PPP_URL' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'ppurl', 'value:ppurl', array( 'id' => 'ppurl', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required' ) ); ?>
				</td>
			</tr>

			<tr class="row<?php echo ++$row%2; ?>" style="vertical-align:top;">
				<td class="key">
					<label for="bankdata">
						<?php $this->txt( 'APP.PPP_EMAIL' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'ppemail', 'value:ppemail', array( 'id' => 'ppemail', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required' ) ); ?>
				</td>
			</tr>

			<tr class="row<?php echo ++$row%2; ?>" style="vertical-align:top;">
				<td class="key">
					<label for="bankdata">
						<?php $this->txt( 'APP.PPP_RURL' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'pprurl', 'value:pprurl', array( 'id' => 'pprurl', 'size' => 75, 'maxlength' => 255, 'class' => 'inputbox required' ) ); ?>
				</td>
			</tr>

			<tr class="row<?php echo ++$row%2; ?>" style="vertical-align:top;">
				<td class="key">
					<label for="bankdata">
						<?php $this->txt( 'APP.PPP_CC' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'ppcc', 'value:ppcc', array( 'id' => 'ppcc', 'size' => 10, 'maxlength' => 255, 'class' => 'inputbox required' , 'style' => 'text-align: center' ) ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<?php $this->trigger( 'OnEnd' ); ?>