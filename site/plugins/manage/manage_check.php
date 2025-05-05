<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

//scenarios
// 0 : host was nopoll				host is up			we are going to check : full writing to db, no event generated, full reporting, "UP"
// 1 : host was up/prob/treshold		host is up, uptime >	we are going to check :                                 event generated,     full reporting
// 2 : host was nopoll/up/prob/treshold	host is down			no check :             full writing to db, no event generated, full reporting, "DOWN"
// 4 : host was down				host is up, uptime >	we are going to check :                                 no event generated, full reporting, "UP"
// 5 : host was down				host is down
// 6 :							host is up, uptime <	we are going to check :                                no event generated, full reporting, "REBOOT"

chdir('../../');

include(dirname(__FILE__) . "/../../include/global.php");

include_once($config["base_path"] . "/lib/snmp.php");
include_once($config["base_path"] . "/lib/ping.php");

global $config, $database_default;

$db = mysql_connect("$database_hostname", "$database_username", "$database_password");
mysql_select_db("$database_default",$db);

$argv = $_SERVER["argv"];

$nb_pol = $argv[1];

$method = $argv[2];

$query = "SELECT
	host.id,
	host.hostname,
	host.description,
	host.snmp_community,
	host.snmp_version,
	host.snmp_username,
	host.snmp_password,
	host.snmp_port,
	host.availability_method,
    host.status,
	snmp_auth_protocol,
	snmp_priv_passphrase,
	snmp_priv_protocol,
	snmp_context,
	host.snmp_timeout,
	manage_host.uptime,
	manage_host.statut,
	manage_host.thresold_ref,
	manage_host.thresold,
	host.ping_timeout,
	host.ping_port,
	host.ping_method,
	host.ping_retries
	FROM host
	left join manage_host on host.id=manage_host.id
	WHERE manage = 'on'
	AND disabled <> 'on'";

$query .= " and (";
for ($counter=3;$counter<count($argv);$counter++) {
	$query .= " host.id ='".$argv[$counter]. "' || ";
}
$query .= " host.id ='-1')";

$hosts  = db_fetch_assoc($query);

$force_host_mib=db_fetch_cell("select value from settings where name='manage_uptime_method'");

