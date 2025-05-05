
DROP TABLE `manage_host`;
DROP TABLE `manage_method`;
DROP TABLE `manage_alerts`;
DROP TABLE `manage_tcp`;
DROP TABLE `manage_device_type`;
DROP TABLE `manage_templates`;
DROP TABLE `manage_groups`;
DROP TABLE `manage_services`;
DROP TABLE `manage_process`;
DROP TABLE `manage_poller_output`;
DROP TABLE `manage_sites`;
DROP TABLE `manage_host_services`;
DROP TABLE `manage_admin_link`;
DROP TABLE `manage_uptime_method`;

ALTER TABLE `host` DROP `manage`;

DELETE FROM `settings` WHERE `name` like 'manage\_%';

DELETE FROM `plugin_update_info` WHERE `plugin` = 'manage';
