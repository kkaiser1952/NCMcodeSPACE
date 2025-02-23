<?php
// index.php   for .space
// Development History:
// First written in 2015, Net Control Manager has been continuously enhanced and upgraded.
// Copyright: © 2015-2025 Keith Kaiser, WA0TJT
// Author: Keith Kaiser, WA0TJT, with contributions from many others. See the Help file for details. Contact at: wa0tjt@gmail.com 
// Current Version:
// v2 - utilizing updated PHP & MySQL

// The net created goes to: actLog
// Search for show/hide for sidebar button

/******************************************************************************
 
 Purpose:
Net Control Manager is a Create, Read, Update, Delete (CRUD) application designed for Amateur Radio operators to document net operations such as weather emergencies, club meetings, event support (e.g., bike rides), and other communication-intensive management needs.

Features:
The application offers a variety of reports, including station mapping, ICS-214, and ICS-309 forms, as well as other FEMA and DHS-compliant reporting options.

Disclaimer:
No Guarantees or Warranties. Except as expressly provided in this agreement, no party makes any guarantees or warranties of any kind, express or implied, including, but not limited to, any warranties of merchantability or fitness for a particular purpose, whether arising by operation of law or otherwise. Provider specifically disclaims any implied warranty of merchantability and/or any implied warranty of fitness for a particular purpose.

Help:
Extensive help documentation is available via the Help link in the upper-right corner of the main page.

 First written some time in mid to late 2015 and in continous enhancment and upgrade since.
 copyright 2015-2025 by: Keith Kaiser, WA0TJT 
 Written by: Keith Kaiser, WA0TJT, with the help of many others. See the help file for more details.
 I can be reached at wa0tjt at gmail.com
 
 The version number. this is v2 -> current PHP & MySQL
  
 How NCM works (for the most part, sorta, kinda):
 If a net is selected from the dropdown:

Net Selection: The list of nets (past 10 days) appears in #select1:
Green: Open nets
Blue: Pre-built nets
No color: Closed nets
Function: The selected net information is passed to showActivities().
Process:
buildUpperRightCorner.php retrieves and populates the upper right corner data.
getactivities.php retrieves the selected net’s data and populates #actLog.
If a new net is created:

Data Entry: Dropdown selections populate details for the new net.
User Identification: The callsign of the net starter is recorded.
Submission: Data is passed to newNet() in NetManager-p2.js.
Creation: newNet.php creates and populates entries in NetLog and TimeLog.
 
****************************************************************************************/

// Error settings
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set headers first
header('Content-Type: text/html; charset=UTF-8');
//header('Content-Type: text/event-stream');header('Cache-Control: no-cache');

// Include files
require_once "dbConnectDtls.php"; // Access to MySQL
require_once "wx.php"; // Weather info
require_once "NCMStats.php"; // Get some stats
include "sidebar.php"; // What columns will show in the UI

require_once "buildThreeDropdowns.php";
    $dropdownBuilder = new DropdownBuilder($db_found, new DatabaseLogger());
    $groupList = $dropdownBuilder->buildGroupList();
    $kindList = $dropdownBuilder->buildKindList();
    $freqList = $dropdownBuilder->buildFreqList();

require_once "buildSubNetCandidates.php";
    $subNets = buildSubNetCandidates($db_found);

require_once "buildOptionsForSelect.php";
    $selectOptions = buildOptionsForSelect($db_found);

    $weatherData = getWeatherInfo();

// End output buffering and flush
//ob_end_flush();
?>

<!doctype html>
<html lang="en" >
<head>
    
<!-- Preload some JS for quicker/easier access -->    
<link rel="preload" href="js/NetManager.js" as="script">
<link rel="preload" href="js/showActivities.js" as="script">
<link rel="preload" href="js/CellEditFunction.js" as="script">
<link rel="preload" href="js/handleSSE.js" as="script">


    <meta charset = "UTF-8" />
    
