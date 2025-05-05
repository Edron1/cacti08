<?php

//important
//no protection, no code, only functions

function manage_check_version () {
	global $config, $database_default;

	include_once($config["library_path"] . "/database.php");

	$install = "full";

//print " 1 ".$install;
	$sql = "SHOW COLUMNS FROM host FROM `" . $database_default . "`";
	$result = mysql_query($sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if ($row['Field'] == 'manage') {
			$install = "upgrade-0.1";
		}
	}

//print " 2 ".$install;
	$sql = "SHOW TABLES FROM `" . $database_default . "`";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_row($result)) {
		if ($row[0] == "manage_host") {
			$sql2 = "SHOW COLUMNS FROM manage_host FROM `" . $database_default . "`";
			$result2 = mysql_query($sql2);

			while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
				if ($row2['Field'] == 'force') {
					$install = "upgrade-0.2";
				}
			}
		}
	}

//print " 3 ".$install;
	if ($install != "full") {
		$sql = "SHOW COLUMNS FROM manage_host FROM `" . $database_default . "`";
		$result = mysql_query($sql);

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if ($row['Field'] == 'group') {
				$install = "upgrade-0.3";
			}
		}
	}

//print " 4 ".$install;
	$sql = "SELECT count(value) AS total FROM settings WHERE name='manage_poller_hosts'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['total'] == 1) {
		$install="upgrade-0.4.2";
	}

//print " 5 ".$install;
	$sql = "SELECT count(value) AS total FROM settings WHERE name='manage_cycle_delay'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['total'] == 1) {
		$install="upgrade-0.5";
	}

//print " 6 ".$install;
    if ($install != "full") {
		$sql = "SHOW COLUMNS FROM manage_host FROM `" . $database_default . "`";
		$result = mysql_query($sql);

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if ($row['Field'] == 'type') {
			  if ($row['Type'] == 'varchar(255)') {
				$install = "upgrade-0.5.2";
			  }
			}
		}
    }

//print " 7 ".$install;
	$sql = "SHOW TABLES FROM `" . $database_default . "`";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_row($result)) {
	  if ($row[0] == "manage_admin_link") {
		$install = "";
	  }
	}

//print " 8 ".$install;
	return $install;

}




function manage_debug($pr) {
global $database_default, $plugins;

$m_d_output=0;

$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_host") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_host' : </td><td>".$debug."</td><td>(0.1)</td><tr>";
}

$debug="<font color=red><b>NOK</b></font>";
$sql = "show columns from host from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'manage') {
		$debug="<font color=green>OK</font>";
		$f=1;
	}
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'manage' from table 'host' : </td><td>".$debug."</td><td>(0.1)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_alerts") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_alerts' : </td><td>".$debug."</td><td>(0.1)</td><tr>";
}




$debug="<font color=green>OK</font> (not here)";
$sql = "show columns from manage_host from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'force') {
		$debug="<font color=red>NOK</font>";
		$f=1;
	}
}

if ($f == 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'force' from table 'manage_host' : </td><td>".$debug."</td><td>(0.2/0.5.2)</td><tr>";
}






$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_templates") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_templates' : </td><td>".$debug."</td><td>(0.2)</td><tr>";
  print "<tr><td colspan=3>--------------------------------------------------------------------------------------------------------------------------------------------</td></tr>";
}





$debug="<font color=red><b>NOK</b></font>";
$sql = "show columns from manage_host from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'group') {
		$debug="<font color=green>OK</font>";
		$f=1;
	}
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'group' from table 'manage_host' : </td><td>".$debug."</td><td>(0.3)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_groups") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_groups' : </td><td>".$debug."</td><td>(0.3)</td><tr>";
  print "<tr><td colspan=3>--------------------------------------------------------------------------------------------------------------------------------------------</td></tr>";
}









$debug="<font color=red><b>NOK</b></font>";
$sql = "show columns from manage_alerts from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'oid') {
		$debug="<font color=green>OK</font>";
		$f=1;
	}
}

if ($f != 1) {
  $m_d_output++;
}


