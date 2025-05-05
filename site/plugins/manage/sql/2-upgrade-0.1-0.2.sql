CREATE TABLE manage_device_type ( id MEDIUMINT(8) NOT NULL AUTO_INCREMENT , name TEXT, image TEXT, PRIMARY KEY ( id ) );
CREATE TABLE manage_templates ( id MEDIUMINT(8) NOT NULL auto_increment, name TEXT, tcp_ports TEXT, PRIMARY KEY  (id) );

ALTER TABLE manage_host ADD `force` MEDIUMINT(8) unsigned NOT NULL default '9';

INSERT INTO manage_device_type VALUES (1, 'Windows 2003 Host', 'win2003.png');
INSERT INTO manage_device_type VALUES (2, 'Windows XP Host', NULL);
INSERT INTO manage_device_type VALUES (3, 'Windows 2000 Host', 'win2000.png');
INSERT INTO manage_device_type VALUES (4, 'Windows NT4 Host', 'winnt.png');
INSERT INTO manage_device_type VALUES (5, 'Windows 9x Host', NULL);
INSERT INTO manage_device_type VALUES (6, 'Linux Host', 'linux.png');
INSERT INTO manage_device_type VALUES (7, 'Router', 'router.png');
INSERT INTO manage_device_type VALUES (8, 'Switch', 'switch.png');
INSERT INTO manage_device_type VALUES (9, 'Other', 'other.png');
INSERT INTO manage_templates VALUES (1, 'Mail Server (Basic)', '110;25');
INSERT INTO manage_templates VALUES (2, 'Mail Server (Enhanced)', '110;995;25;143;993;80;443');
INSERT INTO manage_templates VALUES (3, 'Switch/Router', '23');
INSERT INTO manage_templates VALUES (4, 'Web Server', '80;443');
INSERT INTO manage_templates VALUES (5, 'LDAP Server - Windows 200x Domain Controler', '389');
INSERT INTO manage_templates VALUES (6, 'DNS Server', '53');


