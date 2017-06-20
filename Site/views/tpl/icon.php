<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
$dc = $this->count( 'directories' );
$fc = $this->count( 'files' );
?>
<script type="text/javascript">
	function spSelect(e) {
		parent.<?php $this->show( 'callback' ); ?>(e.src, e.alt);
		e.focus();
	}
</script>
<div class="SobiPro">
	<?php if ( $dc ) { ?>
		<div class="spImageFolders">
			<?php for ( $i = 0; $i < $dc; ++$i ) { ?>
				<div class="spImageFolder">
					<a href="<?php $this->show( 'directories.url', $i ); ?>">
						<span title="<?php $this->show( 'directories.name', $i ); ?> (<?php $this->show( 'directories.count', $i ); ?>)" class="<?php $this->show( 'symbol' ); ?>"></span>
					</a> <a href="<?php $this->show( 'directories.url', $i ); ?>">
						<?php $this->show( 'directories.name', $i ); ?> (<?php $this->show( 'directories.count', $i ); ?>) </a>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	<?php if ( $fc ) { ?>
		<ul class="spImageFiles">
			<?php for ( $i = 0; $i < $fc; ++$i ) { ?>
				<li class="spImageFile">
					<div class="imgThumb imgInput">
						<label>
							<img alt="<?php $this->show( 'files.name', $i ); ?>" title="<?php $this->show( 'files.name', $i ); ?>" src="<?php $this->show( 'files.path', $i ); ?>" onclick="spSelect( this )">
						</label>
					</div>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
	<?php if ( !$dc && !$fc ) { ?>
		<p>No files and folders found!</p>
	<?php } ?>
</div>
