<?php

chdir('../../');

include("./include/auth.php");

include_once("./include/global.php");

$user_id=$_SESSION["sess_user_id"];
$permit = db_fetch_cell("SELECT count(*) FROM user_auth_realm WHERE user_id ='".$user_id."' AND realm_id='3'");
if ($permit == 0) {
  print "Unauthorized access.";
  exit;
}

define("MAX_DISPLAY_PAGES", 21);

$device_actions = array(1 => "Delete");

if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"] = "";
}

switch ($_REQUEST["action"]) {
  case 'actions':
	if (!isset($_REQUEST["id2"])) {
	  manage_form_actions();
	} else {
      if (isset($_GET['force'])) {
        db_execute("INSERT INTO manage_templates ( id , name , tcp_ports ) VALUES ('', '" . $_GET['name'] . "', '" . $_GET['tcp'] . "' )");
      } else {
        db_execute("UPDATE manage_templates SET tcp_ports = '" . $_GET['tcp'] ."', name = '" . $_GET['name'] . "' where id='" . $_GET['id2'] . "'");
      }
    }
  default:
    include_once("./include/top_header.php");
    if (isset($_REQUEST["edit"])) {
      manage_edit();
    } else {
	  manage_host();
    }
	include_once("./include/bottom_footer.php");
	break;
}


function manage_form_actions() {
  global $colors, $device_actions, $fields_host_edit;

  if (isset($_POST["selected_items"])) {
    $selected_items = unserialize(stripslashes($_POST["selected_items"]));
	if ($_POST["drp_action"] == "1") { //delete
  	  for ($i=0;($i<count($selected_items));$i++) {
	    input_validate_input_number($selected_items[$i]);
		db_execute("delete FROM manage_templates where id='" . $selected_items[$i] . "'");
	  }
    }
	header("Location: manage_templates.php");
	exit;
  }

  $host_list = ""; $i = 0;

  foreach ($_POST as $var => $val) {
    if (ereg("^chk_([0-9]+)$", $var, $matches)) {
	  input_validate_input_number($matches[1]);
	  $host_list .= "<li>" . db_fetch_cell("select name from manage_templates where id=" . $matches[1]) . "<br>";
      $host_array[$i] = $matches[1];
	}

	$i++;
  }

  include_once("./include/top_header.php");

  html_start_box("<strong>" . $device_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

  print "<form action='manage_templates.php' method='post'>\n";

  if ($_POST["drp_action"] == "1") { //delete
	print "	<tr><td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'><p>To delete this Template, press the \"yes\" button below.</p><p>$host_list</p></td></tr>";
  }


  if (!isset($host_array)) {
	print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one entry.</span></td></tr>\n";
	$save_html = "";
  } else {
    $save_html = "<input type='image' src='../../images/button_yes.gif' alt='Save' align='absmiddle'>";
  }

  print "	<tr><td colspan='2' align='right' bgcolor='#eaeaea'><input type='hidden' name='action' value='actions'><input type='hidden' name='selected_items' value='" . (isset($host_array) ? serialize($host_array) : '') . "'><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'><a href='manage_templates.php'><img src='../../images/button_no.gif' alt='Cancel' align='absmiddle' border='0'></a>$save_html</td></tr>";

  html_end_box();

  include_once("./include/bottom_footer.php");
}




function manage_host() {
  global $colors, $device_actions;
  load_current_session_value("page_templates", "sess_device_current_page", "1");
  load_current_session_value("filter_templates", "sess_device_filter", "");
  load_current_session_value("host_template_id", "sess_device_host_template_id", "-1");
  load_current_session_value("host_status", "sess_host_status", "-1");

  html_start_box("<strong>Templates</strong>", "98%", $colors["header"], "3", "center", "manage_templates.php?edit=1&id=-1");

  $total_rows = db_fetch_cell("select COUNT(id) from manage_templates");

  $hosts = db_fetch_assoc("select id, name, tcp_ports from manage_templates");

  html_header_checkbox(array("Name", "TCP Ports"));

  $i = 0;
  if (sizeof($hosts) > 0) {
    foreach ($hosts as $host) {

	  form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
	  ?>
	  <td width=250>
	  <?php
	  print "<a class='linkEditMain' href='manage_templates.php?edit=1&id=".$host["id"]."'>";
	  print eregi_replace("(" . preg_quote($_REQUEST["filter_templates"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $host["name"]);
	  print "<a class='linkEditMain' href='manage_templates.php?edit=1&id=".$host["tcp_ports"]."'></td>";
	  ?>
      <td>
	  <?php
	  print eregi_replace("(" . preg_quote($_REQUEST["filter_templates"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $host["tcp_ports"]);
	  ?>
	  </td>
	  <td style="<?php print get_checkbox_style();?>" width="1%" align="right">
	  <input type='checkbox' style='margin: 0px;' name='chk_<?php print $host["id"];?>' title="<?php print $host["name"];?>">
	  </td>
	  </tr>
	  <?php
	}


  } else {
    print "<tr><td><em>No templates</em></td></tr>";
  }

  html_end_box(false);

  draw_actions_dropdown($device_actions);
}





function manage_edit() {
  global $colors;
  $sql = "SELECT * FROM manage_templates where id = '" . $_GET['id'] . "'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result, MYSQL_ASSOC);

  html_start_box("<strong>Template - " . $row['name'] . " - :</strong>", "98%", $colors["header"], "3", "center", "");

  print "<form method='get' action='manage_templates.php'><br>";

  ?>
  <input type='hidden' name='id2' value='<?php print $_GET['id'];?>'>
  <?php

  $i = 0;
  form_alternate_row_color($colors["alternate"],$colors["light"],$i);

  print "<td>Name : </td><td>";
  print "<input type='text' name='name' size='80' value='" . $row['name'] . "'>";

  $i++;

  form_alternate_row_color($colors["alternate"],$colors["light"],$i);
  print "<td>TCP Ports to monitor : </td><td>";
  print "<input type='text' name='tcp' size='80' value='" . $row['tcp_ports'] . "'>";

  if ($_GET['id'] == "-1") {
    ?>
    <input type='hidden' name='force' value='null'>
    <?php
  }
  html_end_box(false);

  $ds_actions = array(1 => "Save");

  draw_actions_dropdown($ds_actions);

  print "</form>\n";
  print "</td></tr></table>";

}



?>
