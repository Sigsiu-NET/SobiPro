<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:include href="alphaindex.xsl"/>
	<xsl:template match="alphaMenu">
		<div class="row">
			<div class="col-md-12">
				<div class="spAlphamenu">
					<xsl:if test="count( fields/* )">
						<div class="alphalist">
							<div class="dropdown">
								<button class="btn dropdown-toggle btn-xs btn-sigsiu" id="alphamenu" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<xsl:value-of select="php:function( 'SobiPro::Txt', 'ALPHALIST_SELECT' )"/><xsl:text> </xsl:text>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" aria-labelledby="alphamenu">
									<xsl:for-each select="fields/*">
										<li>
											<a href="#" rel="{name()}" class="alpha-switch" tabindex="0">
												<xsl:value-of select="."/>
											</a>
										</li>
									</xsl:for-each>
								</ul>
							</div>
						</div>
					</xsl:if>
					<div id="alpha-index" class="alpha">
						<xsl:apply-templates select="letters"/>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"/>
	</xsl:template>
</xsl:stylesheet>
