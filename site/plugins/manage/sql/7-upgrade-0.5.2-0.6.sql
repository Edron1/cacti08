CREATE TABLE manage_admin_link ( id MEDIUMINT(8) NOT NULL, data TEXT );
CREATE TABLE manage_uptime_method ( id MEDIUMINT(8) NOT NULL, data TEXT );

INSERT INTO settings VALUES ('manage_uptime_method', '0');
INSERT INTO settings VALUES ('manage_uptime_cisco', '0');