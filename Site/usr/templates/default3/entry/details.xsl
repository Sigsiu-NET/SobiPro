<?xml version="1.0" encoding="UTF-8"?><!--
 @version: $Id: details.xsl 4420 2015-03-26 09:09:27Z Radek Suski $
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

 $Date: 2015-03-26 10:09:27 +0100 (Thu, 26 Mar 2015) $
 $Revision: 4420 $
 $Author: Radek Suski $
 File location: components/com_sobipro/usr/templates/default2/entry/details.xsl $
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/manage.xsl" />
	<xsl:include href="../common/alphamenu.xsl" />
	<xsl:include href="../common/messages.xsl" />

	<xsl:template match="/entry_details">
		<div class="SPDetails">
			<div>
				<xsl:call-template name="topMenu">
					<xsl:with-param name="searchbox">true</xsl:with-param>
				</xsl:call-template>
				<xsl:apply-templates select="alphaMenu" />
			</div>
			<xsl:apply-templates select="messages" />
			<div class="clearfix" />
			<div class="SPDetailEntry">
				<xsl:call-template name="manage" />
				<h1>
					<xsl:value-of select="entry/name" />
					<xsl:call-template name="status">
						<xsl:with-param name="entry" select="entry" />
					</xsl:call-template>
				</h1>

				<xsl:for-each select="entry/fields/*">
                    <xsl:call-template name="showfield">
                        <xsl:with-param name="fieldname" select="." />
                    </xsl:call-template>
				</xsl:for-each>

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
		</div>
	</xsl:template>

	<xsl:template name="showfield">
        <xsl:param name="fieldname" />
        <div class="{$fieldname/@css_class}">
            <xsl:if test="string-length($fieldname/@itemprop)">
                <xsl:attribute name="itemprop"><xsl:value-of select="$fieldname/@itemprop"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="count($fieldname/data/*) or string-length($fieldname/data)">
                <xsl:if test="$fieldname/label/@show = 1">
                    <strong>
                        <xsl:value-of select="$fieldname/label" /><xsl:text>: </xsl:text>
                    </strong>
                </xsl:if>
            </xsl:if>

            <xsl:choose>
                <xsl:when test="count($fieldname/data/*)">
                    <xsl:copy-of select="$fieldname/data/*" />
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
                    <xsl:value-of select="$fieldname/@suffix" />
                </xsl:if>
            </xsl:if>
        </div>
	</xsl:template>
</xsl:stylesheet>
