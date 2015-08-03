<?xml version="1.0" encoding="UTF-8"?><!--
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
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
    <xsl:template name="category">
        <xsl:variable name="subcatsNumber">
            <xsl:value-of select="/section/number_of_subcats" />
        </xsl:variable>
        <div class="row-fluid">
            <div class="span3 spCaticon">
				<xsl:choose>
                    <xsl:when test="string-length( icon/@element )">
                        <a href="{url}">
                            <xsl:element name="{icon/@element}">
                                <xsl:attribute name="class">
                                    <xsl:value-of select="icon/@class" />
                                </xsl:attribute>
                                <xsl:value-of select="icon/@content" />
                            </xsl:element>
                        </a>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:if test="string-length( icon )">
                            <a href="{url}">
                                <img alt="{name}" src="{icon}" />
                            </a>
                        </xsl:if>
                    </xsl:otherwise>
				</xsl:choose>
			</div>
            <div class="span9 spCatname">
                <p>
                    <a href="{url}">
                        <xsl:value-of select="name" />
                    </a>
                </p>
                <div class="spCatintro">
                    <xsl:value-of select="introtext" disable-output-escaping="yes" />
                </div>
                <xsl:for-each select="subcategories/subcategory">
                    <xsl:if test="position() &lt; ( $subcatsNumber + 1 )">
                        <a href="{@url}">
                            <small><xsl:value-of select="." /></small>
                        </a>
                        <xsl:if test="position() != last() and position() &lt; $subcatsNumber">
                            <xsl:text>, </xsl:text>
                        </xsl:if>
                    </xsl:if>
                </xsl:for-each>
            </div>
        </div>

    </xsl:template>
</xsl:stylesheet>
