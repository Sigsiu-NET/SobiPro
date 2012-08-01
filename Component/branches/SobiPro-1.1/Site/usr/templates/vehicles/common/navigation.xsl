<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:template match="navigation" name="navigation">
		<div style="width: 100%; text-align:center;margin: 5px;">
			<span class="pagination">
				<xsl:if test="count( sites/* ) &gt; 0">
					<span>&#171;</span>
					<xsl:for-each select="sites/site">
						<xsl:variable name="limit">
							<xsl:choose>
								<xsl:when test="../../current_site &lt; 4">
									<xsl:value-of select="8 - ../../current_site" />
								</xsl:when>
								<xsl:when test="../../current_site &gt; count( ../../sites/* ) - 8">
									<xsl:value-of select="7 - ( ../../all_sites  - ../../current_site )" />
								</xsl:when>
								<xsl:otherwise>4</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:variable name="show">
							<xsl:choose>
								<xsl:when test="(.) &gt; ( ../../current_site - $limit ) and (.) &lt; ../../current_site">1</xsl:when>
								<xsl:when test="(.) &lt; ( ../../current_site + $limit ) and (.) &gt; ../../current_site">2</xsl:when>
								<xsl:when test="(.) = ../../current_site">3</xsl:when>
								<xsl:when test="number(.) != (.)">4</xsl:when>
								<xsl:otherwise>0</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
							<xsl:if test="$show &gt; 0">
							<span>
								<xsl:choose>
									<xsl:when test="@url">
										<a href="{@url}"><xsl:value-of select="." /></a>
									</xsl:when>
									<xsl:otherwise>
										<xsl:choose>
											<xsl:when test="@selected = 1">
												<strong><xsl:value-of select="." /></strong>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="." />
											</xsl:otherwise>
										</xsl:choose>
									</xsl:otherwise>
								</xsl:choose>
							</span>
							</xsl:if>
					</xsl:for-each>
					<span>&#187;</span>
				</xsl:if>
			</span>
		</div>
	</xsl:template>
</xsl:stylesheet>
