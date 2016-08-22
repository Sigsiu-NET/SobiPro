<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT AN Y WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:template name="font">

		<!-- Load and apply the special fonts -->
		<xsl:variable name="basefont"><xsl:value-of select="//config/basefont/@value" /></xsl:variable>
		<xsl:if test="//config/basefont/@value != 'none' and //config/basefont/@value != ''">
			<xsl:value-of select="php:function( 'tplDefault3::LoadFont', $basefont)"/>
			<xsl:value-of select="php:function( 'tplDefault3::ApplyBaseFont', $basefont)"/>
		</xsl:if>
		<xsl:variable name="specialfont"><xsl:value-of select="//config/specialfont/@value" /></xsl:variable>
		<xsl:if test="$basefont != $specialfont and //config/specialfont/@value != '' and //config/specialfont/@value != 'none'">
			<xsl:value-of select="php:function( 'tplDefault3::LoadFont', $specialfont)"/>
			<xsl:value-of select="php:function( 'tplDefault3::ApplyFont', $specialfont)"/>
		</xsl:if>

	</xsl:template>
</xsl:stylesheet>
