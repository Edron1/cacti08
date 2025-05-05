<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

if ($host_statut == "up") {

        $p = dirname(__FILE__);
		
		$tcp_options = db_fetch_assoc("SELECT data_local.id, data_local.data_template_id, data_local.snmp_index FROM data_local, data_template WHERE data_local.host_id = '".$host["id"]."' AND data_local.data_template_id = data_template.id AND data_template.name = 'TCP'");

		$cacti_ports=array();
		$nb_cacti_ports=0;
		foreach ($tcp_options as $option) {
		    $stat = db_fetch_cell("SELECT statut FROM `manage_tcp` where services='" . $option["snmp_index"] . "' and id='".$host["id"]."'");
			if ($stat == "") {
			  $stat="nopoll";
			}
		
			print " + port " . $option["snmp_index"] . ", was " . $stat;

			if ($method == "rrdtool") {
				$rrd_values = db_fetch_assoc("SELECT manage_poller_output.output, data_local.id, data_local.data_template_id, data_local.snmp_index FROM data_local, data_template, manage_poller_output WHERE data_local.host_id = '".$host["id"]."' AND data_local.data_template_id = data_template.id AND data_template.name = 'TCP' AND manage_poller_output.local_data_id = data_local.id AND data_local.snmp_index = '".$option["snmp_index"]."'");
				$r2 = "down";

				foreach ($rrd_values as $value) {
				  print " (Data_local_id is : ".$value['id'].")\n";
				  if ($value["output"] == "1") {
					$r2 = "up";
				  }
				}

			}
			
			if ( ($method == "snmp") || ($method == "wmi_vbs") || ($method == "wmi_perl") ) {
				$socket = @fsockopen(strtolower($host["hostname"]), $option["snmp_index"], $error_number, $error, 5);

				if (!$socket) {
					$r2="down";
				} else {
					$r2="up";
					fclose ($socket);
				}
			}

			if ($r2 == 'down') {
				$problem_detected = "1";
			}
			
			if ($full_reporting == "1") {
			  $report .= "Port ".$option["snmp_index"]." : ".strtoupper($r2)."<br>";
			}

			$update="0";
			if ($r2 == $stat) {
			  print " -> no change\n";
			} else {
			  print " -> change (".$r2.")\n";
			  $update="1";
		      manage_logger($option["snmp_index"], $host["id"], $r2, "Service is ", $host["hostname"], $host["description"], $old_uptime, $new_uptime, "port", "", $stat, $event_generated, "");
			}
			
			if ( ($full_writing_to_db == "1") || ($update == "1") ) {
				//met à jour le statut
				$ishere = db_fetch_cell("SELECT count(id) FROM `manage_tcp` where id='" . $host["id"] . "' and services='" . $option["snmp_index"] . "'");
				if ($ishere == "0") {
				  db_execute("INSERT INTO manage_tcp ( id , services , statut ) VALUES ('" . $host["id"] . "', '" . $option["snmp_index"] . "', '".$r2 . "')");
				} else {
				  db_execute("UPDATE manage_tcp SET statut = '" . $r2 ."' where id='" . $host["id"] . "' and services='" . $option["snmp_index"] . "'");
				}
			}
			
			
			
			$cacti_ports[$nb_cacti_ports]=$option["snmp_index"];
			$nb_cacti_ports++;
		} //end foreach

		print " Updating TCPs... (";
		if ($nb_cacti_ports == 0) {
		  db_execute("delete FROM `manage_tcp` where id='" . $host["id"] . "'");
		} else {
		
		db_execute("delete FROM `manage_tcp` where id='" . $host["id"] . "' and services NOT IN (SELECT data_local.snmp_index FROM data_local, data_template WHERE data_local.host_id = '".$host["id"]."' AND data_local.data_template_id = data_template.id AND data_template.name = 'TCP')");
		
		/*
		$manage_ports = db_fetch_assoc("SELECT * from manage_tcp where id='" . $host["id"] . "'");
		foreach ($manage_ports as $port) {
		  $flag_port=0;
          for ($counter=0;$counter<count($cacti_ports);$counter++) {
		    if ($port['services']  == $cacti_ports[$counter]) {
			  $flag_port=1;
			}
		  }

		  if ($flag_port == 0) {
			  db_execute("delete FROM `manage_tcp` where id='" . $host["id"] . "' and services ='".$port['services']."'");
		    print $port['services']." ";
		  }
			
		}
		*/
		
		
		
		}
		print ")\n";
		
		////////////////////////////////////////////////////////////////////

		$services_options = db_fetch_assoc("select data_input_data.value, data_local.snmp_index from data_input_data, data_template_data, data_local, data_template where data_input_data.data_input_field_id=(SELECT distinct(data_input_fields.id) FROM data_input, data_input_fields where data_input.type_id = 6 and data_input_fields.data_input_id=data_input.id and data_input_fields.name='Index Value') and data_input_data.data_template_data_id=data_template_data.id and data_template_data.local_data_id=data_local.id and data_template_data.data_template_id=data_template.id and data_template.name='Win Services' and data_local.host_id='".$host["id"]."'");

		$cacti_services=array();
		$nb_cacti_services=0;

		foreach ($services_options as $option) {
		
		  $service_name_virtual = strtolower(str_replace("  ", " ", $option["value"]));
		  
		  /*
		  $service_name="";
		  $tmp = explode(" ", strtolower($option["value"]));
		  foreach ($tmp as $p_tmp) {
		    if (strlen($p_tmp) > 2) {
			  $service_name=$service_name."name like '%".$p_tmp."%' and ";
		    }
		  }
		  */

			$service_memory = db_fetch_cell("SELECT `oid` FROM manage_services where name like '%".$service_name_virtual."%' and id='".$host["id"]."'");
			if ($service_memory == "") {
			  $service_memory=$option["snmp_index"];
			}
			
		    $stat = db_fetch_cell("SELECT `statut` FROM manage_services where name like '%".$service_name_virtual."%' and id='".$host["id"]."'");
			if ($stat == "") {
			  $stat="nopoll";
			}
			print " + service '" . $service_name_virtual . "', was " . $stat;

			if ($method == "rrdtool") {

			/*
			  //cacti bug, sometimes services name contain a double-space ("  ") which is not correctly interpreted by db_fetch_assoc
              $tmp = explode("  ", $option["value"]);
              $o="";
              foreach ($tmp as $p_tmp) {
                $o .= " AND lower(data_input_data.value) like '%".strtolower($p_tmp)."%'";
              }
*/

			  $rrd_values = db_fetch_assoc("select manage_poller_output.local_data_id, manage_poller_output.output, data_input_data.value, data_local.snmp_index from data_input_data, data_template_data, data_local, data_template, manage_poller_output where data_input_data.data_input_field_id=(SELECT distinct(data_input_fields.id) FROM data_input, data_input_fields where data_input.type_id = 6 and data_input_fields.data_input_id=data_input.id and data_input_fields.name='Index Value') and data_input_data.data_template_data_id=data_template_data.id and data_template_data.local_data_id=data_local.id and data_template_data.data_template_id=data_template.id and data_template.name='Win Services' and data_local.host_id='".$host["id"]."' AND data_local.id = manage_poller_output.local_data_id");
				$r2 = "down";
                
				foreach ($rrd_values as $value) {
				
				  $tmp = strtolower(str_replace("  ", " ", $value['value']));
				  if ($service_name_virtual == $tmp) {
				
					print " (Data_local_id is : ".$value['local_data_id'].")\n";
				    if ($value["output"] == "1") {
						$r2 = "up";
					}
					
				  }
				}

			}

			if ($method == "snmp") {

				if ($config["cacti_server_os"] == "unix") {
					$command_string = db_fetch_cell("select value from settings where name='path_php_binary'");
					$extra_args = "-q " . $config["base_path"] . "/scripts/win_services.php " . $host["hostname"] . " " . $host["snmp_community"] . " " . $host["snmp_version"] . " " . $host["id"] . " get servstate " . $option["snmp_index "];
				}else{
					$command_string = db_fetch_cell("select value from settings where name='path_php_binary'");
					$extra_args = "-q " . strtolower($config["base_path"] . "/scripts/win_services.php " . $host["hostname"] . " " . $host["snmp_community"] . " " . $host["snmp_version"] . " " . $host["id"] . " get servstate " . $option["snmp_index"] );
				}

				$r2=exec($command_string." ".$extra_args);
				if ($r2 == "1") {
					$r2 = "up";
				} else {
					$r2 = "down";
				}
			}

			if ($method == "wmi_vbs") {
				$command_string = "cscript.exe /nologo";
				$extra_args = $p . '/wmi/services.vbs '.$host["hostname"].' "'.$option["value"].'"';
			}

			if ($method == "wmi_perl") {
				$command_string = db_fetch_cell("select value from settings where name='manage_perl'");
				$extra_args = $p . '/wmi/services.pl '.$host["hostname"].' '.strtolower($option["value"]);
			}
			
            //case of access denied
			if ( ($method == "wmi_vbs") || ($method == "wmi_perl") ) {
				$r2=exec($command_string." ".$extra_args);
			    if ($r2 == "") {
					$r2="nopoll";
				}
			}
			
			if ( ($r2 == 'down') || ($r2 == 'nopoll') ) {
				$problem_detected = "1";
			}

			
			
			if ($full_reporting == "1") {
			  $report .= "Service ".$service_name_virtual." : ".strtoupper($r2)."<br>";
			}
			
			$update="0";
			if ($r2 == $stat) {
			  print " -> no change\n";
			} else {
			  print " -> change (".$r2.")\n";
			  $update="1";
			  manage_logger($service_name_virtual, $host["id"], $r2, "Service is ", $host["hostname"] , $host["description"], $old_uptime, $new_uptime, "win_services", $service_memory, $host["statut"], $event_generated, "");
			}
			
			if ( ($full_writing_to_db == "1") || ($update == "1") ) {
				//met à jour le statut
				$ishere = db_fetch_cell("SELECT count(id) FROM `manage_services` where name like '%".$service_name_virtual."%' and id='".$host["id"]."'");
				if ($ishere == "0") {
				  db_execute("INSERT INTO manage_services ( id , name , oid , statut ) VALUES ('" . $host["id"] . "', '" . $service_name_virtual . "', '".$option["snmp_index"]."', '" . $r2 . "')");
				} else {
				  db_execute("UPDATE manage_services SET statut = '" . $r2 ."' where id='" . $host["id"] . "' and name like '%" . $service_name_virtual . "%'");
				}
			}
			
			
			
			
			
			
			
			
            $cacti_services[$nb_cacti_services]=strtolower($option["value"]);
			$nb_cacti_services++;
			
		} //end foreach

        print " Updating services... (";

		if ($nb_cacti_services == 0) {
		  db_execute("delete FROM `manage_services` where id='" . $host["id"] . "'");
		} else {

		  //db_execute("delete FROM `manage_services` where id='" . $host["id"] . "' and name NOT IN (select lower(data_input_data.value) from data_input_data, data_template_data, data_local, data_template where data_input_data.data_input_field_id=(SELECT distinct(data_input_fields.id) FROM data_input, data_input_fields where data_input.type_id = 6 and data_input_fields.data_input_id=data_input.id and data_input_fields.name='Index Value') and data_input_data.data_template_data_id=data_template_data.id and data_template_data.local_data_id=data_local.id and data_template_data.data_template_id=data_template.id and data_template.name='Win Services' and data_local.host_id='".$host["id"]."')");
		  
	
		$manage_services = db_fetch_assoc("SELECT * from manage_services where id='" . $host["id"] . "'");
		foreach ($manage_services as $service) {
		  $flag_service=0;
          for ($counter=0;$counter<count($cacti_services);$counter++) {
		    $tmp = strtolower(str_replace("  ", " ", $cacti_services[$counter]));
		    if ($service['name']  == $tmp) {
			  $flag_service=1;
			}
		  }

		  if ($flag_service == 0) {
			  db_execute("delete FROM `manage_services` where id='" . $host["id"] . "' and name ='".$service['name']."'");
		    print $service['name']." ";
		  }
			
		}

		
		
		
		
		}
		print ")\n";
		  
		  
		  

		////////////////////////////////////////////////////////////////////////////////////////////////////////

		
		$cacti_process=array();
		$nb_cacti_process=0;

		if ($method == "snmp") {
            $arr_proc = cacti_snmp_walk($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.25.4.2.1.2", $host["snmp_version"], $host["snmp_username"], $host["snmp_password"], $host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"], $host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], read_config_option("snmp_retries"),SNMP_WEBUI);
		}

		$process_options = db_fetch_assoc("SELECT * FROM data_input_data, data_template_data, data_template, data_local WHERE data_template.name like '%Running Process Info%' and data_input_data.data_input_field_id='13' and data_template_data.id=data_input_data.data_template_data_id and data_template.id=data_template_data.data_template_id AND data_local.id=data_template_data.local_data_id and data_local.host_id='".$host["id"]."'");
			
		foreach ($process_options as $process) {
		  $stat = db_fetch_cell("SELECT statut FROM `manage_process` where name='" . strtolower($process['value']) . "' and id='".$host["id"]."'");
		  if ($stat == "") {
			$stat="nopoll";
		  }
		  print " + process '" . strtolower($process['value']) . "', was " . $stat;

		  if ($method == "rrdtool") {
			$rrd_values = db_fetch_assoc("SELECT * FROM data_template_data, data_template, data_local, manage_poller_output WHERE data_template.name LIKE '%Running Process Info%' AND data_template.id = data_template_data.data_template_id AND data_local.id = data_template_data.local_data_id AND data_local.host_id = '" . $host["id"] . "' AND data_template_data.name_cache LIKE '%".strtolower($process['value'])."%' AND manage_poller_output.local_data_id = data_local.id");
			$r2 = "down";

			foreach ($rrd_values as $value) {
			  print " (Data_local_id is : ".$value['local_data_id'].")\n";
			  if ($value["output"] != "U") {
				$r2 = "up";
			  }
			}

		  }

		  if ($method == "snmp") {

		    for ($i = 0; $i < sizeof($arr_proc); $i++) {
			  if (strtolower($arr_proc[$i]["value"]) == strtolower($process['value'])) {
			    $r2 = "up";
			  }
			}

		  }

		  if ($method == "wmi_vbs") {
			$command_string = "cscript.exe /nologo";
			$extra_args = $p . '/wmi/process.vbs '.$host["hostname"].' "'.strtolower($process['value']).'"';
		  }

		  if ($method == "wmi_perl") {
			$command_string = db_fetch_cell("select value from settings where name='manage_perl'");
			$extra_args = $p . '/wmi/process.pl '.$host["hostname"].' "'.strtolower($process['value']).'"';
		  }

          //case of access denied
		  if ( ($method == "wmi_vbs") || ($method == "wmi_perl") ) {
			$r2=exec($command_string." ".$extra_args);
			if ($r2 == "") {
			  $r2="nopoll";
			}
		  }
			
		  if ( ($r2 == 'down') || ($r2 == 'nopoll') ) {
			$problem_detected = "1";
		  }
					
					
					
		  if ($full_reporting == "1") {
			$report .= "Process ".strtolower($process['value'])." : ".strtoupper($r2)."<br>";
		  }
			
		  $update="0";
		  if ($r2 == $stat) {
			print " -> no change\n";
		  } else {
			print " -> change (".$r2.")\n";
			$update="1";
			manage_logger(strtolower($process['value']), $host["id"], $r2, "Process is ", $host["hostname"] , $host["description"], $old_uptime, $new_uptime, "win_process", strtolower($process['value']), $host["statut"], $event_generated, "");
		  }
			
		  if ( ($full_writing_to_db == "1") || ($update == "1") ) {
			//met à jour le statut
			$ishere = db_fetch_cell("SELECT count(id) FROM `manage_process` where name='" . strtolower($process['value']) . "' and id='".$host["id"]."'");
			if ($ishere == "0") {
			  db_execute("INSERT INTO manage_process ( id , name , tag , statut ) VALUES ('" . $host["id"] . "', '" . strtolower($process['value']) . "', '".strtolower($process['value'])."', '" . $r2 . "')");
			} else {
			  db_execute("UPDATE manage_process SET statut = '" . $r2 ."' where id='" . $host["id"] . "' and name='" . strtolower($process['value']) . "'");
			}
		  }
			
			
					
					
          $cacti_process[$nb_cacti_process]=strtolower($process['value']);
		  $nb_cacti_process++;
		} //end foreach

        print " Updating Processes... (";

		if ($nb_cacti_process == 0) {
		  db_execute("delete FROM `manage_process` where id='" . $host["id"] . "'");
		} else {

		
		db_execute("delete FROM `manage_process` where id='" . $host["id"] . "' and name NOT IN (SELECT lower(data_input_data.value) FROM data_input_data, data_template_data, data_template, data_local WHERE data_template.name like '%Running Process Info%' and data_input_data.data_input_field_id='13' and data_template_data.id=data_input_data.data_template_data_id and data_template.id=data_template_data.data_template_id AND data_local.id=data_template_data.local_data_id and data_local.host_id='".$host["id"]."')");
		
		/*
		  $manage_process = db_fetch_assoc("SELECT * from manage_process where id='" . $host["id"] . "'");
		  foreach ($manage_process as $process) {
		    $flag_process=0;
            for ($counter=0;$counter<count($cacti_process);$counter++) {
		      if ($process['name']  == $cacti_process[$counter]) {
			    $flag_process=1;
			  }
		    }

		    if ($flag_process == 0) {
			  db_execute("delete FROM `manage_process` where id='" . $host["id"] . "' and name ='".$process['name']."'");
		      print $process['name']." ";
		    }
			
		  }
		  */
		  
		  
		
		}
		print ")\n";
		  

}



