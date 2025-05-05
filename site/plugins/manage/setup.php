<?php

// wrapper for deprecated mysql functions
include_once('mysql.php');
global $database_default, $database_type, $database_port, $database_password, $database_username, $database_hostname;
$cpmdb = mysql_connect($database_hostname, $database_username, $database_password);
mysql_select_db($database_default,$cpmdb);


//important
//no protection, no code, only functions

//$p = dirname(__FILE__);
//include_once($p . '/manage_lib.php');
include_once('manage_lib.php');


function plugin_manage_check_config () {
//called after install
//if return true, plugin will be installed but disabled
//if return false, plugin will be waiting configuration
  return true;
}


//compatibility for plugin update
function manage_version () {
  return plugin_manage_version ();
}

function plugin_manage_version () {
  return array( 	'name' 		=> 'PHP Network Managing',
					'homepage'	=> 'http://forums.cacti.net/viewtopic.php?t=13827',
					'author'	=> 'Gilles Boulon',
					'version' 	=> '0.6.2',
					'longname'	=> 'PHP Network Managing',
					'email'		=> 'http://forums.cacti.net/privmsg.php?mode=post&u=4597',
					'url'       => 'http://gilles.boulon.free.fr/manage/versions.php'
  );
}

function plugin_manage_install () {

  manage_setup_tables();
  
  api_plugin_register_realm ('manage', 'manage.php', 'Use PHP Network Managing', 1);            //enable manage for admin
  
  api_plugin_register_hook ('manage', 'config_arrays', 'plugin_manage_config_arrays', 'setup.php');
  api_plugin_register_hook ('manage', 'top_header_tabs', 'manage_show_tab', 'setup.php');
  api_plugin_register_hook ('manage', 'top_graph_header_tabs', 'manage_show_tab', 'setup.php');
  api_plugin_register_hook ('manage', 'draw_navigation_text', 'manage_draw_navigation_text', 'setup.php');
  api_plugin_register_hook ('manage', 'config_form', 'manage_config_form', 'setup.php');
  api_plugin_register_hook ('manage', 'api_device_save', 'manage_api_device_save', 'setup.php');
  api_plugin_register_hook ('manage', 'device_action_array', 'manage_device_action_array', 'setup.php');
  api_plugin_register_hook ('manage', 'top_graph_refresh', 'manage_graph_refresh', 'setup.php');
  api_plugin_register_hook ('manage', 'device_action_execute', 'manage_device_action_execute', 'setup.php');
  api_plugin_register_hook ('manage', 'device_action_prepare', 'manage_device_action_prepare', 'setup.php');
  api_plugin_register_hook ('manage', 'config_settings', 'manage_config_settings', 'setup.php');
  api_plugin_register_hook ('manage', 'console_after', 'manage_console_after', 'setup.php');
  api_plugin_register_hook ('manage', 'poller_bottom', 'manage_poller_bottom', 'setup.php');
  api_plugin_register_hook ('manage', 'poller_top', 'manage_poller_top', 'setup.php');
  api_plugin_register_hook ('manage', 'poller_output', 'manage_poller_output', 'setup.php');
  api_plugin_register_hook ('manage', 'user_admin_tab', 'manage_user_admin_tab', 'setup.php');
  api_plugin_register_hook ('manage', 'host_edit_top', 'manage_host_edit_top', 'setup.php');
 
}


function plugin_manage_uninstall () {
  db_execute("DROP TABLE `manage_host`");
  db_execute("DROP TABLE `manage_method`");
  db_execute("DROP TABLE `manage_alerts`");
  db_execute("DROP TABLE `manage_tcp`");
  db_execute("DROP TABLE `manage_device_type`");
  db_execute("DROP TABLE `manage_templates`");
  db_execute("DROP TABLE `manage_groups`");
  db_execute("DROP TABLE `manage_services`");
  db_execute("DROP TABLE `manage_process`");
  db_execute("DROP TABLE `manage_poller_output`");
  db_execute("DROP TABLE `manage_sites`");
  db_execute("DROP TABLE `manage_host_services`");
  db_execute("DROP TABLE `manage_admin_link`");
  db_execute("ALTER TABLE `host` DROP `manage`");
  db_execute("DELETE FROM `settings` WHERE `name` like 'manage\_%'");
  db_execute("DELETE FROM `plugin_update_info` WHERE `plugin` = 'manage'");
  
  api_plugin_remove_realms ('manage');
}


function manage_host_edit_top($host_arr) {
  if (isset($_GET['id'])) {
    $link=db_fetch_cell("SELECT data FROM manage_admin_link where id='".$_GET['id']."' limit 1");
    if ($link != "") {
      print "<a href='".$link."' target='_blank'><img src='plugins/manage/images/telnet.png' border='0'> Remote administration</a>";
    }
  }
}


function manage_device_action_array($device_action_array) {
  $device_action_array['manage'] = 'Manage';
  return $device_action_array;
}

function manage_device_action_execute ($action) {

  global $config;

  $selected_items = unserialize(stripslashes($_POST["selected_items"]));

  for ($i=0; ($i < count($selected_items)); $i++) {
	input_validate_input_number($selected_items[$i]);

	if ($_POST['drp_action'] == 'manage') {

	  if ($_POST['manage_enable'] == "off") {
		db_execute("update host set manage='' where id='" . $selected_items[$i] . "'");
		db_execute("delete from manage_host where id='" . $selected_items[$i] . "'");
		db_execute("delete from manage_tcp where id='" . $selected_items[$i] . "'");
		db_execute("delete from manage_services where id='" . $selected_items[$i] . "'");
		db_execute("delete from manage_process where id='" . $selected_items[$i] . "'");
		db_execute("delete from manage_alerts where idh='" . $selected_items[$i] . "'");
      }
			
	  if ($_POST['manage_enable'] == "on") {
		db_execute("update host set `manage`='on' where id='" . $selected_items[$i] . "'");
		$is_host_here = db_fetch_cell("SELECT count(id) FROM `manage_host` where id='" . $selected_items[$i] . "'");
	    if ($is_host_here == "0") {
	      db_execute("INSERT INTO manage_host ( id , uptime, `statut`, `group`, thresold, thresold_ref, mail ) VALUES ('" . $selected_items[$i] . "', '0', 'nopoll', '0', '0', '1', '')");
	    }
      }
			
	  if ($_POST['manage_group'] != "0") {
	    db_execute("update manage_host set `group`='".$_POST['manage_group']."' where id='" . $selected_items[$i] . "'");
	  }

      if ($_POST['manage_type'] != "none") {
	    db_execute("update manage_host set `type`='".$_POST['manage_type']."' where id='" . $selected_items[$i] . "'");
	  }

      if ($_POST['manage_treshold'] != "<- host tresholding ->") {
	    db_execute("update manage_host set `thresold_ref`='".$_POST['manage_treshold']."' where id='" . $selected_items[$i] . "'");
	  }

      if ($_POST['manage_mail'] != "<- private mail ->") {
	    db_execute("update manage_host set `mail`='".$_POST['manage_mail']."' where id='" . $selected_items[$i] . "'");
	  }

      if ($_POST['manage_template'] != "0") {
	    manage_ds_g($selected_items[$i], $_POST['manage_template']);
	  }

	}
		  
  }
  return $action;

}

