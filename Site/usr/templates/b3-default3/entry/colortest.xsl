<?xml version="1.0" encoding="UTF-8"?><!--
 @package: SobiRestara SobiPro Template

 @author
 Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Email: sobi[at]sigsiu.net
 Url: https://www.Sigsiu.NET

 @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 @license Released under Sigsiu.NET Template License V1.
 You may use this SobiPro template on an unlimited number of SobiPro installations and may modify it for your needs.
 You are not allowed to distribute modified or unmodified versions of this template for free or paid.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

    <xsl:include href="../common/topmenu.xsl" />
    <xsl:include href="../common/alphamenu.xsl" />
    <xsl:include href="../common/showfields.xsl" />

    <xsl:template match="/entry_details">

        <div class="spDetails">
            <div>
                <xsl:call-template name="topMenu">
                    <xsl:with-param name="searchbox">true</xsl:with-param>
                </xsl:call-template>
                <xsl:apply-templates select="alphaMenu" />
            </div>
            <xsl:apply-templates select="messages" />
            topmenu: always light text on darker background<br/>
            background-color: @base-color<br/>
            text color: @text-color<br/>
            active elements: background is @active-color and text is @hover-color. @active-color should be darker than @base-color
            <div class="clearfix" />

            <div class="spDetailEntry" >

<h1 class="text-uppercase">Tabs example</h1>

				<!-- Standard Tabs -->
                <ul class="nav nav-tabs spTablist" role="tablist" id="#style1">
                    <li role="presentation" class="active">
                        <a href="#stab1" aria-controls="stab1" role="tab" data-toggle="tab">
                            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Standard Tab 1' )" />
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#stab2" aria-controls="stab2" role="tab" data-toggle="tab">
                            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Standard Tab 2' )" />
                        </a>
                    </li>
                </ul>
                <div class="tab-content tabs">
                    <div role="tabpanel" class="tab-pane tabs active" id="stab1" style="min-height: 300px;">
	                    <div>
	                        <div class="spNoImageContainer left">
	                            <div class="spNoImage">
	                                <i class="icon icon-ban-circle"></i>
	                            </div>
	                        </div>
	                        <div class="spClassViewText">
		                        Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi commodo, ipsum sed pharetra gravida, orci magna rhoncus neque, id pulvinar odio lorem non turpis. Nullam sit amet enim. Suspendisse id velit vitae ligula volutpat condimentum. Aliquam erat volutpat. Sed quis velit. Nulla facilisi. Nulla libero. Vivamus pharetra posuere sapien.

		                        Nam consectetuer. Sed aliquam, nunc eget euismod ullamcorper, lectus nunc ullamcorper orci, fermentum bibendum enim nibh eget ipsum. Donec porttitor ligula eu dolor. Maecenas vitae nulla consequat libero cursus venenatis. Nam magna enim, accumsan eu, blandit sed, blandit a, eros.
		                        Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi commodo, ipsum sed pharetra gravida, orci magna rhoncus neque, id pulvinar odio lorem non turpis. Nullam sit amet enim. Suspendisse id velit vitae ligula volutpat condimentum. Aliquam erat volutpat. Sed quis velit. Nulla facilisi. Nulla libero. Vivamus pharetra posuere sapien.
<br/><br/>
noimage icon is:  lighten(@base-color, 30%)

	                        </div>
	                    </div>
                    </div>
                    <div role="tabpanel" class="tab-pane tabs" id="stab2" style="min-height: 300px;">
					</div>
	            </div>
	            <div class="clearfix" />

	            <!-- Coloured Tabs -->
	            <ul class="nav nav-tabs coloured spTablist" role="tablist" id="#style2">
		            <li role="presentation" class="active">
			            <a href="#ctab1" aria-controls="ctab1" role="tab" data-toggle="tab">
				            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Coloured Tab 1' )" />
			            </a>
		            </li>
		            <li role="presentation">
			            <a href="#ctab2" aria-controls="ctab2" role="tab" data-toggle="tab">
				            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Coloured Tab 2' )" />
			            </a>
		            </li>
	            </ul>
	            <div class="tab-content tabs coloured">
		            <div role="tabpanel" class="tab-pane tabs coloured active" id="ctab1" style="min-height: 100px;">
			            pane: .background(light) and .borders(light);<br/>
			            active tab: .background(light) and .borders(light) and border-bottom-color set to background(light) color manually!!!
		            </div>
		            <div role="tabpanel" class="tab-pane tabs coloured" id="ctab2" style="min-height: 100px;">
		            </div>
	            </div>
	            <div class="clearfix" />

	            <!-- Pills -->
	            <ul class="nav nav-pills spTablist" role="tablist" id="#style3">
		            <li role="presentation" class="active">
			            <a href="#spills1" aria-controls="spills1" role="tab" data-toggle="tab">
				            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Pills 1' )" />
			            </a>
		            </li>
		            <li role="presentation">
			            <a href="#spills2" aria-controls="spills2" role="tab" data-toggle="tab">
				            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Pills 2' )" />
			            </a>
		            </li>
	            </ul>
	            <div class="tab-content pills">
		            <div role="tabpanel" class="tab-pane pills active" id="spills1" style="min-height: 100px;">
			            pane: .background(light) and .borders(light);<br/>
			            active tab: background is @active-color
		            </div>
		            <div role="tabpanel" class="tab-pane pills" id="spills2" style="min-height: 100px;">
		            </div>
	            </div>
	            <div class="clearfix" />

	            <!-- Staples -->
	            <ul class="nav nav-pills staples spTablist" role="tablist" id="#style3">
		            <li role="presentation" class="active">
			            <a href="#staples1" aria-controls="staples1" role="tab" data-toggle="tab">
				            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Staples Tab 1' )" />
			            </a>
		            </li>
		            <li role="presentation">
			            <a href="#staples2" aria-controls="staples2" role="tab" data-toggle="tab">
				            <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Staples Tab 2' )" />
			            </a>
		            </li>
	            </ul>
	            <div class="tab-content pills staples">
		            <div role="tabpanel" class="tab-pane tabs active" id="staples1" style="min-height: 100px;">
			            pane: .background(light) and .borders(light);<br/>
			            active tab: .background(medium) and border-bottom: @active-color;
		            </div>
		            <div role="tabpanel" class="tab-pane pills staples" id="staples2" style="min-height: 100px;">
		            </div>
	            </div>
	            <div class="clearfix" />


