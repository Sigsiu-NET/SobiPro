<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:template match="menu">
		<div class="spTopMenu">
			<div class="SPt">
				<div class="SPt">
					<div class="SPt"></div>
				</div>
			</div>
			<div class="SPm">
				<ul class="spTopMenu">
					<xsl:if test="front">
						<li class="spTopMenu">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="front/@url" />
								</xsl:attribute>
								<xsl:value-of select="front" />
							</a>
						</li>
					</xsl:if>
					<xsl:if test="search">
						<li class="spTopMenu">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="search/@url" />
								</xsl:attribute>
								<xsl:value-of select="search" />
							</a>
						</li>
					</xsl:if>
					<xsl:if test="add">
						<li class="spTopMenu">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="add/@url" />
								</xsl:attribute>
								<xsl:value-of select="php:function( 'SobiPro::Txt', 'Offer a Car' )" />
							</a>
						</li>
					</xsl:if>
				</ul>
				<div style="clear:both;"></div>
			</div>
			<div class="SPb">
				<div class="SPb">
					<div class="SPb"></div>
				</div>
			</div>
		</div>
		<div style="clear:both;"></div>
	</xsl:template>
</xsl:stylesheet>
