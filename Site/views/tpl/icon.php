<?php
/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
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
	<div style="min-width: 480px; display: inline-block">
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
<div style="min-width: 480px; padding-top:10px; display: inline-block;">
<?php for ( $i = 0; $i < $fc ; ++$i ) { ?>
	<div style="float: left; text-align: center; width: 65px; height: 65px; cursor:pointer">
		<img alt="<?php $this->show( 'files.name', $i ); ?>" title="<?php $this->show( 'files.name', $i ); ?>" src="<?php $this->show( 'files.path', $i ); ?>" style="max-width: 60px; max-height: 60px;" onclick="spSelect( this )">
	</div>
	<?php if( ( $i+1 ) % 7 == 0 ) { ?>
		<div style="clear:both"></div>
	<?php } ?>
<?php } ?>
</div>
