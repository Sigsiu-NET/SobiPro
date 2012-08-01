<?php
/**
 * @version: $Id: list.php 1979 2011-11-08 18:25:45Z Radek Suski $
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
 * $Date: 2011-11-08 19:25:45 +0100 (Tue, 08 Nov 2011) $
 * $Revision: 1979 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/list.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20.8em; margin-top: 3px;">
<div class="sheader icon-48-SobiFieldList">
	<?php $this->txt( 'FM.FIELDS_FOR', array( 'section' => $this->get( 'current_path' ) ) ); ?>
</div>
<?php $this->trigger( 'BeforeDisplayList' ); ?>
<table class="adminlist" cellspacing="1" style="width: 100%">
	<thead>
		<tr>
			<th width="5%">
				<?php $this->show( 'header.fid' ); ?>
			</th>
			<th width="5%">
				<?php $this->show( 'header.checkbox' ); ?>
			</th>
			<th class="title">
				<?php $this->show( 'header.name' ); ?>
			</th>
			<th width="10%">
				<?php $this->show( 'header.state' ); ?>
			</th>
			<th width="10%">
				<?php $this->show( 'header.fieldType' ); ?>
			</th>
			<th width="10%">
				<?php $this->show( 'header.showIn' ); ?>
			</th>
			<th width="10%">
				<?php $this->show( 'header.required' ); ?>
			</th>
			<th width="10%">
				<?php $this->show( 'header.editable' ); ?>
			</th>
			<th width="10%">
				<?php $this->show( 'header.isFree' ); ?>
			</th>
			<th width="15%">
				<?php $this->show( 'header.order' ); ?>
			</th>
		</tr>
	</thead>
	<?php
		$c = $this->count( 'fields' );
		for ( $i = 0; $i < $c ; $i++ ) {
			$style = $i%2;
	?>
	<tr class="row<?php echo $style;?>">
		<td style="text-align: center">
			<?php $this->show( 'fields.id', $i ); ?>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.checkbox', $i ); ?>
		</td>
		<td style="text-align: left">
			<?php $this->show( 'fields.name', $i ); ?>
			<div class="SPFListAlias"><?php $this->show( 'fields.nid', $i ); ?></div>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.state', $i ); ?>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.field_type', $i ); ?>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.show_in', $i ); ?>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.required', $i ); ?>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.editable', $i ); ?>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.is_free', $i ); ?>
		</td>
		<td style="text-align: center">
			<?php $this->show( 'fields.order', $i ); ?>
		</td>
	</tr>
	<?php } ?>
</table>
<br />
<br />
<?php $this->trigger( 'AfterDisplayList' ); ?></div>
<?php $this->trigger( 'OnEnd' ); ?>