if ($host_statut == "down") {

		$tcp_options = db_fetch_assoc("SELECT data_local.id, data_local.data_template_id, data_local.snmp_index FROM data_local, data_template WHERE data_local.host_id = '".$host["id"]."' AND data_local.data_template_id = data_template.id AND data_template.name = 'TCP'");

		foreach ($tcp_options as $option) {
		
		    if ($full_reporting == "1") {
			  $report .= "Port ".$option["snmp_index"]." : DOWN<br>";
			}
			
		      manage_logger($option["snmp_index"], $host["id"], "down", "Service is ", $host["hostname"], $host["description"], "", "", "port", "", "", $event_generated, "");

			if ($full_writing_to_db == "1") {
				//met à jour le statut
				$ishere = db_fetch_cell("SELECT count(id) FROM `manage_tcp` where id='" . $host["id"] . "' and services='" . $option["snmp_index"] . "'");
				if ($ishere == "0") {
				  db_execute("INSERT INTO manage_tcp ( id , services , statut ) VALUES ('" . $host["id"] . "', '" . $option["snmp_index"] . "', 'down')");
				} else {
				  db_execute("UPDATE manage_tcp SET statut = 'down' where id='" . $host["id"] . "' and services='" . $option["snmp_index"] . "'");
				}
			}
			
		}
		
		
		
		
		
		$services_options = db_fetch_assoc("select data_input_data.value, data_local.snmp_index from data_input_data, data_template_data, data_local, data_template where data_input_data.data_input_field_id=(SELECT distinct(data_input_fields.id) FROM data_input, data_input_fields where data_input.type_id = 6 and data_input_fields.data_input_id=data_input.id and data_input_fields.name='Index Value') and data_input_data.data_template_data_id=data_template_data.id and data_template_data.local_data_id=data_local.id and data_template_data.data_template_id=data_template.id and data_template.name='Win Services' and data_local.host_id='".$host["id"]."'");

		foreach ($services_options as $option) {
		
			if ($full_reporting == "1") {
			  $report .= "Service ".$option["value"]." : DOWN<br>";
			}
						
			  manage_logger(strtolower($option["value"]), $host["id"], "down", "Service is ", $host["hostname"] , $host["description"], "", "", "win_services", $option["snmp_index"], $host["statut"], $event_generated, "");

			  if ($full_writing_to_db == "1") {
				//met à jour le statut
				$ishere = db_fetch_cell("SELECT count(id) FROM `manage_services` where name='" . strtolower($option["value"]) . "' and id='".$host["id"]."'");
				if ($ishere == "0") {
				  db_execute("INSERT INTO manage_services ( id , name , oid , statut ) VALUES ('" . $host["id"] . "', '" . strtolower($option["value"]) . "', '".$option["snmp_index"]."', 'down')");
				} else {
				  db_execute("UPDATE manage_services SET statut = 'down' where id='" . $host["id"] . "' and name='" . strtolower($option["value"]) . "'");
				}
			}
			
		}
		
		
		
		
		$process_options = db_fetch_assoc("SELECT * FROM data_input_data, data_template_data, data_template, data_local WHERE data_template.name like '%Running Process Info%' and data_input_data.data_input_field_id='13' and data_template_data.id=data_input_data.data_template_data_id and data_template.id=data_template_data.data_template_id AND data_local.id=data_template_data.local_data_id and data_local.host_id='".$host["id"]."'");
		foreach ($process_options as $process) {
			if ($full_reporting == "1") {
			  $report .= "Process ".strtolower($process['value'])." : DOWN<br>";
			}

			  manage_logger(strtolower($process['value']), $host["id"], "down", "Process is ", $host["hostname"] , $host["description"], "", "", "win_process", strtolower($process['value']), $host["statut"], $event_generated, "");

			  if ($full_writing_to_db == "1") {
				//met à jour le statut
				$ishere = db_fetch_cell("SELECT count(id) FROM `manage_process` where name='" . strtolower($process['value']) . "' and id='".$host["id"]."'");
				if ($ishere == "0") {
				  db_execute("INSERT INTO manage_process ( id , name , tag , statut ) VALUES ('" . $host["id"] . "', '" . strtolower($process['value']) . "', '".strtolower($process['value'])."', 'down')");
				} else {
				  db_execute("UPDATE manage_process SET statut = 'down' where id='" . $host["id"] . "' and name='" . strtolower($process['value']) . "'");
				}
			}

		
		}

		
		
		

}

?>