function manage_device_action_prepare ($save) {

  global $colors, $host_list;

  if ( ($save['drp_action'] == 'manage') && ($save['host_list'] != "") ) {
   
  	print "	<tr>
			<td colspan='12' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
				<p>To apply changes to these hosts, press the \"yes\" button below.</p>
			</td>
			</tr>";
  
	?>
	<tr bgcolor="<?php print $colors["panel"];?>">
	<td width="5"></td>
	<td width="1">
	<select name="manage_enable">
    <option selected value='0'><br><- enable Manage ->
    <option value='on'><br>Yes
    <option value='off'><br>No
    <?php
    print '</select></td>';
    ?>
	<td width="5"></td>
	<td width="1">
	<select name="manage_group">
    <?php
  
    $s=array();
    $sql3 = "SELECT manage_sites.name as `site`, manage_groups.name as `group`, manage_groups.id FROM manage_groups, manage_sites where manage_groups.site_id=manage_sites.id order by manage_sites.name, manage_groups.name";
    $result3 = mysql_query($sql3);

    ?>
    <option selected value='0'><br><- site and group ->
    <?php

    while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
      ?>
      <option value='<?php print $row3['id']; ?>'>
      <?php
      print $row3['site']." | ".$row3['group'];
    }

    print '</select></td>';
    ?>
	<td width="5"></td>
	<td width="1">
	<select name="manage_type">
    <?php

    $x = array();
	$p = dirname(__FILE__) . "/images/themes";
	
	$dir=opendir($p);
	while($file=readdir($dir)) {
	  if ($file!="." and $file!="..") {
		if (is_file($p."/".$file)) {
		  $x[$file]=$file;
		}
	  }
	}
	closedir($dir);

	?>
    <option value='none'><br><- image ->
	<?php
    foreach ($x as $xf) {
	  ?>
      <option value='<?php print $xf;?>'><br>
      <?php
	  print $xf;	
    }
  
    print "</select></td>";
    ?>
	<td width="5"></td>
	<td width="1">
	<select name="manage_template">
    <?php

    $sql3 = "SELECT * FROM manage_templates";
    $result3 = mysql_query($sql3);
    ?>
    <option value='0'><br><- template ->
    <?php
    while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
      ?>
      <option value='<?php print $row3['tcp_ports'];?>'><br><?php print $row3['name'];?>
      <?php
    }

    print "</select></td></tr><tr><td colspan='12' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'><tr>";
    ?>  
  	<td width="5"></td>
	<td width="1">
    <?php
    print "<input type='text' name='manage_treshold' size='20' value='<- host tresholding ->'></td>";
    ?>
	<td width="5"></td>
	<td width="1">
    <?php

	print "<input type='text' name='manage_mail' size='30' value='<- private mail ->'></td><tr><td colspan='12' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'><br><p>" . $save['host_list'] . "</p></td></tr>";
  }

  return $save;           //required for next hook in chain

}

function manage_config_form () {

  global $fields_host_edit,$config;
  $fields_host_edit2 = $fields_host_edit;
  $fields_host_edit3 = array();

  foreach ($fields_host_edit2 as $f => $a) {
	$fields_host_edit3[$f] = $a;
	if ($f == 'max_oids') {
      if ( (isset($_SESSION["sess_user_id"])) && (preg_match('/host.php/',$_SERVER['REQUEST_URI'] ,$matches)) && (isset($_GET['id'])) ) {
	  
    $user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);
    $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");
//print $_SESSION["sess_user_id"]."--".$manage_realm;
    $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");
    $manage_for_user = db_fetch_cell("select count(*) from user_auth_realm where user_id='".$user_id."' and realm_id='".($manage_realm+100)."'");
  
    if ( ($manage_enabled == "1") && ($manage_for_user == "1") ) {
		
        $theme = db_fetch_cell("SELECT value FROM settings where name='manage_theme_".$user_id."'");
        if ( ($theme == "") || ($theme == "999") ) {
          $theme = "default";
        }

	    if (isset($_GET['id'])) {
	      $manage_host = db_fetch_row("SELECT * FROM manage_host where id='".$_GET['id']."' limit 1");
		  $manage_host["admin_link"]=db_fetch_cell("SELECT data FROM manage_admin_link where id='".$_GET['id']."' limit 1");
		  
		  if (!isset($manage_host["statut"])) {
		    $manage_host["statut"]="";
		  }
		  
		  if ($manage_host["statut"] != "") {
		    
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

$manage_header='PHP Network Managing Options - Current host status : <img src="./plugins/manage/images/themes/'.$theme.'/'.$manage_host["statut"].'.png" border="0" align=absmiddle onmouseover="return overlib(\'&lt;img src='.$config['url_path'].'plugins/manage/manage_weathermap_gd.php?id='.$_GET["id"].'&fake=\' + (Math.floor(Math.random()*4)+2) + (Math.floor(Math.random()*4)+2) + (Math.floor(Math.random()*4)+2) + (Math.floor(Math.random()*4)+2) + (Math.floor(Math.random()*4)+2) + (Math.floor(Math.random()*4)+2) + \'&gt;\', WRAP)" onMouseOut="nd();">';
			
		  } else {
		    $manage_host = array("type" => "", "mail" => "", "thresold" => "", "thresold_ref" => "", "group" => "", "admin_link" => "");
	        $manage_header="PHP Network Managing Options";
		  }
	    } else {
	      $manage_host = array("type" => "", "mail" => "", "thresold" => "", "thresold_ref" => "", "group" => "", "admin_link" => "");
	      $manage_header="Manage Options";
	    }
	  
		$fields_host_edit3["manage_header"] = array(
			"friendly_name" => $manage_header,
			"method" => "spacer"
		);
		
		$fields_host_edit3["manage"] = array(
			"method" => "checkbox",
			"friendly_name" => "Manage Host",
			"description" => "Check this box to manage this host.",
			"value" => "|arg1:manage|",
			"default" => "off",
			"form_id" => false
		);
 
        if ($manage_host["type"] != "") {
          $type=$manage_host["type"];
        } else {
          $type="none";
        }

        if ($manage_host["mail"] != "") {
          $mail=$manage_host["mail"];
        } else {
          $mail="";
        }
			
        if ($manage_host["thresold"] != "") {
          $thresold=$manage_host["thresold"];
        } else {
          $thresold="0";
        }

        if ($manage_host["thresold_ref"] != "") {
          $thresold_ref=$manage_host["thresold_ref"];
        } else {
          $thresold_ref="1";
        }
  
        if ($manage_host["group"] != "") {
          $group=$manage_host["group"];
        } else {
          $group="0";
        }
		
        if ($manage_host["admin_link"] != "") {
          $admin_link=$manage_host["admin_link"];
        } else {
          $admin_link="";
        }
  
	    $x = array();
	    $x["none"]="none";
	    $p = dirname(__FILE__) . "/images/themes";
	    $dir=opendir($p);
	    while($file=readdir($dir)) {
	      if ($file!="." and $file!="..") {
		    if (is_file($p."/".$file)) {
			  $x[$file]=$file;
			}
		  }
	    }
	    closedir($dir);

		$fields_host_edit3["manage_type"] = array(
			"method" => "drop_array",
			"friendly_name" => "Select image",
			"description" => "",
			"value" => "|arg1:manage_type|",
			"array" => $x,
			"default" => $type,
		);

		$s=array();
		$s[0]="none";
		$sql3 = "SELECT * FROM manage_templates";
		$result3 = mysql_query($sql3);
		while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
		  $s[$row3['id']]=$row3['name'] . " (" . $row3['tcp_ports'].")";
		}
  
		$fields_host_edit3["manage_template"] = array(
			"method" => "drop_array",
			"friendly_name" => "TCP Ports Template",
			"description" => "Data Sources and graphs will be automatically created.<br><u>Warning :</u> Poller Cache will be re-generated !",
			"value" => "|arg1:manage_template|",
			"default" => "0",
			"array" => $s,
		);
			
		$s=array();
		$s[0]="none";
		$sql3 = "SELECT manage_sites.name as `site`, manage_groups.name as `group`, manage_groups.id FROM manage_groups, manage_sites where manage_groups.site_id=manage_sites.id order by manage_sites.name, manage_groups.name";
		$result3 = mysql_query($sql3);
		while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
		  $s[$row3['id']]=$row3['site']." | ".$row3['group'];
		}

		$fields_host_edit3["manage_group"] = array(
			"method" => "drop_array",
			"friendly_name" => "Select site and group",
			"description" => "",
			"value" => "|arg1:manage_group|",
			"default" => $group,
			"array" => $s,
		);

		$fields_host_edit3["manage_thresold_ref"] = array(
			"method" => "textbox",
			"friendly_name" => "Enable host tresholding",
			"description" => "How many DOWN events a host must have before an alert is send.<br>Default is 1. Current treshold value is : <b>".$thresold."</b>.",
			"value" => "|arg1:manage_thresold_ref|",
			"default" => $thresold_ref,
			"max_length" => 255
		);
		
		$fields_host_edit3["manage_mail"] = array(
			"method" => "textbox",
			"friendly_name" => "Private e-mail to sending alerts",
			"description" => "',' is separator. Leave blank to use global e-mail from settings.<br>You can put here Cacti user id too.",
			"value" => "|arg1:manage_mail|",
			"default" => $mail,
			"max_length" => 255
		);
		
		$fields_host_edit3["manage_admin_link"] = array(
			"method" => "textbox",
			"friendly_name" => "Administration link",
			"description" => "Examples :<br>telnet://192.168.1.1:23<br>manage://192.168.1.1:22<br>http://192.168.1.1/admin<br>http://cactiserver/cacti/plugins/manage/manage_java.php?ip=192.168.1.1&port=22",
			"value" => "|arg1:manage_admin_link|",
			"default" => $admin_link,
			"max_length" => 255
		);	
		
			
      }
	  
	  
	  }
	  
	  
	}
  }

  $fields_host_edit = $fields_host_edit3;

}


