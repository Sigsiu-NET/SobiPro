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
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:template name="vcard">
		<span class="lead">
			<a href="{url}">
				<xsl:value-of select="name" />
				<xsl:call-template name="status">
					<xsl:with-param name="entry" select="." />
				</xsl:call-template>
			</a>
		</span>
		<xsl:for-each select="fields/*">
			<div>
				<xsl:attribute name="class"><xsl:value-of select="@css_class" /></xsl:attribute>
				<xsl:if test="count(data/*) or string-length(data)">
					<xsl:if test="label/@show = 1">
						<strong><xsl:value-of select="label" />: </strong>
					</xsl:if>
				</xsl:if>
				<xsl:choose>
					<xsl:when test="count(data/*)">
						<xsl:copy-of select="data/*"/>
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
						<xsl:value-of select="@suffix"/>
					</xsl:if>
				</xsl:if>
			</div>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
