<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>

  <xsl:template name="vcard">
    <xsl:variable name="url">
      <xsl:value-of select="url" />
    </xsl:variable>
    <div class="spEntriesListTitle">
      <a href="{$url}">
        <xsl:value-of select="name" /><xsl:text> </xsl:text>/<xsl:text> </xsl:text><xsl:value-of select="fields/field_first_registration/data" />
      </a>
      <div style="float:right;">
        <xsl:if test="string-length( fields/field_negotiable/data )">
          <small><xsl:value-of select="php:function( 'SobiPro::Txt', 'Negotiable' )" /></small><xsl:text> </xsl:text>
        </xsl:if>
        <xsl:variable name="price"><xsl:value-of select="fields/field_price/data" /></xsl:variable>
        <xsl:value-of select="php:function( 'SobiPro::Currency', $price )" /><xsl:text> </xsl:text>
      </div>
    </div>
    <a href="{$url}">
      <xsl:copy-of select="fields/field_image/data/*" />
    </a>

    <div class="spField">
      <strong><xsl:value-of select="fields/field_make/label" />:<xsl:text> </xsl:text></strong>
      <xsl:value-of select="fields/field_make/data" />
    </div>

    <div class="spField">
      <strong><xsl:value-of select="fields/field_first_registration/label" />:<xsl:text> </xsl:text></strong>
      <xsl:value-of select="fields/field_first_registration/data" />
      <xsl:text> </xsl:text><xsl:value-of select="fields/field_first_registration/@suffix"/>
    </div>

    <div class="spField">
      <strong><xsl:value-of select="fields/field_mileage/label" />:<xsl:text> </xsl:text></strong>
      <xsl:value-of select="fields/field_mileage/data" />
      <xsl:text> </xsl:text><xsl:value-of select="fields/field_mileage/@suffix"/>
    </div>

    <div class="spField">
      <strong><xsl:value-of select="fields/field_power/label" />:<xsl:text> </xsl:text></strong>
      <xsl:value-of select="fields/field_power/data" />
      <xsl:text> </xsl:text><xsl:value-of select="fields/field_power/@suffix"/>
    </div>

    <div class="spField">
      <strong><xsl:value-of select="fields/field_fuel_type/label" />:<xsl:text> </xsl:text></strong>
      <xsl:value-of select="fields/field_fuel_type/data" />
    </div>

    <div class="spField">
      <strong><xsl:value-of select="fields/field_gearbox/label" />:<xsl:text> </xsl:text></strong>
      <xsl:value-of select="fields/field_gearbox/data" />
    </div>

    <div style="clear:both;"/>
  </xsl:template>
</xsl:stylesheet>
