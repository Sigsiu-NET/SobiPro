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
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

    <xsl:include href="../common/topmenu.xsl" />
    <xsl:include href="../common/manage.xsl" />
    <xsl:include href="../common/alphamenu.xsl" />
    <xsl:include href="../common/messages.xsl" />
    <xsl:include href="../common/showfields.xsl" />

    <!-- Uncomment only if >Profile Field is installed -->
    <!--<xsl:include href="../common/profile.xsl" />-->

    <!-- Uncomment only if Review & Ratings App is installed -->
    <!--<xsl:include href="../common/review.xsl" />-->

    <!-- Uncomment only if Collection App is installed -->
    <!--<xsl:include href="../common/collection.xsl" />-->

    <xsl:template match="/entry_details">

        <!-- for proper work a container is needed, we assume that the component area is placed into a container by the template.
        If not, you need to add a container around the SobiPro output here -->
        <div class="spDetails">
            <div>
                <xsl:call-template name="topMenu">
                    <xsl:with-param name="searchbox">true</xsl:with-param>
                </xsl:call-template>
                <xsl:apply-templates select="alphaMenu" />
            </div>
            <xsl:apply-templates select="messages" />
            <div class="clearfix" />
            <div class="spDetailEntry">
                <xsl:call-template name="manage" />
                <xsl:if test="( //reviews/settings/rating_enabled = 1 ) and document('')/*/xsl:include[@href='../common/review.xsl'] ">
                    <xsl:call-template name="ratingStars" />
                </xsl:if>
                <h1>
                    <xsl:value-of select="entry/name" />
                    <xsl:call-template name="status">
                        <xsl:with-param name="entry" select="entry" />
                    </xsl:call-template>
                </h1>

                <!-- Uncomment only if Collection App is installed -->
                <!--<xsl:call-template name="collection"><xsl:with-param name="entry" select="entry"/></xsl:call-template>-->

				<!-- Loop to show all enabled fields from fields manager -->
                <xsl:for-each select="entry/fields/*">
                    <xsl:call-template name="showfield">
                        <xsl:with-param name="fieldname" select="." />
                        <xsl:with-param name="view" select="'dv'" />
                    </xsl:call-template>
                </xsl:for-each>
	            <div class="clearfix"></div>


                <xsl:if test="( //reviews/settings/rating_enabled = 1 ) and document('')/*/xsl:include[@href='../common/review.xsl'] ">
                    <xsl:call-template name="ratingSummary" />
                </xsl:if>

                <xsl:if test="count(entry/categories)">
                    <div class="spEntryCats">
                        <xsl:value-of select="php:function( 'SobiPro::Txt' , 'ENTRY_LOCATED_IN' )" /><xsl:text> </xsl:text>
                        <xsl:for-each select="entry/categories/category">
                            <a href="{@url}">
                                <xsl:value-of select="." />
                            </a>
                            <xsl:if test="position() != last()">
                                <xsl:text> | </xsl:text>
                            </xsl:if>
                        </xsl:for-each>
                    </div>
                </xsl:if>
            </div>
            <div class="clearfix" />

            <xsl:if test="( count(/entry_details/review_form/*) or count(/entry_details/reviews/*) ) and document('')/*/xsl:include[@href='../common/review.xsl'] ">
                <xsl:call-template name="reviewForm"/>
                <xsl:call-template name="reviews"/>
            </xsl:if>
            <!-- Uncomment only if >Profile Field is installed -->
            <!--<xsl:call-template name="UserContributions" />-->
        </div>
    </xsl:template>
</xsl:stylesheet>
