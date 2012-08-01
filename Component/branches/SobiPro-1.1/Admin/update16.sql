
UPDATE #__sobipro_permissions SET value =  '*' WHERE  pid = 18;

CREATE TABLE IF NOT EXISTS `#__sobipro_user_group` (
  `description` text,
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `groupName` varchar(150) NOT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5000 ;
