<?php
/**
 * @version: $Id: edit.php 1500 2011-06-21 11:47:15Z Radek Suski $
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
 * $Date: 2011-06-21 13:47:15 +0200 (Tue, 21 Jun 2011) $
 * $Revision: 1500 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/acl/edit.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPRequest::set( 'hidemainmenu', 1 );
SPFactory::header()->addCSSCode( 'body { min-width: 1200px; }' );
SPFactory::header()->addJsFile( 'jquery' );
$row = 0;
?>
<script language="javascript" type="text/javascript">
	SPErrMsg = SobiPro.Txt( "PLEASE_FILL_IN_ALL_REQUIRED_FIELDS" );
	function submitbutton( task )
	{
		var form = document.adminForm;
		if ( task == 'acl.cancel' || ( SPValidateForm() && SP_CheckVisitor() )  ) {
			if( SP_id( 'rule.nid' ).value == '' ) {
				SP_id( 'rule.nid' ).value = SP_id( 'rule.name' ).value;
			}
			var nid = SP_id( 'rule.nid' ).value;
			nid = nid.replace( /(\s+)/g, '_' );
			nid = nid.replace( /[^\w_]/g, '' );
			SP_id( 'rule.nid' ).value = nid.toLowerCase();
			submitform( task );
		}
	}
	function SP_CheckVisitor()
	{
		var dengerous = [ 17, 18, 19, 20, 21, 24 ];
		var resp = true;
		jQuery( '#rule_groups option:selected' ).each( function() {
			if( parseInt( jQuery( this ).val() ) == 0 ) {
				jQuery( '#front_permissions option:selected' ).each( function () {
					v = parseInt( jQuery( this ).val() );
					if( jQuery.inArray( v, dengerous ) > 0 ) {
						resp = confirm( SobiPro.Txt( "ACL_NONREG_ADM_WARN" ) );
					}
				} );
			}
		} );
		return resp;
	}
	try { Joomla.submitbutton = function( task ) { submitbutton( task ); } } catch( e ) {}
</script>
<?php $this->trigger( 'Start' ); ?>
<div>
	<fieldset class="adminform">
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="rule.name">
						<?php $this->txt( 'ACL.RULE_NAME' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'rule.name', 'value:rule.name', array( 'id' => 'rule.name', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required' ) ); ?>
				</td>
				<td class="key">
					<?php $this->txt( 'VALID_SINCE' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'rule.validSince', 'value:rule.validSince', 'valid_since' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="rule.nid">
						<?php $this->txt( 'ACL.ALIAS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'rule.nid', 'value:rule.nid', array( 'id' => 'rule.nid', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox' ) ); ?>
				</td>
				<td class="key">
					<?php $this->txt( 'VALID_UNTIL' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'rule.validUntil', 'value:rule.validUntil', 'valid_until' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key" valign="top">
					<label for="rule.note">
						<?php $this->txt( 'ACL.NOTES' ); ?>
					</label>
				</td>
				<td colspan="2">
					<?php $this->field( 'textarea', 'rule.note', 'value:rule.note', false, 350, 50, array( 'id' => 'rule.note' ) ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div>
	<fieldset class="adminform">
		<table class="adminlist" cellspacing="1">
			<tr class="row<?php echo ++$row%2; ?>">
				<th class="key" width="25%">
					<label for="rule.name">
						<?php $this->txt( 'ACL.AFFECTED_USER_GROUPS' ); ?>
					</label>
				</th>
				<th class="key" width="25%">
					<label for="rule.name">
						<?php $this->txt( 'ACL.AFFECTED_SECTIONS' ); ?>
					</label>
				</th>
				<th class="key" width="25%">
					<label for="rule.name">
						<?php $this->txt( 'ACL.FRONTEND_PERMISSIONS' ); ?>
					</label>
				</th>
				<th class="key" width="25%">
					<label for="rule.name">
						<?php //$this->txt( 'ACL.BACKEND_PERMISSIONS' ); ?>
					</label>
				</th>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td>
					<?php $this->field( 'select', 'rule.groups', 'value:groups', 'value:selected_groups', true, array( 'id' => 'rule_groups', 'size' => 20, 'class' => 'inputbox', 'style' => 'width: 200px;' ), false  ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'rule.sections', 'value:sections', 'value:selected_sections', true, array( 'id' => 'rule_sections', 'size' => 20, 'class' => 'inputbox', 'style' => 'width: 200px;' ), false  ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'rule.front_permissions', 'value:front_permissions', 'value:selected_permissions', true, array( 'id' => 'front_permissions', 'size' => 20, 'class' => 'inputbox', 'style' => 'width: 200px;' ), false  ); ?>
				</td>
				<td>
					<?php //$this->field( 'select', 'rule.adm_permissions', 'value:adm_permissions', 'value:selected_permissions', true, array( 'id' => 'rule_sections', 'size' => 15, 'class' => 'inputbox', 'style' => 'width:200px;' ), 'permissions'  ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<?php $this->trigger( 'End' ); ?>
