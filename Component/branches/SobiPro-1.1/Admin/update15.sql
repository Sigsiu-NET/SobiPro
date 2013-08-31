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
	ENGINE = MyISAM
	DEFAULT CHARSET = utf8;

UPDATE `#__sobipro_permissions`
SET value = '*'
WHERE pid = 18;

CREATE TABLE IF NOT EXISTS `#__sobipro_user_group` (
	`description` TEXT,
	`gid`         INT(11)      NOT NULL AUTO_INCREMENT,
	`enabled`     INT(11)      NOT NULL,
	`pid`         INT(11)      NOT NULL,
	`groupName`   VARCHAR(150) NOT NULL,
	PRIMARY KEY (`gid`)
)
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 5000;

DELETE FROM `#__sobipro_permissions`
WHERE `pid` = 5;
ALTER TABLE `#__sobipro_permissions` ADD UNIQUE `uniquePermission` (`subject`, `action`, `value`, `site`);
INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES
(NULL, 'section', 'search', '*', 'front', 1),
(NULL, 'entry', 'delete', 'own', 'front', 1),
(NULL, 'entry', 'delete', '*', 'front', 1);

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

INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES
('category', 'Category', 'special', 11);

INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES
('category', 'Category', '1.1', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');

CREATE TABLE IF NOT EXISTS `#__sobipro_history` (
  `revision` varchar(150) NOT NULL,
  `changedAt` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `userName` varchar(150) NOT NULL,
  `userEmail` varchar(150) NOT NULL,
  `change` varchar(150) NOT NULL,
  `site` enum('site','adm') NOT NULL,
  `sid` int(11) NOT NULL,
  `changes` text NOT NULL,
  `params` text NOT NULL,
  `reason` text NOT NULL,
  `language` varchar(50) NOT NULL,
  PRIMARY KEY (`revision`)
) DEFAULT CHARSET=utf8;
