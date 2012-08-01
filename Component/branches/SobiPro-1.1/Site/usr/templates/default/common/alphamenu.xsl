<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
<xsl:include href="alphaindex.xsl" />
	<xsl:template match="alphaMenu">
		<xsl:variable name="contId">SPAlphaIndex<xsl:value-of select="php:function( 'rand', 1, 5 )" /></xsl:variable>
		<div class="spAlphaMenu">
			<xsl:if test="count(fields/*)">
				<script type="text/javascript">
					SPAlphaSwitch( '<xsl:value-of select="$contId" />' );
				</script>
				<xsl:variable name="current">
					<xsl:value-of select="fields/@current" />
				</xsl:variable>
				<span>
					<xsl:attribute name="id"><xsl:value-of select="$contId" />Progress</xsl:attribute>
					<xsl:attribute name="style">z-index: 9; position: absolute;</xsl:attribute>
				</span>
				<select>
					<xsl:attribute name="class">spAlphaMenuSwitch</xsl:attribute>
					<xsl:attribute name="id"><xsl:value-of select="$contId" />Switch</xsl:attribute>
					<xsl:for-each select="fields/*">
						<option>
							<xsl:attribute name="value">
								<xsl:value-of select="name()" />
							</xsl:attribute>
							<xsl:if test="name() = $current">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:value-of select="." />
						</option>
					</xsl:for-each>
				</select>
			</xsl:if>
			<span id="{$contId}">
				<xsl:apply-templates select="letters" />
			</span>
		</div>
		<div style="clear:both;"/>
	</xsl:template>
</xsl:stylesheet>
