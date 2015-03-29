-- Create syntax for TABLE 'solutions_utrack_data'
CREATE TABLE IF NOT EXISTS `solutions_utrack_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(128) NOT NULL,
  `data` varchar(128) NOT NULL,
  `value` text NOT NULL,
  `ts` datetime DEFAULT NULL,
  `source` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'solutions_utrack_events'
CREATE TABLE IF NOT EXISTS `solutions_utrack_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(128) NOT NULL,
  `event` varchar(128) NOT NULL,
  `value` text,
  `ts` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;