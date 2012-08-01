<?php
/**
 * @version: $Id: email.php 2076 2011-12-15 18:04:51Z Radek Suski $
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
 * $Date: 2011-12-15 19:04:51 +0100 (Thu, 15 Dec 2011) $
 * $Revision: 2076 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/edit/email.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<div class="col width-70" style="float: left;">
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<legend>
			<?php $this->txt( 'FM.EMAIL_SPEC_PARAMS' ); ?>
		</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.OWN_TITLE' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.ownLabel', 'value:field.ownLabel', 'ownLabel', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.LABEL_TITLE' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.labelsLabel', 'value:field.labelsLabel', 'id=field_labelsLabel, size=50, maxlength=150, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.TITLE_FIELD_WIDTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.labelWidth', 'value:field.labelWidth', 'id=field_labelWidth, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>&nbsp;px.
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.TITLE_MAX_LENGTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.labelMaxLength', 'value:field.labelMaxLength', 'id=field_label_max_length, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.TITLE_FILTER' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.filter', 'value:filters', 'value:field.filter', false, 'id=filter, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.URL_FIELD_WIDTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.width', 'value:field.width', 'id=field_width, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>&nbsp;px.
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.URL_MAX_LENGTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.maxLength', 'value:field.maxLength', 'id=field_max_length, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.VALIDATE_MX' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.validateUrl', 'value:field.validateUrl', 'validateUrl', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.EMAIL.BOTS_PROTECT' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.botProtection', 'value:field.botProtection', 'botProtection', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
