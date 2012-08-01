<?php
/**
 * @version: $Id: default.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/usr/templates/front/default.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<div style="padding: 10px;">
<?php $c = $this->count( 'sections' );
for ( $i = 0; $i < $c ; $i++ ) { ?>
	<div>
		<a href="<?php echo Sobi::Url( array( 'sid' => $this->get( 'sections.id', $i ) ) )?>"><?php $this->show( 'sections.name', $i ); ?> </a>
	</div>
	<?php } ?>
</div>
