<!doctype html>

<html lang="en">
<head>
    <title>Amateur Radio Net Control Manager Edit Stations Table</title>
    <link rel="apple-touch-icon" sizes="120x120" href="apple-touch-icon-120x120-precomposed.png" /> 
    <link rel="apple-touch-icon" sizes="152x152" href="apple-touch-icon-152x152-precomposed.png" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
      
    <meta name="viewport" content="width=device-width, initial-scale=1.0" >
    <meta name="description" content="Amateur Radio Net Control Manager Stations Editor" >
    <meta name="author" content="Keith Kaiser, WA0TJT" >
    
    <meta name="Rating" content="General" >
    <meta name="Revisit" content="1 month" >
    <meta name="keywords" content="Amateur Radio Net, Ham Net, Net Control, Call Sign, NCM, Emergency Management Net, Net Control Manager" >
    
    <!-- https://fonts.google.com -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Allerta" >
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Stoke" >
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Cantora+One" >
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Risque" >
    
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon-32x32.png" >
    
       <!-- My style sheets -->
<style>
    
table {
      position: absolute;
      top: 100px;
      left: 5px;
      
      border-collapse: collapse;
      border-spacing: 0;
      width: 100%;
      border: 1px solid black;
}

th, td {
      text-align: left;
      padding: 6px;
      border: 1px solid black;
}

tr:nth-child(even) {
    background-color: rgba(170, 159, 159, 0.28);
}
table>thead {
      background-color: darkgreen;
      color: white;
      font-weight: bold;
}
th {
    white-space: nowrap;
} 
p {
    color: red;
    font-size: larger;
    font-weight: bold;
}

#myBtn {
  opacity: 0.7;
  
  display: none;
  position: fixed;
  bottom: 20px;
  right: 30px;
  z-index: 99;
  font-size: 18px;
  border: none;
  outline: none;
  background-color: rgba(129, 118, 118, 0.5);
  color: white;
  cursor: pointer;
  padding: 15px;
  border-radius: 4px;
}

#myBtn:hover {
  background-color: #645f5f;
}

.c7, .c8, c17 { /* Fname and Lname and county */
  white-space: nowrap; 
  text-transform:capitalize;
}

.c9 .c18 .c59 { /* tactical, state and district */
  text-transform:uppercase;
}

.c11 {
    text-transform:lowercase;
}

.dttm {
    white-space: nowrap;
}

#stationcount {
    position: absolute;
    top: 5px;
    left: 50px;
}

.sc-part1 {
    color:blue;
    font-size: 24pt;
    padding: 0;
}
.sc-part2 {
    padding; 0;
    color: red;
    font-size: 18pt;
}

.besticky {
    background-color: darkgreen;
    position: -webkit-sticky; 
    position: sticky;
    top: 1px;
}

.ncmsearch {
    position: absolute;
    top: 60px;
    left: 500px;
}
	 
</style>

</head>
<body>
    <!--
    <h1> Station Call Signs in the NCM Data Base</h1>
    -->
<?php
// function to geocode address, it will return false if unable to geocode address
require_once "dbConnectDtls.php";

// Function below converts seconds to days, hours, minutes, seconds 
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

function secondsToDHMS($seconds) {
	$s = (int)$seconds;
		return sprintf('%d:%02d:%02d:%02d', $s/86400, $s/3600%24, $s/60%60, $s%60);
}


