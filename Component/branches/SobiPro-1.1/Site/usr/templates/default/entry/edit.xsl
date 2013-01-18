<?xml version="1.0" encoding="UTF-8"?>
<!--
 @version: $Id$
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: http://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 $Date$
 $Revision$
 $Author$
 $HeadURL$
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:include href="../common/topmenu.xsl" />

	<xsl:template match="/entry_form">
		<div class="SPEntryEdit">
			<div>
				<xsl:call-template name="topMenu">
					<xsl:with-param name="searchbox">false</xsl:with-param>
				</xsl:call-template>
			</div>

			<div class="form-horizontal">
				<xsl:for-each select="entry/fields/*">
					<xsl:if test="( name() != 'save_button' ) and ( name() != 'cancel_button' )">
						<xsl:variable name="fieldId" select="name(.)" />
						<xsl:if test="string-length( fee )">
							<input name="{$fieldId}Payment" id="{$fieldId}Payment" value="" type="checkbox" class="SPPaymentBox" />
							<label class="control-label" for="{$fieldId}Payment">
								<xsl:value-of select="fee_msg" />
							</label>
							<div>
								<xsl:value-of select="php:function( 'SobiPro::Txt', 'TP.PAYMENT_ADD' )" />
							</div>
						</xsl:if>

						<div class="control-group" id="{$fieldId}Container">
							<label class="control-label" for="inputEmail">
								<xsl:choose>
									<xsl:when test="string-length( description )">
										<a href="#" rel="popover" data-placement="right" data-content="{description}" data-original-title="{label}">
											<xsl:value-of select="label" />
										</a>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="label" />
									</xsl:otherwise>
								</xsl:choose>
							</label>
							<div class="controls" id="{$fieldId}">
								<xsl:choose>
									<xsl:when test="data/@escaped">
										<xsl:value-of select="data" disable-output-escaping="yes" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:copy-of select="data/*" />
									</xsl:otherwise>
								</xsl:choose>
							</div>
						</div>
					</xsl:if>
				</xsl:for-each>
			</div>

			<div>
				<xsl:for-each select="entry/fields/*">
					<xsl:if test="( name() != 'save_button' ) and ( name() != 'cancel_button' )">
						<xsl:variable name="fieldId">
							<xsl:value-of select="name(.)" />
						</xsl:variable>
						<div id="{$fieldId}Container">
							<xsl:attribute name="class">
								<xsl:choose>
									<xsl:when test="position() mod 2">spFormRowEven</xsl:when>
									<xsl:otherwise>spFormRowOdd</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							<xsl:if test="string-length( fee )">
								<div class="spFormPaymentInfo">
									<input name="{$fieldId}Payment" id="{$fieldId}Payment" value="" type="checkbox" class="SPPaymentBox" onclick="SP_ActivatePayment( this )" />
									<label for="{$fieldId}Payment">
										<xsl:value-of select="fee_msg"></xsl:value-of>
										<br />
									</label>
									<div style="margin-left:20px;">
										<xsl:value-of select="php:function( 'SobiPro::Txt', 'TP.PAYMENT_ADD' )" />
									</div>
								</div>
							</xsl:if>
							<div class="spFormRowLeft">
								<label for="{$fieldId}">
									<xsl:choose>
										<xsl:when test="string-length( description )">
											<xsl:variable name="desc">
												<xsl:value-of select="description" />
											</xsl:variable>
											<xsl:variable name="label">
												<xsl:value-of select="label" />
											</xsl:variable>
											<xsl:value-of select="php:function( 'SobiPro::Tooltip', $desc, $label )" disable-output-escaping="yes" />
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="label" />
										</xsl:otherwise>
									</xsl:choose>
								</label>
							</div>
							<div class="spFormRowRight">
								<xsl:choose>
									<xsl:when test="data/@escaped">
										<xsl:value-of select="data" disable-output-escaping="yes" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:copy-of select="data/*" />
									</xsl:otherwise>
								</xsl:choose>
								<xsl:text> </xsl:text><xsl:value-of select="@suffix" />
							</div>
						</div>
					</xsl:if>
				</xsl:for-each>
			</div>
			<div class="spFormRowFooter">
				<div>
					<xsl:copy-of select="entry/fields/cancel_button/data/*" />
					<xsl:copy-of select="entry/fields/save_button/data/*" />
				</div>
			</div>
			<br />
			<div class="clearall" />
		</div>
	</xsl:template>

	<xsl:template name="fieldOutput">

	</xsl:template>
</xsl:stylesheet>
