<?php
/**
 * @version: $Id: image.php 1617 2011-07-08 13:55:37Z Radek Suski $
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
 * $Date: 2011-07-08 15:55:37 +0200 (Fri, 08 Jul 2011) $
 * $Revision: 1617 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/edit/image.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<div class="col width-70" style="float: left;">
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<legend>
			<?php $this->txt( 'FM.IMAGE_SPEC_PARAMS' ); ?>
		</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.MAX_FILE_SIZE' ); ?></td>
				<td>
					<?php $this->field( 'text', 'field.maxSize', 'value:field.maxSize', 'id=field_max_size, size=20, maxlength=50, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.FIELD_WIDTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.width', 'value:field.width', 'id=field_width, size=8, maxlength=10, class=inputbox, style=text-align:center;' ); ?>&nbsp;px.
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.KEEP_ORG' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.keepOrg', 'value:field.keepOrg', 'keepOrg', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.RESIZE_IMG' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.resize', 'value:field.resize', 'resize', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.RESIZE_TO' ); ?>
				</td>
				<td>
					<?php $this->txt( 'WIDTH' ); ?>&nbsp;
					<?php $this->field( 'text', 'field.resizeWidth', 'value:field.resizeWidth', 'id=field_resize_width, size=8, maxlength=50, class=inputbox, style=text-align:center;' ); ?>
					&nbsp;<?php $this->txt( 'HEIGHT' ); ?>&nbsp;
					<?php $this->field( 'text', 'field.resizeHeight', 'value:field.resizeHeight', 'id=field_resize_height, size=8, maxlength=50, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.IMG_FLOAT' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.imageFloat', array( '' => 'translate:[FM.IMG.FLOAT_NO_OPT]', 'right' => 'translate:[FM.IMG.FLOAT_RIGHT_OPT]', 'left' => 'translate:[FM.IMG.FLOAT_LEFT_OPT]' ), 'value:field.imageFloat', false, 'id=field.imageFloat, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.IMG_NAME' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.imageName', 'value:field.imageName', 'id=field_image_name, size=30, maxlength=150, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.CREATE_THUMB' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.generateThumb', 'value:field.generateThumb', 'generateThumb', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.RESIZE_TO' ); ?>
				</td>
				<td>
					<?php $this->txt( 'WIDTH' ); ?>&nbsp;
					<?php $this->field( 'text', 'field.thumbWidth', 'value:field.thumbWidth', 'id=field_thumb_width, size=8, maxlength=50, class=inputbox, style=text-align:center;' ); ?>
					&nbsp;<?php $this->txt( 'HEIGHT' ); ?>&nbsp;
					<?php $this->field( 'text', 'field.thumbHeight', 'value:field.thumbHeight', 'id=field_thumb_height, size=8, maxlength=50, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.THUMB_FLOAT' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.thumbFloat', array( '' => 'translate:[FM.IMG.FLOAT_NO_OPT]', 'right' => 'translate:[FM.IMG.FLOAT_RIGHT_OPT]', 'left' => 'translate:[FM.IMG.FLOAT_LEFT_OPT]' ), 'value:field.thumbFloat', false, 'id=field.thumbFloat, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.THUMB_NAME' ); ?></td>
				<td>
					<?php $this->field( 'text', 'field.thumbName', 'value:field.thumbName', 'id=field_thumb_name, size=30, maxlength=150, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.SHOW_IN_V_CARD' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.inVcard',array( 'thumb' => 'translate:[FM.IMG.THUMBAIL_OPT]', 'image' => 'translate:[FM.IMG.IMAGE_OPT]', 'original' => 'translate:[FM.IMG.ORGINAL_OPT]' ), 'value:field.inVcard', false, 'id=show_in_vc, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.SHOW_IN_DETAILS_VIEW' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.inDetails',array( 'thumb' => 'translate:[FM.IMG.THUMBAIL_OPT]', 'image' => 'translate:[FM.IMG.IMAGE_OPT]', 'original' => 'translate:[FM.IMG.ORGINAL_OPT]' ), 'value:field.inDetails', false, 'id=show_in_details, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IMG.IMAGE_SAVE_PATH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.savePath', 'value:field.savePath', 'id=field_save_paths, size=40, maxlength=150, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>