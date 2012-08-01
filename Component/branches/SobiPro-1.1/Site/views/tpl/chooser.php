<?php
/**
 * @version: $Id: chooser.php 992 2011-03-17 16:31:33Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/views/tpl/chooser.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPFactory::header()->addCSSCode( '.sigsiuTree {height: 270px;}');

?>
<script language="javascript" type="text/javascript">
    var selectedCat = 0;
    var selectedPath = '';
    if( parent.document.getElementById( 'category.path' ).value == null ) {
        SPObjType = 'entry';
    }
    else {
    	SPObjType = 'category';
    }
	function SP_selectCat( sid )
	{
		SP_id( 'pid' ).value = sid;
		try {
			SP_id( 'sobiCats_CatUrl' + sid ).focus();
		} catch( e ) {}
		selectedCat = sid;
		var separator = '<?php echo Sobi::Cfg( 'string.path_separator', ' > '  ); ?>'
		var node = SP_id( 'sobiCatsstNode' + sid );
		var cats = new Array();
		var request = new SobiPro.Json(
			'<?php $this->show( 'parent_ajax_url' ); ?>' + '&sid=' + sid,
			{
				onComplete: function( jsonObj, jsons )
				{
			        jsonObj.categories.each( function( cat ) { cats[ cat.id ] = cat.name; } );
			        selectedPath = cats.join( separator );
			        SP_id( 'path' ).value = SobiPro.StripSlashes( selectedPath );
				}
			}
		).send();
	}
	function SP_Select()
	{
		if( selectedCat ) {
			parent.document.getElementById( SPObjType + '.path' ).value = SobiPro.StripSlashes( selectedPath );
			parent.document.getElementById( SPObjType + '.parent' ).value = selectedCat;
		}
		parent.SP_close();
	}
</script>
<div style="margin: 5px; padding: 5px;">
	<div class="col width-100" style="min-width: 600px;">
		<fieldset class="adminform">
			<legend><?php $this->txt( 'CC.CURRENT_SELECTED_PATH' ); ?></legend>
			<div>
				<div style="height: 36px; float: left; padding: 2px;">
					<?php $this->field( 'textarea', 'parent_path', 'value:parent_path', false, 510, 33, array( 'id' => 'path', 'size' => 50, 'maxlength' => 255, 'class' => 'inputbox', 'readonly' => 'readonly' ) ); ?>
				</div>
				<div style="height: 36px; float: left; padding: 3px;">
					<?php $this->field( 'text', 'pid', 'value:parent', array( 'id' => 'pid', 'size' => 5, 'maxlength' => 50, 'class' => 'inputbox', 'readonly' => 'readonly', 'style' => 'text-align:center;' ) ); ?>
				</div>
			</div>
		</fieldset>
	</div>
	<div style="clear: both"></div>
	<div class="col width-100" style="min-width: 600px;">
		<fieldset class="adminform">
			<legend><?php $this->txt( 'CC.SELECT_PARENT_CAT' ); ?></legend>
			<div><?php $this->get( 'tree' )->display(); ?></div>
		</fieldset>
	</div>
	<div style="clear: both"></div>
	<div style="padding: 4px 0 0 10px"><?php $this->field( 'button', 'save', 'translate:[CC.SAVE_BT]', array( 'onclick' => 'SP_Select();', 'size' => 50, 'class' => 'button', 'style' => 'border: 1px solid silver;' ) ); ?>
	</div>
</div>