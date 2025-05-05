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

include_once("./include/top_graph_header.php");

$user_id=db_fetch_cell("select id from user_auth where id=" .$_SESSION["sess_user_id"]);

$theme=db_fetch_cell("select value from settings where name='manage_theme_".$user_id."'");
if ( ($theme == "") || ($theme == "999") ) {
  $theme = "default";
}



	  $javascript = '<script type="text/javascript">
                        <!--
	                    function manage_popup2(page) {
		                  window.open(page,"manage_settings","width=700,height=500,scrollbars=yes");
	                    }
	
                        //-->
                        </script>';
	print "\n$javascript\n";
?>


<script type="text/javascript">
var plus = new Image();
plus.src = "../../plugins/manage/images/themes/<?php echo $theme; ?>/hide.png";
var minus = new Image();
minus.src = "../../plugins/manage/images/themes/<?php echo $theme; ?>/show.png";

function toggle(objLink, imgName){
  document.images[imgName].src = (document.images[imgName].src==plus.src) ? minus.src:plus.src;
  objLink.blur();
  return false; //cancel action of href
}
</script>

<script type="text/javascript">
// M�thode pour changer la visiblit� d'une balise dont l'ID est pass�e en param�tre
function toggleVisibility(tagId) {
  if (!document.getElementById) {
	msg = 'Too old browser\n';
	msg += 'Please update';
	return false;
  }

  var tagToToggle;

  try { // On tente de r�cup�rer la balise cible dont on doit changer la visibilit�
	tagToToggle = document.getElementById(tagId);
  }catch (e){ // Si �chec de la r�cup�ration de la balise cible
	alert('error');
  }

  try { // Seulement pour les non IE
	if (tagToToggle.style.display == 'none') {
	  tagToToggle.style.display = 'inline';
	}else{
	  tagToToggle.style.display = 'none';
	}
  }catch (e){
  }

  // Pour IE
  if (tagToToggle.style.visibility == 'hidden') {
	tagToToggle.style.visibility = 'visible';
  }else{
	tagToToggle.style.visibility = 'hidden';
  }
}
</script>

<script src="lib/cycle.js"></script>

<body onload="rtime=<?php $tmp=db_fetch_cell("select value from settings where name='manage_cycle_delay_".$user_id."'"); if ($tmp == "is not set") { $tmp == 30;} echo $tmp*1000; ?>;rtimer=<?php $tmp=db_fetch_cell("select value from settings where name='manage_cycle_refresh_".$user_id."'"); if ($tmp == "is not set") { $tmp == 30;} echo $tmp*1000; ?>;parent.getnext();">

<SCRIPT LANGUAGE="JavaScript" SRC="lib/overlib.js"></script>

<?php
//start graph options view panel

if (isset($_REQUEST["simple"])) {
  $view=$_REQUEST["simple"];
} else {
  $view=db_fetch_cell("select value from settings where name='manage_list_".$user_id."'");
  if ( ($view=="") || ($view=="999") ) {
    $view="2";
  }
}

$manage_disable_site_view = db_fetch_cell("select value from settings where name='manage_disable_site_view'");
if ( ($manage_disable_site_view == "1") && ($view == "3") ) {
  print "This view has been disabled.";
  die;
}

$vi2=db_fetch_cell("select value from settings where name='manage_list_2_".$user_id."'");
if ( ($vi2=="") || ($vi2=="999") ) {
  $vi2="2";
}
  
?>
	<form name="form_devices">

	
	
	
				<table width="100%" style='background-color: #f5f5f5; border: 1px solid #bbbbbb;' cellpadding="1" cellspacing="0" border="0">
				  <tr>

				  
					<td width="5"></td>
					<td width="1"><!-- View -->
						<select name="cbo_host_status" onChange="window.location=document.form_devices.cbo_host_status.options[document.form_devices.cbo_host_status.selectedIndex].value">
							<option value="manage.php?simple=0<?php if (isset($_REQUEST["forceids"])) { print "&forceids=" . $_REQUEST["forceids"];}?><?php if (isset($_REQUEST["site"])) { print "&site=" . $_REQUEST["site"];}?><?php if (isset($_REQUEST["group"])) { print "&group=" . $_REQUEST["group"];}?><?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];}?><?php if (isset($_REQUEST["err"])) { print "&err=" . $_REQUEST["err"];}?>"<?php if ($view == 0) {?> selected<?php }?>>Full view</option>
							<option value="manage.php?simple=1<?php if (isset($_REQUEST["forceids"])) { print "&forceids=" . $_REQUEST["forceids"];}?><?php if (isset($_REQUEST["site"])) { print "&site=" . $_REQUEST["site"];}?><?php if (isset($_REQUEST["group"])) { print "&group=" . $_REQUEST["group"];}?><?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];}?><?php if (isset($_REQUEST["err"])) { print "&err=" . $_REQUEST["err"];}?>"<?php if ($view == 1) {?> selected<?php }?>>Simple view</option>
							<option value="manage.php?simple=2<?php if (isset($_REQUEST["forceids"])) { print "&forceids=" . $_REQUEST["forceids"];}?><?php if (isset($_REQUEST["site"])) { print "&site=" . $_REQUEST["site"];}?><?php if (isset($_REQUEST["group"])) { print "&group=" . $_REQUEST["group"];}?><?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];}?><?php if (isset($_REQUEST["err"])) { print "&err=" . $_REQUEST["err"];}?>"<?php if ($view == 2) {?> selected<?php }?>>List view</option>
							<?php
							if ($manage_disable_site_view == "0") {
							  ?>
							  <option value="manage.php?simple=3<?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];}?><?php if (isset($_REQUEST["err"])) { print "&err=" . $_REQUEST["err"];}?>&group=0&site=0"<?php if ($view == 3) {?> selected<?php }?>>Site view</option>
							  <?php
							}
							?>
							<option value="manage.php?simple=4&forceids=0<?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];}?><?php if (isset($_REQUEST["err"])) { print "&err=" . $_REQUEST["err"];}?>&group=0&site=0"<?php if ($view == 4) {?> selected<?php }?>>Tree view</option>
						</select>
					</td>
					
				  
