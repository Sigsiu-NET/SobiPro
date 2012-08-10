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
    function SP_selectCat( sid )
    {
        try {
            SP_id( 'sobiCats_CatUrl' + sid ).focus();
        }
        catch ( e ) {}
        parent.document.getElementById( 'selectedCat' ).value = sid;
        parent.document.getElementById( 'selectedCatName' ).value = SP_id( 'sobiCats_CatUrl' + sid ).innerHTML;
    }
</script>
<div style="margin: 5px; padding: 5px;">
    <div><?php $this->get( 'tree' )->display(); ?></div>
</div>
