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
<xsl:output method="xml" encoding="UTF-8" />
	<xsl:template match="letters|/menu/alphaMenu/letters">
		<xsl:variable name="letter">
			<xsl:value-of select="php:function( 'SobiPro::Request', 'letter' )" />
		</xsl:variable>
		<xsl:for-each select="letter">
			<xsl:choose>
				<xsl:when test="@url">
					<span>
						<xsl:attribute name="class">
							<xsl:choose>
								<xsl:when test=". = $letter">spAlphaLetterSelected</xsl:when>
								<xsl:otherwise>spAlphaLetter</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
						<a>
							<xsl:attribute name="href">
								<xsl:value-of select="@url" />
							</xsl:attribute>
							<xsl:value-of select="." />
						</a>
					</span>
				</xsl:when>
				<xsl:otherwise>
					<span>
						<xsl:value-of select="." />
					</span>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
