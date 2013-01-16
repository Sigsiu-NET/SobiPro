<?xml version="1.0" encoding="UTF-8"?>
<!--
 @version: $Id$
 @package: SobiPro Component for Joomla!

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: http://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 @license GNU/GPL Version 3
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 $Date$
 $Revision$
 $Author$
 $HeadURL$
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:template match="menu">
		<div class="spTopMenu">
			<div class="SPt">
				<div class="SPt">
					<div class="SPt"></div>
				</div>
			</div>
			<div class="SPm">
				<ul class="spTopMenu">
					<xsl:if test="front">
						<li class="spTopMenu">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="front/@url" />
								</xsl:attribute>
								<xsl:value-of select="front" />
							</a>
						</li>
					</xsl:if>
					<xsl:if test="search">
						<li class="spTopMenu">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="search/@url" />
								</xsl:attribute>
								<xsl:value-of select="search" />
							</a>
						</li>
					</xsl:if>
					<xsl:if test="add">
						<li class="spTopMenu">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="add/@url" />
								</xsl:attribute>
								<xsl:value-of select="add" />
							</a>
						</li>
					</xsl:if>
				</ul>
				<div style="clear:both;"></div>
			</div>
			<div class="SPb">
				<div class="SPb">
					<div class="SPb"></div>
				</div>
			</div>
		</div>
		<div style="clear:both;"></div>
	</xsl:template>
</xsl:stylesheet>
