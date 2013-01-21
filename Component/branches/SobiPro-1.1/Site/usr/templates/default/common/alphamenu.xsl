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
	<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />
	<xsl:include href="alphaindex.xsl" />
	<xsl:template match="alphaMenu">
        <div class="row-fluid">
		<div class="navbar">
			<xsl:if test="count( fields/* )">
				<div class="span1 alphalist">
					<div class="btn-group">
						<a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<xsl:for-each select="fields/*">
								<li>
									<a href="#" rel="{name()}" class="alpha-switch">
										<xsl:value-of select="." />
									</a>
								</li>
							</xsl:for-each>
						</ul>
					</div>
				</div>
			</xsl:if>
			<div id="alpha-index" class="span11">
				<xsl:apply-templates select="letters" />
			</div>
		</div>
        </div>
		<div class="clearall"/>
	</xsl:template>
</xsl:stylesheet>
