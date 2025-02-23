// NetManager.js
// changes to mostly use jquery made on 2018-1-25
// Updated: 2024-05-15

var strWindowFeatures = "resizable=yes,scrollbars=yes,status=no,left=20px,top=20px,height=800px,width=800px";

// Cache Elements for faster processing 
// use like this:    $lli.addClass('active').show();   drop the $("#lli") from the call
var $lli 			= $("#lli");
var $closelog 		= $("#closelog");
var $time 			= $("#time");
var $multiselect 	= $(".multiselect");
var $primeNav 		= $("#primeNav");
var $netIDs 		= $("#netIDs");
var $cb0 			= $("#cb0");
var $cb1 			= $("#cb1");
var $actLog 		= $("#actLog");
var $thebox 		= $('#theBox');
var $csnm 			= $("#csnm");
var $forcb1 		= $("#forcb1");
var $idofnet		= $("#idofnet");
var $select1		= $("#select1");
var $isopen			= $("#isopen");

// This function is called by right clicking on the gridsquare, it opens a map in APRS.fi showing the gridsquare
function MapGridsquare(koords2) {
	
	var koords = koords2.split(":"); //alert("2= "+koords2+" 0= "+koords[0]+","+koords[1]);

    var lat = koords[0];
    var lon = koords[1];
    var cs1 = koords[2];
    
    window.open("https://www.qrz.com/hamgrid?lat="+lat+"&lon="+lon+"&sg="+cs1);
}

function MapCounty(cntyst2) {
	var cntyst = cntyst2.split(":");
	
	var county = cntyst[0];
	var state  = cntyst[1];
	
	window.open("https://www.google.com/maps/place/"+county+"+County,"+state);
	//alert("https://www.google.com/maps/place/"+county+"+County,"+state);
}

function whatIstt() {
	$('tr').on('contextmenu', 'th', function(e) { //Get th under tr and invoke on contextmenu	
		alert("you are in the whatIstt function, call= ");
	});
}

function setDfltMode() {
    alert("you are in the setDfltMode() function, in NetManager.js. Soon you will be able to choose a default value for this column.");
    /*
        The List 
        var modeOptions = ["Voice", "CW", "Mob", "HT", "Dig", "FSQ", "D*", "Echo", "DMR", "Fusion", "V&D", "Online", "Relay"];
    */
}


// This function is called when the 'Report by Call Sign' is selected from the hamburger menu
function CallHistoryForWho() {
    var str = prompt("Enter a call sign");
		
	if (str =="") {alert("Sorry no call was selected");}
	
	else {

        var thiscall = [str.trim()];
        //alert("thiscall: "+thiscall);
            var popupWindow = window.open("", thiscall,  strWindowFeatures);
            var id = 0;
          //  e.preventDefault(); //Prevent defaults'
            
		  fetch(`getCallHistory.php?call=${encodeURIComponent(thiscall)}&id=${id}`)
		  .then(response => {
			if (!response.ok) {
			  throw new Error('Network response was not ok');
			}
			return response.text();
		  })
		  .then(html => {
			// Handle success
			const popupWindow = window.open();
			popupWindow.document.write(html);
		  })
		  .catch(error => {
			// Handle error
			console.error('Error fetching call history:', error);
			alert('Last Query Failed, try again.');
		  });
		
      //  alert("in getCallHistory "+cs);  
	} // end of else
}


