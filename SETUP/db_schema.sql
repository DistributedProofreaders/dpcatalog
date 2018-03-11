#
# Table structure for table `catalog`
#
# Creation:
# Last update:
#

CREATE TABLE `catalog` (
  `id` varchar(22) DEFAULT NULL,
  `pg_identifier` text NOT NULL,
  `lccn_id` varchar(12) DEFAULT NULL,
  `oclc_id` varchar(25) DEFAULT NULL,
  `author_name` text NOT NULL,
  `title` text NOT NULL,
  `subject` text NOT NULL,
  `publisher` text NOT NULL,
  `contributor` text NOT NULL,
  `publishdate` text NOT NULL,
  `language` text NOT NULL,
  `page_image_location` text NOT NULL,
  `number_pages` int(4) NOT NULL DEFAULT '0',
  `image_source` varchar(10) DEFAULT NULL,
  `external_source_id` varchar(30) NOT NULL DEFAULT '',
  `reviewed` tinyint(1) NOT NULL DEFAULT '0',
  `reviewed_by` varchar(25) NOT NULL DEFAULT '',
  `review_date` int(20) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
# --------------------------------------------------------

#
# Table structure for table `image_sources`
#
# Creation:
# Last update:
#

CREATE TABLE `image_sources` (
  `code_name` varchar(10) NOT NULL DEFAULT '',
  `display_name` varchar(30) NOT NULL DEFAULT '',
  `full_name` varchar(100) NOT NULL DEFAULT '',
  `info_page_visibility` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(3) NOT NULL DEFAULT '-1',
  `url` varchar(200) DEFAULT NULL,
  `credit` varchar(200) DEFAULT NULL,
  `ok_keep_images` tinyint(4) NOT NULL DEFAULT '-1',
  `ok_show_images` tinyint(4) NOT NULL DEFAULT '-1',
  `public_comment` varchar(255) DEFAULT NULL,
  `internal_comment` text,
  UNIQUE KEY `code_name` (`code_name`),
  UNIQUE KEY `display_name` (`display_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1

# --------------------------------------------------------