function manage_api_device_save ($save) {

  if ($_POST["id"] == "0") {
    $id_host=db_fetch_cell("select max(id) from host")+1;
  } else {
    $id_host=$_POST["id"];
  }
  $is_host_here = db_fetch_cell("SELECT count(id) FROM `manage_host` where id='" . $id_host . "'");
  if (isset($_POST['manage'])) {
	$save["manage"] = form_input_validate($_POST['manage'], "manage", "", true, 3);
    if ($is_host_here == "0") {
	  db_execute("INSERT INTO manage_host ( id , uptime, type, `statut`, `group`, thresold, thresold_ref, mail ) VALUES ('" . $id_host . "', '0', 'none', 'nopoll', '0', '0', '1', '')");
	  db_execute("INSERT INTO manage_admin_link ( id , data ) VALUES ('" . $id_host . "', '')");
	}
		
  } else {
	$save['manage'] = form_input_validate('', "manage", "", true, 3);
		
	if ($is_host_here == "1") {
	  db_execute("delete from manage_host where id='" . $id_host . "'");
	  db_execute("delete from manage_tcp where id='" . $id_host . "'");
	  db_execute("delete from manage_services where id='" . $id_host . "'");
	  db_execute("delete from manage_process where id='" . $id_host . "'");
	  db_execute("delete from manage_alerts where idh='" . $id_host . "'");
	  db_execute("delete from manage_admin_link where id='" . $id_host . "'");
    }
			  
  }

  if ( (isset($_POST['manage_type'])) && (isset($_POST['manage_mail'])) && (isset($_POST['manage_thresold_ref'])) && (isset($_POST['manage_group'])) && (isset($_POST["id"])) && (isset($_POST['manage_admin_link'])) ) {
    db_execute("UPDATE manage_host SET `type` = '".$_POST['manage_type']."',`mail` = '".$_POST['manage_mail']."',`thresold_ref` = '".$_POST['manage_thresold_ref']."',`group` = '".$_POST['manage_group']."' where id='" . $id_host . "'");
	
	$is_admin_link_here = db_fetch_cell("SELECT count(id) FROM `manage_admin_link` where id='" . $id_host . "'");
	if ($is_admin_link_here == "1") {
	  db_execute("UPDATE manage_admin_link SET `data` = '".$_POST['manage_admin_link']."' where id='" . $id_host . "'");
	} else {
	  db_execute("INSERT INTO manage_admin_link ( id , data ) VALUES ('" . $id_host . "', '".$_POST['manage_admin_link']."')");
	}
	  
	if ($_POST['manage_template'] != "0") {
	  $tcps=db_fetch_cell("SELECT tcp_ports FROM manage_templates where id='" . $_POST['manage_template'] . "'");
      manage_ds_g($id_host, $tcps);
	}

  }

  return $save;

}


function manage_poller_top () {
 
  $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");

  $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");
  
  $poller_manage_enable = db_fetch_cell("select value from settings where name ='manage_enable'");
  
  if ( ($manage_enabled == "1") && ($poller_manage_enable == "on") ) {
	$w = db_fetch_cell("SELECT value FROM settings where name='manage_poller_plus'");
	if ($w == 1) {
      print "Manage : initializing...\n";
      db_execute("TRUNCATE TABLE `manage_poller_output`");
	}
  }
}