<?php

$config_done=0;

if ( ( ($_REQUEST["simple"] == 3) || ($_REQUEST["simple"] == 4) ) && ($config_done == 0) ) {   //site or tree
  $do_site=0;
  $do_group=0;
  $do_err=0;
  $do_cycle=0;
  $do_refresh_cycle=1;
  $do_sound=1;
  $do_refresh_button=1;
  
  $config_done=1;
}

if ( ($_REQUEST["site"] == "-1") && ($config_done == 0) ) {   //from tree
  $do_site=1;
  $do_group=1;
  $do_err=1;
  $do_cycle=1;
  $do_refresh_cycle=1;
  $do_sound=1;
  $do_refresh_button=1;
  
  $config_done=1;
}

if ( ($_REQUEST["site"] != "-1") && ($config_done == 0) ) {   //site selected
  $do_site=1;
  $do_group=1;
  $do_err=1;
  $do_cycle=1;
  $do_refresh_cycle=1;
  $do_sound=1;
  $do_refresh_button=1;
  
  $config_done=1;
}

if ( ($_REQUEST["site"] == "0") && ($config_done == 0) ) {   //all devices
  $do_site=1;
  $do_group=1;
  $do_err=1;
  $do_cycle=0;
  $do_refresh_cycle=1;
  $do_sound=1;
  $do_refresh_button=1;
  
  $config_done=1;
}

if ($do_site == 1) {
?>

				  <td width="5"></td>
					<td width="1"><!-- Site -->
						<select name="cbo_host_status5" onChange="window.location=document.form_devices.cbo_host_status5.options[document.form_devices.cbo_host_status5.selectedIndex].value">
<?php

print "<option value='manage.php?site=";
if (isset($_REQUEST["simple"])) {
	print "&simple=" . $_REQUEST["simple"];
}

if (isset($_REQUEST["err"])) {
	print "&err=" . $_REQUEST["err"];
}

print "&group=0";

print "'>Select a site";

print "<option value='manage.php?site=0";
if (isset($_REQUEST["simple"])) {
	print "&simple=" . $_REQUEST["simple"];
}

if (isset($_REQUEST["manage_sound_enable"])) {
	print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];
}

//if (isset($_REQUEST["forceids"])) {
//	print "&forceids=" . $_REQUEST["forceids"];
//}
print "&forceids=0";

if (isset($_REQUEST["err"])) {
	print "&err=" . $_REQUEST["err"];
}
print "'>All sites";



