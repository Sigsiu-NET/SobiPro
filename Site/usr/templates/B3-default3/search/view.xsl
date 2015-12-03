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
	<xsl:include href="../common/alphamenu.xsl" />
	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/navigation.xsl" />
	<xsl:include href="../common/entries.xsl" />
	<xsl:include href="../common/messages.xsl"/>

	<xsl:template match="/search">

		<!-- for proper work a container is needed, we assume that the component area is placed into a container by the template.
		If not, you need to add a container around the SobiPro output here -->
		<div class="spSearch">
			<div>
				<xsl:call-template name="topMenu">
					<xsl:with-param name="searchbox">false</xsl:with-param>
				</xsl:call-template>
				<xsl:apply-templates select="alphaMenu" />
			</div>
			<xsl:apply-templates select="messages"/>
			<div id="SPSearchForm" class="form-horizontal">
				<xsl:if test="/search/fields/searchbox">
					<div class="form-group">
						<label class="col-sm-3 control-label" for="SPSearchBox">
							<xsl:value-of select="/search/fields/searchbox/label" />
						</label>
						<div class="col-sm-9 sp-search-controls">
							<input type="text" name="sp_search_for" value="{/search/fields/searchbox/data/input/@value}" class="form-control input-large pull-left" id="SPSearchBox" />
							<xsl:if test="/search/fields/top_button/label">
								<button type="submit" class="btn btn-primary btn-sigsiu">
									<xsl:value-of select="/search/fields/top_button/label" />
								</button>
							</xsl:if>
							<xsl:if test="count( /search/fields/* ) &gt; 3 and //config/extendedsearch/@value = 'show'">
								<button type="button" class="btn btn-default" name="SPExOptBt" id="SPExOptBt">
									<xsl:value-of select="php:function( 'SobiPro::Txt', 'EXTENDED_SEARCH' )" />
								</button>
							</xsl:if>
						</div>
					</div>
					<xsl:if test="count( /search/fields/phrase/* )">
						<div class="form-group">
							<label class="col-sm-3 control-label" for="sp-search-phrases">
								<xsl:value-of select="/search/fields/phrase/label" />
							</label>
							<div class="col-sm-9 sp-search-phrases">
								<div class="btn-group" data-toggle="buttons">
									<xsl:for-each select="/search/fields/phrase/data/*">
										<label class="btn btn-default">
											<xsl:if test="./input/@checked = 'checked'">
												<xsl:attribute name="class">btn btn-default active</xsl:attribute>
											</xsl:if>
											<xsl:copy-of select="./input" />
											<xsl:value-of select="./label" />
										</label>
									</xsl:for-each>
								</div>
							</div>
						</div>
					</xsl:if>
				</xsl:if>
				<xsl:if test="count( /search/fields/* ) &gt; 3">
					<div>
						<xsl:if test="//config/extendedsearch/@value = 'show'">
							<xsl:attribute name="id">SPExtSearch</xsl:attribute>
						</xsl:if>
						<xsl:for-each select="fields/*">
							<xsl:if test="position() &gt; 3">
								<div class="form-group {@css-search}">
									<label class="col-sm-3 control-label" for="{name(.)}">
										<xsl:value-of select="label" />
									</label>
									<xsl:variable name="colwidth">
										<xsl:choose>
											<xsl:when test="string-length( @width )">
												<xsl:value-of select="@width" />
											</xsl:when>
											<xsl:otherwise>9</xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									<div class="col-sm-{$colwidth}">
										<div>
											<xsl:choose>
												<xsl:when test="string-length( @suffix )">
													<xsl:attribute name="class">input-group</xsl:attribute>
													<xsl:choose>
														<xsl:when test="data/@escaped">
															<xsl:value-of select="data" disable-output-escaping="yes" />
														</xsl:when>
														<xsl:otherwise>
															<xsl:copy-of select="data/*" />
														</xsl:otherwise>
													</xsl:choose>
													<div class="input-group-addon">
														<xsl:value-of select="@suffix" />
													</div>
												</xsl:when>
												<xsl:otherwise>
													<xsl:choose>
														<xsl:when test="data/@escaped">
															<xsl:value-of select="data" disable-output-escaping="yes" />
														</xsl:when>
														<xsl:otherwise>
															<xsl:copy-of select="data/*" />
														</xsl:otherwise>
													</xsl:choose>
												</xsl:otherwise>
											</xsl:choose>
										</div>
									</div>
								</div>
							</xsl:if>
						</xsl:for-each>
					</div>
				</xsl:if>
			</div>
			<xsl:if test="message">
				<div class="alert alert-info">
					<xsl:value-of select="message" />
				</div>
			</xsl:if>
			<xsl:call-template name="entriesLoop" />
			<xsl:apply-templates select="navigation" />
		</div>
	</xsl:template>
</xsl:stylesheet>