function manage_poller_bottom () {
  global $config;
  
  $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");

  $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");
  
  $poller_manage_enable = db_fetch_cell("select value from settings where name ='manage_enable'");
  
  $manage_poller_type = db_fetch_cell("select value from settings where name ='manage_poller'",'');
  
  if ( ($manage_enabled == "1") && ($poller_manage_enable == "on") && ($manage_poller_type == '0') ) {

    $p = dirname(__FILE__);

	  $command_string = db_fetch_cell("select value from settings where name='path_php_binary'");

	if ($config["cacti_server_os"] == "unix") {
	  $extra_args = "-q " . $p . "/poller_manage.php";
	} else {
	  $v = db_fetch_cell("SELECT value FROM settings where name='manage_use_patch'");

	  if ($v == 1) {
	    $command_string = $p . "/poller_manage.bat";
	    $extra_args = "";
	  } else {
	    $extra_args = "-q " . strtolower($p . "/poller_manage.php");
	  }

    }

	$manage_weathermap_enable = db_fetch_cell("select value from settings where name ='manage_weathermap_enable'");
    if ($manage_weathermap_enable == "on") {
      exec($command_string." ".$extra_args);
    } else {
	  exec_background($command_string, $extra_args);
	}
	  
  }
}

function plugin_manage_config_arrays () {
  global $user_auth_realms, $user_auth_realm_filenames, $menu;
  
  if (isset($_SESSION["sess_user_id"])) {
  
	$user_id=$_SESSION["sess_user_id"];

    $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");

    $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");
    $manage_for_user = db_fetch_cell("select count(*) from user_auth_realm where user_id='".$user_id."' and realm_id='".($manage_realm+100)."'");
  
    if ( ($manage_enabled == "1") && ($manage_for_user == "1") ) {
	
/*
    $user_auth_realm_filenames['plugins/manage/manage_viewalerts.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['plugins/manage/manage_templates.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['plugins/manage/manage_groups.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['plugins/manage/manage_sites.php'] = 100+$manage_realm;
*/
    $user_auth_realm_filenames['manage_viewalerts.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_templates.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_groups.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_sites.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_java.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_debug.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_iframe.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_ajax.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage.php'] = 100+$manage_realm;	
    $user_auth_realm_filenames['manage_accounts.php'] = 100+$manage_realm;
	
    $user_auth_realm_filenames['manage_settings.php'] = 100+$manage_realm;
	$user_auth_realm_filenames['manage_direct.php'] = 100+$manage_realm;
    $user_auth_realm_filenames['manage_weathermap_gd.php'] = 100+$manage_realm;

$permit = db_fetch_cell("SELECT count(*) FROM user_auth_realm WHERE user_id ='".$user_id."' AND realm_id='3'");
if ($permit == 1) {
  $menu["Templates"]['plugins/manage/manage_templates.php'] = "Manage Templates";
}

    $menu2 = array ();
    foreach ($menu as $temp => $temp2 ) {
	  $menu2[$temp] = $temp2;
	  if ($temp == 'Management') {
	  
$permit=manage_accounts_permit("reporting");
if ($permit == 1) {
  $menu2["Device Managing"]['plugins/manage/manage_viewalerts.php'] = "Event Reporting";
}

$permit=manage_accounts_permit("groups");
if ($permit == 1) {
  $menu2["Device Managing"]['plugins/manage/manage_groups.php'] = "Groups";
}

$permit=manage_accounts_permit("sites");
if ($permit == 1) {
  $menu2["Device Managing"]['plugins/manage/manage_sites.php'] = "Sites";
}


	  }
    }
    $menu = $menu2;

    api_plugin_load_realms ();
  }
  
  }
  
}


function manage_show_tab () {
  global $config, $user_auth_realms, $user_auth_realm_filenames;

  if (isset($_SESSION["sess_user_id"])) {
  
	$user_id=$_SESSION["sess_user_id"];

    $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");

    $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");
    $manage_for_user = db_fetch_cell("select count(*) from user_auth_realm where user_id='".$user_id."' and realm_id='".($manage_realm+100)."'");
  
    if ( ($manage_enabled == "1") && ($manage_for_user == "1") ) {


	//display an overlib info when user edit a host or view devices
	if (preg_match('/host.php/',$_SERVER['REQUEST_URI'] ,$matches)) {
//	  if ( isset($_GET['action']) && isset($_GET['id']) ) {
//	    if ($_GET['action']=="edit") {
		  ?><SCRIPT LANGUAGE="JavaScript" SRC="plugins/manage/lib/overlib.js"></script><?php
//	    }
//	  }
	}
			
	
	$v1 = db_fetch_cell("select value from settings where name='manage_list_".$user_id."'");
	if ( ($v1 == "") || ($v1 == 999) ) {
	  $v1="2";
	}
	load_current_session_value("simple", "sess_device_simple", $v1);

	$v = $_REQUEST["simple"];

	load_current_session_value("site", "sess_device_site", "0");
	load_current_session_value("group", "sess_device_group", "0");
	load_current_session_value("err", "sess_device_err", "0");
	
	$v2 = db_fetch_cell("select value from settings where name='manage_sound_".$user_id."'");
	if ($v2 == 999) {
	  $v2="";
	}
	load_current_session_value("manage_sound_enable", "sess_device_manage_sound_enable", $v2);
	
	load_current_session_value("forceids", "sess_device_forceids", "0");
		
	$v .= "&group=".$_REQUEST["group"];
	if ($_REQUEST["group"] == "0") {
	  $v .= "&site=".$_REQUEST["site"];
	}
	$v .= "&err=".$_REQUEST["err"];
	$v .= "&manage_sound_enable=".$_REQUEST["manage_sound_enable"];
	$v .= "&forceids=".$_REQUEST["forceids"];
		

	print '<a class="dock-item2" href="' . $config['url_path'] . 'plugins/manage/manage.php?simple='.$v.'" target="_parent"><img src="' . $config['url_path'] . 'plugins/manage/images/tab_manage_';
	$size=db_fetch_cell("select value from settings where name='manage_size_".$user_id."'");
	if ( ($size == "") || ($size == 999) ) {
	  $size="88";
	}
	print $size;

	if (preg_match('/plugins\/manage\/manage.php/',$_SERVER['REQUEST_URI'] ,$matches)) {
	  print "_down";
	}
		
	print '.png" alt="PHP Network Managing" align="absbottom" border="0"></a>';

  }
  
  }

}


