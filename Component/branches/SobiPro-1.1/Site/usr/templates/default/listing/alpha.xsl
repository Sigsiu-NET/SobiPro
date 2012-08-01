<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

<xsl:include href="../common/topmenu.xsl" />
<xsl:include href="../common/alphamenu.xsl" />
<xsl:include href="../common/entries.xsl" />
<xsl:include href="../common/navigation.xsl" />
<xsl:template match="/listing">
	<div class="SPListing">
	    <div>
	      <xsl:apply-templates select="menu" />
	      <xsl:apply-templates select="alphaMenu" />
	    </div>
		<div style="clear:both;"/>
		<xsl:call-template name="entriesLoop" />
		<xsl:apply-templates select="navigation" />
	</div>
</xsl:template>
</xsl:stylesheet>
