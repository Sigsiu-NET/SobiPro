<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:template name="manage">
		<xsl:if test="entry/approve_url or entry/edit_url or entry/publish_url or entry/delete_url">
			<div class="btn-group spManage pull-left">
				<a class="btn btn-sigsiu dropdown-toggle btn-small" data-toggle="dropdown" href="#">
					<i class="icon-edit"></i>
				</a>
				<ul class="dropdown-menu">
					<xsl:if test="entry/publish_url">
						<li>
							<a href="{entry/publish_url}">
								<xsl:choose>
									<xsl:when test="entry/state = 'published'">
										<xsl:value-of select="php:function( 'SobiPro::Txt', 'ENTRY_MANAGE_DISABLE' )" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="php:function( 'SobiPro::Txt', 'ENTRY_MANAGE_ENABLE' )" />
									</xsl:otherwise>
								</xsl:choose>
							</a>
						</li>
					</xsl:if>
					<xsl:if test="entry/approve_url and entry/approved = 0">
						<li>
							<a href="{entry/approve_url}">
								<xsl:value-of select="php:function( 'SobiPro::Txt', 'ENTRY_MANAGE_APPROVE' )" />
							</a>
						</li>
					</xsl:if>
					<xsl:if test="entry/edit_url">
						<li>
							<a href="{entry/edit_url}">
								<xsl:value-of select="php:function( 'SobiPro::Txt', 'ENTRY_MANAGE_EDIT' )" />
							</a>
						</li>
					</xsl:if>
					<xsl:if test="entry/delete_url">
						<li>
							<a href="{entry/delete_url}" id="spDeleteEntry">
								<xsl:value-of select="php:function( 'SobiPro::Txt', 'ENTRY_MANAGE_DELETE' )" />
							</a>
						</li>
					</xsl:if>
				</ul>
			</div>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="status">
		<xsl:param name="entry"/>
		<xsl:if test="$entry/approved = 0">
			<a class="entry-status" href="#" data-toggle="popover" data-content="{php:function( 'SobiPro::Txt', 'ENTRY_STATUS_UNAPPROVED' )}" title="" >
				<i class="icon-thumbs-down-alt" />
			</a>
		</xsl:if>
		<xsl:if test="$entry/state = 'unpublished'">
			<a class="entry-status" href="#" data-toggle="popover" data-content="{php:function( 'SobiPro::Txt', 'ENTRY_STATUS_UNPUBLISHED' )}" title="" >
				<i class="icon-remove-sign" />
			</a>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
