DROP TABLE IF EXISTS `a`;

CREATE TABLE `a` (
  `id` int(11) NOT NULL,
  `jisareacd` varchar(5) NOT NULL,
  `postcd` varchar(7) NOT NULL,
  `pref` varchar(4) NOT NULL,
  `city` varchar(200) NOT NULL,
  `town` varchar(200) NOT NULL,
  `population` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `m`;

CREATE TABLE `m` (
  `id` int(11) NOT NULL,
  `ranking` int(11) NOT NULL,
  `myouji` varchar(4) NOT NULL,
  `population` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
