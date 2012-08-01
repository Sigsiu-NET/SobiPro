<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:template name="category">
		<xsl:variable name="url">
			<xsl:value-of select="url" />
		</xsl:variable>
		<div class="spCatListIcon">
			<xsl:if test="string-length( icon )">
				<a href="{$url}">
					<img alt="icon" class="spCatListIcon">
						<xsl:attribute name="src">
							<xsl:value-of select="icon" />
						</xsl:attribute>
					</img>
				</a>
			</xsl:if>
		</div>
		<div class="spCatsListTitle">
			<a href="{$url}">
				<xsl:value-of select="name" />
			</a>
		</div>
		<div class="spCatsListIntrotext">
			<xsl:value-of select="introtext" disable-output-escaping="yes" />
		</div>
		<xsl:for-each select="subcategories/subcategory">
			<xsl:if test="position() &lt; 6">
				<span class="spCatListSubCats">
					<a>
						<xsl:attribute name="href">
							<xsl:value-of select="@url" />
						</xsl:attribute>
						<xsl:value-of select="." />
					</a>,
				</span>
			</xsl:if>
		</xsl:for-each>

	</xsl:template>
</xsl:stylesheet>
