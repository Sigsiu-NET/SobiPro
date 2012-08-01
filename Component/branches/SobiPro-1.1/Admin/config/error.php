<?php
/**
 * @version: $Id: error.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/config/error.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
$row = 0;
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<fieldset class="adminform">
	<table class="admintable">
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.ERROR_DATE' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.date' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.ERROR_LEVEL' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errNum' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.RETURN_CODE' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errCode' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.ERROR_MESSAGE' ); ?>
			</td>
			<td>
				<span style="color:blue; font-weight: bold; font-size:15px;"><?php $this->show( 'error.errMsg' ); ?></span>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.IN_FILE' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errFile' ); ?>&nbsp;<?php $this->txt( 'at line' ); ?>&nbsp;<?php $this->show( 'error.errLine' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.SECTION_TYPE' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errSect' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.USER_ID' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errUid' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.USER_IP' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errIp' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.USER_AGENT' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errUa' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.REQUESTED_URI' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errReq' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key">
				<?php $this->txt( 'ERR.REFERRER' ); ?>
			</td>
			<td>
				<?php $this->show( 'error.errRef' ); ?>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key" style="vertical-align:top;">
				<?php $this->txt( 'ERR.TRACE' ); ?>
			</td>
			<td>
				<div style="height:500px; overflow:scroll; max-width:800px" >
					<pre>
						<?php SPConfig::debOut( $this->get( 'error.errBacktrace' ) ); ?>
					</pre>
				</div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td class="key" style="vertical-align:top;">
				<?php $this->txt( 'ERR.CALL_STACK' ); ?>
			</td>
			<td>
				<div style="height:500px; overflow:scroll; max-width:800px" >
					<pre>
						<?php SPConfig::debOut( $this->get( 'error.errCont' ) ); ?>
					</pre>
				</div>
			</td>
		</tr>
	</table>
</fieldset>