function manage_draw_navigation_text ($nav) {
//  $nav["manage.php:"] = array("title" => "Manage", "mapping" => "index.php:", "url" => "manage.php", "level" => "1");          //this introduce a bug
  $nav["manage.php:"] = array("title" => "Manage", "mapping" => "index.php:", "url" => "", "level" => "");
	
  $nav["manage_viewalerts.php:"] = array("title" => "Event Reporting", "mapping" => "index.php:", "url" => "manage_viewalerts.php", "level" => "1");
  $nav["manage_viewalerts.php:actions"] = array("title" => "Event Reporting", "mapping" => "index.php:", "url" => "manage_viewalerts.php", "level" => "1");
  $nav["manage_templates.php:"] = array("title" => "Manage Templates", "mapping" => "index.php:", "url" => "manage_templates.php", "level" => "1");
  $nav["manage_templates.php:actions"] = array("title" => "Manage Templates", "mapping" => "index.php:", "url" => "manage_templates.php", "level" => "1");
  $nav["manage_groups.php:"] = array("title" => "Manage Groups", "mapping" => "index.php:", "url" => "manage_groups.php", "level" => "1");
  $nav["manage_groups.php:actions"] = array("title" => "Manage Groups", "mapping" => "index.php:", "url" => "manage_groups.php", "level" => "1");
  $nav["manage_sites.php:"] = array("title" => "Manage Sites", "mapping" => "index.php:", "url" => "manage_sites.php", "level" => "1");
  $nav["manage_sites.php:actions"] = array("title" => "Manage Sites", "mapping" => "index.php:", "url" => "manage_sites.php", "level" => "1");
  $nav["manage_accounts.php:"] = array("title" => "Manage Accounts", "mapping" => "index.php:", "url" => "manage_accounts.php", "level" => "1");
  $nav["manage_accounts.php:actions"] = array("title" => "Manage Accounts", "mapping" => "index.php:", "url" => "manage_accounts.php", "level" => "1");
  
  $nav["manage_settings.php:"] = array("title" => "Manage Settings", "mapping" => "index.php:", "url" => "manage_settings.php", "level" => "1");
  $nav["manage_settings.php:actions"] = array("title" => "Manage Settings", "mapping" => "index.php:", "url" => "manage_settings.php", "level" => "1");
  
  return $nav;
}

