<?php
/**
 * @version: $Id: edit.php 2323 2012-03-27 14:37:23Z Sigrid Suski $
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
 * $Date: 2012-03-27 16:37:23 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2323 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/template/edit.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addCSSCode( 'body { min-width: 1200px; }' );
SPFactory::header()->addJsCode( '
		window.addEvent( "domready", function() {
			var spHandler = $$( "#toolbar-copy a" )[ 0 ];
			spHandlerFn = spHandler.onclick;
			spHandler.onclick = null;
			$( "toolbar-copy" ).addEvent( "click", function() {
				var fName = "'.$this->get( 'file_name' ).'";
				newName = window.prompt( SobiPro.Txt( "SAVE_AS_TEMPL_FILE" ), fName );
				if( newName != "" ) {
					$( "sp_fedit" ).value = newName.replace( /\//g , "." ).replace( /\\\/g , "." );
					try {
						Joomla.submitbutton( "template.saveAs" );
					}
					catch( e ) {
						submitbutton( "template.saveAs" )
					}
				}
			} );
		} );
' );

?>
<?php $this->trigger( 'OnStart' ); ?>
<table style="width: 100%; vertical-align: top;">
	<tr>
		<td style="vertical-align: top;">
			<?php $this->menu(); ?>
		</td>
		<?php $this->trigger( 'AfterDisplayMenu' ); ?>
		<td style="width: 100%; vertical-align: top;">
			<table class="adminlist" cellspacing="1" style="width: 100%;">
				<tr>
					<th style=" width: 90%;">
						<?php $this->txt( 'TP.EDITING_FILE' ); ?>
						<?php $this->show( 'file_name' ); ?>
					</th>
				</tr>
				<tr>
					<td >
						<?php $this->field( 'textarea', 'file_content', 'value:file_content', false, 0, 0, array( 'id' => 'file_content', 'style' => 'width: 97%; min-height: 1200px; font-size: 15px; border:none;' ) ); ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php $this->trigger( 'OnEnd' ); ?>