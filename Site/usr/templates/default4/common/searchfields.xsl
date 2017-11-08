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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl"
                exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

	<xsl:template name="searchfield">
		<xsl:param name="fieldname"/>
		<xsl:param name="position"/>

		<xsl:if test="$position &gt; 3">
			<div class="form-group {$fieldname/@css-search}">
				<xsl:if test="//development = 1">
					<xsl:attribute name="title">
						<xsl:value-of select="name($fieldname)"/><xsl:text> (</xsl:text><xsl:value-of select="$fieldname/@type"/><xsl:text>)</xsl:text>
					</xsl:attribute>
				</xsl:if>
				<label class="col-sm-3 control-label" for="{name($fieldname)}">
					<xsl:value-of select="label"/>
				</label>
				<xsl:variable name="colwidth">
					<xsl:choose>
						<xsl:when test="string-length( $fieldname/@width )">
							<xsl:value-of select="$fieldname/@width"/>
						</xsl:when>
						<xsl:otherwise>9</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<div class="col-sm-{$colwidth}">
					<div>
						<xsl:choose>
							<xsl:when test="string-length( $fieldname/@suffix )">
								<xsl:attribute name="class">input-group</xsl:attribute>
								<xsl:choose>
									<xsl:when test="$fieldname/data/@escaped">
										<xsl:value-of select="$fieldname/data" disable-output-escaping="yes"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:copy-of select="$fieldname/data/*"/>
									</xsl:otherwise>
								</xsl:choose>
								<div class="input-group-addon">
									<xsl:value-of select="$fieldname/@suffix"/>
								</div>
							</xsl:when>
							<xsl:otherwise>
								<xsl:choose>
									<xsl:when test="$fieldname/data/@escaped">
										<xsl:value-of select="$fieldname/data" disable-output-escaping="yes"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:copy-of select="$fieldname/data/*"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</div>
			</div>
		</xsl:if>

	</xsl:template>

</xsl:stylesheet>
