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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/category/edit.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPRequest::set( 'hidemainmenu', 1 );
SPFactory::header()->addCSSCode( 'body { min-width: 1200px; }' );
$row = 0;
?>
<script language="javascript" type="text/javascript">
	SPErrMsg = SobiPro.Txt( "PLEASE_FILL_IN_ALL_REQUIRED_FIELDS!" );
	function submitbutton( task )
	{
		var form = document.adminForm;
		if ( task == 'category.cancel' || SPValidateForm()  ) {
			if( SP_id( 'category.nid' ).value == '' ) {
				SP_id( 'category.nid' ).value = SP_id( 'category.name' ).value;
			}
			var nid = SP_id( 'category.nid' ).value;
			nid = nid.replace( /(\s+)/g, '_' );
			nid = nid.replace( /[^\w_]/g, '' );
			SP_id( 'category.nid' ).value = nid.toLowerCase();
			submitform( task );
		}
	}
	try { Joomla.submitbutton = function( task ) { submitbutton( task ); } } catch( e ) {}
	function SP_close()
	{
		$( 'sbox-btn-close' ).fireEvent( 'click' );
	}
	function SPSelectIcon( src, name )
	{
		$( 'sbox-btn-close' ).fireEvent( 'click' );
		$( 'cat_ico_path' ).value = name;
		$( 'cat_ico' ).innerHTML = '<img src="'+src+'" style="max-width: 55px; max-height: 55px;"/>';
	}
	window.addEvent( 'domready', function() {
		<?php if( $this->get( 'category_icon' ) ) { ?>
			$( 'cat_ico' ).innerHTML = '<img src="<?php $this->show( 'category_icon' ); ?>" style="max-width: 55px; max-height: 55px;" />';
		<?php } ?>
		try {
			SqueezeBox.assign( $( 'cat_ico' ), {  handler: 'iframe', size: { x: 645, y: 500 }, url: '<?php $this->show( 'icon_chooser_url' ); ?>' } );
			SqueezeBox.assign( $( 'category_parent_path' ), {  handler: 'iframe', size: { x: 650, y: 430 }, url: '<?php $this->show( 'cat_chooser_url' ); ?>' });
		}
		catch( x ) {
			$( 'cat_ico' ).addEvent( 'click', function( e ) {
				new Event( e ).stop();
				SqueezeBox.fromElement( $( 'cat_ico' ), { url: '<?php $this->show( 'icon_chooser_url' ); ?>', handler: 'iframe', size: { x: 645, y: 500 } } );
			} );
			$( 'category_parent_path' ).addEvent( 'click', function( e ) {
				new Event( e ).stop();
				SqueezeBox.fromElement( $( 'category_parent_path' ), { url: '<?php $this->show( 'cat_chooser_url' ); ?>', handler: 'iframe', size: { x: 650, y: 430 } } );
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
					<label for="category.name">
						<?php $this->txt( 'CAT.CATEGORY_NAME' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'category.name', 'value:category.name', array( 'id' => 'category.name', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="category.nid">
						<?php $this->txt( 'CAT.CATEGORY_ALIAS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'category.nid', 'value:category.nid', array( 'id' => 'category.nid', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="category.path">
						<?php $this->txt( 'CAT.CATEGORY_PATH' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'parent_path', 'value:parent_path', false, 550, 30, array( 'id' => 'category.path', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required', 'readonly' => 'readonly' ) ); ?>
					<br />
					<?php $this->field( 'text', 'category.parent', 'value:parent', array( 'id' => 'category.parent', 'size' => 5, 'maxlength' => 50, 'class' => 'inputbox required', 'readonly' => 'readonly', 'style' => 'text-align:center;' ) ); ?>
					<?php $this->field( 'button', 'parent_path', 'translate:[CAT.SELECT_CATEGORY_PATH]', array( 'id'=>'category_parent_path', 'size' => 50, 'class' => 'button', 'style' => 'border: 1px solid silver;' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key" valign="top">
					<label for="category.introtext">
						<?php $this->txt( 'CAT.CATEGORY_INTROTEXT' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'category.introtext', 'value:category.introtext', false, 550, 30, 'id=category.introtext' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key" valign="top">
					<label for="category.description">
						<?php $this->txt( 'CAT.CATEGORY_DESCRIPTION' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'textarea', 'category.description', 'value:category.description', true, 550, 350, 'id=category.description' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<?php $this->trigger( 'AfterBasic' ); ?>
<div class="col width-30">
	<fieldset class="adminform" style="border: 1px dashed silver;">
	<legend>
		<?php $this->txt( 'PARAMETERS' ); ?>
	</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'STATE' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'category.state', 'value:category.state', 'state', 'state', 'class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'CREATED_AT' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'category.createdTime', 'value:category.createdTime', 'created' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'VALID_SINCE' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'category.validSince', 'value:category.validSince', 'valid_since' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'VALID_UNTIL' ); ?>
				</td>
				<td>
					<?php $this->field( 'calendar', 'category.validUntil', 'value:category.validUntil', 'valid_until' ); ?>
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
					<?php $this->txt( 'CAT.ICON' ); ?>
				</td>
				<td>
					<?php $this->field( 'button', 'cat_ico', 'translate:[CAT.SELECT_CATEGORY_ICON]', array( 'id'=>'cat_ico', 'size' => 50, 'class' => 'button', 'style' => 'border: 1px solid silver;' ) ); ?>
					<input type="hidden" name="category.icon" id="cat_ico_path" value="<?php $this->show( 'category.icon' );?>" >
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'CAT.SHOW_ICON' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'category.showIcon', '2=translate:[OPT_GLOBAL], 1=translate:[OPT_YES], 0=translate:[OPT_NO]', 'value:category.showIcon', false, array( 'id' => 'show_icon', 'size' => 1, 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'CAT.SHOW_INTROTEXT' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'category.showIntrotext', '2=translate:[OPT_GLOBAL], 1=translate:[OPT_YES], 0=translate:[OPT_NO]', 'value:category.showIntrotext', false, array( 'id' => 'show_introtext', 'size' => 1, 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'CAT.PARSE_CAT_DES' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'category.parseDesc', '2=translate:[OPT_GLOBAL], 1=translate:[OPT_YES], 0=translate:[OPT_NO]', 'value:category.parseDesc', false, array( 'id' => 'parse_desc', 'size' => 1, 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'COUNTER' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'category.count', 'value:category.counter', array( 'id' => 'sp_counter', 'size' => 8, 'class' => 'inputbox' , 'readonly' => 'readonly', 'disabled' => 'disabled', 'style' => 'text-align: center; font-weight: bold; color: #000011' ) ); ?>
					<?php $this->field( 'button', 'reset_counter', 'translate:[CAT.RESET_COUNT]', array( 'id'=>'reset_counter', 'size' => 20, 'class' => 'button', 'style' => 'border: 1px solid silver;', 'onclick' => 'SPResetCount( "category" )' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'VERSION' ); ?>
				</td>
				<td>
					<?php $this->show( 'category.version' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<?php $this->trigger( 'AfterParams' ); ?>
	<fieldset class="adminform" style="border: 1px dashed silver;"><legend><?php $this->txt( 'Meta data' ); ?></legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'META_DESCRIPTION' ); ?>
				</td>
				<td>
					<?php $this->field( 'textarea', 'category.metaDesc', 'value:category.metaDesc', false, 200, 50, array( 'id' => 'category.metaDesc' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'META_KEYS' ); ?>
				</td>
				<td>
					<?php $this->field( 'textarea', 'category.metaKeys', 'value:category.metaKeys', false, 200, 50, array( 'id' => 'category.metaKeys' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="category.metaAuthor">
						<?php $this->txt( 'AUTHOR' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'category.metaAuthor', 'value:category.metaAuthor', array( 'id' => 'category.metaAuthor', 'size' => 30, 'maxlength' => 255, 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<label for="category.metaRobots">
						<?php $this->txt( 'META_ROBOTS' ); ?>
					</label>
				</td>
				<td>
					<?php $this->field( 'text', 'category.metaRobots', 'value:category.metaRobots', array( 'id' => 'category.metaRobots', 'size' => 30, 'maxlength' => 255, 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<?php $this->trigger( 'AfterMeta' ); ?>
</div>
<?php $this->trigger( 'OnEnd' ); ?>