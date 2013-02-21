CREATE TABLE IF NOT EXISTS `#__sobipro_view_cache` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `section` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `fileName` varchar(100) NOT NULL,
  `task` varchar(100) NOT NULL,
  `site` int(11) NOT NULL,
  `request` varchar(255) NOT NULL,
  `language` varchar(15) NOT NULL,
  `template` varchar(150) NOT NULL,
  `configFile` text NOT NULL,
  `userGroups` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
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
  `cid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  PRIMARY KEY (`cid`,`sid`)
);

UPDATE #__sobipro_permissions SET value =  '*' WHERE  pid = 18;

CREATE TABLE IF NOT EXISTS `#__sobipro_user_group` (
  `description` text,
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `groupName` varchar(150) NOT NULL,
  PRIMARY KEY (`gid`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=5000 ;

DELETE FROM `#__sobipro_permissions` WHERE `pid` = 5;
ALTER TABLE `#__sobipro_permissions` ADD UNIQUE  `uniquePermission` (  `subject` ,  `action` ,  `value` ,  `site` );
INSERT IGNORE INTO `#__sobipro_permissions` (`pid`, `subject`, `action`, `value`, `site`, `published`) VALUES
(NULL, 'section', 'search', '*', 'front', 1),
(NULL, 'entry', 'delete', 'own', 'front', 1),
(NULL, 'entry', 'delete', '*', 'front', 1);

CREATE TABLE IF NOT EXISTS `#__sobipro_field_url_clicks` (
  `date` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `fid` varchar(50) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `section` int(11) NOT NULL,
  `browserData` text NOT NULL,
  `osData` text NOT NULL,
  `humanity` int(3) NOT NULL,
  PRIMARY KEY (`date`,`sid`,`fid`,`ip`,`section`)
);

INSERT IGNORE INTO `#__sobipro_field_types` (`tid`, `fType`, `tGroup`, `fPos`) VALUES
('category', 'Category', 'special', 11);

INSERT IGNORE INTO `#__sobipro_plugins` (`pid`, `name`, `version`, `description`, `author`, `authorURL`, `authorMail`, `enabled`, `type`, `depend`) VALUES
('category', 'Category', '1.1', NULL, 'Sigsiu.NET GmbH', 'http://www.sigsiu.net/', 'sobi@sigsiu.net', 1, 'field', '');
