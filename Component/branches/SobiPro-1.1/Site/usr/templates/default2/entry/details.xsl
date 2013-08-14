<?xml version="1.0" encoding="UTF-8"?><!--
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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/manage.xsl" />
	<xsl:include href="../common/alphamenu.xsl" />
	<xsl:include href="../common/messages.xsl" />

	<xsl:template match="/entry_details">
		<div class="SPDetails">
			<div>
				<xsl:call-template name="topMenu">
					<xsl:with-param name="searchbox">true</xsl:with-param>
				</xsl:call-template>
				<xsl:apply-templates select="alphaMenu" />
			</div>
			<xsl:apply-templates select="messages" />
			<div class="clearfix" />
			<div class="SPDetailEntry">
				<xsl:call-template name="manage" />
				<h1>
					<xsl:value-of select="entry/name" />
					<xsl:call-template name="status">
						<xsl:with-param name="entry" select="entry" />
					</xsl:call-template>
				</h1>

				<xsl:for-each select="entry/fields/*">
					<div>
						<xsl:attribute name="class">
							<xsl:value-of select="@css_class" />
						</xsl:attribute>

						<xsl:if test="count(data/*) or string-length(data)">
							<xsl:if test="label/@show = 1">
								<strong>
									<xsl:value-of select="label" /><xsl:text>: </xsl:text>
								</strong>
							</xsl:if>
						</xsl:if>

						<xsl:choose>
							<xsl:when test="count(data/*)">
								<xsl:copy-of select="data/*" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:if test="string-length(data)">
									<xsl:value-of select="data" disable-output-escaping="yes" />
								</xsl:if>
							</xsl:otherwise>
						</xsl:choose>

						<xsl:if test="count(data/*) or string-length(data)">
							<xsl:if test="string-length(@suffix)">
								<xsl:text> </xsl:text>
								<xsl:value-of select="@suffix" />
							</xsl:if>
						</xsl:if>
					</div>
				</xsl:for-each>

				<xsl:if test="count(entry/categories)">
					<div class="spEntryCats">
						<xsl:value-of select="php:function( 'SobiPro::Txt' , 'ENTRY_LOCATED_IN' )" /><xsl:text> </xsl:text>
						<xsl:for-each select="entry/categories/category">
							<a href="{@url}">
								<xsl:value-of select="." />
							</a>
							<xsl:if test="position() != last()">
								<xsl:text> | </xsl:text>
							</xsl:if>
						</xsl:for-each>
					</div>
				</xsl:if>
			</div>
			<div class="clearfix" />
		</div>
	</xsl:template>
</xsl:stylesheet>
