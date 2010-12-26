CREATE TABLE IF NOT EXISTS `@users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` varchar(255) collate utf8_unicode_ci NOT NULL,
  `access` tinyint(4) NOT NULL,
  `registered` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