echo "
		<table class='sortable'>
		<thead id='stationHead' class='' style='text-align: left; font-weight: bold;'>			
    		<tr>          
    		    <th title='recordID'        class=' besticky'>      recordID     </th>
    		    <th title='id'              class=' besticky'>      ID           </th>  
    		    		    
    			<th title='Call Sign'  	    class='c6 besticky'>	Callsign 	</th>
    			<th title='Tactical'        class='c9 besticky'>    Tactical     </th>
    			<th title='First Name'     	class='c7 besticky'>    First Name 	</th>
    			<th title='Last Name'     	class='c8 besticky'>    Last Name  	</th>
    			
    			<th title='Latitude'     	class='c8 besticky'>    Latitude  	</th>
    			<th title='Longitude'     	class='c8 besticky'>    Longitude  	</th>
    			<th title='Grid'          	class='c20 besticky'>   Grid        	</th>
    						
    			<th title='County'   		class='c17 besticky'>   County 		</th> 
    			<th title='State'    		class='c18 besticky'>   State	 	</th>
    			<th title='District' 		class='c59 besticky'>   Dist	 		</th>
    			  
                <th title='Home' 		    class='c19 besticky'>   Home	 		</th>
    			
    			<th title='email' 	  		class='c11 besticky'>   eMail    	</th>	  
    			<th title='Phone'     		class='c10 besticky'>   Phone     	</th>        
    			<th title='Credentials'  	class='c15 besticky'>   Credentials 	</th>	
    			<th title='FCCID'  	        class='fcc besticky'>   FCC ID    	</th>
    			<th title='isActive'  	    class='cxx besticky'>   Active Call 	</th>
    			<th title='DTTM'  	        class='dttm besticky'>  DTTM 	    </th>		
    			<th title='comment'	        class='comment besticky'>  Comment 	    </th>
    		</tr>
		</thead>
	
		<tbody class='sortable' id='stationBody'>
		
