<?xml version="1.0" encoding="UTF-8"?>
<!--
 @version: $Id: user.xsl 4387 2015-02-19 12:24:35Z Radek Suski $
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: http://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 $Date: 2015-02-19 13:24:35 +0100 (Thu, 19 Feb 2015) $
 $Revision: 4387 $
 $Author: Radek Suski $
 File location: components/com_sobipro/usr/templates/default2/listing/user.xsl $
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/alphamenu.xsl" />
	<xsl:include href="../common/entries.xsl" />
	<xsl:include href="../common/navigation.xsl" />
	<xsl:include href="../common/messages.xsl" />
	<xsl:template match="/listing">
		<div class="SPListing">
			<div>
                <xsl:call-template name="topMenu">
                    <xsl:with-param name="searchbox">true</xsl:with-param>
                </xsl:call-template>
				<xsl:apply-templates select="alphaMenu" />
			</div>
			<xsl:apply-templates select="messages" />
			<xsl:call-template name="entriesLoop" />
			<xsl:apply-templates select="navigation" />
		</div>
	</xsl:template>
</xsl:stylesheet>
