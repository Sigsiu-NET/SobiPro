<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

	<xsl:template name="vcard">
		<span class="spEntriesListTitle">
			<a>
				<xsl:attribute name="href">
					<xsl:value-of select="url" />
				</xsl:attribute>
				<xsl:value-of select="name" />
			</a>
		</span>
		<xsl:if test="edit_url">
			<span class="spEntriesListEditLink">
				<a>
					<xsl:attribute name="href">
						<xsl:value-of select="edit_url" />
					</xsl:attribute>
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'Edit Entry' )" />
				</a>
			</span>
		</xsl:if>
		<xsl:for-each select="fields/*">
			<div>
				<xsl:attribute name="class">
					<xsl:value-of select="@css_class" />
				</xsl:attribute>

				<xsl:if test="count(data/*) or string-length(data)">
					<xsl:if test="label/@show = 1">
						<strong><xsl:value-of select="label" />: </strong>
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
		<div style="clear:both;"/>
	</xsl:template>
</xsl:stylesheet>
