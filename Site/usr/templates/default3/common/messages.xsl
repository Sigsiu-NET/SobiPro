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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:template match="messages">
		<div class="clearfix" />
		<div class="spMessage">
            <xsl:for-each select="./*">
                <div class="alert alert-{name()}">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <xsl:for-each select="./*">
                        <xsl:value-of select="." />
                        <div class="clearfix" />
                    </xsl:for-each>
                </div>
            </xsl:for-each>
            <div class="alert hide" id="sobipro-message">
                <button type="button" class="close" data-dismiss="alert">×</button>
            </div>
        </div>
	</xsl:template>
</xsl:stylesheet>
