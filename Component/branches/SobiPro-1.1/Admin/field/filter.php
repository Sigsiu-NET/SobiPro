<?php
/**
 * @version: $Id: filter.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/filter.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
$row = 0;
$m = null;
SPFactory::header()->addJsCode( "
	SPErrMsg = SobiPro.Txt( 'PLEASE_FILL_IN_ALL_REQUIRED_FIELDS' );
	function SP_submitbutton( task )
	{
		if ( task == 'filter.delete' ) {
			if( confirm( SobiPro.Txt( 'DEL_FILTER_WARN' ) ) ) {
				submitform( task );
			}
		}
		else if ( SPValidateForm()  ) {
			submitform( task );
		}
	} " );
$a = array( 'id' => 'filter.regex', 'class' => 'inputbox required' );
$b = array( 'id' => 'filter.id', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required' );
if( !( $this->get( 'filter.editable' ) ) ) {
	$b[ 'readonly' ] = 'readonly';
	$a[ 'disabled' ] = 'disabled';
	$m = Sobi::Txt( 'FLR.FILTER_CORE_NE');
}
?>
<fieldset class="adminform">
	<table class="admintable">
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'FLR.FILTER_ID' ); ?>
			</td>
			<td>
				<?php $this->field( 'text', 'filter.id', 'value:filter.id', $b ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'FLR.FILTER_NAME' ); ?>
			</td>
			<td>
				<?php $this->field( 'text', 'filter.name', 'value:filter.name', array( 'id' => 'filter.name', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox required' ) ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'FLR.FILTER_MSG' ); ?>
			</td>
			<td>
				<?php $this->field( 'textarea', 'filter.message', 'value:filter.message', false, 315, 30, array( 'id' => 'filter.regex', 'class' => 'inputbox required' ) ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'FLR.FILTER_REGEX' ); ?>
			</td>
			<td>
				<span style="color:blue; font-weight:bold;"><?php echo $m; ?></span>
				<?php $this->field( 'textarea', 'filter.regex', 'value:filter.regex', false, 315, 30, $a ); ?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<?php $this->field( 'button', 'send', 'translate:[FLR.SAVE_BT]', array( 'class' => 'text_area' , 'onclick' => "SP_submitbutton( 'filter.save' );" ) ); ?>
				<?php
					if( $this->get( 'filter.editable' ) && $this->get( 'filter.id' ) ) {
						$this->field( 'button', 'delete', 'translate:[FLR.DEL_BT]', array( 'class' => 'text_area', 'onclick' => "SP_submitbutton( 'filter.delete' );" ) );
					}
				?>
			</td>
		</tr>
	</table>
</fieldset>