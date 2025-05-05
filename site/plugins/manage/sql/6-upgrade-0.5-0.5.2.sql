drop TABLE `manage_device_type`;

ALTER TABLE `manage_host` change `type` `type` varchar(255);
ALTER TABLE `manage_host` DROP `services`;
ALTER TABLE `manage_host` DROP `force`;
  
INSERT INTO settings VALUES ('manage_enable', '');
INSERT INTO settings VALUES ('manage_accounts_tab', '-1');
INSERT INTO settings VALUES ('manage_accounts_settings', '-1');
INSERT INTO settings VALUES ('manage_accounts_reporting', '-1');
INSERT INTO settings VALUES ('manage_accounts_sites', '-1');
INSERT INTO settings VALUES ('manage_accounts_groups', '-1');
INSERT INTO settings VALUES ('manage_weathermap_enable', '');
INSERT INTO settings VALUES ('manage_weathermap_theme', 'default');
INSERT INTO settings VALUES ('manage_syslog', '5');
INSERT INTO settings VALUES ('manage_syslog_level', 'LOG_WARNING');
INSERT INTO settings VALUES ('manage_snmp', '5');
INSERT INTO settings VALUES ('manage_snmp_ip', 'Is not set');
INSERT INTO settings VALUES ('manage_snmp_version', '2');
INSERT INTO settings VALUES ('manage_snmp_community', 'public');
INSERT INTO settings VALUES ('manage_snmp_port', '162');
INSERT INTO settings VALUES ('manage_disable_site_view', '0');
INSERT INTO settings VALUES ('manage_tree_analyze', '1');
INSERT INTO settings VALUES ('manage_double_email', '0');
INSERT INTO settings VALUES ('manage_disabled_text', '0');
