<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

<xsl:include href="../common/navigation.xsl" />
<xsl:include href="../common/topmenu.xsl" />
<xsl:include href="../common/alphamenu.xsl" />
<xsl:include href="../common/entries.xsl" />
<xsl:include href="../common/category.xsl" />

	<xsl:template match="/category">
		<xsl:variable name="rssUrlSection">{"sid":"<xsl:value-of select="section/@id"/>","sptpl":"feeds.rss","out":"raw"}</xsl:variable>
		<xsl:variable name="sectionName"><xsl:value-of select="section"/></xsl:variable>
		<xsl:value-of select="php:function( 'SobiPro::AlternateLink', $rssUrlSection, 'application/atom+xml', $sectionName )" />
		<xsl:variable name="rssUrl">{"sid":"<xsl:value-of select="id"/>","sptpl":"feeds.rss","out":"raw"}</xsl:variable>
		<xsl:variable name="categoryName"><xsl:value-of select="name"/></xsl:variable>
		<xsl:value-of select="php:function( 'SobiPro::AlternateLink', $rssUrl, 'application/atom+xml', $categoryName )" />
		<div class="SPListing">
		    <div class="SobiPro componentheading">
		      <xsl:value-of select="section" />
		    </div>
		    <div style="clear:both;"></div>
		    <div>
		      <xsl:apply-templates select="menu" />
		      <xsl:apply-templates select="alphaMenu" />
		    </div>
			<div style="clear:both;"/>
			<div class="spCategoryDesc">
				<xsl:value-of select="description" disable-output-escaping="yes"/>
			</div>
			<div class="spCatListContainer">
				<xsl:variable name="catsInLine">
					<xsl:value-of select="categories_in_line" />
				</xsl:variable>
				<xsl:variable name="cellWidth">
					<xsl:value-of select="(100 div $catsInLine) - 5" />
				</xsl:variable>
				<xsl:for-each select="categories/category">
					<div class="spCatListCell" style="width: {$cellWidth}%">
						<xsl:choose>
							<xsl:when test="(position() - 1) mod $catsInLine"></xsl:when>
							<xsl:otherwise><div style="clear:both;"/></xsl:otherwise>
						</xsl:choose>
						<xsl:call-template name="category" />
					</div>
				</xsl:for-each>
				<div style="clear:both;"/>
			</div>
			<div style="clear:both;"/>

			<xsl:call-template name="entriesLoop" />
			<xsl:apply-templates select="navigation" />
			<div style="clear:both;"/>
		</div>
	</xsl:template>
</xsl:stylesheet>
