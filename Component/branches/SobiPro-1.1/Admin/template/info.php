<?php
/**
 * @version: $Id: info.php 1019 2011-03-28 08:18:35Z Radek Suski $
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
 * $Date: 2011-03-28 10:18:35 +0200 (Mon, 28 Mar 2011) $
 * $Revision: 1019 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/template/info.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addJsCode( '
		window.addEvent( "domready", function() {
			var spCloneHandler = $$( "#toolbar-copy a" )[ 0 ];
			spCloneHandlerFn = spCloneHandler.onclick;
			spCloneHandler.onclick = null;
			$( "toolbar-copy" ).addEvent( "click", function() {
				$( "sptplname" ).value = window.prompt( SobiPro.Txt( "CLONE_TEMPL" ) );
				if( $( "sptplname" ).value != "" ) { 
					spCloneHandlerFn();
				}
			} );
		} );
' );
SPFactory::header()->addCSSCode( 'body { min-width: 1200px; }' );
?>
<table style="width: 100%; vertical-align: top;">
	<tr>
		<td style="vertical-align: top;">
			<?php $this->menu(); ?>
		</td>
		<td style="vertical-align: top; width: 100%; ">
			<table class="adminlist" cellspacing="1">
				<tr>
					<td>
						<?php $this->txt( 'TP.TEMPLATE_NAME' ); ?>
					</td>
					<td>
						<b><?php $this->show( 'template_name' ); ?></b>
					</td>
				</tr>
				<tr>
					<td>
						<?php $this->txt( 'TP.AUTHOR' ); ?>
					</td>
					<td>
						<a href="mailto:<?php $this->show( 'template_author_email' ); ?>" ><?php $this->show( 'template_author' ); ?></a>
					</td>
				</tr>
				<?php if( $this->get( 'template_author_url' ) ) { ?>
				<tr>
					<td>
						<?php $this->txt( 'TP.AUTHOR_URL' ); ?>
					</td>
					<td>
						<a href="<?php $this->show( 'template_author_url' ); ?>"  target="_blank" ><?php $this->show( 'template_author_url' ); ?></a>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td>
						<?php $this->txt( 'TP.COPYRIGHT' ); ?>
					</td>
					<td>
						<?php $this->show( 'template_copyright' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php $this->txt( 'TP.LICENSE' ); ?>
					</td>
					<td>
						<?php $this->show( 'template_license' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php $this->txt( 'TP.VERSION' ); ?>
					</td>
					<td>
						<?php $this->show( 'template_version' ); ?> / <?php $this->show( 'template_date' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php $this->txt( 'TP.DESCRIPTION' ); ?>
					</td>
					<td>
						<?php $this->show( 'template_description' ); ?>
					</td>
				</tr>
				<?php $c = $this->count( 'template_files' ); ?>
				<?php if( $c ) { ?>
				<tr>
					<th colspan="2">
						<?php $this->txt( 'TP.FILES' ); ?>
					</th>
				</tr>
				<?php } ?>
				<?php
					for ( $i = 0; $i < $c ; $i++ ) {
						$style = $i%2;
				?>
				<tr class="row<?php echo $style;?>">
					<td>
						<b><?php $this->show( 'template_files.file', $i ); ?></b>
					</td>
					<td>
						<?php $this->show( 'template_files.description', $i ); ?>
					</td>
				</tr>
				<?php } ?>
			</table>
		</td>
		<?php if( $this->get( 'template_preview_image' ) ) { ?>
			<td style="vertical-align:top; text-align:center;">
				<img src="<?php $this->show( 'template_preview_image' ); ?>" />
			</td>
		<?php } ?>
	</tr>
</table>