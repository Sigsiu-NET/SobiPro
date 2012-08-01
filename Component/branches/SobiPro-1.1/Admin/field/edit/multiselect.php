<?php
/**
 * @version: $Id: multiselect.php 1920 2011-10-06 18:07:18Z Radek Suski $
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
 * $Date: 2011-10-06 20:07:18 +0200 (Thu, 06 Oct 2011) $
 * $Revision: 1920 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/edit/multiselect.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addJsCode( 'var SPoptCount = '.count( $this->get( 'options' ), true ).';' );
SPFactory::header()->addJsFile( 'select_list', true );
?>
<div class="col width-70" style="float: left;">
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<legend>
			<?php $this->txt( 'FM.MSELECT_SPEC_PARAMS' ); ?>
		</legend>
		<table class="admintable">
			<!--
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php //$this->txt( 'FM.SEL.SELECT_LABEL' ); ?>
				</td>
				<td>
					<?php //$this->field( 'text', 'field.selectLabel', 'value:field.selectLabel', 'id=field_selectLabel, size=30, maxlength=150, class=inputbox' ); ?>
				</td>
			</tr>
   -->
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
					<?php $this->txt( 'FM.FIELD_SIZE' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.size', 'value:field.size', 'id=field_size, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>
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
					<?php $this->txt( 'FM.SEARCH_METHOD' ); ?>
				</td>
				<td>
					<?php $this->field( 'select', 'field.searchMethod', array( 'general' => 'translate:[FM.GENERAL_SEARCH_OPT]', 'select' => 'translate:[FM.SELECT_LIST_OPT]', 'mselect' => 'translate:[FM.MSELECT_LIST_OPT]', ), 'value:field.searchMethod', false, 'id=searchMethod, size=1, class=inputbox' ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.SEL.WIDTH_IN_SEARCH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.swidth', 'value:field.swidth', 'id=field_swidth, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>&nbsp;px.
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.SEL.SIZE_IN_SEARCH' ); ?>
				</td>
				<td>
					<?php $this->field( 'text', 'field.ssize', 'value:field.ssize', 'id=field_ssize, size=5, maxlength=10, class=inputbox, style=text-align:center;' ); ?>
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
					<?php $this->txt( 'FM.UPLOAD_OPT_FILE' ); ?></td>
				<td>
					<?php $this->field( 'file', 'spfieldsopts', null, array( 'class' => 'inputbox' ) ); ?>
				</td>
			</tr>
			<tr class="row<?php echo ++$row%2; ?>">
				<td class="key">
					<?php $this->txt( 'FM.SEL.LIST_OPTIONS' ); ?>
				</td>
				<td>
					<div>
						<ul id="spOptions0">
							<li class="spOptionHead">
								<input type="button" class="inputbox SPnewOpt" value="<?php $this->txt( 'FM.SEL.ADD_NEW_OPTION' ); ?>"/>
								<input type="button" id="SPnewOptGr" class="inputbox" value="<?php $this->txt( 'FM.SEL.ADD_NEW_OPTION_GROUP' ); ?>"/>
							</li>
							<?php $options = $this->get( 'options' ); ?>
							<?php $c = 0; ?>
							<?php $lc = 0; ?>
							<?php foreach ( $options as $option ) { ?>
								<?php $c++; ?>
								<?php $id = ( isset( $option[ 'options' ] ) ) ? 'gid' : 'id'; ?>
								<?php $class = isset( $option[ 'options' ] ) ? 'spOptionGroup' : 'spOption'; ?>
								<li class="<?php echo $class; ?>">
									<div class="spOptionContent">
										<?php if( isset( $option[ 'options' ] ) ) $this->txt( 'FM.SEL.OPTION_GROUP' ); ?>
										<input name="field.options[<?php echo $c; ?>][<?php echo $id; ?>]" value="<?php echo $option[ 'id' ]; ?>" size="20" maxlength="50" style="text-align: center;" type="text"/>
										<input name="field.options[<?php echo $c; ?>][name]" value="<?php echo $option[ 'label' ]; ?>" size="40" maxlength="50" style="text-align: center;" type="text"/>
									</div>
									<div class="SPOptDel"></div>
									<div class="SPhandle"></div>
									<?php if( isset( $option[ 'options' ] ) ) {  ?>
										<?php $lc++; ?>
										<?php SPFactory::header()->addJsCode( "
											window.addEvent( 'domready', function() {
												SPsortables[ {$lc} ] = new Sortables( 'spOptions{$lc}', { onComplete: function( el ) {  SPresortOpts( el ); } } );
												SPchangeHandle( 'spOptions{$lc}', '.sSPhandle' );
											} );
										" ); ?>
										<div class="spClearFix"></div>
										<div class="spOptGroup">
											<ul id="spOptions<?php echo $lc; ?>">
												<li class="sspOptionHead">
													<input type="button" class="inputbox SPnewOpt" value="<?php $this->txt( 'FM.SEL.ADD_NEW_OPTION' ); ?>"/>
												</li>
												<?php if( count( $option[ 'options' ] ) ) { ?>
													<?php $sc = 0; ?>
													<?php foreach ( $option[ 'options' ] as $subOption ) { ?>
														<?php $sc++; ?>
														<li class="sspOption">
															<div class="sspOptionContent">
																<input name="field.options[<?php echo $c; ?>][<?php echo $sc; ?>][id]" value="<?php echo $subOption[ 'id' ]; ?>" size="20" maxlength="50" style="text-align: center;" type="text"/>
																<input name="field.options[<?php echo $c; ?>][<?php echo $sc; ?>][name]" value="<?php echo $subOption[ 'label' ]; ?>" size="40" maxlength="50" style="text-align: center;" type="text"/>
															</div>
															<div class="SPOptDel"></div>
															<div class="sSPhandle"></div>
														</li>
													<?php } ?>
												<?php } ?>
											</ul>
										</div>
										<div class="spClearFix"></div>
									<?php } ?>
								</li>
							<?php } ?>
						</ul>
					</div>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<ul id="spOptionsDummy" style="display: none;"><li class="spOption"><div class="spOptionContent"><input name="__.options[1][id]" value="<?php echo str_replace( 'field_', null, $this->get( 'field.nid' ) ); ?>_option_1" size="20" maxlength="50" style="text-align: center;" type="text"/><input name="__.options[1][name]" value="<?php $this->txt( 'FM.SEL.OPTION_NAME' ); ?>" size="40" maxlength="50" style="text-align: center;" type="text"/></div><div class="SPOptDel"></div><div class="SPhandle"></div></li><li class="spOptionGroup"><div class="spOptionContent"><?php $this->txt( 'FM.SEL.OPTION_GROUP' ); ?><input name="__.options[2][gid]" value="<?php echo str_replace( 'field_', null, $this->get( 'field.nid' ) ); ?>_group_1" size="20" maxlength="50" style="text-align: center;" type="text"/><input name="__.options[2][name]" value="<?php $this->txt( 'FM.SEL.GROUP_NAME' ); ?>" size="40" maxlength="50" style="text-align: center;" type="text"/></div><div class="SPOptDel"></div><div class="SPhandle"></div><div class="spClearFix"></div><div class="spOptGroup"><ul><li class="sspOptionHead"><input type="button" class="inputbox SPnewOpt" value="<?php $this->txt( 'FM.SEL.ADD_NEW_OPTION' ); ?>"/></li><li class="sspOption"><div class="sspOptionContent"><input name="__.options[2][1][id]" value="option_id_1" size="20" maxlength="50" style="text-align: center;" type="text"/><input name="__.options[2][1][name]" value="<?php $this->txt( 'FM.SEL.OPTION_NAME' ); ?>" size="40" maxlength="50" style="text-align: center;" type="text"/></div><div class="SPOptDel"></div><div class="sSPhandle"></div></li></ul></div><div class="spClearFix"></div></li></ul>