<h1 class="text-uppercase">Edit form example</h1>
	            <div class="spEntryEdit">
		            <div class="form-horizontal">
			            <div class="form-group spClassEditInbox" id="field_name-container">
				            <label class="col-sm-2 control-label" for="field_name-input-container">Company Name<sup><span class="star"><i class="icon-star"></i></span></sup></label>
				            <div class="col-sm-5" id="field_name-input-container">
					            <div>
						            <input type="text" name="field_name" value="" id="field_name" class="spClassInbox required form-control" maxlength="150" placeholder="Company Name" />
					            </div>
					            <div id="field_name-message" class="message-lightbulb"></div>
				            </div>
			            </div>
			            <div class="form-group spClassEditCategory" id="field_categories-container">
				            <label class="col-sm-2 control-label" for="field_categories-input-container">Categories<sup><span class="star"><i class="icon-star"></i></span></sup></label>
				            <div class="col-sm-4" id="field_categories-input-container">
					            <div>
						            <select name="field_categories[]" multiple="multiple" id="field_categories" class="required spClassCategory form-control" style="height: 150px">
							            <option disabled="disabled" selected="selected" value="62">- Companies</option>
							            <option value="62">-- Category 1</option>
							            <option value="63">-- Category 2</option>
							            <option value="64">-- Category 3</option>
							            <option value="65">-- Category 4</option>
						            </select>
					            </div>
					            <div class="help-block">Please select which categories fit best to your company. You can choose up to 4 categories. To select several entries hold the shift or ctrl/cmd button while selecting the categories.</div>
				            </div>
			            </div>
			            <div class="form-group spClassEditInfo" id="field_address_data-container">
				            <div class="col-sm-6 col-sm-offset-2" id="field_address_data-input-container">
					            <div>
						            <div class="spClassInfo spClassEditInfo">
							            <div class="alert spAlert"><h4>Company Address</h4>background: @alert-color<br/>
								            color: @active-color;<br/>
								            border-color: darken(@alert-color, 10%);</div>
						            </div>
					            </div>
				            </div>
			            </div>
			            <div class="form-group spClassEditInbox" id="field_street-container">
				            <label class="col-sm-2 control-label" for="field_street-input-container">Street<sup><span class="star"><i class="icon-star"></i></span></sup></label>
				            <div class="col-sm-4" id="field_street-input-container">
					            <div>
						            <input type="text" name="field_street" value="" id="field_street" class="spClassInbox required form-control" maxlength="150" placeholder="Street" />
					            </div>
				            </div>
			            </div>
			            <div class="form-group spClassEditInbox" id="field_city-container">
				            <label class="col-sm-2 control-label" for="field_city-input-container">City<sup><span class="star"><i class="icon-star"></i></span></sup></label>
				            <div class="col-sm-4" id="field_city-input-container">
					            <div>
						            <input type="text" name="field_city" value="" id="field_city" class="spClassInbox required form-control" maxlength="150" placeholder="City" />
					            </div>
				            </div>
			            </div>
			            <div class="form-group payment-message">
				            <label class="col-sm-2 control-label">
					            <div class="paybox">
						            <span>
							            <input name="field_websitePayment" id="field_website-payment" value="" type="checkbox" class="payment-box" />
						            </span>
					            </div>
				            </label>
				            <div class="col-sm-10">
					            <div class="alert spAlert"> 'Website' is a paid field. The price is 10,00 €. Click the checkbox on the left side if you wish to activate this option.</div>
				            </div>
			            </div>
			            <div class="form-group spClassEditUrl" id="field_website-container">
				            <label class="col-sm-2 control-label" for="field_website-input-container">Website</label>
				            <div class="col-sm-5" id="field_website-input-container">
					            <div>
						            <div class="spFieldUrl">
							            <div class="input-group"><div class="input-group-btn">
								            <select name="field_website_protocol" id="field_website_protocol" class="spClassUrlProtocol form-control">
									            <option selected="selected" value="http">http://</option>
									            <option value="https">https://</option>
								            </select>

							            </div>
								            <input type="text" name="field_website_url" value="" id="field_website_url" class="spClassUrl form-control" maxlength="150" placeholder="Website" />

							            </div>
						            </div>
					            </div>
				            </div>
			            </div>
		            </div>uppercase
		            <div class="required-message"><sup><span class="star"><i class="icon-star"></i></span></sup><span class="text-sigsiu-important">stars are in @important-color</span></div>
		            <div class="clearfix"></div>
		            <div class="pull-left">
			            <button class="btn btn-default sobipro-cancel" type="button">Cancel</button>
			            <button class="btn btn-primary btn-sigsiu sobipro-submit" type="button" data-loading-text="Loading...">btn-sigsiu</button>
		            </div>
		            <div class="clearfix"></div>
	            </div>
	            <div class="clearfix"></div>
