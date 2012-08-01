<?php
/**
 * @version: $Id: edit.php 1904 2011-09-26 12:22:32Z Radek Suski $
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
 * $Date: 2011-09-26 14:22:32 +0200 (Mon, 26 Sep 2011) $
 * $Revision: 1904 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/entry/edit.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPRequest::set( 'hidemainmenu', 1 );
SPFactory::header()->addCSSCode( 'body { min-width: 1200px; }' );
$row = 0;
?>
<script language="javascript" type="text/javascript">
	SPErrMsg = SobiPro.Txt( "$field is required. Please fill in all required fields!" );
	var CatWin;
	function submitbutton( task )
	{
		var form = document.adminForm;
		if( task == 'entry.cancel' || SPValidateForm()  ) {
			try {
				if( SP_id( 'entry.nid' ).value == '' ) {
					SP_id( 'entry.nid' ).value = SP_id( '<?php echo Sobi::Cfg( 'entry.name_field_nid' ); ?>' ).value;
				}
				var nid = SP_id( 'entry.nid' ).value;
				nid = nid.replace( /(\s+)/g, '_' );
				nid = nid.replace( /[^\w_]/g, '' );
				SP_id( 'entry.nid' ).value = nid.toLowerCase();
			} catch ( e ) {}
			submitform( task );
		}
	}
	try { Joomla.submitbutton = function( task ) { submitbutton( task ); } } catch( e ) {}
	function SP_close()
	{
		var separator = '<?php echo Sobi::Cfg( 'string.path_separator', ' > '  ); ?>';
		$( 'sbox-btn-close' ).fireEvent( 'click' );
	}
	window.addEvent( 'domready', function() {
		try {
			SqueezeBox.assign( SP_id( 'entry_parent_path' ), { handler: 'iframe', size: { x: 650, y: 430 }, url: '<?php $this->show( 'cat_chooser_url' ); ?>' });
		}
		catch( x ) {
			$( 'entry_parent_path' ).addEvent('click', function( e ) {
				new Event( e ).stop();
				SqueezeBox.fromElement( SP_id( 'entry_parent_path' ), { url: '<?php $this->show( 'cat_chooser_url' ); ?>', handler: 'iframe', size: { x: 650, y: 430 } } );
			} );
		}
	} );
</script>
<?php $this->trigger( 'OnStart' ); ?>
<div class="col width-70">
	<fieldset class="adminform">
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="entry.path">
						<?php $this->txt( 'EN.SELECTED_CATS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'parent_path', 'value:parent_path', false, 550, 60, array( 'id' => 'entry.path', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required', 'readonly' => 'readonly' ) ); ?>
					<br />
					<?php $this->field( 'text', 'entry.parent', 'value:parents', array( 'id' => 'entry.parent', 'size' => 15, 'maxlength' => 50, 'class' => 'inputbox required', 'readonly' => 'readonly', 'style' => 'text-align:center;' ) ); ?>
					<?php $this->field( 'button', 'parent_path', 'translate:[EN.SELECT_CATEGORY_PATH]', array( 'id'=>'entry_parent_path', 'size' => 50, 'class' => 'button', 'style' => 'border: 1px solid silver;' ) ); ?>
				</td>
			</tr>
			<?php
				$c = $this->count( 'fields' );
				for ( $i = 0; $i < $c ; $i++ ) {
			?>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="<?php $this->show( 'fields.nid', $i ); ?>">
						<?php $this->show( 'fields.name', $i ); ?>
					</label>
				</td>
				<td>
					<?php $this->get( 'fields', $i )->field(); ?>&nbsp;<?php echo $this->get( 'fields', $i )->get( 'suffix' );?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</fieldset>
</div>
<?php $this->trigger( 'AfterBasic' ); ?>
<div class="col width-30">
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<legend>
			<?php $this->txt( 'EN.PARAMS' ); ?>
		</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="entry.nid">
						<?php $this->txt( 'EN.ALIAS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'entry.nid', 'value:entry.nid', array( 'id' => 'entry.nid', 'size' => 30, 'maxlength' => 255, 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'STATE' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'entry.state', 'value:entry.state', 'state', 'state', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'CREATED_AT' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'entry.createdTime', 'value:entry.createdTime', 'created' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'VALID_SINCE' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'entry.validSince', 'value:entry.validSince', 'valid_since' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'VALID_UNTIL' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'entry.validUntil', 'value:entry.validUntil', 'valid_until' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'AUTHOR' ); ?>
				</td>
				<td>
					<?php $this->show( 'owner' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'COUNTER' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'category.count', 'value:entry.counter', array( 'id' => 'sp_counter', 'size' => 8, 'class' => 'inputbox' , 'readonly' => 'readonly', 'disabled' => 'disabled', 'style' => 'text-align: center; font-weight: bold; color: #000011' ) ); ?>
					<?php $this->field( 'button', 'reset_counter', 'translate:[EN.RESET_COUNT]', array( 'id'=>'reset_counter', 'size' => 20, 'class' => 'button', 'style' => 'border: 1px solid silver;', 'onclick' => 'SPResetCount( "entry" )' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'VERSION' ); ?>
				</td>
				<td>
					<?php $this->show( 'entry.version' ); ?>
				</td>
			</tr>
			<?php if( $this->get( 'entry.updater' ) ) { ?>
				<tr class="row<?php echo ++$row%2; ?>">
					<td class="key">
						<?php $this->txt( 'EN.UPDATED_TIME' ); ?>
					</td>
					<td>
						<?php $this->show( 'entry.updatedTime' ); ?>
					</td>
				</tr>
				<tr class="row<?php echo ++$row%2; ?>">
					<td class="key">
						<?php $this->txt( 'EN.UPDATED_NAME' ); ?>
					</td>
					<td>
						<?php
							$up = SPUser::getBaseData( $this->get( 'entry.updater' ) );
							if( isset( $up[ $this->get( 'entry.updater' ) ] ) ) {
								echo $up[ $this->get( 'entry.updater' ) ]->name;
							}
						?>
					</td>
				</tr>
				<tr class="row<?php echo ++$row%2; ?>">
					<td class="key">
						<?php $this->txt( 'EN.UPDATED_IP' ); ?>
					</td>
					<td>
						<?php $this->show( 'entry.updaterIP' ); ?>
					</td>
				</tr>
			<?php } ?>
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
					<?php $this->field( 'textarea', 'entry.metaDesc', 'value:entry.metaDesc', false, 200, 50, 'id=entry.metaDesc' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'META_KEYS' ); ?>
				</td>
				<td>
					<?php $this->field( 'textarea', 'entry.metaKeys', 'value:entry.metaKeys', false, 200, 50, 'id=entry.metaKeys' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="entry.metaAuthor">
						<?php $this->txt( 'AUTHOR' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'entry.metaAuthor', 'value:entry.metaAuthor', 'id=entry.metaAuthor, size=30, maxlength=255, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="entry.metaRobots">
						<?php $this->txt( 'META_ROBOTS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'entry.metaRobots', 'value:entry.metaRobots', 'id=entry.metaRobots, size=30, maxlength=255, class=inputbox' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<?php $this->trigger( 'AfterMeta' ); ?>
</div>
<?php $this->trigger( 'OnEnd' ); ?>