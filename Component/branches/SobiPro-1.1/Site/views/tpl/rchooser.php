<?php
/**
 * @version: $Id: rchooser.php 992 2011-03-17 16:31:33Z Radek Suski $
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
 * $Date: 2011-03-17 17:31:33 +0100 (Thu, 17 Mar 2011) $
 * $Revision: 992 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/views/tpl/rchooser.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<script language="javascript" type="text/javascript">
    var selectedCat = 0;
    var selectedPath = '';
	function SP_selectCat( sid )
	{
		try {
			SP_id( 'sobiCats_CatUrl' + sid ).focus();
		} catch( e ) {}
		selectedCat = sid;
	}
	function SP_Select()
	{
		parent.document.getElementById( 'sid' ).value = selectedCat;
		parent.SP_close();
	}
</script>
<div style="margin: 5px; padding: 5px;">
	<div style="clear: both; margin-bottom: 10px;" class="hasTip">
		<?php $this->txt( 'CC.JMENU_DESC' ); ?>
	</div>
	<div class="col width-100" style="min-width: 600px;">
		<fieldset class="adminform">
			<legend><?php $this->txt( 'Select category' ); ?></legend>
			<div><?php $this->get( 'tree' )->display(); ?></div>
		</fieldset>
	</div>
	<div style="clear: both"></div>
	<div style="padding: 10px">
		<?php $this->field( 'button', 'save', 'translate:[CC.JMENU_SAVE]', array( 'onclick' => 'SP_Select();', 'size' => 50, 'class' => 'button', 'style' => 'border: 1px solid silver;' ) ); ?>
	</div>
</div>