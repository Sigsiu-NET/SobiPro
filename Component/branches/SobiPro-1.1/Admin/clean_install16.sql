CREATE TABLE IF NOT EXISTS `#__sobipro_category` (
  `id` int(11) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `description` text,
  `parseDesc` enum('0','1','2') NOT NULL DEFAULT '2',
  `introtext` varchar(255) NOT NULL,
  `showIntrotext` enum('0','1','2') NOT NULL DEFAULT '2',
  `icon` varchar(150) DEFAULT NULL,
  `showIcon` enum('0','1','2') NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_config` (
  `sKey` varchar(150) NOT NULL DEFAULT '',
  `sValue` text,
  `section` int(11) NOT NULL DEFAULT '0',
  `critical` tinyint(1) DEFAULT NULL,
  `cSection` varchar(30) NOT NULL,
  PRIMARY KEY (`sKey`,`section`,`cSection`)
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__sobipro_config` (`sKey`, `sValue`, `section`, `critical`, `cSection`) VALUES
('l3_enabled', '1', 0, 0, 'cache'),
('dec_point', ',', 0, 0, 'payments'),
('currency', '€', 0, 0, 'payments'),
('format', '%value %currency', 0, 0, 'payments'),
('vat', '7', 0, 0, 'payments'),
('vat_brutto', '1', 0, 0, 'payments'),
('discount_to_netto', '0', 0, 0, 'payments'),
('percent_format', '%number%sign', 0, 0, 'payments'),
('allowed_tags_array', 'YToxNzp7aTowO3M6MToiYSI7aToxO3M6MToicCI7aToyO3M6MjoiYnIiO2k6MztzOjI6ImhyIjtpOjQ7czozOiJkaXYiO2k6NTtzOjI6ImxpIjtpOjY7czoyOiJ1bCI7aTo3O3M6NDoic3BhbiI7aTo4O3M6NToidGFibGUiO2k6OTtzOjI6InRyIjtpOjEwO3M6MjoidGQiO2k6MTE7czozOiJpbWciO2k6MTI7czoyOiJoMSI7aToxMztzOjI6ImgyIjtpOjE0O3M6MjoiaDMiO2k6MTU7czoyOiJoNCI7aToxNjtzOjI6Img1Ijt9', 0, 0, 'html'),
('allowed_attributes_array', 'YTo4OntpOjA7czo1OiJjbGFzcyI7aToxO3M6MjoiaWQiO2k6MjtzOjU6InN0eWxlIjtpOjM7czo0OiJocmVmIjtpOjQ7czozOiJzcmMiO2k6NTtzOjQ6Im5hbWUiO2k6NjtzOjM6ImFsdCI7aTo3O3M6NToidGl0bGUiO30=', 0, 0, 'html'),
('show_pb', '1', 0, 0, 'general'),
('xml_raw', '0', 0, 0, 'debug'),
('display_errors', '0', 0, 0, 'debug'),
('level', '2', 0, 0, 'debug');

CREATE TABLE IF NOT EXISTS `#__sobipro_errors` (
  `eid` int(25) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `errNum` int(5) NOT NULL,
  `errCode` int(5) NOT NULL,
  `errMsg` text NOT NULL,
  `errFile` varchar(255) NOT NULL,
  `errLine` int(10) NOT NULL,
  `errSect` varchar(50) NOT NULL,
  `errUid` int(11) NOT NULL,
  `errIp` varchar(15) NOT NULL,
  `errRef` varchar(255) NOT NULL,
  `errUa` varchar(255) NOT NULL,
  `errReq` varchar(255) NOT NULL,
  `errCont` text NOT NULL,
  `errBacktrace` text NOT NULL,
  PRIMARY KEY (`eid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_field` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(150) NOT NULL,
  `adminField` tinyint(1) DEFAULT NULL,
  `admList` int(10) NOT NULL,
  `dataType` int(11) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `fee` double DEFAULT NULL,
  `fieldType` varchar(50) DEFAULT NULL,
  `filter` varchar(150) DEFAULT NULL,
  `isFree` tinyint(1) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `priority` int(11) NOT NULL,
  `required` tinyint(1) DEFAULT NULL,
  `section` int(11) DEFAULT NULL,
  `multiLang` tinyint(4) DEFAULT NULL,
  `uniqueData` tinyint(1) DEFAULT NULL,
  `validate` tinyint(1) DEFAULT NULL,
  `addToMetaDesc` tinyint(1) DEFAULT NULL,
  `addToMetaKeys` tinyint(1) DEFAULT '0',
  `editLimit` int(11) NOT NULL DEFAULT '0',
  `editable` tinyint(4) NOT NULL,
  `showIn` enum('both','details','vcard','hidden') NOT NULL DEFAULT 'both',
  `allowedAttributes` text NOT NULL,
  `allowedTags` text NOT NULL,
  `editor` varchar(255) NOT NULL,
  `inSearch` tinyint(4) NOT NULL DEFAULT '1',
  `withLabel` tinyint(4) NOT NULL,
  `cssClass` varchar(50) NOT NULL,
  `parse` tinyint(4) NOT NULL,
  `template` varchar(255) NOT NULL,
  `notice` varchar(150) NOT NULL,
  `params` text NOT NULL,
  `defaultValue` text NOT NULL,
  `version` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  KEY `enabled` (`enabled`),
  KEY `position` (`position`),
  KEY `section` (`section`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_field_data` (
  `publishUp` datetime DEFAULT NULL,
  `publishDown` datetime DEFAULT NULL,
  `fid` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `section` int(11) NOT NULL DEFAULT '0',
  `lang` varchar(50) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL,
  `params` text,
  `options` text,
  `baseData` text,
  `approved` tinyint(1) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT NULL,
  `createdTime` datetime DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdIP` varchar(15) DEFAULT NULL,
  `updatedTime` datetime DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `updatedIP` varchar(15) DEFAULT NULL,
  `copy` tinyint(1) NOT NULL DEFAULT '0',
  `editLimit` INT( 11 ),
  PRIMARY KEY (`fid`,`section`,`lang`,`sid`,`copy`),
  KEY `enabled` (`enabled`),
  KEY `copy` (`copy`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_field_option` (
  `fid` int(11) NOT NULL,
  `optValue` varchar(100) NOT NULL,
  `optPos` int(11) NOT NULL,
  `img` varchar(150) NOT NULL,
  `optClass` varchar(50) NOT NULL,
  `actions` text NOT NULL,
  `class` text NOT NULL,
  `optParent` varchar(100) NOT NULL,
  PRIMARY KEY (`fid`,`optValue`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_field_option_selected` (
  `fid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `optValue` varchar(100) NOT NULL,
  `params` text NOT NULL,
  `copy` tinyint(1) NOT NULL,
  PRIMARY KEY (`fid`,`sid`,`optValue`,`copy`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_field_types` (
  `tid` char(50) NOT NULL,
  `fType` varchar(50) NOT NULL,
  `tGroup` varchar(100) NOT NULL,
  `fPos` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`tid`,`tGroup`),
  UNIQUE KEY `pos` (`fPos`)
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES
('inbox', 'Input Box', 'free_single_simple_data', 1),
('textarea', 'Text Area', 'free_single_simple_data', 2),
('multiselect', 'Multiple Select List', 'predefined_multi_data_multi_choice', 3),
('chbxgroup', 'Check Box Group', 'predefined_multi_data_multi_choice', 4),
('select', 'Single Select List', 'predefined_multi_data_single_choice', 7),
('radio', 'Radio Buttons', 'predefined_multi_data_single_choice', 8),
('image', 'Image', 'special', 9),
('url', 'URL', 'special', 10),
('category', 'Category', 'special', 11),
('email', 'Email', 'special', 12);

CREATE TABLE IF NOT EXISTS `#__sobipro_language` (
  `sKey` varchar(150) NOT NULL DEFAULT '',
  `sValue` text,
  `section` int(11) DEFAULT NULL,
  `language` varchar(50) NOT NULL DEFAULT '',
  `oType` varchar(150) NOT NULL,
  `fid` int(11) NOT NULL,
  `id` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `options` text,
  `explanation` text,
  PRIMARY KEY (`sKey`,`language`,`id`,`fid`),
  KEY `sKey` (`sKey`),
  KEY `section` (`section`),
  KEY `language` (`language`)
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__sobipro_language` (`sKey`, `sValue`, `section`, `language`, `oType`, `fid`, `id`, `params`, `options`, `explanation`) VALUES
('bankdata', '<p>Payment Subject: "Entry in the {section.name} at the {cfg:site_name}. Entry id {entry.id}"</p>\r\n<ul>\r\n<li>Account Owner: Jon Doe </li>\r\n<li>Account No.: 8274230479 </li>\r\n<li>Bank No.: 8038012380 </li>\r\n<li>IBAN: 234242343018 </li>\r\n<li>BIC: 07979079779ABCDEFGH</li>\r\n</ul>', 1, 'en-GB', 'application', 0, 1, '', '', ''),
('ppexpl', '<p>Please click on the button below to pay via Paypal.</p>\r\n<p> </p>', 1, 'en-GB', 'application', 0, 1, '', '', ''),
('ppsubject', 'Entry in the {section.name} at the {cfg:site_name}. Entry id {entry.id}', 1, 'en-GB', 'application', 0, 1, '', '', '');

CREATE TABLE IF NOT EXISTS `#__sobipro_object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(255) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT NULL,
  `counter` int(11) NOT NULL DEFAULT '0',
  `cout` int(11) DEFAULT NULL,
  `coutTime` datetime DEFAULT NULL,
  `createdTime` datetime DEFAULT NULL,
  `defURL` varchar(250) DEFAULT NULL,
  `metaDesc` text,
  `metaKeys` text,
  `metaAuthor` varchar(150) NOT NULL,
  `metaRobots` varchar(150) NOT NULL,
  `options` text,
  `oType` varchar(50) DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  `ownerIP` varchar(15) DEFAULT NULL,
  `params` text,
  `parent` int(11) DEFAULT NULL,
  `state` tinyint(4) DEFAULT NULL,
  `stateExpl` varchar(250) DEFAULT NULL,
  `updatedTime` datetime DEFAULT NULL,
  `updater` int(11) DEFAULT NULL,
  `updaterIP` varchar(15) DEFAULT NULL,
  `validSince` datetime DEFAULT NULL,
  `validUntil` datetime DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `oType` (`oType`),
  KEY `owner` (`owner`),
  KEY `parent` (`parent`),
  KEY `state` (`state`),
  KEY `validSince` (`validSince`),
  KEY `validUntil` (`validUntil`),
  KEY `version` (`version`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_payments` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `refNum` varchar(50) NOT NULL,
  `sid` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `dateAdded` datetime NOT NULL,
  `datePaid` datetime NOT NULL,
  `validUntil` datetime NOT NULL,
  `paid` tinyint(4) NOT NULL,
  `amount` double NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`pid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_permissions` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(150) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `value` varchar(50) NOT NULL,
  `site` varchar(50) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`pid`)
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES
(1, '*', '*', '*', 'front', 0),
(2, 'section', '*', '*', 'front', 0),
(3, 'section', 'access', '*', 'front', 1),
(4, 'section', 'access', 'valid', 'front', 1),
(5, 'section', 'access', '*', 'front', 0),
(6, 'category', '*', '*', 'front', 0),
(7, 'category', 'access', 'valid', 'front', 1),
(8, 'category', 'access', '*', 'front', 1),
(9, 'entry', '*', '*', 'front', 1),
(10, 'entry', 'access', 'valid', 'front', 1),
(11, 'entry', 'access', '*', 'front', 1),
(12, 'entry', 'access', 'unpublished_own', 'front', 1),
(13, 'entry', 'access', 'unapproved_own', 'front', 0),
(14, 'entry', 'access', 'unpublished_any', 'front', 1),
(15, 'entry', 'access', 'unapproved_any', 'front', 1),
(16, 'entry', 'add', 'own', 'front', 1),
(17, 'entry', 'edit', 'own', 'front', 1),
(18, 'entry', 'edit', '*', 'front', 1),
(19, 'entry', 'manage', '*', 'front', 1),
(20, 'entry', 'publish', '*', 'front', 1),
(21, 'entry', 'publish', 'own', 'front', 1),
(22, 'entry', 'adm_fields', '*', 'front', 0),
(23, 'entry', 'adm_fields', 'see', 'front', 0),
(24, 'entry', 'adm_fields', 'edit', 'front', 1),
(25, 'entry', 'payment', 'free', 'front', 1);

CREATE TABLE IF NOT EXISTS `#__sobipro_permissions_groups` (
  `rid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  PRIMARY KEY (`rid`,`gid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_permissions_map` (
  `rid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  PRIMARY KEY (`rid`,`sid`,`pid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_permissions_rules` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `nid` varchar(50) NOT NULL,
  `validSince` datetime NOT NULL,
  `validUntil` datetime NOT NULL,
  `note` varchar(250) NOT NULL,
  `state` tinyint(4) NOT NULL,
  PRIMARY KEY (`rid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_plugins` (
  `pid` varchar(50) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `version` varchar(50) NOT NULL,
  `description` text,
  `author` varchar(150) DEFAULT NULL,
  `authorURL` varchar(250) DEFAULT NULL,
  `authorMail` varchar(150) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `depend` text NOT NULL,
  UNIQUE KEY `pid` (`pid`,`type`)
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES
('bank_transfer', 'Bank Transfer', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'payment', ''),
('paypal', 'PayPal', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'payment', ''),
('chbxgroup', 'Check Box Group', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('email', 'Email', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('image', 'Image', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('inbox', 'Input Box', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('multiselect', 'Multiple Select List', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('radio', 'Radio Buttons', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('select', 'Single Select List', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('textarea', 'Text Area', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
('url', 'URL', '1.0', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');

CREATE TABLE IF NOT EXISTS `#__sobipro_plugin_section` (
  `section` int(11) NOT NULL DEFAULT '0',
  `pid` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`section`,`pid`,`type`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_plugin_task` (
  `pid` varchar(50) NOT NULL DEFAULT '',
  `onAction` varchar(150) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  UNIQUE KEY `pid` (`pid`,`onAction`,`type`)
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__sobipro_plugin_task` (`pid`, `onAction`, `type`) VALUES
('bank_transfer', 'adm_menu', 'payment'),
('bank_transfer', 'entry.payment', 'payment'),
('bank_transfer', 'entry.save', 'payment'),
('bank_transfer', 'entry.submit', 'payment'),
('paypal', 'adm_menu', 'payment'),
('paypal', 'entry.payment', 'payment'),
('paypal', 'entry.save', 'payment'),
('paypal', 'entry.submit', 'payment');

CREATE TABLE IF NOT EXISTS `#__sobipro_registry` (
  `section` varchar(150) NOT NULL,
  `key` varchar(150) NOT NULL,
  `value` text NOT NULL,
  `params` text NOT NULL,
  `description` text NOT NULL,
  `options` text NOT NULL
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__sobipro_registry` (`section`, `key`, `value`, `params`, `description`, `options`) VALUES
('fields_filter', 'website_full', 'Website with Protocol', 'L15odHRwKHMpPzpcL1wvW1x3XC4tXStcLnsxfVthLXpBLVpdezIsNX0oXC9bXlxzXSopPyQv', 'Please enter a valid URL address in the $field field', ''),
('fields_filter', 'website', 'Website w/o Protocol', 'L15bXHdcLi1dK1wuezF9W2EtekEtWl17Miw1fShcL1teXHNdKik/JC8=', 'Please enter a valid website address without the protocol in the $field field', ''),
('fields_filter', 'title', 'Valid Title', 'L15bXHdcZF0rW1x3XGRccyFAXCRcJVwmXCpcIlwnXC1cK19dKiQv', 'The data entered in the $field field contains not allowed characters', 'custom'),
('fields_filter', 'single_letter', 'Single Letter', 'L15bYS16QS1aXSQv', 'This $field field accept only one letter value', ''),
('fields_filter', 'phone', 'Telephone Number', 'L14oXCtcZHsxLDN9XHM/KT8oXHM/XChbXGRdXClccz8pP1tcZFwtXHNcLl0rJC8=', 'Please enter a valid telephone number into $field field.', ''),
('fields_filter', 'integer', 'Decimal Value', 'L15cZCskLw==', 'Please enter a numeric value in the $field field', ''),
('fields_filter', 'float', 'Float Value', 'L15cZCsoXC5cZCopPyQv', 'Please enter a float value like 9.9 or 12.34 into the  $field field', ''),
('fields_filter', 'alphanum', 'Alphanumeric String', 'L15bYS16QS1aMC05XSskLw==', 'In the $field field only alphabetic and numeric characters are allowed', ''),
('fields_filter', 'email', 'Email Address', 'L15bXHdcLi1dK0BbXHdcLi1dK1wuW2EtekEtWl17Miw1fSQv', 'Please enter an email address into the $field field', ''),
('fields_filter', 'alpha', 'Alphabetic String', 'L15bYS16QS1aXSskLw==', 'In this $field field only letters are allowed', ''),
('paypal', 'ppcc', 'EUR', '', '', ''),
('paypal', 'pprurl', '{cfg:live_site}/index.php?option=com_sobipro&sid={section.id}', '', '', ''),
('paypal', 'ppurl', 'https://www.paypal.com/cgi-bin/webscr', '', '', ''),
('paypal', 'ppemail', 'change@me.com', '', '', '');

CREATE TABLE IF NOT EXISTS `#__sobipro_relations` (
  `id` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `oType` varchar(50) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `validSince` datetime DEFAULT NULL,
  `validUntil` datetime DEFAULT NULL,
  `copy` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`,`pid`),
  KEY `oType` (`oType`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_search` (
  `ssid` double NOT NULL,
  `lastActive` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `searchCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `requestData` text NOT NULL,
  `uid` int(11) NOT NULL,
  `browserData` text NOT NULL,
  `entriesResults` text NOT NULL,
  `catsResults` text NOT NULL,
  PRIMARY KEY (`ssid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_section` (
  `id` int(11) NOT NULL,
  `description` text
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_users_relation` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `validSince` datetime DEFAULT NULL,
  `validUntil` datetime DEFAULT NULL,
  PRIMARY KEY  (`uid`,`gid`,`validSince`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_user_group` (
  `description` text,
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `groupName` varchar(150) NOT NULL,
  PRIMARY KEY (`gid`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=5000;
