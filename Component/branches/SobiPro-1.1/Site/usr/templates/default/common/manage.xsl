<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:template name="manage">
		<xsl:if test="entry/publish_url">
			<span class="spEntryEditLink">
				<a>
					<xsl:attribute name="href">
						<xsl:value-of select="entry/publish_url" />
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="entry/state = 'published'">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'Disable Entry' )" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'Enable Entry' )" />
						</xsl:otherwise>
					</xsl:choose>
				</a>
			</span>
		</xsl:if>
		<xsl:if test="entry/approve_url and entry/approved = 0">
			<span class="spEntryEditLink">
				<a>
					<xsl:attribute name="href">
						<xsl:value-of select="entry/approve_url" />
					</xsl:attribute>
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'Approve Entry' )" />
				</a>
			</span>
		</xsl:if>
		<xsl:if test="entry/edit_url">
			<span class="spEntryEditLink">
				<a>
					<xsl:attribute name="href">
						<xsl:value-of select="entry/edit_url" />
					</xsl:attribute>
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'Edit Entry' )" />
				</a>
			</span>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
