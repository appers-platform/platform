CREATE TABLE IF NOT EXISTS `solutions_utrack_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(16) NOT NULL,
  `data_name_id` int(11) NOT NULL,
  `data_value_id` int(11) NOT NULL,
  `ts` datetime DEFAULT NULL,
  `data_source_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `solutions_utrack_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(32) NOT NULL,
  `event_name_id` int(11) NOT NULL,
  `event_value_id` int(11),
  `ts` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `solutions_utrack_cases_data_source` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `solutions_utrack_cases_data_name` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `solutions_utrack_cases_data_value` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `solutions_utrack_cases_event_name` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `solutions_utrack_cases_event_value` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
