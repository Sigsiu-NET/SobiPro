<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPRequest::set( 'hidemainmenu', 1 );
SPFactory::header()
		->addCSSCode( 'body { min-width: 1200px; }' )
		->addJsCode( "
			var SPErrMsg = SobiPro.Txt( 'Please fill in all required fields!' );
	        function submitbutton( task )
	        {
	            var form = document.adminForm;
	            if ( task == 'field.cancel' || SPValidateForm() ) {
	                if ( SP_id( 'field.nid' ).value == '' ) {
	                    SP_id( 'field.nid' ).value = SP_id( 'field.name' ).value;
	                }
	                var nid = SP_id( 'field.nid' ).value;
	                nid = nid.replace( /(\s+)/g, '_' );
	                nid = nid.replace( /[^\w_]/g, '' );
	                SP_id( 'field.nid' ).value = nid.toLowerCase();
	                if ( SP_id( 'field.nid' ).value.indexOf( 'field_' ) != 0 ) {
	                    SP_id( 'field.nid' ).value = 'field_' + SP_id( 'field.nid' ).value;
	                }
	                submitform( task );
	            }
	        }
	        SobiPro.jQuery( document ).ready( function ()
	        {
	        	Joomla.submitform = function ( task )
	            {
	                submitbutton( task );
	            }
	         } );
        " );
$row = 0;
?>
<?php $this->trigger( 'OnStart' ); ?>

<div>
    <fieldset class="adminform">
        <table class="admintable">
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.FIELD_LABEL' ); ?>
                </td>
                <td>
					<?php $this->field( 'text', 'field.name', 'value:field.name', 'id=field.name, size=50, maxlength=255, class=inputbox required' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.ALIAS' ); ?>
                </td>
                <td>
					<?php $this->field( 'text', 'field.nid', 'value:field.nid', 'id=field.nid, size=50, maxlength=255, class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key" valign="top">
					<?php $this->txt( 'FM.SUFFIX' ); ?>
                </td>
                <td>
					<?php $this->field( 'text', 'field.suffix', 'value:field.suffix', 'id=field.suffix, size=50, maxlength=255, class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key" valign="top">
					<?php $this->txt( 'FM.NOTICES' ); ?>
                </td>
                <td>
					<?php $this->field( 'textarea', 'field.notice', 'value:field.notice', false, 550, 30, 'id=field.notice' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key" valign="top">
					<?php $this->txt( 'FM.DESCRIPTION' ); ?>
                </td>
                <td>
					<?php $this->field( 'textarea', 'field.description', 'value:field.description', false, 550, 150, 'id=field.description' ); ?>
                </td>
            </tr>
        </table>
    </fieldset>
	<?php $this->trigger( 'AfterFixed' ); ?>
</div>
<div>
    <fieldset class="adminform" style="border: 1px dashed silver;">
        <legend><?php $this->txt( 'FM.FIELD_PARAMETERS' ); ?></legend>
        <table class="admintable">
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'ENABLED' ); ?>
                </td>
                <td>
					<?php $this->field( 'states', 'field.enabled', 'value:field.enabled', 'enabled', 'enabled', 'class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.AVAILABLE_IN' ); ?>
                </td>
                <td>
					<?php $this->field( 'select', 'field.showIn', 'both=translate:[FM.BOTH_OPT], details=translate:[FM.DETAILS_VIEW_OPT], vcard=translate:[FM.V_CARD_OPT], hidden=translate:[FM.HIDDEN_OPT]', 'value:field.showIn', false, 'id=show_in, size=1, class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.CSS_CLASS' ); ?>
                </td>
                <td>
					<?php
					$params = [ 'id' => 'field_css', 'size' => 15, 'maxlength' => 50, 'class' => 'inputbox', 'style' => 'text-align:center;' ];
					if ( $this->get( 'field.id' ) == SPFactory::config()->nameField()->get( 'id' ) ) {
						$params[ 'readonly' ] = 'readonly';
					}
					$this->field( 'text', 'field.cssClass', 'value:field.cssClass', $params );
					?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.ADM_FIELD' ); ?>
                </td>
                <td>
					<?php $this->field( 'states', 'field.adminField', 'value:field.adminField', 'admin_field', 'yes_no', 'class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.IS_REQUIRED' ); ?>
                </td>
                <td>
					<?php $this->field( 'states', 'field.required', 'value:field.required', 'required', 'yes_no', 'class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.IS_EDITABLE' ); ?>
                </td>
                <td>
					<?php $this->field( 'states', 'field.editable', 'value:field.editable', 'editable', 'yes_no', 'class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.SHOW_LABEL' ); ?>
                </td>
                <td>
					<?php $this->field( 'states', 'field.withLabel', 'value:field.withLabel', 'withLabel', 'yes_no', 'class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'FM.EDIT_LIMITS' ); ?>
                </td>
                <td>
					<?php $this->field( 'text', 'field.editLimit', 'value:field.editLimit', 'id=field_edit_limit, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key">
					<?php $this->txt( 'VERSION' ); ?>
                </td>
                <td>
                    &nbsp;<b><?php $this->show( 'field.version' ); ?></b>
                </td>
            </tr>
        </table>
    </fieldset>
	<?php $this->trigger( 'AfterFixedParams' ); ?>
    <fieldset class="adminform" style="border: 1px dashed silver;">
        <legend>
			<?php $this->txt( 'FM.FIELD_TYPE' ); ?>
        </legend>
        <table class="admintable">
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key" valign="top">
					<?php $this->txt( 'FM.FIELD_TYPE' ); ?>
                </td>
                <td>
					<?php $this->field( 'select', 'field.fieldType', 'value:types', 'value:field.fieldType', false, 'id=field_type, size=15, class=inputbox required, style=width:200px;', 'field_type' ); ?>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset class="adminform" style="border: 1px dashed silver;">
        <legend>
			<?php $this->txt( 'FM.PAYMENT' ); ?>
        </legend>
        <table class="admintable">
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key" valign="top">
					<?php $this->txt( 'FM.FOR_FREE' ); ?>
                </td>
                <td>
					<?php $this->field( 'states', 'field.isFree', 'value:field.isFree', 'is_free', 'yes_no', 'class=inputbox' ); ?>
                </td>
            </tr>
            <tr class="row<?php echo ++$row % 2; ?>">
                <td class="key" valign="top">
					<?php $this->txt( 'FM.FIELD_FEE' ); ?>
                </td>
                <td>
					<?php
					$this->field( 'text', 'field.fee', 'value:field.fee', 'id=field_fee, size=10, maxlength=10, class=inputbox, style=text-align:center;' );
					?>
                    &nbsp; <?php echo Sobi::Cfg( 'payments.currency', 'EUR' ); ?>
                </td>
            </tr>
        </table>
    </fieldset>
	<?php $this->trigger( 'AfterParams' ); ?>
</div>
<?php $this->trigger( 'BeforeFixed' ); ?>

<?php $this->trigger( 'OnEnd' ); ?>
