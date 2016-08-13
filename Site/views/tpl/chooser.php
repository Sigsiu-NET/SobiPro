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
//SPFactory::header()->addCSSCode( '.sigsiuTree {height: 270px;}' );

?>
<script language="javascript" type="text/javascript">
    function SP_selectCat( sid )
    {
        parent.document.getElementById( 'SP_selectedCid' ).value = sid;
        var separator = '<?php echo Sobi::Cfg( 'string.path_separator', ' > ' ); ?>';
        var cats = [];
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
                            cats[ cats.length ] = cat.name;
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
