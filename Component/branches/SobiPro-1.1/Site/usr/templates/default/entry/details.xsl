<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/manage.xsl" />
	<xsl:include href="../common/alphamenu.xsl" />

	<xsl:template match="/entry_details">
		<div class="SPDetails">
		    <div>
		      <xsl:apply-templates select="menu" />
		      <xsl:apply-templates select="alphaMenu" />
		    </div>
			<div style="clear:both;"/>

			<xsl:call-template name="manage" />

			<div class="SPDetailEntry">
				<h1 class="SPTitle"><xsl:value-of select="entry/name" /></h1>

				<xsl:for-each select="entry/fields/*">
					<div>
						<xsl:attribute name="class">
							<xsl:value-of select="@css_class" />
						</xsl:attribute>

						<xsl:if test="count(data/*) or string-length(data)">
							<xsl:if test="label/@show = 1">
								<strong><xsl:value-of select="label" /><xsl:text>: </xsl:text></strong>
							</xsl:if>
						</xsl:if>

						<xsl:choose>
							<xsl:when test="count(data/*)">
								<xsl:copy-of select="data/*"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:if test="string-length(data)">
									<xsl:value-of select="data" disable-output-escaping="yes" />
								</xsl:if>
							</xsl:otherwise>
						</xsl:choose>

						<xsl:if test="count(data/*) or string-length(data)">
							<xsl:if test="string-length(@suffix)">
								<xsl:text> </xsl:text>
								<xsl:value-of select="@suffix"/>
							</xsl:if>
						</xsl:if>
					</div>
				</xsl:for-each>

				<xsl:if test="count(entry/categories)">
					<div class="spEntryCats">
						<xsl:value-of select="php:function( 'SobiPro::Txt' , 'Located in:' )" /><xsl:text> </xsl:text>
						<xsl:for-each select="entry/categories/category">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="@url" />
								</xsl:attribute>
								<xsl:value-of select="." />
							</a>
							<xsl:if test="position() != last()">
							<xsl:text> | </xsl:text>
							</xsl:if>
						</xsl:for-each>
					</div>
				</xsl:if>
			</div>
			<div style="clear:both;"></div>
		</div>
	</xsl:template>
</xsl:stylesheet>
