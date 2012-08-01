<?php
/**
 * @version: $Id: installed.php 802 2011-02-15 10:23:53Z Radek Suski $
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
 * $Date: 2011-02-15 11:23:53 +0100 (Tue, 15 Feb 2011) $
 * $Revision: 802 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/installed.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );

?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<div style="margin-left: 20.8em; margin-top: 3px;">
	<table class="adminlist" cellspacing="1">
		<tr>
			<td>
				<?php $this->txt( 'EX.INSTALL_NEW' ); ?>&nbsp;
				<?php $this->field( 'file', 'spextfile', null, array( 'class' => 'inputbox' ) ); ?>
				<?php $this->field( 'submit', 'install', Sobi::Txt( 'EX.INSTALL_NEW_BT' ), array( 'class' => 'button', 'onclick' => 'submitbutton( "extensions.install" )' ) ); ?>
			</td>
		</tr>
	</table>
	<?php $this->trigger( 'BeforeDisplayPlugins' ); ?>
	<table class="adminlist" cellspacing="1" style="width: 100%; margin-top: 5px;">
		<thead>
			<tr>
				<th width="5%">
					<?php $this->show( 'header.checkbox' ); ?>
				</th>
				<th class="title" width="30%">
					<?php $this->show( 'header.name' ); ?>
				</th>
				<th width="5%">
					<?php $this->show( 'header.pid' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.type' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.version' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.author' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.enabled' ); ?>
				</th>
			</tr>
		</thead>
		<?php
			$c = $this->count( 'plugins' );
			for ( $i = 0; $i < $c ; $i++ ) {
				$style = $i%2;
		?>
		<tr class="row<?php echo $style;?>">
			<td style="text-align: center">
				<?php $this->show( 'plugins.radio', $i ); ?>
			</td>
			<td style="text-align: left">
				<?php $this->show( 'plugins.name', $i ); ?>
			</td>
			<td style="text-align: left">
				<?php $this->show( 'plugins.pid', $i ); ?>
			</td>
			<td style="text-align: center" width="5%">
				<?php $this->show( 'plugins.type', $i ); ?>
			</td>
			<td style="text-align: center" width="5%">
				<?php $this->show( 'plugins.version', $i ); ?>
			</td>
			<td style="text-align: center">
				<?php $this->show( 'plugins.author', $i ); ?>
			</td>
			<td style="text-align: center">
				<?php $this->show( 'plugins.enabled', $i ); ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<?php $this->trigger( 'AfterDisplayPlugins' ); ?>
</div>
