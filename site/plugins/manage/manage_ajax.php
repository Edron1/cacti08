<?php

chdir('../../');

include_once("./include/auth.php");

include_once("./include/global.php");

$p = dirname(__FILE__);
include_once($p . '/manage_lib.php');
$permit=manage_accounts_permit("tab");
if ($permit == 0) {
  print "Unauthorized access.";
  exit;
}

global $colors, $device_actions;

$user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);

//select view
$view = $_REQUEST["simple"];

//Tree view pre-calculation
if ( ($view == 4) || ($_REQUEST["forceids"] == "1") ) {
  include("./lib/html_tree.php");
//  include("./include/config_arrays.php");
  include_once("./lib/tree.php");
  $v=array("");
  $plus=array("");

  manage_tree_c($v,$plus);

  $np=array("");
  foreach (array_keys($v) as $rr) {
    $v[$rr]=manage_replace($rr, $v);
    $x = explode(";", $v[$rr]);
    $val=$x[0];
    foreach ($x as $xp) {
      $y = explode(";", $val);
      $fy=0;
      foreach ($y as $yp) {
        if ($xp == $yp) {
          $fy=1;
        }
      }
      if ($fy == 0) {
	    $val.=";".$xp;
	  }
    }
    $v[$rr]=$val;
    $x = explode(";", $v[$rr]);
	$n=0;
    foreach ($x as $xp) {
	  if ( ($xp != "-1") and ($xp != "") ) {
	    $n++;
      }	  
    }
	$np[$rr]=$n;	
  }
}

//is sound enabled ?
$playsound = 0;
if (isset($_REQUEST["manage_sound_enable"])) {
  if ($_REQUEST["manage_sound_enable"] == "on") {
    $playsound = 1;
  }
}

//select theme
$theme = db_fetch_cell("SELECT value FROM settings where name='manage_theme_".$user_id."'");
if ( ($theme == "") || ($theme == "999") ) {
  $theme = "default";
}
$theme_line=array();
$filename = $config["base_path"].'/plugins/manage/images/themes/'.$theme.'/theme.inc';
if (is_readable($filename)) {
  $i=0;
  $lines = file ($filename);
  foreach ($lines as $line) {
	$tmp = explode("#",$line);
	$theme_line[$i]=trim($tmp[1]);
	$i++;
  }
}

//warning message
$warning_message="";
$tmp2 = db_fetch_cell("select value from settings where name='manage_disabled_text'");
$tmp = db_fetch_cell("select value from settings where name='manage_enable'");
if ( ($tmp != "on") && ($tmp2 != "") && ($tmp2 != "Is not set") ) {
  $warning_message="<br><font color='#".$theme_line[4]."' size='4'><center><b>".$tmp2."</b><br><br>";
}

//are we going to check tresholds ?
$blink = db_fetch_cell("select value from settings where name='manage_thold_".$user_id."'");

//select external link method and timeout for Overlib
$manage_link_method = db_fetch_cell("select value from settings where name='manage_link_method_".$user_id."'");
$tmp=db_fetch_cell("select value from settings where name='manage_connect_timeout_".$user_id."'");
$manage_connect_timeout = $tmp*1000;
if ( ($manage_link_method == "") || ($manage_link_method == 999) ) {
  $manage_link_method=9;
}
if ( ($manage_connect_timeout == "") || ($manage_connect_timeout == "Is not set") || ($manage_connect_timeout == "0") ) {
  $manage_connect_timeout=2;
}

//is auto-expand enable ?
$expand_level=db_fetch_cell("select value from settings where name='manage_expand_".$user_id."'");
$expand_style_yes="visibility: visible; display: inline;";
$expand_style_no="visibility: hidden; display: none;";
$expand_style=$expand_style_yes;
if ($expand_level == 0) {
  $expand_style=$expand_style_no;
}

//select order
$order="";
$tmp=0;

if ($view != 4) {

//select only hosts we can view
  $default_host_perm=db_fetch_cell("select policy_hosts from user_auth where id=" .$_SESSION["sess_user_id"]);
  $host_perm="";
  if ($default_host_perm == "1") {
	$host_perm="not ";
  }
  $sql_perm=" and host.id ". $host_perm ."in (select item_id from user_auth_perms where user_auth_perms.user_id='".$user_id."') ";

  $tmp1=db_fetch_cell("select value from settings where name='manage_order1_".$user_id."'");
  $tmp2=db_fetch_cell("select value from settings where name='manage_order2_".$user_id."'");
  $tmp3=db_fetch_cell("select value from settings where name='manage_order3_".$user_id."'");
  $tmp4=db_fetch_cell("select value from settings where name='manage_order4_".$user_id."'");
  $tmp5=db_fetch_cell("select value from settings where name='manage_order5_".$user_id."'");
  
//  if ( ($_REQUEST["forceids"] == "0") || ($view == 3) ) {

    if ( ($tmp1 != 0) && ($tmp1 != 999) && ($tmp1 != "") ) {
	  $order .= manage_order($tmp1, $tmp);
	  $tmp=1;
    }
    if ( ($tmp2 != 0) && ($tmp2 != 999) && ($tmp2 != "") ) {
	  $order .= manage_order($tmp2, $tmp);
	  $tmp=1;
    }
    if ( ($tmp3 != 0) && ($tmp3 != 999) && ($tmp3 != "") ) {
	  $order .= manage_order($tmp3, $tmp);
	  $tmp=1;
    }
    if ( ($tmp4 != 0) && ($tmp4 != 999) && ($tmp4 != "") ) {
	  $order.= manage_order($tmp4, $tmp);
	  $tmp=1;
    }
    if ( ($tmp5 != 0) && ($tmp5 != 999) && ($tmp5 != "") ) {
	  $order .= manage_order($tmp5, $tmp);
	  $tmp=1;
    }
    if (isset($_REQUEST["order"])) {
	  $order = " order by ".$_REQUEST["order"]." ".$_REQUEST["asc_desc"]." ";
    }

    $sql = "SELECT /* ".$_SESSION["sess_user_id"]."|".$_SERVER['REMOTE_ADDR']." */ host.id, host.hostname, host.description, manage_host.statut, manage_host.uptime, manage_host.group, manage_host.type, manage_groups.site_id, host.notes FROM host, manage_host, manage_groups, manage_sites where (host.manage = 'on') and (host.disabled <> 'on') and (host.id = manage_host.id) and (`group` = manage_groups.id) and (manage_groups.site_id = manage_sites.id) ".$sql_perm.$order;
/*
  } else {

    if ( ( ($tmp1 != 0) && ($tmp1 != 999) && ($tmp1 != "") ) && ($tmp1 != 1) && ($tmp1 != 2) ) {
	  $order .= manage_order($tmp1, $tmp);
	  $tmp=1;
    }
    if ( ( ($tmp2 != 0) && ($tmp2 != 999) && ($tmp2 != "") ) && ($tmp1 != 1) && ($tmp1 != 2) ) {
	  $order .= manage_order($tmp2, $tmp);
	  $tmp=1;
    }
    if ( ( ($tmp3 != 0) && ($tmp3 != 999) && ($tmp3 != "") ) && ($tmp1 != 1) && ($tmp1 != 2) ) {
	  $order .= manage_order($tmp3, $tmp);
	  $tmp=1;
    }
    if ( ( ($tmp4 != 0) && ($tmp4 != 999) && ($tmp4 != "") ) && ($tmp1 != 1) && ($tmp1 != 2) ) {
	  $order.= manage_order($tmp4, $tmp);
	  $tmp=1;
    }
    if ( ( ($tmp5 != 0) && ($tmp5 != 999) && ($tmp5 != "") ) && ($tmp1 != 1) && ($tmp1 != 2) ) {
	  $order .= manage_order($tmp5, $tmp);
	  $tmp=1;
    }
    if (isset($_REQUEST["order"])) {
	  $order = " order by ".$_REQUEST["order"]." ".$_REQUEST["asc_desc"]." ";
    }

    $sql = "SELECT host.id, host.hostname, host.description, manage_host.statut, manage_host.uptime, manage_host.type, host.notes  FROM host, manage_host where (host.manage = 'on') and (host.disabled <> 'on') and (host.id = manage_host.id) ".$sql_perm.$order;
  
  }
  */
  
  $result = mysql_query($sql);
}

//start table
print "<table width='98%' align=center cellspacing='0' background='images/themes/".$theme."/background.png'><tr><td>";
print "<table width='100%' align=center cellspacing='0'><tr>";

//calculate separator
if (isset($_REQUEST["simple"])) {
  switch ($_REQUEST["simple"]) {
    case '0':
	  $ligne = db_fetch_cell("select value from settings where name='manage_full_separator_".$user_id."'");
	  break;
    case '1':
	  $ligne = db_fetch_cell("select value from settings where name='manage_simple_separator_".$user_id."'");
	  break;
    case '2':
	  $ligne = db_fetch_cell("select value from settings where name='manage_list_separator_".$user_id."'");
	  break;
    case '3':
	  $ligne = db_fetch_cell("select value from settings where name='manage_site_separator_".$user_id."'");
	  break;
    case '4':
	  $ligne = db_fetch_cell("select value from settings where name='manage_tree_separator_".$user_id."'");
	  break;
  }
}

//initialization
$error_detected = 0;
$current_line=0;
$nberr=0;
$total_devices=0;

$today = getdate();
$refresh_time=$today['hours'].":".$today['minutes'].":".$today['seconds'];

