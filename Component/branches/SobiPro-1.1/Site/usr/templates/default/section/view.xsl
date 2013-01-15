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
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 $Date$
 $Revision$
 $Author$
 $HeadURL$
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

<xsl:include href="../common/topmenu.xsl" />
<xsl:include href="../common/alphamenu.xsl" />
<xsl:include href="../common/category.xsl" />
<xsl:include href="../common/entries.xsl" />
<xsl:include href="../common/navigation.xsl" />

<xsl:variable name="subcatsNumber">
	<xsl:value-of select="/section/number_of_subcats" />
</xsl:variable>

<xsl:template match="/section">
	<xsl:variable name="rssUrl">{"sid":"<xsl:value-of select="id"/>","sptpl":"feeds.rss","out":"raw"}</xsl:variable>
	<xsl:variable name="sectionName"><xsl:value-of select="name"/></xsl:variable>
	<xsl:value-of select="php:function( 'SobiPro::AlternateLink', $rssUrl, 'application/atom+xml', $sectionName )" />
	<div class="SPListing">
	    <div>
	      <xsl:apply-templates select="menu" />
	      <xsl:apply-templates select="alphaMenu" />
	    </div>
		<div style="clear:both;"/>
		<div class="spSectionDesc">
			<xsl:value-of select="description" disable-output-escaping="yes" />
		</div>
		<div class="spCatListContainer">
			<xsl:variable name="catsInLine">
				<xsl:value-of select="categories_in_line" />
			</xsl:variable>
			<xsl:variable name="cellWidth">
				<xsl:value-of select="(100 div $catsInLine) - 5" />
			</xsl:variable>
			<xsl:for-each select="categories/category">
				<xsl:choose>
					<xsl:when test="(position() - 1) mod $catsInLine"></xsl:when>
					<xsl:otherwise>
						<div style="clear:both;"></div>
					</xsl:otherwise>
				</xsl:choose>
				<div class="spCatListCell" style="width: {$cellWidth}%">
					<xsl:call-template name="category" />
				</div>
			</xsl:for-each>
			<div style="clear:both;"></div>
		</div>
		<xsl:call-template name="entriesLoop" />
		<xsl:apply-templates select="navigation" />
		<div style="clear:both;"></div>
	</div>
</xsl:template>
</xsl:stylesheet>
