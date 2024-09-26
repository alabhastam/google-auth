CREATE DATABASE IF NOT EXISTS `sociallogin` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `sociallogin`;

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `registered` datetime NOT NULL,
  `method` enum('facebook','google','linkedin') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;