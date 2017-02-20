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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:xls="http://www.w3.org/1999/XSL/Transform"
                exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

	<xsl:include href="../common/alphamenu.xsl"/>
	<xsl:include href="../common/topmenu.xsl"/>
	<xsl:include href="../common/navigation.xsl"/>
	<xsl:include href="../common/entries.xsl"/>
	<xsl:include href="../common/messages.xsl"/>
	<xsl:include href="../common/searchfields.xsl"/>

	<xsl:template match="/search">

		<!-- for proper work a container is needed, we assume that the component area is placed into a container by the template.
		If not, you need to add a container around the SobiPro output here -->
		<div class="spSearch">
			<xsl:call-template name="topMenu">
				<xsl:with-param name="searchbox">false</xsl:with-param>
				<xsl:with-param name="title">
					<xsl:value-of select="name"/>
				</xsl:with-param>
			</xsl:call-template>

			<xsl:apply-templates select="alphaMenu"/>
			<xsl:apply-templates select="messages"/>

			<xsl:if test="string-length(description) > 0">
				<div class="spSearchDesc">
					<xsl:value-of select="description" disable-output-escaping="yes"/>
				</div>
			</xsl:if>

			<xsl:variable name="sparam">
				<xsl:choose>
				<xsl:when test="//config/hidesearch/@value = 1">
					<xsl:value-of select="php:function( 'SobiPro::Request', 'sparam' )"/>
				</xsl:when>
				<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<div id="info-window">
				<div class="collapse {$sparam}" id="collapsearea">

					<div id="SPSearchForm" class="form-horizontal">
						<xsl:if test="/search/fields/searchbox">
							<div class="form-group">
								<label class="col-sm-3 control-label" for="SPSearchBox">
									<xsl:value-of select="/search/fields/searchbox/label"/>
								</label>
								<div class="col-sm-9 sp-search-controls">
									<input type="text" name="sp_search_for" value="{/search/fields/searchbox/data/input/@value}"
									       class="form-control input-large pull-left" id="SPSearchBox"
									       placeholder="{php:function( 'SobiPro::Txt', 'SH.SEARCH_FOR_BOX' )}"/>
									<xsl:if test="/search/fields/top_button/label">
										<button type="submit" class="btn btn-primary btn-sigsiu">
											<xls:text>&#160;</xls:text>
											<xsl:value-of select="/search/fields/top_button/label"/>
										</button>
									</xsl:if>
									<xsl:if test="count( /search/fields/* ) &gt; 3 and //config/extendedsearch/@value = 'show'">
										<button type="button" class="btn btn-default" name="SPExOptBt" id="SPExOptBt">
											<xls:text>&#160;</xls:text>
											<xsl:value-of select="php:function( 'SobiPro::Txt', 'EXTENDED_SEARCH' )"/>
										</button>
									</xsl:if>
								</div>
							</div>
							<xsl:if test="count( /search/fields/phrase/* )">
								<div class="form-group">
									<label class="col-sm-3 control-label" for="sp-search-phrases">
										<xsl:value-of select="/search/fields/phrase/label"/>
									</label>
									<div class="col-sm-9 sp-search-phrases">
										<div class="btn-group" data-toggle="buttons">
											<xsl:for-each select="/search/fields/phrase/data/*">
												<label class="sphrase btn btn-default">
													<xsl:if test="./input/@checked = 'checked'">
														<xsl:attribute name="class">sphrase btn btn-sigsiu active</xsl:attribute>
													</xsl:if>
													<xsl:attribute name="for">
														<xsl:value-of select="./@for"/>
													</xsl:attribute>
													<xsl:copy-of select="./input"/>
													<xsl:value-of select="."/>
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
									<xsl:call-template name="searchfield">
										<xsl:with-param name="fieldname" select="."/>
										<xsl:with-param name="position" select="position()"/>
									</xsl:call-template>
								</xsl:for-each>
							</div>
						</xsl:if>
					</div>
				</div>
			</div>

			<xsl:if test="//config/hidesearch/@value = 1">
				<xsl:choose>
					<xsl:when test="$sparam = 'in'">
						<button class="btn btn-info" id="info-window-btn" data-toggle="collapse" data-target="#collapsearea" aria-expanded="true"
						        aria-controls="collapsearea" data-visible="true" type="button">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'TP.SEARCH_HIDE' )"/>
						</button>
						<button type="submit" id="bottom_button" class="btn btn-primary btn-sigsiu">
							<xls:text>&#160;</xls:text>
							<xsl:value-of select="/search/fields/top_button/label"/>
						</button>
					</xsl:when>
					<xsl:otherwise>
						<button class="btn btn-info" id="info-window-btn" data-toggle="collapse" data-target="#collapsearea" aria-expanded="false"
						        aria-controls="collapsearea" data-visible="false" type="button">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'TP.SEARCH_REFINE' )"/>
						</button>
						<button type="submit" id="bottom_button" class="btn btn-primary btn-sigsiu hidden">
							<xls:text>&#160;</xls:text>
							<xsl:value-of select="/search/fields/top_button/label"/>
						</button>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>

			<xsl:if test="message">
				<div class="alert alert-info">
					<xsl:value-of select="message"/>
				</div>
			</xsl:if>
			<div class="spListing search">
				<xsl:call-template name="entriesLoop"/>
				<xsl:apply-templates select="navigation"/>

				<xsl:call-template name="bottomHook"/>
			</div>
		</div>

	</xsl:template>
</xsl:stylesheet>