if ( ($view == 0) || ($view == 1) || ($view == 2) ) {

//start read config files for ports, services and process
  if ( ($view == 0) || ($view == 2) ) {
    $port_number=array();
    $port_name=array();
    $filename = $config["base_path"].'/scripts/ports.inc';
    if (is_readable($filename)) {
	  $i=0;
  	  $lines = file ($filename);
	  foreach ($lines as $line) {
        $val = explode("#",$line);
	    $port_number[$i]=trim($val[0]);
	    $port_name[$i]=trim($val[1]);
	    $i++;
	  }
    }

    $long_name=array();
    $short_name=array();
    $filename = $config["base_path"].'/plugins/manage/include/win_services.inc';
    if (is_readable($filename)) {
	  $i=0;
	  $lines = file ($filename);
	  foreach ($lines as $line) {
	    $val = explode("#",$line);
	    $long_name[$i]=trim($val[0]);
	    $short_name[$i]=trim($val[1]);
	    $i++;
	  }
    }

    $process_long_name=array();
    $process_short_name=array();
    $filename = $config["base_path"].'/plugins/manage/include/manage_process.inc';
    if (is_readable($filename)) {
	  $i=0;
	  $lines = file ($filename);
	  foreach ($lines as $line) {
	    $val = explode("#",$line);
	    $process_long_name[$i]=trim($val[0]);
	    $process_short_name[$i]=trim($val[1]);
	    $i++;
	  }
    }
  }
//end read config files

//start calcul for List view
  if ($view == 2) {
    $sql5 = "SELECT distinct(manage_tcp.services) FROM `manage_tcp`, `manage_host`, host where host.id = manage_host.id AND host.disabled <> 'on' AND manage_tcp.id=manage_host.id AND host.manage='on' ".$sql_perm;

    $tmp="";
    if (isset($_REQUEST["group"])) {
	  if ($_REQUEST["group"] != '0') {
	    $tmp .= " and `group`='".$_REQUEST["group"]."'";
	  }
	}

      if (isset($_REQUEST["site"])) {
	    if ($_REQUEST["site"] != '0') {
	      $tmp .= " and `group` in (select id from manage_groups where site_id = '".$_REQUEST["site"]."')";
	    }
      }

      if (isset($_REQUEST["forceids"])) {
	    if ($_REQUEST["forceids"] == "1") {
		  $tmp=str_replace(";", ",", $v[$_REQUEST["group"]]);
	      $tmp = " and manage_host.id in (".$tmp.")";
		}
	  }
	
	$sql5=$sql5.$tmp;
	
    if (isset($_REQUEST["err"])) {
	  if ($_REQUEST["err"] == '1') {
	    $sql5 .= " and ( (manage_host.statut ='prob') || (manage_host.statut ='down') || (manage_host.statut ='nopoll') )";
	  }
    }
	  
    $sql5 .= " order by manage_tcp.services;";

    $result5 = mysql_query($sql5);
    $nbs =0;
    $A=array("");
    $A2=array();

    while ($row5 = mysql_fetch_array($result5, MYSQL_ASSOC)) {
	  $A[$nbs]=$row5["services"];
	  $nbs ++;
    }

    $y=0;

    for ($counter=0;$counter<$nbs;$counter++) {
	  $yy=0;
	  $t=$A[$counter];

	  for ($re=0;$re<count($port_name);$re++) {
	    if ($A[$counter] == $port_number[$re]) {
		  $t=$port_name[$re];
		  $yy=1;
	    }
	  }

	  if ( isset($A2[$t]) ) {
	    if (( $A2[$t] == $A[$counter]) || ($yy == 1) ) {
		  $A2[$t] .= "<br>".$A[$counter];
	    } else {
		  $t=$A[$counter].(string)$y;
		  $A2[$t] = $A[$counter];
		  $y++;
	    }
	  } else {
	    $A2[$t] = $A[$counter];
	  }
    }

    $E=array_keys($A2);

    $sql5 = "SELECT distinct(manage_services.name) FROM `manage_services`, `manage_host`, `host` where manage_host.id = host.id AND host.disabled <> 'on' AND manage_services.id=manage_host.id ".$sql_perm;

    $tmp="";
    if (isset($_REQUEST["group"])) {
	  if ($_REQUEST["group"] <> '0') {
	    $tmp .= " and `group`='".$_REQUEST["group"]."'";
	  }
    }

    if (isset($_REQUEST["site"])) {
	  if ($_REQUEST["site"] != '0') {
	    $tmp .= " and `group` in (select id from manage_groups where site_id = '".$_REQUEST["site"]."')";
	  }
    }

    if (isset($_REQUEST["forceids"])) {
	  if ($_REQUEST["forceids"] == "1") {
	    $tmp=str_replace(";", ",", $v[$_REQUEST["group"]]);
	    $tmp = " and manage_host.id in (".$tmp.")";
	  }
	}
	  
	$sql5=$sql5.$tmp;
	
    if (isset($_REQUEST["err"])) {
	  if ($_REQUEST["err"] == '1') {
	    $sql5 .= " and ( (manage_host.statut ='prob') || (manage_host.statut ='down') || (manage_host.statut ='nopoll') )";
	  }
    }

    $sql5 .= " order by manage_services.name;";

    $result5 = mysql_query($sql5);

    $nbs2 =0;
    $C=array("");
    $C2=array();

    while ($row5 = mysql_fetch_array($result5, MYSQL_ASSOC)) {
	  $C[$nbs2]=strtolower($row5["name"]);
	  $nbs2 ++;
    }

    $y=0;
    for ($counter=0;$counter<$nbs2;$counter++) {
	  $yy=0;
	  $t=substr($C[$counter], 0, 6);

	  for ($re=0;$re<count($long_name);$re++) {
	    if (strtolower($C[$counter]) == strtolower($long_name[$re])) {
		  $t=$short_name[$re];
		  $yy=1;
	    }
	  }

	  if ( isset($C2[$t]) ) {
	    if (( $C2[$t] == $C[$counter]) || ($yy == 1) ) {
		  $C2[$t] .= "<br>".$C[$counter];
	    } else {
		  $t=substr($C[$counter], 0, 5).(string)$y;
		  $C2[$t] = $C[$counter];
		  $y++;
	    }
	  } else {
	    $C2[$t] = $C[$counter];
	  }
    }

    $D=array_keys($C2);

    $sql5 = "SELECT distinct(manage_process.name) FROM `manage_process`, `manage_host`, `host` where manage_host.id = host.id AND host.disabled <> 'on' AND manage_process.id=manage_host.id ".$sql_perm;

    $tmp="";
    if (isset($_REQUEST["group"])) {
	  if ($_REQUEST["group"] <> '0') {
	    $tmp .= " and `group`='".$_REQUEST["group"]."'";
	  }
    }

    if (isset($_REQUEST["site"])) {
	  if ($_REQUEST["site"] <> '0') {
	    $tmp .= " and `group` in (select id from manage_groups where site_id = '".$_REQUEST["site"]."')";
	  }
    }

    if (isset($_REQUEST["forceids"])) {
	  if ($_REQUEST["forceids"] == "1") {
	    $tmp=str_replace(";", ",", $v[$_REQUEST["group"]]);
	    $tmp = " and manage_host.id in (".$tmp.")";
	  }
	}
	
	$sql5=$sql5.$tmp;
	
    if (isset($_REQUEST["err"])) {
	  if ($_REQUEST["err"] == '1') {
	    $sql5 .= " and ( (manage_host.statut ='prob') || (manage_host.statut ='down') || (manage_host.statut ='nopoll') )";
	  }
    }

    $sql5 .= " order by manage_process.name;";

    $result5 = mysql_query($sql5);

    $nbs3 =0;
    $F=array("");
    $F2=array();

    while ($row5 = mysql_fetch_array($result5, MYSQL_ASSOC)) {
	  $F[$nbs3]=strtolower($row5["name"]);
	  $nbs3 ++;
    }

    $y=0;
    for ($counter=0;$counter<$nbs3;$counter++) {
	  $yy=0;
	  $t=substr($F[$counter], 0, 6);

	  for ($re=0;$re<count($process_long_name);$re++) {
	    if (strtolower($F[$counter]) == strtolower($process_long_name[$re])) {
		  $t=$process_short_name[$re];
		  $yy=1;
	    }
	  }

	  if (isset($F2[$t])) {
	    if (( $F2[$t] == $F[$counter]) || ($yy == 1) ) {
		  $F2[$t] .= "<br>".$F[$counter];
	    } else {
	 	  $t=substr($F[$counter], 0, 5).(string)$y;
		  $F2[$t] = $F[$counter];
		  $y++;
	    }
	  } else {
	    $F2[$t] = $F[$counter];
	  }
    }

    $G=array_keys($F2);
  }
//end calcul for List view

//start graphing
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$sql2 = "SELECT `group`, uptime, type, statut, mail FROM manage_host where id = '" . $row['id'] . "'";
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);

//start test if we are going to display this host
	$do="0";

	if ($_REQUEST["forceids"] == "0") {
	  if ($_REQUEST["site"] == "0") {
	    if ($_REQUEST["group"] == "0") {
		  $do="1";
	    } else {
		  if ($row2["group"] == $_REQUEST["group"]) {
		    $do="1";
		  }
	    }
	  } else {
	    if ($_REQUEST["group"] == "0") {
		  $wx = db_fetch_cell("SELECT site_id FROM manage_groups where id='".$row2["group"]."'");
		  $wx2 = db_fetch_cell("SELECT id FROM manage_sites where id='".$wx."'");
		  if ($_REQUEST["site"] == $wx2) {
		    $do="1";
		  }
	    } else {
		  if ($row2["group"] == $_REQUEST["group"]) {
		    $do="1";
		  }
	    }
	  }
    } else {

	  $sids = explode(";", $v[$_REQUEST["group"]]);
      $t=0;
      foreach ($sids as $pid) {
        $t++;
      }

      for ($counter=0;$counter<$t;$counter++) {
        if ($sids[$counter] == $row['id']) {
		  $do="1";
		}
	  }

	}

	if (isset($_REQUEST["err"])) {
	  if ($_REQUEST["err"] == "1") {
		if ($row2["statut"] == "up") {
		  $do="0";
		}
	  }
	}
//end test if we are going to display this host