if ($pr == 1) {
  print "<td>Column 'oid' from table 'manage_alerts' : </td><td>".$debug."</td><td>(0.4)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_process") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_process' : </td><td>".$debug."</td><td>(0.4)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_services") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}


if ($pr == 1) {
  print "<td>Table 'manage_services' : </td><td>".$debug."</td><td>(0.4)</td><tr>";
}


$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_poller_hosts'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}


if ($pr == 1) {
  print "<td>Setting 'manage_poller_hosts' from table 'settings' : </td><td>".$debug."</td><td>(0.4)</td><tr>";
  print "<tr><td colspan=3>--------------------------------------------------------------------------------------------------------------------------------------------</td></tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_sites") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_sites' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_tcp") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_tcp' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}


$w=0;
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_host_services") {
    $w=1;
  }
}

if ($pr == 1) {
  if ($w == 1) {
    print "<td>Table 'manage_host_services' : </td><td><font color=green>NOK</font></td>";
	$m_d_output++;
  } else {
    print "<td>Table 'manage_host_services' : </td><td><font color=green>OK</font> (not here)</td>";
  }
  print "<td>(0.1/0.5)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_poller_output") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Table 'manage_poller_output' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}







$debug="<font color=red><b>NOK</b></font>";
$sql = "show columns from manage_host from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'thresold_ref') {
		$debug="<font color=green>OK</font>";
		$f=1;
	}
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'thresold_ref' from table 'manage_host' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show columns from manage_host from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'thresold') {
		$debug="<font color=green>OK</font>";
		$f=1;
	}
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'thresold' from table 'manage_host' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}








$debug="<font color=red><b>NOK</b></font>";
$sql = "show columns from manage_host from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'mail') {
		$debug="<font color=green>OK</font>";
		$f=1;
	}
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'mail' from table 'manage_host' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}



$debug="<font color=red><b>NOK</b></font>";
$sql = "show columns from manage_groups from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'site_id') {
		$debug="<font color=green>OK</font>";
		$f=1;
	}
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'site_id' from table 'manage_groups' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}






$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_global_email'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_global_email' from table 'settings' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}







$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_double_email'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_double_email' from table 'settings' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
}






$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_poller_plus'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_poller_plus' from table 'settings' : </td><td>".$debug."</td><td>(0.5)</td><tr>";
  print "<tr><td colspan=3>--------------------------------------------------------------------------------------------------------------------------------------------</td></tr>";
}

















$debug="<font color=red><b>NOK</b></font>";
$sql = "SHOW COLUMNS FROM manage_host FROM `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if ($row['Field'] == 'type') {
			  if ($row['Type'] == 'varchar(255)') {
				$debug="<font color=green>OK</font>";
				$f=1;
				}
			}
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'type' from table 'manage_host' : </td><td>".$debug."</td><td>(0.5.2)</td><tr>";
}












$debug="<font color=green>OK</font> (not here)";
$sql = "show columns from manage_host from `" . $database_default . "`";
$result = mysql_query($sql) or die (mysql_error());
$f=0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row['Field'] == 'services') {
		$debug="<font color=red>NOK</font>";
		$f=1;
	}
}

if ($f == 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Column 'services' from table 'manage_host' : </td><td>".$debug."</td><td>(???/0.5.2)</td><tr>";
}






$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_accounts_tab'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_accounts_tab' from table 'settings' : </td><td>".$debug."</td><td>(0.5.2)</td><tr>";
}






$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_accounts_settings'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_accounts_settings' from table 'settings' : </td><td>".$debug."</td><td>(0.5.2)</td><tr>";
}







$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_accounts_reporting'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_accounts_reporting' from table 'settings' : </td><td>".$debug."</td><td>(0.5.2)</td><tr>";
}







$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_accounts_sites'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_accounts_sites' from table 'settings' : </td><td>".$debug."</td><td>(0.5.2)</td><tr>";
}







$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_accounts_groups'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_accounts_groups' from table 'settings' : </td><td>".$debug."</td><td>(0.5.2)</td><tr>";
}















$w=0;
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_device_type") {
    $w=1;
  }
}