<title>Amateur Radio Net Control Manager</title>

    <!-- Essential Favicons for Browsers -->
    <!-- These icons cover standard browser needs across desktops and Android devices. -->
    <link rel="icon" type="image/png" sizes="16x16" href="favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="favicons/android-icon-512x512.png"> <!-- Suggested for high-resolution Android devices -->
    <link rel="icon" href="favicons/favicon.svg" type="image/svg+xml"> <!-- Suggested SVG favicon for high-DPI screens -->

    <!-- Apple Touch Icons -->
    <!-- Icons specific to Apple iOS devices, allowing users to add your app to their home screen. -->
    <link rel="apple-touch-icon" sizes="57x57" href="favicons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="favicons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="favicons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="favicons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-icon-180x180.png">

    <!-- Apple Touch Icons for Light and Dark Mode -->
    <!-- Optional icons for Apple devices that use dark mode. -->
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-icon-180x180-dark.png" media="(prefers-color-scheme: dark)"> <!-- Dark mode icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-icon-180x180.png" media="(prefers-color-scheme: light)"> <!-- Light mode icon -->

    <!-- Progressive Web App Manifest -->
    <!-- Used for defining the web app's appearance and behavior when installed on Android and supported platforms. -->
    <link rel="manifest" href="favicons/manifest.json">

    <!-- Microsoft Tiles and Theme Color -->
    <!-- These tags customize the appearance of your app’s icon on Windows devices, particularly in the Start menu and taskbar. -->
    
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="favicons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff"> <!-- Sets the color of the browser’s address bar (Android Chrome) -->

    
    <!-- Safari Pinned Tab Icon -->
    <!-- This icon is specifically for Safari’s pinned tabs on macOS, which display a monochrome version of the icon. -->
    <link rel="mask-icon" href="favicons/safari-pinned-tab.svg" color="#5bbad5"> <!-- Suggested for macOS Safari pinned tabs -->

    <!-- End all about favicon images -->
    
    <!-- Meta Tags -->
    <!-- Refresh the page every 9200 seconds (a bit over 2.5 hours) -->
    <meta http-equiv="refresh" content="9200; URL=https://net-control.space/help.php">
    
    <!-- Set the viewport for responsive design across all device sizes -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Description for SEO and social media previews -->
    <meta name="description" content="Amateur Radio Net Control Manager - A comprehensive tool for managing amateur radio nets.">
    
    <!-- Author information -->
    <meta name="author" content="Keith Kaiser, WA0TJT">
    
    <!-- Content rating, indicating the general audience suitability -->
    <meta name="rating" content="General">
    
    <!-- Sets the revisit frequency for search engine crawlers -->
    <meta name="revisit-after" content="1 month">
    
    <!-- Keywords for SEO -->
    <meta name="keywords" content="Amateur Radio Net, Ham Net, Net Control, Call Sign, NCM, Emergency Management Net, Amateur Radio, Ham Radio">

    <!-- =============== All The CSS Goes Here ================================= -->
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Allerta|Stoke|Cantora+One|Risque&display=swap">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon-32x32.png">
    
    <!-- =============== All above this should not be editied ================== -->
    
    <!-- All Update: 2024-11-09 -->
      
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" media="screen">
	<!-- consider this update 
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    -->		
    
	<!-- jQuery Modal (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.css">
    
    <!-- Bootstrap Select (local) -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap-select.min.css">
    
    <!-- jQuery UI (CDN) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    
    <!-- ============== My style sheets ============================================ -->
    
    <!-- Primary style sheet for NCM  -->
    <link rel="stylesheet" type="text/css" href="css/NetManager.css" > 
    <!-- Updated: 2018-1-17 -->  
    <link rel="stylesheet" type="text/css" href="css/tabs.css" >				
    
    <!-- ============== All the @media CSS stuff =================================== -->

    <link rel="stylesheet" type="text/css" href="css/NetManager-media.css">	
    <link rel="stylesheet" type="text/css" href="css/sidebar.css">
	 
<style>
/* Test CSS Here */
</style>

</head>

<body>

<header>
<!-- Upper left corner of opening page -->
<div class="openingImages">
    <!-- NCM and Net Control Manager images at top left of page -->
    <img id="smtitle" src="images/NCM.png" alt="NCM" >
    <img id="pgtitle2" src="images/NCM3.png" alt="NCM2" >
    
    <span id="version">
        <!-- V2 work in progress -->
    	<a href="https://groups.io/g/NCM" target="_blank">V2 About</a> 
    </span> <!-- End of id version -->
    
</div> <!-- End class openingImages -->
</header>

<!-- To hold the netID current value -->
<!-- Values supplied by ....     -->
<input type='hidden' id='currentNetID' value=''>

<div id="dttm"> <!-- flex container -->
    <div id="dttm1">
        <input type="radio" name="tz" id="theLocal" value="theLocal" size = "60" onclick="goLocal()">
        <br>
        <input type="radio" name="tz" id="theUTC" value="theUTC" onclick="goUTC()" >
    </div>

    <!-- To comment this function: comment setInterval('showDTTM()', 1000); in netManager-p2.js -->
	<div id="dttm2">
	</div>  
</div> <!-- end flex container -->


<div class="weather-place">
    <img src="images/US-NWS-Logo.png" alt="US-NWS-Logo" width="50" height="50" onclick="newWX()">
    <a href="https://www.weather.gov" class="theWX" target="_blank" rel="noopener">
        <?php
        if ($weatherData !== false) {
            echo "{$weatherData['location']}: {$weatherData['description']}, " .
                 "{$weatherData['temperature']}F, wind: {$weatherData['windDirection']} @ " .
                 "{$weatherData['windSpeed']}, humidity: {$weatherData['humidity']}%";
        } else {
            echo "Weather information unavailable";
        }
        ?>
    </a>
</div> <!-- End of class: weather-place -->

<!-- End of upper-left-corner stuff -->
		
    <br>
       
<nav>
<div id="rightCorner">    
<div id="upperRightCorner" class="upperRightCorner"> </div> <!-- Filled by buildUpperRightCorner.php -->

<div id="theMenu" class="theMenu">
    <table id="ourfreqs"> <!-- This is a bad name for this id, fix it someday -->
    <tbody>
     <tr class="trFREQS">
       <td class="nobg2" >
       
       <!-- Open the preamble for this net -->
       <a id="preambledev" onclick="openPreamblePopup();" title="Click for the preamble">Preamble &nbsp;||&nbsp;</a>
       					   
       <!-- Open the agenda and announcements for the current net -->
       <a id="agendadiv" onclick="openEventPopup()" title="Click for the agenda">Agenda &nbsp;||&nbsp;</a>
       
       <!-- Build a new preamble/closing or agenda items for the current net -->
       <a href="buildEvents.php" target="_blank" rel="noopener" title="Click to create a new preamble, agenda or announcment" class="colorRed" >New </a>&nbsp;||&nbsp;
    
       <!-- Open the closing for the current net -->
    	   <a id="closingdev" onclick="openClosingPopup()"  title="Click for the closing script">Closing &nbsp;||&nbsp;</a>
			       
   <!-- Open reports Dropdown of the available reports -->
   <span class="dropdown"> <!-- reports list dropdown -->
      <span class="dropbtn">Reports &nbsp;||&nbsp;</span>
    	  <span class="dropdown-content"> 
            	  
    	    <a href="#" id="theUsualSuspects" onclick="theUsualSuspects()" title="build a Call History By NetCall">The Usual Suspects</a>
    	  
    	    <a href="buildGroupList.php" target="_blank" rel="noopener" title="Group List">Groups Information</a>    
    	    <a href="groupScoreCard.php" target="_blank" rel="noopener" title="Group Scores">Group Score Card</a>
    	    <a href="listNets.php" target="_blank" rel="noopener" title="All the nets">List/Find ALL nets</a>
    
    	    <a href="#" onclick="net_by_number();" title="Net by the Number">Browse a Net by Number</a>
    		<a href="NCMreports.php" target="_blank" rel="noopener" title="Stats about NCM">Statistics</a>
    	        
    	    <a href="listAllPOIs.php" target="_blank" rel="noopener" id="listAllPoisLink" title="List all Pois">List all POIs</a>
            <a href="AddRF-HolePOI.php" target="_blank" rel="noopener" id="addRfHolePoiLink" title="Create New RF Hole POI">Add RF Hole POI</a>    
    	    
    	    <a href="#" id="geoDist" onclick="geoDistance()" title="GeoDistance">GeoDistance</a>
    
    	    <a href="#" id="mapIDs" onclick="map2()" title="Map This Net">Map This Net</a>
    	    
    	    <a href="https://vhf.dxview.org" id="mapdxView" target="_blank">DXView Propagation Map</a>
    	    
    	    <a href="https://www.swpc.noaa.gov" id="noaaSWX" target="_blank">NOAA Space Weather</a>
    	    
    	    <a href="https://spaceweather.com" id="SpaceWX" target="_blank">Space Weather</a>
     
    	    <a href="#" id="graphtimeline" onclick="graphtimeline()" title="Graphic Time Line of the active net">Graphic Time Line</a>
    
    		<a href="https://training.fema.gov/icsresource/icsforms.aspx" id="icsforms" target="_blank" rel="noopener">Addional ICS Forms</a>
    		
            <a href="https://docs.google.com/spreadsheets/d/1eFUfVLfHp8uo58ryFwxncbONJ9TZ1DKGLX8MZJIRZmM/edit#gid=0" target="_blank" rel="noopener" title="The MECC Communications Plan">MECC Comm Plan</a>
            
    		<a href="https://upload.wikimedia.org/wikipedia/commons/e/e7/Timezones2008.png" target="_blank" rel="noopener" title="World Time Zone Map">World Time Zone Map</a>
    		
    	  </span> <!-- End of class dropdown-content -->
      </span> <!-- End of dropbtn  or Reports -->
   </span> <!-- End of class dropdown -->
	
  	   <!-- Open the NCM help/instructions document -->
  	   <a id="helpdev" href="https://net-control.space/help.php" target="_blank" rel="noopener" title="Click for the extended help document">Help</a>&nbsp;||&nbsp;
		
  	   <!-- Alternate dropdown of the lesser needed reports -->
  	   <a href="#menu" id="bar-menu" class="gradient-menu"></a>
						  	   		
	  	   <!-- This select only shown if the three bar (hamburger-menu) is selected -->
	  	   <!-- bardropdown is in NetManager-p2.js -->
	  	   <select id="bardropdown" class="bardropdown hidden">
		   		<option value="SelectOne" selected="selected" disabled >Select One</option>
                <option value="convertToPB" >Convert to a Pre-Built (Roll Call) net.</option>
		   		<option value="CreateGroup">Create a Group Profile</option> 
		   
		   		<option value="HeardList">Create a Heard List</option>
                <option value="FSQList">Create FSQ Macro List</option>
		   		<option value="findCall">Report by Call Sign</option>
		   		
		   		<option value="allCalls">List all User Call Signs</option>
		   		<option value="DisplayHelp">NCM Documentation</option>
		   		<option value="DisplayKCARES">KCNARES Deployment Manual</option>
		   		
		   		<option value="" disabled >ARES Resources</option>
		   		<option value="ARESELetter" >ARES E-Letter</option>
		   		
		   		<option value="ARESManual">Download the ARES Manual(PDF)</option>
		   		<option value="DisplayARES">Download ARES Field Resources Manual(PDF)</option>
		   		<option value="ARESTaskBook"> ARES Standardized Training Plan Task Book [Fillable PDF]</option>
		   		
		   		<option value="ARESPlan">ARES Plan</option>
		   		<option value="ARESGroup">ARES Group Registration</option>
		   		<option value="ARESEComm">Emergency Communications Training</option>		
	  	   </select>
			       
	       </td> <!-- End div-nobg2 -->
	     </tr> <!-- This closes the only row in the ID: ourfreqs table -->
       </tbody>
       </table> <!-- End table-ourfreqs upper right corner-->
   </div> <!-- End id theMenu -->
</div> <!-- End id rightCorner -->
</nav>

<main>
        
 	<div id="org" class="hidden"></div> <!-- Used by putInGroupInput() in NetManager-p2.js  -->
    <div id="netchoice">
	<div id="netdata">
    	
    <!-- Use the <p> below to add a short message at the top of NCM. This span is hidden in NetManager.js , It's unhidden in the newnet() current in this file -->
	
	<p style="margin-bottom:20px; font-size: 14pt;">This is a work in progress, this upgrade to NCM not yet ready for use.<br>Please click here for a working verson; <a href="https://net-control.us">https://net-contro.us</p>
    	
            
    <!-- Start a new net or look at an old net -->        
	<div class="theBox">
		<!-- showit() in NetManager.js -->
		<button id="newbttn" class="newbttn left-cell tbb2" onclick="showit();" title="Click to start a new net">Start a new net</button>	
    
		<button id="by_number" style="left:25px;" class="newbttn" onclick="net_by_number();" title="Net by the Number">Browse a Net by Number</button>
		<br><br>	
	</div>
		
<div id="makeNewNet" class="hidden" >	
    		
    <div style="color: red;">* Required Field</div>
    		
    <br>
            
    <div><b style="color:red">*</b>Enter Your Call Sign:</div>   
    	<input onblur="checkCall()" type="text" required id="callsign" maxlength="16" name="callsign" autocomplete="on" title="Enter Your Call Sign" >
			     
    <!-- ==== GROUP ======= -->
    <div><b style="color:red">*</b>Select Group or Call:&nbsp;
        <!-- This is like an alert box but uses no javascript -->
        <a href="#GroupQ" class="Qbutton" tabindex="-1">?</a>
        
        <div class="NewLightbox" id="GroupQ">
            <figure>
                <a href="#" class="Qclose"></a>
                <figcaption>Filter the available group calls by typing the name. <br> For example: <b>MESN</b> <br><br> To create your own net simply type a name. <br> For example: <b>My Birthday</b> <br><br> Then in the Kind of Net selection below <br> consider choosing: <b>Event</b> or <b>Test</b>
    
                </figcaption>
            </figure>
        </div> <!-- End of class: NewLightbox -->
    </div> 
    
    <div id="GroupDropdown" >
        <!-- showGroupCoices() & filterFunctions() at the bottom of index.php -->
        <input type="text" onfocus="showGroupChoices()" placeholder="Type to filter list.." id="GroupInput" style="background-color:white;"
               class="netGroup"  onkeyup="this.value = removeSpaces(this.value); filterFunction(0);" required />
        <div class='GroupDropdown-content hidden'>
            
<?= $groupList; ?>    <!-- Created in buildThreeDropdowns.php -->
            
    </div> <!-- End GroupDropdown -->
        </div> <!-- End GroupDropdown-content -->
            
    <!-- ==== KIND ======= -->
    <div><b style="color:red">*</b>Select Kind of Net:&nbsp;&nbsp;&nbsp;
        <!-- This is like an alert box but uses no javascript -->
        <a href="#KindQ" class="Qbutton" tabindex="-1">?</a>
        <div class="NewLightbox" id="KindQ">
            <figure>
                <a href="#" class="Qclose"></a>
                <figcaption>If you typed in your own name in the Group selction above <br> then consider choosing <b>Event</b> or <b>Test</b> here.
                </figcaption>
            </figure>
        </div> <!-- End of class: NewLightbox -->
    </div> <!-- End of first div under KIND -->
    
    <div id="KindDropdown" >
    <!-- showKindChoices() & filterFunctions() are in NetManager-p2.js -->
    <input type="text" onfocus="showKindChoices(); blurGroupChoices();" placeholder="Type to filter list.." id="KindInput" 
           class="netGroup" onkeyup="filterFunction(1)"/>
    <div class='KindDropdown-content hidden'>
        
<?= $kindList; ?>    <!-- Created in buildThreeDropdowns.php -->
       
    </div> <!-- End KindDropdown -->
    </div> <!-- End KindDropdown-content -->
            
    <!-- ==== FREQ ======= -->  
    <div><b style="color:red">*</b>Select the Frequency:</div>
    <div id="FreqDropdown" >
        <!-- showFreqChoices() & filterFunctions() at the bottom of index.php -->
        <input type="text" onfocus="showFreqChoices(); blurKindChoices(); " placeholder="Type to filter list.." id="FreqInput" 
               class="netGroup" onkeyup="filterFunction(2)"/>
        <div class='FreqDropdown-content hidden'>
            
<?= $freqList; ?>    <!-- Created in buildThreeDropdowns.php -->
           
        </div> <!-- End FreqDropdown -->
    </div> <!-- End FreqDropdown-content -->
            
    <div class="last3qs">If this is a Sub Net select the<br>open primary net:</div>

    <!-- If any option is selected make the cb1 span (show linked nets) button appear using function showLinkedButton() -->
     <select class="last3qs" id="satNet" title="Sub Net Selections" onfocus="blurFreqChoices(); ">
    	<option value="0" selected>None</option>

<?= $subNets ?>     <!-- Created in getactivities.php  I think -->

     </select>
     		
		<label class="radio-inline last3qs" for="pb">Click to create a Pre-Build Event &nbsp;&nbsp;&nbsp;
		    <!-- doalert() & seecopyPB() in NetManager-p2.js -->
			<input id="pb" type="checkbox" name="pb" class="pb last3qs" onchange="doalert(this); seecopyPB(); " />
		</label>
		
		<div class="last3qs">Complete New Net Creation:</div>
		
		<br>
		
		<input id="submit" type="submit" value="Submit" onClick="newNet();" title="Submit The New Net">
		<input class="" type="button" value="Cancel" onclick="hideit();" title="Cancel">
				   
</div>	    <!-- End of makeNewNet -->
	    
	    <div id="remarks" class="remarks hidden"></div> 
        
        <div class="btn-toolbar" >
        
		    <div class="form-group" id="form-group-1" title="form-group" >
    	
    	<!-- When a net is selected from this dropdown, showActivities() is triggered.
         This function fetches the net data, populates the UI with net information,
         and sets up interactive features including real-time updates via SSE.
         It's the central function that prepares the entire interface for managing the selected net. -->
        <select id="select1" data-width='auto' class="tohide form-control selectpicker selectdd" name="activities" 
	        onchange="showActivities(this.value, this.options[this.selectedIndex].innerHTML.trim()); switchClosed();  ">
	        	
	        <option class="tohide pbWhite firstValue" value="a" selected disabled >Or Select From Past 10 Days Nets</option>
	        
	        <option class ="tohide opcolors" value="z" disabled>Open Nets are in green =================//================= Pre-built Nets are in blue</option>
 
            <option class="tohide newAfterHere" data-divider="true">&nbsp;</option>
            
<?= $selectOptions ?> <!-- Last 10 Days only -->
        	
        </select>  	<!-- End of ID: select1 -->
		
<! this may go away or at lest the timed part will -->
<div class="btn-group">	
    <button id="refbutton" class="btn btn-info btn-small hidden" >Refresh</button>
    
    <button id="refrate" class="btn btn-small btn-info dropdown-toggle hidden" 
    		data-toggle="dropdown" type="button">
        Timed
    	<span class="caret"></span>
    </button>
    	    
    <!-- Refresh timer selection -->
    <ul id="refMenu" class="dropdown-menu">
      <li><a href="#" data-sec="M" >Manual</a></li>
      <li class="divider"></li>
      <li><a href="#" data-sec="10" >5s</a></li>
      <li><a href="#" data-sec="10">10s</a></li>
      <li><a href="#" data-sec="30">30s</a></li>
      <li><a href="#" data-sec="60">60s</a></li>
    </ul>	    
</div>  <!-- /btn-group -->

		    </div> <!-- End div-form-group -->
        </div> <!-- End btn-toolbar -->
	</div>  <!-- End div-netdata -->
        
        
    <!-- General Comments control here -->    
    <!-- Edited and saved to DB by CellEditFunctions.js and SaveGenComm.php -->
    <!-- A general pupose entry point for text, it's put into the time line table -->
    <!-- This is activated by a jquery on function in netManager.js at about line 391 -->
    
	<div id="forcb1" class="hidden">
        	
		<div id="genComments" class=" editGComms"></div>

	</div>   <!-- End ID: forcb1 -->
	  <!-- End of besticky -->
	  
<!-- Primary button bar for adding new calls -->	  
<div id="admin" class=" admin ">   <!-- ends about 678 -->
		<div id="csnm" class="hidden">

<div id="primeNav" class="flashit" style="position:sticky; top:0; z-index:1;">  
    	    	    
	    <!-- The cs1 entry or call sign can take the form of a call sign or a first & last name, either will cause the system to filter existing entries on whats entered either fully or partially. An underscore in the entry serves as a wildcard. -->
	    
		<input id="cs1" type="text" placeholder="Call or First Name" maxlength="16" class="cs1" autofocus="autofocus" autocomplete="off" tabindex="1" > 
		
		<!-- Below input is where the hints from cs1 and name go before being selected -->
		<input type="hidden" id="hints">
		
		<!-- Input first name add readonly to prevent editing, this field is not required -->
		<input id="Fname" type="text" placeholder="Name" onblur="" autocomplete="off" tabindex="2">
		
		<!-- This allows for indicating traffic for the net at time of checkin, not a required entry. The onblur kicks off the entry of the callsign info. -->
		<input id="TrfkSwitch" type="text" onblur="handleCheckIn(); this.value=''" autocomplete="off" tabindex="3">
		
		<!-- Some attributes of the below field are controled in NetManager.js -->
		<input id="custom" class="hidden brdrGreen" type="text" placeholder="" autocomplete="off" onblur="checkIn();" >
		
		<!-- DO NOT COMMENT THIS OUT, IT BREAKS THE DISPLAY -->
		<input id="section" class="hidden brdrGreen" type="text" placeholder="" onblur=" this.value=''" maxlength="4" size="4"> 

<!-- https://www.w3schools.com/css/css3_buttons.asp -->
<!-- The job of showing and hiding the time line is done in the TimeLine() in NetManager.js -->
		    <div class = "btn-group2">
    		    <button class="ckin1" onclick="handleCheckIn()">Check In</button>
    		<!--  <button class="dropbtn2" id="columnPicker">Show/Hide Columns</button> -->
    		
    		    <button class="dropbtn2n" id="columnPickern" onclick="openSidebar()">Show/Hide Columns</button> 
    		 
    		    <button class="timelineBut" onClick="TimeLine(); location.href='#timeHead';" >Time Line</button>
    		    
    		    <button class="timelineBut timelineBut2" onclick="RefreshTimeLine(); location.href='#timeHead';">Update</button>
    		  
    		    <button class="copyPB hidden" id="copyPB">Copy a Pre-Built</button>
    		    
    		    <button class="closenet" id="closelog" oncontextmenu="rightclickundotimeout();return false;" >Close Net</button>
		    </div> <!-- End btn-group2 -->

			<!-- A normal left click and the log is closed, a right click resets the timeout to empty -->
			<!-- NetManager.js contains the code that tests to show or hide the close net button -->
			<!-- If a pre-built net is not yet open, at least one check-in, then don't show the button -->
			<!-- this should prevent accidental closing of a pre-built in progress of being built -->

</div>	<!-- End id: primeNav  -->
		
			<div  id="txtHint"></div> <!-- populated by NetManager.js ==> gethintSuspects.php-->
			<div id="netIDs"></div>			
			<div id="actLog"></div> <!-- Home for the net table -->
			
			<br>
			<div class="hidden" id="subNets"></div> <!-- Home for the sub-nets -->
			<br>
					
	<!--	The 'Export CSV' & 'Map This Net' buttons are written by the getactivities.php program --> 
			
			<!-- HideTimeLine() in NetManager.js -->
			<button class="timelineBut timelineBut2" onclick="RefreshTimeLine(); location.href='#timeHead';">Update</button>
			
			<input id="timelinehide" type="submit" value="Hide TimeLine" class="timelinehide" onClick="HideTimeLine();" />
			
			<!-- When the time line shows this is a specific search or numbers -->
			<input id="timelinesearch" type="text" name="timelinesearch"  placeholder="Search Comments: Search for numbers only" class="timelinesearch" style="border: 2px solid green;" />
			
			<button class="timelineBut3" type="button" id="runtls" 
			style="background-color: #f9e1e1; border-radius: 8px; border: 2px solid black; "
			onclick="timelinesearch();">Search</button>
			
			<img src="images/newMarkers/q-mark-in-circle.png" id="QmarkInCircle" class="timelineBut2" alt="q-mark-in-circle" width="15" style="padding-bottom: 25px; margin-left: 1px; background-color: #e0e1e3;" />
			
			<div id="q-mark-in-circle" class="timelineBut timelineBut2" style="font-size: 14pt; background-color: #f6dbdb; border: 2px solid red;  ">
    			<p style="color:red;"><br>This search function is primarily to find numbers.</p><p style="color:blue;">It was written to help track marathon and bike events where bib numbers are used to track participants. </p><p style="color:blue;">Other searches may or may not return what you are looking for. If a more general search is needed, use your browser Find instead, or right-click the Comments field of the station in the NetLog.
    			</p>
			</div>
			
			<div id="timeline" class="timeline"></div>		
			
		</div>   
</div> <!-- end admin from about 500-->		
</main>

	</div> <!-- End id netchoice -->
	
	<!-- https://jquerymodal.com -->
	<div id="#cc" class="modal" style="display:none;">	
		<p>&copy; Copyright 2015-2025, by Keith D. Kaiser, WA0TJT <br> Last Update: <span id="lastup">2024-11-09</span></p>
		<p>Questions, problems, concerns? ....send them to: 
			<a href="mailto:wa0tjt@gmail.com?subject=NCM">Keith D. Kaiser</a><br>
			Or click <a href="help.php" target="_blank" rel="noopener">here for a detailed Help page. </a></p>
			
	    <p> In order to succeed, you must know what you are doing, like what you are doing, and believe in what you are doing. -- Will Rogers
		</p>
		<p><a href="#" rel="modal:close">Close</a></p>
	</div> <!-- End id cc -->
	
	<!-- End of id lli -->
	<div id="lli" class="modal-dialog" style="display:none; "></div> 	
	<div id="pbl" class=" modal hidden"></div> <!-- End of id ppl, Holds the list of pre-built nets created in PBList.php -->
	
	<div id="gcomm" class="gcomm hidden">gcomm</div>
	
	<div id="testEmail" class="testEmail hidden"></div>
	
	<div id="hideme" class="hidden stuff"></div>
	
	<button onclick="topFunction()" id="myBtn" title="Go to top">Top</button>

	
<!-- *********************  JAVASCRIPT LIBRARIES ********************** -->	
    <!-- UPDATED: 2024-04-27 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- UPDATED: 2024-04-27 -->
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>

    <!-- Updated: 2024-04-27 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.js"></script>

    <!-- v1.0.7 -->
	<script src="js/jquery.freezeheader.js"></script>	

    <!-- v3.3.2 -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>	
    
    <!-- v1.12.4 2024-11-09 -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>	
				
    <!-- 2.0 2024-11-09 -->
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/2.0/js/bootstrap-multiselect.min.js"></script>	-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.0/js/bootstrap-multiselect.min.js"></script>
		
    <!-- 1.8.1 2024-11-09 -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-jeditable/1.8.1/jquery.jeditable.min.js"></script>   -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-jeditable/1.7.3/jquery.jeditable.min.js"></script> -->
    <!-- the one below works, the two above get a 404 -->
    <script src="js/jquery.jeditable.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

    <!-- 2 2018-1-18 -->
	<script src="js/sortTable.js"></script>									   
    <!-- Paul Brewer KI6CQ 2014 -->
	<script src="js/hamgridsquare.js"></script>							
    <!-- 1.0.8 2018-1-18 -->
	<script src="js/jquery.countdownTimer.js"></script>
	
    <!-- 1.31 2018-1-18 -->
	<script src="js/w3data.js"></script>
	
    <!-- My javascript -->	
     
    <!-- NCM Primary Javascrip 2018-1-18 to 2024-06-27 -->
    <!-- The order of these is important, don't change -->
	<script src="js/NetManager.js"></script>     
    <script src="js/NetManager-p2.js"></script>	
    <script src="js/NetManager-p3.js"></script>
    <script src="js/NetManager-W3W-APRS.js"></script>	
    
    <script src="js/cookieManagement.js"></script>      
    <script src="js/newNet.js"></script>
    <script src="js/checkIn.js"></script> 
    
    <script src="generateJsWithDefinitions.php"></script>
    <script>
        window.tableStructure = <?php echo json_encode($tableStructure); ?>;
    </script>
    
    <script src="js/column-management.js"></script>
    <script src="js/sidebar.js"></script>  
    <script src="js/showActivities.js"></script>
    
    <script src="js/CellEditFunction.js"></script>	 
    <script src="js/grid.js"></script>
    <script src="js/gridtokoords.js"></script>
    
    <script src="js/addNewCallsignRow.js"></script>
    <script src="js/handleSSE.js"></script>
    <script src="js/sendSseMessage.js"></script>
    
    <script src="js/addToNet.js"></script>  
    
    
    
 <!--   
    <script>
        console.log('Setting up MutationObserver');
        document.addEventListener('DOMContentLoaded', function() {
            const tableBody = document.getElementById('netBody');
            console.log('Table body found:', !!tableBody);
            if (tableBody) {
                const observer = new MutationObserver((mutations) => {
                    console.log('Mutation detected!');
                    mutations.forEach((mutation) => {
                        console.log('Mutation type:', mutation.type);
                        console.log('Added nodes:', mutation.addedNodes.length);
                    });
                });
        
                observer.observe(tableBody, { childList: true, subtree: true });
                console.log('MutationObserver set up successfully');
            }
        });
    </script>
    -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                const addedTable = Array.from(mutation.addedNodes).find(node => node.tagName === 'TABLE');
                if (addedTable) {
                    console.log('Table added to DOM. Applying column management.');
                    if (typeof initializeSortable === 'function') {
                        initializeSortable(addedTable);
                    } else {
                        console.error('initializeSortable function not found when table was added.');
                    }
                }
            }
        });
    });
    // Start observing the actLog div
    const actLog = document.getElementById('actLog');
    if (actLog) {
        observer.observe(actLog, { childList: true, subtree: true });
    } else {
        console.error('actLog div not found');
    }
});
</script>
    

    <script>
    window.onerror = function(message, source, lineno, colno, error) {
        console.error('Caught error:', message, 'at', source, lineno, colno);
        console.error('Error object:', error);
        return true;
    };
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded. Checking for column management function.');
            if (typeof window.applyColumnManagement === 'function') {
                console.log('applyColumnManagement function found. Calling it now.');
                window.applyColumnManagement();
            } else {
                console.error('applyColumnManagement function not found. Current window properties:', Object.keys(window));
            }
        });
    </script>

<!-- Sidebar structure This is for selecting columns to show -->
<div id="columnSidebar" class="sidebar">
    <a href="javascript:void(0)" class="close-btn">&times;</a>
    <h3>Select and Reorder Columns</h3>
    <div id="columnOptions">
        <div id="defaultColumns" class="column-category"></div>
        <div id="optionalColumns" class="column-category"></div>
        <div id="adminColumns" class="column-category"></div>
    </div>
    <button id="applyChanges">Apply</button>
    <button id="resetToDefault">Reset to Default</button>
</div>

<?php
// Uncomment the following line to log included files
 require_once "log_included_files.php";
?>

</body>
</html>