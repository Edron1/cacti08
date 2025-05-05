CREATE TABLE manage_groups ( id MEDIUMINT(8) NOT NULL auto_increment, name TEXT, PRIMARY KEY  (id) );

alter table manage_host add `group` MEDIUMINT(8) default '0';

INSERT INTO manage_groups VALUES (1, 'Test');
