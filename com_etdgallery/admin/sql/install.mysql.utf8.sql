--
-- Table structure for table `#__etdgallery`
--

CREATE TABLE IF NOT EXISTS `#__etdgallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('image','video') NOT NULL,
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `article_id` int(10) unsigned NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` tinytext NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_catid` (`catid`),
  KEY `idx_article_id` (`article_id`),
  KEY `idx_article_state` (`article_id`,`state`),
  KEY `idx_article_state_featured` (`article_id`,`state`,`featured`),
  KEY `idx_type_state` (`type`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__content_types` (`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`)
VALUES (NULL, 'EtdGallery Image', 'com_etdgallery.image', '{"special":{"dbtable":"#__etdgallery","key":"id","type":"Image","prefix":"EtdGalleryTable"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_created_time":"created","core_body":"filename", "core_featured":"featured", "core_ordering":"ordering"}, "special":{"type":"type","article_id":"article_id","description":"description"}}', '', '');