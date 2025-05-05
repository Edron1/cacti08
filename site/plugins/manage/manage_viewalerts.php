<?php

chdir('../../');

include("./include/auth.php");

include_once("./include/global.php");

$p = dirname(__FILE__);
include_once($p . '/manage_lib.php');
$permit=manage_accounts_permit("reporting");
if ($permit == 0) {
  print "Unauthorized access.";
  exit;
}

include_once("./include/top_header.php");

print "<script type='text/javascript' src='" . $config["url_path"] . "include/layout.js'></script>";

if (isset($_GET['method'])) {
	foreach ($_GET as $var => $val) {
		if (substr($var, 0, 3 ) == 'sip') {
			$b = "si" . substr($var, 3, 3 );
			$d = substr($_GET[$b], 19, 50 );

			switch ($_GET['method']){
			case "delete" :
				db_execute("delete from manage_alerts where ida='" . $d . "'");  //////////////
				break;
			case "save" :
			db_execute("update manage_alerts set note='" . $val . "' where ida='" . $d . "'");   ///////////////////
				break;
			}
		}
	}
}

//execute save or delete,
if (isset($_POST['drp_action'])) {
	$valp="";
	$si="";
	$si3="";
	$i=0;

	foreach ($_POST as $var => $val) {
		if ($val == 'on') {
			$p=substr($var, 4, 10 ) . " " . substr($var, 15, 8 );
			$si .= "<li>" . $p . " (" . $valp . ")<br>";
			$si2[$i]=$p . substr($var, 24, 50 );
			$si3[$i]=$valp;
			$i++;
		}

		$valp=$val;
	}

	switch ($_POST['drp_action']){
	case "1" :
		$msg="save";
		$title="Save";
		break;
	case "2" :
		$msg="delete";
		$title="Delete";
		break;
	}

	print "<br><br><center><table width='400'><tr><td bgcolor='#6d88ad' class='textHeaderDark'><strong>" . $title . "</strong></td></tr><td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'><p>Are you sure you want to " . $msg . " the following events entries ?</p><p>$si</p></td></tr>";
	print "<tr><td colspan='2' align='right' bgcolor='#eaeaea'>";
	print "<a href='manage_viewalerts.php?del=no'><img src='" . $config["url_path"] . "images/button_no.gif' alt='Cancel' align='absmiddle' border='0'></a>";

	if (!isset($si2)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one event entry.</span></td></tr>\n";
	}else{
		print "<a href='manage_viewalerts.php?method=" . $msg;    // . "&id=" . $_REQUEST['id'];

		$i=0;

		foreach ($si2 as $t) {
			print "&si" . $i . "=" . $t;
			print "&sip" . $i . "=" . $si3[$i];
			$i++;
		}

		print "'><img src='" . $config["url_path"] . "images/button_yes.gif' alt='Validate' align='absmiddle' border='0'></a></td></tr>";
	}

	html_end_box();
} //end drp_action

define("MAX_DISPLAY_PAGES", 21);

	global $colors, $device_actions;
	//
	load_current_session_value("page", "sess_device_current_page", "1");
	load_current_session_value("filter", "sess_device_filter", "");
	load_current_session_value("host_template_id", "sess_device_host_template_id", "-1");
	load_current_session_value("host_status", "sess_host_status", "-1");
//	load_current_session_value("host_managed", "sess_host_managed", "1");

	html_start_box("<strong>Filter</strong>", "98%", $colors["header"], "3", "center", "");
//	include("inc_device_filter_table.php");

	?>
	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="form_devices">
		<td>
			<table width="500" cellpadding="0" cellspacing="0">
				<tr>
					<td width="5"></td>
    				<td width="90">
						Type of device:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_host_status" onChange="window.location=document.form_devices.cbo_host_status.options[document.form_devices.cbo_host_status.selectedIndex].value">