if ($pr == 1) {
  if ($w == 1) {
    print "<td>Table 'manage_device_type' : </td><td><font color=red>NOK</font></td>";
	$m_d_output++;
  } else {
    print "<td>Table 'manage_device_type' : </td><td><font color=green>OK</font> (not here)</td>";
  }
  print "<td>(0.2/0.5.2)</td><tr>";
}  
  








$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_enable'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_enable' from table 'settings' : </td><td>".$debug."</td><td>(0.5.2)</td><tr>";
  print "<tr><td colspan=3>--------------------------------------------------------------------------------------------------------------------------------------------</td></tr>";
}












$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_uptime_method") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}


if ($pr == 1) {
  print "<td>Table 'manage_uptime_method' : </td><td>".$debug."</td><td>(0.6)</td><tr>";
}








$debug="<font color=red><b>NOK</b></font>";
$sql = "show tables from `" . $database_default . "`";
$result = mysql_query($sql);
$f=0;
while ($row = mysql_fetch_row($result)) {
  if ($row[0] == "manage_admin_link") {
    $debug="<font color=green>OK</font>";
	$f=1;
  }
}

if ($f != 1) {
  $m_d_output++;
}


if ($pr == 1) {
  print "<td>Table 'manage_admin_link' : </td><td>".$debug."</td><td>(0.6)</td><tr>";
}





$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_uptime_method'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_uptime_method' from table 'settings' : </td><td>".$debug."</td><td>(0.6)</td><tr>";
}






$f=0;
$w = db_fetch_cell("SELECT count(value) as total FROM settings where name='manage_uptime_cisco'");
if ($w == 1) {
  $debug="<font color=green>OK</font>";
  $f=1;
} else {
  $debug="<font color=red><b>NOK</b></font>";
}

if ($f != 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'manage_uptime_cisco' from table 'settings' : </td><td>".$debug."</td><td>(0.6)</td><tr>";
  print "<tr><td colspan=3>--------------------------------------------------------------------------------------------------------------------------------------------</td></tr>";
}


















$f=0;
$w = db_fetch_cell("SELECT value FROM settings where name='manage_use_patch'");
if ($w == 1) {
  $debug="<font color=red><b>NOK</b></font>";
  $f=1;
} else {
  $debug="<font color=green>OK</font>";
}

if ($f == 1) {
  $m_d_output++;
}

if ($pr == 1) {
  print "<td>Setting 'Use Windows PHP Patch' : </td><td>".$debug."</td><td>Don't use. Select 'No'.</td><tr>";
}











$f=0;
$w = db_fetch_cell("SELECT value FROM settings where name='manage_link_method'");
if ($w == 1) {
  $x = db_fetch_cell("SELECT value FROM settings where name='manage_relay_ip'");
  if ($x == "") {
    $debug="<font color=red><b>NOK</b></font></td><td>Please select a relay ip.</td><tr>";
	$f=1;
  } else {
    $debug="<font color=green>OK</font></td><td></td><tr>";
  }

  if ($pr == 1) {
    print "<td>Setting 'Relay daemon ip' : </td><td>".$debug;
  }

}

if ($f == 1) {
  $m_d_output++;
}













$w = db_fetch_cell("SELECT value FROM settings where name='manage_events'");
if ($w != 5) {
  $x = db_fetch_cell("SELECT value as total FROM settings where name='manage_global_email'");
  if ($x == "") {
    if ($pr == 1) {
	  print "<td>Setting 'Global e-mail to sending alerts' : </td><td><font color=red><b>NOK</b></font></td><td>Put an email address.</td><tr>";
    }
	$m_d_output++;
  } else {
  
    if ($pr == 1) {
	  print "<td>Setting 'Global e-mail to sending alerts' : </td><td><font color=green>OK</font></td><td></td><tr>";
	}
  }
}







