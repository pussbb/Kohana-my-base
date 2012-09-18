
CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) CHARACTER SET latin1 NOT NULL,
  `locale` varchar(5) CHARACTER SET latin1 NOT NULL,
  `name` varchar(200) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


INSERT INTO `languages` (`code`, `locale`, `name`) VALUES
('en', 'en-EN', 'English'), ('ru', 'ru-RU', 'Русский');