//This function will find the information about the history of the call right clicked on
//https://stackoverflow.com/questions/23740548/how-to-pass-variables-and-data-from-php-to-javascript
// Function name changed from getLastLogin to getCallHistory on 2018-1-17
function getCallHistory() {
	console.log('in the getCallHistory function');
	//Get the td under tr and invoke on contextmenu
	$('tr').on('contextmenu', 'td', function(e) { 
    	
    	//e.preventDefault(); //Prevent defaults'
    	
    	// Get the call and clean it up, that was right clicked
		var idparm = $(this).attr('id');
		var arparm = idparm.split(":");
		var id     = arparm[1];
			id 	   = id.replace(/\s+/g, '');
		var call   = $(this).html();            //alert(call);
		    call   = call.replace(/\s+/g, '');  // remove spaces 
		
		    if (call == '' ) {alert("no call");}
		
		var vals = [call].map((item) => item.split(/\W+/,1));
			call = vals[0];
		
		var thisdomain = getDomain();  //alert("@124 in NetManager.js thisdomain= "+thisdomain); 
		// By using _blank it allows multiple popups
		
	  	var popupWindow = window.open(" ", call,  strWindowFeatures);
            
	  	
		e.preventDefault(); //Prevent defaults'

		fetch(`getCallHistory.php?call=${call}&id=${id}`)
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.text();
  })
  .then(html => {
    popupWindow.document.write(html);
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Last Query Failed, try again.');
  });

	});
}

function getCallHistory7Day(value) {
    console.log('in the getCallHistory7Day function');
    // Use the 'value' parameter as needed
    console.log('Value:', value);
    
    //var strWindowFeatures = "resizable=yes,scrollbars=yes,status=no,left=20px,top=20px,height=900px,width=800px";

    var popupWindow = window.open(" ", value,  strWindowFeatures);

	fetch("getCallHistory7Day.php?call=" + value)
	.then(response => {
	  if (!response.ok) {
		throw new Error('Network response was not ok');
	  }
	  return response.text();
	})
	.then(html => {
	  // Open a new popup window and write the HTML content
	  const popupWindow = window.open();
	  popupWindow.document.write(html);
	})
	.catch(error => {
	  console.error('Error:', error);
	  alert('Last Query Failed, try again.');
	});
  
}

// The testCookies and refresh functions were here, now near the bottom 


function rightclickundotimeout() {
// 2018-1-25	var netid = document.getElementById("select1").value;

	var netid = $("#select1").val();
	var opensign = prompt("Enter your call sign to re-open log #" + netid);
	var opensign = opensign.toUpperCase();
 
		if (opensign != "" ) {
			fetch("rightclickundotimeout.php", {
				method: "POST",
				headers: {
				  "Content-Type": "application/json"
				},
				body: JSON.stringify({ netid: netid, opensign: opensign }),
				cache: "no-cache"
			  })
				.then(response => {
				  if (!response.ok) {
					throw new Error('Network response was not ok');
				  }
				  return response.text();
				})
				.then(response => {
				  // Display a success message
				  alert("Closed Log #" + netid + " has been reset.");
				  // Reload the page
				  location.reload(true);
				  // Optionally, call the showActivities function with netid
				  // showActivities(netid);
				})
				.catch(error => {
				  console.error('Error:', error);
				  // Handle any errors that occurred during the fetch operation
				});
			  
	}
}

function pre() {
	//alert("The pre is working");
	window.open("buildEvents.php");
}

/* Added 12/02/2016 to Call Sign entry CS1 */
function isKeyPressed(event) {
    if (event.altKey) {
	    var str = ' ';
        showHint(str);
    } 
}

// Added 2018-02-09 
// Gets the domain needed to build the preamble, agenda, closing, etc...
function getDomain() {
	// Find initial value for domain
	//if ($("#activity").length === 0 ) {alert("in missing activity");}
	
	// If the length of activity is 0, its actually missing because it never got created. 
	// I'm ussing this to trigger a search in the vaious php programs to display all known versions
	// of that dta... ie. preamble, closing, etc.
	if ($("#activity").length === 0 ) {
			//alert("in missing activity");
			var domain = 'ALL';
			//alert(domain);
	} else {
	
		var domain = $("#activity").html().trim();  //alert(domain);  // KCNARES  Weekly 2 Meter Voice Net
	
		if (domain.startsWith("CREW 2273")) {domain = "Crew2273";}
		if (domain.startsWith("Clay Co.")) {domain = "W0TE";}
		if (domain.startsWith("North Central MO")) {domain = "NCMO";}
		if (domain.startsWith("Carroll County")) {domain = "CARROLL";}   //alert('in js '+domain);
		if (domain.startsWith("Johnson County")) {domain = "KS0JC";}
	}
		
		return domain; 
}

