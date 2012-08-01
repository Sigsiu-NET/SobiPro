<?php
/**
 * @version: $Id: errors.php 626 2011-01-19 18:13:09Z Sigrid Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/config/errors.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20.8em; margin-top: 3px;">
<?php if( $this->count( 'errors' ) ) { ?>
	<table class="adminlist" cellspacing="1" style="width: 100%">
		<thead>
			<tr>
				<th width="1%">
					<?php $this->txt( 'ERR.ERROR_ID' ); ?>
				</th>
				<th width="5%">
					<?php $this->txt( 'ERR.ERROR_LEVEL' ); ?>
				</th>
				<th >
					<?php $this->txt( 'ERR.ERROR_INFO' ); ?>
				</th>
				<th >
					<?php $this->txt( 'ERR.ERROR_DETAILS' ); ?>
				</th>
			</tr>
		</thead>
		<?php
			$c = $this->count( 'errors' );
			for ( $i = 0; $i < $c ; $i++ ) {
				$style = $i%2;
		?>
		<tr class="row<?php echo $style;?>">
			<td style="text-align: center">
				<?php $this->show( 'errors.eid', $i ); ?>
			</td>
			<td style="text-align: center">
				<?php $this->show( 'errors.errNum', $i ); ?>
			</td>
			<td style="text-align: left">
				<div style="width:100%;">
					<div style="float: left;padding:3px;">
						<b><?php $this->txt( 'ERR.SECTION_TYPE' ); ?></b>&nbsp;<?php $this->show( 'errors.errSect', $i ); ?>
					</div>
					<div style="float: left;padding:3px;">
						<b><?php $this->txt( 'ERR.ERROR_DATE' ); ?></b>&nbsp; <?php $this->show( 'errors.date', $i ); ?>
					</div>
					<div style="float: left;padding:3px;">
						<b><?php $this->txt( 'ERR.IN_FILE' ); ?></b>&nbsp;<?php $this->show( 'errors.errFile', $i ); ?>:<?php $this->show( 'errors.errLine', $i ); ?>
					</div>
					<!--
					<div style="float: left;padding:3px;">
						<b><?php $this->txt( 'ERR.USER_ID' ); ?></b>&nbsp;<?php $this->show( 'errors.errUid', $i ); ?>
					</div>
					-->
					<div style="clear:right;padding:3px;">
						<!--<b><?php $this->txt( 'ERR.RETURN_CODE' ); ?></b>&nbsp;<?php $this->show( 'errors.errCode', $i ); ?>-->
					</div>

				</div>
				<div style="padding:0 0 3px 3px;clear:both;">
					<b><?php $this->txt( 'ERR.REQUESTED_URI' ); ?></b>&nbsp; <?php $this->show( 'errors.errReq', $i ); ?>
				</div>
				<div style="padding:3px;">
					<span style="color:blue; font-size:12px;">
						<b><?php $this->txt( 'ERR.ERROR_MESSAGE' ); ?></b>&nbsp;<?php $this->show( 'errors.errMsg', $i ); ?>
					</span>
				</div>
			</td>
			<td style="text-align: center">
				<?php $this->show( 'errors.details', $i ); ?>
			</td>
		</tr>
		<?php } ?>
		<tfoot>
			<tr>
				<td colspan="4">
					<div class="container">
						<?php $this->show( 'page_nav' ); ?>
					</div>
				</td>
			</tr>
		</tfoot>
	</table>
<?php } else { ?>
	<h1 style="padding: 25px;" ><?php $this->txt( 'ERR.NO_ERRORS' ); ?></h1>
<?php } ?>
<br />
</div>