//display host
	if ($do == "1") {
	  $total_devices++;

//test if we draw colum for list view, use overlib
	  if ($view == 2) {
		if ( ($current_line == $ligne) || ($current_line == 0)) {
		  print '</tr><tr bgcolor="#'.$theme_line[2].'">';
		  $asc_desc="asc";

		  if (isset($_REQUEST["asc_desc"])) {
			$asc_desc = $_REQUEST["asc_desc"];
		  }

		  $tmp=0;
		  print "<td width=80><center><b><a id='vvv' href='#' onclick='parent.srt_up_";

		  if (isset($_REQUEST["order"])) {
			if ($_REQUEST["order"] == "uptime") {
			  $tmp=1;

			  if ($asc_desc == "asc") {
				$asc_desc="desc";
			  } else {
				$asc_desc="asc";
			  }
			} else {
			  $asc_desc="asc";
			}
		  }

		  print $asc_desc."()'><FONT COLOR='#".$theme_line[3]."'>Uptime";

		  if ($tmp == 1) {
			print "**";
		  }
		  print "</a></td>";

		  $asc_desc="asc";

		  if (isset($_REQUEST["asc_desc"])) {
			$asc_desc = $_REQUEST["asc_desc"];
		  }

		  $tmp=0;
		  print "<td width=25><center><b><a id='yyy' href='#' onclick='parent.srt_stt_";

		  if (isset($_REQUEST["order"])) {
			if ($_REQUEST["order"] == "statut") {
			  $tmp=1;

			  if ($asc_desc == "asc") {
				$asc_desc="desc";
			  } else {
				$asc_desc="asc";
			  }
			} else {
			  $asc_desc="asc";
			}
		  }

		  print $asc_desc."()'><FONT COLOR='#".$theme_line[3]."'>_";
		  $asc_desc="asc";

		  if (isset($_REQUEST["asc_desc"])) {
			$asc_desc = $_REQUEST["asc_desc"];
		  }

		  if ($tmp == 1) {
			print "**";
		  }

		  print "</a></td>";
		  $tmp=0;
		  print "<td><center><b><FONT COLOR='#".$theme_line[3]."'>Hosts (<a id='www' href='#' onclick='parent.srt_hst_";
		  if (isset($_REQUEST["order"])) {
			if ($_REQUEST["order"] == "hostname") {
			  $tmp=1;

			  if ($asc_desc == "asc") {
				$asc_desc="desc";
			  } else {
				$asc_desc="asc";
			  }
			} else {
			  $asc_desc="asc";
			}
		  }

		  print $asc_desc."()'><FONT COLOR='#".$theme_line[3]."'>Hostname";

		  if ($tmp == 1) {
			print "**";
		  }

		  print "</a> (<a id='zzz' href='#' onclick='parent.srt_descr_";
		  $asc_desc="asc";

		  if (isset($_REQUEST["asc_desc"])) {
			$asc_desc = $_REQUEST["asc_desc"];
		  }

		  $tmp=0;

		  if (isset($_REQUEST["order"])) {
			if ($_REQUEST["order"] == "description") {
			  $tmp=1;

			  if ($asc_desc == "asc") {
				$asc_desc="desc";
			  } else {
				$asc_desc="asc";
			  }
			} else {
			  $asc_desc="asc";
			}
		  }

		  print $asc_desc."()'><FONT COLOR='#".$theme_line[3]."'>Description";

          if ($tmp == 1) {
		    print "**";
		  }
		  print "</a>))</td>";

	      for ($counter=0;$counter<count($E);$counter++) {
            print "<td width='30'><center><b><FONT COLOR='#".$theme_line[3]."'>";
		    $msg="Port ".$A2[$E[$counter]];
		    $vs = explode("<br>",$A2[$E[$counter]]);
		    if (count($vs) > 1) {
		      $msg="";
			  for ($yo=0;$yo<count($vs);$yo++) {
		        $msg .= "Port ".$vs[$yo]."<br>";
			  }
		    }
		    print '<dt onMouseOver="return overlib(\'<font color='.$theme_line[4].'>'.$msg.'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();">';
		    print $E[$counter];
            print "</dt></td>";
          }
          for ($counter=0;$counter<count($D);$counter++) {
            print "<td width='30'><center><b><FONT COLOR='#".$theme_line[3]."'>";
		    print '<dt onMouseOver="return overlib(\'<font color='.$theme_line[4].'>'.$C2[$D[$counter]].'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();">';
		    print $D[$counter];
            print "</dt></td>";
          }

          for ($counter=0;$counter<count($G);$counter++) {
            print "<td width='30'><center><b><FONT COLOR='#".$theme_line[3]."'>";
		    print '<dt onMouseOver="return overlib(\'<font color='.$theme_line[4].'>'.$F2[$G[$counter]].'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();">';
		    print $G[$counter];
            print "</dt></td>";
          }

		  print "<tr></tr>";
		  $current_line=0;
	    }
      }    //end draw colum

      if ( ($view == 0) || ($view == 1) ) {
//start a new line
        if ($current_line == $ligne) {
          print "</tr><tr></tr><tr></tr><tr></tr><tr>";
	      $current_line=0;
        }
        print "<td valign='top'><center>";
        print "<table border='1'><tr><td>";
	    print "<table background='images/themes/".$row2['type'];

        if ($view == 1) {
          print "' width='60'>";
        } else {
          print "' width='130'>";
        }
        print "<tr>";
        print "<td valign='top'>";
      }


//calculate uptime
      if ( ($view == 0) || ($view == 2) ){
		$days = intval($row2["uptime"] / (60*60*24*100));
		$remainder = $row2["uptime"] % (60*60*24*100);
		$hours = intval($remainder / (60*60*100));
		$remainder = $remainder % (60*60*100);
		$minutes = intval($remainder / (60*100));
      }

//alternate color lines
	  $clr=$theme_line[6];
      if (($current_line % 2) == 1) {
	    $clr=$theme_line[7];
	  }

//display uptime for list view
      if ($view == 2) {
	    print "<td width=80 bgcolor='".$clr."'><center><font color=".$theme_line[4].">";
	    print "<b>" . $days . "</b>d<b>" . $hours . "</b>h<b>" . $minutes . "</b>m</b></td>";
        print "<td width=25 bgcolor='".$clr."'><center>";
      }

//display statut image, use overlib
	  if ( ($view == 0) || ($view == 1) || ($view == 2) ) {

        $global_alert_address = db_fetch_cell("select value from settings where name='manage_global_email'");
	    $private_alert_address = $row2["mail"];
		
        $alert_address=manage_mail($private_alert_address, $global_alert_address);
	
        manage_overlib_rightclick($row['id'], $row["description"], $row2["statut"], $alert_address, $row['notes'], $theme);
	  }

	  
//display overlib graphs for list view
      if ($view == 2) {
//	    print "</td>";
        print "</td><td bgcolor='".$clr."'>";
        manage_overlib_graph($row['id'], $blink, $theme_line[5], $row['description'], $row['hostname']);
      }

      if ( ($view == 0) || ($view == 1) || ($view == 2) ) {
        print "</td>";
      }

//calculate errors
      $hosterr=0;
      if ($row2['statut'] == 'down') {
        $nberr++;
	    $hosterr=1;
      }

      if ($view == 1) {
	    if ($row2['statut'] == 'prob') {
          $nberr++;
        }
        print "<td></td></tr><tr><td><br><br></td></tr>";
      }

      if ( ($view == 0) || ($view == 2) ){

        if ($view == 0) {
          print "<td align='right'>";
          print "<b><font color=".$theme_line[4].">" . $days . "</b>d<b>" . $hours . "</b>h<b>" . $minutes . "</b>m</b>";
	      print "<br>";
        }

//TCP ports start
        $B=array("");
        $sql3 = "SELECT services, statut FROM manage_tcp where id = '" . $row['id'] . "'";
        $result3 = mysql_query($sql3);

        $l=0;
        while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
          $t=$row3['services'];
		  $t2=$t;
          for ($w=0;$w<count($port_number);$w++) {
            if ($t == $port_number[$w]) {
              $t=$port_name[$w];
			  $t2=$port_number[$w];
            }
          }

//start TCP port for full view
	      if ($view == 0) {
print "<table border=0 cellspacing=0><tr><td>";
            print '<dt onMouseOver="return overlib(\'<font color='.$theme_line[4].'>Port '.$t2.'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();">';
		    print $t;
print "</td><td>";
	        if ( ($row3['statut'] == "up") || ($row3['statut'] == "down")) {
			
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "<a href='" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $row['id'] . "&port=" . $row3['services'] . "'>";
              }
		      

//count errors, full view
		      if ( ($row3['statut'] == "down") and ($hosterr == 0) ) {
                $nberr++;
              }

            }
			


			
			if ($manage_link_method != 9) {
			  if ($manage_link_method == 0) {
                $connect='<a href=manage://'.$row["hostname"].':'.$row3['services'].'>Connect ('.$row3['services'].')</a>';
              }
			  if ($manage_link_method == 1) {
                $connect='<a href=manage_java.php?ip='.$row["hostname"].'&port='.$row3['services'].' target=manage_java'.$row["hostname"].$row3['services'].'>Connect ('.$row3['services'].')</a>';
              }
			  print ' <img src="./images/themes/'.$theme.'/led_'.$row3["statut"].'.png" border="0" onMouseOver="return overlib(\''.$connect.'\', RIGHT, OFFSETY, 5, WRAP, STICKY, TIMEOUT, '.$manage_connect_timeout.')"  onMouseOut="nd();">';
			} else {
			  print ' <img src="./images/themes/'.$theme.'/led_'.$row3["statut"].'.png" border="0">';
			}
			
			
			
			
			
			
			
			
			
			
			
            //manage 0.6.1
	        if ( ($row3['statut'] == "up") || ($row3['statut'] == "down")) {
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "</a>";
              }
            }


print "</td></tr></table>";
	      }
//end TCP port for Full view


