CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL DEFAULT '1' COMMENT 'role id',
  `email` text NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `api_key` text NOT NULL,
  `meta_data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