function manage_config_settings () {
// $database_default, $database_type, $database_port, $database_password, $database_username, $database_hostname, 
  global $tabs, $settings, $config, $user_auth_realms, $user_auth_realm_filenames;

  if (isset($_SESSION["sess_user_id"])) {

#####    define_syslog_variables();

	$user_id=$_SESSION["sess_user_id"];

    $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");

    $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");

    $permit=manage_accounts_permit("settings");
	
	
	
	
	$current = plugin_manage_version ();
	$current_manage_version = $current['version'];
	$old_manage_version = db_fetch_cell("select version from plugin_config where directory='manage'");
	if ( ($current_manage_version != $old_manage_version) && ($old_manage_version != "") ) {
	  if ($permit == 1) {
	    plugin_manage_install ();
	    db_execute("UPDATE plugin_config SET version = '".$current_manage_version."' where directory='manage'");
	  } else {
	    print "An updated PHP Network Managing version has been installed to this system.<br>";
		print "To allow the update process, please login with a user with administrator rights.";
		die();
	  }
    }


	
	
    $manage_for_user = db_fetch_cell("select count(*) from user_auth_realm where user_id='".$user_id."' and realm_id='".($manage_realm+100)."'");
  
    if ( ($manage_enabled == "1") && ($manage_for_user == "1") ) {
	
    $tabs["manage"] = "Manage";
  
//    $p = dirname(__FILE__);
//    include_once($p . '/manage_lib.php');
  
	//addition code for themes
	$x = array();
	$x[999]="Is not set";
	$x["none"]="none";
	$p = dirname(__FILE__) . "/images/themes";
	$dir=opendir($p);
	while($file=readdir($dir)) {
		if ($file!="." and $file!="..") {
			if (is_dir($p."/".$file)) {
				$x[$file]=$file;
			}
		}
	}
	closedir($dir);

    if ($permit == 1) {

  	  $infos = plugin_manage_version ();

	  $javascript = '<script type="text/javascript">
                        <!--
	                    function manage_popup(page) {
		                  window.open(page,"popup","");
	                    }
	
	                    function manage_popup2(page) {
		                  window.open(page,"popup","width=300,height=300");
	                    }
	                    function manage_popup3(page) {
		                  window.open(page,"manage_settings","width=700,height=500,scrollbars=yes");
	                    }
				
                        //-->
                        </script>';

		if (isset($_SERVER["SERVER_ADDR"])) {
		  $cacti_ip=$_SERVER["SERVER_ADDR"];
		} else {
		  $cacti_ip="[undetectable]";
		}
		
	  $temp_manage = array(
		"manage_header" => array(
			"friendly_name" => "\n$javascript\n<table width='99%' cellspacing=0 cellpadding=0 align=left><tr><td class='textSubHeaderDark'>Network Managing version ".$infos['version']." (Manage)</td><td align=right class='textSubHeaderDark'><a href='javascript:manage_popup(\"plugins/manage/manage_debug.php\")' class='textSubHeaderDark'><font color=white>Debug (Click here to verify your tables)</font></a></td></tr></table>",
			"method" => "spacer",
		),
		"manage_enable" => array(
			"friendly_name" => "Enable",
			"description" => "Check to enable the poller, uncheck to disable.",
			"method" => "checkbox",
		),
		"manage_disabled_text" => array(
			"friendly_name" => "Manage disabled message",
			"description" => "In case Manage is disabled, this message will be displayed on top. Leave blank if you don't want to display a message.",
			"method" => "textbox",
			"default" => "Is not set",
			"max_length" => 255,
		),
		"manage_poller_hosts" => array(
			"friendly_name" => "How many concurrent Manage Pollers can be run",
			"description" => "To speed up poller time, you can choose to distribute hosts you manage in multiples pollers which will be run in same time.<br>Default is 5.",
			"method" => "textbox",
			"default" => "Is not set",
			"max_length" => 10,
		),
		"manage_use_patch" => array(
			"friendly_name" => "Use Windows PHP Patch",
			"description" => "Don't use. Select 'No'. Default is 'No'.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 0 => 'No', 1 => 'Yes'),
		),
		"manage_poller" => array(
			"friendly_name" => "Type of poller",
			"description" => "You can use the default task Cacti poller. If you need a faster poller, scheduled another task with 'poller_manage.php' (or 'poller_manage.bat' if you want to use Windows PHP Patch) <u>from Cacti install directory</u>.<br>Default is 'Cacti poller'.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 0 => 'Cacti poller', 1 => 'Manage poller'),
		),
		"manage_poller_plus" => array(
			"friendly_name" => "Performance",
			"description" => "'Poller_output' is the best choice. Default is SNMP.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 1 => 'Poller_output', 0 => 'SNMP', 2 => 'WMI (Vbs)', 3 => 'WMI (Perl)'),
		),
		"manage_uptime_method" => array(
			"friendly_name" => "Check system uptime",
			"description" => "If available, rather than snmp agent uptime.",
			"method" => "checkbox",
		),
		"manage_uptime_cisco" => array(
			"friendly_name" => "Gather Cisco info",
			"description" => "If host is identified as a Cisco router, gather reboot information.",
			"method" => "checkbox",
		),
		"manage_perl" => array(
			"friendly_name" => "Perl Binary Path (optional)",
			"description" => "The path to your perl binary.",
			"default" => "Is not set",
			"method" => "textbox",
			"max_length" => 255,
		),
		"manage_reporting_daily" => array(
			"friendly_name" => "Check to enable daily report, uncheck to disable.",
			"description" => "Put your reports (.rpt) in '/manage/reports/daily' folder.",
			"method" => "checkbox",
		),
		"manage_disable_site_view" => array(
			"friendly_name" => "Disable Site View",
			"description" => "Default is 'Yes'.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 1 => 'Yes', 0 => 'No'),
		),
		"manage_tree_analyze" => array(
			"friendly_name" => "How deep a tree must be analyzed",
			"description" => "Can be time consuming on a big tree. Default is 5.",
			"method" => "textbox",
			"default" => "Is not set",
			"max_length" => 10,
		),
		"manage_mail_header" => array(
			"friendly_name" => "Mail Options",
			"method" => "spacer",
		),
		"manage_events" => array(
			"friendly_name" => "Enable mail alerts",
			"description" => "You need to configure Settings Plugin to send mails.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 5 => 'None', 6 => 'All', 0 => 'UP events', 1 => 'DOWN events', 2 => 'Reboot events', 3 => 'UP and DOWN events', 4 => 'DOWN and reboot events'),
		),
		"manage_global_email" => array(
			"friendly_name" => "Global e-mail to sending alerts",
			"description" => "',' is separator. You can put here Cacti user id too.",
			"method" => "textbox",
			"max_length" => 255,
		),
		"manage_double_email" => array(
			"friendly_name" => "Use global e-mail even if private e-mail is selected for a device",
			"description" => "Alerts will be sent to global <u>and</u> private e-mail.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 0 => 'No', 1 => 'Yes'),
		),
		"manage_netsend_header" => array(
			"friendly_name" => "Netsend Options",
			"method" => "spacer",
		),
		"manage_netsend_events" => array(
			"friendly_name" => "Enable 'net send' alerts",
			"description" => "Sends popups.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 5 => 'None', 6 => 'All', 0 => 'UP events', 1 => 'DOWN events', 2 => 'Reboot events', 3 => 'UP and DOWN events', 4 => 'DOWN and reboot events'),
		),
		"manage_send" => array(
			"friendly_name" => "Stations that will receive 'net send' alerts",
			"description" => "';' is separator. Windows stations only.",
			"method" => "textbox",
			"max_length" => 255,
		),
		"manage_netsend_method" => array(
			"friendly_name" => "'net send' method",
			"description" => "Perl module need Net::NetSend and Net-Nslookup.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 0 => 'Windows build-in command', 1 => 'Perl module'),
		),
		"manage_syslog_header" => array(
			"friendly_name" => "Syslog Options",
			"method" => "spacer",
		),
		"manage_syslog" => array(
			"friendly_name" => "Enable 'syslog' alerts",
			"description" => "These messages will be sent to your local syslog. If you would like these sent to a remote box, you must setup your local syslog to do so.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 5 => 'None', 6 => 'All', 0 => 'UP events', 1 => 'DOWN events', 2 => 'Reboot events', 3 => 'UP and DOWN events', 4 => 'DOWN and reboot events'),
		),
		"manage_syslog_level" => array(
			"friendly_name" => "Syslog Level",
			"description" => "This is the priority level that your syslog messages will be sent as.",
			"method" => "drop_array",
			"default" => LOG_WARNING,
			"array" => array(LOG_EMERG => 'Emergency', LOG_ALERT => 'Alert', LOG_CRIT => 'Critical', LOG_ERR => 'Error', LOG_WARNING => 'Warning', LOG_NOTICE => 'Notice', LOG_INFO => 'Info', LOG_DEBUG => 'Debug'),
			),
		"manage_snmp_header" => array(
			"friendly_name" => "SNMP Trap Sender Options",
			"method" => "spacer",
		),
		"manage_snmp" => array(
			"friendly_name" => "Enable 'snmptrap' alerts",
			"description" => "",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 5 => 'None', 6 => 'All', 0 => 'UP events', 1 => 'DOWN events', 2 => 'Reboot events', 3 => 'UP and DOWN events', 4 => 'DOWN and reboot events'),
		),
		"manage_snmp_ip" => array(
			"friendly_name" => "SNMP IP receiver",
			"description" => "Cacti server is default IP (currently : ".$cacti_ip.").",
			"method" => "textbox",
			"default" => "Is not set",
			"max_length" => 255,
		),
		"manage_snmp_version" => array(
			"friendly_name" => "SNMP Version",
			"description" => "Choose the SNMP version.",
			"method" => "drop_array",
			"default" => 999,
			"array" => array(999 => 'Is not set', 1 => 'Version 1', 2 => 'Version 2c'),
		),
		"manage_snmp_community" => array(
			"friendly_name" => "SNMP Community",
			"description" => "Choose the SNMP community.",
			"method" => "textbox",
			"default" => "Is not set",
			"max_length" => 255,
		),
		"manage_snmp_port" => array(
			"friendly_name" => "SNMP Port",
			"description" => "Choose the SNMP Port.",
			"method" => "textbox",
			"default" => "Is not set",
			"max_length" => 10,
		),
		"manage_security_header" => array(
			"friendly_name" => "Security Options",
			"method" => "spacer",
		),
		"manage_accounts_settings" => array(
			"friendly_name" => "<a href='javascript:manage_popup2(\"plugins/manage/manage_accounts.php?dest=settings\")'>Settings accounts</a>",
			"method" => "",
			"description" => "This is a listing of accounts that can configure Manage.<br>These accounts will be known as <b>Manage Administrators</b>.<br>Only these accounts can update database when a new version is installed.<br>Users will always have access to users settings.",
		),
		"manage_accounts_tab" => array(
			"friendly_name" => "<a href='javascript:manage_popup2(\"plugins/manage/manage_accounts.php?dest=tab\")'>Tab accounts</a>",
			"method" => "",
			"description" => "This is a listing of accounts that can access tab Manage.",
		),
		"manage_accounts_reporting" => array(
			"friendly_name" => "<a href='javascript:manage_popup2(\"plugins/manage/manage_accounts.php?dest=reporting\")'>Reporting accounts</a>",
			"method" => "",
			"description" => "This is a listing of accounts that can access Device Managing / Event Reporting.",
		),
		"manage_accounts_sites" => array(
			"friendly_name" => "<a href='javascript:manage_popup2(\"plugins/manage/manage_accounts.php?dest=sites\")'>Sites accounts</a>",
			"method" => "",
			"description" => "This is a listing of accounts that can access Device Managing / Sites.",
		),
		"manage_accounts_groups" => array(
			"friendly_name" => "<a href='javascript:manage_popup2(\"plugins/manage/manage_accounts.php?dest=groups\")'>Groups accounts</a>",
			"method" => "",
			"description" => "This is a listing of accounts that can access Device Managing / Groups.",
		),
		"manage_weathermap_header" => array(
			"friendly_name" => "Weathermap for Manage Options",
			"method" => "spacer",
		),
		"manage_weathermap_enable" => array(
			"friendly_name" => "Check to enable preprocessing maps, uncheck to disable",
			"description" => "Put your originals maps (.conf) in '/manage/weathermaps' folder.<br>These maps will be 'patched' and copied in '/weathermap/configs' folder.",
			"method" => "checkbox",
		),
		"manage_weathermap_theme" => array(
			"friendly_name" => "Theme for images",
			"description" => "Create a folder with your images in '<cacti>/plugins/manage/images/themes folder'.",
			"method" => "drop_array",
			"array" => $x,
		),
        "manage_User_options" => array(
			"friendly_name" => "<a href='javascript:manage_popup3(\"".$config['url_path']."plugins/manage/manage_settings.php\")'>User Options</a>",
			"description" => "",
			"method" => "none")
	  );

	  $settings["manage"]=$temp_manage;
  } else {

    if (preg_match('/settings.php/',$_SERVER['REQUEST_URI'] ,$matches)) {
  
  	  $javascript = '<script type="text/javascript">
                        <!--
	                    function manage_popup3(page) {
		                  window.open(page,"manage_settings","width=700,height=500,scrollbars=yes");
	                    }
	
                        //-->
                        </script>';
	print "\n$javascript\n";
	
	
      $settings["manage"]=array("manage_not_autorized" => array(
			"friendly_name" => "Unautorized access",
			"description" => "Please contact your Cacti Administrator.",
			"method" => "none"),
"manage_User_options" => array(
			"friendly_name" => "<a href='javascript:manage_popup3(\"".$config['url_path']."plugins/manage/manage_settings.php\")'>User Options</a>",
			"description" => "",
			"method" => "none")

			);
			
    }
  }

    }  //end manage enabled

  } //end test user logged

} //end function


