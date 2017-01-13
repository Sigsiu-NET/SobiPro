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

		<xsl:template name="searchfield">
		<xsl:param name="fieldname"/>
			<xsl:param name="position"/>

			<xsl:if test="$position &gt; 3">
				<div class="control-group {$fieldname/@css-search}">
					<label class="control-label" for="{name($fieldname)}">
						<xsl:value-of select="label"/>
					</label>
					<div class="controls">
						<xsl:variable name="colwidth">
							<xsl:choose>
								<xsl:when test="string-length( $fieldname/@width )">
									<xsl:value-of select="$fieldname/@width"/>
								</xsl:when>
								<xsl:otherwise>9</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<div class="span{$colwidth}">
							<xsl:if test="string-length( $fieldname/@suffix )">
								<xsl:attribute name="class">input-append</xsl:attribute>
							</xsl:if>
							<xsl:copy-of select="data/*"/>
							<xsl:if test="string-length( $fieldname/@suffix )">
								<span class="add-on">
									<xsl:value-of select="$fieldname/@suffix"/>
								</span>
							</xsl:if>
						</div>
					</div>
				</div>
			</xsl:if>

	</xsl:template>
</xsl:stylesheet>
