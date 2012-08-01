<?php
/**
 * @version: $Id: default.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/extensions/default.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
?>
<?php $this->trigger( 'OnStart' ); ?>
<div style="float: left; width: 20em;">
	<?php $this->menu(); ?>
</div>
<?php $this->trigger( 'AfterDisplayMenu' ); ?>
<div style="margin-left: 20em; padding: 5px;">
	<div class="sheader icon-48-SobiCatList">
		<?php $this->show( 'title' ); ?>
	</div>
	<div>
		<?php $this->show( 'content' ); ?>
	</div>
</div>
<?php $this->trigger( 'OnEnd' ); ?>