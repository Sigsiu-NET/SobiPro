<?xml version="1.0" encoding="UTF-8"?>
<!--
 @version: $Id: edit.xsl 4387 2015-02-19 12:24:35Z Radek Suski $
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: http://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 $Date: 2015-02-19 13:24:35 +0100 (Thu, 19 Feb 2015) $
 $Revision: 4387 $
 $Author: Radek Suski $
 File location: components/com_sobipro/usr/templates/default2/entry/edit.xsl $
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/messages.xsl" />

	<xsl:template match="/entry_form">
		<div class="SPEntryEdit">
			<div>
				<xsl:call-template name="topMenu">
					<xsl:with-param name="searchbox">false</xsl:with-param>
				</xsl:call-template>
			</div>
			<xsl:apply-templates select="messages" />
			<div class="form-horizontal">
				<xsl:for-each select="entry/fields/*">
					<xsl:if test="( name() != 'save_button' ) and ( name() != 'cancel_button' )">
						<xsl:variable name="fieldId" select="name(.)" />
						<xsl:if test="string-length( fee )">
							<div class="control-group">
								<div class="control-label">
									<input name="{$fieldId}Payment" id="{$fieldId}-payment" value="" type="checkbox" class="payment-box" />
								</div>
								<div class="alert spAlert controls">
									<xsl:value-of select="fee_msg" /><xsl:text> </xsl:text>
									<xsl:value-of select="php:function( 'SobiPro::Txt', 'TP.PAYMENT_ADD' )" />
								</div>
							</div>
						</xsl:if>
						<div class="control-group" id="{$fieldId}-container">
							<label class="control-label" for="{$fieldId}-input-container">
								<xsl:choose>
									<xsl:when test="string-length( description )">
										<a href="#" rel="popover" data-placement="top" data-content="{description}" data-original-title="{label}">
											<xsl:value-of select="label" />
										</a>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="label" />
									</xsl:otherwise>
								</xsl:choose>
							</label>
							<div class="controls" id="{$fieldId}-input-container">
								<div>
									<xsl:if test="string-length( @suffix )">
										<xsl:attribute name="class">input-append</xsl:attribute>
									</xsl:if>
									<xsl:choose>
										<xsl:when test="data/@escaped">
											<xsl:value-of select="data" disable-output-escaping="yes" />
										</xsl:when>
										<xsl:otherwise>
											<xsl:copy-of select="data/*" />
										</xsl:otherwise>
									</xsl:choose>
									<xsl:choose>
										<xsl:when test="string-length( @suffix )">
											<span class="add-on">
												<xsl:value-of select="@suffix" />
											</span>
										</xsl:when>
										<xsl:otherwise>
											<span id="{$fieldId}-message" class="hide message-lightbulb">
												<i class="icon-lightbulb" />
											</span>
										</xsl:otherwise>
									</xsl:choose>
								</div>
								<xsl:if test="string-length( @suffix )">
									<span id="{$fieldId}-message" class="hide message-lightbulb">
										<i class="icon-lightbulb" />
									</span>
								</xsl:if>
							</div>
						</div>
					</xsl:if>
				</xsl:for-each>
			</div>
			<div class="pull-right">
				<button class="btn sobipro-cancel" type="button">
					<xsl:value-of select="entry/fields/cancel_button/data/button" />
				</button>
				<button class="btn btn-primary sobipro-submit" type="button" data-loading-text="Loading...">
					<xsl:value-of select="entry/fields/save_button/data/input/@value" />
				</button>
			</div>
			<div class="clearfix" />
		</div>
		<input type="hidden" name="method" value="xhr" />
		<input type="hidden" name="format" value="raw" />
	</xsl:template>
</xsl:stylesheet>
