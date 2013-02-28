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
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 $Date$
 $Revision$
 $Author$
 $HeadURL$
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" encoding="UTF-8"/>
    <xsl:template match="/section|/category">
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>
                <xsl:value-of select="name"/>
            </title>
            <xsl:for-each select="entries/entry">
                <xsl:variable name="url">
                    <xsl:value-of select="php:function( 'SobiPro::Cfg', 'live_site' )"/><xsl:value-of select="url"/>
                </xsl:variable>
                <entry>
                    <title>
                        <xsl:value-of select="name"/>
                    </title>
                    <link rel="alternate">
                        <xsl:attribute name="href"><xsl:value-of select="php:function( 'Sobi::FixPath', $url )"/></xsl:attribute>
                    </link>
                    <id>
                        <xsl:value-of select="@id"/>
                    </id>
                    <content type="html">
                        <xsl:value-of select="fields/field_short_description/data"/>
                    </content>
                </entry>
            </xsl:for-each>
        </feed>
    </xsl:template>
</xsl:stylesheet>
