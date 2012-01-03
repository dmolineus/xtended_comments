CREATE TABLE `tl_comments` (
  `member_id` int(10) NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tl_news_archive` (
  `xc_showAvatar` char(1) NOT NULL default '',
  `xc_defaultAvatar` varchar(255) NOT NULL default '',
  `xc_gravatarRating` char(2) NOT NULL default '',
  `xc_gravatarSize` int(10) NOT NULL default '0',
  `xc_useAvatarExtension` char(1) NOT NULL default '',
  `xc_highlightAuthor` varchar(32) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tl_content` (
  `com_showAvatar` char(1) NOT NULL default '',
  `com_defaultAvatar` varchar(255) NOT NULL default '',
  `com_gravatarRating` char(2) NOT NULL default '',
  `com_gravatarSize` int(10) NOT NULL default '0',
  `com_useAvatarExtension` char(1) NOT NULL default '',
  `com_highlightAuthor` varchar(32) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tl_module` (
  `com_showAvatar` char(1) NOT NULL default '',
  `com_defaultAvatar` varchar(255) NOT NULL default '',
  `com_gravatarRating` char(2) NOT NULL default '',
  `com_gravatarSize` int(10) NOT NULL default '0',
  `com_useAvatarExtension` char(1) NOT NULL default '',
  `com_highlightAuthor` varchar(32) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
