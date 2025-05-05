/*******************************************************************************

    Author ......... Gilles Boulon
    Home Site ...... http://forums.cacti.net/viewtopic.php?t=13827
    Program ........ PHP Network Managing
    Version ........ 0.6.2
    Purpose ........ Cacti dashboard for Network Services

*******************************************************************************/


----[ Purpose

    This plugin allows you to automatically view your network by checking :
	- TCP Ports
	- Processes
	- Services
	
    Up, down and reboot events :
	- can be immediatly sent to you by email, "net send", syslog/NT event viewer or SNMP trap
	- are logged and available in Event Reporting


----[ Features

    Monitor TCP ports, windows services and processes for multiple hosts.
    Multiples views.
    You can associate an image with your host.
    Use his own groups or those from Tree.
    You can display only hosts in error.
    Use AJAX.
    Use overlib to display graphs.
    Blink hostnames when a threshold is breaked.
    Use tresholds.
    You can set cycle time delay between groups.
    The time until the next graph change is displayed.
    You can click objects to access Reporting.
	Let you open remote shell for each TCP port.
    Guest account can only view tab manage, not Reporting.
    You can choose between the default Cacti output or force SNMP, WMI (Vbs or Perl).
    You can make themes for images.
    Alerts by sound, mail, poput, syslog or SNMP trap.
    Use the plugin Update for checking updates.
	Daily report.

----[ Installation

    Read #INSTALL.txt.
    

----[ Bugs
   
    SNMP agent for Win32 is not perfect, so WMI is better.
    The Cacti poller only use first 155 caracters for the oid.
    When you first log on manage tab (without user settings set), apply your settings.
    Text alignement in tree view.

	
----[ Future Changes
    
    Got any ideas then please let me know.


----[ Changelog

     --- 0.6.2 --- (20/10/2010)
	  BUGS RESOLVED :
			- manage_lib.php : compatibility with cacti 0.8.7g and PIA 2.9b2
			- manage_ajax.php : bug in connect link in full view
			- setup.php :
				- bug in hook chain (function manage_device_action_prepare)
				- /cacti/ hardcoded in javascript (and one thing from my production platform too)
			- manage.php : /cacti/ hardcoded in javascript
			- manage_accounts.php : /cacti/ hardcoded in javascript
			- manage_iframe.php : /cacti/ hardcoded in javascript
			- manage_settings.php : /cacti/ hardcoded in javascript

     --- 0.6.1 --- (23/02/2009)
	  BUGS RESOLVED :
			- setup.php : error on refresh delay when you're not on manage tab
			- setup.php : patch for include manage_lib.php
			- manage_ajax.php : url error on links for TCP ports in full view
			- manage_ajax.php : only enabled treshold are displayed
			- manage_check.php : patch for false reboot alert
			
     --- 0.6 --- (15/03/2008)
	  ADDONS
			- remote access to the device from "Console -> Devices", "Console -> Devices -> (Edit)" and tab Manage
			- only 2 first lines of notes are now displayed
			- email cells now support cacti user id
			- for supported platform, can check snmp host uptime rather than snmp agent uptime

	  BUGS RESOLVED :
		    - manage_ajax.php : URISCHEME and Java available in "full view"
			- error when reading realm from PA tables
			- manage_settings : typo error (statut -> status)
			- manage_groups.php, manage.php : corrected short tag (<? -> <?php)
			- direct linking is now working
			- setup.php : bug with $_SERVER["SERVER_ADDR"] when displaying cacti server IP
			- manage_check.php : updated ping parameters
			
     --- 0.5.2 --- (26/03/2008)
	  ADDONS
			- Tree view (support Cacti realm permissions)
			- code for use URISCHEME or Java applet from http://www.javassh.org
		    - use Plugin Updates to check version
			- Tab size can be specified
			- can send alerts, "net send" , syslog or SNMP trap message for all events
			- MotD
			- ability to disable Manage and display a warning message in this case
			- weathermaps preprocessing
			- output from the Cacti poller
			- use Plugin Settings for sending emails
			- settings are now "per user"
			- security options : admins can disable settings, menu and tab access...
			- daily reports

	  BUGS RESOLVED :
		    - manage_check.php :
			      - error when a service name contains a double space ("  ")
		          - error on thold_mail
				  - mail separator is ','
				  - argv and argc are not recognized in certain case (see php.ini)
				  - ping function correction
		    - manage_groups.php :
		          - "orphean" groups are deleted when you delete a site
			- manage.php :
				  - bug when you use "view errors only" in List view
		    - manage_ajax.php :
		          - function bcmod not available in PHP < v5
			- setup.php :
		          - function "manage_poller_output" rewrited
		          - use the hook "poller_top"
				  - mail separator is ','
				  - error in sql statement 'CREATE TABLE manage_host_services...'
			- manage_types.php : deleted
			- all functions have been preceded by "manage_"
			- 4-upgrade-0.3-0.4.sql have been corrected (missing ;)
				  
     --- 0.5.1 --- (22/04/2007)
	  BUGS RESOLVED :
		    - setup.php :
		          - function manage_setup_table was not performing upgrade 0.5 from a fresh install
		    - debug.php :
		          - error in the debug link from linux: "\" replaced with "/"
				  
     --- 0.5 --- (21/04/2007)
	  ADDONS
		    - use AJAX (no more full page reload), thanks Matt Emerick-Law, author of Cycle Plugin
		    - now, use TCP Port Template, no more hardcoded code : http://forums.cacti.net/about16477.html
		    - you can choose between SNMP, WMI (Vbs or Perl) and "rrdtool" (added "Manage performance" view in tab manage)
		    - on all view, you must click on the image for the host status to access Reporting
		    - on all view, right click on the image for the host status to access shortcuts
		    - notion of site
		    - debug page in Misc/settings
		    - you can click columns names to help sorting
		    - tresholds
		    - tab color change (code from twelzy (Enio Sanches), http://forums.cacti.net/viewtopic.php?t=19256)
		    - themes
		    - sound
		    - private/global emails
		
	  BUGS RESOLVED :
		    - check.php :
		          - errors in function logger (adds from 0.4.2)
		          - function logger redeclared (bug with Thold 0.3.1) : function renamed to manage_logger
		    - list.php :
		          - now delete process and services tables when you delete a host
		          - suppress dev bug (print "1")
		          - strripos function replaced because it is not implemented in php 4
		    - manage.php & ajax.php :
		          - changes to saves all settings (view, group and errors)
		          - don't display TCP ports for disabled hosts
		    - manage_ajax.php :
		          - prevent services and process colums names to display when host is disabled
		    - manage_list.php :
		          - SQL errors for a fresh device

     --- 0.4.2 --- (30/08/2006)
	  ADDONS
		    - saves all settings (view, group and errors)
		
	  BUGS RESOLVED
		    - check.php : adds to function logger
		    - check-manage.php :
		          - changes to function go
		          - errors in function execInBackground (poller bug with linux/unix)
		    - setup.php : changes to function manage_show_tab ()
		    - manage.php : 
		          - change thold blinking
		          - changes to overlib (auto-width)
		    - list.php : error with "OS type" and "Force Method" option value for mass change
		  
     --- 0.4.1 --- (28/08/2006)
	  ADDONS
		    - added thold blinking
		
	  BUGS RESOLVED
		    - setup.php : errors in function manage_poller_bottom (exec_background)
		    - problem with 'manage_list_view' which was not set
		
     --- 0.4 --- (28/08/2006)
	  ADDONS
		    - added Windows Services Managing
		    - added Process Managing
		    - added "Net send" alerts
		    - you can use file "ports.inc" to map your specials ports to reals names
		    - you can use file "win_services.inc" to map windows services names to shorts names
		    - you can use file "process.inc" to map windows process names to shorts names
		    - added "List" view in tab manage
		    - added "List view" options in tab settings\misc
		    - added "Net send" options in tab settings\misc
		    - added "Use windows patch" options in tab settings\misc
		    - you can select a default view
		    - guest account can view tab manage (but can't access host	events/reporting)
		    - added overlib graphs : overlib.js is copyright Erik Bosrup, http://www.bosrup.com/web/overlib/?License
		    - you can use multiple Manage Poller
		    - legend
		    - listmanage2.php : renamed to list.php
		    - manage_templates.php : renamed to templates.php
		    - viewalerts.php : has been "rollbacked" to version 0.2

	  BUGS RESOLVED
		    - manage-0.1.sql : sql error in insert (character ')')
		    - upgrade-0.1-0.2.sql : sql error in alter table (character ' changed to `)
		    - list.php :
		          - when you stop managing a device, suppressing of alerts wasn't effective
		          - floor function to caculate uptime
		          - port wasn't selected when you select a template

	 --- 0.3.2 --- (?????)
	  BUGS RESOLVED
            - error in a fresh install
            - listmanage2.php
            - in a certain circonstance, the back button has inserted a second time the host

	 --- 0.3.1 --- (15/06/2006)
	  BUGS RESOLVED
            - groups.php on line 174 
            - listmanage2.php on line 977 
		
     --- 0.3 --- (14/06/2006)
	  ADDONS
	        - groups
	        - mass changes
	  BUGS RESOLVED
	        - manage.php : error on line 232
	        - manage view screen : it is always 1 short
	        - manage_templates.php : error on line 1364
		
     --- 0.2 --- (02/06/2006)
	  ADDONS
	        - added Device types 
	        - added Templates 
	        - you can monitor a host by icmp or snmp individually (force) 
	        - items are clickable (you must setup Host Tree Item type for each managed host)
	  BUGS RESOLVED
	        - check-tcp.php : fsokopen error (@)
	        - manage.php : error on line 219
	        - manage.php : error on line 326 (second edition)
		
     --- 0.1 --- (29/05/2006)
	  ADDONS
  	        - full view
	        - simple view
	        - reporting


----
PHP Network Managing contains components from other software developers:

overlib.js is part of Overlib 4.21, copyright Erik Bosrup 1998-2004. All rights reserved.
See http://www.bosrup.com/web/overlib/?License