function openPreamble() {
	var domain = getDomain();  //alert("domain in openPreamble= "+domain); 
	//if (!domain) {alert('no domain');}
		document.getElementById("preambledev").href = "buildPreambleListing.php?domain="+domain;
}

function openClosing() {
	var domain = getDomain();
		document.getElementById("closingdev").href = "buildClosingListing.php?domain="+domain;
}
						   
function openAgenda() {
	var domain = getDomain(); alert(domain); // PCARG  Weekly 2 Meter Voice Net
		document.getElementById("agendadiv").href = "buildEventListing.php?domain="+domain;
}

function hideit() {
	$("#makeNewNet").addClass("hidden");
	//$("#select1").removeClass("hidden");
	$("#form-group-1").show();
}

function showit() {
	$("#makeNewNet").removeClass("hidden");

	$("#callsign").focus();
	$("#openNets").addClass("hidden");  // Added 01-31-2017
	
	$("#form-group-1").hide();
}

function showSubNets(str) {
	//var str = $("#select1").val();
	var str = $("#idofnet").html().trim();
	//	str = str.trim();
	//alert(str);   // active net at the time clicked 54, not the subnet we're looking for
	$("#subNets").removeClass("hidden");
	  if (document.getElementById('cb1').checked == false) {
		   document.getElementById("subNets").innerHTML = "";
             	  return;
	  } else {
		  
		  // This change made 2018-08-04
		  fetch('getsubnets.php?q=' + str)
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.text();
  })
  .then(response => {
    // Update the content of the #subNets element with the response
    document.getElementById('subNets').textContent = response;
  })
  .catch(error => {
    console.error('Error:', error);
    // Handle any errors that occurred during the fetch operation
  });

	  }
}  // End showSubNets

/* created 2018-08-04 combined two function into this one to close a log */
$(document).ready(function() {
    
    $(".timelineBut2").hide(500); // this is the update button for the Time Log. Its hidden here in order to hide it during the initial display of the net. Without it, it shows up and defeats the purpose of it coming and going.
    
	$('#closelog').click(function() {
  var closesign = prompt("Enter your callsign to confirm closing the log:");
  var closesign = closesign.toUpperCase();

  if (closesign != "") {
    var net2close = $("#idofnet").html();
    var str = net2close + "," + closesign; // replace spaces in string
    var str = str.replace(/\s/g, '');
    console.log("@337 Tope of closeLog.php fetch(); str: "+str);

    $("#closelog").html("Net Closed");
    $(".toggleTOD").toggle();
    binaryops = binaryops + 1; // show the TOD column
    
    fetch('closeLog.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ q: str })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(response => {
      // Toggle the elements with the "toggleTOD" class
      $('.toggleTOD').toggleClass('hidden');
      binaryops++;

      // Update the content of the #actlog element with the response
      if ($('#actlog').length) {
        $('#actlog').html(response);
      }

      // Update the content of the #closelog element
      $('#closelog').html('Net Closed');

      // Call the showActivities function with the net2close parameter
      showActivities(net2close);

      // Open the ICS-214 report for the current net
      window.open('https://net-control.us/ics214.php?NetID=' + net2close);
    })
    .catch(error => {
      console.error('Error:', error);
      // Handle any errors that occurred during the fetch operation
    });
  } // end if (closesign != "")
}); // end closelog click function
}); // end ready function

function graphtimeline() {
    // str here is NetID 
	if ( $('#idofnet').length ) {
		var str	 = $("#idofnet").html().trim();
	} else {
		var str = prompt("Enter a Log number.");
	}
		
	if (str =="") {alert("Sorry no net was selected");}
	else {
		window.open("HzTimeline.php?NetID="+str);
	}
}


