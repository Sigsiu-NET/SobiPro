<?xml version="1.0" encoding="UTF-8"?>
<!--
 @version: $Id: categories.xsl 4387 2015-02-19 12:24:35Z Radek Suski $
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
 File location: components/com_sobipro/usr/templates/default2/common/categories.xsl $
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:include href="category.xsl" />
	<xsl:template name="categoriesLoop">

		<xsl:variable name="catsInLine">
			<xsl:value-of select="categories_in_line" />
		</xsl:variable>

		<xsl:variable name="cellClass">
			<xsl:value-of select="floor( 12 div $catsInLine )" />
		</xsl:variable>

		<xsl:variable name="catsCount">
			<xsl:value-of select="count( categories/category )" />
		</xsl:variable>

		<xsl:comment> categories loop - start </xsl:comment>
        <div class="category-container">
                <xsl:for-each select="categories/category">
                    <xsl:if test="($catsInLine > 1 and (position() = 1 or (position() mod $catsInLine) = 1)) or $catsInLine = 1">
                        <!-- opening the "table" row -->
                        <xsl:text disable-output-escaping="yes">&lt;div class="row-fluid" &gt;</xsl:text>
                    </xsl:if>
                    <div class="span{$cellClass} thumbcat">
                        <xsl:call-template name="category" />
                    </div>
                    <xsl:if test="($catsInLine > 1 and ((position() mod $catsInLine) = 0 or position() = $catsCount))  or $catsInLine = 1">
                        <!-- closing the "table" row -->
                        <xsl:text disable-output-escaping="yes">&lt;/div&gt;</xsl:text>
                    </xsl:if>
                </xsl:for-each>
        </div>
		<xsl:comment> categories loop - end </xsl:comment>
	</xsl:template>
</xsl:stylesheet>
