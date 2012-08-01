<?php
/**
 * @version: $Id: edit.php 1454 2011-06-01 10:30:07Z Radek Suski $
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
 * $Date: 2011-06-01 12:30:07 +0200 (Wed, 01 Jun 2011) $
 * $Revision: 1454 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/section/edit.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPRequest::set( 'hidemainmenu', 1 );
$row = 0;
?>
<script language="javascript" type="text/javascript">
	SPErrMsg = SobiPro.Txt( "PLEASE_FILL_IN_ALL_REQUIRED_FIELDS!" );
	function submitbutton( task )
	{
		var form = document.adminForm;
		if ( task == 'section.cancel' || SPValidateForm()  ) {
			if( SP_id( 'section.nid' ).value == '' ) {
				SP_id( 'section.nid' ).value = SP_id( 'section.name' ).value;
			}
			var nid = SP_id( 'section.nid' ).value;
			nid = nid.replace( /(\s+)/g, '_' );
			nid = nid.replace( /[^\w_]/g, '' );
			SP_id( 'section.nid' ).value = nid.toLowerCase();
			submitform( task );
		}
	}
	try { Joomla.submitbutton = function( task ) { submitbutton( task ); } } catch( e ) {}
</script>
<?php $this->trigger( 'OnStart' ); ?>
<div class="col width-70">
	<fieldset class="adminform">
	<table class="admintable">
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<label for="section.name">
					<?php $this->txt( 'SEC.ADD_SECTION_NAME' ); ?>
				</label>
			</td>
			<td>
				<?php $this->field( 'text', 'section.name', 'value:section.name', 'id=section.name, size=50, maxlength=255, class=inputbox required' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<label for="section.nid">
					<?php $this->txt( 'SEC.ADD_SECTION_ALIAS' ); ?>
				</label>
			</td>
			<td>
				<?php $this->field( 'text', 'section.nid', 'value:section.nid', 'id=section.nid, size=50, maxlength=255, class=inputbox' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key" valign="top">
				<label for="section.description">
					<?php $this->txt( 'SEC.ADD_DESC' ); ?>
				</label>
			</td>
			<td>
				<?php $this->field( 'textarea', 'section.description', 'value:section.description', true, 550, 350, 'id=section.description' ); ?>
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<?php $this->trigger( 'AfterBasic' ); ?>
<div class="col width-30">
	<fieldset class="adminform" style="border: 1px dashed silver;"><legend><?php $this->txt( 'Parameters' ); ?></legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key"><?php $this->txt( 'STATE' ); ?></td>
				<td><?php $this->field( 'states', 'section.state', 'value:section.state', 'state', 'state', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key"><?php $this->txt( 'CREATED_AT' ); ?></td>
				<td><?php $this->field( 'calendar', 'section.createdTime', 'value:section.createdTime', 'created' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key"><?php $this->txt( 'VALID_SINCE' ); ?></td>
				<td><?php $this->field( 'calendar', 'section.validSince', 'value:section.validSince', 'valid_since' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key"><?php $this->txt( 'VALID_UNTIL' ); ?></td>
				<td><?php $this->field( 'calendar', 'section.validUntil', 'value:section.validUntil', 'valid_until' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<?php $this->trigger( 'AfterParams' ); ?>
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<legend>
			<?php $this->txt( 'META_DATA' ); ?>
		</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'META_DESCRIPTION' ); ?>
				</td>
				<td>
					<?php $this->field( 'textarea', 'section.metaDesc', 'value:section.metaDesc', false, 200, 50, 'id=section.metaDesc' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'META_KEYS' ); ?>
				</td>
				<td>
					<?php $this->field( 'textarea', 'section.metaKeys', 'value:section.metaKeys', false, 200, 50, 'id=section.metaKeys' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="section.metaAuthor">
						<?php $this->txt( 'AUTHOR' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'section.metaAuthor', 'value:section.metaAuthor', 'id=section.metaAuthor, size=30, maxlength=255, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="section.metaRobots">
						<?php $this->txt( 'META_ROBOTS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'section.metaRobots', 'value:section.metaRobots', 'id=section.metaRobots, size=30, maxlength=255, class=inputbox' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<?php $this->trigger( 'AfterMeta' ); ?>
</div>
<?php $this->trigger( 'OnEnd' ); ?>