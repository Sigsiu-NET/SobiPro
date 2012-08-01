<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="/frontpage">
		<div style="padding: 10px;">
			<xsl:for-each select="sections/section">
				<div>
					<xsl:variable name="url">
						<xsl:value-of select="url" />
					</xsl:variable>
					<a href="{$url}">
						<xsl:value-of select="name" />
					</a>
				</div>
			</xsl:for-each>
		</div>
	</xsl:template>
</xsl:stylesheet>