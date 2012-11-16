<?php
/**
 * @version: $Id: icon.php 551 2011-01-11 14:34:26Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/views/tpl/icon.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
$dc = $this->count( 'directories' );
$fc = $this->count( 'files' );
?>
<script type="text/javascript">
	function spSelect( e )
	{
		parent.<?php $this->show( 'callback' ); ?>( e.src, e.alt );
		e.focus();
	}
</script>
<?php if( $dc ) { ?>
	<div style="padding-left: 10px; min-width: 450px; display: inline-block">
	<?php for ( $i = 0; $i < $dc ; ++$i ) { ?>
		<div style="float: left; width: 100px; height: 90px; padding: 5px; text-align:center">
			<a href="<?php $this->show( 'directories.url', $i ); ?>" >
				<img alt="<?php $this->show( 'directories.name', $i ); ?>" title="<?php $this->show( 'directories.name', $i ); ?>" src="<?php $this->show( 'folder' ); ?>" style="max-width: 55px; max-height: 55px;" >
			</a>
			<br/>
			<a href="<?php $this->show( 'directories.url', $i ); ?>" >
				<?php $this->show( 'directories.name', $i ); ?> (<?php $this->show( 'directories.count', $i ); ?>)
			</a>
		</div>
		<?php if( ( $i+1 ) % 4 == 0 ) { ?>
			<div style="clear:both"></div>
		<?php } ?>
	<?php } ?>
</div>
<?php } ?>
<div style="padding-top:10px; padding-left: 10px; display: inline-block;">
<?php for ( $i = 0; $i < $fc ; ++$i ) { ?>
	<div style="float: left; width: 65px; height: 65px; cursor:pointer">
		<img alt="<?php $this->show( 'files.name', $i ); ?>" title="<?php $this->show( 'files.name', $i ); ?>" src="<?php $this->show( 'files.path', $i ); ?>" style="max-width: 60px; max-height: 60px;" onclick="spSelect( this )">
	</div>
	<?php if( ( $i+1 ) % 7 == 0 ) { ?>
		<div style="clear:both"></div>
	<?php } ?>
<?php } ?>
</div>