<h1 class="text-uppercase">text and background defines</h1>

	            <div>
		            <div style="min-height:300px;margin:10px;text-align:center;padding-top:100px;border-width:5px;" class="bg-sigsiu"><p>.bg-sigsiu</p></div>
		            <div style="min-height:300px;margin:10px;text-align:center;padding-top:100px;border-width:5px;" class="bg-sigsiu-light"><p>.bg-sigsiu-light</p></div>
		            <div style="min-height:300px;margin:10px;text-align:center;padding-top:100px;border-width:5px;" class="bg-sigsiu-alert"><p>.bg-sigsiu-alert</p></div>
		            <div style="text-align:center;padding-top:10px" class="">
			            <h2 class="text-sigsiu">.text-sigsiu</h2>
		                <h2 class="text-sigsiu-active">.text-sigsiu-active</h2>
		                <h2 class="text-sigsiu-important">.text-sigsiu-important</h2>
		            </div>
	            </div>

<h1 class="text-uppercase">Calendar table example</h1>
	            <div class="spListing" id="SpCalendar">
		            <div class="row spCalNav">
			            <div class="col-xs-12 col-sm-4 spCalendarPrev top">
				            <a href="." class="btn btn-default pull-left"><i class="icon-double-angle-left"></i> October</a>
			            </div>
			            <div class="col-xs-12 col-sm-4 spCalendarMonth top">
				            <h3>November 2015</h3>
			            </div>
			            <div class="col-xs-12 col-sm-4 spCalendarNext top">
				            <a href="." class="btn btn-default pull-right">December <i class="icon-double-angle-right"></i></a>
			            </div>
		            </div>
		            <div class="table-responsive">
			            <table class="table table-bordered SpCalendar hidden-xs">
				            <thead>
					            <tr>
						            <th>Monday</th>
						            <th>Tuesday</th>
						            <th>Wednesday</th>
						            <th>Thursday</th>
						            <th>Friday</th>
						            <th>Saturday</th>
						            <th>Sunday</th>
					            </tr>
				            </thead>
				            <tbody>
					            <tr>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
							            .background(light)<br/>
							            .borders(light)
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td class="SpCalSat" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td class="SpCalSun" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">1</span>
						            </td>
					            </tr>
					            <tr>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">2</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">3</span>
							            badge: background-color: @alert-color;<br/>
							            color: @active-color;<br/>
							            border-color: darken(@alert-color,8%);
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">4</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">5</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">6</span>
						            </td>
						            <td class="SpCalSat" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">7</span>
						            </td>
						            <td class="SpCalSun" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">8</span>
						            </td>
					            </tr>
					            <tr>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">9</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">10</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">11</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">12</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">13</span>
						            </td>
						            <td class="SpCalSat" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">14</span>
						            </td>
						            <td class="SpCalSun" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">15</span>
							            .background(medium)<br/>
							            .borders(medium)
						            </td>
					            </tr>
					            <tr>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">16</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">17</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">18</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">19</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">20</span>
						            </td>
						            <td class="SpCalSat" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">21</span>
						            </td>
						            <td class="SpCalSun" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">22</span>
						            </td>
					            </tr>
					            <tr>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">23</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">24</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">25</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">26</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">27</span>
						            </td>
						            <td class="SpCalSat" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">28</span>
						            </td>
						            <td class="SpCalSun" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">29</span>
						            </td>
					            </tr>
					            <tr>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;">30</span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td class="SpCalSat" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
						            <td class="SpCalSun" style="width: 14%;height: 100px;overflow: scroll;padding: 0!important;">
							            <span class="SpCalDay badge pull-right" style="border-top-right-radius: 0;border-bottom-right-radius: 0;border-top-left-radius: 0;font-size: 13px;margin-bottom: 5px;"></span>
						            </td>
					            </tr>
				            </tbody>
			            </table>
		            </div>
	            </div>
            </div>
        </div>
    </xsl:template>
</xsl:stylesheet>