//start TCP port for List view
	      if ($view == 2) {

            $fgh=0;
		    for ($fg=0;$fg<count($port_number);$fg++) {
		      if ($row3['services'] == $port_number[$fg]) {
		        $B[$port_name[$fg]] = $row3['statut'];
			    $fgh=1;
		      }
		    }
			
            if ($fgh == 0) {
              for ($jk=0;$jk<count($E);$jk++) {
                if ($A2[$E[$jk]] == $row3['services']) {
                  $B[$E[$jk]] = $row3['statut'];
                }
              }
		    }
			
	      }

//end TCP port for List view

	      $l++;
        }
//TCP ports end

//start display windows services
        $sql3 = "SELECT name, statut, oid FROM manage_services where id = '" . $row['id'] . "'";
        $result3 = mysql_query($sql3);

        while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
	      if ($view == 0) {
	        $t=substr($row3["name"], 0, 6). " ";
		    $t2=$row3["name"];
		    for ($re=0;$re<count($long_name);$re++) {
		      if (strtolower($row3["name"]) == strtolower($long_name[$re])) {
		        $t=$short_name[$re];
			    $t2=$long_name[$re];
		      }
		    }
		    print '<dt onMouseOver="return overlib(\'<font color='.$theme_line[4].'>'.$t2.'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();">';
	        print $t;
            if ( ($row3['statut'] == "up") || ($row3['statut'] == "down") ) {
              $ss="SELECT `oid` FROM `manage_services` WHERE `name`='".$row3['name']."' and id = '" . $row['id'] . "'";
              $rr = mysql_query($ss);
              $rrw = mysql_fetch_array($rr, MYSQL_ASSOC);
              $k=$rrw['oid']."\n";
			  
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "<a href='" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $row['id'] . "&win_svc=" . $k . "'>";
              }
			                
	        }
		    print " <img src='./images/themes/".$theme."/svc_led_".$row3['statut'].".png' border='0'></a>";

//count errors, full view
		    if ( ($row3['statut'] == "down") and ($hosterr == 0) ) {
              $nberr++;
            }
		  }

		  if ($view == 2) {
            $fgh=0;
		    for ($fg=0;$fg<count($long_name);$fg++) {
		      if (strtolower($row3['name']) == strtolower($long_name[$fg])) {
		        $B[$short_name[$fg]] = $row3['statut'];
			    $fgh=1;
		      }
		    }

            if ($fgh == 0) {
              for ($jk=0;$jk<count($D);$jk++) {
                if ($C2[$D[$jk]] == strtolower($row3['name'])) {
                  $B[$D[$jk]] = $row3['statut'];
                }
              }
		    }
	      }
          $l++;
        }
//end display windows services

//start display process
        $sql3 = "SELECT name, statut, tag FROM manage_process where id = '" . $row['id'] . "'";
        $result3 = mysql_query($sql3);

        while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
	      if ($view == 0) {
	        $t=substr($row3["name"], 0, 6). " ";
	        $t2=$row3["name"];
		    for ($re=0;$re<count($process_long_name);$re++) {
		      if (strtolower($row3["name"]) == strtolower($process_long_name[$re])) {
		        $t=$process_short_name[$re];
			    $t2=$process_long_name[$re];
		      }
		    }
		    print '<dt onMouseOver="return overlib(\'<font color='.$theme_line[4].'>'.$t2.'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();">';
            print $t;
            if ( ($row3['statut'] == "up") || ($row3['statut'] == "down") ) {
              $ss="SELECT `tag` FROM `manage_process` WHERE `name`='".$row3['name']."' and id = '" . $row['id'] . "'";
              $rr = mysql_query($ss);
              $rrw = mysql_fetch_array($rr, MYSQL_ASSOC);
              $k=$rrw['tag']."\n";
			  
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "<a href='" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $row['id'] . "&tag=" . $k . "'>";
              }
			  
            }
		    print " <img src='./images/themes/".$theme."/prc_led_".$row3['statut'].".png' border='0'></a>";

//count errors, full view
		    if ( ($row3['statut'] == "down") and ($hosterr == 0) ) {
              $nberr++;
            }
		  }

		  if ($view == 2) {
            $fgh=0;
		    for ($fg=0;$fg<count($process_long_name);$fg++) {
		      if ($row3['name'] == $process_long_name[$fg]) {
		        $B[$process_short_name[$fg]] = $row3['statut'];
			    $fgh=1;
		      }
		    }

            if ($fgh == 0) {
			  for ($jk=0;$jk<count($G);$jk++) {
                if ($F2[$G[$jk]] == strtolower($row3['name'])) {
                  $B[$G[$jk]] = $row3['statut'];
                }
              }
            }
	      }
          $l++;
        } //end while

// count errors
	    if ($view == 2) {
          for ($yu=0;$yu<count($E);$yu++) {
            print "<td width='30' bgcolor='".$clr."'><center>";
            if (isset($B[$E[$yu]])) {
              $vs=$B[$E[$yu]];
            } else {
              $vs="";
            }
            if ( ($vs == "up") || ($vs == "down") ) {
			  $val=$A2[$E[$yu]];
	          $new="`services`='".$val."'";

              $az=explode("<br>",$A2[$E[$yu]]);
			  if (count($az) > 1) {
			    $new="";
			    for ($rg=0;$rg<count($az);$rg++) {
			      if ($rg == 0) {
			        $new = "`services`='".$az[$rg]."'";
			      } else {
			        $new .= " || `services`='".$az[$rg]."'";
			      }
			    }
			  }
			  $new .= ")";

	          $ss="SELECT `services` FROM `manage_tcp` WHERE id='" . $row['id']."' and (".$new;
	          $rr = mysql_query($ss);
	          $rrw = mysql_fetch_array($rr, MYSQL_ASSOC);
	          $k=$rrw['services'];
			  
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "<a href='" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $row['id'] . "&port=" . $k . "'>";
              }

//count errors ports, List view
	          if ( ($vs == "down") and ($hosterr == 0) ) {
                $nberr++;
              }
            }

			if (isset($k)) {
			  if ($vs != "") {
			    if ($manage_link_method != 9) {
				  if ($manage_link_method == 0) {
                    $connect='<a href=manage://'.$row["hostname"].':'.$k.'>Connect ('.$k.')</a>';
                  }
				  if ($manage_link_method == 1) {
                    $connect='<a href=manage_java.php?ip='.$row["hostname"].'&port='.$k.' target=manage_java'.$row["hostname"].$k.'>Connect ('.$k.')</a>';
                  }
				  print '<img src="./images/themes/'.$theme.'/led_'.$vs.'.png" border="0" onMouseOver="return overlib(\''.$connect.'\', RIGHT, OFFSETY, 5, WRAP, STICKY, TIMEOUT, '.$manage_connect_timeout.')"  onMouseOut="nd();">';
				} else {
				  print '<img src="./images/themes/'.$theme.'/led_'.$vs.'.png" border="0">';
				}
			  } else {
			    print '<img src="./images/themes/'.$theme.'/led_'.$vs.'.png" border="0">';
			  }
			} else {
			  print '<img src="./images/themes/'.$theme.'/led_.png" border="0">';
			}
			
            print "</td>";
          }

		  for ($yu=0;$yu<count($D);$yu++) {
            print "<td width='30' bgcolor='".$clr."'><center>";
            if (isset($B[$D[$yu]])) {
              $vs=$B[$D[$yu]];
            } else {
              $vs="";
            }
            if ( ($vs == "up") || ($vs == "down") ) {
			  $val=$C2[$D[$yu]];
	          $new="`name`='".$val."'";

              $az=explode("<br>",$C2[$D[$yu]]);
			  if (count($az) > 1) {
			    $new="";
			    for ($rg=0;$rg<count($az);$rg++) {
			      if ($rg == 0) {
			        $new = "`name`='".$az[$rg]."'";
			      } else {
			        $new .= " || `name`='".$az[$rg]."'";
			      }
			    }
			  }
			  $new .= ")";
	          $ss="SELECT `oid` FROM `manage_services` WHERE id='" . $row['id']."' and (".$new;
	          $rr = mysql_query($ss);
	          $rrw = mysql_fetch_array($rr, MYSQL_ASSOC);
	          $k=$rrw['oid'];
			  
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "<a href='" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $row['id'] . "&win_svc=" . $k . "'>";
              }
	          
            }

//count services errors for list view
            if ( ($vs == "down") and ($hosterr == 0) ) {
              $nberr++;
            }

            print "<img src='./images/themes/".$theme."/svc_led_".$vs.".png' border='0'>";
            print "</td>";
		  }

          for ($yu=0;$yu<count($G);$yu++) {
            print "<td width='30' bgcolor='".$clr."'><center>";

            if (isset($B[$G[$yu]])) {
              $vs=$B[$G[$yu]];
            } else {
              $vs="";
            }

            if ( ($vs == "up") || ($vs == "down") ) {
			  $val=$F2[$G[$yu]];
	          $new="`name`='".$val."'";

              $az=explode("<br>",$F2[$G[$yu]]);
			  if (count($az) > 1) {
			    $new="";
			    for ($rg=0;$rg<count($az);$rg++) {
			      if ($rg == 0) {
			        $new = "`name`='".$az[$rg]."'";
			      } else {
			        $new .= " || `name`='".$az[$rg]."'";
			      }
			    }
			  }
              $new .= ")";
	          $ss="SELECT `tag` FROM `manage_process` WHERE id='" . $row['id']."' and (".$new;
	          $rr = mysql_query($ss);
	          $rrw = mysql_fetch_array($rr, MYSQL_ASSOC);
	          $k=$rrw['tag'];
			  
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "<a href='" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $row['id'] . "&tag=" . $k . "'>";
              }
	          
            }

//count errors for services, list view
            if ( ($vs == "down") and ($hosterr == 0) ) {
              $nberr++;
            }

            print "<img src='./images/themes/".$theme."/prc_led_".$vs.".png' border='0'>";
            print "</td>";
		  }

        }  //end view 2

        if ($view == 0) {
		  if ($l == 0 ) {
		    $l = 1;
		  }

          $j=0;
		  if ($l < 3 ) {
		    $j = 4-$l;
		  }

          for ($k=0;$k<$j;$k++) {
            print "<br>";
          }

          print "</td>";
        }

      }  //end view 0 or 2

      if ($view == 0) {
        print "</tr>";
      }

