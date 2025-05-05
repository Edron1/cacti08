<?php
chdir('../../');

include("./include/auth.php");

include_once("./include/global.php");

include_once($config["library_path"] . "/database.php");

global $config;

	$user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);
		
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

?>

<meta http-equiv="refresh" content="0; url=<?php print "manage.php?simple=".$v;?>" />

</head>

<body>

<p>Please wait...</p>

</body>
</html>