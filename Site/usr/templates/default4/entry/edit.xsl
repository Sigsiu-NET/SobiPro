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
	<xsl:include href="../common/topmenu.xsl"/>
	<xsl:include href="../common/messages.xsl"/>
	<xsl:include href="../common/editfields.xsl"/>

	<xsl:template match="/entry_form">
		<div class="spEntryEdit">
			<xsl:call-template name="topMenu">
				<xsl:with-param name="searchbox">false</xsl:with-param>
				<xsl:with-param name="title">
					<xsl:value-of select="name"/>
				</xsl:with-param>
			</xsl:call-template>
			<xsl:apply-templates select="messages"/>

			<!-- Snippet for the field highlighting feature. Do not remove or change it! -->
			<xsl:variable name="development">
				<xsl:if test="//development = 1">
					<xsl:text>development</xsl:text>
				</xsl:if>
			</xsl:variable>

			<xsl:variable name="form-orientation">
				<xsl:value-of select="//config/form-class/@value"/>
			</xsl:variable>
			<div class="form-{$form-orientation}">
				<xsl:for-each select="entry/fields/*">
					<xsl:call-template name="editfield">
						<xsl:with-param name="fieldname" select="."/>
					</xsl:call-template>
				</xsl:for-each>
			</div>
			<xsl:if test="//config/required-star/@value = 1">
				<div class="required-message">
					<sup>
						<span class="star">
							<i class="icon-star"></i>
						</span>
					</sup>
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'ENTRY_REQUIRED_MESSAGE' )"/>
				</div>
			</xsl:if>
			<div class="clearfix"/>
			<div class="pull-right">
				<button class="btn btn-default sobipro-cancel" type="button">
					<xsl:value-of select="entry/fields/cancel_button/data/button"/>
				</button>
				<button class="btn btn-primary btn-sigsiu sobipro-submit" type="button" data-loading-text="Loading...">
					<xsl:value-of select="entry/fields/save_button/data/input/@value"/>
				</button>
			</div>
			<div class="clearfix"/>

			<xsl:call-template name="bottomHook"/>
		</div>
		<div class="sk-fading-circle">
		  <div class="sk-circle1 sk-circle"></div>
		  <div class="sk-circle2 sk-circle"></div>
		  <div class="sk-circle3 sk-circle"></div>
		  <div class="sk-circle4 sk-circle"></div>
		  <div class="sk-circle5 sk-circle"></div>
		  <div class="sk-circle6 sk-circle"></div>
		  <div class="sk-circle7 sk-circle"></div>
		  <div class="sk-circle8 sk-circle"></div>
		  <div class="sk-circle9 sk-circle"></div>
		  <div class="sk-circle10 sk-circle"></div>
		  <div class="sk-circle11 sk-circle"></div>
		  <div class="sk-circle12 sk-circle"></div>
		</div>
		<input type="hidden" name="method" value="xhr"/>
		<input type="hidden" name="format" value="raw"/>
	</xsl:template>
</xsl:stylesheet>
