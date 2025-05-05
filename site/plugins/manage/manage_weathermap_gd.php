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

$theme = db_fetch_cell("select value from settings where name='manage_weathermap_theme'");

$nb_tcps = db_fetch_cell("select count(*) from manage_tcp where id='".$_GET['id']."'");
$nb_services = db_fetch_cell("select count(*) from manage_services where id='".$_GET['id']."'");
$nb_processes = db_fetch_cell("select count(*) from manage_process where id='".$_GET['id']."'");
$nb = $nb_tcps+$nb_services+$nb_processes;

$bg = db_fetch_cell("select `type` from manage_host where id='".$_GET['id']."'");
//$bg="win2003.png";
//$bg="";

if ( ($bg == "") || ($bg == "none") ) {
  $img_final = @ImageCreate (200, 10*($nb+1)) or die ("Error");
  $tmp = ImageColorAllocate ($img_final, 255, 255, 255); //color white
  $c_b = imagecolorallocate($img_final,0,0,0 ); //color black
} else {
  $img_final = ImageCreateFromPng ("./plugins/manage/images/themes/".$bg);
  $c_b = imagecolorallocate($img_final,0,0,0 ); //color black
}

header ("Content-type: image/png");  

$tcps = db_fetch_assoc("select * from manage_tcp where id='".$_GET['id']."'");
$i=0;
foreach ($tcps as $tcp) {
  $img = ImageCreateFromPng ("./plugins/manage/images/themes/".$theme."/led_".$tcp["statut"].".png");
  @imageCopyMerge ($img_final, $img, 60, 5+10*$i, 0, 0, 16, 10, 100);
  imagestring($img_final,2,80,13+($i-1)*10,$tcp["services"],$c_b);
  $i++;
}

$services = db_fetch_assoc("select * from manage_services where id='".$_GET['id']."'");
foreach ($services as $service) {
  $img = ImageCreateFromPng ("./plugins/manage/images/themes/".$theme."/svc_led_".$service["statut"].".png");
  @imageCopyMerge ($img_final, $img, 60, 5+10*$i, 0, 0, 16, 10, 100);
  imagestring($img_final,2,80,13+($i-1)*10,$service["name"],$c_b);
  $i++;
} 

$processes = db_fetch_assoc("select * from manage_process where id='".$_GET['id']."'");
foreach ($processes as $process) {
  $img = ImageCreateFromPng ("./plugins/manage/images/themes/".$theme."/prc_led_".$process["statut"].".png");
  @imageCopyMerge ($img_final, $img, 60, 5+10*$i, 0, 0, 16, 10, 100);
  imagestring($img_final,2,80,13+($i-1)*10,$process["name"],$c_b);
  $i++;
}

ImagePng ($img_final); 

?>