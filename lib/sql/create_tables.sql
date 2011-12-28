CREATE TABLE `mail_data` (
  `id` int(11) NOT NULL auto_increment,
  `list_mask` varchar(255) collate utf8_bin default NULL,
  `time` datetime NOT NULL COMMENT '回复时间',
  `mail_header` text collate utf8_bin,
  `mail_from` varchar(255) character set utf8 default NULL,
  `mail_to` varchar(255) character set utf8 default NULL,
  `mail_cc` text collate utf8_bin,
  `title` varchar(255) character set utf8 NOT NULL,
  `content_text` text collate utf8_bin,
  `content_html` text collate utf8_bin,
  `file_name` varchar(255) collate utf8_bin default NULL,
  `attachment` tinyint(4) default NULL,
  PRIMARY KEY  (`id`),
  KEY `list_mask` (`list_mask`)
) ENGINE=MyISAM AUTO_INCREMENT=258 DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `mail_attachment` (
  `id` int(11) NOT NULL auto_increment,
  `mail_id` int(11) NOT NULL,
  `path` text collate utf8_bin,
  `file_name` varchar(255) collate utf8_bin default NULL,
  `file_type` varchar(255) collate utf8_bin default NULL,
  `file_description` text collate utf8_bin,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=128 DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;