function manage_poller_output ($manage_rrd_update_array) {

  $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");

  $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");
  
  $poller_manage_enable = db_fetch_cell("select value from settings where name ='manage_enable'");
  
  if ( ($manage_enabled == "1") && ($poller_manage_enable == "on") ) {
    $w = db_fetch_cell("SELECT value FROM settings where name='manage_poller_plus'");
	if ($w == 1) {
	  foreach($manage_rrd_update_array as $item) {
       $d = $item['local_data_id'];
		if (isset($item['times'][key($item['times'])])) {
		  $item = $item['times'][key($item['times'])];
		  if (isset($item['TCP'])) {
		    print "Checking Manage... TCP : \n      Data Source : ".$d." is : ".$item['TCP']."\n";
			db_execute("INSERT INTO manage_poller_output ( local_data_id, output ) VALUES ('" . $d . "', '".$item['TCP']."')");
		  }
		  if (isset($item['service_state'])) {
		    print "Checking Manage... Service : \n      Data Source : ".$d." is : ".$item['service_state']."\n";
			db_execute("INSERT INTO manage_poller_output ( local_data_id, output ) VALUES ('" . $d . "', '".$item['service_state']."')");
		  }
		  if (isset($item['proc_mem'])) {
		    print "Checking Manage... Process : \n      Data Source : ".$d." is : ".$item['proc_mem']."\n";
			db_execute("INSERT INTO manage_poller_output ( local_data_id, output ) VALUES ('" . $d . "', '".$item['proc_mem']."')");
		  }
		}
	  }
	}
	return $manage_rrd_update_array;
  }
}


function manage_console_after () {
  global $config, $user_auth_realms, $user_auth_realm_filenames;

  if (isset($_SESSION["sess_user_id"])) {
  
	$user_id=$_SESSION["sess_user_id"];

    $manage_realm = db_fetch_cell("select id from plugin_realms where plugin='manage'");

    $manage_enabled = db_fetch_cell("select status from plugin_config where directory='manage'");
    $manage_for_user = db_fetch_cell("select count(*) from user_auth_realm where user_id='".$user_id."' and realm_id='".($manage_realm+100)."'");
  
    if ( ($manage_enabled == "1") && ($manage_for_user == "1") ) {
	
	$p = dirname(__FILE__);
    include_once($p . '/manage_lib.php');
    $infos = plugin_manage_version ();

	$user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);
	
    $motd = db_fetch_cell("SELECT value FROM settings where name='manage_motd_enabled_".$user_id."'");
	if ($motd == "") {
	  $motd = 1;
	}
    if ($motd != 3) {
      $nbp = manage_debug(0);

	  $motd_style = db_fetch_cell("SELECT value FROM settings where name='manage_motd_style_".$user_id."'");
	  if ($motd_style == "") {
	    $motd_style = 1;
	  }
	  $url="plugins/manage/manage_iframe.php?motd=".$motd."&style=".$motd_style."&nbp=".$nbp;

	  if ($motd_style == 1) { // MotD Console
	    if ( ($motd == 2) && ($nbp <= 0) ) {
	      //
	    } else {
	      ?>
          <IFRAME src="<?php print $url; ?>" width=400 height=105 scrolling=no frameborder=0> </IFRAME>
          <?php
	    }
	  } // End Console
	
	  if ($motd_style == 2) { // MotD popup
	    if ( ($motd == 2) && ($nbp <= 0) ) {
	      //
	    } else {

	      $last_logon = db_fetch_cell("SELECT time FROM user_log WHERE user_id = '".$user_id."' ORDER BY time DESC LIMIT 1 ");

          $y=substr($last_logon, 0, 4);
          $mo=substr($last_logon, 5, 2);
          $d=substr($last_logon, 8, 2);
          $h=substr($last_logon, 11, 2);
          $mi=substr($last_logon, 14, 2);
          $s=substr($last_logon, 17, 2);

          $t2=mktime ( $h , $mi , $s , $mo , $d , $y);
          $t1=date("U");
          if ( ($t1-$t2) < 10) {
		    ?>
	        <body onload=window.open("<?php print $url; ?>","xyz","width=400,height=105");>
            <?php
		  }

	    }

	  } // End popup

    } // End MotD enabled
  
  }
  
  }
  
}

function manage_graph_refresh() {
  if (preg_match('/plugins\/manage\/manage.php/',$_SERVER['REQUEST_URI'] ,$matches)) {
    return '';
  } else {
    return '600';
  }
}

function manage_user_admin_tab() {
  global $config;

  	  $javascript = '<script type="text/javascript">
                        <!--
	                    function manage_popup2(page) {
		                  window.open(page,"manage_settings","width=700,height=500,scrollbars=yes");
	                    }
	
                        //-->
                        </script>';
	print "\n$javascript\n";
	
  ?>
  <td <?php print ((get_request_var("action") == "manage_user_edit") ? "bgcolor='silver'" : "bgcolor='#DFDFDF'");?> nowrap='nowrap' width='130' align='center' class='tab'>
	<span class='textHeader'><a href='javascript:manage_popup2("<?php print $config["url_path"];?>plugins/manage/manage_settings.php?id=<?php print $_GET["id"];?>")'>PHP Network Managing Settings</a></span>
  </td>
  <?php
}