foreach ($hosts as $host) {

  $host_mib="";

  $scenario="";
  
  print "----------------------------------------------\nid : " . $host["id"];

  $old_uptime = $host["uptime"];
  if ($old_uptime == "") {
	$old_uptime = "0";
  }

  $old_statut = $host["statut"];
  if ($host["statut"] == "") {
	$old_statut = "nopoll";
  }
  print " , statut was : " . $old_statut;

  if ( ($host["availability_method"] == "1") || ($host["availability_method"] == "2") ) {     //snmp enabled
	print " , SNMP enabled";
	
	

	
  $host_mib=db_fetch_cell("select data from manage_uptime_method where id='".$host["id"]."'");
  
  $new_uptime_host = 0;
  if ( ($force_host_mib=="on") && ( ($host_mib=="1") || ($host_mib=="") ) ) {
    print " (host MIB=";
    $new_uptime_host = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.25.1.1.0",$host["snmp_version"], $host["snmp_username"], $host["snmp_password"], $host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"], $host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], $host["ping_retries"], SNMP_WEBUI);
    if (!is_numeric($new_uptime_host)) {
	  $new_uptime_host=0;
	}
	print $new_uptime_host.")";
	$new_uptime=$new_uptime_host;
  }

  $new_uptime_mib2 = 0;
  if ( ($force_host_mib == "on") && ($host_mib == "1") ) {
//  
  } else {
    print " (MIB II=";
    $new_uptime_mib2 = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.3.0", $host["snmp_version"], $host["snmp_username"], $host["snmp_password"], $host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"], $host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], $host["ping_retries"], SNMP_WEBUI);

    if (!is_numeric($new_uptime_mib2)) {
	  $new_uptime_mib2=0;
	}
	print $new_uptime_mib2.")";
	$new_uptime=$new_uptime_mib2;
  }

  if ( ($force_host_mib == "on") && ($host_mib == "") ) {
    if ($new_uptime_host > $new_uptime_mib2) {
      $host_mib="1";
	  $new_uptime=$new_uptime_host;
    } else {
	  $host_mib="0";
	  $new_uptime=$new_uptime_mib2;
    }
  }
  
  $result=$new_uptime;
  
  db_execute("delete from manage_uptime_method where id='" . $host["id"] . "'"); 	
  db_execute("insert into manage_uptime_method ( id , data ) values ( '" . $host["id"] . "' , '".$host_mib."' ) ");

	
	
	
	
	  
	if ( ($result == "") || ($result == "0") || ($result == "U") ) {         //host is down
	  $result = "0";
	}
	  
  }
  
  if ($host["availability_method"] == "3") {     //ping enabled
    print " , Ping enabled";
    $ping = new Net_Ping;
    $ping->host["hostname"] = $host["hostname"];
    $ping->ping($host["availability_method"], $host["ping_method"], $host["ping_timeout"], $host["ping_retries"]);
		
    if ($ping->ping_status == "down") {
      $result = "0";
    } else {
      $result = "OK";
    }
  }
	
  if ($host["availability_method"] == "0") {     //none
	print " , no SNMP or Ping";
	$result="-1";
  }
 
    print ", result is ".$result."\n";
   
    if ($result == "0") {
      $new_uptime = $old_uptime;
      print " Host : " . $host["hostname"] . " (" . $host["description"] . ") is not responding !";
	  $host_statut="down";
    } else {
	  $new_uptime = $result;
	  print " Host : " . $host["hostname"] . " (" . $host["description"] . "), old uptime was : " . $old_uptime . ", new is : " . $new_uptime;
	  $host_statut="up";
    }

    $today = getdate();
    $date = $today['year']."/".$today['mon']."/".$today['mday'].", ".$today['hours'].":".$today['minutes'].":".$today['seconds'];

    $report = "";
    $problem_detected="0";
    $is_host_here = db_fetch_cell("SELECT count(id) FROM `manage_host` where id='" . $host["id"] . "'");

    $continue=1;
    if ( ($host_statut == 'down') && ( ($old_statut == 'nopoll') || ($old_statut == 'up') || ($old_statut == 'prob') || ($old_statut == 'treshold') ) ) {
      if ($is_host_here == "1") {
	  	db_execute("UPDATE manage_host SET thresold = '" . ($host["thresold"]+1) ."' where id='" . $host["id"] . "'");
		print "\ntreshold is now : ".($host["thresold"]+1)."\n";
		if (($host["thresold"]+1) == "1") {
		  db_execute("UPDATE manage_host SET statut = 'treshold' where id='" . $host["id"] . "'");
		}
	    if (($host["thresold"]+1) < $host["thresold_ref"]) {
		  $continue=0;
		}
		
	  }
    }
		
    if ($continue == 1) {
    
	  $scenario = "";
	
      if ( ($old_statut == "nopoll") && ($host_statut == 'up') && ($scenario == '') ) {
        $scenario = "0";
        print "\nScenario : ".$scenario."\n";
	    $full_writing_to_db = "1";
	    $event_generated = "0";
	    $full_reporting = "1";
	    include($config["base_path"] . "/plugins/manage/manage_do_check.php");
        if ($problem_detected == "1") {
          $global_statut="prob";
        } else {
          $global_statut="up";
	    }
	    $report .= "<br>Manage global status : ".$global_statut." (uptime ".$old_uptime." -> ".$new_uptime.")";
	    $report .= "<br><br>Cacti host id : ".$host["id"];
	    if ($is_host_here == "0") {
	      db_execute("INSERT INTO manage_host ( id , uptime, type, statut, thresold, thresold_ref, mail ) VALUES ('" . $host["id"] . "', '" . $new_uptime . "', 'none', '".$global_statut."', '0', '1', '')");
	    } else {
          db_execute("UPDATE manage_host SET statut = '".$global_statut."', `uptime` = '".$new_uptime."', thresold = '0' where id='" . $host["id"] . "'");
	    }
	    manage_logger("", $host["id"], "up", "Host is ", $host["hostname"] , $host["description"], $old_uptime, $new_uptime, "host", "", $old_statut, "1", $report);
      }

      if ( ( ($old_statut == "nopoll") || ($old_statut == "up") || ($old_statut == "prob") || ($old_statut == "treshold") ) && ($host_statut == 'down') && ($scenario == '') ) {
        $scenario = "2";
        print "\nScenario : ".$scenario."\n";
	    $full_writing_to_db = "1";
	    $event_generated = "0";
	    $full_reporting = "1";
	    include($config["base_path"] . "/plugins/manage/manage_do_check.php");
	    $global_statut="down";
	    $report .= "<br>Manage global status : ".$global_statut." (uptime ".$old_uptime." -> ".$new_uptime.")";
	    $report .= "<br><br>Cacti host id : ".$host["id"];

	    if ($is_host_here == "0") {
	      db_execute("INSERT INTO manage_host ( id , uptime, statut, thresold, thresold_ref, mail ) VALUES ('" . $host["id"] . "', '0', '".$global_statut."', '1', '1', '')");
	    } else {
          db_execute("UPDATE manage_host SET statut = '".$global_statut."' where id='" . $host["id"] . "'");
	    }
	    manage_logger("", $host["id"], "down", "Host is ", $host["hostname"] , $host["description"], "", "", "host", "", $old_statut, "1", $report);
      }
	
      if ( ( ($old_statut == "up") || ($old_statut == "prob") || ($old_statut == "treshold") ) && ($host_statut == 'up') && ($new_uptime >= $old_uptime) && ($scenario == '') ) {
        $scenario = "1";
        print "\nScenario : ".$scenario."\n";
	    $full_writing_to_db = "0";
	    $event_generated = "1";
	    $full_reporting = "1";
	    include($config["base_path"] . "/plugins/manage/manage_do_check.php");
        if ($problem_detected == "1") {
          $global_statut="prob";
        } else {
          $global_statut="up";
	    }
	    db_execute("UPDATE manage_host SET statut = '".$global_statut."', `uptime` = '".$new_uptime."', `thresold` = '0' where id='" . $host["id"] . "'");
      }

      if ( ($old_statut == "down") && ($host_statut == 'up') && ($new_uptime >= $old_uptime) && ($scenario == '') ) {
        $scenario = "4";
        print "\nScenario : ".$scenario."\n";
	    $full_writing_to_db = "0";
	    $event_generated = "0";
	    $full_reporting = "1";
	    include($config["base_path"] . "/plugins/manage/manage_do_check.php");
        if ($problem_detected == "1") {
          $global_statut="prob";
        } else {
          $global_statut="up";
	    }
	    $report .= "<br>Manage global status : ".$global_statut." (uptime ".$old_uptime." -> ".$new_uptime.")";
	    $report .= "<br><br>Cacti host id : ".$host["id"];
        db_execute("UPDATE manage_host SET statut = '".$global_statut."', `uptime` = '".$new_uptime."', `thresold` = '0' where id='" . $host["id"] . "'");
	    manage_logger("", $host["id"], "up", "Host is ", $host["hostname"] , $host["description"], "", "", "host", "", $old_statut, "1", $report);
      }
	
      if ( ($old_statut == "down")  && ($host_statut == 'down') && ($scenario == '') ) {
        $scenario = "5";
        print "\nScenario : ".$scenario."\n";
	  }
  
      if ( ($host_statut == 'up') && ($new_uptime < $old_uptime) && ($scenario == '') ) {
        $scenario = "6";
        print "\nScenario : ".$scenario."\n";
	    $full_writing_to_db = "0";
	    $event_generated = "0";
	    $full_reporting = "1";

	    include($config["base_path"] . "/plugins/manage/manage_do_check.php");

        if ($problem_detected == "1") {
          $global_statut="prob";
        } else {
          $global_statut="up";
	    }
	    $report .= "<br>Manage global status : ".$global_statut." (uptime ".$old_uptime." -> ".$new_uptime.")";
	    $report .= "<br><br>Cacti host id : ".$host["id"];
	    db_execute("UPDATE manage_host SET statut = '".$global_statut."', `uptime` = '".$new_uptime."' where id='" . $host["id"] . "'");
	    manage_logger("", $host["id"], "rebooted", "Host has ", $host["hostname"] , $host["description"], $old_uptime, $new_uptime, "host", "", $old_statut, "1", $report);
      } 

    } else {
      print "\nScenario : skipping\n";
    }
  
}  //end foreach host



