<?xml version="1.0" encoding="UTF-8"?><!--
 SobiPro Template SobiRestara
 Authors: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Copyright (C) 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 Released under Sigsiu.NET Template License V1
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" encoding="UTF-8" />
	<xsl:template match="/section|/category">
		<feed xmlns="http://www.w3.org/2005/Atom">
			<title>
				<xsl:value-of select="name" />
			</title>
			<id>
				<xsl:value-of select="php:function( 'SobiPro::Cfg', 'live_site' )" />
			</id>
			<link rel="self">
				<xsl:attribute name="href">
					<xsl:variable name="rssUrl">{"sid":"<xsl:value-of select="id" />","sptpl":"feeds.rss","out":"raw"}
					</xsl:variable>
					<xsl:variable name="feedurl">
						<xsl:value-of select="php:function( 'SobiPro::Cfg', 'live_site' )" /><xsl:value-of select="php:function( 'SobiPro::Url', $rssUrl )" />
					</xsl:variable>
					<xsl:value-of select="php:function( 'Sobi::FixPath', $feedurl )" />
				</xsl:attribute>
			</link>
			<updated>
				<xsl:variable name="updated_time">
					<xsl:value-of select="entries/entry[position() = 1]/updated_time" />
				</xsl:variable>
				<xsl:value-of select="php:function( 'SobiPro::FormatDate', 'c', string($updated_time) )" />
			</updated>
			<xsl:for-each select="entries/entry">
				<xsl:variable name="entryUrl">
					{"sid":"<xsl:value-of select="@id" />","title":"<xsl:value-of select="@nid" />"}
				</xsl:variable>
				<xsl:variable name="url">
					<xsl:value-of select="php:function( 'SobiPro::Url', $entryUrl, 0, 1, 1, 1 )" />
				</xsl:variable>
				<entry>
					<title>
						<xsl:value-of select="name" />
					</title>
					<author>
						<name>
							<xsl:choose>
								<xsl:when test="author != 0">
									<xsl:variable name="author">
										<xsl:value-of select="author" />
									</xsl:variable>
									<xsl:value-of select="php:function('SobiPro::User', $author, 'name')" />
								</xsl:when>
								<xsl:otherwise>
									Guest author
								</xsl:otherwise>
							</xsl:choose>
						</name>
					</author>
					<link rel="alternate" href="{$url}"/>
					<id>
						<xsl:value-of select="$url" />
					</id>
					<content type="html">
						<xsl:value-of select="fields/field_description/data" />
					</content>
					<updated>
						<xsl:variable name="updated_time">
							<xsl:value-of select="updated_time" />
						</xsl:variable>
						<xsl:value-of select="php:function( 'SobiPro::FormatDate', 'c', string($updated_time) )" />
					</updated>
				</entry>
			</xsl:for-each>
		</feed>
	</xsl:template>
</xsl:stylesheet>

