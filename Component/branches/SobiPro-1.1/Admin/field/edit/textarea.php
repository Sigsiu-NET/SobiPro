<?php
/**
 * @version: $Id: textarea.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/edit/textarea.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addJsCode
("
	window.addEvent('domready', function(){
		$( 'editor_yes' ).addEvent('click', function() {
			if( $( 'editor_yes' ).checked ) {
				if( confirm( SobiPro.Txt( 'FM_TEXTAREA_WYSIWYG_NOLIMIT_WARN' ) ) ) {
					$( 'field_max_length' ).value = 0;
				}
				else {
					$( 'editor_yes' ).checked = false;
					$( 'editor_no' ).checked = true;
				}
			}
		} );
		$( 'field_max_length' ).addEvent('change', function() {
			if( $( 'editor_yes' ).checked ) {
				if( confirm( SobiPro.Txt( 'FM_TEXTAREA_WYSIWYG_NOLIMIT_WARN2' ) ) ) {
					$( 'editor_yes' ).checked = false;
					$( 'editor_no' ).checked = true;
				}
				else {
					$( 'field_max_length' ).value = 0;
				}
			}
		});
	});
")
?>
<div class="col width-70" style="float: left;">
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<legend>
			<?php $this->txt( 'FM.TEXTAREA_SPEC_PARAMS' ); ?>
		</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.TXT.ENABLE_WYSIWYG' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.editor', 'value:field.editor', 'editor', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.TXT.PARSE_CONTENT' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.parse', 'value:field.parse', 'parse', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.TXT.ALLOW_HTML' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.allowHtml', array( '0' => 'translate:[FM.TXT.ALLOW_HTML_NO]', '1' => 'translate:[FM.TXT.ALLOW_HTML_YES]', '2' => 'translate:[FM.TXT.ALLOW_HTML_RAW]'  ), 'value:field.allowHtml', false, 'id=allowHtml, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="field.allowedTags">
						<?php $this->txt( 'FM.TXT.ALLOWED_TAGS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'field.allowedTags', 'value:allowedTags', false, 550, 30, 'id=field.allowedTags' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="field.allowedAttributes">
						<?php $this->txt( 'FM.TXT.ALLOWED_ATTR' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'field.allowedAttributes', 'value:allowedAttributes', false, 550, 30, 'id=field.allowedAttributes' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.ADD_META_DESC' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.addToMetaDesc', 'value:field.addToMetaDesc', 'addToMetaDesc', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.IS_SEARCHABLE' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.inSearch', 'value:field.inSearch', 'inSearch', 'yes_no', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.SEARCH_PRIORITY' ); ?></td>
				<td>
					<?php $this->field( 'select', 'field.priority', array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10  ), 'value:field.priority', false, 'id=priority, size=1, class=inputbox spCfgNumberSelectList' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.FIELD_WIDTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.width', 'value:field.width', 'id=field_width, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>&nbsp;px.
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.FIELD_HEIGHT' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.height', 'value:field.height', 'id=field_height, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>&nbsp;px.
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.MAX_LENGTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.maxLength', 'value:field.maxLength', 'id=field_max_length, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>