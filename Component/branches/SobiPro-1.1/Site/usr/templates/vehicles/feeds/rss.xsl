<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:template match="/section|/category">		
	<feed xmlns="http://www.w3.org/2005/Atom">
	<title><xsl:value-of select="name"/></title>	
	<xsl:for-each select="entries/entry">
		<xsl:variable name="url">
			<xsl:value-of select="php:function( 'SobiPro::Cfg', 'live_site' )" /><xsl:value-of select="url" />			
		</xsl:variable>
		<entry>	
			<title><xsl:value-of select="name" /></title>
			<link rel="alternate">
				<xsl:attribute name="href">
					<xsl:value-of select="php:function( 'Sobi::FixPath', $url )" />
				</xsl:attribute>
			</link>
			<updated><xsl:value-of select="updated_time"/></updated>			
			<id><xsl:value-of select="@id" /></id>
			<content type="html">							
				<xsl:value-of select="fields/field_make/label" />:<xsl:text> </xsl:text>
				<xsl:value-of select="fields/field_make/data" />	
				&lt;br&gt;
				<xsl:value-of select="fields/field_first_registration/label" />:<xsl:text> </xsl:text>
				<xsl:value-of select="fields/field_first_registration/data" />
				<xsl:text> </xsl:text><xsl:value-of select="fields/field_first_registration/@suffix"/>
				&lt;br&gt;	
				<xsl:value-of select="fields/field_mileage/label" />:<xsl:text> </xsl:text>
				<xsl:value-of select="fields/field_mileage/data" />
				<xsl:text> </xsl:text><xsl:value-of select="fields/field_mileage/@suffix"/>
				&lt;br&gt;
				<xsl:value-of select="fields/field_power/label" />:<xsl:text> </xsl:text>
				<xsl:value-of select="fields/field_power/data" />
				<xsl:text> </xsl:text><xsl:value-of select="fields/field_power/@suffix"/>
				&lt;br&gt;
				<xsl:value-of select="fields/field_fuel_type/label" />:<xsl:text> </xsl:text>
				<xsl:value-of select="fields/field_fuel_type/data" />
				&lt;br&gt;
				<xsl:value-of select="fields/field_gearbox/label" />:<xsl:text> </xsl:text>
				<xsl:value-of select="fields/field_gearbox/data" />
				&lt;br&gt;
				&lt;img src=<xsl:value-of select="fields/field_image/data/@thumbnail" />&gt;
		</content>
		</entry>	
	</xsl:for-each>
	</feed>
</xsl:template>
</xsl:stylesheet>