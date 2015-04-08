CREATE TABLE IF NOT EXISTS `feeds` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text,
  `url` text NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `feed_items` (
  `id` int(11) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `author` varchar(250) DEFAULT NULL,
  `url` text NOT NULL,
  `pubDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
ALTER TABLE `feeds`
  ADD PRIMARY KEY (`id`);

--
ALTER TABLE `feed_items`
  ADD PRIMARY KEY (`id`);

--
ALTER TABLE `feeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
ALTER TABLE `feed_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
