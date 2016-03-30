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
    <xsl:template name="topMenu">
        <xsl:param name="searchbox" />
        <xsl:if test="count(//menu/*)">
            <xsl:variable name="currentUrl">
                <xsl:value-of select="php:function( 'SobiPro::Url', 'current' )" />
            </xsl:variable>
            <div class="navbar navbar-default topmenu" role="navigation">
                <div class="container-fluid">
                    <ul class="nav navbar-nav">
                        <xsl:if test="//menu/front">
                            <li>
                                <a href="{//menu/front/@url}">
	                                <xsl:choose>
		                                <xsl:when test="//menu/search">
			                                <xsl:if test="$currentUrl != //menu/add/@url and $currentUrl != //menu/search/@url">
				                                <xsl:attribute name="class">active</xsl:attribute>
			                                </xsl:if>
		                                </xsl:when>
		                                <xsl:otherwise>
			                                <xsl:if test="$currentUrl != //menu/add/@url">
				                                <xsl:attribute name="class">active</xsl:attribute>
			                                </xsl:if>
		                                </xsl:otherwise>
	                                </xsl:choose>
                                    <i class="icon-th-list"></i>
                                    <xsl:text> </xsl:text>
                                    <xsl:value-of select="//menu/front" />
                                </a>
                            </li>
                        </xsl:if>
                        <xsl:if test="//menu/add">
                            <li>
                                <a href="{//menu/add/@url}">
                                    <xsl:if test="$currentUrl = //menu/add/@url">
                                        <xsl:attribute name="class">active</xsl:attribute>
                                    </xsl:if>
                                    <i class="icon-plus-sign"></i>
                                    <xsl:text> </xsl:text>
                                    <xsl:value-of select="//menu/add" />
                                </a>
                            </li>
                        </xsl:if>
                        <xsl:if test="//menu/search">
                            <li>
                                <a href="{//menu/search/@url}">
                                    <xsl:if test="$currentUrl = //menu/search/@url">
                                        <xsl:attribute name="class">active</xsl:attribute>
                                    </xsl:if>
                                    <i class="icon-search"></i>
                                    <xsl:text> </xsl:text>
                                    <xsl:value-of select="//menu/search" />
                                </a>
                            </li>
                        </xsl:if>
                        <li>
                            <xsl:copy-of select="/*/collection/button/*"/>
                        </li>
                    </ul>
                    <xsl:if test="//menu/search and $searchbox = 'true'">
                        <div class="collapse navbar-collapse">
                            <form class="navbar-form navbar-right navbar-search">
                                <div class="form-group">
                                    <input type="text" name="sp_search_for" autocomplete="off" class="search-query form-control" placeholder="{php:function( 'SobiPro::Txt', 'SH.SEARCH_FOR_BOX' )}" />
                                    <input type="hidden" name="task" value="search.search" />
                                    <input type="hidden" name="option" value="com_sobipro" />
                                    <input type="hidden" name="sid" value="{//@id}" />
                                </div>
                            </form>
                        </div>
                    </xsl:if>
                </div>
            </div>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>