function ics214button() {
	// str here is NetID 
	if ( $('#idofnet').length ) {
		var str	 = $("#idofnet").html().trim();
	} else {
		var str = prompt("Enter a Log number.");
	}
		
	if (str =="") {alert("Sorry no net was selected");}
	else {
		window.open("ics214.php?NetID="+str);
	}
}

function ics309button() {
	// str here is NetID 
	if ( $('#idofnet').length ) {
		var str	 = $("#idofnet").html().trim();
	} else {
		var str = prompt("Enter a Log number.");
	}
		
	if (str =="") {alert("Sorry no net was selected");}
	else {
		window.open("ics309.php?NetID="+str);
	}
}

function ics205Abutton() {
	// str here is NetID 
	if ( $('#idofnet').length ) {
		var str	 = $("#idofnet").html().trim();
	} else {
		var str = prompt("Enter a Log number.");
	}
		
	if (str =="") {alert("Sorry no net was selected");}
	else {
		window.open("ics205A.php?NetID="+str);
	}
}


function map1() {
	if ( $('#idofnet').length ) {
		var str	 = $("#idofnet").html().trim();
	} else {
		var str = prompt("Enter a Log number.");
	}
		
	if (str =="") {alert("Sorry no net was selected");}
	else {
	window.open("map1.php?NetID="+str);
	}
}

function map2() {
	if ( $('#idofnet').length ) {
		var str	 = $("#idofnet").html().trim();
	} else {
		var str = prompt("Enter a Log number.");
	}
		
	if (str =="") {alert("Sorry no net was selected");}
	else {
	window.open("map.php?NetID="+str);
	}
}

function printByNetID() {
	if ( $('#idofnet').length ) {
		var str	 = $("#idofnet").html().trim();
	} else {
		var str = prompt("Enter a Log number.");
	}
		
	if (str =="") {alert("Sorry no net was selected");}
	else {
	window.open("printByNetID.php?NetID="+str);
	}
} // End printByNetID


// This empty looking function is actually run by CellEditFunction() in CellEditFunction.js
$('.editGComms').on("click", function(){
    $('.editGComms').empty(); 
});



// This function empties the contents of other fields
// For some reason it doesn't work properly in jQuery
function empty(thisID) {
    document.getElementById(thisID).innerHTML = "";
} // End empty()



// ========================================================================================================
// This function populates and shows the Time Line 
// See: https://www.w3schools.com/jquery/tryit.asp?filename=tryjquery_eff_toggle_callback for an example
// An additonal setting of timelineBut2 is done at line 263 to control its visibility at initial load of the table
function TimeLine() {
	$("#timeline").toggle(500, function() {
        $(".timelinehide").toggle(500);
        $(".timelineBut2").toggle(500); // this is the update button
        $("#timelinesearch").toggle(500);
        $(".timelineBut3").toggle(500);
	});
	
		var str = $("#idofnet").html();
		//alert(str);
		$.get('getTimeLog.php', {q: str}, function(data) {
			$("#timeline").html(data);	// this once said .TimeLine() as if calling itself again
		}); // end response
} // End TimeLine()

// The filtering (searching) of the timeline
function timelinesearch() {
    
    var findthis = $("#timelinesearch").val();
    console.log('<br>Now in timelinesearch; findthis= '+findthis+'<br>');
        if (findthis == "") { 
            alert("No values in search box."); 
                return false;
        }else {
            var netID = $("#idofnet").text().trim();
            var str = netID+","+findthis;
                console.log("@451 netID: "+netID+" findthis: "+findthis+" str: "+str+" in timelinesearch function");
                if (findthis) {
                    $.get('getTimeLogSearch.php', {q: str}, function(data) {
                        $("#timeline").html(data);
                    });
                   
                } // End if findthis exists
                    // Empty the search field when all else is done
                    $("#timelinesearch").val(" ");
        } // End else because findthis does exist
}