<?php
/*
///////////////////////////							<option value="manage_viewalerts.php?host_managed=1"<?php if ($_REQUEST["host_managed"] == "1") {?> selected<?php }?>>Any</option>
///////////////////////////							<option value="manage_viewalerts.php?host_managed=2"<?php if ($_REQUEST["host_managed"] == "2") {?> selected<?php }?>>Managed</option>
///////////////////////////							<option value="manage_viewalerts.php?host_managed=3"<?php if ($_REQUEST["host_managed"] == "3") {?> selected<?php }?>>Not managed</option>
*/
?>
							<option value="manage_viewalerts.php?host_managed=1<?php if (isset($_REQUEST["event"])) { print "&event=" . $_REQUEST["event"];}?>"<?php if (isset($_REQUEST["host_managed"])) { if ($_REQUEST["host_managed"] == "1") {?> selected<?php }}?>>Any</option>
							<option value="manage_viewalerts.php?host_managed=2<?php if (isset($_REQUEST["event"])) { print "&event=" . $_REQUEST["event"];}?>"<?php if (isset($_REQUEST["host_managed"])) { if ($_REQUEST["host_managed"] == "2") {?> selected<?php }}?>>Managed</option>
							<option value="manage_viewalerts.php?host_managed=3<?php if (isset($_REQUEST["event"])) { print "&event=" . $_REQUEST["event"];}?>"<?php if (isset($_REQUEST["host_managed"])) { if ($_REQUEST["host_managed"] == "3") {?> selected<?php }}?>>Not managed</option>

						</select>
					</td>





					<td width="5"></td>
    				<td width="90">
						Type of event:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_host_status3" onChange="window.location=document.form_devices.cbo_host_status3.options[document.form_devices.cbo_host_status3.selectedIndex].value">

							<option value="manage_viewalerts.php?event=1<?php if (isset($_REQUEST["id"])) { print "&id=" . $_REQUEST["id"];}?><?php if (isset($_REQUEST["host_managed"])) { print "&host_managed=" . $_REQUEST["host_managed"];}?>"<?php if (isset($_REQUEST["event"])) { if ($_REQUEST["event"] == "1") {?> selected<?php }}?>>Any</option>
							<option value="manage_viewalerts.php?event=2<?php if (isset($_REQUEST["id"])) { print "&id=" . $_REQUEST["id"];}?><?php if (isset($_REQUEST["host_managed"])) { print "&host_managed=" . $_REQUEST["host_managed"];}?>"<?php if (isset($_REQUEST["event"])) { if ($_REQUEST["event"] == "2") {?> selected<?php }}?>>UP</option>
							<option value="manage_viewalerts.php?event=3<?php if (isset($_REQUEST["id"])) { print "&id=" . $_REQUEST["id"];}?><?php if (isset($_REQUEST["host_managed"])) { print "&host_managed=" . $_REQUEST["host_managed"];}?>"<?php if (isset($_REQUEST["event"])) { if ($_REQUEST["event"] == "3") {?> selected<?php }}?>>DOWN</option>
							<option value="manage_viewalerts.php?event=4<?php if (isset($_REQUEST["id"])) { print "&id=" . $_REQUEST["id"];}?><?php if (isset($_REQUEST["host_managed"])) { print "&host_managed=" . $_REQUEST["host_managed"];}?>"<?php if (isset($_REQUEST["event"])) { if ($_REQUEST["event"] == "4") {?> selected<?php }}?>>Reboot</option>
							<option value="manage_viewalerts.php?event=5<?php if (isset($_REQUEST["id"])) { print "&id=" . $_REQUEST["id"];}?><?php if (isset($_REQUEST["host_managed"])) { print "&host_managed=" . $_REQUEST["host_managed"];}?>"<?php if (isset($_REQUEST["event"])) { if ($_REQUEST["event"] == "5") {?> selected<?php }}?>>UP and DOWN</option>
							<option value="manage_viewalerts.php?event=6<?php if (isset($_REQUEST["id"])) { print "&id=" . $_REQUEST["id"];}?><?php if (isset($_REQUEST["host_managed"])) { print "&host_managed=" . $_REQUEST["host_managed"];}?>"<?php if (isset($_REQUEST["event"])) { if ($_REQUEST["event"] == "6") {?> selected<?php }}?>>DOWN and reboot</option>

						</select>
					</td>






					<td width="5"></td>
				</tr>
			</table>
		</td>
		<input type='hidden' name='page' value='1'>
		</form>
	</tr>

	<?php
	html_end_box();


	$sql="";
    $sql_where = "";
	$sql_where1 = "";
	$sql2="SELECT id, hostname, description FROM host where (hostname like '%%' OR description like '%%')";
	if (isset($_REQUEST["host_managed"])) { ////////////////////////////////////
      if ($_REQUEST["host_managed"] == "2") {
		$sql_where1 = " and (manage='on')";
	  }
	  if ($_REQUEST["host_managed"] == "3") {
		$sql_where1 = " and (manage='')";
	  }
	  $sql2 .=  $sql_where1." order by description";
	  $sql_where = $sql_where1;
	}  //////////////////////////////////////////////////

	$tmp="";
	if (isset($_REQUEST["event"])) { ////////////////////////////////////
  	  if ($_REQUEST["event"] == "2") {
		$tmp = " and (message like '%%up%%')";
		$sql_where .= $tmp;
	  }
      if ($_REQUEST["event"] == "3") {
	    $tmp = " and (message like '%%down%%')";
		$sql_where .= $tmp;
	  }
      if ($_REQUEST["event"] == "4") {
		$tmp = " and (message like '%%reboot%%')";
		$sql_where .= $tmp;
	  }
	  if ($_REQUEST["event"] == "5") {
		$tmp = " and ( (message like '%%up%%') or (message like '%%down%%') )";
		$sql_where .= $tmp;
	  }
	  if ($_REQUEST["event"] == "6") {
		$tmp = " and ( (message like '%%reboot%%') or (message like '%%down%%') )";
		$sql_where .= $tmp;
	  }


	$sql = "SELECT distinct (host.id) , host.hostname, host.description FROM host, manage_alerts where manage_alerts.idh=host.id and (hostname like '%%' OR description like '%%')" . $sql_where;

	}  //////////////////////////////////////////////////




