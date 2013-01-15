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
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
<xsl:include href="alphaindex.xsl" />
	<xsl:template match="alphaMenu">
		<xsl:variable name="contId">SPAlphaIndex<xsl:value-of select="php:function( 'rand', 1, 5 )" /></xsl:variable>
		<div class="spAlphaMenu">
			<xsl:if test="count(fields/*)">
				<script type="text/javascript">
					SPAlphaSwitch( '<xsl:value-of select="$contId" />' );
				</script>
				<xsl:variable name="current">
					<xsl:value-of select="fields/@current" />
				</xsl:variable>
				<span>
					<xsl:attribute name="id"><xsl:value-of select="$contId" />Progress</xsl:attribute>
					<xsl:attribute name="style">z-index: 9; position: absolute;</xsl:attribute>
				</span>
				<select>
					<xsl:attribute name="class">spAlphaMenuSwitch</xsl:attribute>
					<xsl:attribute name="id"><xsl:value-of select="$contId" />Switch</xsl:attribute>
					<xsl:for-each select="fields/*">
						<option>
							<xsl:attribute name="value">
								<xsl:value-of select="name()" />
							</xsl:attribute>
							<xsl:if test="name() = $current">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:value-of select="." />
						</option>
					</xsl:for-each>
				</select>
			</xsl:if>
			<span id="{$contId}">
				<xsl:apply-templates select="letters" />
			</span>
		</div>
		<div style="clear:both;"/>
	</xsl:template>
</xsl:stylesheet>
