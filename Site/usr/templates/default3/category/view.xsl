<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

	<xsl:include href="../common/navigation.xsl" />
	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/alphamenu.xsl" />
	<xsl:include href="../common/entries.xsl" />
	<xsl:include href="../common/categories.xsl" />
    <xsl:include href="../common/messages.xsl" />
	<xsl:template match="/category">
		<xsl:variable name="rssUrlSection">{"sid":"<xsl:value-of select="section/@id" />","sptpl":"feeds.rss","out":"raw"}
		</xsl:variable>
		<xsl:variable name="sectionName">
			<xsl:value-of select="section" />
		</xsl:variable>

		<xsl:value-of select="php:function( 'SobiPro::AlternateLink', $rssUrlSection, 'application/atom+xml', $sectionName )" />
		<xsl:variable name="rssUrl">{"sid":"<xsl:value-of select="id" />","sptpl":"feeds.rss","out":"raw"}
		</xsl:variable>
		<xsl:variable name="categoryName">
			<xsl:value-of select="name" />
		</xsl:variable>
		<xsl:value-of select="php:function( 'SobiPro::AlternateLink', $rssUrl, 'application/atom+xml', $categoryName )" />

		<xsl:call-template name="topMenu">
			<xsl:with-param name="searchbox">true</xsl:with-param>
		</xsl:call-template>
		<xsl:apply-templates select="messages" />

		<xsl:apply-templates select="alphaMenu" />

        <h1><xsl:value-of select="name" /></h1>
        <div class="spCategoryDesc">
    		<xsl:value-of select="description" disable-output-escaping="yes" />
    	</div>

		<xsl:call-template name="categoriesLoop" />
		<xsl:call-template name="entriesLoop" />
		<xsl:apply-templates select="navigation" />
	</xsl:template>
</xsl:stylesheet>
