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

	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/messages.xsl"/>
	<xsl:include href="list.xsl" />
	<xsl:template match="/payment_details">
		<div class="SPPayment">
			<div>
				<xsl:call-template name="topMenu">
					<xsl:with-param name="searchbox">true</xsl:with-param>
				</xsl:call-template>
			</div>
			<xsl:apply-templates select="messages"/>
			<xsl:call-template name="paymentTable" />
			<p class="text-info">
				<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_SELECT_PAYMENT' )" />:
			</p>
			<div class="payment-details row-fluid">
				<xsl:for-each select="payment_methods/*">
					<div class="thumbnail span6 hidden-phone">
						<xsl:value-of select="@title" />
						<xsl:choose>
							<xsl:when test="@escaped">
								<xsl:value-of select="." disable-output-escaping="yes" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:choose>
									<xsl:when test="count(./*)">
										<xsl:copy-of select="./*" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="." disable-output-escaping="yes" />
									</xsl:otherwise>
								</xsl:choose>
							</xsl:otherwise>
						</xsl:choose>
					</div>
					<div class="thumbnail span12 visible-phone">
						<xsl:value-of select="@title" />
						<xsl:choose>
							<xsl:when test="@escaped">
								<xsl:value-of select="." disable-output-escaping="yes" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:choose>
									<xsl:when test="count(./*)">
										<xsl:copy-of select="./*" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="." disable-output-escaping="yes" />
									</xsl:otherwise>
								</xsl:choose>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</xsl:for-each>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
