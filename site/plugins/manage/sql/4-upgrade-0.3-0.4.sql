CREATE TABLE manage_services ( id MEDIUMINT(8) NOT NULL, name TEXT, oid TEXT, statut TEXT );
CREATE TABLE manage_process ( id MEDIUMINT(8) NOT NULL, name TEXT, tag TEXT, statut TEXT );

ALTER TABLE manage_alerts ADD `oid` TEXT;

INSERT INTO `settings` VALUES ('manage_use_patch', '0');
INSERT INTO `settings` VALUES ('manage_send', '');
INSERT INTO `settings` VALUES ('manage_netsend_events', '5');
INSERT INTO settings VALUES ('manage_netsend_method', '1');
INSERT INTO settings VALUES ('manage_list', '0');
INSERT INTO settings VALUES ('manage_list_separator', '25');
INSERT INTO settings VALUES ('manage_full_separator', '6');
INSERT INTO settings VALUES ('manage_simple_separator', '12');
INSERT INTO settings VALUES ('manage_legend', 'on');
INSERT INTO settings VALUES ('manage_poller_hosts', '5');
INSERT INTO settings VALUES ('manage_perl', 'c:/perl/bin/perl.exe');




