<?php
/**
 * @version: $Id: radio.php 1317 2011-05-11 15:53:35Z Sigrid Suski $
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
 * $Date: 2011-05-11 17:53:35 +0200 (Wed, 11 May 2011) $
 * $Revision: 1317 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/edit/radio.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addJsCode( 'var SPoptCount = '.$this->count( 'options' ).';' );
SPFactory::header()->addJsFile( 'select_list', true );
?>
<div class="col width-70" style="float: left;">
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<legend>
			<?php $this->txt( 'FM.RADIO_SPEC_PARAMS' ); ?>
		</legend>
		<table class="admintable">
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.RADIO.OPTS_IN_LINE' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.optInLine', array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8  ), 'value:field.optInLine', false, 'id=optInLine, size=1, class=inputbox spCfgNumberSelectList' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.RADIO.CELL_WIDTH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.optWidth', 'value:field.optWidth', 'id=field_optWidth, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>&nbsp;px.
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.RADIO.LABEL_SITE' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.labelSite', 'right=translate:[FM.RADIO.RIGHT_SITE_OPT],left=translate:[FM.RADIO.LEFT_SITE_OPT]', 'value:field.labelSite', false, 'id=label_site, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.ADD_META_KEYS' ); ?>
				</td>
				<td>
					<?php $this->field( 'states', 'field.addToMetaKeys', 'value:field.addToMetaKeys', 'addToMetaKeys', 'yes_no', 'class=inputbox' ); ?>
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
					<?php $this->txt( 'FM.SEARCH_PRIORITY' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.priority', array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10  ), 'value:field.priority', false, 'id=priority, size=1, class=inputbox spCfgNumberSelectList' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.SEARCH_METHOD' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.searchMethod', array( 'general' => 'translate:[FM.GENERAL_SEARCH_OPT]', 'chbx' => 'translate:[FM.CHECKBOX_GROUP_OPT]', 'radio' => 'translate:[FM.RADIO_BUTTONS_OPT]', 'select' => 'translate:[FM.SELECT_LIST_OPT]', 'mselect' => 'translate:[FM.MSELECT_LIST_OPT]' ), 'value:field.searchMethod', false, 'id=searchMethod, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.RADIO.DEFSEL' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.defSel', 'value:field.defSel', 'id=field_defSel, size=10, maxlength=100, class=inputbox, style=text-align:center;' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.RADIO.LIST_OPTIONS' ); ?>
				</td>
				<td>
					<div>
						<ul id="spOptions0">
							<li class="spOptionHead">
								<input type="button" class="inputbox SPnewOpt" value="<?php $this->txt( 'FM.RADIO.ADD_NEW_OPTION' ); ?>"/>
							</li>
							<?php $options = $this->get( 'options' ); ?>
							<?php $c = 0; ?>
							<?php foreach ( $options as $option ) { ?>
								<?php $c++; ?>
								<li class="spOption">
									<div class="spOptionContent">
										<?php if( isset( $option[ 'options' ] ) ) $this->txt( 'FM.RADIO.OPTION_GROUP' ); ?>
										<input name="field.options[<?php echo $c; ?>][id]" value="<?php echo $option[ 'id' ]; ?>" size="20" maxlength="50" style="text-align: center;" type="text">
										<input name="field.options[<?php echo $c; ?>][name]" value="<?php echo $option[ 'label' ]; ?>" size="40" maxlength="50" style="text-align: center;" type="text">
									</div>
									<div class="SPOptDel"></div>
									<div class="SPhandle"></div>
								</li>
							<?php } ?>
						</ul>
					</div>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<ul id="spOptionsDummy" style="display: none;"><li class="spOption"><div class="spOptionContent"><input name="__.options[1][id]" value="<?php echo str_replace( 'field_', null, $this->get( 'field.nid' ) ); ?>_option_1" size="20" maxlength="50" style="text-align: center;" type="text"><input name="__.options[1][name]" value="<?php $this->txt( 'FM.RADIO.OPTION_NAME' ); ?>" size="40" maxlength="50" style="text-align: center;" type="text"></div><div class="SPOptDel"></div><div class="SPhandle"></div></li></ul>