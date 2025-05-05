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

?>
<link href="<?php print $config['url_path']; ?>include/main.css" rel="stylesheet">
<?php

global $config;

include_once($config["library_path"] . "/database.php");

$infos=plugin_manage_version ();

  print '<script type="text/javascript">
<!--
	function manage_popup(page,option) {
		window.open(page,"popup2",option);
	}
//-->
</script>';

  $text1="<table width='98%' align='center' border='0'><tr><td class='textArea' colspan='2'>";
  $text2="<strong>You have Plugin <a href='" . $infos['homepage']."' target='_blank'>PHP Network Managing</a> installed.</strong>";
  $text3="</td></tr><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td class='textArea'><li>Version ".$infos['version'] ." : ";
  $text4="<a href='javascript:manage_popup(\"manage_debug.php\", \"width=900,height=480,resizable=yes,scrollbars=yes\")'>";
  $text5=" problem(s) detected</a>.</li>";
  $text6="</td></tr></table>";
  
  if ($_REQUEST["motd"] == 1) {
	    print $text1;
		
		if ($_REQUEST["style"] == 1) {
		  print "<br><br>";
		}
		
	    print $text2;
	    print $text3;
	
	    if ($_REQUEST["nbp"] > 0) {
	      print $text4.$_REQUEST["nbp"].$text5;
	    } else {
	      print "no problem detected.";
	    }
        $tmp=$infos['url']."?myversion=".$infos['version'];
        print "</td></tr><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td class='textArea'><li>Check if update is available <a href='javascript:manage_popup(\"$tmp\", \"width=200,height=50,resizable=no,scrollbars=no\")'>here</a>.</li>";
	    print $text6;		

  }
  
  if ( ($_REQUEST["motd"] == 2) && ($_REQUEST["nbp"] > 0) ) {
	print $text1;
	
	if ($_REQUEST["style"] == 1) {
	  print "<br><br>";
	}
		
	print $text2;
	print $text3;
	print $text4.$_REQUEST["nbp"].$text5;
  }

?>