//display overlib for Simple and full view
      if ( ($view == 0) || ($view == 1) ) {
	    print "</table>";
        print "</td></tr></table>";
        manage_overlib_graph($row['id'], $blink, $theme_line[5], $row['description'], $row['hostname']);
      }

      $current_line++;

      if ($view == 2) {
        print "</td></tr>";
      }

    }  //end test do

  }  //end while

}  //end full simple list












//start Site View
if ($view == 3) {
  print "<tr>";

  $nb = mysql_num_rows($result);
  for ($j=0;$j<$nb;$j++) {
    $row_arrayCde[$j] = mysql_fetch_row($result);
  }

  $nberr=0;

  $sql5 = "SELECT * FROM manage_sites order by name";
  $result5 = mysql_query($sql5);
  while ($row5 = mysql_fetch_array($result5, MYSQL_ASSOC)) {

	if ($current_line == $ligne) {
      print "</tr><tr>";
	  $current_line=0;
    }

    print "<td><center><table border='0'><tr><td valign='top'>";

    $st="nopoll";
    $nberrs=0;
    $nbt=0;
    for ($k=0; $k < $nb; $k++) {
	  if ($row5["id"] == $row_arrayCde[$k][7]) {
	    $nbt++;
		if ( ($row_arrayCde[$k][3] == "up") and ($st == "nopoll") ) {
		  $st="up";
		}
		if ( ($row_arrayCde[$k][3] == "prob") and ( ($st == "nopoll") || ($st == "up") ) ) {
		  $st="prob";
		}
		if ($row_arrayCde[$k][3] == "treshold") {
		  $st="treshold";
		}
		if ($row_arrayCde[$k][3] == "down") {
		  $st="down";
		}
		if ( $row_arrayCde[$k][3] == "prob" || $row_arrayCde[$k][3] == "down" ) {
		  $nberrs++;
		  $nberr++;
        }
	  }
    }

	print "<img src='images/themes/".$theme."/".$st.".png'>";
	print "</td><td>";

    $vs=db_fetch_cell("select value from settings where name='manage_list_2_".$user_id."'");
    $v2="";
    if (isset($_REQUEST["err"])) {
      $v2="&err=".trim($_REQUEST["err"], "#");
    }

    ?>
    <div>
    <a href="javascript:toggleVisibility('texte_s_<?php print $row5["id"]?>')"><font color=<?php print $theme_line[5]; ?>>
    <?php
    $tmp=db_fetch_cell("SELECT count(manage_groups.id) FROM manage_sites, manage_groups where manage_groups.site_id=manage_sites.id and manage_sites.id='" . $row5["id"] . "'");
    if ($tmp > 0) {
      print "[+]";
    } else {
      print "[  ]";
    }
    ?>
    </a>
    <?php
    print "<a href='" . $config["url_path"] . "plugins/manage/manage.php?simple=".$vs."&group=0".$v2."&site=".$row5["id"];
    print "&forceids=0&manage_sound_enable=";
    if ($playsound == "1") {
      print "on";
    }
    print "'><font color=".$theme_line[5]."><b>".$row5["name"]."</b></a> (".$nberrs."/".$nbt.")";
    ?>
    </div>
    <div id="texte_s_<?php print $row5["id"]?>" style="<?php print $expand_style?>">
    <?php

    $sql6 = "SELECT * FROM manage_groups where site_id=".$row5["id"]." order by name";
    $result6 = mysql_query($sql6);
    while ($row6 = mysql_fetch_array($result6, MYSQL_ASSOC)) {
      $st="nopoll";
      $msg="<table>";
      $nberrs=0;
      $nbt2=0;
      for ($k=0; $k < $nb; $k++) {
        if ($row6["id"] == $row_arrayCde[$k][5]) {
          $nbt2++;
 	      if ( ($row_arrayCde[$k][3] == "up") and ($st == "nopoll") ) {
		    $st="up";
		  }
	      if ( ($row_arrayCde[$k][3] == "prob") and ( ($st == "nopoll") || ($st == "up") ) ) {
		    $st="prob";
	      }
          if ($row_arrayCde[$k][3] == "treshold") {
		    $st="treshold";
	      }
	      if ($row_arrayCde[$k][3] == "down") {
		    $st="down";
	      }
	      if ( $row_arrayCde[$k][3] == "prob" || $row_arrayCde[$k][3] == "treshold" || $row_arrayCde[$k][3] == "down" ) {
		    $nberrs++;
		    $msg .= "<tr><td><img src=images/themes/".$theme."/".$row_arrayCde[$k][3].".png></td><td><font color=".$theme_line[4].">".$row_arrayCde[$k][2]." (".$row_arrayCde[$k][1].")</td></tr>";
          }
	    }
      }

#      print "<img src='images/themes/".$theme."/".$st.".png'> <a href='" . $config["url_path"] . "plugins/manage/manage.php?simple=".$vs."&group=".$row6["id"].$v2;
      print "<img src='images/themes/".$theme."/".$st.".png'> <a href='" . $config["url_path"] . "plugins/manage/manage.php?simple=2&group=".$row6["id"].$v2;
      print "&forceids=0&manage_sound_enable=";
      if ($playsound == "1") {
        print "on";
      }
      print "'";
      if ($msg <> "<table>") {
  	    print ' onMouseOver="return overlib(\''.$msg.'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();"';
      }
	  print "><font color=".$theme_line[5].">".$row6["name"]."</a> (".$nberrs."/".$nbt2.")<br>";
    }
  
    print "</div></td></tr></table>";

	$current_line++;
  }
}  //end Site View







//start Tree Mode View
if ($view == 4) {
  $nbt=0;
  print "<tr>";
  print "<td><center><table width='100%' align=center cellspacing='0' border='0'>";
  manage_tree_g($v,$v,$ligne,$current_line,$theme,$theme_line,$expand_style_no,$expand_style_yes,$expand_level,$plus,$np,$nbt);
  print "</tr></table>";		
}//end Tree Mode View








print "</tr><tr>";
print "</tr></table>";
print "<center>";
$tmp = db_fetch_cell("select value from settings where name='manage_date'");
print "<font color='#".$theme_line[4]."'><center><b>Last poll date : </b>" . $tmp . "<br>";

//display legend
$tmp=db_fetch_cell("select value from settings where name='manage_legend_".$user_id."'");
if ($tmp == "on") {
  print "<br><center><table cellpadding=1 cellspacing=0 bgcolor='#000000'><tr><td>&nbsp;<font color='#FFFFFF'><b>Legend</b></font></td></tr><tr><td bgcolor='#000000'>\n";
  print "<table cellspacing=0 bgcolor='#FFFFFF' id=legend>\n";
  print "<tr align=center><td></td><td>Up</td><td>Waiting</td><td>Alert</td><td>Tresholded</td><td>Down</td>";
  print "<tr align=center><td>Host</td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/up.png'></td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/nopoll.png'></td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/prob.png'></td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/treshold.png'></td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/down.png'></td></tr>";
  print "<tr align=center><td>TCP Port</td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/led_up.png'></td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/led_nopoll.png'></td><td>NA</td><td>NA</td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/led_down.png'></td></tr>";
  print "<tr align=center><td>Windows Service</td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/svc_led_up.png'></td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/svc_led_nopoll.png'></td><td>NA</td><td>NA</td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/svc_led_down.png'></td></tr>";
  print "<tr align=center><td>Process</td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/prc_led_up.png'></td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/prc_led_nopoll.png'></td><td>NA</td><td>NA</td><td><img src='" . $config['url_path'] . "plugins/manage/images/themes/".$theme."/prc_led_down.png'></td></tr>";
  print "</table></td></tr></table></center>\n";
}

//display info version
include_once "setup.php";
$infos=plugin_manage_version ();
$pagefoot = "<td align=left class='textHeader' nowrap width=88><a href='http://www.bosrup.com/web/overlib/' alt='Popups by overLIB!' target='_blank'><img src='lib/power.gif' border='0' ></a></td><td align=left class='textHeader' nowrap width=10></td><td align=left class='textHeader' nowrap>Powered by <a href='".$infos["homepage"]."' target='_blank'>PHP Network Managing version ".$infos["version"]."</a></td>";
$pagefoot .= "<td align=right class='textHeader' nowrap>";
$pagefoot .= "<a href='javascript:manage_popup2(\"".$config['url_path']."plugins/manage/manage_settings.php\")'>User settings</a></td>";

html_graph_start_box(1,true);
?>
<tr bgcolor="<?php print $colors["panel"];?>"><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td class="textHeader" nowrap> <?php print $pagefoot; ?> </td></tr></table></td></tr></table><br>
<?php


