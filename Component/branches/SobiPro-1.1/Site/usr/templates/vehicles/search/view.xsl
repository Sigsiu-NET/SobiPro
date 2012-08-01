<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
<xsl:include href="../common/topmenu.xsl" />
<xsl:include href="../common/entries.xsl" />
<xsl:include href="../common/navigation.xsl" />

<xsl:template match="/search">
	<div class="SPSearch">
	    <div class="SobiPro componentheading">
	      <xsl:value-of select="section" />
	    </div>
	    <div style="clear:both;"></div>
	    <div>
	      <xsl:apply-templates select="menu" />
	    </div>
		<div id="SPSearchForm">
			<!-- define variable to check if there are more than 3 fields -->
			<xsl:variable name="fieldsCount">
				<xsl:value-of select="count(fields/*)" />
			</xsl:variable>
			<xsl:choose>
				<!-- if there are more than 3 fields we show the extended search option -->
				<xsl:when test="$fieldsCount &gt; 3">
					<xsl:for-each select="fields/*">
						<!-- output the first 3 fields -->
						<xsl:if test="position() &lt; 4">
							<!-- directly after the "search" button -->
							<xsl:if test="position() = 3">
								<xsl:variable name="ExOptLabel">
									<xsl:value-of select="php:function( 'SobiPro::Txt', 'Extended Search' )" />
								</xsl:variable>
								<input id="SPExOptBt" class="button osx" name="SPExOptBt" value="{$ExOptLabel}" type="button"/>
							</xsl:if>
							<xsl:call-template name="FieldCell" />
						</xsl:if>
					</xsl:for-each>
					<!-- output all other fields -->
					<div id="osx-modal-content">
						<div id="osx-modal-title">
							<xsl:value-of select="php:function( 'SobiPro::Txt' , 'Extended Search Options' )" />
						</div>
						<div id="osx-close"><a href="#" class="simplemodal-close">x</a></div>
						<div id="osx-modal-data" style="overflow: scroll; height: 450px; ">
							<button class="simplemodal-close inputbox" style="float: right; font-weight:bold;">
								<xsl:value-of select="php:function( 'SobiPro::Txt' , 'Save Selected Options' )" />
							</button>
							<div style="clear:both;"/>
							<xsl:for-each select="fields/*">
								<xsl:if test="position() &gt; 3">
									<xsl:call-template name="FieldCell" />
								</xsl:if>
							</xsl:for-each>
							<button class="simplemodal-close inputbox" style="float: right; font-weight:bold;">
								<xsl:value-of select="php:function( 'SobiPro::Txt' , 'Save Selected Options' )" />
							</button>
						</div>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<xsl:for-each select="fields/*">
						<xsl:call-template name="FieldCell" />
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</div>
		<div style="clear:both;"/>
		<xsl:if test="message">
			<div class="message">
				<xsl:value-of select="message"/>
			</div>
		</xsl:if>

		<xsl:call-template name="entriesLoop" />
		<xsl:apply-templates select="navigation" />
		<div style="clear:both;"/>
	</div>
</xsl:template>

<xsl:template name="FieldCell">
	<div class="SPSearchCell">
		<xsl:if test="not( name() = 'top_button' )">
			<div class="SPSearchLabel">
				<strong><xsl:value-of select="label" /><xsl:text>: </xsl:text></strong>
			</div>
		</xsl:if>
		<div class="SPSearchField">
			<xsl:copy-of select="data/*"/>
		</div>
	</div>
	<xsl:if test="not( name() = 'searchbox' or name() = 'top_button' )">
		<div style="clear:both;"/>
	</xsl:if>
</xsl:template>
</xsl:stylesheet>
