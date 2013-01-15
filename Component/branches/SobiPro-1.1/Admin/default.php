<?php
/**
 * @version: $Id$
 * @package: SobiPro Template
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
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