if ($manage_disable_site_view != "1") {

$sql4 = "SELECT * FROM manage_sites order by name";
$result4 = mysql_query($sql4);

while ($row4 = mysql_fetch_array($result4, MYSQL_ASSOC)) {
	print "<option value='manage.php?site=" . $row4['id'];

	if (isset($_REQUEST["simple"])) {
		if ($_REQUEST["simple"] <> "3") {
			print "&simple=" . $_REQUEST["simple"];
		}else{
			print "&simple=".$vi2;
		}
	}

	if (isset($_REQUEST["manage_sound_enable"])) {
	  print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];
    }
    print "&group=0";
	if (isset($_REQUEST["err"])) {
		print "&err=" . $_REQUEST["err"];
	}


	//if (isset($_REQUEST["forceids"])) {
	//	print "&forceids=" . $_REQUEST["forceids"];
	//}
print "&forceids=0";
	
	print "'";
	if ( isset($_REQUEST["site"]) and ($_REQUEST["site"]==$row4['id']) ) {
	    print " selected";
	}
	print ">" . $row4['name'] . "</option>";
}
}

  print "</select></td>";
}  //end do site
?>

					
					
					
					
					
					
					
					
					
					
					
					
	<?php
	if ($manage_disable_site_view != "1") {
if ($do_group == 1) {
	?>
					
					<td width="5"></td>
					<td width="1"> <!-- Group -->
						<select name="cbo_host_status4" onChange="window.location=document.form_devices.cbo_host_status4.options[document.form_devices.cbo_host_status4.selectedIndex].value">
<?php

$sql4 = "SELECT manage_groups.id, manage_groups.name, manage_groups.site_id   FROM `manage_groups`, `manage_sites` where manage_groups.site_id=manage_sites.id and manage_groups.id <> '0'";
if (isset($_REQUEST["site"])) {
	if ( ($_REQUEST["site"] <> "0") && ($_REQUEST["site"] <> "-1") ) {
		$sql4 .= " AND site_id='".$_REQUEST["site"]."'";
	}
}

$sql4 .= " order by site_id, manage_groups.name";
$result4 = mysql_query($sql4);

print "<option value='manage.php?group=999";
if (isset($_REQUEST["site"])) {
	if ($_REQUEST["site"] == "-1") {
	  print "&site=0";
	} else { 
	  print "&site=" . $_REQUEST["site"];
	}
}

if (isset($_REQUEST["simple"])) {
	print "&simple=" . $_REQUEST["simple"];
}

if (isset($_REQUEST["manage_sound_enable"])) {
	print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];
}

//if (isset($_REQUEST["forceids"])) {
//	print "&forceids=" . $_REQUEST["forceids"];
//}
print "&forceids=0";

if (isset($_REQUEST["err"])) {
	print "&err=" . $_REQUEST["err"];
}
print "'>Select a group";

print "<option value='manage.php?group=0";

if (isset($_REQUEST["site"])) {
	if ($_REQUEST["site"] == "-1") {
	  print "&site=0";
	} else { 
	  print "&site=" . $_REQUEST["site"];
	}
}

if (isset($_REQUEST["simple"])) {
	print "&simple=" . $_REQUEST["simple"];
}

if (isset($_REQUEST["manage_sound_enable"])) {
	print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];
}

//if (isset($_REQUEST["forceids"])) {
//	print "&forceids=" . $_REQUEST["forceids"];
//}
print "&forceids=0";

if (isset($_REQUEST["err"])) {
	print "&err=" . $_REQUEST["err"];
}
print "'>All groups";

while ($row4 = mysql_fetch_array($result4, MYSQL_ASSOC)) {
	print "<option value='manage.php?group=" . $row4['id'];

if (isset($_REQUEST["site"])) {
	if ($_REQUEST["site"] == "-1") {
	  print "&site=0";
	} else { 
	  print "&site=" . $_REQUEST["site"];
	}
}

	if (isset($_REQUEST["simple"])) {
		if ($_REQUEST["simple"] <> "3") {
			print "&simple=" . $_REQUEST["simple"];
		}else{
			print "&simple=".$vi2;
		}
	}

	if (isset($_REQUEST["err"])) {
		print "&err=" . $_REQUEST["err"];
	}

if (isset($_REQUEST["manage_sound_enable"])) {
	print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];
}

	//if (isset($_REQUEST["forceids"])) {
	//  print "&forceids=" . $_REQUEST["forceids"];
	//}
    print "&forceids=0'";
	if ( isset($_REQUEST["group"]) and ($_REQUEST["group"]==$row4['id']) ) {
	    print " selected";
	}
	print ">";

	$f=0;
	if (!isset($_REQUEST["site"])) {
		$f=1;
	} else {
		if ( ($_REQUEST["site"] == "0") || ($_REQUEST["site"] == "-1") ) {
			$f=1;
		}
	}

	if ($f == 1) {
		$w = db_fetch_cell("SELECT name FROM manage_sites where id='".$row4['site_id']."'");
		print $w. " | ";
	}

	print $row4['name'] . "</option>";
}

  print "</select></td>";
}  //end do group
}
//}
?>


		
		
		

		

	<?php