$w = db_fetch_cell("SELECT value FROM settings where name='manage_events'");
if ($w != 5) {
	if ((!in_array('settings', $plugins)) &&
		(db_fetch_cell("SELECT directory FROM plugin_config WHERE directory='settings' AND status=1") == "")) {
		    if ($pr == 1) {
			  print "<td>Plugin Settings : </td><td><font color=red><b>NOK</b></font></td><td>Install and enable Plugin Settings.</td><tr>";
            }
			$m_d_output++;	
	} else {
		    if ($pr == 1) {
		  	  print "<td>Plugin Settings : </td><td><font color=green>OK</font></td><td></td><tr>";
		    }
	}

}


	






$w = db_fetch_cell("SELECT value FROM settings where name='manage_netsend_events'");
if ($w != 5) {
  $x = db_fetch_cell("SELECT value FROM settings where name='manage_send'");
  if ($x == "") {
    if ($pr == 1) {
	  print "<td>Setting 'Stations that will receive 'net send' alerts' : </td><td><font color=red><b>NOK</b></font></td><td>Put a Windows computer name.</td><tr>";
    }
	$m_d_output++;
  } else {
    if ($pr == 1) {
	  print "<td>Setting 'Stations that will receive 'net send' alerts' : </td><td><font color=green>OK</font></td><td></td><tr>";
	}
  }
}






$w2 = db_fetch_cell("SELECT value FROM settings where name='manage_netsend_events'");
if ($w2 != 5) {
  $w = db_fetch_cell("SELECT value FROM settings where name='manage_netsend_method'");
  if ($w == 1) {
    $x = db_fetch_cell("SELECT value FROM settings where name='manage_perl'");
    if ($x == "") {
      if ($pr == 1) {
	    print "<td>Setting 'Perl Binary Path' : </td><td><font color=red><b>NOK</b></font></td><td>Put the path to perl binary.</td><tr>";
      }
	  $m_d_output++;
    } else {
      if ($pr == 1) {
	    print "<td>Setting 'Perl Binary Path' : </td><td><font color=green>OK</font></td><td></td><tr>";
	  }
    }
  }
}






$w = db_fetch_cell("SELECT value FROM settings where name='manage_thold'");
if ($w == 1) {
	if ((!in_array('thold', $plugins)) &&
		(db_fetch_cell("SELECT directory FROM plugin_config WHERE directory='thold' AND status=1") == "")) {
		    if ($pr == 1) {
			  print "<td>Plugin Thold : </td><td><font color=red><b>NOK</b></font></td><td>Install and enable Plugin Thold.</td><tr>";
            }
			$m_d_output++;	
	} else {
		    if ($pr == 1) {
		  	  print "<td>Plugin Thold : </td><td><font color=green>OK</font></td><td></td><tr>";
		    }
	}

}







$w = db_fetch_cell("SELECT value FROM settings where name='manage_poller_hosts'");
if ($w == "") {
		    if ($pr == 1) {
			  print "<td>Concurrent Manage Pollers : </td><td><font color=red><b>NOK</b></font></td><td>Minimum 1.</td><tr>";
            }
			$m_d_output++;	
} else {
		    if ($pr == 1) {
		  	  print "<td>Concurrent Manage Pollers : </td><td><font color=green>OK</font></td><td></td><tr>";
		    }
}











$w = db_fetch_cell("SELECT count(*) FROM `manage_host` WHERE `group` = '0' || `group` = '' || `group` = 'null'");
if ($w == "0") {
		    if ($pr == 1) {
		  	  print "<td>Devices : </td><td><font color=green>OK</font></td><td></td><tr>";
		    }
} else {
$wz = db_fetch_assoc("SELECT id FROM `manage_host` WHERE `group` = '0' || `group` = '' || `group` = 'null'");
$mm="";

foreach ($wz as $h) {
  $zeze = db_fetch_cell("SELECT description FROM host WHERE id = '".$h["id"]."'");

  $mm .= $zeze.", ";
}

		    if ($pr == 1) {
			  print "<td>".$w." devices : </td><td><font color=red><b>NOK</b></font></td><td>These hosts need to be associated to a site and a group : ".$mm."</td><tr>";
            }
			$m_d_output++;
}








