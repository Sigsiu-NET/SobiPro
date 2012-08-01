<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:include href="../common/topmenu.xsl" />
	<xsl:include href="../common/catchooser.xsl" />

	<xsl:template match="/entry_form">
		<div class="SPEntryEdit">
		    <div>
		      <xsl:apply-templates select="menu" />
		    </div>
			<div style="clear:both;"/>
			<div id="osx-modal-content">
				<div id="osx-modal-title">
					<xsl:value-of select="php:function( 'SobiPro::Txt' , 'TP.SEL_CAT_BOX' )" />
				</div>
				<div class="close"><a href="#" class="simplemodal-close">x</a></div>
				<div id="osx-modal-data">
					<xsl:call-template name="catChooser"/>
				</div>
			</div>
			<div class="spFormRowOdd" >
				<div class="spFormRowLeft">
					<label for="entry.parent">
						<xsl:value-of select="php:function( 'SobiPro::Txt' , 'TP.CAT_BOX' )" />
					</label>
				</div>
				<div class="spFormRowRight">
					<xsl:copy-of select="entry/category_chooser/path/*"/>
					<div style="clear:both;"/>
					<div style="float:left; display:none;">
						<xsl:copy-of select="entry/category_chooser/selected/*"/>
					</div>
					<div style="float:left;">
						<button type="button" name="parent_path" id="entry_parent_path" class="osx inputbox">
							<xsl:value-of select="php:function( 'SobiPro::Txt' , 'EN.SELECT_CAT_PATH' )" />
						</button>
					</div>
					<div style="clear:both;"/>
				</div>
			</div>
			<div style="clear:both;"/>
			<div>
				<xsl:for-each select="entry/fields/*">
					<xsl:if test="( name() != 'save_button' ) and ( name() != 'cancel_button' )">
						<xsl:variable name="fieldId">
							<xsl:value-of select="name(.)" />
						</xsl:variable>
						<div id="{$fieldId}Container">
							<xsl:attribute name="class">
								<xsl:choose>
									<xsl:when test="position() mod 2">spFormRowEven</xsl:when>
									<xsl:otherwise>spFormRowOdd</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							<xsl:if test="string-length( fee )">
								<div class="spFormPaymentInfo">
									<input name="{$fieldId}Payment" id="{$fieldId}Payment" value="" type="checkbox" class="SPPaymentBox" onclick="SP_ActivatePayment( this )"/>
									<label for="{$fieldId}Payment">
										<xsl:value-of select="fee_msg"></xsl:value-of><br/>
									</label>
									<div style="margin-left:20px;">
										<xsl:value-of select="php:function( 'SobiPro::Txt', 'TP.PAYMENT_ADD' )" />
									</div>
								</div>
							</xsl:if>
							<div class="spFormRowLeft">
								<label for="{$fieldId}">
									<xsl:choose>
										<xsl:when test="string-length( description )">
											<xsl:variable name="desc">
												<xsl:value-of select="description" />
											</xsl:variable>
											<xsl:variable name="label">
												<xsl:value-of select="label" />
											</xsl:variable>
											<xsl:value-of select="php:function( 'SobiPro::Tooltip', $desc, $label )" disable-output-escaping="yes"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="label"/>
										</xsl:otherwise>
									</xsl:choose>
								</label>
							</div>
							<div class="spFormRowRight">
								<xsl:choose>
									<xsl:when test="data/@escaped">
										<xsl:value-of select="data" disable-output-escaping="yes"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:copy-of select="data/*" />
									</xsl:otherwise>
								</xsl:choose>
								<xsl:text> </xsl:text><xsl:value-of select="@suffix"/>
							</div>
						</div>
					</xsl:if>
				</xsl:for-each>
			</div>
			<div class="spFormRowFooter">
				<div>
					<xsl:copy-of select="entry/fields/cancel_button/data/*" />
					<xsl:copy-of select="entry/fields/save_button/data/*" />
				</div>
			</div>
			<br/>
			<div style="clear:both;"/>
		</div>
	</xsl:template>
</xsl:stylesheet>
