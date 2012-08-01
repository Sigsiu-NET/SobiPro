<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/manage.xsl" />
	<xsl:template match="/entry_details">
		<div class="SPDetails">
		    <div class="SobiPro componentheading">
		      <xsl:value-of select="section" />
		    </div>
		    <div style="clear:both;"></div>
		    <div>
		      <xsl:apply-templates select="menu" />
		    </div>

			<div>
				<div style="clear:both; margin: 10px; margin-right: 20px;">
					<xsl:call-template name="manage" />
					<h3 style="float:left"><xsl:value-of select="entry/name" /></h3>
					<h3 style="float:right">
						<xsl:if test="string-length( entry/fields/field_negotiable/data )">
							<small><xsl:value-of select="php:function( 'SobiPro::Txt', 'Negotiable' )" /></small><xsl:text> </xsl:text>
						</xsl:if>
						<xsl:variable name="price"><xsl:value-of select="entry/fields/field_price/data" /></xsl:variable>
						<xsl:value-of select="php:function( 'SobiPro::Currency', $price )" /><xsl:text> </xsl:text>
					</h3>
				</div>
				<div style="clear:both"></div>
				<hr style=" margin: 10px; margin-left: 10px; margin-right: 100px; color: #F7F7F7;"/>
				<div style="float: right;" id="SPGallery">
					<a class="modal" >
						<xsl:attribute name="href">
							<xsl:value-of select="entry/fields/field_image/data/@original"/>
						</xsl:attribute>
						<xsl:copy-of select="entry/fields/field_image/data/*" />
					</a>
					<br/>
					<a class="modal" >
						<xsl:attribute name="href">
							<xsl:value-of select="entry/fields/field_image_2/data/@original"/>
						</xsl:attribute>
						<xsl:copy-of select="entry/fields/field_image_2/data/*" />
					</a>
					<br/>
					<a class="modal" >
						<xsl:attribute name="href">
							<xsl:value-of select="entry/fields/field_image_3/data/@original"/>
						</xsl:attribute>
						<xsl:copy-of select="entry/fields/field_image_3/data/*" />
					</a>
					<br/>
					<a class="modal" >
						<xsl:attribute name="href">
							<xsl:value-of select="entry/fields/field_image_4/data/@original"/>
						</xsl:attribute>
						<xsl:copy-of select="entry/fields/field_image_4/data/*" />
					</a>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_make/label" />: </strong>
					<xsl:value-of select="entry/fields/field_make/data"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_first_registration/label" />: </strong>
					<xsl:value-of select="entry/fields/field_first_registration/data"/>
					<xsl:text> </xsl:text>
					<xsl:value-of select="entry/fields/field_first_registration/@suffix"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_mileage/label" />: </strong>
					<xsl:value-of select="entry/fields/field_mileage/data"/>
					<xsl:text> </xsl:text>
					<xsl:value-of select="entry/fields/field_mileage/@suffix"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_power/label" />: </strong>
					<xsl:value-of select="entry/fields/field_power/data"/>
					<xsl:text> </xsl:text>
					<xsl:value-of select="entry/fields/field_power/@suffix"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_fuel_type/label" />: </strong>
					<xsl:value-of select="entry/fields/field_fuel_type/data"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_gearbox/label" />: </strong>
					<xsl:value-of select="entry/fields/field_gearbox/data"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_airbags/label" />: </strong>
					<xsl:for-each select="entry/fields/field_airbags/data/ul/li">
						<xsl:value-of select="."/>,<xsl:text> </xsl:text>
					</xsl:for-each>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_climate_control/label" />: </strong>
					<xsl:value-of select="entry/fields/field_climate_control/data"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_number_of_seats/label" />: </strong>
					<xsl:value-of select="entry/fields/field_number_of_seats/data"/><xsl:text>  </xsl:text>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_door_count /label" />: </strong>
					<xsl:value-of select="entry/fields/field_door_count /data"/>
				</div>

				<br/>
				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_feature_sets/label" />: </strong>
					<br/>
					<xsl:copy-of select="entry/fields/field_feature_sets/data"/>
				</div>

				<div class="spField">
					<strong><xsl:value-of select="entry/fields/field_description/label" />: </strong>
					<br/>
					<xsl:value-of select="entry/fields/field_description/data" disable-output-escaping="yes" />
				</div>
				<div style="clear:both;"/>
			</div>
			<div style="clear:both; width: 100%; text-align: center; margin-top: 30px;">
				<hr style="margin: 40px; margin-top: 10px; margin-bottom: 10px; color: #F7F7F7;"/>
				<xsl:value-of select="php:function( 'SobiPro::Txt', 'Date added' )" />:<xsl:text> </xsl:text>
				<xsl:value-of select="entry/created_time"/><xsl:text> | </xsl:text>

				<xsl:value-of select="php:function( 'SobiPro::Txt', 'Last time updated' )" />:<xsl:text> </xsl:text>
				<xsl:value-of select="entry/updated_time"/><xsl:text> | </xsl:text>

				<xsl:value-of select="php:function( 'SobiPro::Txt', 'Viewed' )" />:<xsl:text> </xsl:text>
				<strong><xsl:value-of select="entry/counter"/></strong><xsl:text> </xsl:text>
				<xsl:value-of select="php:function( 'SobiPro::Txt', 'times' )" />
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
