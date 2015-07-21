<?xml version="1.0" encoding="UTF-8"?>
<!--
 @version: $Id: alphamenu.xsl 4387 2015-02-19 12:24:35Z Radek Suski $
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

 $Date: 2015-02-19 13:24:35 +0100 (Thu, 19 Feb 2015) $
 $Revision: 4387 $
 $Author: Radek Suski $
 File location: components/com_sobipro/usr/templates/default2/common/alphamenu.xsl $
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
    <xsl:include href="alphaindex.xsl" />
    <xsl:template match="alphaMenu">
        <div class="row-fluid">
            <div class="span12 hidden-phone">
                <xsl:if test="count( fields/* )">
                    <div class=" alphalist">
                        <div class="btn-group">
                            <a class="btn dropdown-toggle btn-mini btn-sigsiu" data-toggle="dropdown" href="#">
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <xsl:for-each select="fields/*">
                                    <li>
                                        <a href="#" rel="{name()}" class="alpha-switch">
                                            <xsl:value-of select="." />
                                        </a>
                                    </li>
                                </xsl:for-each>
                            </ul>
                        </div>
                    </div>
                </xsl:if>
                <div id="alpha-index" class="alpha">
                    <xsl:apply-templates select="letters" />
                </div>
            </div>
        </div>
        <div class="clearfix"/>
    </xsl:template>
</xsl:stylesheet>
