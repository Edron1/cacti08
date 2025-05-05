CREATE TABLE manage_poller_output (local_data_id mediumint(8), output text);
CREATE TABLE manage_sites ( id MEDIUMINT(8) NOT NULL auto_increment, name TEXT, PRIMARY KEY  (id) );

ALTER TABLE manage_host_services RENAME manage_tcp;
ALTER TABLE manage_groups ADD site_id MEDIUMINT NOT NULL;
ALTER TABLE manage_host ADD `thresold` MEDIUMINT(8) unsigned NOT NULL default '0';
ALTER TABLE manage_host ADD `thresold_ref` MEDIUMINT(8) unsigned NOT NULL default '1';
ALTER TABLE manage_host ADD `mail` TEXT;

INSERT INTO settings VALUES ('manage_cycle_delay', '30');
INSERT INTO settings VALUES ('manage_cycle_refresh', '5');
INSERT INTO settings VALUES ('manage_poller_plus', '1');
INSERT INTO settings VALUES ('manage_thold', '1');
INSERT INTO settings VALUES ('manage_order1', '1');
INSERT INTO settings VALUES ('manage_order2', '2');
INSERT INTO settings VALUES ('manage_order3', '3');
INSERT INTO settings VALUES ('manage_order4', '0');
INSERT INTO settings VALUES ('manage_order5', '0');
INSERT INTO settings VALUES ('manage_list_2', '2');
INSERT INTO settings VALUES ('manage_theme', 'default');
INSERT INTO settings VALUES ('manage_sound', '0');
INSERT INTO settings VALUES ('manage_global_email', '');