if ($do_err == 1) {
	?>
					<td width="5"></td>
					<td width="1">
						<select name="cbo_host_status3" onChange="window.location=document.form_devices.cbo_host_status3.options[document.form_devices.cbo_host_status3.selectedIndex].value">

<option value="manage.php?err=0<?php if (isset($_REQUEST["forceids"])) { print "&forceids=" . $_REQUEST["forceids"];}?><?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];}?><?php if (isset($_REQUEST["group"])) {
print "&group=" . $_REQUEST["group"];
if ($_REQUEST["group"] == "0") {
  if (isset($_REQUEST["site"])) {
    print "&site=" . $_REQUEST["site"];
  }
}
}?><?php if (isset($_REQUEST["simple"])) { print "&simple=" . $_REQUEST["simple"];}?>"<?php if (isset($_REQUEST["err"])) { if ($_REQUEST["err"] == "0") {?> selected<?php }}?>>All hosts</option>
<option value="manage.php?err=1<?php if (isset($_REQUEST["forceids"])) { print "&forceids=" . $_REQUEST["forceids"];}?><?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=" . $_REQUEST["manage_sound_enable"];}?><?php if (isset($_REQUEST["group"])) {
print "&group=" . $_REQUEST["group"];
if ($_REQUEST["group"] == "0") {
  if (isset($_REQUEST["site"])) {
    print "&site=" . $_REQUEST["site"];
  }
}
}?><?php if (isset($_REQUEST["simple"])) { print "&simple=" . $_REQUEST["simple"];}?>"<?php if (isset($_REQUEST["err"])) { if ($_REQUEST["err"] == "1") {?> selected<?php }}?>>Only hosts with errors</option>

<?php
  print "</select></td>";
}  //end do err
?>




	<?php
if ($do_cycle == 1) {
if ($manage_disable_site_view != "1") {
	?>
					<td width="5"></td>
					<td width="120">
					<center>
Cycle : <a id="cstop" style="display:none;" href="#" onclick="stopTime()">Stop</a>
<a id="cstart" href="#" onclick="startTime()">Start</a>
<span id="countdown"></span> | <a href="#" onclick="parent.getnext();">Next</a>

<?php
  print "</td>";
  }
}  //end do cycle
?>




					
					

	<?php
if ($do_refresh_cycle == 1) {
	?>
					<td width="5"></td>
					<td width="120">
				<center>
Auto-refresh : <a id="rstop" style="display:none;" href="#" onclick="refrstop()">Stop</a>
<a id="rstart" href="#" onclick="refrstart()">Start</a>
<span id="cd"></span>
<?php
  print "</td>";
}  //end do refresh cycle
?>
					
					
					
					
					
	<?php
if ($do_sound == 1) {
	?>
					<td width="5"></td>
					<td width="20">
					
<?php
$snd="";
if (isset($_REQUEST["manage_sound_enable"])) {
  $snd=$_REQUEST["manage_sound_enable"];
}
//print "-".$snd;
?>

<a id="snd_on" style="display:
<?php
if ($snd == "on") {
  print "inline";
} else {
  print "none";
}
?>
;" href="manage.php?<?php if (isset($_REQUEST["err"])) { print "err=" . $_REQUEST["err"];}
if (isset($_REQUEST["forceids"])) {
  print "&forceids=" . $_REQUEST["forceids"];
}
if (isset($_REQUEST["group"])) {
  print "&group=" . $_REQUEST["group"];
}
if ($_REQUEST["group"] == "0") {
  if (isset($_REQUEST["site"])) { print "&site=" . $_REQUEST["site"];}
}
if (isset($_REQUEST["simple"])) { print "&simple=" . $_REQUEST["simple"];}?><?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=";}?>" onclick="manage_snd()"><center><img src='../../plugins/manage/images/sound.gif' border="0" onclick="manage_snd()"></a>

<a id="snd_off" style="display:<?php
if ($snd == "") {
  print "inline";
} else {
  print "none";
}
?>
;" href="manage.php?<?php if (isset($_REQUEST["err"])) { print "err=" . $_REQUEST["err"];}
if (isset($_REQUEST["forceids"])) {
  print "&forceids=" . $_REQUEST["forceids"];
}
if (isset($_REQUEST["group"])) {
  print "&group=" . $_REQUEST["group"];
  if ($_REQUEST["group"] == "0") {
    if (isset($_REQUEST["site"])) { print "&site=" . $_REQUEST["site"];}
  }
}
if (isset($_REQUEST["simple"])) { print "&simple=" . $_REQUEST["simple"];}?><?php if (isset($_REQUEST["manage_sound_enable"])) { print "&manage_sound_enable=on";}?>" onclick="manage_snd()"><center><img src='../../plugins/manage/images/sound_no.gif' border="0" onclick="manage_snd()"></a>

<?php
  print "</td>";
}  //end do sound
?>

					
					
					

	<?php
if ($do_refresh_button == 1) {
	?>
					<td width="5"></td>
<?php
/*
if ($do_cycle_clear == 0) {
  print "<td>";
} else {
  print '<td width="80">';
}
*/
print '<td>';
?>					
					<a href="#"><img src='../../images/button_refresh.gif' border="0" onclick="refr2()"></a>
<?php
  print "</td>";
}  //end do sound
?>

					
					

		</tr>
	  </table>


<span id="title"></span>

<span id="image"></span><br>
		</form>
</body></html>

	<?php
//end graph options view panel
	?>