<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

	<xsl:include href="../common/topmenu.xsl"/>
	<xsl:include href="../common/alphamenu.xsl"/>
	<xsl:include href="../common/entries.xsl"/>
	<xsl:include href="../common/navigation.xsl"/>
	<xsl:include href="../common/messages.xsl"/>

	<xsl:template match="/listing">

		<!-- for proper work a container is needed, we assume that the component area is placed into a container by the template.
		If not, you need to add a container around the SobiPro output here -->
		<div class="spListing alpha">
			<xsl:call-template name="topMenu">
				<xsl:with-param name="searchbox">true</xsl:with-param>
				<xsl:with-param name="title"></xsl:with-param>
			</xsl:call-template>
			<xsl:apply-templates select="messages"/>
			<xsl:apply-templates select="alphaMenu"/>

			<h1>
				<xsl:value-of select="name"/>
			</h1>
			<xsl:call-template name="entriesLoop"/>
			<xsl:apply-templates select="navigation"/>

			<xsl:call-template name="bottomHook"/>
		</div>
	</xsl:template>
</xsl:stylesheet>
