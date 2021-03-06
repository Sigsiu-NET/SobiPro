
# Changelog for SobiPro Component

#### @package

SobiPro Component for Joomla!

#### @author

Name: Sigrid Suski and Radek Suski, Sigsiu.NET GmbH  
Email: sobi[at]sigsiu.net  
Url: https://www.Sigsiu.NET  

#### @copyright

Copyright (C) 2006 - 2018 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.  
@license GNU/GPL Version 3  
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 
as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.  
See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

#### Legend:

(*) Security Fix  
(#) Bug Fix  
(+) Addition  
(-) Removed  
(!) Change  


### 1.4.7.3 (11 May 2018)

    (!) Increasing size of notice in fields
    (!) Data columns in language table changed to MEDIUMTEXT (formerly TEXT)
    (!) Base data column in field_data table changed to LONGTEXT (formerly TEXT)
    (!) Data columns in language table changed to utf8mb4_unicode_ci collation also for existing installations
    (!) Base data column in field_data table changed to utf8mb4_unicode_ci collation also for existing installations

	(#) Unpacking of Sobi Framework in case of missing Phar support works now (Issue #80)
	(#) Error 500 instead of 404 for undefined tasks (URL addresses) (Issue #97)
	(#) Sorting categories in category field by position doesn't reflect changes if cache is on (Issue #25)
	(#) No CSS styles loaded after previous cancel of a paid entry form (Issue #93)
	(#) Aborted paid entry form not refilled although within 48 hours (Issue #98)
	(#) Data aren't passed to the notification app when new entry is saved from front-end (Issue #66)
	(#) Custom entry form redirect bypasses custom template functions


### 1.4.7.2 (8 May 2018)

   	(#) Setting incorrect task in listing (Issue #90 and #91)
   	(#) Added paragraph to response message in SAM (Issue #92)
   	(#) Reverted previous commits for #90 and #91 as it didn't help
   	(#) JFolder:: delete: The path is not a folder. (Issue #95)
   	(#) 'Specified key was too long; max key length is 767 bytes' (Issue #96)
   	(#) Removed try to unpack the framework if no Phar support is available, as it does not work (Issue #95)
   	
   	
### 1.4.7.1 (4 May 2018)

   	(!) Includes Sobi Framework 1.0.6


### 1.4.7 (3 May 2018)

    (!) Loading fonts on front-end independent from font usage settings
   	(!) Includes Sobi Framework 1.0.5
   	(!) SobiPro itself does not longer count as application which needs to be updated
	(!) Update link for SobiPro goes to com_installer now
	(!) List of outdated applications opens in cPanel automatically

    (#) Double filtering of textarea (Issue #87)
    (#) Although Allow HTML Code ist set to 'do not filter', the tags are filtered using the filters set in the configuration
    (#) Login procedure does not end normally if no repository is installed
    (#) Call to undefined method Joomla\CMS\Document\RawDocument::addHeadLink(): Call to a member function getString() on null in payment view
    

### 1.4.6 (25 April 2018)

	(+) New template colour @link-color
   	(+) Support for Import of Gallery field (Imex) added
   	(+) Basic API
   	(+) Entry sort order by checkbox fields added

	(!) Improvement of template style 'elevated'
	(!) Improvement of all colour themes
	(!) Default template version V4.1
	(!) Image field is using a framework for image manipulation
	(!) Info field set to not im-/exportable
   	(!) Includes Sobi Framework 1.0.4
   	(!) If uploading a new image for an entry, the old images of this field will be deleted
   	(!) Default JPG quality set to 90%
	(!) Category icon size set to 3 by default
	(!) Collation of all tables changed to utf8mb4_unicode_ci (new installations only)
	(!) Database engine for new installations changed to InnoDB
	(!) Database character set for new installations changed to utf8mb4

	(#) Backslashes are added prior apostrophe to input field in back-end
	(#) Ini file for template override (sptpl) not loaded
	(#) Categories not translated properly in multilingual mode (Issue #63)
	(#) Approved flag not reset after editing an entry (Issue #67)
	(#) Large image was not created if it should not be resized
	(#) Cropped image (cropped_) will be deleted after image processing (temporary file)
	(#) RegEx in URL field corrected
	(#) Windows 10 falsely recognised as old operating system
	(#) Collation changed from utf8mb4_bin to utf8mb4_unicode_ci (Issue #69)
	(#) Correct icon font selected for new SobiPro installation
	(#) Removal of incompatibility with Joomla < 3.8.0
	(#) Development highlighting feature is set on for the example section after installation
	(#) All plugins are being loaded if task contain "list"
	(#) Multiple calls into plugins load method
	(#) alpaindex.xsl wrong "match" definition (Issue #76)
	(#) Field descriptions are now within CDATA (Issue #77)
	(#) Continents in country list are capitalized now (new installations only)
	(#) Data aren't passed to the notification app when new entry is added from front-end (Issue #66)
	(#) Error message instead of warnings if the template's .xml file does not longer exist


### 1.4.5 (30 January 2018)

	(#) Fatal error on Urls without Joomla Itemid set
	(#) Notices in router for Urls without or without valid Itemid
	(#) Notices in back-end if no toolbar class given
	

### 1.4.4 (29 January 2018)

	(+) New ACL rule 'approve.own'
	(+) New ACL rules 'access.expired_any' and 'access.expired.own'
	(+) Expired flag on front-end for expired entries (if user can see expired entries)
	(+) Possibility to define own template colours in the template settings
	(+) Extracting protocol from pasted URL into an URL field and setting the right protocol in the select list
	(+) Additional button colours
	(+) New colour theme 'pastels'
	(+) Show icon next to category name
	(+) Additional template style added: elevated (selectable from template settings)
	(+) Small template improvements of file upload elements
	(+) Information in entry form if a field is administrative
	(+) The Joomla menu page heading (if any) will be shown instead of the SobiPro page heading
	(+) Added support for emojis in field_data and language
	(+) Changed repository SSL certificate added
	(+) Possibility not to load but to use specific fonts

    (!) Extracting Sobi Framework automatically in case PHAR isn't usable
    (!) Separate icon from label in Button field
    (!) Redefinition of theme colours
    (!) SobiPro background and font colours adjustable via template settings
    (!) Default template is now default4
    (!) Several fixes and improvements in the default template
    (!) Field widths in administrator edit entry screen are now responsive
    (!) Category icon handling improved; global category icon added
    (!) Setting 'cacherequest' back to 'post' in Sobi\Input (While changing from SPRequest to Sobi\Input, hardcode method to 'post')
   	(!) Includes Sobi Framework 1.0.3
   	(!) Conditional requirement for /cms/version/version.php (cron jobs no longer worked with new Joomla)
   	(!) Multiselect list height settings for category field changed to size
   	(!) User interface of Paypal payment application improved
   	(!) Number of possible sub-subcategories increased to 30 (not recommended)
	(!) Category Id shown in category edit screen
	(!) Entry Id shown in back-end entry edit screen

    (#) Visitors are treated as users if checking permissions
    (#) Even if user may see own unpublished entry, a newly created entry wasn't shown after saving
    (#) Status explanation popover on front-end does not longer work
    (#) Button bar top menu shows wrong directory name
    (#) Button margins corrected
    (#) Only config.json file of template settings is re-read (Issue #49)
    (#) 'Hide search parameter' template settings wasn't remembered
    (#) Left side of text in Codemirror editor may be not visible
    (#) Handling for image EXIF data with invalid GPS data
    (#) Non-well formed warning while processing GPS coordinates of images
    (#) A duplicated entry is not longer set to unpublished after duplication (Issue #44)
    (#) Deleting a template from within section redirects to section configuration (Issue #45)
    (#) CodeMirror editor now with word-wrap; content-area styles corrected on front-end
    (#) Joomla toolbar not visible in modules when using 3rd party module manager
    (#) Wrong data handling with disabled multi-language mode (Issue #51)
    (#) Handling for CKEDITOR added (Issue #50)
    (#) Display problems when CodeMirror editor is in active tab
    (#) Removing 'canonical' only in case our own was added (Issue #53)
    (#) Responsive search form layout corrected
    (#) Status of 'xml output' does not refelect the status of the setting in the sections
    (#) Missing part of JavaScript translations in backend (Issue #56)
    (#) {payment.methods.html} placeholder doesn't generate correct HTML output for Paypal payment method
    (#) Shown number of characters in textarea with HTML code corrected (if limitation is used)
 	(#) Wrong amount for Paypal payment if tax is set to 0 (Issue #62)
  	 

### 1.4.3 (19 October 2017)

	(#) Saving of XSL files in the template editor corrupts the file (Issue #43)
	

### 1.4.2 (13 October 2017)

	(+) New repository SSL certificate
	
	(!) Modal layout adapted to new certificate type
	(!) Primary category indicator more discreet
	(!) Template information window improved
	(!) XSLT processor: changed from transformToDoc to transformToXml due to issues with "disable-output-escaping"
	(!) General settings for title and description of the forms moved down to template tab
	(!) SQL installation changes added to clean install script too
	
	(#) Missing icons in template manager's save options
	(#) If some SobiPro applications are outdated, the message is shown on each page (should be only the Joomla cPanel page)
	(#) Missing argument 1 for SPFilter::save() (Issue #37)
	(#) Error in Joomla cPanel when SSL certificate expires (Issue #32)
	(#) Wrong template name in duplicated template (Issue #34)
	(#) Changed print_r to var_export in error log output (Issue #36)
	(#) Un-installation message corrected and improved
	(#) Entry approval leads to MYSQL error if calendar field 2.3 installed and used
	(#) Section and category selection in SobiPro modules does not longer work
	(#) 'Notice: A non well formed numeric value encountered'


### 1.4.1 (29 September 2017)

	(-) Old calendar script removed
	
	(!) cropper.js minified
	(!) Template debug options moved to section settings
	(!) Core field versions changed to 1.4
	(!) Improvements of input filter messages
	
    (#) Entire cache is being deleted while saving section settings
    (#) Version checker reports outdated version due to use of an undefinied constant
    (#) Core button field not listed in the list of installed applications
    (#) Purge entries didn't work (Issue #31)
    (#) New categories folder not created; default image moved to categories folder
    
   
### 1.4 (19 September 2017)

	(+) Back-end layout improved and adapted to Joomla! 3.7s
	(+) Back-end Bootstrap Less files evacuated to separate package 'sobiadmin'
	(+) Font Awesome for back-end included in sobiadmin.css; separate version for front-end available (sobifont.css)
	(+) Indicator in back-end for primary category
	(+) Possibility to add entry link to image in vCard
	(+) Autosuggest function extended to general search field
	(+) Autosuggest results	 sorted alphabetically
	(+) Switch off some fields for a certain category (entry fields; views only)
	(+) Script to use map in tabs in entry form added
	(+) Dates, author, url and state of a category available in XML data
	(+) Possibility to delete all entries in a section
	(+) Basic template development support added (fields highlighting)
	(+) Description in entry form can be placed on the right side too
	(+) Possibility to disable check for updates in plugin via config.ini (extensions.check_updates)

	(-) Unused tabs script of SobiPro version 1.0 removed
	(-) Clean-up of loaded scripts in template
	(-) en-GB preload function removed as Joomla 3.7 is always pre-loading the English language
	(-) Support for applications written for SobiPro 1.0 removed
	(-) Legacy mode for old category chooser (1.0) removed
	(-) default3 template for Bootstrap 2 removed (separately available)

	(!) Less folder removed; less files moved to css folder
	(!) Redesign of entries & categories in SobiPro's cPanel
	(!) Font Awesome 4 updated to 4.7.0
	(!) Improved field type info in fields manager
	(!) Category images now located in /images instead /media folder
	(!) Configuration ini files cleaned up
	(!) Changes in b3-default3 template for basic accessibility
	(!) default.less/default.css renamed to custom.less/custom.css
	(!) Layout improvement of payment control in entry form
	(!) Description in entry form uses full width (not longer limited to field width)

	(#) Font Awesome 3 local does not show icons in front-end datepicker
	(#) Solution to overcome Joomla's IcoMoon when Font Awesome 3 is loaded via CDN
	(#) Typo in page-header CSS class
	(#) Section redirect parameters won't be processed if section is unpublished
	(#) Calendar Field in entry form shown wrong (CSS)
	(#) Notice in File/lib/models/field.php: 295
	(#) 'Fill in required fields' message if no value for max. Categories per entry is given
	(#) general class attribute for info field not saved
	(#) Storing left menu state (Issue #2)
	(#) Skipping unnecessary template path detection in cache view (Issue #18)
	(#) Title label for url and email fields not translatable (Issue #28)
	(#) Select list validation doesn't work (Issue #21)
	(#) Wrong arguments order after changing to Sobi Framework (Issue #23)
	(#) Class css-search not output in the template
	(#) Missing input range search not styled
	(#) Range search with suffix not styled
	(#) RSS News work again
	

### 1.3.7 (28 April 2017)

	(!) Moved Sobi Framework inclusion to the loader

	(#) Wrong Ajax definition (switch to Framework) while installing applications from repository (Issue #1791)
	(#) Added exception for search results in router (Issue #1783)
	(#) CSS cache is trying to load CSS files over URL (Issue #1745 again and #1813)
	(#) RSS feeds use general settings (Issue #1802)
	(#) Wrong condition for subject while parsing text node in administrator area (Issue #1801)
	(#) Different directory separators used in path (Issue #1797)
	(#) Overwritten "searchSuggest" method in image field (Issue #1828)
	(#) Switching off transparency detection for images does not work (Issue #1833)
	(#) Codemirror truncated in Joomla 3.7


### 1.3.6 (4 March 2017)

	(#) Installing files to template storage doesn't work correctly
	(#) edit.json settings won't be loaded on editing existing entries


### 1.3.5 (4 March 2017)

	(!) Changed string related functions to multibyte in router and alpha view
	(!) Includes Sobi Framework 1.0.2


### 1.3.4 (1 March 2017)

	(+) Small layout improvement in Entries & Categories Manager
	(+) Title and description for entry and search forms adjustable
	(+) Meta description separator for section, search and entry forms adjustable
	(+) Remembering last selected tab in local storage
	(+) New Google Font 'Dosis' added to both templates
	(+) New toolbar style 'buttonbar'
	(+) New template settings 'Hide search parameters'
	(+) Switch to disable transparency recognition
	(+) Up-to-date checker for SobiPro applications in Joomla! cPanel

	(!) Loading all fonts into the category edit screen
	(!) Search phrases adapted to template style
	(!) Login redirect added to toolbar style 'linkbar'
	(!) Added some of B3-default3 template features to default3 template
	(!) Includes Sobi Framework 1.0.1

	(#) Sorting the select list in search form for input box field (Issue #1762)
	(#) Missing container 'spListing' added to search results
	(#) icons.json not read from current template
	(#) Add to meta keys for checkbox groups fields added the data also to meta description
	(#) Meta description cannot be added to the search and entry forms
	(#) Wrong URL for directly linked entries (Issue #1764)
	(#) Dependency field cannot be saved without allowed selecting parent (Issue #1772)
	(#) Override json file data isn't passsed to the template xml config nodes (Issue #1770)
	(#) Selecting fields in fields manager via checkbox does not distinguish between entry and category fields (Issue #1765)
	(#) Several small template corrections
	(#) Corrected Regex for email field (Issue #1777)
	(#) Deleting temporary update list files after application has been installed

	(*) Added rel="noopener noreferrer" to all user controlled links with target="_blank" @see https://dev.to/ben/the-targetblank-vulnerability-by-example


### 1.3.3 (21 January 2017)

	(+) Category dates available in XML data
	(+) New field 'Button'
	(+) Support for discounts in payment messages
	(+) Raw payment/discount values added to XSL nodes
	(+) Raw payment values (values without currency) available as email placeholders
	(+) Absolute discount support
	(+) Load path 'storage' to load CSS and JS files from the Template Storage
	(+) Width for radio buttons and checkbox groups in search form adjustable
	(+) Categories can be shown in RSS feeds
	(+) Options available in XML for radio buttons and single select list fields
	(+) List of most popular and new categories in CPanel; by default off
	(+) Global setting to switch off showing entries and categories in CPanel
	(+) History/Logging types improved, more actions logged during rejection
	(+) Possibility to sort categories by id, by creation date and by update date in back-end

    (!) Bootstrap 3 updated to version 3.3.7 (local and CDN)
    (!) Font Awesome 4 updated to 4.6.3
	(!) Changed method to determine a super user from authorise( 'core.admin' ) to authorise( 'core.manage', 'com_users' )
	(!) Checkboxes aligned (b3-default3 template)
	(!) Back-end title text revised for some pages
	(!) Predefined title label texts for email and url fields changed
	(!) Url field setting 'open in new window' set to 'no' by default
	(!) First item in protocols list of URL field will be used as default in new entry
	(!) Started moving libraries to Sobi Framework
	(!) Payment screen layout improved
	(!) Select label option of single select lists moved to general settings
	(!) Select label in search form for multiple select lists adjustable
	(!) Option settings of radio buttons and checkbox groups fields moved to general settings
	(!) Categories ordering and categories in line setting moved to template; deprecated message added
	(!) Different pathway items for search parameters and search result
	(!) Improvement of search form (b3-default3) on mobile
	(!) Extended search fields output in default templates moved to a separate template (searchfields.xsl)
	(!) Styles for version comparison screen improved

	(-) Suffix setting removed from image field

	(#) Checkboxes for backend settings are again vertically ordered
	(#) Re-define standard Bootstrap btn link colours (b3-default3 only)
	(#) If no custom label for a URL set, but label given in the settings, it wasn't used (2nd try)
	(#) If no custom label for an email set, but label given in the settings, it wasn't used (2nd try)
	(#) Double row container for categories list removed (b3-default3 only)
	(#) Select list and 0 in name (Issue #1723)
	(#) Dependant list needs to be selected on every search (Issue #1703)
	(#) Syntax error in example data for information field (Issue #1726)
	(#) Cleaning escaped slashes in autosuggest (Issue #1709)
	(#) Exception for index.php in redirects URLs (Issue #1737)
	(#) Missing HTML element in default3 payments template (Issue #1738)
	(#) Wrong hover colour for datepicker buttons in back-end
	(#) No prices on payment screen if VAT is 0
	(#) Discounts not calculated if VAT is 0
	(#) Payment information is not being refreshed after user changes selected options
	(#) Missing distance for dependency list in entry and search forms
	(#) Dependency error message if selecting parents is not allowed, is truncated
	(#) Double select label for dependency select lists in search form
	(#) CSS cache is trying to load CSS files over URL (Issue #1745)
	(#) Dependency select list shows wrong option in views
	(#) Dependency select list shows 0 instead of parent option in views (Issue #1711)
	(#) Don't save a wrong value if dependency select list is unselected
	(#) Search phrase labels missing (Issue #1749)
	(#) Wrong button type for search phrase buttons
	(#) Width for radio buttons and checkbox groups in entry and search form too small
	(#) JSON settings file isn't loaded when using template override
	(#) No possibility to adjust RSS settings for categories
	(#) Missing size and label settings in search form for checkbox groups and radio buttons
	(#) Section meta data are added always to meta data of entry and search forms
	(#) Discard Changes while rejection of an entry never worked
	(#) Comparision library (php-diff) updated to work with PHP7 (Issue #1760)


### 1.3.2 (11 October 2016)

	(+) Message shown if used template does not longer exist
	(+) Auto-generate placeholders for alias fields (nid)
	(+) Changing class names and CSS scope while duplicating a template
	(+) Categories field in categories list
	(+) Support for panels in Bootstrap 3 default template

	(-) Option to choose the side of checkbox and radio buttons removed as not supported by Bootstrap

    (#) Notice in entry form for category field
    (#) Wrong label shown in textarea for categories field settings
    (#) Changes in router for calendar field listing navigation with suffix
	(#) Missing settings for inbox in category fields
	(#) Missing template override in navigation
	(#) If image won't be re-sized/cropped but original available, original wasn't used
	(#) No image type selectable for image field in category view
	(#) Call to undefined method SPDateListing::getDateEntries() (Issue #1707)


### 1.3.1 (31 August 2016)

	(+) Support for SobiPro Template Storage added

    (#) View cache doesn't work with absolute template path
	(#) Select lists show options instead of values
	(#) B/C with Profile Field (Issue #1699)


### 1.3 (28 August 2016)

	(+) Image field: original image name without extension used for alt and title tag
	(+) Possibility to add fields to a category
	(+) Colour styles for Gallery field added
	(+) Possibility to redirect to login page if only registered users can add entries in B3-default3 template
	(+) Support for base fee for an entry added
	(+) Cmd+S and Ctrl+S shortcuts - if applicable will save entry, category, settings
	(+) Coupon field support added to the payment template file
	(+) CSS template scope added
	(+) List of most popular, new and awaiting approval entries in CPanel
	(+) Support for Entries Module added to the templates settings
	(+) ACL to the XML output

	(!) publishing_format set to a more readable format by default
	(!) B3 template folder renamed to 'b3-default3' as path names have to be lowercase
	(!) Payment page shows name of the entry
	(!) Payment page shows name of the section
	(!) Small back-end layout refreshments
    (!) schema file template.xsd updated
    (!) SobiPro's default template is now b3-default3 (Bootstrap 3 version)
    (!) Left column more wide
    (!) Template's abstract class renamed to tplDefault3 to avoid module conflicts
    (!) Tree layout changed
    (!) Directory iterator sorts directories before entries now

	(-) SobiPro template theme 'blackwidow' removed

	(#) textarea width in entry form set to 100% if WYSIWYG is used
	(#) textarea in entry form checked for empty height and set default value
	(#) Entry history screen malformed if no user given (entry renewal requests)
	(#) Codemirror styles in back-end got overwritten by SobiPro
	(#) Missing icon in category data if using font instead of file (#1647)
	(#) Added nid to subcategories nodes (Issue #1648)
	(#) Info field, import method incompatibility with PHP 7 fixed (Issue #1662)
	(#) Info field not available when editing an entry (non superuser only)
	(#) Wrong date format while duplicating a template (Issue #1661)
	(#) Meta description ordering fixed (Issue #1658)
	(#) Duplicated robots in header (Issue #1657)
	(#) Codemirror vanishes after pressing 'Save' (Issue #1659)
	(#) "Option in line" causing invalid modulo operation if is set to all (Issue #1666)
	(#) Joomla! and SobiPro symbol wrong/missing in category edit screen (Issue #1660)
	(#) Several small layout issues in back-end fixed
	(#) Ensuring that no negative limitStart can be passed to a query
	(#) Notices in router (Issue #1678)
	(#) Select List -> dependency method: wrong option displayed (Issue #1694)
	(#) Notices in multilingual mode (Issue #1693)
	(#) Loading behavior.core in editor; causing issue while using CodeMirror (Issue #1687)
	(#) Application's LESS file being compiled even if it doesn't exist yet (Issue #1677)
	(#) Sub-subcategories not visible in category views (Issue #1685)
	(#) Added Windows 10 to trusted operating systems (Issue #1630)
	(#) Router: added slash at the end of particular URLs


### 1.2.4 (30 June 2016)

    (+) Category field: new category selector (populated select list)
    (+) Category field: sorting of categories in entry form adjustable
    (+) Tooltip colours now correspond to base colour for B3-default3 template
	(+) General distance classes for distances on top and bottom in B3-default3 template
	(+) Template hook added on bottom of all views
	(+) Google Fonts selectable from template settings
	(+) Link Bar instead of Top Menu selectable from template settings
	(+) Selected option id in select list and radio button added as attribute into XML output
	(+) Possibility to ignore storing in default language

	(!) Cleaning Joomla! cache - removing only the "page" group
	(!) Field version info moved to 'General Field Settings'
	(!) HTML tags now possible in helptext (field description)
	(!) Added "tmp" in exception while displaying the template's files

	(#) Warnings/notices in SobiPro panel, if no news available from our server
	(#) Version of information field in fields manager missing
	(#) Label and itemprop set result in template syntax error
	(#) Changes in SobiPro content doesn't clear Joomla cache (Issue #1639)
	(#) Compatibility with MySQL 5.7+ (Issue #1638)
	(#) Section selectable in category selector if field is not free (Issue #1633)
	(#) Fatal error in alpha listing when field isn't defined (Issue #1628)
	(#) Wrong order of fields in meta data (Issue #1611)
	(#) Clearing image field data after save in backend (Issue #1641)
	(#) Height of the SigsiuTree container for menu items corrected (Issue #1640)
	(#) Wrong order in aliases in multilingual mode (Issue #1627)
	(#) "finaliseSave" method on category field called even if disabled (Issue #1645)
	(#) Removing empty segment from router (Issue #1373)


### 1.2.3 (30 March 2016)

	(+) Added unpublished sign to unpublished categories
	(+) Additional cache for categories relation in category field
	(+) datepicker CSS improvements (against template overwrites)
	(+) System Report now informs user to scroll down to read all results of the system check
	(+) Possibility to switch off shown categories in front-end (section and category view)

    (!) Improvements in Crawler Cron CLI script
    (!) PNG compression increased
    (!) no page counter in browser title when on page 1

    (#) Forcing template installation doesn't work
    (#) Wrong setting displayed for separate JSON files in template settings
    (#) Not possible to save multi select list (Issue #1591)
    (#) SPFactory::Instance PHP 5.6 compatibility
    (#) Missing method "saveSelectLabel" moved to parent class (Issue #1591)
    (#) SobiPro cache not deleted when edit new template settings (Issue #1588)
    (#) Wrong ACL permission check for managing sections (Issue #1586)
    (#) Empty head line when no font icons selected (Issue # #1593)
    (#) Missing language as a selector in category field cache (Issue #1579)
    (#) Forcing string handling of labels in checkbox and radio buttons (Issue ##1578)
    (#) Wrong languages selected as current while translating (Issue #1572)
    (#) Missing some settings due to ACL implementation (Issue #1575)
	(#) Directory menu item in template's topmenu not set to active if search not available


### 1.2.2 (28 January 2016)

    (+) Possibility to load a local version of Bootstrap 3 with 'SobiPro' namespace
    (+) Information box when install/update SobiPro
    (+) Bootstrap 2 overwrite file for Bootstrap 3 template
    (+) New template theme 'waterfall'
    (+) base font size adjustable via template settings
    (+) Template override for search form
    (+) Possibility to implement fields' specific settings while installing new templates

    (!) Bootstrap 3 updated to version 3.3.6
    (!) SobiPro menu link selection layout improved
    (!) Warning if no year is selected when adding menu link to a listing by date
    (!) Template for display fields now includes the default image
    (!) Layout of back-end ACL settings improved
    (!) Alpha menu title contains now select field name

    (#) Visibility problem with datepicker in modal window
    (#) Wrong condition for while loop in SPJoomlaFs::copy
    (#) Images aren't saved from the back-end (Issue #1552)
    (#) 'State' header text is missing in 'All entries' screen (Issue #1559)
    (#) Bootstrap 2 loaded in B3-default3 template
    (#) Firing "AfterSave" trigger on after update (Issue #1561)
    (#) Several fixes in template installer
    (#) No possibility to translate the "select option" label in select list (Issue #1562)
    (#) Wrong selector for search highlighter (Issue #1563)
    (#) No warning if no entry is selected when adding menu link to entry
    (#) Several issues with backend ACL (Issues #1567, #1568, #1569)
    (#) Missing fields to change position of categories (Issue #1570)
	(#) Autosuggest does not work with B3-default3 template (Issue #1577)



### 1.2.1 (22 December 2015)

    (+) Fallback for wrong path setting in image field
    (+) B2 input size styles implemented in B3 default template
    (+) Tooltip support in default templates
    (+) Coloured tabs and staple tabs support in default B3 template
    (+) Carousel support in default B3 template
    (+) Several features added to the default template (e.g. shorten text, hiding categories, image ratio, hiding extended search button)
    (+) Possibility to load Bootstrap 3 from CDN
    (+) Support for printing
    (+) Backend ACL
    (+) New template theme 'terra'
    (+) Specific SobiPro Icon for Administrator Header
    (+) PHP7 compatibility
    (+) Joomla! 3.5 compatibility
    (+) Selection if the user may delete the click counter of web links (URL field)
    (+) Ajax navigation in listing views

    (-) jQuery-UI library removed

    (!) Less influences of Joomla! template to Theme template
    (!) Local Font-Awesome 3 icons without SobiPro scope
    (!) Datepicker attached to body for Bootstrap 3 compatibility
    (!) Field labels now within CSS class
    (!) Font Awesome 4 updated to version 4.5.0
    (!) Small CSS improvements in backend
    (!) Editing the fields in default templates moved to a separate template (editfields.xsl)
    (!) jQuery updated to 1.11.3

    (#) Missing default template configuration files added
    (#) Duplicate icons from Font Awesome 4 removed
    (#) Only one information field in a section works correctly
    (#) Small bux fixes in default template files
    (#) Suffix layout in search view in B3-default3 template wrong
    (#) Multiple language nodes in SobiPro App definition were ignored
    (#) Information field isn't installed during update (Issue #1535)
    (#) Information field does not duplicate output data
    (#) Less file 'Compile & Save' now 'Save & Compile'
    (#) Missing message type after entry has been saved
    (#) Buffer output not cleared for the payment screen when submitting an entry
    (#) URL reverted while changing menu settings (Issue #1538)
    (#) Missing existence check while copying files in image field (cloning) (Issue #1534)
    (#) Removing double quotes from user names (Issue #1516)
    (#) Issue with non-latin aliases (Issue #1515)
    (#) Globally disabled application still enabled in sections (Issue #1514)
    (#) All fields in backend entry form don't get the set width
    (#) Payment notifications aren't sent after entry edition (Issue #1507)
    (#) New template was loaded before Bootstrap with CSS cache on (styling issues)

    (*) Unregistered user with permission to edit "own" entry can access the edit form of entries without assigned owner



### 1.2 (28 October 2015)

    (+) Possibility to define a separator sign for meta descriptions from fields
    (+) Required state of a field as node attribute in XML data available
    (+) Additional CSS class node attributes for fields in add/edit form (css-edit), details view & vCard (css-view) and search form (css-search)
    (+) Category alias as node attribute in details view
    (+) Possibility to load different font icons (Material Icons, Font Awesome 3 and 4)
    (+) Possibility to load no icon font in frontend
    (+) Possibility to select font icons for categories
    (+) Possibility to add condition attributes into toolbar's elements in administrator area
    (+) LESS compiler for SobiPro templates
    (+) New default template (default3) with different colour themes
    (+) New default template for Bootstrap 3 (B3-default3) with different colour themes
    (+) Different default class selectors for fields
    (+) Nofollow attribute for url field
    (+) Possibility to switch the field label off in entry form
    (+) Possibility to add the label as placeholder into the input element for inbox, textarea (not Wysiwyg), email and url fields
    (+) Template specific settings
    (+) Language installer for templates
    (+) Definition file for used icons
    (+) Default values for inbox, textarea, select list, multiple select list, checkbox group, email and url fields
    (+) Highlighting search keywords in search results
    (+) Possibility to override input fields renderer in the template
    (+) New core field 'Information' to output HTML text in entry form and/or DV and/or vCard
    (+) Setting to switch HTML output format from Bootstrap 2 to Bootstrap 3
    (+) Implementation of Bootstrap 3 HTML output (e.g. modals, grids, forms, input fields)
    (+) Adjustable search field width for category, select, multiselect and inbox fields (span, col)
    (+) APC cache in front of SQLite in SobiPro Accelerator
    (+) Bootbox for Bootstrap 3

    (!) Template styles from SobiPro.css moved to template's css file
    (!) New tree icons
    (!) New Paypal logo
    (!) Removed MYISAM setting from the tables where it is not necessary
    (!) Curl test URL changed
    (!) Bootstrap responsive CSS file added by default
    (!) Layout improvements for range search
    (!) Default view templates of SobiPro functionality not longer selectable in Joomla! menu manager
    (!) SobiPro section view functionality in Joomla! menu manager moved to first position
    (!) Set image crop function off by default
    (!) Header plugin version updated
    (!) Field width settings changed from px to Bootstrap classes (span, col)
    (!) SigsiuTree is responsive now
    (!) New repository server and certificate
    (!) preg_replace /e changed to be PHP7 compatible
    (!) Throwing ErrorException on fatal errors
    (!) Intelligence test refined. Was too hard to get it.

    (-) Category icons removed
    (-) Joomla! 1.5 code completely removed
    (-) Joomla! 2.5 support dropped

    (#) Fixed issues in SigsiuTree and Internet Explorer (Issue #1469)
    (#) Passing comma separated values to the SPHtml_Input::select (Issue #1481)
    (#) Removing duplicates meta keys (Issue #1476)
    (#) Approved value in the front-end when user is accessing unapproved version (Issue #1472)
    (#) Non-editable field cannot be set as required; it should be set as not required after it has been used once (Issue  #1457)
    (#) HTML style error in range search corrected
    (#) Crop function doesn't work under Joomla! higher than 3.4.1 (Issue #1487)
    (#) Wrong query in header functions fixed
    (#) Default option id for Select/Multi-Select/Checkbox/Radio fields set to option-id
    (#) Problem when editing a select field using dependency list which is a required field (Issue #1496)
    (#) Cloning images while entry is being saved "as copy" (Issue #1494)
    (#) Category field warnings in the search (Issue #1492)
    (#) Wrong message in image field if cropping is disabled (Issue #1486)
    (#) Payments and history of an entry not being deleted when entry is being deleted (Issue #1491)
    (#) Deleting section related data from ACL while deleting a section (Issue #1491)
    (#) Issues with alias for an entry fixed (Issue #1468)
    (#) Missing user id in request params for unique cache creation (Issue #1453)
    (#) 'Valid until' reset after each edit (Issue #1452)
    (#) Wrong error code if page has not been found (Issue #1440)
    (#) Issues with image cropper while uploading same image (name) into two different fields (Issue #1431)
    (#) Checking for width and height size in image field when crop or resize is enabled (Issue #1445)
    (#) Image handler is adding transparency to images that originally don't have it and removes the background
    (#) Permission "to access any unpublished entry" is being overridden by the permission to access own unpublished entries (Issue #1439)
    (#) Information messages don't appear on redirection to non-SobiPro URLs (Issue #1366)
    (#) Second search ordering by counter doesn't work (Issue #1495)
    (#) Preventing "multiple select list" as a search method while dependency option is enabled
    (#) rGetChilds method limited to entries only
    (#) Static call into JSite::getMenu() has been removed
    (#) Leveraging Joomla! files' protection (Issue #1506)
    (#) New typeahead library for Bootstrap 3 (Issue #1500)
    (#) White screen in SobiPro panel if news couldn't be fetched



### 1.1.13 (31 March 2015)

    (+) Possibility to prevent choosing parent options in the select field with dependency list (Related to #1421)

    (!) Showing the fields in default2 template moved to a separate template

    (#) Valid since / valid until time issues while creating new ACL rule (Issue #1415)
    (#) Dependency field don't work in search view (Issue #1417)
    (#) Notices in function fixTimes() (Issue #1423)
    (#) Access own unpublished rule hide other users entries (Issue #1419)



### 1.1.12 (28 February 2015)

    (+) Possibility to define template functions globally
    (+) Implemented "dependency" functionality for single select lists

    (#) Image field didn't appear on payment page (Issue #1375)
    (#) Original image is being overwritten with cropped image
    (#) Size of News container corrected
    (#) Fixed issues with time and time offset
    (#) Help URL corrected
    (#) Itemprop attribute not being displayed in field edit form (Issue #1392)
    (#) Not possible to access entries in front end due to time offset (Issue #1389)
    (#) Path issues on Windows "servers" (Issue #1365)
    (#) Added IE11 to valid browsers list (Issue #1368)
    (#) Allowing unicode group names in select lists (Issue #1364)
    (#) Redirection message is missing (Issue #1366)
    (#) Disabling time check in cache while adding an object and "force" parameter set to true
    (#) SobiPro menu link looses function if edited (Issue #1397)
    (#) Pre-selecting categories in SigsiuTree to prevent "O items selected" output on iOS (Issue #1367)
    (#) Task transformation doesn't work for alpha index (Issue #1362)
    (#) URL click counter counts only once (Issue #1283)
    (#) "Entry update" not fired while updating an entry from administrator area
    (#) View Cache is not restoring task for the $_REQUEST array (Issue #1321)
    (#) Loading language from XML administrator definition file
    (#) Pending/expired entries not visible in category when user has permission to see those (Issue #1354)
    (#) Not possible to add an image with an uppercase file extension (Issue #1404)
    (#) Removing empty <ul> list from the multiselect field if no data are selected



### 1.1.11 (28 October 2014)

    (+) Possibility to define parent container for datePicker
    (+) Image Field functionality extended: possibility to crop an image

    (#) Messages queue overflow
    (#) Category field causes 500 error if contains comma separated data
    (#) SobiPro redirects are causing a 303 code
    (#) DatePicker converts timestamp to a scientific notation on some servers; forcing integer output
    (#) Router - not possible to use dash in the in config defined alias (Issue #1322)
    (#) Creation date of the duplicated entry is the same as the original (Issue #1328)
    (#) If in datepicker all time options are disabled it is removing also 'now' and 'clear' buttons (Issue #1346)
    (#) Wrong date when Joomla! time zone is different than server time (Issue #1348)
    (#) Wrong results for range search (Issue #1343)
    (#) SigsiuTree, category field: disabled possibility to select (click) additional category as long as the previous wasn't added (Issue #1347)
    (#) Wrong language used if 'en-GB' pre-load function is disabled (Issue #1352)
    (#) Pending entries aren't visible in front-end (Issue #1354)

    (!) Changed deprecated JException to core Exception


### 1.1.10 (28 July 2014)

    (+) "Save & New" functionality for entries and categories
    (+) Fixing image rotation according to the EXIF data

    (!) Label "Save as New" to "Save as Copy"
    (!) Changing all db dates to UTC; Warning! can cause a glitch in the matrix
    (!) Dates like "valid until, since, etc" are displayed in 'in Joomla! set' timezone by default

    (#) Router not copied during installation process
    (#) Changed language recognition method (compatibility with Joomla! 3.3.2)
    (#) Bug in SPHeader::addMeta fixed
    (#) Parent category in add entry form not pre-selected (Issue #1299)
    (#) Limiting chars for textarea doesn't work correctly with Hebrew chars (Issue #1293)
    (#) Image field - EXIF data - wrong coordinates calculation
    (#) Double title in administrator area (Issue #1306)
    (#) Apostrophes are not correctly escaped in Sub-categories names (Issue #1308)
    (#) Textarea not saving changes when using Code Mirror (Issue #1303)
    (#) Field in loop conditions check was executed twice and due to wrong subject the second one fails


### 1.1.9 (03 July 2014)

    (+) SEF router now included in SobiPro directly
    (+) Warning about missing menu item to SobiPro section view

    (!) Requirements checker is getting current version from Joomla updater extension
    (!) Some icons in administration panel improved (datepicker)
    (!) Changed order of meta keys and meta description (Issue #1231)
    (!) Image field - changing all file extensions to lower case now
    (!) Repository SSL Certificate information renewed

    (#) Rejection messages corrected
    (#) Corrected attributes handling in SPHeader::meta()
    (#) Discard changes for a new entry (Issue #1221)
    (#) Extended exceptions to include "entry.delete" in router (#Issue)
    (#) Workaround for a bug in JApplicationCli
    (#) Crawler accesses operational tasks (Issue #1226)
    (#) 'Now' in the datepicker sets wrong hour (Issue #1223)
    (#) Not possible to use more than one SigsiuTree categories chooser (Issue #1220)
    (#) Category field set to be an administrative field is loosing data when user edits the entry (Issue #1219)
    (#) Incorrect results while ordering by popularity (counter) (Issue #1267)
    (#) Incorrect results while ordering by title in multilingual mode (Issue #1258)


### 1.1.8 (30 March 2014)

    (#) Cache improvement removed for Joomla 2.5


### 1.1.7 (28 March 2014)

    (+) Possibility to define list of exif data to pass to the XML output
    (+) Possibility to pass parameters to the editor
    (+) Possibility to delete a date from datetimepicker (Issue #1208)

    (!) Improved exif data cleaning method (again) (Issue #1205)
    (!) Limiting the search results to 1000 by default (adjustable in config.ini)
    (!) Several improvements in the search functionality
    (!) CSS style for alert messages
    (!) Improved Joomla! cache management (not caching session token)
    (!) Added several tasks (entry submit, delete, approve, publish, ...) to be ignored by the crawler

    (#) Possibility to define the Itemid and pass it to SobiPro::Url (Issue #1206)
    (#) Limiting the "explode" in crawler to separate body from header to two pieces (Issue #1213)
    (#) Method SPDBObject::getChilds limits type to entries only.


### 1.1.6 (28 February 2014)

    (+) Possibility to define list of parameters to pass from the search function to the search results page

    (!) Improved exif data cleaning method
    (!) Uncompressed js files removed from packages; separate package available
    (!) Bootstrap javascript plugin files removed as already included in the main Bootstrap file

    (#) Problems with searching when field is set to multi selection in the search (Issue #1187)
    (#) Newly created categories are not approved by default (Issue #1183)
    (#) New Approval in Entry detail does not fire EntryAfterChangeState and EntryAfterApprove (Issue #1184)
    (#) Apostrophes are not correctly escaped in section name (Issue #1188)
    (#) Wrong path definition in the cronjob script
    (#) Several fixes in the crawler
    (#) Special characters in regex not escaped (Issue #1189)
    (#) ACL permission "see unpublished entries" has no effect (Issue #1192)
    (#) Category field in fixed choice allow to select section id (Issue #1191)
    (#) Problem with creating Joomla! menu item (Issue #1166)
    (#) Inputbox search method single select list and multilingual mode (Issue #1193)


### 1.1.5 (28 January 2014)

    (+) "Now" selector in date-time picker
    (+) CLI crawler script to re-create cache from command line and/or cron job
    (+) Entry Approve button in administration area

    (!) Background colour of Datepicker and Userselector buttons changed in backend
    (!) Font Awesome updated to version 3.2.1
    (!) JQuery updated to v1.10.2
    (!) Unpublished entries are now shown in Joomla! menu manager (to be able to link to them)

    (#) Changed timestamp getter from integer to double to avoid problems on old 32 bit systems (Issue #1143)
    (#) Removed short open tag in the administrator/components/com_sobipro/default.php file (Issue #1141)
    (#) Improved the default date-time picker (Issue #1137)
    (#) Editing entries with image field set as required (Issue #1017)
    (#) Pathway creation improved (Issue #1147)
    (#) Approval status is displayed to unauthorised users (Issue #1150)
    (#) repository.xml won't be overwritten on update
    (#) Replacing '_' with '-' on update (options)
    (#) Preventing entries id being used as parent id (Issue #1157 and #1158)
    (#) Minor fix for Firefox in CSS file of default template
    (#) Wrong language identifier for field labels while editing field definition (Issue #1161)
    (#) URL field set as required doesn't work correctly (Issue #1160)
    (#) Deleting category without selection deletes current category (Issue #1162)
    (#) Missing 'Percent Formats' in Global Configuration added
    (#) Missing text for SP.EX.CORE_PLUGIN
    (#) Comma as decimal mark translation corrupted (Issue #1016)

    (*) Section listings missing the ACL check (Issue #1177)


### 1.1.4 (28 December 2013)

    (+) Added support for schema.org in field model and default templates
    (+) Passing exif data to the XML output in image field
    (+) Timestamp to the URL of search results site to prevent caching by the Joomla! page cache plugin (adjustable in config.ini)
    (+) Moderate mode for history changes in the administrator area

    (!) Changed bootstrap datepicker to datetimepicker
    (!) Textarea field with allowed HTML input ignores HTML markup while checking the allowed length
    (!) Changed Joomla! menu handling; added possibility to load new options dynamically
    (!) Improved default template (Thanks to Robert Vining)
    (!) Approval of unpublished entries triggers automatically "publish" action (adjustable in config.ini)
    (!) Added spinner in administrator area

    (#) Missing error text for FILE_WRONG_TYPE added.
    (#) Additional parameters in some fields are being destroyed
    (#) No requirement to pass selected categories while editing existing entry (Issue #1106)
    (#) Wrong config key for the default phrase (Issue #1104)
    (#) Templates are being installed (copied) even if requirements are not satisfied
    (#) Missing Site name in the browser title if set in Joomla! configuration (Issue #1088)
    (#) Missing argument 3 for SPField_Category (Issue #1079)
    (#) Added legacy layer for multiple predefined data (Issue #1078)
    (#) Special characters in field label not escaped (Issue #1087)
    (#) When entry history is disabled, editing entries in backend keeps asking for reason (Issue #1077)
    (#) Preventing invalid category id in category field when set to fixed choice (Issue #1075)
    (#) Allowing float values in range search (Issue #1074)
    (#) Reverse title key fixed (Issue #1067)
    (#) Double directory separator in the 'SOBI_MEDIA_LIVE' definition (Issue #1062)
    (#) Translation method returns NID instead of name (Issue #1060)
    (#) Storing right template in XML cache when template override is enabled (Issue #1064)
    (#) Unapproved images are being displayed for unauthorised users (Issue #1095)
    (#) Joomla! cache is being frequently deleted
    (#) Disabling Joomla! cache in search results
    (#) View cache broken
    (#) Entry meta data is not translatable (Issue #1110)
    (#) Missing labels for different objects when a label is entered in a language which isn't used later
    (#) Missing header title in administrator area (Issue #1086)
    (#) Missing template override in pagination
    (#) Added timestamp to several URLs administrator links to leverage browser caching


### 1.1.3 (21 October 2013)

	(+) History of changes of an entry
	(+) Rejection functionality
	(+) Version management
	(+) Support for custom templates override while adding menu item
	(+) Possible placeholders for path in image field extended; {id}, {orgname}, {entryname}, {oid}, {ownername}, {uid}, {username}
	
	(!) Default input style for URL and Email field improved
	(!) Header output moved to a separate plugin
	
	(#) Backend search for entry returns categories (Issue #1022)
	(#) Error while installing Joomla! native extensions in SobiPro (Issue #1013)
	(#) Add menu entry to an entry in Joomla 2.5 (Issue #1042)
	(#) Preventing saving of custom ordering when nothing has been selected (Issue ##1041)
	(#) Entries without valid category assigned are not visible in 'All Entries' screen (Issue #1037)
	(#) Approving changes from the front-end with multilingual mode (Issue #1027)
	(#) Backend search for entry redirects to main section view (Issue #1022)
	(#) Editing entries with image field set as required (Issue #1017)
	(#) Category Name as Alias in SobiPro 1.1.2 (Issue #1015)
	(#) Destination folder not found while updating SobiPro (Issue #1014)
	(#) Missing hidden "showIn" option in category field (Issue #1047)
	(#) Wrong pathway when entry is linked directly in the menu (Issue #1046)
	(#) No data sent to check when using CodeMirror (Issue #1048)
	(#) Email field: corrected path replacement to fit to Windows directory separator (Issue #1045)
	(#) Missing file extension in image field if not using "orgname" placeholder (Issue #1058)
	(#) Quotes in the payment descriptions are not escaped (Issue: #1057)


### 1.1.2 (22 August 2013)

	(+) Calling Joomla! Cache cleaner after SobiPro cache has been invalidated
	(+) Keep-Alive method to avoid session expire
	(+) Possibility to store ordering of lists in administrator area permanently
	(+) Information about entry's status while displaying unapproved/unpublished version in default template
	(+) Storing currently displayed entries into 'user state'
	(+) Method for table creation in the database driver
	
	(!) Style revisions to default2 template and SobiPro frontend CSS
	(!) php-prefix exclusion added to all templates in default2 template package
	(!) Section information added to site title in administrator area
	
	(#) Wrong parameter in AlphaListingAfterGetEntries Trigger (Issue #964)
	(#) JavaScript file uploader preserving expired form (Issue #928)
	(#) Category field set to non-editable with "fixed choice" method do not allow edit entries (Issue #966)
	(#) Wrong directory separator in path comparison for Ajax file upload exception (Issue #930)
	(#) Missing select list nodes in a multi select list with list's groups (Issue #953)
	(#) Template method "BeforeStoreEntry" gets data array in administrator area but name of the request in frontend
	(#) Missing texts for apps added
	(#) No category/section title when page number has been added
	(#) Error when trying to add entry in backend with a filter set (Issue #990)
	(#) Image field as required field doesn't work (Issue #974)
	(#) Alpha search field selector missing the field alias (Issue #980)
	(#) Category icons on Windows machines (Issue #997)
	(#) Publishing entry from frontend - cache issue (Issue #1000)
	(#) Entry not checked in while hitting "exit" in administrator area
	(#) Entry approve in frontend gives success message in a warning message type container
	(#) Fixed several validation errors in default2 RSS feed template
	(#) Missing prefix in SPSectionCtrl::userPermissionsQuery for some particular permissions (Issue #1002)
	(#) SPLang::translateObject is getting nid from the wrong table (Issue #1006)
	(#) Language files are not being removed while uninstalling particular language. (Removed SOBI_ROOT from the log for all apps) (Issue #1007)
	(#) Language files aren't checked for requirements
	(#) Wrong URL in the RSS feeds when Joomla! is installed in subdirectory (Issue #1011)
	(#) Click counter data are not re-validated when XML cache is enabled (Issue #1010)
	(#) Passing un-serialised data through un-serialise config method (legacy) (Issue #Chad's bug)


### 1.1.1 (1 July 2013)

	(+) Added jquery-migrate for jQuery backward compatibility
	
	(#) Removing spaces from CSS paths in cached files
	(#) Bootstrap not loaded in administrator area when "prevent bootstrap loading" is activated
	(#) Declaration of SPFrontView::setTemplate() incompatible with the interface
	(#) Missing argument 3 for SPField_Category::searchNarrowResults()
	(#) en-GB preload not enabled by default
	(#) Typo in English language file


### 1.1.0 (28 June 2013)

	(+) Possibility to prevent bootstrap CSS loading
	
	(!) Larger JS and CSS files are minimised now
	(!) Bootstrap CSS updated to version 2.3.2
	(!) Font Awesome updated to version 3.1.0/3.1.1
	(!) JQuery updated to v1.10.1
	(!) Optional parameter added to SPJoomlaLang::replacePlaceHolders to remove empty placeholders
	
	(#) Fixed language code replacement for the section crawler (Issue #890)
	(#) Fixed missing parent id in entry
	(#) Non-unicode aliases (Issue #901)
	(#) Fixed installation of clean and full version on J!1.5
	(#) Fixed wrong path replacement on Windows (Issue #891)
	(#) Some complex parameters aren't transferred to a string while editing configuration (Issue #894)
	(#) Added AfterGetEntries trigger in the Alpha Listing Controller
	(#) Browser title generated by SobiPro is not longer added to a custom title set from template
	(#) While in section/category view in administrator area, the "All Entries" menu is highlighted as active
	(#) XML parser was generating Itemid for all loops in administrator area
	(#) Changed attr method to prop in serial actions (interface.js) as jQuery 1.10 returns "unknown" for the "checked" attribute
	(#) No label for objects (e.g. section drop'n'down menu) when it has been stored in a "dead" language
	(#) Disabled core update button was clickable
	(#) Checkbox group and radio fields aren't properly validated due to exception in validation method (Issue #910)
	(#) Multilanguage mode in directory crawler fixed
	(#) Entry counter doesn't work when cache is enabled
	(#) Escaping field suffix
	(#) XMLCall error fixed
	(#) Trigger in search called before the priorities have been rearranged
	(#) Cached view missing current template name; override via template package doesn't work
	(#) security redirection for specific files only if mod_rewrite available
	(#) Width of image upload field reduced; setting from configuration removed
	(#) fixed width of file upload progress bar and message removed
	(#) CSS tweaks for URL field in default2 template


### 1.1.0 Beta3 (10 May 2013)

	(+) Added support for LESS in Codemirror
	(+) Extended the search.suggest task with the possibility to select a particular field for the search
	(+) Added message with reserved words to fields defining ini-file format lists
	
	(!) Default textarea width reduced
	(!) Removed Joomla! 3.1 canonical URL (a bit dirty solution)
	(!) Removed site counter from browser's title when there is only one page (Issue #877)
	(!) Category field of method 'fixed choice' cannot be set to required
	
	(#) Shifted arguments in URL field for SPRequest::word call (Issue #861)
	(#) While narrowing search down and if the previous search has no results, the search behaves as if there were no search params (to get a category search only)
	(#) Scroll bar for template tree added and overflow hidden removed (Issue #867)
	(#) Overflow hidden removed for SigsiuTree (Issue #868)
	(#) Some XML attributes in administrator templates are being translated by default (Issue #865)
	(#) Joomla! icon font was overwritten in frontend (J!3 only)
	(#) Loading bootstrap in Joomla! 3.x if template hasn't (Issue #880)
	(#) Visits counter fixed (Issue #875)
	(#) Wrong path replacement for category chooser (Issue #873)


### 1.1.0 Beta2 (15 April 2013)

	(+) Joomla! version dependency to the repository data
	
	(#) Using method_exists on a string in admin view (Issue #841)
	(#) The request cache is overriding the global $_REQUEST (Issue #835)
	(#) XML cache file deletion of non-existent files (Issue #853)
	(#) Calling non existing SEF URL causes 500 internal server error (Issue #849)
	(#) Field aliases are not lowercase (Issue #855)
	(#) The SobiPro.Ready JavaScript method is not being called when format is set to 'html' (Issue #844)
	(#) "Parent Category selectable" does not work for SigsiuTree method (Issue #839)
	(#) Search function access level "No Access" for public (Issue #846)
	(#) SPHtml_Input::radioList does not accept '0' as label (Issue #858)
	
	(-) Image field not storing temporary data any longer while using ajax uploader
	(-) Automatic upload of files with file uploader


### 1.1.0 Beta1 (30 March 2013)

	(+) XML based template engine for administrator templates
	(+) Possibility to override administrator templates for particular sections
	(+) Definition file for Radio Button field
	(+) Field controller implements now a proxy pattern to the field type
	(+) Category Chooser Field with different category selection methods implemented
	(+) Possibility to define a primary category
	(+) Prohibition of assigning an entry to parent category added to new Category Chooser Field
	(+) Frontend Category Search to filter the search results by one or more categories
	(+) IP search query link added to category and entry publishing IP (admin interface)
	(+) Button to hide left menu in order to stretch the content screen (admin interface)
	(+) Field description shown in backend 'Edit Entry' as popover
	(+) 'Sort Entries by Field' reversible for frontend and backend sorting
	(+) Direct Entry Selection via Joomla! Menu Manager
	(+) Template override for specific categories and entries via Joomla! Menu Manager
	(+) Workaround to bypass Joomla! menu type bug
	(+) Possibility to define exact type of custom output including header content type, exit and clear
	(+) Possibility to limit XML raw data to a specific IP
	(+) Joomla! 3.0 compatibility (needs at least Joomla! 3.0.2)
	(+) Auto-suggest feature for search function
	(+) Excluding particular user groups for administrator templates override
	(+) Found issues and state of important settings on Control Panel
	(+) Second ordering for entries in search function
	(+) Possibility to delete (instead of unpublish) an entry from frontend
	(+) View and menu link for own entries
	(+) View for all entries of an specific user
	(+) View for all entries added within a particular date (year/month/or exact day)
	(+) View and menu link for entries added in a particular date period
	(+) Range search for inputbox with free defined value input
	(+) URL field - ability to count and display number of visits/clicks for the particular link
	(+) Category and Entry Alias for URL
	(+) Meta data for specific add entry and search view
	(+) Possibility to update/override a template package
	(+) Custom redirect for accessing add entry form without permission
	(+) Custom redirect for accessing entry details view without permission
	(+) Custom redirect after a new entry has been added
	(+) Possibility to define new permission to allow or disallow search function usage
	(+) Custom redirect for accessing search function without permission
	(+) XML cache view implemented; XML output is stored into a XML file and reused in next request if there were no changes
	(+) Possibility to disable pre-load of "en-GB" language files
	(+) Current page number of pagination to the browser title
	(+) Added full text indexes to fields' data and language table
	(+) Search priority to XML results
	(+) Possibility to define different template methods (see default template) to plug-in an action between (and whilst) submit and save entry actions
	(+) System checker added all status messages in en-GB
	(+) Section crawler - possibility to trigger complete cache creation
	(+) Simple translation method for sections, categories, entries and fields from administration panel
	
	(-) Vehicles template removed; will be a separate template now
	(-) SobiPro Admin Menu Module removed; sections available now on a menu list
	
	(!) Administrator UI redesigned using Bootstrap library and new XML templates
	(!) SPHeader::addMeta() takes now optional list of custom parameters as an array
	(!) SPLang::nid() uses JFilterOutput::stringURLSafe now
	(!) Category and entry Meta Robots parameter selection improved (admin interface)
	(!) Category and entry publishing information extended (admin interface)
	(!) Field description now allows HTML code
	(!) Default settings while creating a new section changed
	(!) CodeMirror upgraded to version 2.36
	(!) jQuery upgraded to version 1.8.2
	(!) jQuery UI updated to version v1.9.2
	(!) Category selector for default Business Directory changed to Category Field
	(!) No fix MyISAM database type on installation
	(!) Sendmail account information removed from system check file
	(!) Category meta description does not longer ends with a dot
	(!) No default filter for fields title, contact and city in demo data
	(!) CSS and Javascript cache now separate settings
	(!) single .htaccess files removed, global file added
	(!) Default Template now using Bootstrap; several improvements; new name
	(!) JavaScript translation method speed improvement
	(!) IMPORTANT!! The URL contains now the nid/alias instead of the title; that way the generated URL can be better controlled but it also changes all existing URLs
	(!) Alias/nid (beside fields) is now translatable
	(!) Counter of an object has been moved to a separate table due to performance issues (update statement deletes MySQL cache for a particular table)
	(!) 'Bank Transfer' app renamed to 'Offline Payment'
	
	(#) {entry.url} placeholder doesn't work (Issue #708)
	(#) Typo in text label in Entry Manager
	(#) getChilds() in model does not care about the child type
	(#) Meta author corrected to 'author'
	(#) Suffix not shown in backend
	(#) Approved state for category has no effect
	(#) Meta author and robots not added to header (Issue #734)
	(#) Options of select list or checkbox fields will be deleted now if the field is deleted
	(#) Duplication of a field more than once does not increment the alias counter
	(#) Missing id for inbox fields in search form
	(#) Non-closed string (url) in CSS cache while converting file path
	(#) Not possible to transform url paths in CSS cache if more than one url per line
	(#) Column count doesn't match value while installing a new payment App
	(#) Workaround for non-latin characters in alpha index for problems in strtoupper
	(#) Missing the section/category title in the page navigation URL
	(#) Wrong date used for createdTime while submitting new entry (GMT instead of server time)
	(#) Wrong date used for updatedTime while saving an entry (GMT instead of server time)
	(#) Wrong date used for validUntil while saving an entry from frontend (GMT instead of server time)
	(#) Session id passed to URL in case the cookie cannot be set for some reason (Time zone problems in Chrome) while adding/editing an entry
	(#) Select list returns no results when searching for "exact phase" (Issue #801)
	(#) Email field data invisible when Data Accelerator is enabled and data has been cached by a bot
	(#) Template installer: complex fields' options are incorrect while installing new section (e.g. selected views in SP-GeoMap field)
	(#) Cache not cleaned after an ACL rule has been saved
	(#) Empty values for range search passed to the search method (Issue #806)
	(#) Clean cache from outside of the SobiPro extension didn't worked
	(#) Removing entry_row from the cache failed
	(#) Bug #832 - NOT LIKE creates fatal error
	(#) Uncaught exception when CURL isn't installed in the administrator main panel


### 1.0.8 (6 August 2012)

	(!) Added "token" to placeholders in SPlang::replacePlaceHolders method
	(!) Changed method for discovering the right return point
	(!) SobiPro Repository Certificate data renewed
	
	(#) Plugged Apps wasn't able to trigger own actions. Caused for example that the payment method wasn't delivered to the notification App (Issue #598)
	(#) Missing entry data in entry.payment task (SPEntry::checkCopy method - entry.payment added to exceptions)
	(#) UTF-8 chars destroyed in PayPal App - SPPPaypal::content - htmlentities removed (Issue #637)
	(#) Entry AfterApprove trigger doubled SPEntry::save():637 (Issue #630)
	(#) Disabling links verification for alpha index crash SobiPro front-end (Issue #626)
	(#) Non static data missing in search results (Issue #627)
	(#) Missing section identifier in language values (method screen) in PayPal and Bank Transfer Apps (Issue #635)
	(#) Wrong SQL-query in alpha listing for unicode characters (Issue #638)
	(#) Error reporting didn't really worked .... and no one realised
	(#) URL in addObjToPathway method were passed through htmlentities. (Issue #692)
	(#) Place-holder {entry.url} does not contain the site URL when used from front-end (Issue #646)


### 1.0.7 (28 April 2012)

	(!) Updated jQuery to v1.7.2
	(!) Updated jQueryUi to 1.8.18
	
	(#) Missing section id in SPController::parseOrdering
	(#) Problems while adding entries with disabled cache (Issue #619)
	(#) Fixed several problems with strtolower and non-latin characters
	(#) Changes made by administrator are being overwritten while approving an entry from the administrator form (Issue #620)
	(#) Changes made by unauthorized users being auto-approved
	(#) Missing translatable text added (Issue #623)


### 1.0.6 (28 March 2012)

	(+) Possibility to force numeric ordering in the text fields
	(+) Alpha Index - support for multiple select list and checkbox group
	(+) Possibility to define tasks in config.ini to disable the content parser
	(+) Protocol for structural data in config keys; supported: "csv://", "json://", "serialized://"
	(+) Triggering state changes and approval while saving an entry
	(+) Possibility to force the parent category id for an entry in section view
	
	(!) Content parser disabled in entry.submit and entry.payment tasks; adjustable in config.ini (Issue #600)
	(!) Putting results of Sobi::GetUserState back to the request
	(!) dTree script now compressed
	(!) Added output encoding to all default templates
	(!) Deleting template package from template directory after installation
	(!) Approving all language versions while approving an entry - need to finish multilanguage mode first
	(!) template.xsd schema file updated
	
	(#) New subcategories not visible in list while using SobiPro Data Accelerator (Issue #581)
	(#) Alpha Index with select list doesn't work (Issue  #592)
	(#) Email and URL fields - prevent displaying empty labels
	(#) Fields data not approved while changing entry state from the edit entry form in administrator area (Issue #576)
	(#) Incomplete entries visible in list if an entry is not approved but user has permission to see unpublished entries (Issue #597)
	(#) CSS compression conflicts with files override - when compression is enabled always the main file is taken instead of the template version of the file
	(#) Site number missing in the canonical URL
	(#) PHP bug #47370 affects only version 5.2.9 and not previous; changed the workaround condition
	(#) Problem while installing apps on Windows machines; folders are not being created due to wrong path separator in explode
	(#) Fields which are set as non-editable, aren't saved during add entry process; after saving entry, field is empty.(Issue #610)
	(#) Wrong path separator in icons for categories on windows server (Issue #579)
	(#) File max size in image field has no effects (Issue #614)
	(#) Wrong initial parameters for entries and categories ordering in administrator area
	(#) Cannot enable editor buttons; Joomla! requires explicit a boolean value
	(#) Notice: Undefined property: stdClass::$publishDown/$publishUp in /lib/models/field.php on line 708
	(#) Alpha Index with non-latin characters on Windows servers returns empty urls (Issue #615)
	(#) Navigation does not work correctly in Alpha Index with alternative field
	(#) URL field "Validate URL" setting does not work


### 1.0.5 (28 January 2012)

	(+) Method "SFormatDate" to the template helper
	
	(!) SPFactory::db()->getQuery() to replace Joomla! db prefix
	(!) Recommended Joomla! version to 2.5
	
	(#) Fields options are missing in Template installer
	(#) Field fee is loosing the decimal place (Issue #588)
	(#) Not all xml specific tags are being removed from the HTML output
	(#) Re-ordering entries and categories with multiple pages doesn't work correctly (Issue #580)
	(#) Cache not deleted if re-ordering entries and categories in administrator area
	(#) Default language file (en-GB) for apps is not loaded
	(#) No field id for email and url fields in default edit/add entry templates


### 1.0.4 (10 January 2012)

	(+) Template installer: added support for additional multiple options
	
	(!) Switched off fields cache because not longer necessary
	(!) General Search method renamed to "via input field only"
	(!) Template JavaScript and CSS files are loaded at the end now - possible to override JavaScript functions and CSS classes in template files
	
	(#) Cache not deleted correctly (caused by fix of Issue #522)
	(#) Select lists and radio buttons aren't shown if paid fields and data accelerator on
	(#) Select lists and radio buttons aren't shown if auto-approval is off
	(#) Undefined index: adminField, editLimit while installing template with a section definition
	(#) Template JavaScript and CSS files override doesn't work if the JavaScript/CSS cache is enabled
	(#) Entry approval in administrator area - only one category is being approved and additionally each time one unapproved relation is being deleted
	(#) New, unapproved entries are not visible for users having the permissions to see unpublished entries
	(#) Ordering entries by a single select list - wrong tables join


### 1.0.3 (28 December 2011)

	(+) Performance improvements in administrator area
	(+) Support for language files in sub folder in Apps installation packages
	
	(!) Non-existing URL raises 404 return code now
	
	(#) SPPlugins::trigger - in the emergency break the current trigger counter wasn't decreased so after jump out - no action was triggered
	(#) Search order not through field priority (Issue #564)
	(#) Wrong parameter passed to the error message function in multi value fields - There is no option '%s' in field '%s'.
	(#) If category description contains a plugin code Joomla! throws error: 500 Unable to load renderer class
	(#) Double prefix in image field (Issue #556)
	(#) Wrong URL created for entries while in administrator area (Issue #554)
	(#) No error handling when problems with connection to repository
	(#) SPImage::resample destroys GIF images
	(#) Switching object cache while submitting or storing an object (Issue #522)
	(#) Counter is not being refreshed
	(#) Cleaning entities in fields output (Issue #568)
	(#) Error while removing Joomla! native extensions via SobiPro apps installer in Joomla! 1.6/7 (Issue #571)


### 1.0.2 (2 December 2011)

	(#) Entry name disappearing (Issue #558)


### 1.0.1 (28 November 2011)

	(+) Field alias in fields list
	(+) jQuery.noConflict - always after jquery has been included
	
	(!) Visibility of newly created fields set to "details" instead of "both"
	(!) Renaming Object Cache to 'SobiPro Data Accelerator'
	(!) Disabling cache if no SQLite support available
	(!) jQuery updated to version 1.6.4
	
	(#) Minor HTML bugs in several administrator templates
	(#) ACL rule 'access own unpublished' doesn't work for lists (Issue #521)
	(#) MySQL seems to store sometimes 0000-00-00 00:00:00 as 1970-01-01 00:00:00; cannot find the reason :(
	(#) Parse category description; "Global" always reverts to "No" (Issue #517)
	(#) Select List field: wrong data selected for un-approved entries (Issues #529 #522)
	(#) Edit item in relation to non-editable fields (Issue #532)
	(#) Fixed word_filter for search to include unicode characters (Issue #535)
	(#) Disabled categories visible in category chooser (Issue #539)
	(#) Undefined variable: cfile In file lib/base/header.php in line 147 (Issue #541)
	(#) Can't clear search inputbox (Issue #520)
	(#) Duplicate template: new id wasn't created
	(#) Problem with creating cache directory while FTP mode is enabled
	(#) SobiPro mailer doesn't work when Joomla! is set to use SMTP
	(#) Sorting entries by select list field (Issue #542)
	(#) Entries hits counter doesn't refresh dynamically with cache enabled (Issue #547)
	(#) Clean cache is executing SQLite query without checking if cache is enabled


### 1.0 (28 September 2011)

	(+) isOutputOnly and isInputOnly handling for fields
	(+) SPPlugins::registerHandler() method with Sobi::RegisterHandler() alias
	(+) Token - pass the post/get param
	(+) Possibility to override almost all config INI files. config_override.ini <=> config.ini
	(+) Possibility to override all administrator templates files. entry_override.php <=> entry.php
	
	(!) Var JavaScript files - including original name
	(!) Apps do not longer create language folders
	(!) No HTML filter for super admin
	(!) Inbox field - trimming data
	(!) Removed PHP short tags (Issue #512)
	
	(#) $noId parameter not passed to SPSectionView::cachedEntry()
	(#) Triggering a custom list task in entry view (VC) independent from current task (Bug #507)
	(#) SPRemote::_construct() - For some reason on certain PHP/CURL version it causes error if $url is null
	(#) Problem when field alias exist twice (in other section i.e.) while sorting by this field
	(#) If field is set to administrator field after an entry has been edited all data from this field are being deleted
	(#) get_defined_constants(true) causes white screen (no response error) under PHP 5.3 - seems to be a PHP bug
	(#) Image field - images aren't deleted while entry is being deleted
	(#) Un-approved entries (changes) not visible in search results
	(#) updates.xml not deleted after core update
	(#) SPHtml_Input::checkBoxGroup() and SPHtml_Input::select() - problem when selected value is 0
	(#) Inbox field in search - "select" label was overwritten with empty data
	(#) RSS feeds links in default templates
	(#) Edit entry button visible sometimes for unregistered users. (Issue #506)
	(#) Workaround for public users having SU permissions in Joomla! 1.6/1.7
	(#) Accordion menu in Control Panel not translated into other languages (Bug #510)


### 1.0 RC5 (9 August 2011)

	(!) Preventing numeric-only option ids in select list
	
	(#) Not possible to autopublish an entry
	(#) Problem with default language recognition in Joomla! 1.5
	(#) Multi lang mode for predefined data fields works now
	(#) Expired entries still visible when accessing the details view directly
	(#) Multiple select list - missing data when re-editing entry
	(#) Undefined variable: tid while installing new extension
	(#) Cache not cleaned after "un-approve" entry
	(#) DOMDocument::createElement() unterminated entity reference in /lib/types/array.php:134


### 1.0 RC4 (1 August 2011)

	(+) Codename
	(+) Warning when using default template
	(+) Exception for MS doc files in SPFileInfo
	(+) Extended template installer - possibility to add categories, requirements and additional settings
	(+) Div Container for default Details View Template and CSS style code
	(+) Warning if giving administrative permissions to an un-registered user
	(+) Env data to the system check
	(+) Real cURL checker
	(+) Default (current) XML-Schema definition to the package (for installations without outgoing Internet connection)
	(+) Token function passed to the template functions
	(+) Exec file in template installer
	(+) Possibility to override App's CSS files in the template package
	(+) Possibility to override App's JavaScript files in the template package
	(+) Own icon for the expired entries in administrator area
	(+) Second cache layer to hold data image
	(+) Title filter for entries in the "all entries" listing in administrator area
	(+) Mata data (keys, description) are translatable now
	(+) Canonical links to the site
	
	(!) Skipping unique field data exception if duplicating an entry
	(!) Highlighting non existing fields in the fields manager
	(!) New language handling; Multi-language mode switch added
	(!) Changed MT JavaScript to JQ in search.js within default templates
	(!) Cache: (Try) Workaround for Windows Servers; dropping tables instead of deleting db files
	(!) Removed cp checker in the administrator area
	(!) Changed MT JavaScript to JQ in alpha.js within default templates
	(!) Application loader doesn't throw a fatal error if doesn't exists
	(!) Default navigation template - changed behaviour while displaying many sites
	(!) Unification of XML fields output (CSS and data)
	(!) Newly created section shows default template in the SobiPro menu even if the configuration wasn't saved
	(!) Limiting number of displayed entries in admin area while exhausting memory
	(!) Usertype for un-registered users set to 'Visitor'
	(!) Moved object creation into the views - object can be destroyed in the loop and free the memory
	(!) Default Template: small CSS changes
	(!) Joomla! version detection
	(!) Small changes for Joomla! 1.7
	(!) Recommended Joomla! 1.6/1.7 version set to 1.7.0
	
	(#) Cache / SQLiteDatabase - checking for class_exists('SQLiteDatabase') instead of the function; causing GPL License violation
	(#) Multi-choice fields - loosing selected options if there were no changes for approval pending
	(#) Radio buttons of search phrases on wrong side
	(#) While initializing field type in admin area and the special administrator definition doesn't exist, field type is not being loaded (template fields creation i.e.)
	(#) Requirements checker - missing revision
	(#) "input-xml" added into SPTemplateXSLT::repairHtml() - changed in-line styles into CSS class
	(#) Missing field nid in the CSS class in inherited fields
	(#) Replacing & - to &amp; in SPHtml_Input::checkbox()
	(#) Currency separator in field edit function
	(#) Image field - not possible to change the width
	(#) Undefined index: installed - In File: lib/views/adm/extensions.php at line 83 Requested URI: extensions.manage
	(#) Unpublished field doesn't appear in alpha index additional fields list
	(#) Checkbox group and radio field - label on the wrong site
	(#) Section categories/entries counter fixed
	(#) Multi-language problems while accessing fields data
	(#) Wrong language in menu selection (sections names always in default language)
	(#) Not possible to save default language values in fields
	(#) parse_ini_file in the requirement checker fixed
	(#) No error message when not possible to create remote connection while adding new repository
	(#) Some errors are still logged even if the debug level is lower than the error type
	(#) Not possible to add full URL into the redirects
	(#) Deprecated CURLOPT_MUTE option removed
	(#) Wrong language label in ACL rule editor
	(#) Wrong URL for SobiProAdmUrl if Joomla! 1.6 installed in sub-directory
	(#) Alpha Listing: letter in page navigation URL is lower case now
	(#) Multiple db entries for mod_spmenu in Joomla! 1.6 after installation
	(#) The work-around for PHP 5.2.9 bug disturbs perms getter on PHP lower than 5.2.9
	(#) Skipping HTML editor initialization if working in raw mode (work-around for Joomla! 1.6 bug)
	(#) Fit Joomla! 1.6 user (author) selector
	(#) Alpha Listing - wrong url in the navigation while using optional field
	(#) (Dev) Not possible to get entry name while storing an entry for the first time (Bug #482)
	(#) Not possible to redirect to 'index.php'
	(#) Range search - not possible to search for float values
	(#) Missing field suffix while editing entry in administrator area
	(#) Wrong return point when performing administrative operation in administrator area in entries list
	(#) Error Message: Undefined variable: fileData in requirements checker
	(#) Resetting cache after update
	(#) JavaScript and CSS cache is not being reset after changes
	(#) Joomla! content plugin not triggered in details view - running in the plugins overload
	(#) Suffix shown for empty data in default template
	(#) No Label and Suffix shown for some fields (e.g. image)
	(#) Default option name in option groups of select lists
	(#) Multi-language mode - several labels aren't translated
	(#) URL/Email fields - quotes not cleaned in the field output
	(#) Wrong labels in the category children for name - getting field label instead sometimes
	(#) Not possible to overwrite the details view output (XML for example)
	(#) Field: Select List and Checkbox group - empty options are always selected
	(#) Labels of disabled fields are visible in the admin area while editing an entry
	(#) Field: Multiple Select List - options group is not sortable
	(#) Field: Image - cannot set to display original image. Typo orginal/original
	(#) Date in config class - formatting error. Passing timestamp through strtotime
	(#) Field Image: Typo in XSL attribute thumbail -> thumbnail
	(#) Missing usertype in XML output in Joomla! 1.6
	(#) No possibility to use Joomla! settings in the mailer class
	(#) Missing 'editLimit' in fields
	(#) Fields select and multi select list - width in the search form incorrect
	(#) Field "select" (and inherited) - no db table given in the approval method
	(#) Wrong node in the default template - seeking for section instead of category
	(#) In Joomla! 1.6/7 the registered user group id in sample data is was wrong
	(#) JavaScript/CSS compression - improved the regular expression to translate relative path to absolute
	(#) When unapproved data in a field fits better to the current language - these data were displayed
	(#) Fields multi select list and checkbox group - while editing an entry the real selected data (unapproved too)
	(#) Cache deletion was language depend
	(#) Removed &nbsp; from the payment settings in global configuration
	(#) Fields: multi-select list and checkbox group - several errors in the SQL-clause
	(#) Alpha listing controller is seeking for approved entries only
	(#) Wrong id (once 0 once the field id) while inserting language depend option of a field
	(#) Removed unused setting 'Select Label' from multiple select list settings
	(#) Cache - clean all function doesn't clear all files but only expired
	(#) Fields: multi-select list and checkbox group - several errors in the SQL-clause
	(#) Undefined index: msg in base/mainframe.php - string is also an array
	(#) Core updater seeking for type "extension" instead "component"
	(#) Exception handler - calling backtrace only if class already imported
	(#) Wrong labels for categories in details view - getting field label instead sometimes
	(#) Cannot break/continue 1 level in lib/ctrl/adm/sobipro.php
	(#) Field: Textarea missing params and option col - data in default language shifted in the db
	(#) Field: all fields with selectable options - labels are always overwritten in the current administrator language while editing field definition
	(#) SPTemplateXSLT::repairHtml does not return the repaired node
	(#) Wrong default language recognition
	(#) Administrator recognition for Joomla! 1.6/7 using core ACL now
	
	(*) Possibility to delete an entry without necessary permissions
	(*) ACL permission may not work depend on error reporting settings
	
	(-) Categories and entries counter in the front in administrator area - causes time-out on sites with many entries


### 1.0 RC3 (16 May 2011)

	(+) Template file "Save As"
	(+) Obligatory constructor in SPTemplateXSLT class
	(+) Possibility to define XML translation file for template
	(+) Updater data in entry edit form in admin area
	(+) Template info shown in section template
	(+) jQuery-UI and autocomplete libraries
	(+) Search suggest method
	(+) Adjustment in header to use it from outside
	(+) Possibility to define templates language overrides file
	(+) Count method for templates
	(+) parse_ini_file to the requirements checker
	(+) JavaScript and CSS files acceleration
	(+) JavaScript compression
	(+) Factory method for entry model
	(+) URL is now included in the entry model
	
	(!) Catching attempts to access non-existent files
	(!) Setting tmpl to component while cleaning buffer and reseting Joomla response body
	(!) Default debug level to 2
	(!) Switching to jQuery case possible
	(!) Apps installer doesn't throw error if trying to re-install an App
	(!) Layout of News in Control Panel
	(!) Backend styles to fit Joomla! 1.5 and 1.6
	(!) Moved some texts to text file
	(!) Workaround to prevent Joomla! "prepare content" trigger in admin area
	(!) Added inclusion path clean method
	(!) Textarea field - nl2br if no HTML allowed
	(!) Excluding index.html files from templates editor
	(!) Help icon
	
	(#) New menu entry: Notice: Use of undefined constant SOBI_ADM_FOLDER in /lib/cms/joomla_common/base/mainframe.php on line 288
	(#) External initialisation function with section id sets the objects as an id
	(#) PHP Fatal error:  Call to undefined method SPCmsInstaller::error() in /lib/cms/joomla_common/base/installer.php on line 223
	(#) Regex replacer for the URL safe method doesn't work
	(#) Error while copying directories and path is terminated by dir separator
	(#) General template parser always enabled
	(#) Problems with Joomla! in subdirectory
	(#) Bug in default template while displaying one entry in a row
	(#) Joomla! 1.6 txt.js include script - escaping ampersands
	(#) General template parser enabled in edit entry form
	(#) Missing icon in category
	(#) Fixed path (double slash) in icon path
	(#) URL passed ordering worked for registered users only
	(#) Repository browser - missing return point
	(#) Not possible to delete repository
	(#) Field Manager - state change message not translated
	(#) Bug in app installer when subdirectories deep is larger than 2
	(#) Sobi::Init - missing const class (SPC) inclusion
	(#) Wrong labels in field type while editing special field type
	(#) Several fixes for MT 1.2.5
	(#) Alpha listing while searching for range and SEF is activated
	(#) Not possible to load css file from media directory in template
	(#) Catching non-existing fields for Alpha-Index
	(#) Some typos in language files
	(#) Class 'SPField_Inbox' not found in textarea.php - if textarea is the first field in a section
	(#) Backend SigsiuTree - translating URL to SEF
	(#) JavaScript form validator failed with tooltip (SobiPro.htmlEntities)
	(#) Alpha listing: navigation URL not conform
	(#) URL string replacer doesn't work for upper case non-ASCII characters
	(#) Wrong RSS-Feed ordering in default templates
	(#) *Task Apps always active within a section
	(#) Cannot write to file \tmp\edit\2011-04-27_05-04-00_::1\post.var on Windows (IPv6)
	(#) Wrong data passed to alpha listing controller for "alpha_field"
	(#) Missing entry title within URL in details view
	(#) Missing some images in Joomla! 1.6 - admin images are now in template
	(#) Typo and wrong label setting for checkbox and radio button fields
	(#) Select list - problem with HTML-entities in the value
	(#) CheckBox Group - all options pre-selected when returning to add entry function
	(#) Removed multiple id from fields output
	(#) Default templates - several JavaScript and CSS/XSl bugs in IE8/9
	(#) Special characters in meta keywords converted to htmlentities
	(#) Entry model doesn't work in autonomous mode - missing right section ID
	
	(*) db::update - value not escaped
	(*) SPRequest::cmd - wrong filter definition


### 1.0 RC2 (1 April 2011)

	(+) Joomla! plugins and modules can be installed and un-installed in SobiPro Apps Manager - SobiPro tag required
	(+) Added SobiPro version in to the system check log file
	(+) Whole template output can be passed through the Joomla! content plugin
	(+) Possibility to backup files modified by an App
	(+) Possibility to install an Update-App
	(+) Possibility to revert files modified by an App
	
	(!) After an entry or category has been duplicated the copy state is set to "disabled"
	(!) Escaping dot (.) in the search function while using regex
	(!) Changed Joomla! recommended version to 1.5.22 / 1.6.1
	
	(#) Class 'SPLang' not found in lib/models/field.php ( Added in rev 1069 after RC2 release )
	(#) SigsiuTree - selected category not highlighted in Chrome
	(#) Bug #444 (UTF-8 encoding) Using a field as meta-data
	(#) Bug #442 UNAUTHORIZED_ACCESS while accessing admin panel / PHP 5.2.9 Issue
	(#) Bug #364 Field Type = email does not display in Joomla! front-end
	(#) JavaScript messages are not being translated
	(#) SqueezeBox (Filter editor) in Joomla! 1.6 doesn't work
	(#) JavaScript errors (wrong live url) when installed in a sub-directory
	(#) Bug #454 Javascript message problem if Joomla! 1.6 installed in sub directory
	(#) Admin module: JavaScript error if section name contains single quote
	(#) Not possible to add a repository in Joomla! 1.6
	(#) Cars & Vehicles payment template shows HTML code
	(#) Not possible to save input filter
	(#) Missing argument 2 for SPSectionCtrl::userPermissionsQuery(), called in /components/com_sobipro/lib/ctrl/search.php
	(#) ACL - "Edit Any" permission doesn't work
	(#) Temporary JavaScript file for the edit entry function - wrong params for the file name
	(#) Wrong root URL when Joomla! is installed in sub-directory
	(#) Templates config merge doesn't work
	(#) Bug in the default templates when displaying more entries in one row
	(#) Update Installer for Joomla! 1.6
	(#) Possibility to overwrite the section data - an entry has been saved as a section
	(#) Entry payment screen not shown with SEF URL
	(#) JavaScript translation method in the admin area doesn't work
	(#) ACL - "Skip Payment" permission doesn't work
	(#) Checked out icon doesn't show in Joomla! 1.6
	(#) Bug #380: templates - extended search container is partially hidden
	(#) Cancel button while cloning a template doesn't work
	(#) Several bugs in the application installer
	(#) Field un-installer doesn't remove the field type
	(#) JavaScript frontend file loaded in backend when using language other than en-GB
	(#) Error reporting and debug level wasn't restored correctly
	(#) Deleting repository without choosing a repo to delete removes all repositories
	(#) Wrong URL to the Joomla! user edit function on Joomla! 1.5
	(#) Error log navigation doesn't work right
	(#) Frontend language file for Apps not loaded in the admin area
