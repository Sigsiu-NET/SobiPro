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
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:include href="category.xsl" />
	<xsl:template name="categoriesLoop">

		<xsl:variable name="catsInLine">
			<xsl:value-of select="categories_in_line" />
		</xsl:variable>

		<xsl:variable name="cellClass">
			<xsl:value-of select="floor( 9 div $catsInLine )" />
		</xsl:variable>

		<div class="span12">
			<xsl:for-each select="categories/category">
				<xsl:if test="$catsInLine > 1 and ( position() = 1 or ( position() mod $catsInLine ) = 1 )">
					<!-- opening the "table" row -->
					<xsl:text disable-output-escaping="yes">&lt;div class="row" &gt;</xsl:text>
				</xsl:if>
				<div class="span{$cellClass} thumbnail" style="border-style:solid">
					<xsl:call-template name="category" />
				</div>
				<xsl:if test="$catsInLine > 1 and ( ( position() mod $catsInLine ) = 0 or position() = $catsInLine )">
					<!-- closing the "table" row -->
					<xsl:text disable-output-escaping="yes">&lt;/div&gt;</xsl:text>
				</xsl:if>
			</xsl:for-each>
		</div>
	</xsl:template>
</xsl:stylesheet>