if ($view < 4) {

  if ( ($_REQUEST["forceids"] == "0") || ($view == 3) ) {
  $new_site = $_REQUEST["site"];
  if(!isset($_REQUEST["group"]))
  {
      $_REQUEST["group"] = 0;
  }
  $fh=0;
  if ($_REQUEST["site"] == "0") {
    if ($_REQUEST["group"] == "0") {
      $text="<center><font color='#".$theme_line[1]."' size='2'><b>All devices</b></font><font color='#".$theme_line[1]."'> (".$nberr." errors / ".$total_devices." devices)";
      if ($nberr > 0) {
        $error_detected = 1;
      }
      $prev = 0;
      $fh=1;
    } else {               // one group selected and no site
      $w2 = db_fetch_cell("SELECT name FROM manage_groups where id='".$_REQUEST["group"]."'");
      $w3 = db_fetch_cell("SELECT site_id FROM manage_groups where id='".$_REQUEST["group"]."'");
      $w = db_fetch_cell("SELECT name FROM manage_sites where id='".$w3."'");
      
	  if ($_REQUEST["forceids"] == "0") {
	    $text = "<center><font color='#".$theme_line[1]."' size='2'><b>Site : ".$w." - Group : ".$w2."</b></font><font color='#".$theme_line[1]."'> (".$nberr." errors / ".$total_devices." devices)";
	  } else {
	    $text = "<center><font color='#".$theme_line[1]."'>";
	  }
	  
      if ($nberr > 0) {
        $error_detected = 1;
      }

      $wm = db_fetch_cell("SELECT max(id) FROM manage_groups where site_id='".$w3."'");
      if ($_REQUEST["group"] == $wm) {        //this is the last group, we need to change site
//print " max_group_detected ";
        $wsm = db_fetch_cell("SELECT max(id) FROM manage_sites");
        if ($w3 == $wsm) {               //this site was the last site, we need to select the first group from the first site
//print " max_site_detected ";
          $next = db_fetch_cell("SELECT min(id) FROM manage_groups where site_id in(SELECT min(id) FROM manage_sites)");
          $new_site = "0";
        } else {
          $st=array();
          $ic=0;
          $sql = "SELECT id FROM manage_sites order by id";
          $result = mysql_query($sql);
          while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $st[$ic] = $row2["id"];
            $ic++;
          }
          for ($j=0;$j<$ic;$j++) {
            if ($st[$j] == $w3) {
              $pos=$j;
            }
          }

          $next = db_fetch_cell("SELECT min(id) FROM manage_groups where site_id='".($st[$pos+1])."'");
          $next_site = "0";
        }

      } else {
        $prev = $_REQUEST["group"];
        $fh=1;
      }

    }

  } else {

    if (!isset($_REQUEST["group"]) or $_REQUEST["group"] == "0") {              //one site selected and no group
      $w = db_fetch_cell("SELECT name FROM manage_sites where id='".$_REQUEST["site"]."'");

      $text = "<center><font color='#".$theme_line[1]."' size='2'><b>Site : ".$w." - No group selected</b></font><font color='#".$theme_line[1]."'> (".$nberr." errors / ".$total_devices." devices)";

      if ($nberr > 0) {
        $error_detected = 1;
      }

      $next = db_fetch_cell("SELECT min(id) FROM manage_groups where site_id ='".$_REQUEST["site"]."'");

    } else {

	  $w2 = db_fetch_cell("SELECT name FROM manage_groups where id='".$_REQUEST["group"]."'");
      $w3 = db_fetch_cell("SELECT site_id FROM manage_groups where id='".$_REQUEST["group"]."'");
      $w = db_fetch_cell("SELECT name FROM manage_sites where id='".$w3."'");

	  if ($_REQUEST["forceids"] == "0") {
        $text = "<center><font color='#".$theme_line[1]."' size='2'><b>Site : ".$w." - Group : ".$w2."</b></font><font color='#".$theme_line[1]."'> (".$nberr." errors / ".$total_devices." devices)";
      } else {
	    $text = "<center><font color='#".$theme_line[1]."'> (".$nberr." errors / ".$total_devices." devices)";
	  }
	  if ($nberr > 0) {
        $error_detected = 1;
      }
	
      $wm = db_fetch_cell("SELECT max(id) FROM manage_groups where site_id='".$_REQUEST["site"]."'");
      if ($_REQUEST["group"] == $wm) {
	    $new_site = $_REQUEST["site"];
	    $next = db_fetch_cell("SELECT min(id) FROM manage_groups where site_id='".$_REQUEST["site"]."'");
      } else {
        $new_site = $_REQUEST["site"];
        $prev = $_REQUEST["group"];
        $fh=1;
      }
    }
  }

  if ($fh == 1) {
    $gr=array();
    $ic=0;
    $sql = "SELECT id FROM manage_groups order by site_id, id";
    $result = mysql_query($sql);
    while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $gr[$ic] = $row2["id"];
      $ic++;
    }

    $pos=0;

    for ($j=0;$j<$ic;$j++) {
      if ($gr[$j] == $prev) {
        $pos=$j;
      }
    }

    if ($pos == count($gr)-1) {
      $pos=-1;
    }

    $next=$gr[$pos+1];

    if ($prev == 0) {
      $next=$gr[0];
    }

  }

  $text_tmp="<center><table width='98%' border='0' cellspacing='0'><tr bgcolor='#".$theme_line[0]."'><td>";

  if ($view == 3) {
    $text=$text_tmp."<font color='#".$theme_line[1]."' size='2'><center><b>Sites</font><font color='#".$theme_line[1]."'>";
    if ($nbt > 0) {
      $error_detected = 1;
    }

  } else {
    $text=$text_tmp.$text;
  }

  $text .= "</b> (".$refresh_time.")";

  } else {            //we have selected a leaf from tree view

    if ($nberr > 0) {
      $error_detected = 1;
    }
	
    $prev=$_REQUEST["group"];
	$next="";
	$tmp = db_fetch_cell("SELECT title FROM graph_tree_items where id='".$_REQUEST["group"]."'");
	if ($tmp == "") {
	  $tmp = "Host";
	}
	
	$is_tree = strpos($_REQUEST["group"], "t");
	if ($is_tree === false) {
//
    } else {
	  $tree_title=db_fetch_cell("SELECT name FROM `graph_tree` WHERE id='".substr($_REQUEST["group"], 1)."'");
      $tmp = "Tree ".$tree_title;
    }

    $text="<center><table width='98%' border='0' cellspacing='0'><tr bgcolor='#".$theme_line[0]."'><td><font color='#".$theme_line[1]."' size='2'><center><b>".$tmp."</font><font color='#".$theme_line[1]."'> </b>(".$nberr." errors / ".$total_devices." devices) (".$refresh_time.")";
	$new_site="";

	$arr_pos=array("");
	$current_pos=0;
	$pos=0;
    foreach (array_keys($v) as $rr) {
      $arr_pos[$current_pos]=$rr;
      if ($rr == $prev) {
	    $pos=$current_pos;
		$next=$current_pos+1;
	  }
      $current_pos++;
	}
	$max=count($v);
  	if ($next >= $max) {
	  $next=2;
    }
    $next=$arr_pos[$next];
	
  }
  
  if ( ($playsound == "1") && ($error_detected == "1") ) {
	print '<EMBED src="' . $config['url_path'] . 'plugins/manage/images/themes/'.$theme.'/attn-noc.wav" autostart=true loop=true volume=100 hidden=true><NOEMBED><BGSOUND src="' . $config['url_path'] . 'plugins/manage/images/themes/'.$theme.'/attn-noc.wav"></NOEMBED>' . "\n";
  }
  
  $text = $warning_message.$text;

  echo ":::".$text.":::".$prev.":::".$next.":::".$new_site;
  
}

if ($view == 4) {

  if ( ($playsound == "1") && ($nbt > 0) ) {
	print '<EMBED src="' . $config['url_path'] . 'plugins/manage/images/themes/'.$theme.'/attn-noc.wav" autostart=true loop=true volume=100 hidden=true><NOEMBED><BGSOUND src="' . $config['url_path'] . 'plugins/manage/images/themes/'.$theme.'/attn-noc.wav"></NOEMBED>' . "\n";
  }

  $prev="";
  $next="";
  $new_site="";
  
  echo ":::"."<center>(".$refresh_time."):::".$prev.":::".$next.":::".$new_site;
  
}


function manage_order($o2, $f2) {
  if ($f2 == 0) {
    $o3 = " order by ";
  } else {
    $o3 = ", ";
  }

  if ($o2 == 1) {
    $o3 .= "manage_sites.name ASC";
  }
  if ($o2 == 2) {
    $o3 .= "manage_groups.name ASC";
  }
  if ($o2 == 3) {
    $o3 .= "host.description ASC";
  }
  if ($o2 == 4) {
    $o3 .= "host.hostname ASC";
  }
  if ($o2 == 5) {
    $o3 .= "manage_host.statut ASC";
  }
  if ($o2 == 6) {
    $o3 .= "manage_host.uptime ASC";
  }

  if ($o2 == 11) {
    $o3 .= "manage_sites.name DESC";
  }
  if ($o2 == 12) {
    $o3 .= "manage_groups.name DESC";
  }
  if ($o2 == 13) {
    $o3 .= "host.description DESC";
  }
  if ($o2 == 14) {
    $o3 .= "host.hostname DESC";
  }
  if ($o2 == 15) {
    $o3 .= "manage_host.statut DESC";
  }
  if ($o2 == 16) {
    $o3 .= "manage_host.uptime DESC";
  }
  
  return $o3;
}

//graph preview graphs for each host
function manage_overlib_graph($id_t, $blink_t, $theme_line_t, $hostname_t, $description_t) {
  global $config;

  $result5 = mysql_query("SELECT * FROM graph_local where host_id = '" . $id_t . "'");

  $u=0;
  $u2=0;
  $str="";

  while ($row5 = mysql_fetch_array($result5, MYSQL_ASSOC)) {
    $str .= "<img src=../../graph_image.php?local_graph_id=".$row5['id']."&rra_id=0&graph_height=50&graph_width=150&graph_nolegend=true&print_source=false>";
    $u++;
    if ($u == 2) {
      $str .= "<br>";
	  $u=0;
	  $u2=1;
    }
  }

  if (strlen($str) != 0) {
	?>
<dt onmouseover="return overlib('<?php
print $str;
if ($u2 == 1) {
?>
',DELAY,2000,WIDTH,515);" onMouseOut="nd();">
<?php
} else {
?>
',DELAY,2000);" onMouseOut="nd();">
<?php
}
  }

  $td=0;
  if ($blink_t == "1") {
    $sql6 = "SELECT * FROM thold_data where host_id = '" . $id_t . "' and thold_enabled = 'on'";
    $result6 = mysql_query($sql6);
    while ($row6 = mysql_fetch_array($result6, MYSQL_ASSOC)) {
	  if ($row6["thold_fail_count"] >= 1) {
        $td=1;
      }
	  if ($row6["thold_fail_count"] >= $row6["thold_fail_trigger"]) {
        $td=2;
      }
	} 
  }

  print "<center><a href='" . $config["url_path"] . "graph_view.php?action=preview&host_id=" . $id_t . "'><font color=".$theme_line_t.">";
  $msg = $description_t. " (".$hostname_t.")";
  if ($td == 0) {
	print $msg;
  }

  if ($td == 1) {
	print "<blink><b><font color='yellow'>".$msg."</font></b></blink>";
  }

  if ($td == 2) {
	print "<blink><b><font color='red'>".$msg."</font></b></blink>";
  }
		
}

