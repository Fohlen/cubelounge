CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `auth_id` varchar(21) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` text,
  `alias` varchar(255) DEFAULT NULL,
  `pubkey` varchar(49) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `auth_id` (`auth_id`);

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;