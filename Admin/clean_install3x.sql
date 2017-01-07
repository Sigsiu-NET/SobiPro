CREATE TABLE IF NOT EXISTS `#__sobipro_counter` (
	`sid`        INT(11)  NOT NULL,
	`counter`    INT(11)  NOT NULL,
	`lastUpdate` DATETIME NOT NULL,
	PRIMARY KEY (`sid`)
);

CREATE TABLE IF NOT EXISTS `#__sobipro_view_cache` (
	`cid`        INT(11)      NOT NULL AUTO_INCREMENT,
	`section`    INT(11)      NOT NULL,
	`sid`        INT(11)      NOT NULL,
	`fileName`   VARCHAR(100) NOT NULL,
	`task`       VARCHAR(100) NOT NULL,
	`site`       INT(11)      NOT NULL,
	`request`    VARCHAR(255) NOT NULL,
	`language`   VARCHAR(15)  NOT NULL,
	`template`   VARCHAR(150) NOT NULL,
	`configFile` TEXT         NOT NULL,
	`userGroups` VARCHAR(200) NOT NULL,
	`created`    DATETIME     NOT NULL,
	PRIMARY KEY (`cid`),
	KEY `sid` (`sid`),
	KEY `section` (`section`),
	KEY `language` (`language`),
	KEY `task` (`task`),
	KEY `request` (`request`),
	KEY `site` (`site`),
	KEY `userGroups` (`userGroups`)
);

CREATE TABLE IF NOT EXISTS `#__sobipro_view_cache_relation` (
	`cid` INT(11) NOT NULL,
	`sid` INT(11) NOT NULL,
	PRIMARY KEY (`cid`, `sid`)
);

