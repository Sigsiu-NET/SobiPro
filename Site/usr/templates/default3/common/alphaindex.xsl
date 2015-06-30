<?xml version="1.0" encoding="UTF-8"?><!--
 @version: $Id: alphaindex.xsl 4387 2015-02-19 12:24:35Z Radek Suski $
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
 File location: components/com_sobipro/usr/templates/default2/common/alphaindex.xsl $
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" encoding="UTF-8" />
	<xsl:template match="letters|/menu/alphaMenu/letters">
		<xsl:variable name="letter">
			<xsl:value-of select="php:function( 'SobiPro::Request', 'letter' )" />
		</xsl:variable>
		<div class="pagination pagination-mini">
			<ul>
				<xsl:for-each select="letter">
					<li>
						<xsl:if test=". = $letter">
							<xsl:attribute name="class">active</xsl:attribute>
						</xsl:if>
						<xsl:if test="not( @url )">
							<xsl:attribute name="class">disabled</xsl:attribute>
						</xsl:if>
						<xsl:choose>
							<xsl:when test="@url">
								<a href="{@url}">
									<xsl:value-of select="." />
								</a>
							</xsl:when>
							<xsl:otherwise>
								<a href="#">
									<xsl:value-of select="." />
								</a>
							</xsl:otherwise>
						</xsl:choose>
					</li>
				</xsl:for-each>
			</ul>
		</div>
	</xsl:template>
</xsl:stylesheet>
