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
	<xsl:template name="manage">
		<xsl:if test="entry/publish_url">
			<span class="spEntryEditLink">
				<a>
					<xsl:attribute name="href">
						<xsl:value-of select="entry/publish_url" />
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="entry/state = 'published'">
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'Disable Entry' )" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="php:function( 'SobiPro::Txt', 'Enable Entry' )" />
						</xsl:otherwise>
					</xsl:choose>
				</a>
			</span>
		</xsl:if>
		<xsl:if test="entry/approve_url and entry/approved = 0">
			<span class="spEntryEditLink">
				<a>
					<xsl:attribute name="href">
						<xsl:value-of select="entry/approve_url" />
					</xsl:attribute>
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'Approve Entry' )" />
				</a>
			</span>
		</xsl:if>
		<xsl:if test="entry/edit_url">
			<span class="spEntryEditLink">
				<a>
					<xsl:attribute name="href">
						<xsl:value-of select="entry/edit_url" />
					</xsl:attribute>
					<xsl:value-of select="php:function( 'SobiPro::Txt', 'Edit Entry' )" />
				</a>
			</span>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
