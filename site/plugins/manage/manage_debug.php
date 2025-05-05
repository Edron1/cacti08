<?php

chdir('../../');

include("./include/auth.php");

include_once("./include/global.php");

global $config;

$p = dirname(__FILE__);
include_once($p . '/manage_lib.php');
$permit=manage_accounts_permit("settings");
if ($permit == 0) {
  print "Unauthorized access.";
  exit;
}

include_once($config["library_path"] . "/database.php");

$infos=plugin_manage_version ();
print "Current version : ".$infos['version'];

print "<br>--------------------------------------------------------------------------------<br>";

print "Upgrade needed : ".manage_check_version();
if (manage_check_version() == "") {
  print "none";
} else {
  print manage_check_version();
}
print "<br>--------------------------------------------------------------------------------<br>";

print "<table><tr>";

manage_debug(1);








?>



