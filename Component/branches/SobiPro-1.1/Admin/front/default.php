<?php
/**
 * @version: $Id: default.php 2078 2011-12-16 16:11:14Z Radek Suski $
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
 * $Date: 2011-12-16 17:11:14 +0100 (Fri, 16 Dec 2011) $
 * $Revision: 2078 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/front/default.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
$c = $this->count( 'icons' );
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="width: 48%; float:left; padding: 5px;">
    <?php for ( $i = 0; $i < $c; $i++ ) { ?>
    <div class="SPCpanelIcon">
        <?php
        $icon = Sobi::FixPath( Sobi::Cfg( 'img_folder_live' ) . '/' . $this->get( 'icons.icon', $i ) );
        $url = Sobi::Url( $this->get( 'icons.task', $i ) );
        $name = Sobi::Txt( $this->get( 'icons.name', $i ) );
        ?>
        <a href="<?php echo $url;?>">
            <img alt="" src="<?php echo $icon;?>"/>
        </a>
        <br/>
        <a href="<?php echo $url;?>">
            <?php echo $name;?>
        </a>
    </div>
    <?php } ?>
    <?php $this->trigger( 'AfterPanelIcons' ); ?>
    <table class="adminlist" cellspacing="1">
        <thead>
        <tr>
            <th width="5%">
                <?php $this->show( 'header.id' ); ?>
            </th>
            <th width="5%">
                <?php $this->show( 'header.checkbox' ); ?>
            </th>
            <th class="title">
                <?php $this->show( 'header.name' ); ?>
            </th>
            <th width="10%">
                <?php $this->show( 'header.state' ); ?>
            </th>
        </tr>
        </thead>

        <?php
        $c = $this->count( 'sections' );
        for ( $i = 0; $i < $c; $i++ ) {
            $style = $i % 2;
            ?>
            <tr class="row<?php echo $style;?>">
                <td style="text-align: center">
                    <?php $this->show( 'sections.id', $i ); ?>
                </td>
                <td style="text-align: center">
                    <?php $this->show( 'sections.checkbox', $i ); ?>
                </td>
                <td>
                    <?php $this->show( 'sections.name', $i ); ?>
                </td>
                <td style="text-align: center">
                    <?php $this->show( 'sections.state', $i ); ?>
                </td>
            </tr>
            <?php } ?>
    </table>
</div>
<?php $this->trigger( 'AfterSections' ); ?>
<div style="width: 50%; float:right; padding: 3px;" id="SpAbout">
    <?php $this->show( 'about' ); ?>
</div>
<?php $this->trigger( 'OnEnd' ); ?>