function RefreshTimeLine() {
    var str = $("#idofnet").html();
		//alert(str);
		$.get('getTimeLog.php', {q: str}, function(data) {
			$(".timeline").html(data);	// this once said .TimeLine() as if calling itself again
		}); // end response
}

function HideTimeLine() { 	
	$(".timelineBut2").hide(500); // this is the update button
		
	$("#timeline").hide(500);
	$(".timelinehide").hide(500);
	
	$("#timelinesearch").hide(500);
	$(".timelineBut3").hide(500);
} // End HideTimeLine()

// re-wrote these two function 2018-02-12
// finds and displays a list of hints for the callsign entry
function showHint(str) {
    // var nc = $( "#thenetcallsign" ).html(); alert('val= '+nc);
   // alert(str);
	if (str.length == 0) {
  		$("#txtHint").html(""); // set to empty
  			return;
  	} else {
		fetch("gethint.php?q=" + encodeURIComponent(str))
		.then(response => {
		  if (!response.ok) {
			throw new Error('Network response was not ok');
		  }
		  return response.text();
		})
		.then(data => {
		  // Handle success
		  document.getElementById('txtHint').innerHTML = data;
		})
		.catch(error => {
		  // Handle error
		  console.error('Error fetching hint:', error);
		  alert('Sorry no hints.');
		});
	  
}} 

// finds and displays a list of hints for the name entry
function nameHint(str) {
	if (str.length == 0) {

		$("#txtHint").html("");
		return;
	} else {
		fetch("gethintName.php?q=" + encodeURIComponent(str))
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.text();
  })
  .then(data => {
    // Handle success
    document.getElementById('txtHint').innerHTML = data;
  })
  .catch(error => {
    // Handle error
    console.error('Error fetching hint:', error);
    alert('Sorry no hints.');
  });

}}

function set_cs1(item) { 	 	//Load up the call sign and name from the selection  list
  	$('#cs1').val(item);
  	$('#Fname').focus();
  	$('#txtHint').show();
}

function set_Fname(item) { 	 	//Load up the call sign and name from the selection  list
  	$('#Fname').val(item);
  	$("#txtHint").html("");
  	$('#txtHint').show();
}

function set_hidden(item) {  	//Load up the call sign and name from the selection  list
  	$('#hideme').val(item);
  	$('#txtHint').show();
}

function set_hidestuff(item) { 	//Other needed values
	$('#hidestuff').val(item);
}

function loadsorttable() {
	var newTableObject = document.getElementById("thisNet");
    	sorttable.makeSortable(newTableObject); 
 // 2018-1-25   document.getElementById("csnm").style.visibility="hidden";
    	$("#csnm").addClass("hidden");
}

//This function is used in the buildEvents.php program
function selectDomain(thisdom) {
	//alert("thisdom "+thisdom);
	var domain = $('#netDomain').find(":selected").text(); 
	alert("from netDomain "+domain+" and "+thisdom); 
	//from netDomain CREW2273  and CREW2273
}


function AprsFiMap() {
	var q = $("#select1").val();
	
	fetch("AprsFiMap.php?q=" + q)
		.then(response => {
			if (!response.ok) {
			throw new Error('Network response was not ok');
			}
			return response.text();
		})
		.then(data => {
			// Open the response in a new window
			window.open(data);
		})
		.catch(error => {
			console.error('Error:', error);
		});

}

