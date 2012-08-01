<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" encoding="UTF-8" />
	<xsl:template match="letters|/menu/alphaMenu/letters">
		<xsl:variable name="letter">
			<xsl:value-of select="php:function( 'SobiPro::Request', 'letter' )" />
		</xsl:variable>
		<xsl:for-each select="letter">
			<xsl:choose>
				<xsl:when test="@url">
					<span>
						<xsl:attribute name="class">
							<xsl:choose>
								<xsl:when test=". = $letter">spAlphaLetterSelected</xsl:when>
								<xsl:otherwise>spAlphaLetter</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
						<a>
							<xsl:attribute name="href">
								<xsl:value-of select="@url" />
							</xsl:attribute>
							<xsl:value-of select="." />
						</a>
					</span>
				</xsl:when>
				<xsl:otherwise>
					<span>
						<xsl:value-of select="." />
					</span>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
