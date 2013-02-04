<?xml version="1.0" encoding="UTF-8"?>
<!--
 @version: $Id$
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: http://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 $Date$
 $Revision$
 $Author$
 $HeadURL$
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:include href="list.xsl" />
	<xsl:template match="/payment_details">
		<div class="modal">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				<h4>
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_CHOSEN_FOLLOWING_OPTIONS' )" />
				</h4>
			</div>
			<div class="modal-body">
				<xsl:call-template name="paymentTable" />
			</div>
			<div class="modal-footer">
				<a href="#" class="btn" data-dismiss="modal">
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'EN.PAYMENT_BACK_BT' )" />
				</a>
				<a href="{/payment_details/save_url}" class="btn btn-primary">
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'EN.PAYMENT_SAVE_ENTRY_BT' )" />
				</a>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