//	load_current_session_value("page", "sess_device_current_page", "1");
//	load_current_session_value("filter", "sess_device_filter", "");
//	load_current_session_value("host_template_id", "sess_device_host_template_id", "-1");
//	load_current_session_value("host_status", "sess_host_status", "-1");
//	load_current_session_value("host_managed", "sess_host_managed", "1");
//	$_REQUEST["host_managed"] = "2";
	html_start_box("<strong>Device</strong>", "98%", $colors["header"], "3", "center", "");
//	include("inc_device_filter_table.php");

  $result = mysql_query($sql);
////////////////////////////////  $row = mysql_fetch_array($result, MYSQL_ASSOC);

	?>
	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="form_devices2">
		<td>
			<table width="200" cellpadding="0" cellspacing="0">
				<tr>
					<td width="5"></td>
    				<td width="20">
						Device:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_host_status2" onChange="window.location=document.form_devices2.cbo_host_status2.options[document.form_devices2.cbo_host_status2.selectedIndex].value">
<option value='manage_viewalerts.php?<?php if (isset($_REQUEST["id"])) { print "id=" . $_REQUEST["id"]; } ?>'><--- select ----></option>";


<?php
/*
<option value="manage_listmanage2.php?host_managed=3"<?php if ($_REQUEST["host_managed"] == "3") {?> selected<?php }?>>Not managed</option>
<option value="manage_listmanage2.php?host_managed=1"<?php if ($_REQUEST["host_managed"] == "1") {?> selected<?php }?>>Any</option>
<option value="manage_listmanage2.php?host_managed=2"<?php if ($_REQUEST["host_managed"] == "2") {?> selected<?php }?>>Managed</option>
*/

  $result2 = mysql_query($sql2);
while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {

  print "<option value='manage_viewalerts.php?";
/////////////////  if ($_REQUEST["host_managed"] <> "") {
  if (isset($_REQUEST["host_managed"])) {
    print "host_managed=" . $_REQUEST["host_managed"] . "&";
  }
  if (isset($_REQUEST["event"])) {
    print "event=" . $_REQUEST["event"] . "&";
  }
  print "id=" . $row2['id']  . "'";

  if (isset($_REQUEST["id"])) {       //////////////////////////////////
    if ($_REQUEST["id"] == $row2['id']) {
      print " selected";
    }
  }  /////////////////////////////////////////

  print ">" . $row2['description'] . " (". $row2['hostname'] . ")</option>";
//  $q_row['description'] . " (" . $q_row['hostname']


}

