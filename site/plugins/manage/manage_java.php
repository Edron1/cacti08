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

if (isset($_REQUEST["ip"])) {
  $ip = $_REQUEST["ip"];
} else {
  print "Bug.";
  exit;
}

if (isset($_REQUEST["port"])) {
  $port = $_REQUEST["port"];
} else {
  print "Bug.";
  exit;
}

$user_id = db_fetch_cell("select id from user_auth where id=" . $_SESSION["sess_user_id"]);
$relay_ip = db_fetch_cell("select value from settings where name='manage_relay_ip_".$user_id."'");
$relay_port = db_fetch_cell("select value from settings where name='manage_relay_port_".$user_id."'");

if ( ($relay_ip == "") || ($relay_ip == "Is not set") ) {
  $relay_ip = $_SERVER['REMOTE_ADDR'];
}

if ( ($relay_port == "") || ($relay_port == "Is not set") ) {
  $relay_port = "31415";
}

?>

<html>

<head>

</head>

<body>

<center>

<?php
if ($port == "22") {
?>
<APPLET CODE="com.sshtools.sshterm.SshTermApplet.class"
               WIDTH=600 HEIGHT=500 CODEBASE="java"
			   archive="SSHTermApplet-signed.jar,SSHTermApplet-jdkbug-workaround-signed.jar,SSHTermApplet-jdk1.3.1-dependencies-signed.jar">
<param name="sshapps.connection.host" value="<?php print $ip; ?>">
<param name="sshapps.connection.connectImmediately"   value="true">
<param name="sshapps.connection.authenticationMethod" value="password">
<param name="sshapps.connection.showConnectionDialog" value="true">
<param name="sshapps.connection.userName"  value="">
</applet>
<?php
} else {
?>
<applet CODEBASE="java"  ARCHIVE="jta20.jar" CODE="de.mud.jta.Applet"  WIDTH=590 HEIGHT=360>
<PARAM NAME="config" VALUE="include/java.conf">
<PARAM NAME="plugins" VALUE="Status,Socket,Telnet,Terminal">
<PARAM NAME="Socket.host" VALUE="<?php print $ip; ?>">
<PARAM NAME="Socket.port" VALUE="<?php print $port; ?>">
<PARAM NAME="Socket.relay" VALUE="<?php print $relay_ip; ?>">
<PARAM NAME="Socket.relayport" VALUE="<?php print $relay_port; ?>"> 
</applet>
<?php
}
?>

</center>

</body>

</html>

