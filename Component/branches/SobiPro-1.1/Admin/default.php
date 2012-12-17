<?php
/**
 * @version: $Id: list.php 1979 2011-11-08 18:25:45Z Radek Suski $
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
 * $Date: 2011-11-08 19:25:45 +0100 (Tue, 08 Nov 2011) $
 * $Revision: 1979 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/field/list.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
$data = $this->getData();
?>
<?php echo $this->toolbar(); ?>
<?php $this->trigger( 'OnStart' ); ?>
<!--starts here-->
<?php foreach ( $data[ 'data' ] as $element ) : ?>
	<?php $this->getParser()->parse( $element ); ?>
<?php endforeach; ?>
<!--ends here-->
<?php $this->trigger( 'OnEnd' ); ?>
