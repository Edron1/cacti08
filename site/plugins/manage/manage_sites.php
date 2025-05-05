<?php

chdir('../../');

include("./include/auth.php");

include_once("./include/global.php");

$p = dirname(__FILE__);
include_once($p . '/manage_lib.php');
$permit=manage_accounts_permit("sites");
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
        db_execute("INSERT INTO manage_sites ( id , name ) VALUES ('', '" . $_GET['name'] . "')");
      } else {
        db_execute("UPDATE manage_sites SET name = '" . $_GET['name'] . "' where id='" . $_GET['id2'] . "'");
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
	  for ($i=1;($i<=count($selected_items));$i++) {
	    input_validate_input_number($selected_items[$i]);
		db_execute("delete FROM manage_sites where id='" . $selected_items[$i] . "'");             //delete site
		db_execute("delete FROM manage_groups where site_id not in (SELECT id FROM manage_sites)");      //delete orphean groups
	  }
	}
	header("Location: manage_sites.php");
	exit;
  }

  $host_list = ""; $i = 0;

  foreach ($_POST as $var => $val) {
    if (ereg("^chk_([0-9]+)$", $var, $matches)) {
	  input_validate_input_number($matches[1]);

	  $host_list .= "<li>" . db_fetch_cell("select name from manage_sites where id=" . $matches[1]) . "<br>";
	  $host_array[$i] = $matches[1];
	}
	$i++;
  }

  include_once("./include/top_header.php");
  html_start_box("<strong>" . $device_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");
  print "<form action='manage_sites.php' method='post'>\n";

  if ($_POST["drp_action"] == "1") { //delete
	print "	<tr><td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'><p>To delete this Site, press the \"yes\" button below.</p><p>$host_list</p></td></tr>";
  }

  if (!isset($host_array)) {
	print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one entry.</span></td></tr>\n";
	$save_html = "";
  } else {
    $save_html = "<input type='image' src='../../images/button_yes.gif' alt='Save' align='absmiddle'>";
  }

  print "	<tr><td colspan='2' align='right' bgcolor='#eaeaea'><input type='hidden' name='action' value='actions'><input type='hidden' name='selected_items' value='" . (isset($host_array) ? serialize($host_array) : '') . "'><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'><a href='manage_sites.php'><img src='../../images/button_no.gif' alt='Cancel' align='absmiddle' border='0'></a>$save_html</td></tr>";

  html_end_box();

  include_once("./include/bottom_footer.php");
}




function manage_host() {
  global $colors, $device_actions;
  load_current_session_value("page2", "sess_device_current_page2", "1");
  load_current_session_value("filter", "sess_device_filter", "");
  load_current_session_value("host_template_id", "sess_device_host_template_id", "-1");
  load_current_session_value("host_status", "sess_host_status", "-1");

  html_start_box("<strong>Sites</strong>", "98%", $colors["header"], "3", "center", "manage_sites.php?edit=1&id=-1");

  html_end_box();

  print "<br>";

  html_start_box("", "98%", $colors["header"], "3", "center", "host.php?action=edit&host_template_id=" . $_REQUEST["host_template_id"] . "&host_status=" . $_REQUEST["host_status"]);

  $total_rows = db_fetch_cell("select COUNT(id) from manage_sites");
  $hosts = db_fetch_assoc("select id, name from manage_sites limit " . (read_config_option("num_rows_device")*($_REQUEST["page2"]-1)) . "," . read_config_option("num_rows_device"));
  $url_page2_select = get_page_list($_REQUEST["page2"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "");
  $nav = "<tr bgcolor='#" . $colors["header"] . "'><td colspan='3'><table width='100%' cellspacing='0' cellpadding='0' border='0'><tr><td align='left' class='textHeaderDark'><strong>&lt;&lt; ";
  if ($_REQUEST["page2"] > 1) {
    $nav .= "<a class='linkOverDark' href='listmanage2.php?filter=" . $_REQUEST["filter"] . "&host_template_id=" . $_REQUEST["host_template_id"] . "&host_status=" . $_REQUEST["host_status"] . "&page2=" . ($_REQUEST["page2"]-1) . "'>";
  }
  $nav .= "Previous";
  if ($_REQUEST["page2"] > 1) {
    $nav .= "</a>";
  }
  $nav .= "</strong></td>\n<td align='center' class='textHeaderDark'>Showing Rows " . ((read_config_option("num_rows_device")*($_REQUEST["page2"]-1))+1) . " to " . ((($total_rows < read_config_option("num_rows_device")) || ($total_rows < (read_config_option("num_rows_device")*$_REQUEST["page2"]))) ? $total_rows : (read_config_option("num_rows_device")*$_REQUEST["page2"])) . " of $total_rows [$url_page2_select]</td>\n<td align='right' class='textHeaderDark'><strong>";
  if (($_REQUEST["page2"] * read_config_option("num_rows_device")) < $total_rows) {
    $nav .= "<a class='linkOverDark' href='listmanage2.php?filter=" . $_REQUEST["filter"] . "&host_template_id=" . $_REQUEST["host_template_id"] . "&host_status=" . $_REQUEST["host_status"] . "&page2=" . ($_REQUEST["page2"]+1) . "'>";
  }
  $nav .= "Next";
  if (($_REQUEST["page2"] * read_config_option("num_rows_device")) < $total_rows) {
    $nav .= "</a>";
  }
  $nav .= " &gt;&gt;</strong></td>\n</tr></table></td></tr>\n";
  print $nav;
  html_header_checkbox(array("Name"));

  $i = 0;
  if (sizeof($hosts) > 0) {
    foreach ($hosts as $host) {

	  form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
	  ?>
	  <td width=250>
	  <?php
	  print "<a class='linkEditMain' href='manage_sites.php?edit=1&id=".$host["id"]."'>";
	  print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $host["name"]);
  	  ?>

	  <td style="<?php print get_checkbox_style();?>" width="1%" align="right">
	  <input type='checkbox' style='margin: 0px;' name='chk_<?php print $host["id"];?>' title="<?php print $host["name"];?>">
	  </td>
	  </tr>
	  <?php
	}

    /* put the nav bar on the bottom as well */
	print $nav;
  } else {
    print "<tr><td><em>No sites</em></td></tr>";
  }
  html_end_box(false);

  draw_actions_dropdown($device_actions);
}





function manage_edit() {
  global $colors;
  $sql = "SELECT * FROM manage_sites where id = '" . $_GET['id'] . "'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result, MYSQL_ASSOC);

  html_start_box("<strong>Site - " . $row['name'] . " - :</strong>", "98%", $colors["header"], "4", "center", "");

  print "<form method='get' action='manage_sites.php'><br>";

  ?>
  <input type='hidden' name='id2' value='<?php print $_GET['id'];?>'>
  <?php

  $i = 0;
  form_alternate_row_color($colors["alternate"],$colors["light"],$i);

  print "<td>Name : </td><td>";
  print "<input type='text' name='name' size='80' value='" . $row['name'] . "'>";

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
