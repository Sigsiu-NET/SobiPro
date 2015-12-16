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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl"
                exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

	<xsl:template name="editfield">
		<xsl:param name="fieldname"/>

		<xsl:if test="( name($fieldname) != 'save_button' ) and ( name($fieldname) != 'cancel_button' )">
			<xsl:variable name="fieldId" select="name($fieldname)" />
			<xsl:if test="string-length( $fieldname/fee )">
				<div class="form-group payment-message">
					<label class="col-sm-2 control-label">
						<div class="paybox">
							<span>
								<input name="{$fieldId}Payment" id="{$fieldId}-payment" value="" type="checkbox" class="payment-box" />
							</span>
						</div>
					</label>
					<div class="col-sm-10">
						<div class="alert spAlert">
							<xsl:value-of select="$fieldname/fee_msg" /><xsl:text> </xsl:text>
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'TP.PAYMENT_ADD' )" />
						</div>
					</div>
				</div>
			</xsl:if>

			<xsl:variable name="offset">
				<xsl:choose>
					<xsl:when test="$fieldname/label/@show = 1"> </xsl:when>
					<xsl:otherwise> col-sm-offset-2</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<div class="form-group {$fieldname/@css-edit}" id="{$fieldId}-container">
				<xsl:if test="$fieldname/label/@show = 1">
					<label class="col-sm-2 control-label" for="{$fieldId}-input-container">
						<xsl:choose>
							<xsl:when test="string-length( $fieldname/description ) and //config/help-position/@value = 'popup'">
								<a href="#" rel="popover" data-placement="top" data-content="{$fieldname/description}" data-original-title="{$fieldname/label}">
									<xsl:value-of select="$fieldname/label" />
								</a>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$fieldname/label" />
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="$fieldname/@required = 1 and //config/required-star/@value = 1">
							<sup><span class="star"><i class="icon-star"></i></span></sup>
						</xsl:if>
					</label>
				</xsl:if>
				<xsl:variable name="colwidth">
					<xsl:choose>
						<xsl:when test="string-length( $fieldname/@width )">
							<xsl:value-of select="$fieldname/@width" />
						</xsl:when>
						<xsl:otherwise>10</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<div class="col-sm-{$colwidth}{$offset}" id="{$fieldId}-input-container">
					<xsl:if test="string-length( $fieldname/description ) and //config/help-position/@value = 'above'">
						<div class="help-block">
							<xsl:value-of select="$fieldname/description" />
						</div>
					</xsl:if>
					<div>
						<xsl:choose>
							<xsl:when test="string-length( $fieldname/@suffix )">
								<xsl:attribute name="class">input-group</xsl:attribute>
								<xsl:choose>
									<xsl:when test="$fieldname/data/@escaped">
										<xsl:value-of select="$fieldname/data" disable-output-escaping="yes" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:copy-of select="$fieldname/data/*" />
									</xsl:otherwise>
								</xsl:choose>
								<div class="input-group-addon">
									<xsl:value-of select="$fieldname/@suffix" />
								</div>
							</xsl:when>
							<xsl:otherwise>
								<xsl:choose>
									<xsl:when test="$fieldname/data/@escaped">
										<xsl:value-of select="$fieldname/data" disable-output-escaping="yes" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:copy-of select="$fieldname/data/*" />
									</xsl:otherwise>
								</xsl:choose>
							</xsl:otherwise>
						</xsl:choose>
					</div>
					<div id="{$fieldId}-message" class="hide message-lightbulb"></div>

					<xsl:if test="string-length( $fieldname/description ) and //config/help-position/@value = 'below'">
						<div class="help-block">
							<xsl:value-of select="$fieldname/description" />
						</div>
					</xsl:if>
				</div>
			</div>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
