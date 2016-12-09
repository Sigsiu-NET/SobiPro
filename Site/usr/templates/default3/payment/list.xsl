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
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:template name="paymentTable">
		<table class="table table-striped payment">
			<thead>
				<tr>
					<td>#</td>
					<td>
						<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_NAME' )" />
					</td>
					<td>
						<xsl:if test="summary/@vat-raw > 0">
						<div class="pull-right">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_NET' )" />
						</div>
						</xsl:if>
					</td>
					<td>
						<div class="pull-right">
							<xsl:choose>
								<xsl:when test="summary/@vat-raw > 0">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_GROSS' )" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_AMOUNT' )"/>
								</xsl:otherwise>
							</xsl:choose>
						</div>
					</td>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="positions/position">
					<tr>
						<td>
							<xsl:value-of select="position()" />
						</td>
						<td>
							<xsl:value-of select="." />
						</td>
						<td>
							<xsl:if test="//summary/@vat-raw > 0">
							<div class="pull-right">
								<xsl:value-of select="@netto" />
							</div>
							</xsl:if>
						</td>
						<td>
							<div class="pull-right">
								<xsl:choose>
									<xsl:when test="//summary/@vat-raw > 0">
								<xsl:value-of select="@brutto" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="summary/@vat-raw"/>
										<xsl:value-of select="@amount"/>
									</xsl:otherwise>
								</xsl:choose>
							</div>
						</td>
					</tr>
				</xsl:for-each>


				<xsl:if test="string-length(discount/@discount)">
					<tr>
						<td></td>
						<td colspan="2">
							<xsl:value-of select="discount/@for" />
							<xsl:if test="discount/@is_percentage = 'true'">
								<xsl:text> (</xsl:text>
								<xsl:value-of select="discount/@discount" />
								<xsl:text>)</xsl:text>
							</xsl:if>
						</td>
						<td>
							<div class="pull-right">
								<xsl:text>-</xsl:text>
								<xsl:value-of select="discount/@discount_sum" />
							</div>
						</td>
					</tr>
				</xsl:if>


				<tr class="summary">
					<td colspan="4">
						<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_SUMMARY' )" />:
					</td>
				</tr>
				<xsl:choose>
					<xsl:when test="summary/@vat-raw > 0">
				<tr class="info">
					<td colspan="3">
						<div class="pull-right">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_NET' )" />
						</div>
					</td>
					<td>
						<div class="pull-right">
							<xsl:value-of select="summary/@sum_netto" />
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<div class="pull-right">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'VAT' )" />
							(<xsl:value-of select="summary/@vat" />)
						</div>
					</td>
					<td>
						<div class="pull-right">
							<xsl:value-of select="summary/@sum_vat" />
						</div>
					</td>
				</tr>
				<tr class="success sum">
					<td colspan="3">
						<div class="pull-right">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_GROSS' )" />
						</div>
					</td>
					<td>
						<div class="pull-right">
							<xsl:value-of select="summary/@sum_brutto" />
						</div>
					</td>
				</tr>
					</xsl:when>
					<xsl:otherwise>
						<tr class="info sum">
							<td colspan="3">
								<div class="pull-right">
									<xsl:value-of select="php:function( 'SobiPro::Txt', 'PAYMENT_POSITION_TOTAL' )"/>
								</div>
							</td>
							<td>
								<div class="pull-right">
									<xsl:value-of select="summary/@sum_amount"/>
								</div>
							</td>
						</tr>
					</xsl:otherwise>
				</xsl:choose>
				<tr class="success">
					<td colspan="4">
						<div class="pull-right">
							<xsl:value-of select="summary/@coupon" />
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</xsl:template>
</xsl:stylesheet>
