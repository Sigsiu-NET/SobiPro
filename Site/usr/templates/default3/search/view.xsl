<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

	<xsl:include href="../common/alphamenu.xsl"/>
	<xsl:include href="../common/topmenu.xsl"/>
	<xsl:include href="../common/navigation.xsl"/>
	<xsl:include href="../common/entries.xsl"/>
	<xsl:include href="../common/messages.xsl"/>
	<xsl:include href="../common/searchfields.xsl"/>

	<xsl:template match="/search">
		<div class="spSearch">
			<xsl:call-template name="topMenu">
				<xsl:with-param name="searchbox">false</xsl:with-param>
			</xsl:call-template>

			<xsl:apply-templates select="alphaMenu"/>
			<xsl:apply-templates select="messages"/>

			<div id="SPSearchForm" class="form-horizontal control-group">
				<xsl:if test="/search/fields/searchbox">
					<div class="control-group">
						<label class="control-label" for="SPSearchBox">
							<xsl:value-of select="/search/fields/searchbox/label"/>
						</label>
						<div class="controls sp-search-controls">
							<div class="span9">
								<input type="text" name="sp_search_for" value="{/search/fields/searchbox/data/input/@value}" class="input-medium"
								       id="SPSearchBox"/>
								<xsl:if test="/search/fields/top_button/label">
									<button type="submit" class="btn btn-primary btn-sigsiu">
										<xsl:value-of select="/search/fields/top_button/label"/>
									</button>
								</xsl:if>
								<xsl:if test="count( /search/fields/* ) &gt; 3 and //config/extendedsearch/@value = 'show'">
									<button type="button" class="btn" name="SPExOptBt" id="SPExOptBt">
										<xsl:value-of select="php:function( 'SobiPro::Txt', 'EXTENDED_SEARCH' )"/>
									</button>
								</xsl:if>
							</div>
						</div>
					</div>
					<xsl:if test="count( /search/fields/phrase/* )">
						<div class="control-group">
							<label class="control-label" for="sp-search-phrases">
								<xsl:value-of select="/search/fields/phrase/label"/>
							</label>
							<div class="controls sp-search-phrases">
								<div class="span9">
									<div class="btn-group" data-toggle="buttons-radio">
										<xsl:for-each select="/search/fields/phrase/data/*">
											<button type="button" class="btn btn-default spsearchphrase" name="{./input/@name}" value="{./input/@value}" checked="checked">
												<xsl:if test="./input/@checked = 'checked'">
													<xsl:attribute name="class">btn btn-success spsearchphrase active</xsl:attribute>
												</xsl:if>
												<xsl:value-of select="."/>
											</button>
										</xsl:for-each>
									</div>
									<input type="hidden" name="spsearchphrase" id="spsearchphrase" value=""/>
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

						</xsl:for-each>
					</div>
				</xsl:if>
			</div>
			<xsl:if test="message">
				<div class="alert alert-info">
					<xsl:value-of select="message"/>
				</div>
			</xsl:if>
			<xsl:call-template name="entriesLoop"/>
			<xsl:apply-templates select="navigation"/>

			<xsl:call-template name="bottomHook"/>
		</div>

	</xsl:template>
</xsl:stylesheet>