$today = getdate();
$poller_date = $today['year']."-".$today['mon']."-".$today['mday'].", at ".$today['hours'].":".$today['minutes'].":".$today['seconds'];
print "\n".$poller_date;
db_execute("UPDATE settings SET value = '" . $poller_date . "' where name='manage_date'");

print "\nClosing poller number ".$nb_pol;
db_execute("UPDATE settings SET value = '1' where name='manage_poller_number_".$nb_pol."'");







function manage_exec_background($filename, $args = "") {
  global $config;
  exec($filename . " " . $args, $output);
  return $output[0];
}




function manage_logger($svc, $id, $val, $typ, $hn, $hd, $ou, $nu, $t, $o, $fm, $e, $rpt) {
//$svc
//$id
//$val
//$typ
//$hn
//$hd
//$ou old_uptime, used when host rebooted
//$nu new uptime, used when host rebooted
//$t
//$o
//$fm
//$e [0-1] will generate an event
//$rpt report message, used when host's statut changed

	$manage_events = db_fetch_cell("select value from settings where name='manage_events'");
	$manage_netsend_events = db_fetch_cell("select value from settings where name='manage_netsend_events'");
	$manage_netsend_method = db_fetch_cell("select value from settings where name='manage_netsend_method'");
	$netsend = db_fetch_cell("select value from settings where name='manage_send'");
	$perl = db_fetch_cell("select value from settings where name='manage_perl'");
	$manage_syslog_events = db_fetch_cell("select value from settings where name='manage_syslog'");
	$manage_snmp_events = db_fetch_cell("select value from settings where name='manage_snmp'");
	
	$today = getdate();
	$date = $today['year']."/".$today['mon']."/".$today['mday'].", ".$today['hours'].":".$today['minutes'].":".$today['seconds'];

	$global_alert_address = db_fetch_cell("select value from settings where name='manage_global_email'");
	$private_alert_address = db_fetch_cell("SELECT mail FROM `manage_host` WHERE id='" . $id . "'",'');
	$alert_address=manage_mail($private_alert_address, $global_alert_address);
	
	$tt=$svc;
	if ($t == "win_services") {
		$tt="9999";
	}

	if ($t == "win_process") {
		$tt="9998";
	}

    $whyReload = "";
	
	if ($e == "1") {

  	  if ( ($manage_events != '5') || ($manage_netsend_events != '5') || ($manage_syslog_events != '5') || ($manage_snmp_events != '5') ) {
		print " + start action (" . $alert_address . ")\n";
		$h='';

		if ($val == 'up') {
			if ( ($manage_events == '0') || ($manage_events == '3') || ($manage_events == '6') || ($manage_netsend_events == '3') || ($manage_netsend_events == '0') || ($manage_netsend_events == '6') || ($manage_syslog_events == '3') || ($manage_syslog_events == '0') || ($manage_syslog_events == '6') || ($manage_snmp_events == '3') || ($manage_snmp_events == '0') || ($manage_snmp_events == '6') ) {
//				print " + OK up\n";
				$h='UP';
			}
		}

		if ($val == 'down') {
			if ( ($manage_events == '1') || ($manage_events == '3') || ($manage_events == '4') || ($manage_events == '6') || ($manage_netsend_events == '1') || ($manage_netsend_events == '3') || ($manage_netsend_events == '4') || ($manage_netsend_events == '6') || ($manage_syslog_events == '1') || ($manage_syslog_events == '3') || ($manage_syslog_events == '4') || ($manage_syslog_events == '6') || ($manage_snmp_events == '1') || ($manage_snmp_events == '3') || ($manage_snmp_events == '4') || ($manage_snmp_events == '6') ) {
//				print " + OK down\n";
				$h='DOWN';
			}
		}

		if ($val == 'rebooted') {
			if ( ($manage_events == '2') || ($manage_events == '4') || ($manage_events == '6') || ($manage_netsend_events == '2') || ($manage_netsend_events == '4') || ($manage_netsend_events == '6') || ($manage_syslog_events == '2') || ($manage_syslog_events == '4') || ($manage_syslog_events == '6') || ($manage_snmp_events == '2') || ($manage_snmp_events == '4') || ($manage_snmp_events == '6') ) {
//				print " + OK reboot\n";
				$h='Reboot';
			}
		}

		if ($h != '') {
			if ($t == 'host') {
				$g='Host';
			}

			if ($t == 'port') {
				$g='Port ' . strtolower($svc);
			}

			if ($t == 'win_services') {
				$g='Windows Service "' . strtolower($svc). '"';
			}

			if ($t == 'win_process') {
				$g='Windows Process "' . strtolower($svc). '"';
			}

			$h2=$h;
         	$force_host_mib=db_fetch_cell("select value from settings where name='manage_uptime_method'");
			$host_mib=db_fetch_cell("select data from manage_uptime_method where id='".$id."'");
			if ( ( ($force_host_mib == "on") && ($host_mib == "0") ) || ($force_host_mib != "on") ) {
			  if ( ($ou > 4285440000) && ($ou < 4320000000) ) {
				$h='Maybe false alert - Reboot';
			  }			
			}
							  
			$msg=$g . ' is ' . $h2;
			if ($rpt != "") {
			  $msg .= '<br><br>'.$rpt;
			}

			if ($h2 == 'Reboot') {
				$days=floor($ou/8640000);
				$r=$ou-$days*8640000;
				$hours=floor( $r/360000 );
				$r=$ou-$days*8640000-$hours*360000;
				$minutes=floor($r/6000);

				$days2=floor($nu/8640000);
				$r2=$nu-$days2*8640000;
				$hours2=floor( $r2/360000 );
				$r2=$nu-$days2*8640000-$hours2*360000;
				$minutes2=floor($r2/6000);

				$msg = 'Old uptime was '.$days . 'd ' . $hours . 'h ' . $minutes .'m. New uptime is '.$days2 . 'd ' . $hours2 . 'h ' . $minutes2;
				if ($rpt != "") {
			      $msg .= '<br><br>'.$rpt;
			    }
		
		
    $gather_cisco_info=db_fetch_cell("select value from settings where name='manage_uptime_cisco'");
	$host_template=db_fetch_cell("select host_template_id from host where id='".$id."'");
    if ( ($gather_cisco_info=="on") && ($host_template == "5") ) {
	  $host_options = db_fetch_assoc("SELECT * FROM host where id = '".$id."' limit 1");
	  foreach ($host_option as $host) {
	    $whyReload = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.4.1.9.2.1.2.0", $host["snmp_version"], $host["snmp_username"], $host["snmp_password"], $host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"], $host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], $host["ping_timeout"], SNMP_WEBUI);
	  }
    }
	
				
			}

			if ( ($manage_events != '5') && ($manage_events != "is not set") && ($manage_events != "") ) {
			  $m = explode(",", $alert_address);
			  foreach ($m as $mm) {
				send_mail($mm, '', $h . ' event detected for host ' . $hd . ' (' . $hn . ')', $msg, '', '');
			  }
			}

			if ( ($manage_netsend_events != '5') && ($manage_netsend_events != "is not set") && ($manage_netsend_events != "") ) {
				$winpc = explode(";",$netsend);

				foreach ($winpc as $machine) {
					if ($manage_netsend_method == '0') {
						$command_string = "net send ";
					}else{
						$p = dirname(__FILE__);
						$command_string = $perl." ".$p."/netsend.pl ";
					}

					$command_string .= $machine.' "'.$h.' event detected for host '.$hd.' ('.$hn.'). '.$msg.'"';
					$extra_args = "";
					exec($command_string, $extra_args);
				}
			}
			
			if ( ($manage_syslog_events != '5') && ($manage_syslog_events != "is not set") && ($manage_syslog_events != "") ) {
			  define_syslog_variables();
			  $syslog_level = db_fetch_cell("select value from settings where name='manage_syslog_level'");
			  if (!isset($syslog_level)) {
				$syslog_level = LOG_WARNING;
			  } else if (isset($syslog_level) && ($syslog_level > 7 || $syslog_level < 0)) {
				$syslog_level = LOG_WARNING;
			  }
			  syslog($syslog_level, $h." event detected for host ".$hd." (".$hn."). ".$msg);
			}
			
			if ( ($manage_snmp_events != '5') && ($manage_snmp_events != "is not set") && ($manage_snmp_events != "") ) {

					$p = dirname(__FILE__);
					$command_string = $perl." ".$p."/manage_trap.pl ";

					$ip = db_fetch_cell("select value from settings where name='manage_snmp_ip'");
					if ( ($ip == "") || ($ip == "Is not set") ) {
					  $ip=$_SERVER["SERVER_ADDR"];
					}
					$com=db_fetch_cell("select value from settings where name='manage_snmp_community'");
					$ver=db_fetch_cell("select value from settings where name='manage_snmp_version'");
					$port=db_fetch_cell("select value from settings where name='manage_snmp_port'");
					$command_string .= $ip." ".$com." ".$ver." ".$port." ".$nu." \"".$msg."\" 1.3.6.1.4.1.56";
					$extra_args = "";
					$command_string = str_replace("<br><br>", ". ", $command_string);
					$command_string = str_replace("->", "-", $command_string);

					exec($command_string, $extra_args);

			}
			
		} else {
			print " + No action for this event.\n";
		}
	  }
	
	}

	//$s="INSERT INTO manage_alerts ( idh , datetime , ids , message, ida, oid, note ) VALUES ('" . $id . "', '" . $date . "', '" . $tt . "', '" . $val . "', '', '".$o."', '".$whyReload."')";
	//db_execute($s);
	
}

		
?>

