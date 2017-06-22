<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
    <xsl:template name="category">
        <xsl:variable name="subcatsNumber">
	        <xsl:value-of select="//./number_of_subcats" />
        </xsl:variable>
        <div class="row">
            <div class="col-xs-3 spCaticon">
				<xsl:choose>
                    <xsl:when test="string-length( icon/@element )">
                        <a href="{url}" aria-label="switch to category {name}">
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
            <div class="col-xs-9 spCatname">
                <p>
                    <a href="{url}">
                        <xsl:value-of select="name" />
                    </a>
                    <xsl:if test="//config/countentries/@value = 1">
                        <span class="spEntryCount">
                            <xsl:text> (</xsl:text>
                            <xsl:value-of select="php:function( 'SobiPro::Count', string( @id ), 'entry' )" />
                            <xsl:text>)</xsl:text>
                        </span>
                    </xsl:if>
	                <xsl:call-template name="cat-status">
		                <xsl:with-param name="entry" select="."/>
	                </xsl:call-template>
                </p>

                <div class="spCatintro">
                    <xsl:value-of select="introtext" disable-output-escaping="yes" />
                </div>

                <!-- Output here category fields for sub-categories -->

                <div class="spSubcats">
                    <xsl:for-each select="subcategories/subcategory">
                        <xsl:if test="position() &lt; ( $subcatsNumber + 1 )">
                            <a href="{@url}">
                                <small><xsl:value-of select="." /></small>
                            </a>
                            <xsl:if test="position() != last() and position() &lt; $subcatsNumber">
                                <span role="separator">, </span>
                            </xsl:if>
                        </xsl:if>
                    </xsl:for-each>
                </div>
            </div>
        </div>
    </xsl:template>

	<xsl:template name="cat-status">
		<xsl:param name="entry"/>
		<xsl:if test="$entry/state = 'unpublished'">
			<a class="entry-status" href="#" data-toggle="popover" data-content="{php:function( 'SobiPro::Txt', 'CATEGORY_STATUS_UNPUBLISHED' )}" title="" >
				<i class="icon-remove-sign" />
			</a>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
