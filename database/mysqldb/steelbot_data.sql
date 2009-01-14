CREATE TABLE IF NOT EXISTS `@data` (
  `user` int(10) unsigned NOT NULL,
  `dkey` varchar(255) collate latin1_general_ci NOT NULL,
  `data` text collate latin1_general_ci,
  `last_edit` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `user` (`user`,`dkey`)
) ENGINE=MyISAM;