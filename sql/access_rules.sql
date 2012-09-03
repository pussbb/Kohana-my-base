CREATE TABLE IF NOT EXISTS `access_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `directory` varchar(255) DEFAULT NULL,
  `controller` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Database table dump `access_rules`
--

INSERT INTO `access_rules` ( `role_id`, `directory`, `controller`, `action`) VALUES
(0, NULL, 'users', 'login'),
(0, NULL, 'users', 'register'),
(1, NULL, 'users', 'logout'),
(1, NULL, 'users', 'account_info'),
(1, NULL, 'users', 'settings');
