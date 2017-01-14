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
	<xsl:output method="xml" encoding="UTF-8"/>
	<xsl:template match="/section|/category">
		<feed xmlns="http://www.w3.org/2005/Atom">
			<title>
				<xsl:value-of select="name"/>
			</title>
			<id>
				<xsl:value-of select="php:function( 'SobiPro::Cfg', 'live_site' )"/>
			</id>
			<link rel="self">
				<xsl:attribute name="href">
					<xsl:variable name="rssUrl">{"sid":"<xsl:value-of select="id"/>","sptpl":"feeds.rss","out":"raw"}
					</xsl:variable>
					<xsl:variable name="feedurl">
						<xsl:value-of select="php:function( 'SobiPro::Cfg', 'live_site' )"/><xsl:value-of select="php:function( 'SobiPro::Url', $rssUrl )"/>
					</xsl:variable>
					<xsl:value-of select="php:function( 'Sobi::FixPath', $feedurl )"/>
				</xsl:attribute>
			</link>
			<updated>
				<xsl:variable name="updated_time">
					<xsl:value-of select="entries/entry[position() = 1]/updated_time"/>
				</xsl:variable>
				<xsl:value-of select="php:function( 'SobiPro::FormatDate', 'c', string($updated_time) )"/>
			</updated>
			<generator>SobiPro</generator>

			<xsl:if test="//config/showrss/@value = 'entries' or //config/showrss/@value = 'both'">
				<xsl:for-each select="entries/entry">
					<xsl:variable name="entryUrl">
						{"sid":"<xsl:value-of select="@id"/>","title":"<xsl:value-of select="@nid"/>"}
					</xsl:variable>
					<xsl:variable name="url">
						<xsl:value-of select="php:function( 'SobiPro::Url', $entryUrl, 0, 1, 1, 1 )"/>
					</xsl:variable>
					<entry>
						<title>
							<xsl:value-of select="name"/>
						</title>
						<author>
							<name>
								<xsl:choose>
									<xsl:when test="author != 0">
										<xsl:variable name="author">
											<xsl:value-of select="author"/>
										</xsl:variable>
										<xsl:value-of select="php:function('SobiPro::User', $author, 'name')"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>Guest author</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</name>
						</author>
						<link rel="alternate" href="{$url}"/>
						<id>
							<xsl:value-of select="$url"/>
						</id>
						<content type="html">
							<xsl:value-of select="fields/field_description/data"/>
						</content>
						<xsl:variable name="created_time">
							<xsl:value-of select="created_time"/>
						</xsl:variable>
						<published>
							<xsl:value-of select="php:function( 'SobiPro::FormatDate', 'c', string($created_time) )"/>
						</published>
						<updated>
							<xsl:variable name="updated_time">
								<xsl:value-of select="updated_time"/>
							</xsl:variable>
							<xsl:value-of select="php:function( 'SobiPro::FormatDate', 'c', string($updated_time) )"/>
						</updated>
					</entry>
				</xsl:for-each>
			</xsl:if>

			<xsl:if test="//config/showrss/@value = 'categories' or //config/showrss/@value = 'both'">
				<xsl:for-each select="categories/category">
					<xsl:variable name="entryUrl">
						{"sid":"<xsl:value-of select="@id"/>","title":"<xsl:value-of select="@nid"/>"}
					</xsl:variable>
					<xsl:variable name="url">
						<xsl:value-of select="php:function( 'SobiPro::Url', $entryUrl, 0, 1, 1, 1 )"/>
					</xsl:variable>
					<entry>
						<title>
							<xsl:value-of select="name"/>
						</title>
						<author>
							<name>
								<xsl:choose>
									<xsl:when test="author != 0">
										<xsl:variable name="author">
											<xsl:value-of select="author"/>
										</xsl:variable>
										<xsl:value-of select="php:function('SobiPro::User', $author, 'name')"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>Guest author</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</name>
						</author>
						<link rel="alternate" href="{$url}"/>
						<id>
							<xsl:value-of select="$url"/>
						</id>
						<summary type="text">
							<xsl:value-of select="introtext"/>
						</summary>
						<!--
											<content type="html">
												<xsl:value-of select="fields/field_description/data" disable-output-escaping="no" />
											</content>
						-->
						<published>
							<xsl:variable name="created_time">
								<xsl:value-of select="created_time"/>
							</xsl:variable>
							<xsl:value-of select="php:function( 'SobiPro::FormatDate', 'c', string($created_time) )"/>
						</published>
						<updated>
							<xsl:variable name="updated_time">
								<xsl:value-of select="updated_time"/>
							</xsl:variable>
							<xsl:value-of select="php:function( 'SobiPro::FormatDate', 'c', string($updated_time) )"/>
						</updated>
					</entry>
				</xsl:for-each>
			</xsl:if>
		</feed>
	</xsl:template>
</xsl:stylesheet>

