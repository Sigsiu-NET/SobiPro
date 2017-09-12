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

	<xsl:template name="showfield">
		<xsl:param name="fieldname"/>
		<xsl:param name="view"/>
		<xsl:choose>
			<xsl:when test="count($fieldname/data/*) or string-length($fieldname/data)">
				<div>
					<xsl:if test="string-length($fieldname/@css-view)">
						<xsl:attribute name="class">
							<xsl:value-of select="$fieldname/@css-view"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="//development = 1">
						<xsl:attribute name="title">
							<xsl:value-of select="name($fieldname)"/><xsl:text> (</xsl:text><xsl:value-of select="$fieldname/@type"/><xsl:text>)</xsl:text>
						</xsl:attribute>
					</xsl:if>
					<xsl:choose>
						<xsl:when test="count($fieldname/data/*)">  <!-- complex data -->
							<xsl:if test="string-length($fieldname/@itemprop)"> <!-- itemprop attached to div container -->
								<xsl:attribute name="itemprop">
									<xsl:value-of select="$fieldname/@itemprop"/>
								</xsl:attribute>
							</xsl:if>
							<xsl:if test="$fieldname/label/@show = 1"> <!-- field label -->
								<span class="spLabel">
									<xsl:value-of select="$fieldname/label"/><xsl:text>: </xsl:text>
								</span>
							</xsl:if>
							<xsl:choose>
								<xsl:when test="contains($fieldname/@css-class,'primary') and $view = 'vcard' and $fieldname/@type = 'image'">
									<a href="{../../url}">
										<xsl:copy-of select="$fieldname/data/*"/>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy-of select="$fieldname/data/*"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise> <!-- no complex data -->
							<xsl:if test="string-length($fieldname/data)">
								<xsl:choose>
									<xsl:when test="contains($fieldname/@css-class,'spClassText')"> <!-- is textarea -->
										<xsl:if test="string-length($fieldname/@itemprop)"> <!-- itemprop attached to div container -->
											<xsl:attribute name="itemprop">
												<xsl:value-of select="$fieldname/@itemprop"/>
											</xsl:attribute>
										</xsl:if>
										<xsl:if test="$fieldname/label/@show = 1"> <!-- field label -->
											<span class="spLabel">
												<xsl:value-of select="$fieldname/label"/><xsl:text>: </xsl:text>
											</span>
										</xsl:if>
										<xsl:choose>
											<xsl:when
													test="contains($fieldname/@css-class,'shorten') and ($view = 'vcard') and //config/textlength/@value != 'no'">
												<xsl:value-of select="substring ($fieldname/data,1,//config/textlength/@value)"
												              disable-output-escaping="yes"/>...
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="$fieldname/data" disable-output-escaping="yes"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:when>
									<xsl:otherwise> <!-- no textarea -->
										<xsl:if test="$fieldname/label/@show = 1"> <!-- field label -->
											<span class="spLabel">
												<xsl:value-of select="$fieldname/label"/><xsl:text>: </xsl:text>
											</span>
										</xsl:if>
										<span>  <!-- add surroundig span -->
											<xsl:if test="string-length($fieldname/@itemprop)"> <!-- attach itemprop to span -->
												<xsl:attribute name="itemprop">
													<xsl:value-of select="$fieldname/@itemprop"/>
												</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="$fieldname/data" disable-output-escaping="yes"/>
										</span>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:if>
						</xsl:otherwise>
					</xsl:choose>

					<xsl:if test="string-length($fieldname/@suffix)"> <!-- suffix -->
						<xsl:text> </xsl:text>
						<xsl:choose>
							<xsl:when test="$view = 'dv'">
								<span class="spDetailSuffix">
									<xsl:value-of select="$fieldname/@suffix"/>
								</span>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$fieldname/@suffix"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="$view != 'category'">
					<xsl:if test="$fieldname/@type = 'image'">
						<xsl:if test="//config/noimage/@value = 1">
							<div class="spNoImageContainer {$fieldname/@css-view} right">
								<xsl:if test="//development = 1">
									<xsl:attribute name="title">
										<xsl:value-of select="name()"/><xsl:text> (</xsl:text><xsl:value-of select="$fieldname/@type"/><xsl:text>)</xsl:text>
									</xsl:attribute>
								</xsl:if>
								<div class="spNoImage">
									<i class="icon icon-ban-circle"></i>
								</div>
							</div>
						</xsl:if>
					</xsl:if>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="development">
		<xsl:param name="fieldname"/>
		<xsl:if test="//development = 1">
			<xsl:attribute name="title">
				<xsl:value-of select="name($fieldname)"/><xsl:text> (</xsl:text><xsl:value-of select="$fieldname/@type"/><xsl:text>)</xsl:text>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
