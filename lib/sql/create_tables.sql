CREATE TABLE `mail_data` (
	`id` int(11) NOT NULL auto_increment,
	`mail_id` varchar(255) default NULL,
	`mail_from` varchar(255) NOT NULL,
	`mail_to` text NOT NULL,
	`mail_delivered_to` text NOT NULL,
	`subject` varchar(255) NOT NULL,
	`mail_body_text` text NOT NULL,
	`mail_body_html` text NOT NULL,
	`mail_attachments` text NOT NULL,
	`create_at` int(11) NOT NULL,
	PRIMARY KEY  (`id`),
	KEY `mail_from` (`mail_from`),
	KEY `mail_id` (`mail_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;
