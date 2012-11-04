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
SPFactory::header()->addCSSCode( '.sigsiuTree {height: 270px;}' );

?>
<script language="javascript" type="text/javascript">
    function SP_selectCat( sid )
    {
        parent.document.getElementById( 'SP_selectedCid' ).value = sid;
        var separator = '<?php echo Sobi::Cfg( 'string.path_separator', ' > ' ); ?>'
        var cats = new Array();
        try {
            SP_id( 'sobiCats_CatUrl' + sid ).focus();
        }
        catch ( e ) {
        }
        var request = new SobiPro.Json(
                '<?php $this->show( 'parent_ajax_url' ); ?>' + '&sid=' + sid,
                {
                    onComplete:function ( jsonObj, jsons )
                    {
                        catName = '';
                        jsonObj.categories.each( function ( cat )
                        {
                            cats[ cat.id ] = cat.name;
                            catName = cat.name;
                        } );
                        selectedPath = cats.join( separator );
                        parent.document.getElementById( 'SP_selectedCatPath' ).value = SobiPro.StripSlashes( selectedPath );
                        parent.document.getElementById( 'SP_selectedCatName' ).value = SobiPro.StripSlashes( catName );
                    }
                }
        ).send();
    }
</script>
<div>
    <div><?php $this->get( 'tree' )->display(); ?></div>
</div>
