<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

	<xsl:include href="../common/topmenu.xsl" />
	<xsl:template match="/payment_details">
		<div class="SPPayment">
			<div>
				<xsl:apply-templates select="menu" />
			</div>
			<div style="clear:both;"/>
			<div class="spPaymentPreview">
				<div class="spPaymentExpl">
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'You have chosen following non-free options' )" />:
				</div>
				<div style="width: 100%; padding: 5px;">
					<div style="width: 10%; text-align:center" class="spPaymentHeader">
						#
					</div>
					<div style="width: 40%;" class="spPaymentHeader">
						<xsl:value-of select="php:function( 'SobiPro::Txt', 'Name' )" />
					</div>
					<div style="width: 15%;" class="spPaymentHeader">
						<xsl:value-of select="php:function( 'SobiPro::Txt', 'Netto' )" />
					</div>
					<div style="width: 15%;" class="spPaymentHeader">
						<xsl:value-of select="php:function( 'SobiPro::Txt', 'Brutto' )" />
					</div>
					<div style="clear:both;"/>
					<xsl:for-each select="positions/position">
						<div style="width: 10%; text-align:center" class="spPaymentPosition">
							<xsl:value-of select="position()" />
						</div>
						<div style="width: 40%;" class="spPaymentPosition">
							<xsl:value-of select="." />
						</div>
						<div style="width: 15%; text-align:right" class="spPaymentPosition">
							<xsl:value-of select="@netto" />
						</div>
						<div style="width: 15%; text-align:right" class="spPaymentPosition">
							<xsl:value-of select="@brutto" />
						</div>
						<div style="clear:both;"/>
					</xsl:for-each>
					<xsl:if test="discount">
						<div style="width: 100%; padding: 5px;">
							<div class="spPaymentDiscount">
								<xsl:value-of select="php:function( 'SobiPro::Txt', 'Discount' )" />:
							</div>
							<div style="width: 40%; text-align:left" class="spPaymentPosition">
								<xsl:value-of select="discount/@for" />
							</div>
							<div style="width: 10%; text-align:right" class="spPaymentPosition">
								<xsl:value-of select="discount/@discount" />
							</div>
							<div style="width: 30%; text-align:right" class="spPaymentPosition">
								- <xsl:value-of select="discount/@discount_sum" />
							</div>
							<div style="clear:both;"/>
						</div>
					</xsl:if>
					<div style="width: 100%; padding: 5px;">
						<div class="spPaymentSum">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'Summary' )" />:
						</div>
						<div style="clear:both;"/>
						<div style="width: 40%; text-align:left" class="spPaymentSumDesc">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'Netto' )" />
						</div>
						<div style="width: 40%; text-align:right" class="spPaymentSumPosition">
							<xsl:value-of select="summary/@sum_netto" />
						</div>
						<div style="width: 40%; text-align:left" class="spPaymentSumDesc">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'VAT' )" /> (<xsl:value-of select="summary/@vat" />)
						</div>
						<div style="width: 40%; text-align:right" class="spPaymentSumPosition">
							<xsl:value-of select="summary/@sum_vat" />
						</div>
						<div style="width: 40%; text-align:left" class="spPaymentSumDesc">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'Brutto' )" />
						</div>
						<div style="width: 40%; text-align:right" class="spPaymentSumPosition">
							<xsl:value-of select="summary/@sum_brutto" />
						</div>
						<div style="clear:both;"/>
					</div>
					<br/>
					<br/>
					<div style="width: 100%; padding: 5px;">
					<div class="spPaymentExpl">
						<xsl:value-of select="php:function( 'SobiPro::Txt', 'Please select payment method below' )" />:
					</div>
					<hr/>
					<xsl:for-each select="payment_methods/*">
						<div class="spPaymentSum">
							<xsl:value-of select="@title"/>
						</div>
						<div style="width: 100%; padding: 5px;">
							<xsl:choose>
								<xsl:when test="@escaped">
									<xsl:value-of select="." disable-output-escaping="yes"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:choose>
										<xsl:when test="count(./*)">
											<xsl:copy-of select="./*"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="." disable-output-escaping="yes" />
										</xsl:otherwise>
									</xsl:choose>
								</xsl:otherwise>
							</xsl:choose>
						</div>
						<div style="clear:both;"/>
					</xsl:for-each>
					</div>
				</div>
			<br/><br/>
			</div>
			<div style="clear:both;"/>
		</div>
	</xsl:template>
</xsl:stylesheet>