?>


						</select>
					</td>


						</select>
					</td>

					<td width="5"></td>
				</tr>
			</table>
		</td>
		<input type='hidden' name='page' value='1'>
		</form>
	</tr>

	<?php
	html_end_box();



//print $_REQUEST["id"];
	html_start_box("", "98%", $colors["header"], "3", "center", "");

	if (!isset($_REQUEST["id"])) {
//////////////		if ( ($_REQUEST["id"] == '') || ($_REQUEST["id"] == '0') ) {
	  $total_rows = '0';
	  $alerts=array();
//
    } else {
	  $total_rows = db_fetch_cell("SELECT COUNT(idh) FROM manage_alerts where idh=" . $_REQUEST["id"] . $tmp);
///////////////////    }


	if (isset($_REQUEST["port"])) {
		$tmp .= " and ( ids like '%%".$_REQUEST["port"]."%%') ";
	}

	if (isset($_REQUEST["win_svc"])) {
		$tmp .= " and ( oid like '%%".$_REQUEST["win_svc"]."%%') ";
	}

	if (isset($_REQUEST["tag"])) {
	  $tag= str_replace(" ", "%%", $_REQUEST["tag"]);
      $tmp .= " and ( oid like '%%".$tag."%%') ";
	}

	$alerts = db_fetch_assoc("select
		ida,
		idh,
		datetime,
		ids,
		message,
		note,
		oid
		from manage_alerts
		where idh=" . $_REQUEST["id"]. $tmp . " order by datetime DESC limit 20");
//		limit " . (read_config_option("num_rows_device")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_device"));

	} //////////////////////////////////

		$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "manage_viewalerts.php");



	print "<tr bgcolor='#" . $colors["header"] . "'></tr>\n";
  html_start_box("", "100%", $colors["header"], "4", "center", "");
html_header_checkbox(array("Date/Time", "Host/Service", "Event", "Notes"));

/*
  ?>
  <input type='hidden' name='id' value='<?php print $_REQUEST['id'];?>'>
  <?php
*/

	$i = 0;
	if (sizeof($alerts) > 0) {
		foreach ($alerts as $alert) {

			form_alternate_row_color($colors["alternate"],$colors["light"],$i);
				?>
				<td width="130"><?php print $alert["datetime"];?></td>

				<td width="70">
				<?php
				if ($alert["ids"] == "0") {
				  print "Host";
				} else {

				  if ($alert["ids"] == "9999") {
				    $ss=("SELECT `name` FROM `manage_services` WHERE `oid`='".$alert["oid"]."'");
				    $rr = mysql_query($ss);
				    $rrw = mysql_fetch_array($rr, MYSQL_ASSOC);
				    print "Service " . $rrw['name'];
				  } else {
				  	if ($alert["ids"] == "9998") {
//				      $ss=("SELECT `name` FROM `manage_process` WHERE `tag`='".$alert["oid"]."'");
//				      $rr = mysql_query($ss);
//				      $rrw = mysql_fetch_array($rr, MYSQL_ASSOC);
				      print "Process " . $alert["oid"];
				    } else {
				  	  print "Port " . $alert["ids"];
					}
				  }

				}

				?></td>

				<td width="60"><?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $alert["message"]);?></td>

				<td><input type='text' name='inp_<?php print $i;?>' size='70' value='<?php print $alert['note'];?>'></td>

				<td style="<?php print get_checkbox_style();?>" width="10" align="right">
	<input type='checkbox' style='margin: 0px;' name='chk_<?php print $alert['datetime'];?>_<?php print $alert['ida'];?>'>
	</td>

			</tr>
			<?php
			$i++;
		}

		/* put the nav bar on the bottom as well */
//		print $nav;
	}else{
		print "<tr><td><em>No alerts</em></td></tr>";
	}

	html_end_box(false);

print "</table>";

  $ds_actions = array(
	1 => "Save",
	2 => "Delete"
  );

  draw_actions_dropdown($ds_actions);

  print "</form>\n";
  print "</td></tr></table>";




//nclude_once("./include/bottom_footer.php");

?>