$settings_user = db_fetch_cell("select count(*) from settings where name like 'manage\_%' and name NOT LIKE '%poller\_number%' and name like '%\_".$_SESSION["sess_user_id"]."'");
if ($settings_user != 26) {
		    if ($pr == 1) {
			  print "<td>User settings : </td><td><font color=red><b>NOK</b></font></td><td>Save your settings (".$settings_user.").</td><tr>";
            }
			$m_d_output++;	
} else {
		    if ($pr == 1) {
		  	  print "<td>User settings : </td><td><font color=green>OK</font></td><td>(".$settings_user.")</td><tr>";
		    }
}







return $m_d_output;

}



function manage_accounts_permit($page) {
  global $database_default, $database_type, $database_port, $database_password, $database_username, $database_hostname, $config;
  $permit=0;
  if (isset($_SESSION["sess_user_id"])) {
    $accounts=db_fetch_cell("select value from settings where name='manage_accounts_".$page."'");

//    $link = mysql_connect("$database_hostname", "$database_username", "$database_password") or die("connexion impossible");
//    mysql_select_db("$database_default",$link) or die("database unknown");
//    $result = mysql_query("select value from settings where name='manage_accounts_".$page."'");
//    $row_arrayCde = mysql_fetch_row($result);
//    $accounts=$row_arrayCde[0];
	
    $accounts=explode(';', $accounts);
    foreach ($accounts as $account) {
      if ( ($account == "-1") || ($account == $_SESSION["sess_user_id"]) || ($_SESSION["sess_user_id"] == 1) ) {
        $permit=1;
      }
    }
  }
  return $permit;
}


function manage_mail($private_alert_address, $global_alert_address) {
  global $config;
  if ($private_alert_address != "") {
	$alert_address = $private_alert_address;
	$double_alert_address = db_fetch_cell("select value from settings where name='manage_double_email'");
	if ($double_alert_address == "1") {
	  $alert_address .= ",".$global_alert_address;
	}
  } else {
	$alert_address = $global_alert_address;
  }
  
  $mail_list="";
  $emails=explode(",",$alert_address);
  foreach ($emails as $mail) {
    $pos = strpos($mail, "@");
    if ($pos === false) {
	  $user_mail=db_fetch_cell("SELECT data FROM plugin_thold_contacts where user_id='".$_SESSION["sess_user_id"]."'");
      $mail_list .= ",".$user_mail;
    } else {
      $mail_list .= ",".$mail;
    }
  }
  
  return $mail_list;
}



