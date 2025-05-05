<?php

chdir('../../');

include_once("./include/auth.php");

include_once("./include/global.php");

global $config;

include_once($config["library_path"] . "/database.php");

?>
<html>
<head>
	<title>Cacti</title>
	<link href="<?php print $config['url_path']; ?>include/main.css" rel="stylesheet">
	<link href="<?php print $config['url_path']; ?>images/favicon.ico" rel="shortcut icon"/>
	<script type="text/javascript" src="<?php print $config['url_path']; ?>include/layout.js"></script>
	</style>
</head>

<body>

<?php
if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"] = "";
}

switch ($_REQUEST["action"]) {
  case 'actions':
	manage_settings_save();
	break;

  default:
	manage_settings_edit();
	break;
}



function manage_settings_save() {
  print "<b>Saved.</b>";
  
  foreach ($_GET as $var => $val) {
    if (ereg("manage_", $var)) {
//print $var." ".$val."<br>";
      db_execute("DELETE FROM `settings` WHERE `name` like '".$var."'");
	  db_execute("INSERT INTO `settings` VALUES ('".$var."', '".$val."')");
    }
  }
  
  manage_settings_edit();
}

function manage_settings_edit() {
  global $colors, $config;

  if (isset($_GET["id"])) {
    $user_id=db_fetch_cell("select id from user_auth where id=" .$_GET["id"]);
    $user_name=db_fetch_cell("select username from user_auth where id=" .$_GET["id"]);
  } else {
    $user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);
    $user_name=db_fetch_cell("select username from user_auth where id=" .$_SESSION["sess_user_id"]);
  }
  
  $manage_size=manage_load_default("manage_size", $user_id, "999");
  $manage_thold=manage_load_default("manage_thold", $user_id, "999");
  $manage_list=manage_load_default("manage_list", $user_id, "999");
  $manage_list_2=manage_load_default("manage_list_2", $user_id, "999");
  $manage_expand=manage_load_default("manage_expand", $user_id, "999");
  $manage_theme=manage_load_default("manage_theme", $user_id, "999");
  $manage_sound=manage_load_default("manage_sound", $user_id, "");
  $manage_list_separator=manage_load_default("manage_list_separator", $user_id, "Is not set");
  $manage_full_separator=manage_load_default("manage_full_separator", $user_id, "Is not set");
  $manage_simple_separator=manage_load_default("manage_simple_separator", $user_id, "Is not set");
  $manage_site_separator=manage_load_default("manage_site_separator", $user_id, "Is not set");
  $manage_tree_separator=manage_load_default("manage_tree_separator", $user_id, "Is not set");
  $manage_legend=manage_load_default("manage_legend", $user_id, "");
  $manage_cycle_delay=manage_load_default("manage_cycle_delay", $user_id, "Is not set");
  $manage_cycle_refresh=manage_load_default("manage_cycle_refresh", $user_id, "Is not set");
  $manage_order1=manage_load_default("manage_order1", $user_id, "999");
  $manage_order2=manage_load_default("manage_order2", $user_id, "999");
  $manage_order3=manage_load_default("manage_order3", $user_id, "999");
  $manage_order4=manage_load_default("manage_order4", $user_id, "999");
  $manage_order5=manage_load_default("manage_order5", $user_id, "999");
  $manage_link_method=manage_load_default("manage_link_method", $user_id, "999");
  $manage_connect_timeout=manage_load_default("manage_connect_timeout", $user_id, "Is not set");
  $manage_relay_ip=manage_load_default("manage_relay_ip", $user_id, "Is not set");
  $manage_relay_port=manage_load_default("manage_relay_port", $user_id, "Is not set");
  $manage_motd_enabled=manage_load_default("manage_motd_enabled", $user_id, "999");
  $manage_motd_style=manage_load_default("manage_motd_style", $user_id, "999");

  html_start_box("<strong><font color='#FFFFFF'>Manage Settings for user '".$user_name."' :</font></strong>", "98%", $colors["header"], "1", "center", "");

  print '<form name="form_devices">';

  if (isset($_GET["id"])) {
    print "<INPUT type='hidden' name='id' value='".$_GET["id"]."'>";
  }
  
  ?>
  <tr bgcolor="#6D88AD"><td><font color='#FFFFFF'><b>General Options</b></font></td><td><font color='#FFFFFF'></font></td></tr>
  <?php


  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Tab size</b><br>88px is default.</font></td><td>
  <?php
  print "<select name='manage_size_".$user_id."'>";

  print "<option value='999'";
    if ($manage_size=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='88'";
    if ($manage_size=="88") {
	  print " selected";
	}
  print ">88</option>";
  print "<option value='44'";
    if ($manage_size=="44") {
	  print " selected";
	}
  print ">44</option>";
  print "<option value='22'";
    if ($manage_size=="22") {
	  print " selected";
	}
  print ">22</option>";
  print "<option value='22b'";
    if ($manage_size=="922") {
	  print " selected";
	}
  print ">22 bis</option>";
  print "<option value='22t'";
    if ($manage_size=="822") {
	  print " selected";
	}
  print ">22 ter</option>";

  print "</select></td>";

  
  
  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Check thresholds</b><br>Blink hostname when a threshold is breaked.<br>This need Thold Plugin and Firefox.</font></td><td>
  <?php
  print "<select name='manage_thold_".$user_id."'>";

  print "<option value='999'";
    if ($manage_thold=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='0'";
    if ($manage_thold=="0") {
	  print " selected";
	}
  print ">No</option>";
  print "<option value='1'";
    if ($manage_thold=="1") {
	  print " selected";
	}
  print ">Yes</option>";

  print "</select></td>";
  
  
  
  
  
  
  
   //    $manage_disable_site_view = db_fetch_cell("select value from settings where name ='manage_disable_site_view'");
    $result = mysql_query("select value from settings where name ='manage_disable_site_view'");
    $row_arrayCde = mysql_fetch_row($result);
    $manage_disable_site_view=$row_arrayCde[0];	
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Default view</b><br></font></td><td>
  <?php
  print "<select name='manage_list_".$user_id."'>";

  print "<option value='999'";
    if ($manage_list=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='4'";
    if ($manage_list=="4") {
	  print " selected";
	}
  print ">Tree</option>";

  if ($manage_disable_site_view == "0") {  
    print "<option value='3'";
    if ($manage_list=="3") {
	  print " selected";
	}
  print ">Site</option>";
  }
  
  print "<option value='2'";
    if ($manage_list=="2") {
	  print " selected";
	}
  print ">List</option>";
  print "<option value='1'";
    if ($manage_list=="1") {
	  print " selected";
	}
  print ">Simple</option>";
  print "<option value='0'";
    if ($manage_list=="0") {
	  print " selected";
	}
  print ">Full</option>";

  print "</select></td>";
  
  
  
  
  
 
  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Secondary view</font></td><td>
  <?php
  print "<select name='manage_list_2_".$user_id."'>";

  print "<option value='999'";
    if ($manage_list_2=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='2'";
    if ($manage_list_2=="2") {
	  print " selected";
	}
  print ">List</option>";
  print "<option value='1'";
    if ($manage_list_2=="1") {
	  print " selected";
	}
  print ">Simple</option>";
  print "<option value='0'";
    if ($manage_list_2=="0") {
	  print " selected";
	}
  print ">Full</option>";

  print "</select></td>";
  
  
  
  
  
  
  
  
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Auto-expand view</b><br>For 'Site' and 'Tree Mode' views.</font></td><td>
  <?php
  print "<select name='manage_expand_".$user_id."'>";

  print "<option value='999'";
    if ($manage_expand=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='0'";
    if ($manage_expand=="0") {
	  print " selected";
	}
  print ">No</option>";
  print "<option value='9'";
    if ($manage_expand=="9") {
	  print " selected";
	}
  print ">All levels</option>";
  print "<option value='1'";
    if ($manage_expand=="1") {
	  print " selected";
	}
  print ">1 level</option>";
  print "<option value='2'";
    if ($manage_expand=="2") {
	  print " selected";
	}
  print ">2 levels</option>";
  print "<option value='3'";
    if ($manage_expand=="3") {
	  print " selected";
	}
  print ">3 levels</option>";
  print "<option value='4'";
    if ($manage_expand=="4") {
	  print " selected";
	}
  print ">4 levels</option>";
  print "<option value='5'";
    if ($manage_expand=="5") {
	  print " selected";
	}
  print ">5 levels</option>";

  print "</select></td>";
  
  
  
  
  
  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Theme for images</b><br>Create a folder with your images in '<cacti>/plugins/manage/images/themes folder'.</font></td><td>
  <?php
  print "<select name='manage_theme_".$user_id."'>";
  
	print "<option value='999'";
    if ($manage_theme=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
	print "<option value='none'>None</option>";
	$p = dirname(__FILE__) . "/images/themes";
	$dir=opendir($p);
	while($file=readdir($dir)) {
		if ($file!="." and $file!="..") {
			if (is_dir($p."/".$file)) {
				print "<option value='".$file."'";
    if ($manage_theme==$file) {
	  print " selected";
	}
  print ">".$file."</option>";
			}
		}
	}
	closedir($dir);
	
  print "</select></td>";
  

  
  
  
  
  
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Use Sound</b><br>Use sound from theme.</font></td><td>
  <?php
  
  print "<INPUT type='hidden' name='manage_sound_".$user_id."' value=''>";
  
  print "<INPUT type='checkbox' name='manage_sound_".$user_id."'";
    if ($manage_sound=="on") {
	  print " checked";
	}
  print "></td>";
  
 
  

  
  
  
  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>List view separator</b><br>25 is default.</font></td><td>
  <?php
  
  print "<INPUT type='text' value='";
  if ($manage_list_separator=="") {
    $manage_list_separator="Is not set";
  }
  print $manage_list_separator."' name='manage_list_separator_".$user_id."'></td>";

  
  
  
  
  
  
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Hosts per line in Full view</b><br>6 is default.</font></td><td>
  <?php
  
  print "<INPUT type='text' value='";
  if ($manage_full_separator=="") {
    $manage_full_separator="Is not set";
  }
  print $manage_full_separator."' name='manage_full_separator_".$user_id."'></td>";
  
 
  

  
  
  
  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Hosts per line in Simple view</b><br>12 is default.</font></td><td>
  <?php
  
  print "<INPUT type='text' value='";
  if ($manage_simple_separator=="") {
    $manage_simple_separator="Is not set";
  }
  print $manage_simple_separator."' name='manage_simple_separator_".$user_id."'></td>";

  

  
  
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Sites per line in Site view</b><br>10 is default.</font></td><td>
  <?php
  
  print "<INPUT type='text' value='";
  if ($manage_site_separator=="") {
    $manage_site_separator="Is not set";
  }
  print $manage_site_separator."' name='manage_site_separator_".$user_id."'></td>";
  
 
  

  
  
  
  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Trees per line in Tree view</b><br>3 is default.</font></td><td>
  <?php
  
  print "<INPUT type='text' value='";
  if ($manage_tree_separator=="") {
    $manage_tree_separator="Is not set";
  }
  print $manage_tree_separator."' name='manage_tree_separator_".$user_id."'></td>";

  
  

  
  
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Show legend</b><br>Check this to show a legend.</font></td><td>
  <?php

  print "<INPUT type='hidden' name='manage_legend_".$user_id."' value=''>";
  
  print "<INPUT type='checkbox' name='manage_legend_".$user_id."'";
    if ($manage_legend=="on") {
	  print " checked";
	}
  print "></td>";
  
  

  
  
  
  
  ?>
  <tr bgcolor="#6D88AD"><td><font color='#FFFFFF'><b>Cycle Options</b></font></td><td><font color='#FFFFFF'></font></td></tr>
  <?php
  

  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Delay Interval</b><br>This is the time in seconds before the next group is displayed (1 - 300).<br>30 is default.</font></td><td>
  <?php
  
  print "<INPUT type='text' value='";
  if ($manage_cycle_delay=="") {
    $manage_cycle_delay="Is not set";
  }
  print $manage_cycle_delay."' name='manage_cycle_delay_".$user_id."'></td>";

  
  

  
  
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Delay Interval</b><br>This is the time in seconds before the view is refreshed (1 - 300).<br>5 is default.</font></td><td>
  <?php
  
  print "<INPUT type='text' value='";
  if ($manage_cycle_refresh=="") {
    $manage_cycle_refresh="Is not set";
  }
  print $manage_cycle_refresh."' name='manage_cycle_refresh_".$user_id."'></td>";
  
  







  ?>
  <tr bgcolor="#6D88AD"><td><font color='#FFFFFF'><b>Orders Options</b></font></td><td><font color='#FFFFFF'></font></td></tr>
  <?php
  

  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>1st sort order</b></td><td>
  <?php
  print "<select name='manage_order1_".$user_id."'>";

  print "<option value='999'";
    if ($manage_order1=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='1'";
    if ($manage_order1=="1") {
	  print " selected";
	}
  print ">Site name (ascendant)</option>";
  print "<option value='11'";
    if ($manage_order1=="11") {
	  print " selected";
	}
  print ">Site name (descendant)</option>";
  print "<option value='2'";
    if ($manage_order1=="2") {
	  print " selected";
	}
  print ">Group name (ascendant)</option>";
  print "<option value='12'";
    if ($manage_order1=="12") {
	  print " selected";
	}
  print ">Group name (descendant)</option>";
  print "<option value='3'";
    if ($manage_order1=="3") {
	  print " selected";
	}
  print ">Description (ascendant)</option>";
  print "<option value='13'";
    if ($manage_order1=="13") {
	  print " selected";
	}
  print ">Description (descendant)</option>";
  print "<option value='4'";
    if ($manage_order1=="4") {
	  print " selected";
	}
  print ">Hostname (ascendant)</option>";
  print "<option value='14'";
    if ($manage_order1=="14") {
	  print " selected";
	}
  print ">Hostname (descendant)</option>";
  print "<option value='5'";
    if ($manage_order1=="5") {
	  print " selected";
	}
  print ">Status (ascendant)</option>";
  print "<option value='15'";
    if ($manage_order1=="15") {
	  print " selected";
	}
  print ">Status (descendant)</option>";
  print "<option value='6'";
    if ($manage_order1=="6") {
	  print " selected";
	}
  print ">Uptime (ascendant)</option>";
  print "<option value='16'";
    if ($manage_order1=="16") {
	  print " selected";
	}
  print ">Uptime (descendant)</option>";
  print "<option value='0'";
    if ($manage_order1=="0") {
	  print " selected";
	}
  print ">None</option>";

  print "</select></td>";






  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>2nd sort order</b><br></font></td><td>
  <?php
  print "<select name='manage_order2_".$user_id."'>";

  print "<option value='999'";
    if ($manage_order2=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='1'";
    if ($manage_order2=="1") {
	  print " selected";
	}
  print ">Site name (ascendant)</option>";
  print "<option value='11'";
    if ($manage_order2=="11") {
	  print " selected";
	}
  print ">Site name (descendant)</option>";
  print "<option value='2'";
    if ($manage_order2=="2") {
	  print " selected";
	}
  print ">Group name (ascendant)</option>";
  print "<option value='12'";
    if ($manage_order2=="12") {
	  print " selected";
	}
  print ">Group name (descendant)</option>";
  print "<option value='3'";
    if ($manage_order2=="3") {
	  print " selected";
	}
  print ">Description (ascendant)</option>";
  print "<option value='13'";
    if ($manage_order2=="13") {
	  print " selected";
	}
  print ">Description (descendant)</option>";
  print "<option value='4'";
    if ($manage_order2=="4") {
	  print " selected";
	}
  print ">Hostname (ascendant)</option>";
  print "<option value='14'";
    if ($manage_order2=="14") {
	  print " selected";
	}
  print ">Hostname (descendant)</option>";
  print "<option value='5'";
    if ($manage_order2=="5") {
	  print " selected";
	}
  print ">Statut (ascendant)</option>";
  print "<option value='15'";
    if ($manage_order2=="15") {
	  print " selected";
	}
  print ">Statut (descendant)</option>";
  print "<option value='6'";
    if ($manage_order2=="6") {
	  print " selected";
	}
  print ">Uptime (ascendant)</option>";
  print "<option value='16'";
    if ($manage_order2=="16") {
	  print " selected";
	}
  print ">Uptime (descendant)</option>";
  print "<option value='0'";
    if ($manage_order2=="0") {
	  print " selected";
	}
  print ">None</option>";

  print "</select></td>";









  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>3rd sort order</b><br></font></td><td>
  <?php
  print "<select name='manage_order3_".$user_id."'>";

  print "<option value='999'";
    if ($manage_order3=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='1'";
    if ($manage_order3=="1") {
	  print " selected";
	}
  print ">Site name (ascendant)</option>";
  print "<option value='11'";
    if ($manage_order3=="11") {
	  print " selected";
	}
  print ">Site name (descendant)</option>";
  print "<option value='2'";
    if ($manage_order3=="2") {
	  print " selected";
	}
  print ">Group name (ascendant)</option>";
  print "<option value='12'";
    if ($manage_order3=="12") {
	  print " selected";
	}
  print ">Group name (descendant)</option>";
  print "<option value='3'";
    if ($manage_order3=="3") {
	  print " selected";
	}
  print ">Description (ascendant)</option>";
  print "<option value='13'";
    if ($manage_order3=="13") {
	  print " selected";
	}
  print ">Description (descendant)</option>";
  print "<option value='4'";
    if ($manage_order3=="4") {
	  print " selected";
	}
  print ">Hostname (ascendant)</option>";
  print "<option value='14'";
    if ($manage_order3=="14") {
	  print " selected";
	}
  print ">Hostname (descendant)</option>";
  print "<option value='5'";
    if ($manage_order3=="5") {
	  print " selected";
	}
  print ">Statut (ascendant)</option>";
  print "<option value='15'";
    if ($manage_order3=="15") {
	  print " selected";
	}
  print ">Statut (descendant)</option>";
  print "<option value='6'";
    if ($manage_order3=="6") {
	  print " selected";
	}
  print ">Uptime (ascendant)</option>";
  print "<option value='16'";
    if ($manage_order3=="16") {
	  print " selected";
	}
  print ">Uptime (descendant)</option>";
  print "<option value='0'";
    if ($manage_order3=="0") {
	  print " selected";
	}
  print ">None</option>";

  print "</select></td>";








  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>4th sort order</b><br></font></td><td>
  <?php
  print "<select name='manage_order4_".$user_id."'>";

  print "<option value='999'";
    if ($manage_order4=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='1'";
    if ($manage_order4=="1") {
	  print " selected";
	}
  print ">Site name (ascendant)</option>";
  print "<option value='11'";
    if ($manage_order4=="11") {
	  print " selected";
	}
  print ">Site name (descendant)</option>";
  print "<option value='2'";
    if ($manage_order4=="2") {
	  print " selected";
	}
  print ">Group name (ascendant)</option>";
  print "<option value='12'";
    if ($manage_order4=="12") {
	  print " selected";
	}
  print ">Group name (descendant)</option>";
  print "<option value='3'";
    if ($manage_order4=="3") {
	  print " selected";
	}
  print ">Description (ascendant)</option>";
  print "<option value='13'";
    if ($manage_order4=="13") {
	  print " selected";
	}
  print ">Description (descendant)</option>";
  print "<option value='4'";
    if ($manage_order4=="4") {
	  print " selected";
	}
  print ">Hostname (ascendant)</option>";
  print "<option value='14'";
    if ($manage_order4=="14") {
	  print " selected";
	}
  print ">Hostname (descendant)</option>";
  print "<option value='5'";
    if ($manage_order4=="5") {
	  print " selected";
	}
  print ">Statut (ascendant)</option>";
  print "<option value='15'";
    if ($manage_order4=="15") {
	  print " selected";
	}
  print ">Statut (descendant)</option>";
  print "<option value='6'";
    if ($manage_order4=="6") {
	  print " selected";
	}
  print ">Uptime (ascendant)</option>";
  print "<option value='16'";
    if ($manage_order4=="16") {
	  print " selected";
	}
  print ">Uptime (descendant)</option>";
  print "<option value='0'";
    if ($manage_order4=="0") {
	  print " selected";
	}
  print ">None</option>";

  print "</select></td>";







  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>5th sort order</b><br></font></td><td>
  <?php
  print "<select name='manage_order5_".$user_id."'>";

  print "<option value='999'";
    if ($manage_order5=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='1'";
    if ($manage_order5=="1") {
	  print " selected";
	}
  print ">Site name (ascendant)</option>";
  print "<option value='11'";
    if ($manage_order5=="11") {
	  print " selected";
	}
  print ">Site name (descendant)</option>";
  print "<option value='2'";
    if ($manage_order5=="2") {
	  print " selected";
	}
  print ">Group name (ascendant)</option>";
  print "<option value='12'";
    if ($manage_order5=="12") {
	  print " selected";
	}
  print ">Group name (descendant)</option>";
  print "<option value='3'";
    if ($manage_order5=="3") {
	  print " selected";
	}
  print ">Description (ascendant)</option>";
  print "<option value='13'";
    if ($manage_order5=="13") {
	  print " selected";
	}
  print ">Description (descendant)</option>";
  print "<option value='4'";
    if ($manage_order5=="4") {
	  print " selected";
	}
  print ">Hostname (ascendant)</option>";
  print "<option value='14'";
    if ($manage_order5=="14") {
	  print " selected";
	}
  print ">Hostname (descendant)</option>";
  print "<option value='5'";
    if ($manage_order5=="5") {
	  print " selected";
	}
  print ">Statut (ascendant)</option>";
  print "<option value='15'";
    if ($manage_order5=="15") {
	  print " selected";
	}
  print ">Statut (descendant)</option>";
  print "<option value='6'";
    if ($manage_order5=="6") {
	  print " selected";
	}
  print ">Uptime (ascendant)</option>";
  print "<option value='16'";
    if ($manage_order5=="16") {
	  print " selected";
	}
  print ">Uptime (descendant)</option>";
  print "<option value='0'";
    if ($manage_order5=="0") {
	  print " selected";
	}
  print ">None</option>";

  print "</select></td>";




  
  
  
  
  
  
  ?>
  <tr bgcolor="#6D88AD"><td><font color='#FFFFFF'><b>External links Options</b></font></td><td><font color='#FFFFFF'></font></td></tr>
  <?php
  






  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>URL external link</b><br></font>URI scheme files <a href=<?php print $config['url_path']; ?>plugins/manage/ressources/urischeme/urischeme.zip>download</a> for Internet Explorer/Firefox (Windows only).
				<br>Java <a href=http://www.java.com/en/download/manual.jsp target="_blank">download</a>.
				<br>Due to several java security restrictions, it is not possible to open a telnet/ssh connection to any other machine then the webserver.
				<br>A <a href=<?php print $config['url_path']; ?>plugins/manage/ressources/relay/relay.zip>port forwarding program</a> need to be used.</td><td>
  <?php
  print "<select name='manage_link_method_".$user_id."'>";

  print "<option value='999'";
    if ($manage_link_method=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='0'";
    if ($manage_link_method=="0") {
	  print " selected";
	}
  print ">URI scheme (Windows only)</option>";
  print "<option value='1'";
    if ($manage_link_method=="1") {
	  print " selected";
	}
  print ">Java</option>";
  print "<option value='9'";
    if ($manage_link_method=="9") {
	  print " selected";
	}
  print ">None</option>";

  print "</select></td>";  









  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Timeout for Overlib</b><br></font>Time is in second. 2 is default.</td><td>
  <?php
  print "<INPUT type='text' value='";
  if ($manage_connect_timeout=="") {
    $manage_connect_timeout="Is not set";
  }
  print $manage_connect_timeout."' name='manage_connect_timeout_".$user_id."'></td>";

























  
  
  ?>
  <tr bgcolor="#6D88AD"><td><font color='#FFFFFF'><b>Relay daemon Options for Java</b></font></td><td><font color='#FFFFFF'></font></td></tr>
  <?php
  






  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>Relay daemon ip</b><br>Client IP is default IP (currently : <?php print $_SERVER["REMOTE_ADDR"]; ?>).</td><td>
  <?php
  print "<INPUT type='text' value='";
  if ($manage_relay_ip=="") {
    $manage_relay_ip="Is not set";
  }
  print $manage_relay_ip."' name='manage_relay_ip_".$user_id."'></td>";









  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Relay daemon port</b><br></font>31415 is default port.</td><td>
  <?php
  print "<INPUT type='text' value='";
  if ($manage_relay_port=="") {
    $manage_relay_port="Is not set";
  }
  print $manage_relay_port."' name='manage_relay_port_".$user_id."'></td>";



		
  
  
  
  
  
  
  
  
  
  

  ?>
  <tr bgcolor="#6D88AD"><td><font color='#FFFFFF'><b>Message of the Day Options</b></font></td><td><font color='#FFFFFF'></font></td></tr>
  <?php
  






  ?>
  <tr bgcolor="<?php print $colors["panel"];?>"><td><font color='#000000'><b>MotD enabled</td><td>
  <?php
  print "<select name='manage_motd_enabled_".$user_id."'>";

  print "<option value='999'";
    if ($manage_motd_enabled=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='3'";
    if ($manage_motd_enabled=="3") {
	  print " selected";
	}
  print ">No</option>";
  print "<option value='1'";
    if ($manage_motd_enabled=="1") {
	  print " selected";
	}
  print ">Yes</option>";
  print "<option value='2'";
    if ($manage_motd_enabled=="2") {
	  print " selected";
	}
  print ">Display only errors</option>";

  print "</select></td>";  









  ?>
  <tr bgcolor="#F5F5F5"><td><font color='#000000'><b>Style</b><br></font></td><td>
  <?php
  print "<select name='manage_motd_style_".$user_id."'>";

  print "<option value='999'";
    if ($manage_motd_style=="999") {
	  print " selected";
	}
  print ">Is not set</option>";
  print "<option value='1'";
    if ($manage_motd_style=="1") {
	  print " selected";
	}
  print ">Console</option>";
  print "<option value='2'";
    if ($manage_motd_style=="2") {
	  print " selected";
	}
  print ">Popup</option>";

  print "</select></td>"; 

  
  
  
  
  ?>
  </tr>
  <?php
  html_end_box();

  $ds_actions = array(
	1 => "Save"
  );

  draw_actions_dropdown($ds_actions);

  print "</td></tr></table>";


}




  

function manage_load_default($value, $user_id, $default_value) {
  $result=db_fetch_cell("select value from settings where name like '".$value."_".$user_id."'");
  if ($result == "") {
    $result=$default_value;
  }
  return $result;
}



?>
