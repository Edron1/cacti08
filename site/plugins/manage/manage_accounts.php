<?php

chdir('../../');

include("./include/auth.php");

include_once("./include/global.php");

global $config;

$p = dirname(__FILE__);
include_once($p . '/manage_lib.php');
$permit=manage_accounts_permit("settings");
if ($permit == 0) {
  print "Unauthorized access.";
  exit;
}

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
	manage_accounts_save();
	break;

  default:
	manage_accounts_edit();
	break;
}


function manage_accounts_edit() {
  global $colors;

  $w = db_fetch_cell("SELECT value FROM settings where name='manage_accounts_".$_REQUEST["dest"]."'");
  $u=explode(';', $w);

  html_start_box("<strong><font color='#FFFFFF'>Accounts ".$_REQUEST["dest"]." :</font></strong>", "98%", $colors["header"], "4", "center", "");
  ?>
  <tr bgcolor="<?php print $colors["panel"];?>">
  <?php

  $users = db_fetch_assoc("SELECT id, full_name, username FROM user_auth ORDER BY username");

  print '<td><form name="form_devices">';
  print "<select name='notify_accounts[]' multiple>";
  print "<option value='-1'";
  for ($counter=0;$counter<count($u);$counter++) {
	if ($u[$counter] == "-1") {
	  print " selected";
	}
  }
  print ">All users</option>";

  foreach ($users as $user) {
    print "<option value='".$user["id"]."'";
	for ($counter=0;$counter<count($u);$counter++) {
	  if ( ($u[$counter] == $user["id"]) || ($user["id"] == 1) ) {
	    print " selected";
	  }
	}
	print ">".$user["username"]." (".$user["full_name"].")</option>";
  }
  print "</select></td>";

  ?>
  <input type='hidden' name='dest' value='<?php print $_REQUEST["dest"];?>'>
  </tr>
  <?php
  html_end_box();

  $ds_actions = array(
	1 => "Save"
  );

  draw_actions_dropdown($ds_actions);

  print "</td></tr></table>";


}



function manage_accounts_save() {
  foreach ($_GET as $var => $val) {
    if ($var == "notify_accounts") {
      $n="";
	  for ($counter=0;$counter<count($val);$counter++) {
        if ($counter == 0) {
          $n = $val[$counter];
        } else {
          $n .= ";".$val[$counter];
        }		
	  }
	  
	  $is_setting_here = db_fetch_cell("SELECT count(value) FROM `settings` where name='manage_accounts_" . $_REQUEST["dest"] . "'");
	  if ($is_setting_here == "0") {
	    db_execute("INSERT INTO settings ( name, value ) VALUES ('manage_accounts_".$_REQUEST["dest"]."', '".$n."')");
	  } else {
        db_execute("UPDATE settings SET value = '" . $n . "' where name='manage_accounts_".$_REQUEST["dest"]."'");
	  }
    }
  }

  print "<b><strong>Saved.</strong></b><br>";
  manage_accounts_edit();
}


?>