"; // END OF header

	$sql = ("
		SELECT   recordID
		        ,ID
		        ,callsign
		        ,tactical
		        ,Fname
		        ,Lname
		        ,latitude
		        ,longitude
		        ,grid
		        ,county
		        ,state
		        ,district
		        ,home
		        ,email
		        ,phone
		        ,creds
		        ,fccID
		        ,active_call
		        ,dttm
		        ,comment
          FROM `stations` 
        /* WHERE ID <= 13 */
        /*   WHERE state = '' 
             AND callsign NOT LIKE '%NONHAM%' 
             AND callsign NOT LIKE '%EMCOMM%'
             AND callsign REGEXP '[0-9]'
             AND callsign NOT REGEXP '^...[0-9]'
             AND LEFT(callsign,1) IN('A','K','N','W') 
        */
         ORDER BY ID ASC, callsign ASC
	");
	
	// The callsign NONHAM sometimes has a number NONHAM3 so the NOT LIKE must be used
	// The "AND callsign NOT REGEXP '^...[0-9]'" part gets rid of all the MARS calls where the digit 
    // is in position 3 or greater 
    // The "LEFT(callsign,1) IN('A','K','N','W')" test to be sure it is a US call sign
	
	$station_count = 0;
	
foreach($db_found->query($sql) as $row) {
    
    $station_count = $station_count + 1;
    
    echo "
        <tr>
            <td class='c0'  > $row[recordID] </td>
            <td class='c0'  > $row[ID]       </td>
            <td class='c6'  > $row[callsign] </td>   
            
            <td class='c9 editTAC editable' id='tactical:$row[ID]' 
                onClick=\"empty('tactical:$row[ID]');\" > $row[tactical] </td>
            <td class='c7 editFnm editable' id='Fname:$row[ID]'  
                onClick=\"empty('Fname:$row[ID]');\"  > $row[Fname]      </td>    
            <td class='c8 editLnm editable' id='Lname:$row[ID]'  
                onClick=\"empty('Lname:$row[ID]');\"  > $row[Lname]      </td> 
            
            <td class='c21 editLAT  editable' id='latitude:$row[ID]' 
                onClick=\"empty('latitude:$row[ID]');\" > $row[latitude]    </td>
            <td class='c22 editLON  editable' id='longitude:$row[ID]' 
                onClick=\"empty('longitude:$row[ID]');\" > $row[longitude]	</td>
            <td class='c20 editGRID editable' id='grid:$row[ID]'   
                onClick=\"empty('grid:$row[ID]');\" > $row[grid]            </td>
        	
            <td class='c17 editcnty  editable' id='county:$row[ID]' 
                onClick=\"empty('county:$row[ID]');\" > $row[county]     </td>
            <td class='c18 editstate editable' id='state:$row[ID]' 
                onClick=\"empty('state:$row[ID]');\" > $row[state]       </td>
            <td class='c59 editdist  editable' id='district:$row[ID]' 
                onClick=\"empty('district:$row[ID]');\" > $row[district] </td>
            
            <td class=''  > $row[home] </td>
            	
            <td class='c11 editEMAIL editable' id='email:$row[ID]' 
                onClick=\"empty('email:$row[ID]');\"> $row[email]  </td>
            <td class='c10 editPhone editable' id='phone:$row[ID]' 
                onClick=\"empty('phone:$row[ID]');\"> $row[phone]  </td>
        	<td class='c15 editCREDS editable' id='creds:$row[ID]' 
        	    onClick=\"empty('creds:$row[ID]');\" > $row[creds] </td>
        	    
            <td class='fcc' id='fccid:$row[ID]'> $row[fccID] </td>
            
            <td class='cxx  editable' id='actcall:$row[ID]' 
        	    onClick=\"empty('active_call:$row[ID]');\" > $row[active_call] </td>
        	
        	<td CLASS='dttm' id='dttm:$row[ID]' > $row[dttm] </td>
        	<td CLASS='comment' id='comment:$row[comment]' > $row[comment] </td>
        </tr>
    ";   
}
    echo '</tbody></table>';
 
    echo "<div id='stationcount'>
            <div class='sc-part1'> Stations in the NCM Data Base </div>
            <div class='sc-part2'> Station count: $station_count<br><br>$row[call_count] </div>
          </div>";
				
?>

<!-- This form doesnt do anything yet, i'll work on that -->
<!--
<form class="ncmsearch" action="">
    <span>does not work yet</span><br>
<span>Find: </span>
<input type="search" id="ncmsearch" name="ncmsearch">
<input type="submit">
</form>
-->

<button onclick="topFunction()" id="myBtn" title="Go to top">Top</button>

<script>
    // When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};
function topFunction() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
};
function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("myBtn").style.display = "block";
    } else {
        document.getElementById("myBtn").style.display = "none";
    }
}

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

	<script src="bootstrap/js/bootstrap.min.js"></script>			<!-- v3.3.2 -->
	<script src="js/jquery.freezeheader.js"></script>				<!-- v1.0.7 -->
	<script src="js/jquery.simpleTab.min.js"></script>				<!-- v1.0.0 2018-1-18 -->
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
	<!--
	<script src="js/jquery.modal.min.js"></script> -->
	<script src="bootstrap/js/bootstrap-select.min.js"></script>				<!-- v1.12.4 2018-1-18 -->
	<script src="bootstrap/js/bootstrap-multiselect.js"></script>				<!-- 2.0 2018-1-18 -->

    <!-- http://www.appelsiini.net/projects/jeditable -->
    <script src="js/jquery.jeditable.js"></script>							<!-- 1.8.1 2018-04-05 -->

	<!-- http://www.kryogenix.org/code/browser/sorttable/ -->
	<script src="js/sortTable.js"></script>										<!-- 2 2018-1-18 -->

	
	<script src="js/w3data.js"></script>										<!-- 1.31 2018-1-18 -->
	
	<!-- My javascript -->
	
	<script src="js/NetManager.js"></script> 	<!-- NCM Primary Javascrip 2018-1-18 -->
	
	<!--<script src="js/NetManager-p2.js"></script>		-->			<!-- Part 2 of NCM Primary Javascript 2018-1-18 -->

	<!-- End My javascript -->
	
	<script src="js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
	
	<script src="js/CellEditFunctionStations.js"></script>
	
	<script>
	    /* these must remain under all the script srource files abnove. */
        // This is the setup for the popup windows
        var strWindowFeatures = "resizable=yes,scrollbars=yes,status=no,left=20px,top=20px,height=800px,width=600px";
    </script>
    
    
    <script>
        // This function call makes it all work
        CellEditFunctionStations( jQuery );
    </script>
   	
   	
   	
</body>
</html>
