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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:template name="category">
		<xsl:variable name="url">
			<xsl:value-of select="url" />
		</xsl:variable>
		<div class="spCatListIcon">
			<xsl:if test="string-length( icon )">
				<a href="{$url}">
					<img alt="icon" class="spCatListIcon">
						<xsl:attribute name="src">
							<xsl:value-of select="icon" />
						</xsl:attribute>
					</img>
				</a>
			</xsl:if>
		</div>
		<div class="spCatsListTitle">
			<a href="{$url}">
				<xsl:value-of select="name" />
			</a>
		</div>
		<div class="spCatsListIntrotext">
			<xsl:value-of select="introtext" disable-output-escaping="yes" />
		</div>
		<xsl:for-each select="subcategories/subcategory">
            <xsl:if test="position() &lt; ( $subcatsNumber + 1 )">
                <span class="spCatListSubCats">
                    <a>
                    	<xsl:attribute name="href">
                    		<xsl:value-of select="@url" />
                    	</xsl:attribute>
                    	<xsl:value-of select="." />
                    </a>
                    <xsl:if test="position() != last() and position() &lt; $subcatsNumber">
                    	<xsl:text>, </xsl:text>
                    </xsl:if>
                </span>
            </xsl:if>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
