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
SPFactory::header()->addCSSCode( 'legend { color: #0b55c4; font-size: 12px!important; font-weight: bold;}' );
SPFactory::header()->addCSSCode( '.sigsiuTree {height: 330px;}');
?>
<script language="javascript" type="text/javascript">
    var selectedCat = 0;
    var selectedCatName = '';
    var selectedCats = [];
    var selectedCatNames = [];
    var selectedPath = '';
    var selCid = 0;
    if( parent.document.getElementById( 'category.path' ) ) {
    	var SPObjType = 'category';
    }
    else {
    	var SPObjType = 'entry';
    }
    var maxCat = <?php echo Sobi::Cfg( 'legacy.maxCats', '5'  ); ?>;
    var Cinit = String( parent.document.getElementById( 'entry.parent' ).value );
    if( Cinit != '' ) {
		var cats = Cinit.split( ',' );
		for( var i = 0; i < cats.length; i++ ) {
			if( cats[ i ] > 0 ) {
				SP_selectCat( cats[ i ], 1 );
			}
		}
    }
	function SP_selectCat( sid, add )
	{
		var separator = "<?php echo Sobi::Cfg( 'string.path_separator', ' > '  ); ?>";
		var node = SP_id( 'sobiCatsstNode' + sid );
		try {
			SP_id( 'sobiCats_CatUrl' + sid ).focus();
		} catch( e ) {}
		var cats = [];
		var request = new SobiPro.Json(
			"<?php $this->show( 'parent_ajax_url' ); ?>" + '&sid=' + sid,
			{
				onComplete: function( jsonObj, jsons )
				{
					selectedCat = sid;
			        jsonObj.categories.each( function( cat ) { cats[ cat.id ] = cat.name; selectedCatName = cat.name; } );
			        selectedPath = cats.join( separator );
			        if( add == 1 ) {
			        	SP_addCat();
				    }
				}
			}
		).send();
	}
	function SP_Save()
	{
		if( selectedCat ) {
			parent.document.getElementById( SPObjType + '.path' ).value = SobiPro.StripSlashes( selectedCatNames.join( '\n' ) );
			parent.document.getElementById( SPObjType + '.parent' ).value = selectedCats.join( ', ' );
		}
		parent.SP_close();
	}
	function SP_addCat()
	{
		if( selectedCat == 0 || selectedPath == '' ) {
			SobiPro.Alert( "PLEASE_SELECT_CATEGORY_YOU_WANT_TO_ADD_IN_THE_TREE_FIRST" );
			return false;
		}
		for( var i = 0; i <= selectedCats.length; ++i ) {
			if( selectedCats[ i ] == selectedCat ) {
				SobiPro.Alert( "THIS_CATEGORY_HAS_BEEN_ALREADY_ADDED" );
				return false;
			}
		}
		var selCats = SP_id( 'selectedCats' );
		var newOpt = document.createElement( 'option' );
		newOpt.text = SobiPro.StripSlashes( selectedCatName );
		newOpt.value = selectedCat;
		newOpt.title = SobiPro.StripSlashes( selectedPath );
	    try { selCats.add( newOpt, null ); } catch( x ) { selCats.add( newOpt ); }
	    selectedCatNames[ selectedCats.length ] = selectedPath;
	    selectedCats[ selectedCats.length ] = selectedCat;
	    for ( var i = 0; i <= selCats.options.length; ++i ) {
		    if( i >=  maxCat ) {
		    	SP_id( 'addCat' ).disabled = true;
		    	break;
			}
	    }
	}
	function SP_delCat()
	{
		var selCats = SP_id( 'selectedCats' );
		var selOpt = selCats.options[ selCats.selectedIndex ];
		cid = selOpt.value;
		cp =  selOpt.title;
		selCats.options[ selCats.selectedIndex ] = null;
		for( var i = 0; i <= selectedCats.length; ++i ) {
			if( selectedCats[ i ] == cid ) {
				selectedCatNames.splice( i, 1 );
				selectedCats.splice( i, 1 );
			}
		}
		SP_id( 'addCat' ).disabled = false;
	}
</script>
<div style="margin: 5px; padding: 5px;">
	<div style="width: 300px; float: left; min-height: 360px; overflow: hidden;">
		<fieldset class="adminform" style="height: 350px;">
			<legend>
				<?php $this->txt( 'CC.SELECT_PARENT_CAT' ); ?>
			</legend>
			<div style="height: 330px; max-width: 305px; overflow: hidden;">
				<?php $this->get( 'tree' )->display(); ?>
			</div>
		</fieldset>
	</div>
	<div style="min-height: 270px;  min-width: 225px;">
		<fieldset class="adminform" style="min-height: 260px;">
		<legend>
			<?php $this->txt( 'CC.CURRENT_SELECTED_CATS' ); ?>
		</legend>
		<div>
			<div style="height: 240px; float:left; padding: 2px;">
				<?php $this->field( 'select', 'categories', [], null, false, [ 'id' => 'selectedCats', 'size' => 10, 'class' => 'inputbox', 'style' => 'width: 225px; height: 240px; font-weight: bold; font-size: 10px; overflow: hidden; border-color: #eeeeee!important;' ] ); ?>
			</div>
		</div>
		</fieldset>
	</div>
		<div style="padding: 4px; float: left;">
			<?php $this->field( 'button', 'addCat', 'translate:[CC.ADD_BT]', [ 'id' => 'addCat', 'onclick' => 'SP_addCat();', 'size' => 50, 'class' => 'button', 'style' => ' text-align: center; width: 180px; font-size: 13px; border: 1px solid silver;' ] ); ?>
		</div>
        <div style="padding: 4px; float: left; ">
            <?php $this->field( 'button', 'delCat', 'translate:[CC.DEL_BT]', [ 'onclick' => 'SP_delCat();', 'size' => 50, 'class' => 'button', 'style' => 'text-align: center; width: 180px; font-size: 13px; border: 1px solid silver;' ] ); ?>
        </div>
	<div style="clear: both"></div>
	<div style="padding: 4px; float: right;">
		<?php $this->field( 'button', 'save', 'translate:[CC.SAVE_BT]', [ 'onclick' => 'SP_Save();', 'size' => 50, 'class' => 'button', 'style' => 'text-align: center; width: 100px; font-size: 13px; border: 1px solid silver;' ] ); ?>
	</div>
</div>
