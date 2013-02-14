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
