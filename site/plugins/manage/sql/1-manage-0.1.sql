
CREATE TABLE manage_host ( id mediumint(8) unsigned NOT NULL default '0', uptime bigint(20) default NULL, type mediumint(8) unsigned NOT NULL default '0', services text, statut text);
CREATE TABLE manage_alerts (idh mediumint(8) unsigned NOT NULL default '0', datetime datetime NOT NULL default '0000-00-00 00:00:00', ids mediumint(8) unsigned default '0', message text, note text, ida mediumint(9) unsigned NOT NULL auto_increment, PRIMARY KEY  (ida));
CREATE TABLE manage_host_services ( id mediumint(8) unsigned NOT NULL default '0', services mediumint(8) unsigned NOT NULL default '0', statut text);

alter table host add manage char(3) default '' not null after disabled;

INSERT INTO settings VALUES ('manage_events', '5');
INSERT INTO settings VALUES ('manage_poller', '0');
INSERT INTO settings VALUES ('manage_date', '0');




