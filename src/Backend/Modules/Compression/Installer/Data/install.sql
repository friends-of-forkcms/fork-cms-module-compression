CREATE TABLE `compression_folders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `compression_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `status` enum('active','archived') NOT NULL,
  `original_size` int(11) DEFAULT NULL,
  `compressed_size` int(11) DEFAULT NULL,
  `saved_bytes` int(11) DEFAULT NULL,
  `saved_percentage` int(11) DEFAULT NULL,
  `checksum_hash` char(40) DEFAULT NULL,
  `compressed_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;