//graph menu overlib when you right-click
function manage_overlib_rightclick($id, $descr, $stat, $email, $note, $skin) {
  global $config;
  
  //0.6.2
  $descr = str_replace("'", " ", $descr);
  
			  $permit=manage_accounts_permit("reporting");
              if ($permit == 1) {
                print "<a href='" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $id . "'>";
              }
  

  $user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);
  
  $permit = db_fetch_cell("SELECT count(*) FROM user_auth_realm WHERE user_id ='".$user_id."' AND realm_id='3'");
  if ($permit == 1) {
    $tmp = "<a href=" . $config["url_path"] . "host.php?action=edit&id=" . $id . ">Management -> Devices</a><br>";
  } else {
    $tmp = "";
  }

  $link=db_fetch_cell("SELECT data FROM manage_admin_link where id='".$id."' limit 1");

  $permit=manage_accounts_permit("reporting");
if ($permit == 1) {
  $tmp .= "<a href=" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $id . ">Device Managing -> Event Reporting</a><br>";
}

  if ($link != "") {
    $tmp .= "<a href=" . $link . " target=_blank>Remote administration</a><br>";
  }
  
	$mails = explode(",", $email);
    $tmp .= "Mail to: ";
	if (count($mails) < 2) {
	  $tmp .= "<a href=mailto:".$email.">".$email."</a>";
	} else {
      foreach ($mails as $mail) {
        $tmp .= "<a href=mailto:".$mail.">".$mail."</a> ";
      }
	}

  $permit = db_fetch_cell("SELECT count(*) FROM user_auth_realm WHERE user_id ='".$user_id."' AND realm_id='3'");
  if ($permit == 1) {
    $new_note="";
	$note2 = str_replace("\n", "<br>", $note);
	$note2 = str_replace("\r", "", $note2);
    $note_lines = explode("<br>", $note2);
	$note_counter=0;
    foreach ($note_lines as $note_line) {
	  if ($note_counter < 2) {
	    if ($note_counter == 0) {
	      $new_note .= $note_line."<br>";
		} else {
		  $new_note .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$note_line."<br>";
		}
	  }
	  $note_counter++;
	}
    $tmp .= "<br>Notes: ".$new_note;
  }

    print '<img src="./images/themes/'. $skin. '/'.$stat.'.png" border="0" align=middle oncontextmenu="return overlib(\''.$tmp.'\', HAUTO, OFFSETY, 5, WRAP, STICKY, CLOSECLICK, CAPTION, \''.$descr.'\', CLOSETEXT, \'Close\')">';
		
    print "</a>";
  
}

//calcul for tree view
function manage_tree_c(&$v,&$plus) {
  $parent_tmp=array("");

//limit
$manage_tree_analyze = db_fetch_cell("select value from settings where name='manage_tree_analyze'")+2;
if ( ($manage_tree_analyze == "") || ($manage_tree_analyze == "Is not set") ) {
  $manage_tree_analyze=5;
}

//select only trees we can view  
  $default_tree_perm=db_fetch_cell("select policy_trees from user_auth where id=" .$_SESSION["sess_user_id"]);
  $user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);
  if ($default_tree_perm == "2") {
    $trees = db_fetch_assoc("SELECT * FROM graph_tree, user_auth_perms where graph_tree.id = user_auth_perms.item_id and user_auth_perms.user_id=".$user_id);
  }
  if ($default_tree_perm == "1") {
    $trees = db_fetch_assoc("SELECT * FROM graph_tree where graph_tree.id NOT IN (select user_auth_perms.item_id from user_auth_perms where user_auth_perms.user_id=".$user_id.")");
  }
  
//select only hosts we can view
  $default_host_perm=db_fetch_cell("select policy_hosts from user_auth where id=" .$_SESSION["sess_user_id"]);
  $host_perm="";
  if ($default_host_perm == "1") {
	$host_perm="not ";
  }

  foreach ($trees as $tree) {

	$heirarchy = db_fetch_assoc("select
		graph_tree_items.id,
		graph_tree_items.title,
		graph_tree_items.local_graph_id,
		graph_tree_items.rra_id,
		graph_tree_items.host_id,
		graph_tree_items.order_key,
		graph_templates_graph.title_cache as graph_title,
		CONCAT_WS('',host.description,' (',host.hostname,')') as hostname,
		settings_tree.status
		from graph_tree_items
		left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0)
		left join settings_tree on (graph_tree_items.id=settings_tree.graph_tree_item_id)
		left join host on (graph_tree_items.host_id=host.id)
		where graph_tree_id='".$tree['id']."'
		order by graph_tree_items.order_key");
		
	$parent="";
	$v[$parent]="";
	$v["t".$tree['id']] = "-1";
	if (sizeof($heirarchy) > 0) {
	  foreach ($heirarchy as $leaf) {

		$tier = tree_tier($leaf["order_key"]);

	    if ( ($leaf["title"] != "") && ($leaf["local_graph_id"] == "0") ) {      // header object
          $parent_tmp[$tier]=$leaf["id"];
if ($tier < $manage_tree_analyze) {
		  $v[$parent_tmp[$tier-1]]=$v[$parent_tmp[$tier-1]].";*".$leaf["id"];
}
		  $plus[$parent_tmp[$tier-1]]="+";
		  $parent=$leaf["id"];
		  $v[$leaf["id"]]="-1";
		}

	    if ($leaf["rra_id"] != "0") {                                            // graph object
          $tt = db_fetch_cell("SELECT host_id FROM `graph_local`, host WHERE graph_local.id ='".$leaf["local_graph_id"]."' and graph_local.host_id=host.id and (host.manage = 'on') and (host.disabled <> 'on') and host_id ". $host_perm ."in (select item_id from user_auth_perms where user_auth_perms.user_id='".$user_id."')");
		  if ($tt != "") {
		    $vps = explode(";", $v[$parent]);
            $fvp=0;
            foreach ($vps as $vp) {
              if ($vp == $tt) {
                $fvp=1;
              }
            }
            if ($fvp == 0) {
if ($tier < $manage_tree_analyze) {
	          $v[$parent] = $v[$parent].";".$tt;
              $v["t".$tree['id']] = $v["t".$tree['id']].";".$tt;
}

			}
		  }
		}

	    if ( ($leaf["title"] == "") && ($leaf["local_graph_id"] == "0") ) {      // host object
          $tt = db_fetch_cell("SELECT id FROM host WHERE (host.manage = 'on') and (host.disabled <> 'on') and id ". $host_perm ."in (select item_id from user_auth_perms where user_auth_perms.user_id='".$user_id."') and id='".$leaf["host_id"]."'");
		  if ($tt != "") {
if ($tier < $manage_tree_analyze) {
		    $v[$parent_tmp[$tier-1]]=$v[$parent_tmp[$tier-1]].";".$leaf["host_id"];
			$v["t".$tree['id']] = $v["t".$tree['id']].";".$leaf["host_id"];
}
		    $v[$leaf["id"]]=$leaf["host_id"];
		    $plus[$parent_tmp[$tier-1]]="+";
		    $plus[$leaf["id"]]="*";
		  }
		}
	  } //end foreach leaf
	}
  }  //end foreach tree

}

//a function for tree view
function manage_sid($id) {
  if ($id != "") {
    $sids = explode(";", $id);
    $t=0;
    foreach ($sids as $pid) {
      $t++;
    }

    $r = " and (";
    $r .= "manage_host.id=".$sids[0];
    for ($counter=1;$counter<$t;$counter++) {
	  if ($sids[$counter] != "") {
	    $r .= " or manage_host.id=".$sids[$counter];
	  }
    }
    $r .= ")";

  } else {
    $r = " and (manage_host.id=-1)";
  }

  $ress = db_fetch_assoc("select `statut` from manage_host,host where manage_host.id=host.id and host.manage='on' and host.disabled<>'on'".$r);
  $res="none";

  foreach ($ress as $re) {
    if ($re["statut"] == "down") {
	  $res="down";
	}
    if ( ($re["statut"] == "prob") and ($res != "down") ) {
	  $res="prob";
	}
    if ( ($re["statut"] == "up") and ($res != "down") and ($res != "prob") ) {
	  $res="up";
	}
  }
  return $res;
}

//graph tree view
function manage_tree_g($v2,$v3,$ligne,$current_line,$theme,$theme_line,$expand_style_no,$expand_style_yes,$expand_level,$plus,$np,&$nbt) {
  global $config;

  $user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);
  