function manage_ds_g($idd, $pp) {
		  $t = db_fetch_cell("SELECT id FROM data_template where name = 'TCP'");
          $t2 = db_fetch_cell("SELECT id FROM snmp_query where name='TCP'");
		  $t4 = db_fetch_cell("SELECT id FROM data_input where name='Get Script Server Data (Indexed)'");



		  $m6 = db_fetch_cell("SELECT id FROM snmp_query_graph where name = 'TCP'");
		  $t3 = db_fetch_cell("SELECT id FROM graph_templates where name = 'TCP'");

$d = db_fetch_cell("SELECT description FROM host where id = ".$idd);
		  $oi = explode(";", $pp);
		  foreach ($oi as $voi) {
$voi=trim($voi);


$m = db_fetch_cell("SELECT max(id) FROM data_template");



$mz = db_fetch_cell("SELECT count(id) FROM data_local where data_template_id = ".$t. " AND snmp_index = ".$voi." AND host_id =".$idd);
if ($mz == "0") {


db_execute("INSERT INTO data_local VALUES ('', '".$t."', '".$idd."', '".$t2."', '".$voi."')");

$m2 = db_fetch_cell("SELECT max(id) FROM data_local");

$m4 = db_fetch_cell("SELECT min(id) FROM data_template_data where data_template_id =".$t);

db_execute("INSERT INTO data_template_data VALUES ('', ".$m4.", ".$m2.", ".$t.", ".$t4.", NULL, '".$d." - TCP State - ".$voi."', '".$d." - TCP State - ".$voi."', '<path_rra>/".$idd."_tcp_".$voi.".rrd', NULL, 'on', NULL, 300, NULL)");

$m3 = db_fetch_cell("SELECT max(id) FROM data_template_data");

db_execute("delete from data_template_data_rra where data_template_data_id=".$m3);

$uptimearray = "select rra_id from data_template_data_rra where data_template_data_id=".$m4;
$queryrows = db_fetch_assoc($uptimearray) or die (mysql_error() );

foreach ($queryrows as $q_row) {
  db_execute("insert into data_template_data_rra (data_template_data_id,rra_id) values (".$m3.",".$q_row["rra_id"].")");
}

$m5 = db_fetch_cell("SELECT min(id) FROM data_template_rrd where data_template_id =".$t);

db_execute("INSERT INTO data_template_rrd VALUES ('', '', ".$m5.", ".$m2.", ".$t.", NULL, 0, NULL, 0, NULL, 600, NULL, 1, NULL, 'TCP', NULL, 0)");

db_execute("INSERT INTO data_input_data VALUES (35, ".$m3.", '', 'ServiceIndex')");
db_execute("INSERT INTO data_input_data VALUES (36, ".$m3.", '', '".$voi."')");
db_execute("INSERT INTO data_input_data VALUES (37, ".$m3.", '', '".$m6."')");

////////////////////////////////////////////////////////////////////////////

db_execute("INSERT INTO graph_local VALUES ('', ".$t3.", ".$idd.", '0', '')");

$ma = db_fetch_cell("SELECT max(id) FROM graph_local");

$mb = db_fetch_cell("SELECT min(id) FROM graph_templates_graph where graph_template_id ='".$t3."'");

db_execute("INSERT INTO graph_templates_graph VALUES ('', '".$mb."', ".$ma.", ".$t3.", '0', 1 , '0', '".$d." - TCP State - ".$voi."', '".$d." - TCP State - ".$voi."', '0', 120, '0', 500, '0', '100', '0', '0', '0', '', '0', 'on', '0', 'on', '0', 2, '0', '', '0', '', '0', '', '0', 'on', '0', 1000, '0', '', '0', 'on', '0', '', '0', '')");
                                                                                               
$mc = db_fetch_cell("SELECT max(id) FROM data_template_rrd");
db_execute("update graph_templates_item set task_item_id='".$mb."' where id='".$mc."'");

$uptimearray = "SELECT * FROM graph_templates_item where graph_template_id = '".$t3."' ORDER BY id ASC limit 0,3";
$queryrows = db_fetch_assoc($uptimearray) or die (mysql_error() );

$i=0;
foreach ($queryrows as $q_row) {
 $arr[$i]=$q_row["id"];
  $i++;
}

db_execute("INSERT INTO graph_templates_item VALUES ('', '', ".$arr[0].", ".$ma.", ".$t3.", ".$mc.", 8,'FF' , 7, 0, 4, 'TCP'                       , '', ''  , 2, 1)");
db_execute("INSERT INTO graph_templates_item VALUES ('', '', ".$arr[1].", ".$ma.", ".$t3.", ".$mc.", 0,'FF' , 9, 0, 4, ''                          , '', 'on', 3, 2)");
db_execute("INSERT INTO graph_templates_item VALUES ('', '', ".$arr[2].", ".$ma.", ".$t3.",       0, 0,'FF' , 1, 0, 1, '(1 = Running, 0 = Stopped)', '', ''  , 2, 3)");

///////////////////////////////////////////////////////////////////////////////

repopulate_poller_cache();
}

}

}


function manage_execInBackground($path, $exe, $args, $args2, $cnt) {
  global $conf;
  chdir($path);
  if (substr(php_uname(), 0, 7) == "Windows"){
//print "start \"manage\" \"" . $exe . "\" " . escapeshellarg($args). " ".$cnt. $args2;
    pclose(popen("start \"manage\" \"" . $exe . "\" " . escapeshellarg($args). " ".$cnt. $args2, "r"));
  } else {
    exec("./" . $exe . " " . $args . " ". $cnt .$args2 . " > /dev/null 2>&1 &");
  }
}



?>