CREATE TABLE IF NOT EXISTS `#__sobipro_crawler` (
	`url`   VARCHAR(255) NOT NULL,
	`crid`  INT(11)      NOT NULL AUTO_INCREMENT,
	`state` TINYINT(1)   NOT NULL,
	PRIMARY KEY (`crid`),
	UNIQUE KEY `url` (`url`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_category` (
	`id`            INT(11)             NOT NULL,
	`position`      INT(11) DEFAULT NULL,
	`description`   TEXT,
	`parseDesc`     ENUM('0', '1', '2') NOT NULL DEFAULT '2',
	`introtext`     VARCHAR(255)        NOT NULL,
	`showIntrotext` ENUM('0', '1', '2') NOT NULL DEFAULT '2',
	`icon`          VARCHAR(150) DEFAULT NULL,
	`showIcon`      ENUM('0', '1', '2') NOT NULL DEFAULT '2',
	PRIMARY KEY (`id`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_config` (
	`sKey`     VARCHAR(150) NOT NULL DEFAULT '',
	`sValue`   TEXT,
	`section`  INT(11)      NOT NULL DEFAULT '0',
	`critical` TINYINT(1) DEFAULT NULL,
	`cSection` VARCHAR(30)  NOT NULL,
	PRIMARY KEY (`sKey`, `section`, `cSection`)
)
	DEFAULT CHARSET = utf8;

INSERT IGNORE INTO `#__sobipro_config` (`sKey`, `sValue`, `section`, `critical`, `cSection`) VALUES
  ('allowed_attributes_array', 'YTo4OntpOjA7czo1OiJjbGFzcyI7aToxO3M6MjoiaWQiO2k6MjtzOjU6InN0eWxlIjtpOjM7czo0OiJocmVmIjtpOjQ7czozOiJzcmMiO2k6NTtzOjQ6Im5hbWUiO2k6NjtzOjM6ImFsdCI7aTo3O3M6NToidGl0bGUiO30=', 0, 0, 'html'),
  ('allowed_tags_array', 'YToxNzp7aTowO3M6MToiYSI7aToxO3M6MToicCI7aToyO3M6MjoiYnIiO2k6MztzOjI6ImhyIjtpOjQ7czozOiJkaXYiO2k6NTtzOjI6ImxpIjtpOjY7czoyOiJ1bCI7aTo3O3M6NDoic3BhbiI7aTo4O3M6NToidGFibGUiO2k6OTtzOjI6InRyIjtpOjEwO3M6MjoidGQiO2k6MTE7czozOiJpbWciO2k6MTI7czoyOiJoMSI7aToxMztzOjI6ImgyIjtpOjE0O3M6MjoiaDMiO2k6MTU7czoyOiJoNCI7aToxNjtzOjI6Img1Ijt9', 0, 0, 'html'),
  ('alphamenu_extra_fields_array', '', 0, 0, 'alphamenu_extra_fields_array'),
  ('compress_js', '0', 0, 0, 'cache'),
  ('currency', '€', 0, 0, 'payments'),
  ('dec_point', ',', 0, 0, 'payments'),
  ('discount_to_netto', '0', 0, 0, 'payments'),
  ('display_errors', '0', 0, 0, 'debug'),
  ('engb_preload', '1', 0, 0, 'lang'),
  ('extra_fields_array', '', 0, 0, 'alphamenu'),
  ('format', '%value %currency', 0, 0, 'payments'),
  ('include_css_files', '1', 0, 0, 'cache'),
  ('include_js_files', '1', 0, 0, 'cache'),
  ('l3_enabled', '1', 0, 0, 'cache'),
  ('level', '2', 0, 0, 'debug'),
  ('multimode', '0', 0, 0, 'lang'),
  ('percent_format', '%number%sign', 0, 0, 'payments'),
  ('show_pb', '1', 0, 0, 'general'),
  ('vat', '7', 0, 0, 'payments'),
  ('vat_brutto', '1', 0, 0, 'payments'),
  ('xml_enabled', '1', 0, 0, 'cache'),
  ('xml_ip', '', 0, 0, 'debug'),
  ('xml_no_reg', '0', 0, 0, 'cache'),
  ('xml_raw', '0', 0, 0, 'debug');


CREATE TABLE IF NOT EXISTS `#__sobipro_errors` (
	`eid`          INT(25)      NOT NULL AUTO_INCREMENT,
	`date`         DATETIME     NOT NULL,
	`errNum`       INT(5)       NOT NULL,
	`errCode`      INT(5)       NOT NULL,
	`errMsg`       TEXT         NOT NULL,
	`errFile`      VARCHAR(255) NOT NULL,
	`errLine`      INT(10)      NOT NULL,
	`errSect`      VARCHAR(50)  NOT NULL,
	`errUid`       INT(11)      NOT NULL,
	`errIp`        VARCHAR(15)  NOT NULL,
	`errRef`       VARCHAR(255) NOT NULL,
	`errUa`        VARCHAR(255) NOT NULL,
	`errReq`       VARCHAR(255) NOT NULL,
	`errCont`      TEXT         NOT NULL,
	`errBacktrace` TEXT         NOT NULL,
	PRIMARY KEY (`eid`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 1;


CREATE TABLE IF NOT EXISTS `#__sobipro_field` (
	`fid`               INT(11)                                    NOT NULL AUTO_INCREMENT,
	`nid`               VARCHAR(150)                               NOT NULL,
	`adminField`        TINYINT(1) DEFAULT NULL,
	`admList`           INT(10)                                    NOT NULL,
	`dataType`          INT(11) DEFAULT NULL,
	`enabled`           TINYINT(1) DEFAULT NULL,
	`fee`               DOUBLE DEFAULT NULL,
	`fieldType`         VARCHAR(50) NOT NULL,
	`filter`            VARCHAR(150) DEFAULT NULL,
	`isFree`            TINYINT(1) DEFAULT NULL,
	`position`          INT(11) DEFAULT NULL,
	`priority`          INT(11)                                    NOT NULL,
	`required`          TINYINT(1) NOT NULL,
	`section`           INT(11) DEFAULT NULL,
	`multiLang`         TINYINT(4) DEFAULT NULL,
	`uniqueData`        TINYINT(1) DEFAULT NULL,
	`validate`          TINYINT(1) DEFAULT NULL,
	`addToMetaDesc`     TINYINT(1) DEFAULT NULL,
	`addToMetaKeys`     TINYINT(1) DEFAULT '0',
	`editLimit`         INT(11)                                    NOT NULL DEFAULT '0',
	`editable`          TINYINT(4)                                 NOT NULL,
	`showIn`            ENUM('both', 'details', 'vcard', 'hidden') NOT NULL DEFAULT 'both',
	`allowedAttributes` TEXT                                       NOT NULL,
	`allowedTags`       TEXT                                       NOT NULL,
	`editor`            VARCHAR(255)                               NOT NULL,
	`inSearch`          TINYINT(4)                                 NOT NULL DEFAULT '1',
	`withLabel`         TINYINT(4)                                 NOT NULL,
	`cssClass`          VARCHAR(50)                                NOT NULL,
	`parse`             TINYINT(4)                                 NOT NULL,
	`template`          VARCHAR(255)                               NOT NULL,
	`notice`            VARCHAR(150)                               NOT NULL,
	`params`            TEXT                                       NOT NULL,
	`defaultValue`      TEXT                                       NOT NULL,
	`version`           INT(11)                                    NOT NULL DEFAULT '0',
	PRIMARY KEY (`fid`),
	KEY `enabled` (`enabled`),
	KEY `position` (`position`),
	KEY `section` (`section`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_field_data` (
	`publishUp`   DATETIME DEFAULT NULL,
	`publishDown` DATETIME DEFAULT NULL,
	`fid`         INT(11)     NOT NULL DEFAULT '0',
	`sid`         INT(11)     NOT NULL DEFAULT '0',
	`section`     INT(11)     NOT NULL DEFAULT '0',
	`lang`        VARCHAR(50) NOT NULL DEFAULT '',
	`enabled`     TINYINT(1)  NOT NULL,
	`params`      TEXT,
	`options`     TEXT,
	`baseData`    TEXT,
	`approved`    TINYINT(1) DEFAULT NULL,
	`confirmed`   TINYINT(1) DEFAULT NULL,
	`createdTime` DATETIME DEFAULT NULL,
	`createdBy`   INT(11) DEFAULT NULL,
	`createdIP`   VARCHAR(15) DEFAULT NULL,
	`updatedTime` DATETIME DEFAULT NULL,
	`updatedBy`   INT(11) DEFAULT NULL,
	`updatedIP`   VARCHAR(15) DEFAULT NULL,
	`copy`        TINYINT(1)  NOT NULL DEFAULT '0',
	`editLimit`   INT(11),
	PRIMARY KEY (`fid`, `section`, `lang`, `sid`, `copy`),
	KEY `enabled` (`enabled`),
	KEY `copy` (`copy`),
	FULLTEXT KEY `baseData` (`baseData`)
)
-- needs to my MyISAM as InnoDB support FULLTEXT indices first since 5.6
  ENGINE = MyISAM
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_field_option` (
	`fid`       INT(11)      NOT NULL,
	`optValue`  VARCHAR(100) NOT NULL,
	`optPos`    INT(11)      NOT NULL,
	`img`       VARCHAR(150) NOT NULL,
	`optClass`  VARCHAR(50)  NOT NULL,
	`actions`   TEXT         NOT NULL,
	`class`     TEXT         NOT NULL,
	`optParent` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`fid`, `optValue`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_field_option_selected` (
	`fid`      INT(11)      NOT NULL,
	`sid`      INT(11)      NOT NULL,
	`optValue` VARCHAR(100) NOT NULL,
	`params`   TEXT         NOT NULL,
	`copy`     TINYINT(1)   NOT NULL,
	PRIMARY KEY (`fid`, `sid`, `optValue`, `copy`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_field_types` (
	`tid`    CHAR(50)     NOT NULL,
	`fType`  VARCHAR(50)  NOT NULL,
	`tGroup` VARCHAR(100) NOT NULL,
	`fPos`   INT(11)      NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`tid`, `tGroup`),
	UNIQUE KEY `pos` (`fPos`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 13;

INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES
('inbox', 'Input Box', 'free_single_simple_data', 1),
('textarea', 'Text Area', 'free_single_simple_data', 2),
('multiselect', 'Multiple Select List', 'predefined_multi_data_multi_choice', 3),
('chbxgroup', 'Check Box Group', 'predefined_multi_data_multi_choice', 4),
('button', 'Button', 'special', 5),
('info', 'Information', 'free_single_simple_data', 6),
('select', 'Single Select List', 'predefined_multi_data_single_choice', 7),
('radio', 'Radio Buttons', 'predefined_multi_data_single_choice', 8),
('image', 'Image', 'special', 9),
('url', 'URL', 'special', 10),
('category', 'Category', 'special', 11),
('email', 'Email', 'special', 12);


CREATE TABLE IF NOT EXISTS `#__sobipro_field_url_clicks` (
	`date`        DATETIME    NOT NULL,
	`uid`         INT(11)     NOT NULL,
	`sid`         INT(11)     NOT NULL,
	`fid`         VARCHAR(50) NOT NULL,
	`ip`          VARCHAR(15) NOT NULL,
	`section`     INT(11)     NOT NULL,
	`browserData` TEXT        NOT NULL,
	`osData`      TEXT        NOT NULL,
	`humanity`    INT(3)      NOT NULL,
	PRIMARY KEY (`date`, `sid`, `fid`, `ip`, `section`)
);


CREATE TABLE IF NOT EXISTS `#__sobipro_language` (
	`sKey`        VARCHAR(150) NOT NULL DEFAULT '',
	`sValue`      TEXT,
	`section`     INT(11) NOT NULL,
	`language`    VARCHAR(50)  NOT NULL DEFAULT '',
	`oType`       VARCHAR(150) NOT NULL,
	`fid`         INT(11)      NOT NULL,
	`id`          INT(11)      NOT NULL DEFAULT '0',
	`params`      TEXT,
	`options`     TEXT,
	`explanation` TEXT,
	PRIMARY KEY (`sKey`, `language`, `id`, `fid`),
	KEY `sKey` (`sKey`),
	KEY `section` (`section`),
	KEY `language` (`language`),
	FULLTEXT KEY `sValue` (`sValue`)
)
-- needs to my MyISAM as InnoDB support FULLTEXT indices first since 5.6
  ENGINE = MyISAM
	DEFAULT CHARSET = utf8;


INSERT IGNORE INTO `#__sobipro_language` (`sKey`, `sValue`, `section`, `language`, `oType`, `fid`, `id`, `params`, `options`, `explanation`) VALUES
  ('bankdata', '<p>Payment Subject: "Entry #{entry.id} in {section.name} at {cfg:site_name}."</p>\r\n<ul>\r\n<li>Account Owner: Jon Doe </li>\r\n<li>Account No.: 8274230479 </li>\r\n<li>Bank No.: 8038012380 </li>\r\n<li>IBAN: 234242343018 </li>\r\n<li>BIC: 07979079779ABCDEFGH</li>\r\n</ul>', 1, 'en-GB', 'application', 0, 1, '', '', ''),
  ('ppexpl', '<p>Please click on the button below to pay via Paypal.</p>\r\n<p> </p>', 1, 'en-GB', 'application', 0, 1, '', '', ''),
  ('ppsubject', 'Entry #{entry.id} in {section.name} at {cfg:site_name}.', 1, 'en-GB', 'application', 0, 1, '', '', ''),
  ('rejection-of-a-new-entry', 'Entry {entry.name} has been rejected as it does not comply with the rules.\n\n<br/>Rejected by {user.name}\n<br/>at {date%d F Y H:i:s}\n', 0, 'en-GB', 'rejections-templates', 0, 1, '', '', ''),
  ('rejection-of-changes', 'Changes in {entry.name} discarded as these changes violating rules.\n\n<br/>Rejected by {user.name}\n<br/>at {date%d F Y H:i:s}\n', 0, 'en-GB', 'rejections-templates', 0, 1, '', '', '');


CREATE TABLE IF NOT EXISTS `#__sobipro_object` (
	`id`          INT(11)      NOT NULL AUTO_INCREMENT,
	`nid`         VARCHAR(255) NOT NULL,
	`name`        VARCHAR(250) DEFAULT NULL,
	`approved`    TINYINT(1) DEFAULT NULL,
	`confirmed`   TINYINT(1) DEFAULT NULL,
	`counter`     INT(11)      NOT NULL DEFAULT '0',
	`cout`        INT(11) DEFAULT NULL,
	`coutTime`    DATETIME DEFAULT NULL,
	`createdTime` DATETIME DEFAULT NULL,
	`defURL`      VARCHAR(250) DEFAULT NULL,
	`metaDesc`    TEXT,
	`metaKeys`    TEXT,
	`metaAuthor`  VARCHAR(150) NOT NULL,
	`metaRobots`  VARCHAR(150) NOT NULL,
	`options`     TEXT,
	`oType`       VARCHAR(50) NOT NULL,
	`owner`       INT(11) DEFAULT NULL,
	`ownerIP`     VARCHAR(15) DEFAULT NULL,
	`params`      TEXT,
	`parent`      INT(11) DEFAULT NULL,
	`state`       TINYINT(4) DEFAULT NULL,
	`stateExpl`   VARCHAR(250) DEFAULT NULL,
	`updatedTime` DATETIME DEFAULT NULL,
	`updater`     INT(11) DEFAULT NULL,
	`updaterIP`   VARCHAR(15) DEFAULT NULL,
	`validSince`  DATETIME NOT NULL,
	`validUntil`  DATETIME NOT NULL,
	`version`     INT(11)      NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `name` (`name`),
	KEY `oType` (`oType`),
	KEY `owner` (`owner`),
	KEY `parent` (`parent`),
	KEY `state` (`state`),
	KEY `validSince` (`validSince`),
	KEY `validUntil` (`validUntil`),
	KEY `version` (`version`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 54;


CREATE TABLE IF NOT EXISTS `#__sobipro_payments` (
	`pid`        INT(11)      NOT NULL AUTO_INCREMENT,
	`refNum`     VARCHAR(50)  NOT NULL,
	`sid`        INT(11)      NOT NULL,
	`fid`        INT(11)      NOT NULL,
	`subject`    VARCHAR(150) NOT NULL,
	`dateAdded`  DATETIME     NOT NULL,
	`datePaid`   DATETIME     NOT NULL,
	`validUntil` DATETIME     NOT NULL,
	`paid`       TINYINT(4)   NOT NULL,
	`amount`     DOUBLE       NOT NULL,
	`params`     TEXT         NOT NULL,
	PRIMARY KEY (`pid`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 16;


CREATE TABLE IF NOT EXISTS `#__sobipro_permissions` (
	`pid`       INT(11)     NOT NULL AUTO_INCREMENT,
	`subject`   VARCHAR(150) NOT NULL,
	`action`    VARCHAR(50) NOT NULL,
	`value`     VARCHAR(50) NOT NULL,
	`site`      VARCHAR(50) NOT NULL,
	`published` TINYINT(1)  NOT NULL,
	PRIMARY KEY (`pid`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 26;

INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES
(1, '*', '*', '*', 'front', 0),
(2, 'section', '*', '*', 'front', 0),
(3, 'section', 'access', '*', 'front', 1),
(4, 'section', 'access', 'valid', 'front', 1),
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
(25, 'entry', 'payment', 'free', 'front', 1),
(86, 'entry', '*', '*', 'adm', 1),
(87, 'category', '*', '*', 'adm', 1),
(88, 'section', '*', '*', 'adm', 1),
(89, 'section', 'access', '*', 'adm', 1),
(90, 'section', 'configure', '*', 'adm', 1),
(91, 'section', 'delete', '*', 'adm', 0),
(92, 'category', 'edit', '*', 'adm', 1),
(93, 'category', 'add', '*', 'adm', 1),
(94, 'category', 'delete', '*', 'adm', 1),
(95, 'entry', 'edit', '*', 'adm', 1),
(96, 'entry', 'add', '*', 'adm', 1),
(97, 'entry', 'delete', '*', 'adm', 1),
(98, 'entry', 'approve', '*', 'adm', 1),
(99, 'entry', 'publish', '*', 'adm', 1);

DELETE FROM `#__sobipro_permissions`
WHERE `pid` = 5;
ALTER TABLE `#__sobipro_permissions` ADD UNIQUE `uniquePermission` (`subject`, `action`, `value`, `site`);
INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES
(NULL, 'section', 'search', '*', 'front', 1),
(NULL, 'entry', 'delete', 'own', 'front', 1),
(NULL, 'entry', 'delete', '*', 'front', 1);

CREATE TABLE IF NOT EXISTS `#__sobipro_permissions_groups` (
	`rid` INT(11) NOT NULL,
	`gid` INT(11) NOT NULL,
	PRIMARY KEY (`rid`, `gid`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_permissions_map` (
	`rid` INT(11) NOT NULL,
	`sid` INT(11) NOT NULL,
	`pid` INT(11) NOT NULL,
	PRIMARY KEY (`rid`, `sid`, `pid`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_permissions_rules` (
	`rid`        INT(11)      NOT NULL AUTO_INCREMENT,
	`name`       VARCHAR(250) NOT NULL,
	`nid`        VARCHAR(50)  NOT NULL,
	`validSince` DATETIME     NOT NULL,
	`validUntil` DATETIME     NOT NULL,
	`note`       VARCHAR(250) NOT NULL,
	`state`      TINYINT(4)   NOT NULL,
	PRIMARY KEY (`rid`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 3;


CREATE TABLE IF NOT EXISTS `#__sobipro_plugins` (
	`pid`         VARCHAR(50) NOT NULL,
	`name`        VARCHAR(150) DEFAULT NULL,
	`version`     VARCHAR(50) NOT NULL,
	`description` TEXT,
	`author`      VARCHAR(150) DEFAULT NULL,
	`authorURL`   VARCHAR(250) DEFAULT NULL,
	`authorMail`  VARCHAR(150) DEFAULT NULL,
	`enabled`     TINYINT(1) DEFAULT NULL,
	`type`        VARCHAR(250) NOT NULL,
	`depend`      TEXT        NOT NULL,
	UNIQUE KEY `pid` (`pid`, `type`)
)
	DEFAULT CHARSET = utf8;

INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES
  ('bank_transfer', 'Offline Payment', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'payment', ''),
  ('paypal', 'PayPal', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'payment', ''),
  ('chbxgroup', 'Check Box Group', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('email', 'Email', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('image', 'Image', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('inbox', 'Input Box', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('multiselect', 'Multiple Select List', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('radio', 'Radio Buttons', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('select', 'Single Select List', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('textarea', 'Text Area', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('url', 'URL', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('category', 'Category', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', ''),
  ('info', 'Information', '1.2', NULL, 'Sigsiu.NET GmbH', 'https://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');

CREATE TABLE IF NOT EXISTS `#__sobipro_plugin_section` (
	`section`  INT(11)     NOT NULL DEFAULT '0',
	`pid`      VARCHAR(50) NOT NULL DEFAULT '',
	`type`     VARCHAR(50) NOT NULL,
	`enabled`  TINYINT(1) DEFAULT NULL,
	`position` INT(11) DEFAULT NULL,
	PRIMARY KEY (`section`, `pid`, `type`)
)
	DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `#__sobipro_plugin_task` (
	`pid`      VARCHAR(50) NOT NULL DEFAULT '',
	`onAction` VARCHAR(150) NOT NULL,
	`type`     VARCHAR(50) NOT NULL,
	UNIQUE KEY `pid` (`pid`, `onAction`, `type`)
)
	DEFAULT CHARSET = utf8;

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
	`section`     VARCHAR(150) NOT NULL,
	`key`         VARCHAR(150) NOT NULL,
	`value`       TEXT         NOT NULL,
	`params`      TEXT         NOT NULL,
	`description` TEXT         NOT NULL,
	`options`     TEXT         NOT NULL
)
	DEFAULT CHARSET = utf8;

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
('paypal', 'ppemail', 'change@me.com', '', '', ''),
('rejections-templates', 'rejection-of-a-new-entry', 'Rejection of a new entry', 'YTo0OntzOjE3OiJ0cmlnZ2VyLnVucHVibGlzaCI7YjoxO3M6MTc6InRyaWdnZXIudW5hcHByb3ZlIjtiOjA7czo5OiJ1bnB1Ymxpc2giO2I6MTtzOjc6ImRpc2NhcmQiO2I6MDt9', '', ''),
('rejections-templates', 'rejection-of-changes', 'Rejection of changes', 'YTo0OntzOjE3OiJ0cmlnZ2VyLnVucHVibGlzaCI7YjowO3M6MTc6InRyaWdnZXIudW5hcHByb3ZlIjtiOjE7czo5OiJ1bnB1Ymxpc2giO2I6MDtzOjc6ImRpc2NhcmQiO2I6MTt9', '', '');


CREATE TABLE IF NOT EXISTS `#__sobipro_relations` (
	`id`         INT(11)     NOT NULL DEFAULT '0',
	`pid`        INT(11)     NOT NULL DEFAULT '0',
	`oType`      VARCHAR(50) NOT NULL,
	`position`   INT(11) DEFAULT NULL,
	`validSince` DATETIME NOT NULL,
	`validUntil` DATETIME NOT NULL,
	`copy`       TINYINT(1)  NOT NULL,
	PRIMARY KEY (`id`, `pid`),
	KEY `oType` (`oType`)
)
	DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_search` (
	`ssid`           DOUBLE    NOT NULL,
	`lastActive`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`searchCreated`  TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`requestData`    TEXT      NOT NULL,
	`uid`            INT(11)   NOT NULL,
	`browserData`    TEXT      NOT NULL,
	`entriesResults` TEXT      NOT NULL,
	`catsResults`    TEXT      NOT NULL,
	PRIMARY KEY (`ssid`)
)
	DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_section` (
	`id`          INT(11) NOT NULL,
	`description` TEXT
)
	DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__sobipro_users_relation` (
	`uid`        INT(11) NOT NULL DEFAULT '0',
	`gid`        INT(11) NOT NULL DEFAULT '0',
	`validSince` DATETIME NOT NULL,
	`validUntil` DATETIME NOT NULL,
	PRIMARY KEY (`uid`, `gid`, `validSince`)
)
	CHARSET = utf8;

DROP TABLE IF EXISTS `#__sobipro_user_group`;
CREATE TABLE `#__sobipro_user_group` (
	`description` TEXT,
	`gid`         INT(11)      NOT NULL AUTO_INCREMENT,
	`enabled`     INT(11)      NOT NULL,
	`pid`         INT(11)      NOT NULL,
	`groupName`   VARCHAR(150) NOT NULL,
	PRIMARY KEY (`gid`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 5000;

CREATE TABLE IF NOT EXISTS `#__sobipro_history` (
  `revision` varchar(150) NOT NULL,
  `changedAt` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `userName` varchar(150) NOT NULL,
  `userEmail` varchar(150) NOT NULL,
  `changeAction` varchar(150) NOT NULL,
  `site` enum('site','adm') NOT NULL,
  `sid` int(11) NOT NULL,
  `changes` text NOT NULL,
  `params` text NOT NULL,
  `reason` text NOT NULL,
  `language` varchar(50) NOT NULL,
  PRIMARY KEY (`revision`)
) DEFAULT CHARSET=utf8;
