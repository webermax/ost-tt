INSERT INTO  ost_list  ( name ,  name_plural ,  sort_mode ,  masks ,  type ,  notes ,  created ,  updated ) VALUES ('Time Type', 'Time Types', 'SortCol', '13', 'time-type', 'Time Spent plugin list, do not modify', NOW(), NOW());

INSERT INTO `ost_list_items` (`list_id`, `status`, `value`, `sort`)
SELECT ost_list.id, 1, 'Telephone', 1
FROM ost_list
WHERE `name`='Time Type';

INSERT INTO `ost_list_items` (`list_id`, `status`, `value`, `sort`)
SELECT ost_list.id, 1, 'Email', 2
FROM ost_list
WHERE `name`='Time Type';

INSERT INTO `ost_list_items` (`list_id`, `status`, `value`, `sort`)
SELECT ost_list.id, 1, 'Remote', 3
FROM ost_list
WHERE `name`='Time Type';

INSERT INTO `ost_list_items` (`list_id`, `status`, `value`, `sort`)
SELECT ost_list.id, 1, 'Workshop', 4
FROM ost_list
WHERE `name`='Time Type';

INSERT INTO `ost_list_items` (`list_id`, `status`, `value`, `sort`)
SELECT ost_list.id, 1, 'Onsite', 5
FROM ost_list
WHERE `name`='Time Type';

INSERT INTO  ost_config  (`namespace`, `key`, `value`, `updated`) VALUES
 ('core', 'isclienttime', 0, now()),
 ('core', 'istickettime', 0, now()),
 ('core', 'isthreadtime', 0, now()),
 ('core', 'isthreadtimer', 0, now()),
 ('core', 'isthreadbill', 0, now()),
 ('core', 'isthreadbilldefault', 0, now()),
 ('core', 'istickethardware', 0, now());

ALTER TABLE  ost_ticket  ADD COLUMN  time_spent  INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER  closed;

ALTER TABLE  ost_ticket_thread  ADD COLUMN  time_spent  INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER  thread_type;

ALTER TABLE  ost_ticket_thread  ADD COLUMN  time_type  INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER  time_spent;

ALTER TABLE  ost_ticket_thread  ADD COLUMN  time_bill  INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER  time_type;

CREATE TABLE IF NOT EXISTS `ost_ticket_hardware` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) default NULL,
  `description` varchar(255) default NULL,
  `qty` int(11) NOT NULL default '0',
  `unit_cost` DECIMAL(15,2) NOT NULL default '0',
  `total_cost` DECIMAL(15,2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
