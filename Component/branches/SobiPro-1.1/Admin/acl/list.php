<?php
/**
 * @version: $Id: list.php 626 2011-01-19 18:13:09Z Sigrid Suski $
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
 * $Date: 2011-01-19 19:13:09 +0100 (Wed, 19 Jan 2011) $
 * $Revision: 626 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/acl/list.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<?php $this->trigger( 'Start' ); ?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20.8em; margin-top: 3px;">
	<table class="adminlist" cellspacing="1" style="width: 100%">
		<thead>
			<tr>
				<th width="5%">
					<?php $this->show( 'header.rid' ); ?>
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
					<?php $this->show( 'header.validSince' ); ?>
				</th>
				<th width="10%">
					<?php $this->show( 'header.validUntil' ); ?>
				</th><!--
				<th width="15%">
					<?php $this->show( 'header.perms_count' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.group_count' ); ?>
				</th>
			--></tr>
		</thead>
		<?php
			$c = $this->count( 'rules' );
			for ( $i = 0; $i < $c ; $i++ ) {
				$style = $i%2;
		?>
			<tr class="row<?php echo $style;?>">
				<td style="text-align: center">
					<?php $this->show( 'rules.id', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'rules.checkbox', $i ); ?>
				</td>
				<td>
					<?php $this->show( 'rules.name', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'rules.state', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'rules.validSince', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'rules.validUntil', $i ); ?>
				</td><!--
				<td style="text-align: center">
					<?php $this->show( 'rules.perms_count', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'rules.group_count', $i ); ?>
				</td>
			--></tr>
		<?php } ?>
	</table>
	<br />
</div>
<?php $this->trigger( 'End' ); ?>