// Function to delete a row
// You can NOT delete a row that shows 'OUT'
$(document).on('click', '#thisNet td a.delete', function()  {
	$("#thisNet td a.delete").click(function() {
		if (confirm("Are you sure you want to delete this row?")) {
		
			var id = $(this).parent().parent().attr('id'); 
			var parent = $(this).parent().parent();
			
			fetch('delete-row.php', {
				method: 'POST',
				headers: {
				  'Content-Type': 'application/json'
				},
				body: JSON.stringify({ id: id }) // Assuming id is defined elsewhere
			  })
				.then(response => {
				  if (!response.ok) {
					throw new Error('Network response was not ok');
				  }
				  return response.text();
				})
				.then(() => {
				  // Assuming parent is defined elsewhere
				  parent.fadeOut('slow', function() {
					$(this).remove();
				  });
				})
				.catch(error => {
				  console.error('Error:', error);
				  // Handle any errors that occurred during the fetch operation
				});
							  
		}
	});
});

function RefreshGenComm() {
    var str = $("#idofnet").html();
		$.get('getGenComments.php', {q: str}, function(data) {
			$(".editGComms").html(unescape(data));
		}); // end response
}

// This controls the refresh rate selection
 $(document).ready(function () {
	var interval = 6000000000;
	var autoRefId = null;
	
	$("#refMenu a").click(function(e){
    	e.preventDefault(); // cancel the link behaviour
			var selText = $(this).text();  
				$("#refrate").text(selText);
				clearInterval(autoRefId);    //https://www.w3schools.com/jsref/met_win_clearinterval.asp

			    interval = $(this).attr('data-sec'); // get the interval in seconds
					if (interval == 'M') {interval = 60000000;}
						interval = interval * 1000;

				autoRefId = setInterval(function() {
	        		// set the automatic refresh for the interval selected above 
	        		showActivities($("#idofnet").html().trim());  // this is the netID, number 
	        	}, interval);		    	 
	});


/* This function is called by the button named refresh to refresh the screen */
/*
function refresh() {
	var refreshnet=$("#idofnet").html().trim();   //
		showActivities(refreshnet); // showActivities 	
}
*/
// Phase 1 version updated 2024-06-29
window.refresh = function() {
    var refreshnet = $("#idofnet").html().trim();
    showActivities(refreshnet);
}

$('#refbutton').click(function() {
     //   var tz_domain = getCookie("tz_domain"); //alert('@739 '+tz_domain);
     //       if ( tz_domain == 'Local' ) { goLocal(); } else { goUTC(); }
		// refreshnet is the netID of the open net
		var refreshnet=$("#idofnet").html().trim();   //alert("in refresh()= "+refreshnet);
			showActivities(refreshnet); // showActivities function is in this file		
	});
	
}); // end document ready

function showColumns() {
	var netcall   = $("#thenetcallsign").html().replace(/\s+/g, '');
	var myCookie = (getCookie('columnChoices_'+netcall));  //alert("refresh @761 myCookie = "+myCookie);
	
	if (myCookie) {
		var testem = myCookie.split(",");  //alert("testem= "+testem);
		testem.forEach(toggleCol); // The function here is showCol() in cookieManagement.js
	} // End if myCookie
}

function goLocal() {
    //alert('In goLocal');
    setCookie('tz_domain', 'Local', 2);
    $("#theLocal").prop('checked','checked');
    $(".tzld").addClass("hidden"); // tzld = timezone localdate (time in)
    $(".tzto").addClass("hidden"); // tzto = timezone timeout (out time)
    $(".tzlld").removeClass("hidden");
    $(".tzlto").removeClass("hidden");
}

function goUTC() {
    //console.log('In goUTC');
    setCookie('tz_domain', 'UTC', 2);
    //console.log('cookie set');
    $("#theUTC").prop('checked','checked');
    //console.log('#theUTC done');
    $(".tzld").removeClass("hidden"); // tzld = timezone localdate (time in)
    //console.log('tzld done');
    $(".tzto").removeClass("hidden"); // tzto = timezone timeout (out time)
    //console.log('tzto done');
    $(".tzlld").addClass("hidden");
    //console.log('tzlld done');
    $(".tzlto").addClass("hidden");
    //console.log('tzlto done');
}
