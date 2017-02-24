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
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:include href="font.xsl"/>

	<xsl:template name="topMenu">
		<xsl:param name="searchbox"/>
		<xsl:param name="title"/>

		<!-- load the fonts needed -->
		<xsl:call-template name="font"/>

		<!-- Show the Directory name and description -->
		<div class="pageheader lead">
			<xsl:value-of select="section"/>
			<xsl:if test="string-length($title) > 0">
				<xsl:text> - </xsl:text><xsl:value-of select="$title"/>
			</xsl:if>
		</div>

		<!-- if top menu is switched on in SobiPro settings -->
		<xsl:if test="count(//menu/*)">

			<xsl:choose>
				<xsl:when test="//config/navigationlinks/@value = 'topmenu'">
					<xsl:variable name="currentUrl">
						<xsl:value-of select="php:function( 'SobiPro::Url', 'current' )"/>
					</xsl:variable>
					<div class="navbar topmenu standard">
						<div class="navbar-inner">
							<div class="container-fluid">
								<ul class="nav">
									<xsl:if test="//menu/front">
										<li>
											<a href="{//menu/front/@url}">
												<xsl:choose>
													<xsl:when test="//menu/search">
													<xsl:if test="$currentUrl != //menu/add/@url and not(contains($currentUrl, //menu/search/@url))">
															<xsl:attribute name="class">active</xsl:attribute>
														</xsl:if>
													</xsl:when>
													<xsl:otherwise>
														<xsl:if test="$currentUrl != //menu/add/@url">
															<xsl:attribute name="class">active</xsl:attribute>
														</xsl:if>
													</xsl:otherwise>
												</xsl:choose>
												<i class="icon-th-list"></i>
												<xsl:text> </xsl:text>
												<xsl:value-of select="//menu/front"/>
											</a>
										</li>
									</xsl:if>
								<xsl:choose>
									<xsl:when test="//menu/add">
										<li>
											<a href="{//menu/add/@url}">
												<xsl:if test="$currentUrl = //menu/add/@url">
													<xsl:attribute name="class">active</xsl:attribute>
												</xsl:if>
												<i class="icon-plus-sign"></i>
												<xsl:text> </xsl:text>
												<xsl:value-of select="//menu/add"/>
											</a>
										</li>
									</xsl:when>
									<xsl:otherwise>
										<xsl:if test="string-length(//config/redirectlogin/@value) > 0">
											<li>
												<a href="{//config/redirectlogin/@value}">
													<i class="icon-plus-sign"></i>
													<xsl:text> </xsl:text>
													<xsl:value-of select="php:function( 'SobiPro::Txt', 'MN.ADD_ENTRY' )" />
												</a>
											</li>
									</xsl:if>
									</xsl:otherwise>
								</xsl:choose>
									<xsl:if test="//menu/search">
										<li>
										<a href="{//menu/search/@url}/?sparam=in">
											<xsl:if test="contains($currentUrl, //menu/search/@url)">
													<xsl:attribute name="class">active</xsl:attribute>
												</xsl:if>
												<i class="icon-search"></i>
												<xsl:text> </xsl:text>
												<xsl:value-of select="//menu/search"/>
											</a>
										</li>
									</xsl:if>
									<li>
										<xsl:copy-of select="/*/collection/button/*"/>
									</li>
								</ul>
								<xsl:if test="//menu/search and $searchbox = 'true'">
									<div class="nav-collapse">
										<form class="navbar-search pull-right">
											<input type="text" name="sp_search_for" autocomplete="off" class="search-query"
											       placeholder="{php:function( 'SobiPro::Txt', 'SH.SEARCH_FOR_BOX' )}"/>
											<input type="hidden" name="task" value="search.search"/>
											<input type="hidden" name="option" value="com_sobipro"/>
											<input type="hidden" name="sid" value="{//@id}"/>
										</form>
									</div>
								</xsl:if>
							</div>
						</div>
					</div>
				</xsl:when>
				<xsl:when test="//config/navigationlinks/@value = 'linkbar'">
					<xsl:call-template name="linkbar"/>
				</xsl:when>
				<xsl:when test="//config/navigationlinks/@value = 'buttonbar'">
					<xsl:call-template name="buttonbar"/>
				</xsl:when>
			</xsl:choose>
		</xsl:if>

	</xsl:template>

	<xsl:template name="linkbar">
		<div class="topmenu linkbar alert alert-navigationlinks">
			<xsl:variable name="currentUrl">
				<xsl:value-of select="php:function( 'SobiPro::Url', 'current' )"/>
			</xsl:variable>
			<ul class="spNavigationLinks">
				<xsl:if test="//menu/front">
					<li>
						<a href="{//menu/front/@url}">
							<xsl:choose>
								<xsl:when test="//menu/search">
									<xsl:if test="$currentUrl != //menu/add/@url and $currentUrl != //menu/search/@url">
										<xsl:attribute name="class">active</xsl:attribute>
									</xsl:if>
								</xsl:when>
								<xsl:otherwise>
									<xsl:if test="$currentUrl != //menu/add/@url">
										<xsl:attribute name="class">active</xsl:attribute>
									</xsl:if>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:value-of select="//menu/front"/>
						</a>
					</li>
				</xsl:if>
				<xsl:choose>
					<xsl:when test="//menu/add">
					<li>
						<a href="{//menu/add/@url}">
							<xsl:if test="$currentUrl = //menu/add/@url">
								<xsl:attribute name="class">active</xsl:attribute>
							</xsl:if>
								<xsl:text> </xsl:text>
							<xsl:value-of select="//menu/add"/>
						</a>
					</li>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="string-length(//config/redirectlogin/@value) > 0">
							<li>
								<a href="{//config/redirectlogin/@value}">
									<xsl:text> </xsl:text>
									<xsl:value-of select="php:function( 'SobiPro::Txt', 'MN.ADD_ENTRY' )" />
								</a>
							</li>
				</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:if test="//menu/search">
					<li>
						<a href="{//menu/search/@url}/?sparam=in">
							<xsl:if test="$currentUrl = //menu/search/@url">
								<xsl:attribute name="class">active</xsl:attribute>
							</xsl:if>
							<xsl:value-of select="//menu/search"/>
						</a>
					</li>
				</xsl:if>
				<!--<li>-->
				<!--<xsl:copy-of select="/*/collection/button/*"/>-->
				<!--</li>-->
			</ul>
		</div>
	</xsl:template>

	<xsl:template name="buttonbar">
		<!--<xsl:variable name="currentUrl">-->
			<!--<xsl:value-of select="php:function( 'SobiPro::Url', 'current' )"/>-->
		<!--</xsl:variable>-->

		<div class="topmenu buttonbar" role="navigation">
			<div class="menu">
				<xsl:if test="//menu/front">
					<a href="{//menu/front/@url}" class="btn btn-sigsiu">
						<xsl:text>Showcase Directory</xsl:text>
					</a>
				</xsl:if>
			</div>
			<div class="add {//config/buttonpos/@value}">
				<xsl:choose>
					<xsl:when test="//menu/add">
						<a href="{//menu/add/@url}" class="btn btn-success">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'MN.ADD_ENTRY' )"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="string-length(//config/redirectlogin/@value) > 0">
							<a href="{//config/redirectlogin/@value}" class="btn btn-success">
								<xsl:value-of select="php:function( 'SobiPro::Txt', 'MN.ADD_ENTRY' )"/>
							</a>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</div>
			<div class="search {//config/buttonpos/@value}">
				<xsl:if test="//menu/search">
					<a href="{//menu/search/@url}/?sparam=in" class="btn btn-sigsiu">
						<xsl:value-of select="//menu/search"/>
					</a>
				</xsl:if>
			</div>
			<div class="clearfix"></div>
		</div>
	</xsl:template>

	<xsl:template name="bottomHook">
	</xsl:template>

</xsl:stylesheet>