function manage_setup_tables() {
	global $config, $database_default;

	include_once($config["library_path"] . "/database.php");

	$install = manage_check_version();

	if ($install == "full") {
		$sql = "alter table host add manage char(3) default '' not null after disabled";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_host ( id mediumint(8) unsigned NOT NULL default '0', uptime bigint(20) default NULL, type mediumint(8) unsigned NOT NULL default '0', services text, statut text)";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_alerts (idh mediumint(8) unsigned NOT NULL default '0', datetime datetime NOT NULL default '0000-00-00 00:00:00', ids mediumint(8) unsigned default '0', message text, note text, ida mediumint(9) unsigned NOT NULL auto_increment, PRIMARY KEY  (ida))";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_host_services ( id mediumint(8) unsigned NOT NULL default '0', services mediumint(8) unsigned default '0', statut text)";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_events', '5')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_poller', '0')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_date', '0')";
		$result = mysql_query($sql);
	}

	if ( ($install == "full") || ($install == "upgrade-0.1") ) {
		$sql = "ALTER TABLE manage_host ADD `force` MEDIUMINT(8) unsigned NOT NULL default '9'";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_device_type ( id MEDIUMINT(8) NOT NULL AUTO_INCREMENT , name TEXT, image TEXT, PRIMARY KEY ( id ) )";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (1, 'Windows 2003 Host', 'win2003.png')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (2, 'Windows XP Host', NULL)";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (3, 'Windows 2000 Host', 'win2000.png')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (4, 'Windows NT4 Host', 'winnt.png')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (5, 'Windows 9x Host', NULL)";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (6, 'Linux Host', 'linux.png')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (7, 'Router', 'router.png')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (8, 'Switch', 'switch.png')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_device_type VALUES (9, 'Other', 'other.png')";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_templates ( id MEDIUMINT(8) NOT NULL auto_increment, name TEXT, tcp_ports TEXT, PRIMARY KEY  (id) )";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_templates VALUES (1, 'Mail Server (Basic)', '110;25')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_templates VALUES (2, 'Mail Server (Enhanced)', '110;995;25;143;993;80;443')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_templates VALUES (3, 'Switch/Router', '23')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_templates VALUES (4, 'Web Server', '80;443')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_templates VALUES (5, 'LDAP Server - Windows 200x Domain Controler', '389')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_templates VALUES (6, 'DNS Server', '53')";
		$result = mysql_query($sql);
	}

	if ( ($install == "full") || ($install == "upgrade-0.1") || ($install == "upgrade-0.2") ) {
		$sql = "alter table manage_host add `group` MEDIUMINT(8) default '0'";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_groups ( id MEDIUMINT(8) NOT NULL auto_increment, name TEXT, PRIMARY KEY  (id) )";
		$result = mysql_query($sql);
		$sql = "INSERT INTO manage_groups VALUES (1, 'Test')";
		$result = mysql_query($sql);
	}

	if (($install == "full") || ($install == "upgrade-0.1") || ($install == "upgrade-0.2") || ($install == "upgrade-0.3")) {
		$sql = "CREATE TABLE manage_services ( id MEDIUMINT(8) NOT NULL, name TEXT, oid TEXT, statut TEXT )";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_process ( id MEDIUMINT(8) NOT NULL, name TEXT, tag TEXT, statut TEXT )";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE manage_alerts ADD `oid` TEXT";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_use_patch', '0')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_send', '')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_netsend_events', '5')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_netsend_method', '1')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_list', '0')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_list_separator', '25')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_full_separator', '6')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_simple_separator', '12')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_legend', 'on')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_poller_hosts', '5')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_perl', 'c:/perl/bin/perl.exe')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_thold', '1')";
		$result = mysql_query($sql);
	}

	if (($install == "full") || ($install == "upgrade-0.1") || ($install == "upgrade-0.2") || ($install == "upgrade-0.3") || ($install == "upgrade-0.4-2")) {
		$sql = "INSERT INTO settings VALUES ('manage_cycle_delay', '30')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_cycle_refresh', '5')";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_poller_output (local_data_id mediumint(8), output text)";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_poller_plus', '1')";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE manage_host_services RENAME manage_tcp";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_sites ( id MEDIUMINT(8) NOT NULL auto_increment, name TEXT, PRIMARY KEY  (id) )";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE manage_groups ADD site_id MEDIUMINT NOT NULL";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_order1', '1')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_order2', '2')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_order3', '3')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_order4', '0')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_order5', '0')";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE manage_host ADD `thresold_ref` MEDIUMINT(8) unsigned NOT NULL default '1'";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE manage_host ADD `thresold` MEDIUMINT(8) unsigned NOT NULL default '0'";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE manage_host ADD `mail` TEXT";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_list_2', '2')";
		$result = mysql_query($sql);
		$sql = "INSERT INTO settings VALUES ('manage_theme', 'default')";
		$result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_sound', '0')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_global_email', '')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_thold', '1')";
	    $result = mysql_query($sql);
	}

	if (($install == "full") || ($install == "upgrade-0.1") || ($install == "upgrade-0.2") || ($install == "upgrade-0.3") || ($install == "upgrade-0.4-2") || ($install == "upgrade-0.5")) {
		$sql = "ALTER TABLE manage_host change `type` `type` varchar(255)";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE `manage_host` DROP `services`";
		$result = mysql_query($sql);
		$sql = "ALTER TABLE `manage_host` DROP `force`";
		$result = mysql_query($sql);
	    $sql = "drop TABLE `manage_device_type`;";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_enable', '')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_accounts_tab', '-1')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_accounts_settings', '-1')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_accounts_reporting', '-1')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_accounts_sites', '-1')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_accounts_groups', '-1')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_weathermap_enable', '')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_weathermap_theme', 'default')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_syslog', '5')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_syslog_level', 'LOG_WARNING')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_snmp', '5')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_snmp_ip', 'Is not set')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_snmp_version', '2')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_snmp_community', 'public')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_snmp_port', '162')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_disable_site_view', '0')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_tree_analyze', '1')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_double_email', '0')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_disabled_text', '')";
	    $result = mysql_query($sql);
	  }
	  
	if (($install == "full") || ($install == "upgrade-0.1") || ($install == "upgrade-0.2") || ($install == "upgrade-0.3") || ($install == "upgrade-0.4-2") || ($install == "upgrade-0.5") || ($install == "upgrade-0.5.2")) {
		$sql = "CREATE TABLE manage_admin_link ( id MEDIUMINT(8) NOT NULL, data TEXT )";
		$result = mysql_query($sql);
		$sql = "CREATE TABLE manage_uptime_method ( id MEDIUMINT(8) NOT NULL, data TEXT )";
		$result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_uptime_method', '')";
	    $result = mysql_query($sql);
	    $sql = "INSERT INTO settings VALUES ('manage_uptime_cisco', '')";
	    $result = mysql_query($sql);
	}
	
}


?>
