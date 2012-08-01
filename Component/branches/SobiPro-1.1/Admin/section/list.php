<?php
/**
 * @version: $Id: list.php 641 2011-01-20 11:49:43Z Sigrid Suski $
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
 * $Date: 2011-01-20 12:49:43 +0100 (Thu, 20 Jan 2011) $
 * $Revision: 641 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/section/list.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20.8em; margin-top: 3px;">
	<div class="sheader icon-48-SobiCatList">
		<?php $this->txt( 'CAT.CATEGORY_LIST_HEAD', array( 'category' => Sobi::Section( true ) ) ); ?>
	</div>
	<?php $this->trigger( 'BeforeDisplayCategories' ); ?>
	<?php if( $this->count( 'categories' ) ) { ?>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th width="5%">
						<?php $this->show( 'header.c_sid' ); ?>
					</th>
					<th width="5%">
						<?php $this->show( 'header.checkbox' ); ?>
					</th>
					<th class="title" colspan="3" width="30%">
						<?php $this->show( 'header.name' ); ?>
					</th>
					<th width="15%">
						<?php $this->show( 'header.state' ); ?>
					</th>
					<th width="15%">
						<?php $this->show( 'header.approved' ); ?>
					</th>
					<th width="15%">
						<?php $this->show( 'header.order' ); ?>
					</th>
					<th width="15%">
						<?php $this->show( 'header.owner' ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<div class="container">
							<?php $this->show( 'cat_page_nav' ); ?>
						</div>
					</td>
				</tr>
			</tfoot>
			<?php
				$c = $this->count( 'categories' );
				for ( $i = 0; $i < $c ; $i++ ) {
					$style = $i%2;
			?>
			<tr class="row<?php echo $style;?>">
				<td style="text-align: center">
					<?php $this->show( 'categories.id', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'categories.checkbox', $i ); ?>
				</td>
				<td style="text-align: left">
					<?php $this->show( 'categories.name', $i ); ?>
				</td>
				<td style="text-align: center" width="5%">
					<?php $this->show( 'categories.goin_link', $i ); ?>
				</td>
				<td style="text-align: center" width="5%">
					<?php $this->show( 'categories.edit_link', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'categories.state', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'categories.approved', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'categories.order', $i ); ?>
				</td>
				<td style="text-align: left">
					<?php $this->show( 'categories.owner', $i ); ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	<?php } else { ?>
		<table style="width: 100%; vertical-align: top;">
		<tr>
			<td style="vertical-align: top; width: 100%; ">
			<p style="margin: 0 0 0 65px; font-size: 14px;"><strong><?php $this->txt( 'CATEGORY.NONE'); ?></strong></p>
			</td>
		</tr>
		</table>
	<?php } ?>
	<br />
	<br />
	<?php $this->trigger( 'AfterDisplayCategories' ); ?>
	<div class="sheader icon-48-SobiEntries">
		<?php $this->txt( 'CAT.ENTRIES_LIST_HEAD', array( 'category' => Sobi::Section( true ) ) ); ?>
	</div>
	<?php if( $this->count( 'entries' ) ) { ?>
		<table class="adminlist" cellspacing="1" style="width: 100%">
			<thead>
				<tr>
					<th width="5%">
						<?php $this->show( 'entries_header.e_sid' ); ?>
					</th>
					<th width="5%">
						<?php $this->show( 'entries_header.checkbox' ); ?>
					</th>
					<th class="title" width="20%">
						<?php $this->show( 'entries_header.name' ); ?>
					</th>
					<?php
						$fc = $this->count( 'custom_fields' );
						$fields = $this->get( 'custom_fields' );
						for ( $k = 0; $k < $fc ; $k++ ) {
					?>
					<th width="1%">
						<?php $this->show( 'entries_header.'.$fields[ $k ] ); ?>
					</th>
					<?php } ?>
					<th width="10%">
						<?php $this->show( 'entries_header.state' ); ?>
					</th>
					<th width="10%">
						<?php $this->show( 'entries_header.approved' ); ?>
					</th>
					<th width="10%">
						<?php $this->show( 'entries_header.order' ); ?>
					</th>
					<th width="10%">
						<?php $this->show( 'entries_header.owner' ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<div class="container">
							<?php $this->show( 'entries_page_nav' ); ?>
						</div>
					</td>
				</tr>
			</tfoot>
			<?php
				$c = $this->count( 'entries' );
				for ( $i = 0; $i < $c ; $i++ ) {
					$style = $i%2;
			?>
			<tr class="row<?php echo $style;?>">
				<td style="text-align: center">
					<?php $this->show( 'entries.id', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'entries.checkbox', $i ); ?>
				</td>
				<td style="text-align: left">
					<?php $this->show( 'entries.name', $i ); ?>
				</td>
				<?php for ( $k = 0; $k < $fc ; $k++ ) { ?>
				<td>
					<?php $this->show( 'entries.'.$fields[ $k ], $i ); ?>
				</td>
				<?php } ?>
				<td style="text-align: center">
					<?php $this->show( 'entries.state', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'entries.approved', $i ); ?>
				</td>
				<td style="text-align: center">
					<?php $this->show( 'entries.order', $i ); ?>
				</td>
				<td style="text-align: left">
					<?php $this->show( 'entries.owner', $i ); ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	<?php } ?>
	<?php $this->trigger( 'AfterDisplayEntries' ); ?>
</div>
<?php $this->trigger( 'OnEnd' ); ?>