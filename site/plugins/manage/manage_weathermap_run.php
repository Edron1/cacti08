<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

include(dirname(__FILE__) . "/../../include/global.php");

$db = mysql_connect("$database_hostname", "$database_username", "$database_password");
mysql_select_db("$database_default",$db);

$theme = db_fetch_cell("select value from settings where name='manage_weathermap_theme'");

	$weathermaps = array();
	$p = dirname(__FILE__) . "/weathermaps";

	$dir=opendir($p);
	while($file=readdir($dir)) {
		if ($file!="." and $file!="..") {
			if (is_file($p."/".$file)) {
				$weathermaps[$file]=$file;
			}
		}
	}
	closedir($dir);

	foreach ($weathermaps as $map) {
	  print "-Preprocessing ".$map."\n";
	  
	  print "  Reading...\n";
	  $data=array();
	  $nodes=array();
	  $hosts=array();
	  $positions=array();
	  $labels=array();
	  $end=array();
	  $current_node="";
      $filename = $p.'/'.$map;
      if (is_readable($filename)) {
        $i=0;
        $lines = file ($filename);
        foreach ($lines as $line) {
          $data[$i]=$line;

		  if (substr($line, 0, 4 ) == "NODE") {
		    print "    found NODE at ".$i."\n";
			$current_node=$i;
			$nodes[$i]=$i;
			$tmp=substr($line, 5, 20);
		  }

		  $pos = strpos($line, "LABEL ");
		  if ($pos === false) {
            //
		  } else {
			$tmp=substr($line, $pos+6, 20);
		    $pos2 = strpos($tmp, "\n");
		    if ($pos === false) {
              //
		    } else {
			  if (($i-$current_node) < 5) {
		        print "      found label ";
			    $tmp=substr($tmp, 0, $pos2);
			    print $tmp."\n";
			    $labels[$current_node]=$tmp;
				$end[$current_node]=$i;
			  }
            }	
          }
		  
		  $pos = strpos($line, "local_graph_id=");
		  if ($pos === false) {
            //
		  } else {
			$tmp=substr($line, $pos+15, 4);
		    $pos2 = strpos($tmp, "&");
			if ($pos2 == "") {
			  $pos2 = strpos($tmp, "\n");
			}
		    if ($pos === false) {
              //
		    } else {
			  if (($i-$current_node) < 5) {
		        print "      found graph ";
			    $tmp=substr($tmp, 0, $pos2);
			    $host_id=db_fetch_cell("select host_id from graph_local where id='".$tmp."'");
			    print " -> host ".$host_id."\n";
			    $hosts[$current_node]=$host_id;
				$end[$current_node]=$i;
			  }
            }	
          }
		  
		  $pos = strpos($line, "POSITION ");
		  if ($pos === false) {
            //
		  } else {
			$tmp=substr($line, $pos+9, 20);
		    $pos2 = strpos($tmp, "\n");
		    if ($pos === false) {
              //
		    } else {
			  if (($i-$current_node) < 5) {
		        print "      found position ";
			    $tmp=substr($tmp, 0, $pos2);
			    print $tmp."\n";
			    $positions[$current_node]=$tmp;
				$end[$current_node]=$i;
			  }
            }	
          }
		  
	      $i++;
        }
      }
	  print "  done\n";

	  print "  -> valid nodes are ";
      foreach ($nodes as $node) {
	    if (!isset($hosts[$node])) {
          unset ($nodes[$node]);
		} else {
		  print $nodes[$node]." ";
		}
	  }
      print ("\n");
	  
	  print "  Writing...\n";
	  $i=0;
	  $limit=-1;
	  $over=0;
	  $info=0;
	  $current_node="";
	  $new_map = $p."/newmaps/".$map;
      $FileHandle = fopen($new_map, 'w') or die("can't open file");
	  $o="\n";
	  foreach ($lines as $line) {
	    foreach ($nodes as $node) {
		  if ($i == $node) {
			$limit=$end[$i];
			$current_node=$i;

			$coord=split(" ", $positions[$current_node]);
			$c=strlen($labels[$current_node]);
			$x=intval($coord[0]-(($c/2)*9+9));
			$y=intval($coord[1]);
			
			$statut = db_fetch_cell("select statut from manage_host where id='".$hosts[$current_node]."'");
			$o .= "NODE statut".$current_node."\n";
			$o .= "\tICON ".$config["base_path"]."/plugins/manage/images/themes/".$theme."/".$statut.".png\n";
			$o .= "\tINFOURL ".$config["url_path"] . "plugins/manage/manage_viewalerts.php?id=".$hosts[$current_node]."\n";

			$o .= "\tRIGHTOVERLIB &lt;a href=" . $config["url_path"] . "host.php?action=edit&id=" . $hosts[$current_node] . "&gt;Management -&gt; Devices&lt;/a&gt;&lt;br&gt;";

			$o .= "&lt;a href=" . $config["url_path"] . "plugins/manage/manage_viewalerts.php?edit=1&id=" . $hosts[$current_node] . "&gt;Device Managing -&gt; Event Reporting&lt;/a&gt;&lt;br&gt;";
	
	        $email=db_fetch_cell("select mail from manage_host where id='".$hosts[$current_node]."'");
	        $mails = explode(",", $email);
            $tmp = "Mail to: ";
	        if (count($mails) < 2) {
	          $tmp .= "&lt;a href=mailto:".$email."&gt;".$email."&lt;/a&gt;";
	        } else {
              foreach ($mails as $mail) {
                $tmp .= "&lt;a href=mailto:".$mail."&gt;".$mail."&lt;/a&gt; ";
              }
	        }
			$o .= $tmp."&lt;br&gt;";
		
			$note=db_fetch_cell("select notes from host where id='".$hosts[$current_node]."'");
			$o .= "Notes: ".$note."\n";
			
			$o .= "\tPOSITION ".$x." ".$y."\n\n";

		  }
		}

		if ($i < $limit) {
	      $replacing=1;
		} else {
		  $replacing=0;
		}
		
		$pos = strpos($line, "OVERLIBGRAPH");
		if ($pos === false) {
          $over=0;
		} else {
		  $over=1;
		  if ($replacing == 1) {
		    print "    Patching ".$current_node."...\n";
		    fwrite($FileHandle, "\tOVERLIBGRAPH ".$config["url_path"] . "plugins/manage/manage_weathermap_gd.php?id=".$hosts[$current_node]."\n");
		  } else {
		    fwrite($FileHandle, $line);
		  }
		}
		
		if ( ($over == 0) && ($info == 0) ) {
		  fwrite($FileHandle, $line);
		}
		$i++;
	  }
	  print "    Adding new nodes...\n";
	  fwrite($FileHandle, $o);
      fclose($FileHandle);
	  print "  done\n";
	  
	  copy($config["base_path"]."/plugins/manage/weathermaps/newmaps/".$map, $config["base_path"]."/plugins/weathermap/configs/".$map);
	  print "done\n";
	}

?>
