<?php
/**
 * @version: $Id: repositories.php 626 2011-01-19 18:13:09Z Sigrid Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/repositories.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addJsCode( 'var spReq ="'.Sobi::Url( array( 'task' => 'extensions.%task%', 'out' => 'raw' ), true ).'"' );
SPFactory::header()->addJsFile( 'repositories', true );
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20.8em; margin-top: 3px;">
	<table class="adminlist" cellspacing="1">
		<tr>
			<td colspan="2">
				<?php $this->txt( 'EX.ADD_NEW_REPO' ); ?>&nbsp;
				<b>https://</b> <?php $this->field( 'text', 'sprepo', null, array( 'id' => 'sprepo', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox' ) ); ?>
				<?php $this->field( 'button', 'install', 'translate:[EX.ADD_NEW_REPO_BT]', array( 'class' => 'text_area', 'id' => 'spaddrepo' ) ); ?><span id="sprwait"></span>
				<div id="spresponse" style="height: 275px;"></div>
			</td>
		</tr>
	</table>
	<table class="adminlist" cellspacing="1" style="margin-top: 5px;">
		<thead>
			<tr>
				<th width="1%">
					#
				</th>
				<th>
					<?php $this->txt( 'EX.REPOSITORY_NAME' ); ?>
				</th>
				<th width="30%">
					<?php $this->txt( 'EX.REPO_DESCRIPTION' ); ?>
				</th>
				<th class="title" colspan="2">
					<?php $this->txt( 'EX.REPO_MAINTAINER' ); ?>
				</th>
			</tr>
		</thead>
		<?php
			$c = $this->count( 'repositories' );
			for ( $i = 0; $i < $c ; $i++ ) {
				$style = $i%2;
		?>
		<tr class="row<?php echo $style;?>">
			<td style="text-align: center">
				<?php
					$v = $this->get( 'repositories.repository.id', $i );
					$this->field( 'checkbox', "sprepo[{$v}]", $v );
				?>
			</td>
			<td style="text-align: center">
				<?php $this->show( 'repositories.repository.name', $i ); ?>
			</td>
			<td style="text-align: left">
				<?php $this->show( 'repositories.repository.description', $i ); ?>
			</td>
			<td style="text-align: center" width="15%">
				<a href="<?php $this->show( 'repositories.repository.maintainer.url', $i ); ?>" target="_blank" >
					<?php $this->show( 'repositories.repository.maintainer.name', $i ); ?>
				</a>
			</td>
			<td style="text-align: center" width="15%">
				<?php if( $this->get( 'repositories.repository.maintainer.supporturl', $i ) ) { ?>
					<a href="<?php $this->show( 'repositories.repository.maintainer.supporturl', $i ); ?>" target="_blank" >
						<?php $this->txt( 'EX.MAINT_SUPPORT' ); ?>
					</a>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>