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

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <!-- Uncomment only if Review & Ratings App is installed -->
    <!--<xsl:import href="review.xsl" />-->
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
    <xsl:include href="showfields.xsl" />
    <!-- Uncomment only if Collection App is installed -->
    <!--<xsl:include href="collection.xsl" />-->

    <xsl:template name="vcard">
        <xsl:if test="( //reviews/settings/rating_enabled = 1 ) and document('')/*/xsl:import[@href='review.xsl']" >
            <xsl:call-template name="ratingStars" />
        </xsl:if>

        <h2 class="page-header lead">
            <a href="{url}">
                <xsl:value-of select="name" />
                <xsl:call-template name="status">
                    <xsl:with-param name="entry" select="." />
                </xsl:call-template>
            </a>
        </h2>
        <!-- Uncomment only if Collection App is installed -->
        <!--<xsl:call-template name="collection"><xsl:with-param name="entry" select="."/></xsl:call-template>-->

        <xsl:for-each select="fields/*">
            <xsl:call-template name="showfield">
                <xsl:with-param name="fieldname" select="." />
                <xsl:with-param name="view" select="'vcard'" />
            </xsl:call-template>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
