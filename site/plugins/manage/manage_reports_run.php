<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

include(dirname(__FILE__) . "/../../include/global.php");

$db = mysql_connect("$database_hostname", "$database_username", "$database_password");
mysql_select_db("$database_default",$db);


$polling_interval = db_fetch_cell("select value from settings where name='poller_interval'");

$reports_daily = array();

$p = dirname(__FILE__) . "/reports/daily";
$dir=opendir($p);
while($file=readdir($dir)) {
  if ($file!="." and $file!="..") {
	if (is_file($p."/".$file)) {
	  $reports_daily[$file]=$file;
	}
  }
}
closedir($dir);

$today = getdate();

$now_day=$today["mday"];
if ($now_day < 10) {
  $now_day="0".$now_day;
}

$now_month=$today["mon"];
if ($now_month < 10) {
  $now_month="0".$now_month;
}

$now_year=$today["year"];

$now_time_ref=$today["hours"]*60+$today["minutes"];

$p = dirname(__FILE__) . "/reports/daily";
foreach ($reports_daily as $report) {
//print "- daily : ".$report."\n";
  $filename = $p.'/'.$report;
  if (is_readable($filename)) {
//print $filename;
	$lines = file ($filename);
	$i=0;
    foreach ($lines as $line) {
      $data[$i]=str_replace("\n", "", $line);
	  $i++;
	}
//print_r($data);
  }
  $days=explode(",", $data[0]);
  foreach ($days as $day) {
    if ($day == $today["wday"]) {

      $h=explode(":", $data[1]);
	  $report_time_ref=$h[0]*60+$h[1];
//print $now_time_ref." ".$report_time_ref." ".($now_time_ref-$report_time_ref)." ".($polling_interval/60)."\n";

      if ( ($now_time_ref > $report_time_ref) && ( ($now_time_ref-$report_time_ref) < ($polling_interval/60) ) ) {

        $timestamp=strtotime("-1 day");
        $start_d=date('d', $timestamp);
        $start_m=date('m', $timestamp);
        $start_y=date('Y', $timestamp);

        if (strlen($h[1]) == "3") {
		  $h[1]=substr($h[1], 0, 2);
		}

		$rpt="<b><u>Daily Report from ".$start_y."/".$start_m."/".$start_d." ".$h[0].":".$h[1]." to ".$now_year."/".$now_month."/".$now_day." ".$h[0].":".$h[1]."</b></u><br><br>";

        $total_down_events=0;
        $total_up_events=0;
        $total_events=0;

		$total_hours=array("00" => 0, "01" => 0, "02" => 0, "03" => 0, "04" => 0, "05" => 0, "06" => 0, "07" => 0, "08" => 0, "09" => 0);
		for ($j=0;$j<24;$j++) {
		  $total_hours[$j]=0;
		}
		  
		$rpt2="";
		$rpt3="";
		$rpt_tmp="<br><b>Events by host :</b><br>";
		
		$filter="";
		if ($data[3] != "0") {
		  $filter=" where id in (".$data[3].")";
		}
		
		$tmp=db_fetch_assoc("select id, hostname, description from host".$filter);
        foreach ($tmp as $id) {
		  $rpt2 = "<b>".$id["hostname"]."</b> (".$id["description"].") : ";

          $good_alerts=array();
          $down_events=0;
          $up_events=0;
          $events=0;
          $i=0;

		  $sql="SELECT * FROM `manage_alerts` where idh='".$id["id"]."' and datetime like '%".$now_year."-".$now_month."-".$now_day."%' order by datetime";
          $alerts=db_fetch_assoc($sql);
          foreach ($alerts as $alert) {
//print $alert["datetime"]." OK\n";
			$good_alerts[$i]=$alert;
			if ($alert["message"] == "down") {
			  $down_events++;
			}
			if ($alert["message"] == "up") {
			  $up_events++;
			}
			$events++;
			$i++;
			$tmp=substr($alert["datetime"], 11, 2);
			$total_hours[$tmp]++;
		  }
		  
		  $sql="SELECT * FROM `manage_alerts` where idh='".$id["id"]."' and datetime like '%".$start_y."-".$start_m."-".$start_d."%' order by datetime";
          $alerts=db_fetch_assoc($sql);
          foreach ($alerts as $alert) {
//print $alert["datetime"];
			$alert_h=substr($alert["datetime"], 11,2);
			$alert_m=substr($alert["datetime"], 14,2);
			
			if ( ($alert_h*60+$alert_m)>$report_time_ref ) {
//print " OK\n";
			  $good_alerts[$i]=$alert;
			  if ($alert["message"] == "down") {
			    $down_events++;
			  }
			  if ($alert["message"] == "up") {
			    $up_events++;
			  }
			  $events++;
			  $i++;
			  $tmp=substr($alert["datetime"], 11, 2);
			  $total_hours[$tmp]++;
			} else {
//			  print "\n";
			}
		  }
		  
		  $rpt3=$events." events (DOWN events : ".$down_events.", UP events : ".$up_events.")<br>";
		  
          $total_down_events=$total_down_events+$down_events;
          $total_up_events=$total_up_events+$up_events;
          $total_events=$total_events+$events;
		  
		  if ($events != "0") {
		    $rpt_tmp=$rpt_tmp.$rpt2.$rpt3;
		  }
		  
//print_r($hours);
		  
		}  //end of select host
//print_r($total_hours);
		$rpt=$rpt."<b>Total events :</b> ".$total_events." (DOWN events : ".$total_down_events.", UP events : ".$total_up_events.")<br><br>";
		
		$rpt=$rpt."<b>Events by hour :</b><br>";
		$rpt=$rpt."<table border='1'>";
		$rpt=$rpt."<tr><td><b>Hours</b></td><td><b>Total</b></td></tr>";
		if ($total_hours["00"] != 0) {
		  $rpt=$rpt."<tr><td><center>00</td><td><center>".$total_hours["00"]."</td>";
		}
		if ($total_hours["01"] != 0) {
		  $rpt=$rpt."<tr><td><center>01</td><td><center>".$total_hours["01"]."</td>";
		}
		if ($total_hours["02"] != 0) {
		  $rpt=$rpt."<tr><td><center>02</td><td><center>".$total_hours["02"]."</td>";
		}
		if ($total_hours["03"] != 0) {
		  $rpt=$rpt."<tr><td><center>03</td><td><center>".$total_hours["03"]."</td>";
		}
		if ($total_hours["04"] != 0) {
		  $rpt=$rpt."<tr><td><center>04</td><td><center>".$total_hours["04"]."</td>";
		}
		if ($total_hours["05"] != 0) {
		  $rpt=$rpt."<tr><td><center>05</td><td><center>".$total_hours["05"]."</td>";
		}
		if ($total_hours["06"] != 0) {
		  $rpt=$rpt."<tr><td><center>06</td><td><center>".$total_hours["06"]."</td>";
		}
		if ($total_hours["07"] != 0) {
		  $rpt=$rpt."<tr><td><center>07</td><td><center>".$total_hours["07"]."</td>";
		}
		if ($total_hours["08"] != 0) {
		  $rpt=$rpt."<tr><td><center>08</td><td><center>".$total_hours["08"]."</td>";
		}
		if ($total_hours["09"] != 0) {
		  $rpt=$rpt."<tr><td><center>09</td><td><center>".$total_hours["09"]."</td>";
		}
		if ($total_hours["10"] != 0) {
		  $rpt=$rpt."<tr><td><center>10</td><td><center>".$total_hours["10"]."</td>";
		}
		if ($total_hours["11"] != 0) {
		  $rpt=$rpt."<tr><td><center>11</td><td><center>".$total_hours["11"]."</td>";
		}
		if ($total_hours["12"] != 0) {
		  $rpt=$rpt."<tr><td><center>12</td><td><center>".$total_hours["12"]."</td>";
		}
		if ($total_hours["13"] != 0) {
		  $rpt=$rpt."<tr><td><center>13</td><td><center>".$total_hours["13"]."</td>";
		}
		if ($total_hours["14"] != 0) {
		  $rpt=$rpt."<tr><td><center>14</td><td><center>".$total_hours["14"]."</td>";
		}
		if ($total_hours["15"] != 0) {
		  $rpt=$rpt."<tr><td><center>15</td><td><center>".$total_hours["15"]."</td>";
		}
		if ($total_hours["16"] != 0) {
		  $rpt=$rpt."<tr><td><center>16</td><td><center>".$total_hours["16"]."</td>";
		}
		if ($total_hours["17"] != 0) {
		  $rpt=$rpt."<tr><td><center>17</td><td><center>".$total_hours["17"]."</td>";
		}
		if ($total_hours["18"] != 0) {
		  $rpt=$rpt."<tr><td><center>18</td><td><center>".$total_hours["18"]."</td>";
		}
		if ($total_hours["19"] != 0) {
		  $rpt=$rpt."<tr><td><center>19</td><td><center>".$total_hours["19"]."</td>";
		}
		if ($total_hours["20"] != 0) {
		  $rpt=$rpt."<tr><td><center>20</td><td><center>".$total_hours["20"]."</td>";
		}
		if ($total_hours["21"] != 0) {
		  $rpt=$rpt."<tr><td><center>21</td><td><center>".$total_hours["21"]."</td>";
		}
		if ($total_hours["22"] != 0) {
		  $rpt=$rpt."<tr><td><center>22</td><td><center>".$total_hours["22"]."</td>";
		}
		if ($total_hours["23"] != 0) {
		  $rpt=$rpt."<tr><td><center>23</td><td><center>".$total_hours["23"]."</td>";
		}
		$rpt=$rpt."</tr></table>";
		
		$rpt=$rpt.$rpt_tmp;
	    print "Sending mail...";
		
		$real_mails="";
		$mails=explode(",", $data[2]);
        foreach ($mails as $mail) {
		  $pos = strpos($mail, "@");
		  if ($pos === false) {
            $user_mail=db_fetch_cell("select data from plugin_thold_contacts where user_id='".$mail."'");
			$real_mails=$real_mails.",".$user_mail;
		  } else {
		    $real_mails=$real_mails.",".$mail;
		  }
		}
//print $real_mails;
		send_mail($real_mails, '', 'Manage Daily Report (Cacti) - '.$start_d.'/'.$start_m.' '.$h[0].':'.$h[1], $rpt, 'xxx', '');
	  }
	}
  }
}


?>
