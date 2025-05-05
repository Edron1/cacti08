<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

chdir('../../');

include(dirname(__FILE__) . "/../../include/global.php");

include_once($config["base_path"] . "/lib/snmp.php");

include_once(dirname(__FILE__) . '/manage_lib.php');
sleep(10);

$db = mysql_connect("$database_hostname", "$database_username", "$database_password");
mysql_select_db("$database_default",$db);

$manage_enable = db_fetch_cell("select value from settings where name='manage_enable'");

$argv = $_SERVER["argv"];

if (isset($argv[1])) {
  $force = $argv[1];
  if ($argv[1] == "/force") {
    print "Forcing\n";
  }
} else {
  $force = "";
}

if ( ($manage_enable == "on") || ($force == "/force") ) {

  cacti_log("Checking Manage", true, "MANAGE");
  $uptimearray = "SELECT host.id, host.hostname, host.description, host.snmp_community, host.snmp_version, host.snmp_username, host.snmp_password, host.snmp_port, host.snmp_timeout, manage_host.uptime, manage_host.statut FROM host,manage_host where manage = 'on' and disabled <> 'on' and host.id=manage_host.id";
  $queryrows = db_fetch_assoc($uptimearray) or die (mysql_error() );

  $nb_pollers = db_fetch_cell("select value from settings where name='manage_poller_hosts'");
  if ( (intval($nb_pollers) <= 0) || ($nb_pollers == "") || ($nb_pollers == "is not set") ) {       //in case "concurrent Manage Pollers" is empty
    $nb_pollers=5;
  }
  print $nb_pollers." pollers";
  
  $H=array("");
  $i=1;
  foreach ($queryrows as $q_row) {
    $H[$i]=$q_row["id"];
    $i++;
  }

  print ", " . (count($H)-1)." hosts, ";

  $N=intval((count($H)-1)/$nb_pollers);
  print $N." for each poller";

  if ($N == 0) {
    $N++;
    print "\ncorrection : ".$N." hosts for each poller";
  }

  $manage_reporting_daily = db_fetch_cell("select value from settings where name='manage_reporting_daily'");
  $manage_weathermap_enable = db_fetch_cell("select value from settings where name='manage_weathermap_enable'");
  if ($manage_weathermap_enable == "on") {
    db_execute("DELETE from settings where name like 'manage\_poller\_number\_%'") or die;
	print "\nErasing poller status..";
    for ($i=0;$i<$nb_pollers;$i++) {
	  print ".".($i+1);
      db_execute("INSERT INTO settings ( name , value ) VALUES ('manage_poller_number_" . ($i+1) . "', '0')") or die;
    }
  }
  
  $w = db_fetch_cell("select value from settings where name='manage_poller_plus'");
  $ff="snmp";
  if ($w == 1) {
    $ff="rrdtool";
  }
  if ($w == 2) {
    $ff="wmi_vbs";
  }
  if ($w == 3) {
    $ff="wmi_perl";
  }

  $p = dirname(__FILE__);

  if ($config["cacti_server_os"] == "unix") {
	$command_string = db_fetch_cell("select value from settings where name='path_php_binary'");
	$extra_args = "-q " . $p . "/manage_check.php";
	$extra_args_wm = "-q " . $p . "/manage_weathermap_run.php";
	$extra_args_rpt = "-q " . $p . "/manage_reports_run.php";
  } else {
    $v = db_fetch_cell("select value from settings where name='manage_use_patch'");
	if ( ($v == 999) || ($v == "") ) {            //in case manage_use_patch is empty
	  $v=0;
	}
	if ($v == 1) {
	  $command_string = "php-manage.exe";
	} else {
	  $command_string = db_fetch_cell("select value from settings where name='path_php_binary'");
	}
 	$extra_args = strtolower($p . "/manage_check.php");
	$extra_args_wm = strtolower($p . "/manage_weathermap_run.php");
	$extra_args_rpt = strtolower($p . "/manage_reports_run.php");
  }

  $pt1 = dirname ($command_string);
  $pt2 = basename ($command_string);

  $active_pollers=0;
  for ($i=0;$i<$nb_pollers;$i++) {
    print "\nlaunching poller ".($i+1);
    if ( (1+$N*$i) > (count($H)-1) ) {
      print " skipping";
	} else {
	  $active_pollers++;
	  if ( ($i+1) == $nb_pollers) {
        print " max ";
        manage_go((1+$N*$i), (count($H)-1), $H, $ff, $pt1, $pt2, $extra_args, ($i+1));
	  } else {
	    manage_go((1+$N*$i), $N*($i+1), $H, $ff, $pt1, $pt2, $extra_args, ($i+1));
	  }
	}
  }

  print "\n".$active_pollers." active pollers";

  if ($manage_reporting_daily == "on") {
	
    print "\nGenerating reports...\n";
  $pt=$pt1."/".$pt2;
  if ($pt1 == ".") {
    $pt=$pt2;
  }
  exec($pt. " " . $extra_args_rpt);
  }
  
  if ($manage_weathermap_enable == "on") {
	
	$polling_interval = db_fetch_cell("select value from settings where name='poller_interval'");
	$end=false;
	$waiting_loop=0;
	while($end == false) {
	  print "\nWaiting checks...";
	  $flag_end=1;
      for ($i=0;$i<$active_pollers;$i++) {
        $result = db_fetch_cell("SELECT value FROM `settings` where name='manage_poller_number_" . ($i+1) . "'");
		if ($result == "0") {
		  $flag_end=0;
		}
	    print $result;
      }
	  if ($waiting_loop > intval($polling_interval/2)) {
	    cacti_log("Manage taking too long time to run.", true, "MANAGE");
	    $end=true;
	  }
	  if ($flag_end == 0) {
	    sleep(2);
	  }
	  if ($flag_end == 1) {
	    $end=true;
	  }
	  $waiting_loop++;
    }

    print "\nPreprocessing maps...\n";
  $pt=$pt1."/".$pt2;
  if ($pt1 == ".") {
    $pt=$pt2;
  }
  exec($pt. " " . $extra_args_wm);
  }

} else {
  cacti_log("Skipping Manage", true, "MANAGE");
  print ("Manage is disabled.");
}


function manage_go($deb, $fin, $H2, $fff, $p1, $p2, $extra_args, $c) {
  global $config;
//print " - start ".$deb." - end ".$fin."\n";
  $extra_args2=" ".$fff;
  for ($j=0;($deb+$j)<$fin+1;$j++) {
    $extra_args2 .= " ".$H2[$deb+$j];
  }

  manage_execInBackground($p1."/",$p2, $extra_args, $extra_args2, $c);
}


?>

