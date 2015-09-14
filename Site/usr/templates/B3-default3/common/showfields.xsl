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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

     <xsl:template name="showfield">
        <xsl:param name="fieldname" />
        <xsl:param name="view" />

        <div>
            <xsl:if test="string-length($fieldname/@css-view)">
                <xsl:attribute name="class">
                    <xsl:value-of select="$fieldname/@css-view" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="string-length($fieldname/@itemprop)">
                <xsl:attribute name="itemprop"><xsl:value-of select="$fieldname/@itemprop"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="count($fieldname/data/*) or string-length($fieldname/data)">
                <xsl:if test="$fieldname/label/@show = 1">
                    <strong><xsl:value-of select="$fieldname/label" /><xsl:text>: </xsl:text></strong>
                </xsl:if>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="count($fieldname/data/*)">
                    <xsl:copy-of select="$fieldname/data/*"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:if test="string-length($fieldname/data)">
                        <xsl:value-of select="$fieldname/data" disable-output-escaping="yes" />
                    </xsl:if>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:if test="count($fieldname/data/*) or string-length($fieldname/data)">
                <xsl:if test="string-length($fieldname/@suffix)">
                    <xsl:text> </xsl:text>
                    <xsl:choose>
                    <xsl:when test="$view = dv">
                        <span class="spDetailSuffix"><xsl:value-of select="$fieldname/@suffix" /></span>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$fieldname/@suffix"/>
                    </xsl:otherwise>
                    </xsl:choose>
                </xsl:if>
            </xsl:if>
        </div>
    </xsl:template>

</xsl:stylesheet>