//select only trees we can see
  $default_tree_perm=db_fetch_cell("select policy_trees from user_auth where id=" .$_SESSION["sess_user_id"]);
  if ($default_tree_perm == "2") {
    $trees = db_fetch_assoc("SELECT * FROM graph_tree, user_auth_perms where graph_tree.id = user_auth_perms.item_id and user_auth_perms.user_id=".$user_id);
  }
  if ($default_tree_perm == "1") {
    $trees = db_fetch_assoc("SELECT * FROM graph_tree where graph_tree.id NOT IN (select user_auth_perms.item_id from user_auth_perms where user_auth_perms.user_id=".$user_id.")");
  }
  
  foreach ($trees as $tree) {
    if ($current_line == $ligne) {
      print "</tr><tr>";
	  $current_line=0;
    }
	
	print "<td valign='top'><center><font color=".$theme_line[4].">";

	if (isset($_REQUEST["simple"])) {
	  $tmp=db_fetch_cell("select value from settings where name='manage_list_2_".$user_id."'");
	  $l="?simple=".$tmp;
	}
	if (isset($_REQUEST["err"])) {
	  $l.="&err=".$_REQUEST["err"];
	}
	if (isset($_REQUEST["manage_sound_enable"])) {
	  $l.="&manage_sound_enable=".$_REQUEST["manage_sound_enable"];
	}
	$l.="&forceids=1&group="."t".$tree['id']."&site=-1";
	
    $cc=manage_sid($v3["t".$tree['id']]);
	$nbtreeerr=0;
	$msg=manage_over($v3["t".$tree['id']],$theme,$theme_line,$nbtreeerr,$nbt);
	
	print '<img src=images/themes/'.$theme.'/'.$cc.'.png border=0 align=middle>';
	
	      if ( ($cc == "prob") || ($cc == "down") ) {
            print ' <a href=' . $config["url_path"] . 'plugins/manage/manage.php'.$l.' onMouseOver="return overlib(\''.$msg.'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();"><font color='.$theme_line[5].'><b>'.$tree['name'].'</b></a> ('.$nbtreeerr.'/'.$np["t".$tree['id']].')</font></center><br>';
          }
          if ($cc == "up") {
            print ' <a href=' . $config["url_path"] . 'plugins/manage/manage.php'.$l.'><font color='.$theme_line[5].'><b>'.$tree['name'].'</b></a> ('.$nbtreeerr.'/'.$np["t".$tree['id']].')</font></center><br>';
          }
		  
		  
		  //0.6.2
          //if ($cc == "none") {
            //print ' <font color='.$theme_line[5].'><b>'.$tree['name'].'</b> (------0/0)</font></center><br>';
          //}

		  
	
//	print "<b>".$tree['name']."</b></font></center><br>";
	$hide_until_tier = false;

	$heirarchy = db_fetch_assoc("select
		graph_tree_items.id,
		graph_tree_items.title,
		graph_tree_items.local_graph_id,
		graph_tree_items.rra_id,
		graph_tree_items.host_id,
		graph_tree_items.order_key,
		graph_templates_graph.title_cache as graph_title,
		CONCAT_WS('',host.description,' (',host.hostname,')') as hostname,
		settings_tree.status
		from graph_tree_items
		left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0)
		left join settings_tree on (graph_tree_items.id=settings_tree.graph_tree_item_id)
		left join host on (graph_tree_items.host_id=host.id)
		where graph_tree_id='".$tree['id']."'
		order by graph_tree_items.order_key");
		
	$i = 0;
	$tier_p="";
    $final_prec=0;
	$tt="";
	$parent="";
	if (sizeof($heirarchy) > 0) {
	  foreach ($heirarchy as $leaf) {
	    if ($final_prec == 1) {
	      $tt="";
	    }

	    $tier = tree_tier($leaf["order_key"]);
	    if ($leaf["title"] != "") {
		  $current_leaf_type = "heading";
	    } elseif (!empty($leaf["local_graph_id"])) {
		  $current_leaf_type = "graph";
	    } else {
		  $current_leaf_type = "host";
	    }
	    $final=0;
	    if ((($current_leaf_type == 'heading') || ($current_leaf_type == 'host')) && (($tier <= $hide_until_tier) || ($hide_until_tier == false))) {
		  $current_title = (($current_leaf_type == "heading") ? $leaf["title"] : $leaf["hostname"]);

	      $s="<img src=images/themes/".$theme."/show.png border=0 align=middle name=B".$leaf["id"].">";
          $style=$expand_style_no;
          if ($expand_level >= $tier) {
            $style=$expand_style_yes;
			$s="<img src=images/themes/".$theme."/hide.png border=0 align=middle name=B".$leaf["id"].">";
          }
		  
		  if ( (!isset($plus[$leaf["id"]]) || ($plus[$leaf["id"]] == "*") ) ) {
		    $s="<img src=images/themes/".$theme."/final.png border=0 align=middle>";
		    $final=1;
		  }
		
  		  if (isset($_REQUEST["simple"])) {
		    $tmp=db_fetch_cell("select value from settings where name='manage_list_2_".$user_id."'");
		    $l="?simple=".$tmp;
	      }
		  if (isset($_REQUEST["err"])) {
		    $l.="&err=".$_REQUEST["err"];
	      }
		  if (isset($_REQUEST["manage_sound_enable"])) {
		    $l.="&manage_sound_enable=".$_REQUEST["manage_sound_enable"];
	      }
		  $l.="&forceids=1&group=".$leaf["id"]."&site=-1";
		
          if ($tier <= $tier_p) {
            for ($counter=0;$counter<($tier_p - $tier);$counter++) {
              print "</div>";
            }
          }
		  
if (isset($v3[$leaf["id"]])) {

		  $cc=manage_sid($v3[$leaf["id"]]);
		  //0.6.2
          if ($cc != "none") {
            print "<br>";


            $img=array("");
		    $img[0]="";
            for ($counter=1;$counter<$tier;$counter++) {
              $img[$counter]="<img src=images/themes/".$theme."/tree2.png border=0 align=middle>";
            }
		    $img[$tier-1]="<img src=images/themes/".$theme."/tree1.png border=0 align=middle>";
		  
		    for ($counter=1;$counter<$tier;$counter++) {
              print $img[$counter]." ";
            }
			
            if ($final != 1) {
		      ?>
              <a class="expandable" href="#" onclick="toggleVisibility('texte_s_<?php print $leaf["id"]?>');return toggle(this,'B<?php print $leaf["id"]?>')"><?php print $s; ?></a>
              <?php
            } else {
              print $s." ";
            }
			
			
          $nbtreeerr=0;
          $msg=manage_over($v3[$leaf["id"]],$theme,$theme_line,$nbtreeerr,$nbt);
		  
		  $right_click=0;
		  if (isset($plus[$leaf["id"]])) {
		    if ($plus[$leaf["id"]] == "*") {
			  $dev = db_fetch_assoc("SELECT host.description, host.notes, manage_host.mail FROM host, manage_host where host.id=manage_host.id and host.id='".$v3[$leaf["id"]]."'");
			  if (sizeof($dev) > 0) {
			    $right_click=1;
			    foreach ($dev as $d) {
			      $descr=$d["description"];
				  $notes=$d['notes'];
				  $private_alert_address=$d['mail'];
			    }
                $global_alert_address = db_fetch_cell("select value from settings where name='manage_global_email'");
                $alert_address=manage_mail($private_alert_address, $global_alert_address);
		        manage_overlib_rightclick($v3[$leaf["id"]],$descr,$cc,$alert_address,$notes, $theme);
		      }
			}
		  }

		  if ($right_click == 0) {
		    print '<img src=images/themes/'.$theme.'/'.$cc.'.png border=0 align=middle>';
		  }
		  
          if ( ($cc == "prob") || ($cc == "down") ) {
            print ' <a href=' . $config["url_path"] . 'plugins/manage/manage.php'.$l.' onMouseOver="return overlib(\''.$msg.'\', HAUTO, OFFSETY, 5, WRAP)" onMouseOut="nd();"><font color='.$theme_line[5].'><b>'.$current_title."</b></a> (".$nbtreeerr."/".$np[$leaf["id"]].")</font>";
          }
          if ($cc == "up") {
            print ' <a href=' . $config["url_path"] . 'plugins/manage/manage.php'.$l.'><font color='.$theme_line[5].'><b>'.$current_title."</b></a> (".$nbtreeerr."/".$np[$leaf["id"]].")</font>";
          }
		  
		  //0.6.2
          //if ($cc == "none") {
            //print ' <font color='.$theme_line[5].'><b>'.$current_title."</b> (0/0)</font>";
          //}
			
		  }
		  
		  
}
// else {
//  print '<img src=images/themes/'.$theme.'/none.png border=0 align=middle>';
//  print ' <font color='.$theme_line[5].'><b>'.$current_title."</b> (0/0)</font>";
//}

          if ($final != 1) {
?>
<div id="texte_s_<?php print $leaf["id"]?>" style="<?php print $style?>">
<?php
          }

          $tier_p=$tier;

	    } else {
	      $tt=$leaf["local_graph_id"].";".$tt;
	      $v2[$parent] = $tt;
	    }
        $i++;

	    $final_prec=$final;
	  
	  }  //end foreach
    }
	print "</td>";
	$current_line++;
  }  //end foreach tree
  $return="xxx";
}

//a recursive function for tree view
function manage_replace($id, $v) {
  $tmp = explode(";",$v[$id]);
  foreach ($tmp as $pid) {
    $pos = strrpos($pid, "*");
    if ($pos === false) {
//
    } else {
	  $nn=substr($pid, 1);
      $n=manage_replace($nn, $v);
	  if ($n != "") {
        $v[$id]=str_replace("*".$nn, $n, $v[$id]);
	  } else {
        $v[$id]=str_replace("*".$nn, "-1", $v[$id]);
	  }
    }
  }
  return $v[$id];
}


//display overlib image for tree view
function manage_over($id, $theme,$theme_line,&$nbtreeerr,&$nbt) {
  $output="";
  if ($id != "") {
    $sids = explode(";", $id);
    for ($counter=0;$counter<count($sids);$counter++) {
	  if ($sids[$counter] != "") {
        $sql = db_fetch_assoc("select `statut`,`description`,`hostname` from manage_host,host where manage_host.id=host.id and host.manage='on' and host.disabled<>'on' and manage_host.id=".$sids[$counter]);
        foreach ($sql as $device) {
          if ( ($device['statut'] == "prob") || ($device['statut'] == "down") ) {
		    $nbtreeerr++;
		    $nbt++;
	        $output .= "<tr><td><img src=images/themes/".$theme."/".$device['statut'].".png border=0></td><td><font color=".$theme_line[4].">".$device['description']." (".$device['hostname'].")<br>";
          }
        }
	  }
    }
  }
  return $output;
}

		
?>
