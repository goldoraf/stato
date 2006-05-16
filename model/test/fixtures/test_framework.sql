# Database : test_framework

CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `author` varchar(45) default NULL,
  `text` text default NULL,
  `published` tinyint(1) default '1',
  `created_on` datetime default NULL,
  `updated_on` datetime default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `companies` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `employes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `company_id` int(11) default NULL,
  `firstname` varchar(50) default NULL,
  `lastname` varchar(50) default NULL,
  `function` varchar(150) default NULL,
  `date_of_birth` date default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `price` float default NULL,
  `company_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `profiles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `employe_id` int(11) default NULL,
  `cv` text default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `developers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `developers_projects` (
  `developer_id` int(11) NOT NULL default '0',
  `project_id` int(11) NOT NULL default '0'
) TYPE=MyISAM ;


CREATE TABLE `projects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `contracts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `client_id` int(11) default NULL,
  `code` varchar(20) default NULL,
  `date` date default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


CREATE TABLE `topics` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 50 ) default NULL,
`forum_id` INT default NULL,
`position` INT default NULL,
PRIMARY KEY ( `id` )
) TYPE=MyISAM ;


CREATE TABLE `forums` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 50 ) default NULL,
PRIMARY KEY ( `id` )
) TYPE=MyISAM ;


CREATE TABLE `articles` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`title` VARCHAR( 255 ) default NULL,
`text` TEXT default NULL,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;


CREATE TABLE `comments` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`article_id` INT default NULL,
`author` VARCHAR( 255 ) default NULL,
`text` TEXT default NULL,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;


CREATE TABLE `categories` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 50 ) default NULL,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;


CREATE TABLE `articles_categories` (
  `article_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0'
) TYPE=MyISAM ;

CREATE TABLE `people` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `first_name` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
