<?php
/**
 * @version: $Id: filters.php 957 2011-03-08 16:37:02Z Radek Suski $
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
 * $Date: 2011-03-08 17:37:02 +0100 (Tue, 08 Mar 2011) $
 * $Revision: 957 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/filters.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<script language="javascript" type="text/javascript">
	var spAddNew = $$( "#toolbar-new a" )[ 0 ];
	spAddNew.onclick = null;
	spAddNew.addEvent( "click", function() { SP_EditFilter( '', spAddNew ); } );
	function SP_EditFilter( fid, el )
	{
		fid = fid ? fid : '';
		eUrl = '<?php $this->show( 'edit_url' );?>' + '&fid=' + fid;
		try {
			SqueezeBox.open( eUrl, { handler: 'iframe', size: { x: 525, y: 250 } } );
		}
		catch( x ) {
			SqueezeBox.fromElement( spAddNew, { url: '<?php $this->show( 'edit_url' );?>' + '&fid=' + fid, handler: 'iframe', size: { x: 525, y: 250 } } );
		}
	}
</script>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em; margin-left: 3px;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20.8em; margin-top: 3px;">
	<table class="adminlist" cellspacing="1" style="width: 100%">
		<thead>
			<tr>
				<!--
				<th width="15%">
					<?php $this->txt( 'FLR.FILTER_ID' ); ?>
				</th>
				-->
				<th width="25%">
					<?php $this->txt( 'FLR.FILTER_NAME' ); ?>
				</th>
				<th >
					<?php $this->txt( 'FLR.FILTER_VALUE' ); ?>
				</th>
				<th >
					<?php $this->txt( 'FLR.EDIT_FILTER' ); ?>
				</th>
			</tr>
		</thead>
		<?php
			$c = $this->count( 'filters' );
			for ( $i = 0; $i < $c ; $i++ ) {
				$style = $i%2;
		?>
		<tr class="row<?php echo $style;?>">
			<!--
			<td style="text-align: center">
				<?php $this->show( 'filters.id', $i ); ?>
			</td>
			-->
			<td style="text-align: left; padding: 10px;">
				<div style="padding:10px;clear:both; font-size:12px; font-weight:bold">
					<?php $this->show( 'filters.name', $i ); ?>
				</div>
			</td>
			<td style="text-align: left">
				<div style="width:100%;padding:2px;">
					<b><?php $this->txt( 'FLR.FILTER_MSG' ); ?></b>&nbsp;<?php $this->show( 'filters.message', $i ); ?>
				</div>
				<div style="padding:2px;clear:both">
					<span style="color:blue; font-size:12px;">
						<b><?php $this->txt( 'FLR.FILTER_REGEX' ); ?></b>&nbsp;<?php $this->show( 'filters.regex', $i ); ?>
					</span>
				</div>
			</td>
			<td style="text-align: center">
				<a onclick="SP_EditFilter( '<?php $this->show( 'filters.id', $i ); ?>', this )" href="javascript:void( 0 );">
					<img src="<?php echo Sobi::Cfg( 'list_icons.filter_edit' ); ?>" alt="edit"/>
				</a>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>