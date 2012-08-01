<?php
/**
 * @version: $Id: browse.php 1133 2011-04-11 17:54:02Z Radek Suski $
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
 * $Date: 2011-04-11 19:54:02 +0200 (Mon, 11 Apr 2011) $
 * $Revision: 1133 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/browse.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addJsFile( 'updates', true );
?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<div style="margin-left: 20.8em; margin-top: 3px;">
	<table class="adminlist" cellspacing="1">
		<tr>
			<td>
				<div id="spupdate">
					<span style="font-size: 15px;">
						<?php $this->txt( 'EX.LAST_UPDATE' ); ?>&nbsp;
						<b style="color:#0B55C4"><?php $this->show( 'last_update' ); ?></b>
						<span id="sprwait"></span>
					</span>
				</div>
				<div id="spupdating" style="padding: 20px; height:800px; vertical-align:top;">
					<div style="text-align:left; margin-bottom: 20px; display:none;" class="SPPbarMsgbox" id="spupd">
						<?php $this->txt( 'EX.UPDATING' ); ?>
					</div>
					<div style="text-align:left; margin-bottom: 20px; display:none;" class="SPPbarMsgbox" id="spdwn">
						<?php $this->txt( 'EX.DOWNLOADING' ); ?>
					</div>
					<div id="spresponse" style="height:100px; margin:10px;"></div>
					<div id="spdresponse" style="height:400px; margin:10px;"></div>
				</div>
			</td>
		</tr>
	</table>
	<?php $this->trigger( 'BeforeDisplayPlugins' ); ?>
	<table class="adminlist" id="splist" cellspacing="1" style="width: 100%; margin-top: 5px;">
		<thead>
			<tr>
				<th width="1%">
					<?php $this->show( 'header.checkbox' ); ?>
				</th>
				<th class="title" width="19%">
					<?php $this->show( 'header.name' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.type' ); ?>
				</th>
				<th width="10%">
					<?php $this->show( 'header.version' ); ?>
				</th>
				<th width="5%">
					<?php $this->show( 'header.state' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.author' ); ?>
				</th>
				<th width="15%">
					<?php $this->show( 'header.license' ); ?>
				</th>
				<th width="20%">
					<?php $this->show( 'header.availability' ); ?>
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
			<td style="text-align: center" width="5%">
				<?php $this->show( 'plugins.type', $i ); ?>
			</td>
			<td style="text-align: center" width="5%">
				<?php $this->show( 'plugins.version', $i ); ?>
			</td>
			<td style="text-align: center" width="5%">
				<?php $this->show( 'plugins.installed', $i ); ?>
			</td>
			<td style="text-align: center">
				<?php $this->show( 'plugins.author', $i ); ?>
			</td>
			<td style="text-align: center">
				<?php $this->show( 'plugins.license', $i ); ?>
			</td>
			<td style="text-align: left">
				<?php $this->show( 'plugins.availability', $i ); ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<?php $this->trigger( 'AfterDisplayPlugins' ); ?>
</div>