/* 
NetManager-p2.js 

All of this NetManager-p2 (part 2) file was copied from the top of the index.php and moved here on 2018-1-15 
*/

// V2 Updated: 2024-05-01

function mapWhat3Words(w3w) {
    if (w3w == '') {
        alert('No entry or click the blue refresh button first to see this location.');
    } else {
        // Split the input by line breaks and use only the first line
        //let lines = w3w.split(/\r?\n/);
        let lines = w3w.split("<br>");
        let firstLine = lines[0].trim(); // Trim any leading or trailing whitespace
            console.log("firstLine: "+firstLine);

        // Open the what3words map with the first line
        window.open("https://map.what3words.com/" + firstLine);
    }
}

///////////////////////////////////////////

// This MIGHT be used to more easily enter W3W values correctly
// currently not being used...
let parms = Array();
function getW3W(parms) {
    
    console.log('@42 in NetManager-W3W-APRS.js now in getW3W function');
    const objName = prompt('Enter a W3W address & optional object \n ex: clip.apples.leap green car'); 
    parms = parms.split(',');   
    const recordID = parms[0];
    const netID = $("#idofnet").text().trim();
    
   $("#w3w:"+recordID).text(objName);
    
    console.log('@50 netID '+netID+' recordID= '+recordID+' objName= '+objName);       
} // End of the getW3W() function

////////////////////////////////////////////////////////

function doubleClickComments(rID, cs1, netID) {
    console.log("@58 You did it");
}

// This function will run doubleClickCall.php, it has two jobs, one is to update the stations table
// with the information about this DX call and two is to update the NetLog table at the appropriate
// recordID with pretty much the same information. 
// Two birds with one stone kind of thing.
function doubleClickCall(rID, cs1, netID) {
   var rID = rID;
   var cs1 = cs1;
   var netID = netID;
   // var str = rID+','+ cs1;
   // alert('In the doubleClickCall function \n recordID:'+rID+',  cs1:'+cs1);
     //var fst = cs1.charAt(0);
   
   if ( cs1.charAt(0) == 'A' || cs1.charAt(0) == 'N' || cs1.charAt(0) == 'K' || cs1.charAt(0) == 'W' ) 
        { alert("DX calls only on double click"); }
        else {
            fetch(`getDXstationInfo.php?rID=${encodeURIComponent(rID)}&cs1=${encodeURIComponent(cs1)}&netID=${encodeURIComponent(netID)}`, {
				method: 'GET',
			  })
				.then(response => response.text())
				.then(response => {
				  alert('The records have been updated for DX station ' + cs1);
				})
				.catch(error => {
				  // Handle any errors that occurred during the fetch request
				  console.error('Error:', error);
				});
        }
}

function whoAreYou() {
    var WRU = prompt("Your Call?");
}

function openW3W() {
    window.open("https://what3words.com");
}

function showDTTM() {
	if (!document.all&&!document.getElementById)
		return
		thelement=document.getElementById? document.getElementById("dttm2"): document.all.dttm2
		
        var mydate  = new Date();
		var lcldate = Math.floor(Date.now() / 1000); 
		    lcldate = Date(lcldate * 1000);
		    lcldate = lcldate.substring(0, lcldate.length-15); // removes the GMG offset on the end
        var utcdate = mydate.toUTCString();   
            utcdate = utcdate.substring(0, utcdate.length-3);  // removes the GMT from the end

        thelement.innerHTML='Local: '+lcldate+'<br><span style="color:red; ">UTC: '+utcdate+'</span>'; 
}

$(document).ready(function() {
	setInterval('showDTTM()', 1000);
	
	$( ".editable_selectACT" ).change(function() {
		alert( "you changed ACT" );
	});
});

// newNet function moved to newNet.js

function hideCloseButton(pb) {
    //alert('pb '+pb);
    //console.log("@227 in hideCloseButton function "+pb);
    if (pb == 1) {$("#closelog").addClass("hidden");}
}  

// checkIN function moved to checkIn.js

function fillFreqs() {
	//alert("in fillFreqs()");
	var netcall = $('#netcall option:selected').text();
	//var e = document.getElementById("netcall");  2018-05-20
	//var netcall = e.options[e.selectedIndex].text; 
		netcall = netcall.replace(/(\w+).*/,"$1");
			
	$(document).ready(function() {
		//$("#ourFrequencies").load("buildourFrequencies.php?call="+netcall); 
		//alert(netcall);
		$("#upperRightCorner").load("buildUpperRightCorner.php?call="+netcall);
		$("#urc").load("buildUpperRightCorner.php?call="+netcall);
	});
}

function fillURC() {
	var netcall = $('#netcall option:selected').text();
	//var e = document.getElementById("netcall");  2018-05-20
	//var netcall = e.options[e.selectedIndex].text; 
		netcall = netcall.replace(/(\w+).*/,"$1");
			
	$(document).ready(function() {
		$("#urc").load("buildUpperRightCorner.php?call="+netcall);
	});
}

$(document).ready(function () {
    $('#ckin1').on('keyup', function (e) {
        var keyCode = e.keyCode || e.which; 

  if (keyCode == 9 && !e.shiftKey) { 
    e.preventDefault();
    $('#cs1').focus();
    return false;
  } 

    });
});

$('.remember-selection').each(function(r) {
    var thisSelection = $(this);
    var thisId = thisSelection.attr('id');
    var storageId = 'remember-selection-' + thisId;
    var storedInfo = localStorage.getItem(storageId);
    if( storedInfo ) {
    	var rememberedOptions = storedInfo.split(',');
        thisSelection.val( rememberedOptions );
        
     //   alert(thisSelection.val( rememberedOptions ));
    };
    thisSelection.on('change', function(e) {
        var selectedOptions = [];
        thisSelection.find(':selected').each(function(i) {
            var thisOption = $(this);
            selectedOptions.push(thisOption.val());
        });
        localStorage.setItem(storageId, selectedOptions.join(','));
    });
}); // end of fillFreqs() function

// This function changes the onscreen button from Close Net to Net Closed as is appropriate based on the net
// that was selected from the dropdown of previous nets (id = select1)
// And it hides the 'Start a new net' button when a net is selected from the dropdown
// 0 means the net is still open
// 1 means the net is closed
function switchClosed() {
	$(".theBox").addClass("hidden");
	var status = $("#select1").find(":selected").attr("data-net-status");
	
	    //console.log("@420 status = "+status);
	    
	if ( status == 0 ) { 
		$("#closelog").html("Close Net"); 
	} else {
		$("#closelog").html("Net Closed");
	} 
	
	   var ispb = $("#ispb").html();    // Is this a pre-built? 1=yes, 0=no
       var pbStat = $("#pbStat").html();    // may not be working correctly
       var isopen = $("#isopen").html();    // Is this open? 1=yes, 0=no
       
	    //console.log("@432 status = "+status+" ispb = "+ispb+" pbStat= "+pbStat+" isopen= "+isopen);
       
    // Test if its a new PB being built, if so hide the 'close net' button
   // if ( $("#isopen").html() != "undefined") 
     //   { hideCloseButton(1); }
      
} // End of switchClosed function

// https://stackoverflow.com/questions/7697936/jquery-show-hide-options-from-one-select-drop-down-when-option-on-other-select
// These two functions control what will be seen in the newnetnm dropdown after the organization is chosen
// The close is after the filterSelectOptions function at about line 533
function filterSelectOptions(selectElement, attributeName, attributeValue) {
	if (selectElement.data("currentFilter") != attributeValue) {
	    selectElement.data("currentFilter", attributeValue);
	    var originalHTML = selectElement.data("originalHTML");
	    if (originalHTML)
	        selectElement.html(originalHTML)
	    else {
	        var clone = selectElement.clone();
	        clone.children("option[selected]").removeAttr("selected");
	        selectElement.data("originalHTML", clone.html());
	    }
	    if (attributeValue) {
	        selectElement.children("option:not([" + attributeName + "='" + attributeValue + "'],:not([" + attributeName + "]))").remove();
		}
}}
		
//$(document).ready(function () {
	$("#netcall").change( function() {
		var selected_org = $("#netcall option:selected").text();
			//alert('selected org= '+selected_org);
			if (selected_org == " Name Your Own ") { //alert("IN NYO");
				
	    	};
	});   
    
    var nc = $("#thenetcallsign").text();
        //console.log('@469 nc= '+nc);        
    
        $("#cs1").autocomplete({
            autoFocus: true,
            minLength: 3,
            source: "gethintSuspects.php",
            extraParams: {
              nc: nc
            },
            select: function(event, ui) {
              // This is under the readonly on the Fname input field below
              if (ui.item.label == 'NONHAM') {
                $('#Fname').prop('readonly', false);
              }
              $("#cs1").val(ui.item.label);
              $("#hints").val(ui.item.value);
              $("#Fname").val(ui.item.desc);
              var nc = $("#thenetcallsign").html();
              console.log('@487 tnc: '+nc);
              //return false;
            }
          }) // end autocomplete
          
          .data("ui-autocomplete")._renderItem = function(ul, item) {
            return $("<li>")
              .append("<a>" + item.label + " --->  " + item.desc + "</a>")
              .appendTo(ul);
          };
        
        
                   
//});

$(document).ready(function () {
	// if the value of isopen is 1 then the net is still open
	//var isopen = $("#isopen").val(); //alert(isopen);
});

/* Go get the needed stuff to populate the dropdowns to create a new net */
	$('#netcall').on('change',function() {
		var callID = $(this).val(); //alert(callID); //31:31:18:RSKC
		if(callID) {
			fetch('newnetOPTS.php', {
				method: 'POST',
				headers: {
				'Content-Type': 'application/json'
				},
				body: JSON.stringify({ call_id: callID })
				})
				.then(response => response.text())
				.then(html => {
				document.getElementById('newnetkind').innerHTML = html;
				//document.getElementById('newnetfreq').innerHTML = '<option value="">Select a net Frequency</option>';
				});
		}else {
			$('#newnetkind').html('<option value="">Select call first</option>');
            $('#newnetfreq').html('<option value="">Select call first</option>');
		}
	});   // End of netcall
	
	// This does the same thing for the frequency at the same time
	$('#netcall').on('change',function() {
		var kindID = $(this).val(); //alert(kindID);
		if(kindID) {
			fetch('newnetOPTS.php', {
				method: 'POST',
				body: JSON.stringify({kind_id: kindID}),
				headers: {
				'Content-Type': 'application/json'
				}
				})
				.then(response => response.text())
				.then(html => {
				document.getElementById('newnetfreq').innerHTML = html;
				});
		}
	});  // End of newnetkind
	
	// This does the same thing for the frequency if the kind changed
	$('#newnetkind').on('change',function() {
		var kindID = $(this).val();  //alert(kindID);
		if(kindID) {
			fetch('newnetOPTS.php', {
				method: 'POST',
				body: JSON.stringify({kind_id: kindID}),
				headers: {
				'Content-Type': 'application/json'
				}
				})
				.then(response => response.text())
				.then(html => {
				document.getElementById('newnetfreq').innerHTML = html;
				});
		}
	});  // End of newnetkind
//});

// All Below Copied from index.php on 2019-01-09

//$(document).ready(function() {
	$( ".netGroup" ).change(function() {
		var newnetnm = $('.netGroup option:selected').val(); 
			if (newnetnm == 7 ) {    
				//alert(" @572 "+newnetnm); //should be a 7
				window.open('BuildNewGroup.php');	
			};
			if (newnetnm == '29:23:29:EVENT' ){
				$(".last3qs").addClass("hidden");
			}
	});
	
	// This detects the click of the 'Copy a Pre-Built' button when building a new Pre-Built
	$( "#copyPB").click(function() {
		var netID = $("#idofnet").text().trim();
	//	var oldPB = prompt("Enter the log No. to clone.");
		var newKind = $("#activity").text().trim();
		
		fetch('PBList.php', {
			method: 'POST',
			body: JSON.stringify({ newPB: netID }),
			headers: {
			'Content-Type': 'application/json'
			}
			})
		.done(function(html) {  // .done is kind of line success: with more options
				$('#pbl').html(html);  // put output of fetch in the ID=pbl
				$('#pbl').modal();	   // display the modal made from pbl
				$('#pbl').removeClass('hidden');   // unhide the modal
				$('#copyPB').addClass('hidden');   // hide the button copyPB
			}
		);
	}); // End of click function for copyPB
	

	$('#columnPicker').mousedown(function(event) {
		switch (event.which) {
			case 1:
				//alert('Left');
				openColumnPopup();
				break;
			case 2:
				//alert('Middle');
				break;
			case 3:
				//alert('Right');
				openColumnPopup();
				break;
			default:
				alert('You have a strange Mouse!');
		}
	});
	
//}); // End of ready function



// This function runs clonePB.php to clone oldPB into newPB, its called the selectCloneBTN button in PBList.php
function fillaclone() {
		var netcalltoclone 	= $('#netcalltoclone option:selected').text();
		var netIDtoclone	= $('#netcalltoclone option:selected').val();
		var netID = $("#idofnet").text().trim();
	//	var oldPB = prompt("Enter the log No. to clone.");
		var newKind = $("#activity").text().trim();
		var oldCall = $(".cs1").text();
		console.log('@633 stand alone= '+netcalltoclone+' val= '+netIDtoclone+' oldCall= '+oldCall);
		fetch('clonePB.php', {
			method: 'POST',
			body: JSON.stringify({oldPB: netIDtoclone, newPB: netID, newKind: newKind, oldCall: oldCall}),
			headers: {
			'Content-Type': 'application/json'
			}
			}).then(response => {
			// Handle success
			}).catch(error => {
			// Handle error
			});
			showActivities(netID);
} // End of fillaclone() function

// This function is used to set the Pre-Built column in NetLog, and to display the cloning button on the main page.
function doalert(checkboxElem) {
  if (checkboxElem.checked) {
	//var txt;
	var r = confirm("Click OK to confirm you want to create a Pre-Built Net.")
		if (r == true) {
			//alert ("you preseed OK");
			//var oldPB = prompt("Enter the log No. to clone.");
		} else {
			//alert ("you pressed cancel");
			$('#pb').prop('checked', false); // Unchecks it
			$('.last3qs').removeClass('hidden');
			$('.radio-inline').addClass('hidden');  // hides the Click to create a pre-build event. I do this only
													// on the selection of 'cancel' from the alert box asking for
													// confirmation of the pre-built idea. My thinking is its not needed now.
		}
}}


// Below takes the place of openPreamble() in the NetManager.js
	//var windowObjectReference;
	var strWindowFeatures = "resizable=yes,scrollbars=yes,status=no,left=20px,top=20px,height=800px,width=600px";
		
function openPreamblePopup() {
   // alert("in the openPreamblePopup js");
	//var thisdomain = getDomain(); // alert("domain in openPreamble= "+thisdomain); //KCNARES  Weekly 2 Meter Voice Net
	  // domain in openPreamble= Johnson County ARES  Weekly 2 Meter Voice Net
    //var netid = $("#select1").val(); alert(netid);
	var thisdomain = $('#domain').html().trim();  //alert(thisdomain);
	var thisactivity = $('#activity').html().trim();
	//alert(thisactivity);
	var popupWindow = window.open("", "Preamble",  strWindowFeatures);
	
	fetch('buildPreambleListing.php?domain=thisdomain&activity=thisactivity', {
		method: 'GET'
		})
		.then(response => response.text())
		.then(html => {
		popupWindow.document.write(html);
		});
}
function openEventPopup() {
	//var thisdomain = getDomain();  //alert("domain in openPreamble= "+thisdomain); //KCNARES  Weekly 2 Meter Voice Net
	var thisdomain = $('#domain').html().trim();   // alert(thisdomain);
	var popupWindow = window.open("", "Events",  strWindowFeatures);
	    console.log('@693 NM-p2.js thisdomain= '+thisdomain);
	
	//alert(thisdomain);
	
	fetch('buildEventListing.php', {
		method: 'GET',
		headers: {
		'Content-Type': 'application/json'
		},
		body: JSON.stringify({ domain: thisdomain })
		})
}
function openClosingPopup() {
	//var thisdomain = getDomain();  //alert("domain in openPreamble= "+thisdomain); //KCNARES  Weekly 2 Meter Voice Net
	var thisdomain = $('#domain').html().trim();
	var popupWindow = window.open("", "Closing",  strWindowFeatures);
	
	//alert("thisdomain= "+thisdomain);
	//thisdomain= Kansas City National Weather Service Weekly 40 Meter Voice Net
	
	fetch('buildClosingListing.php', {
		method: 'GET',
		data: {domain: thisdomain}
		})
		
}

// Build an APRStt config file from the current net
function buildDWtt() {
	var netID = $("#idofnet").text().trim();
	var popupWindow = window.open("buildDWtt.php?q="+netID, "APRStt",  strWindowFeatures);
}

function openColumnPopup() {
	var netcall = $("#domain").html().trim();
	var popupWindow = window.open("columnPicker.php?netcall="+netcall, "Columns",  strWindowFeatures);
}



function seecopyPB() {
	$("#copyPB").removeClass("hidden");
}

//$(document).ready(function () {
  $("#bar-menu").click(function(e){
	  $("#bardropdown").removeClass('hidden');
  });
  
  // Which option was selected?
  $("select.bardropdown").change(function() {
	  var selectedOption = $(this).children("option:selected").val();
	  	//alert(selectedOption);
	  	if (selectedOption == 'EditCorner') {alert("Opton comming soon");}
	  	if (selectedOption == 'CreateGroup') {window.open("https://net-control.us/BuildNewGroup.php");}
	  	if (selectedOption == 'convertToPB') {convertToPB();}
	  	if (selectedOption == 'SelectView') {alert("Opton comming soon");}
	  	if (selectedOption == 'HeardList') {heardlist();}
	  	if (selectedOption == 'FSQList') {buildFSQHeardList();}
	  	if (selectedOption == 'APRStt') {buildDWtt();}
	  	if (selectedOption == 'findCall') {CallHistoryForWho();}
	  	if (selectedOption == 'DisplayHelp') {window.open("help.php");}
	  	if (selectedOption == 'allCalls') {window.open("https://net-control.us/buildUniqueCallList.php");}
	  	if (selectedOption == 'DisplayKCARES') {window.open("http://www.kcnorthares.org/policys-procedures/");}
	  	if (selectedOption == 'DisplayARES') {window.open("http://www.arrl.org/files/file/ARESFieldResourcesManual.pdf");}
	  	if (selectedOption == 'ARESManual') {window.open("http://www.arrl.org/files/file/Public%20Service/ARES/ARESmanual2015.pdf");}
	  	if (selectedOption == 'ARESELetter') {window.open("http://www.arrl.org/ares-el");}
	  	if (selectedOption == 'ARESTaskBook') {window.open("http://www.arrl.org/files/file/Public%20Service/ARES/ARRL-ARES-FILLABLE-TRAINING-TASK-BOOK-V2_1_1.pdf");}
	  	if (selectedOption == 'ARESPlan') {window.open("http://www.arrl.org/ares-plan");}
	  	if (selectedOption == 'ARESGroup') {window.open("http://www.arrl.org/ares-group-id-request-form");}
	  	if (selectedOption == 'ARESEComm') {window.open("http://www.arrl.org/emergency-communications-training");}
	  	

	  // hides the dropdown again	
	  $("#bardropdown").addClass('hidden');
	  $('select').prop('selectedIndex', 0);   // resets the select/options to 'Select One' the first option
  })
//})  // End ready function

function convertToPB() {	
    if ( typeof netID === 'undefined' || netID === null ) {
        var netID = prompt("Enter a Net Number to convert.");  //alert(netID);
    
        if (netID == "") {
            alert("Sorry no net number was given"); return; }
    }
    
    var popupWindow = window.open("", "_blank",  smallWindowFeatures);
	
	fetch('convertToPB.php', {
		method: 'POST',
		body: JSON.stringify({q: netID.trim()}),
		headers: {
		'Content-Type': 'application/json'
		}
		}).then(response => {
		return response.text();
		}).then(data => {
		var diditwork = '<span style="color:blue"><h2>SUCCESS:<br>Net No. '+netID+'<br> Has successfully been converted to a Pre-Build net.</h2></span>';
		popupWindow.document.write(diditwork);
		});
}

function heardlist() {
	var netID 	= $("#idofnet").html();
	
	    if ( typeof netID === 'undefined' || netID === null ) {
    	    var netID = prompt("Enter a Net Number sign");  //alert(netID);
		
            if (netID =="") {alert("Sorry no net number was selected"); return;}
	    }

	var popupWindow = window.open("", "_blank",  strWindowFeatures);
	
	fetch('buildHeardList.php', {
		method: 'POST',
		body: JSON.stringify({q: netID.trim()}),
		headers: {
		'Content-Type': 'application/json'
		}
		})
		.then(response => response.text())
		.then(html => {
		popupWindow.document.write(html);
		});
}

function buildFSQHeardList() {
    var netID 	= $("#idofnet").html();
        if ( typeof netID === 'undefined' || netID === null ) {
    	    var netID = prompt("Enter a Net Number sign");  //alert(netID);
		
            if (netID =="") {alert("Sorry no net number was selected"); return;}
	    }
	    
    var popupWindow = window.open("", "_blank",  strWindowFeatures);
    
    fetch('buildFSQHeardList.php', {
		method: 'POST',
		body: JSON.stringify({q: netID.trim()}),
		headers: {
		'Content-Type': 'application/json'
		}
		})
		.then(response => response.text())
		.then(data => {
		popupWindow.document.write(data);
		});
    
}

function theUsualSuspects() {
// V2 Modified 2024-05-30
  let netcall = '';
  let numofmo = 0;

  if (typeof netcall === 'undefined' || netcall === null || netcall === '') {
    netcall = prompt("Enter a Net call sign i.e. MODES, MESN, KCNARES");
    numofmo = prompt("How many months back? i.e. 3, 5, 12");
    
console.log("@828 netcall: "+netcall+(" numofmo: "+numofmo));

    if (netcall === "") {
      alert("Sorry no net was selected");
      return;
    }
  }

  const popupWindow = window.open("", "_blank", strWindowFeatures);
  let runurl = 'theUsualSuspects.php'; // default

  if (netcall === 'KCHEART' || netcall === 'kcheart') {
    runurl = 'getCallsHistoryByNetCall-FACILITY.php';
  }

  fetch(runurl, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `netcall=${encodeURIComponent(netcall.trim())}&numofmo=${encodeURIComponent(numofmo)}`
})
    .then(response => response.text())
    .then(html => {
      popupWindow.document.write(html);
    })
    .catch(error => {
      console.error('Error:', error);
      // Handle the error appropriately (e.g., display an error message to the user)
    });
console.log("@858 End of theUsualSuspects");
} // End theUsualSuspects()

//const netID = $("#idofnet").text().trim();
function geoDistance() {
    if ( $('#idofnet').length ) {
		var netID	 = $("#idofnet").html().trim();
	} else {
		var netID = prompt("Enter a Log number.");
	}
        	    
    //var popupWindow = window.open("", "_blank",  strWindowFeatures);
    
    window.open("geoDistance.php?NetID="+netID,"_blank", "toolbar=yes,scrollbars=yes,resizable=yes");
    //var runurl = 'geoDistance.php'; // default
    
}

// ===================================== put here from index.php at the bottom ------------------------------

// This is the setup for the popup windows
var strWindowFeatures = "resizable=yes,scrollbars=yes,status=no,left=20px,top=20px,height=800px,width=600px";
var smallWindowFeatures = "resizable=yes,scrollbars=yes,status=no,left=20px,top=20px,height=400px,width=600px";
// Put test scripts here
// All the script files here were copied to NetManager-p2.js on 2019-01-09
function buildRightCorner() {
	//var thisdomain = getDomain(); //alert(thisdomain);  // KCNARES  Digital Training Net
	var thisdomain = $("#domain").text().trim(); //alert("@901 in -p2.js= "+thisdomain);  // @901 in -p2.js= CREW2273
	var thisactivity = $("#activity").text().trim(); 
	//alert("@903 in index.php thisactivity= "+thisactivity);
		myWindow = window.open("buildRightCorner.php?domain="+thisdomain+"&act="+thisactivity);
}


// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("myBtn").style.display = "block";
    } else {
        document.getElementById("myBtn").style.display = "none";
    }
}

// When the user clicks on the button, scroll to the top of the document
function topFunction() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
};

// Hide the initial 'Hide Timeline' button
$("#timelinehide").hide();
$("#timelinesearch").hide();
$(".timelineBut3").hide();


// 2018-08-16 This script is used to show the subnets of any given net. It also shows the parent
// of any given net and can even show the parent and child of a net if it has one. Its been tested
// on up to 3 nets. I'm not sure what happens after that.
	$(document).on('click', '.subnetkey', function(event) {
    	
    	$(".timelineBut2").addClass("hidden");
    	
    	
		$("#subNets").html("");				  // Clears any previous entries
		var strr   = $(".subnetkey").val();   // The string of net numbers like 789, 790
		var netnos = strr.split(",");         // The string made into an array of nets
		
		$("#subNets").removeClass("hidden");  // Remove the hidden from the subNets div
		
			netnos.forEach(nets => {				// Loop through the list, usually only one 
				fetch('getactivities.php', {
					method: 'GET',
					})
					.then(response => response.text())
					.then(data => {
					var thelist = "#" + nets + "<br>" + data + "<br><br>";
					document.getElementById("subNets").innerHTML += thelist;
					})
					.catch(error => {
					alert('Sorry no nets.');
					});
			}); // End netnos.forEach
	}); // End on(click
	
function Clear_All_Tactical() {
    if (confirm("This process sets all Tactical Calls to blank.\r\n Are you sure this is what you want to do?\r\nIt can not be undone.")) {
        // if Yes
        var netID = $("#idofnet").text().trim(); 
        alert('apprently yes for net: '+netID);
		fetch('Clear_All_Tactical.php', {
			method: 'GET',
			data: {q: netID}
			});
    } else {
        // if cancel
        alert('apprently no');
    }
}
	

// BELOW IS THE FILTERFUNCTION, PUTINGROUPINPUT FUNCTIONS
function filterFunction(x) { 
  var input, filter, ul, li, a, i;
    if (x == 0 ) {
        input = document.getElementById("GroupInput");
    }else if (x == 1 ) {
        input = document.getElementById("KindInput");
    }else if (x == 2 ) {
        input = document.getElementById("FreqInput");
    }
  filter = input.value.toUpperCase();  // alert(filter);
    if (x == 0 ) {
        div = document.getElementById("GroupDropdown");
    }else if (x == 1 ) {
        div = document.getElementById("KindDropdown");
    }else if (x == 2 ) {
        div = document.getElementById("FreqDropdown");
    } 
  a = div.getElementsByTagName("a");  
  for (i = 0; i < a.length; i++) {
    txtValue = a[i].textContent || a[i].innerText; // alert(txtValue); //dont run it loops
    if (txtValue.toUpperCase().indexOf(filter) > -1) {
      a[i].style.display = "";
    } else {
      a[i].style.display = "none";
    }
  }
}

// These three function put the data into there respective dropdown-content DIV's
function putInGroupInput(pidi) {
    var hrefContent = pidi.getAttribute("href"); 
      //  alert(hrefContent); // #1:Weekly 2 Meter Voice:146.790MHz, T 107.2Hz:W0KCN:KCNARES
    var pidi2 = hrefContent.split(";")[3].trim();
    var org = hrefContent.split(";")[4].trim(); //alert(org); // KCNARES
    $("#org").html(org);
    
    // Get the defaults for the selected group from the pidi value
    var konID = hrefContent.split(";")[1].trim(); //alert('konID= '+konID);  // Find kindofnet id number
    var frqID = hrefContent.split(";")[2].trim(); //alert(frqID);  // Find frequency id number

    // Put the defaults for the selected group into the dropdowns
    $("#KindInput").val(konID);
    $("#FreqInput").val(frqID);

    $("#GroupInput").val(pidi2);
    $(".GroupDropdown-content").addClass("hidden");
}

function putInKindInput(pidi) {
   // alert("pidi= "+pidi);  // pidi= https://net-control.us/#23;Event
    var hrefContent = pidi.getAttribute("href");
    var pidi3 = (hrefContent.split(";")[1]);
     //   alert(pidi3);

    $("#KindInput").val(pidi3);
    $(".KindDropdown-content").addClass("hidden");
}

function putInFreqInput(pidi) {
   // alert("pidi= "+pidi);  // pidi= https://net-control.us/index.php#9:146.955MHZ,NoTone
    var hrefContent = pidi.getAttribute("href");
    var pidi4 = (hrefContent.split(";")[1]);
  //  alert(pidi4);

    $("#FreqInput").val(pidi4);
    $(".FreqDropdown-content").addClass("hidden");
}


// All the dropdowns are hidden by default, here we show them again as needed by the app.
// They are shown one at a time because they contain a lot of data, show when needed only.
function showGroupChoices() {
    $(".GroupDropdown-content").removeClass("hidden");
}

function showKindChoices() {
    $(".KindDropdown-content").removeClass("hidden");
}

function showFreqChoices() {
    $(".FreqDropdown-content").removeClass("hidden");
}

function blurGroupChoices() {
    $(".GroupDropdown-content").addClass("hidden");
}

function blurKindChoices() {
    $(".KindDropdown-content").addClass("hidden");
}

function blurFreqChoices() {
    $(".FreqDropdown-content").addClass("hidden");
}

function custom_setup() {
    $('#Fname').prop('type','hidden');
    $('#custom').prop('type','text');
    $('#section').prop('type','text');
}
    
    // This function is used to test the entered values of custom (category) and ARRL section
    // Test No. for 1 to 22
    // Test Letter for A, B, C, D, E, F
function checkFD() {
    var str = $("#custom").val().trim().toUpperCase();
    var letterNumber = /^(?:[1-9]|1[0-9]|2[0-2])[A-F]$/;
        if (letterNumber.test(str)) {
            var sec = $("#section").val().trim().toUpperCase();
            //alert("sec: "+sec);
            checkIn();
          //  return true;
        } else { 
            alert("Bad Category, please reenter"); 
            return false; 
        }
}


// This function creates a test net from the checkbox 
$(".tn").click(function() {
    var testEmail = prompt("What is your email address?\nOptional but helpful.");
    $('.testEmail').html(testEmail);
    
    if(!$( '#callsign' ).val()) {
        alert("Mandatory: Please provide your FCC call sign,\nnot TE0ST.");
        $( '#callsign' ).focus();
        //document.getElementById("callsign").focus();
    } else {   
    $('.last3qs').addClass('hidden');
    $('#GroupInput').val('TE0ST');
    $('#KindInput').val('Test Net');
    $('#FreqInput').val('Multiple');
}});

// This functin is used to show a net by its number and hide most of the controls
//$(document).ready(function() { do something here  });

function net_by_number() {
    var s = prompt("net ID?"); // get the net number
      if(s) {
		fetch('getkind.php', {
			method: 'GET',
			data: {q: s}
			})
			.then(response => response.text())
			.then(html => {
			var remarks = 'Net No: ' + s + ', ' + html;
			document.getElementById('remarks').innerHTML = remarks;
			})
			.catch(error => {
			alert('Last Query Failed, try again.');
			});
                
      } // end if(s)
    
    showActivities(s);
    
        // Hide some elements to prevent play
        $(".ckin1").addClass("hidden");
        $("#closelog").addClass("hidden");
        $("#cs1").addClass("hidden");
        $("#Fname").addClass("hidden");
        $("#newbttn").addClass("hidden");
        $(".tohide").addClass("hidden");
        
        $("#remarks").removeClass("hidden");
        //$("#remarks").html('You are browsing Net No.: '+s+' ');
                      
        // Set this value before the MySQL get the data to prevent editing
        $(".closenet").html('Net Closed');
}   


// Creates a CSV output file of the currently displayed net. It only uses the fields showing on screen
    // If additional fields are needed, add them to the screen first with the show/hide button   
    // https://www.jqueryscript.net/table/jQuery-Plugin-To-Export-Table-Data-To-CSV-File-table2csv.html     

$("#dl").click(function(){

    var idofnet = $("#idofnet").html();
        idofnet = 'netID'+idofnet+'.csv';
        idofnet = idofnet.replace(/\s/g,'');
            //alert(idofnet);
    let options = {
        "filename": idofnet
    }
    
$("#thisNet").table2csv('download', options);
});

function toCVS() {
    var netID = $("#idofnet").text().trim(); 
        if(netID) {
            //alert(netID); // 2825
            fetch('netCSVdump.php', {
				method: 'GET',
				data: {netID: netID}
				});  
        }
}

function sendEMAIL(adr,netid) {
    //alert(adr+'  '+netid);
    var link = "mailto:"+adr+"?subject=NCM Net# "+netid;
        window.location.href = link;
}

function sendGroupEMAIL() {
    var netID = $("#idofnet").text().trim();
        if(netID) {
            fetch('getNetEmails.php', {
				method: 'GET',
				data: {netID: netID}
				})
				.then(response => response.json())
				.then(data => sendEMAIL(data, netID));
        }
}

function stationTimeLineList(info) {
    var info = info.split(":");
	
	var callsign = info[0];
	var netID    = info[1];
	
	var popupWindow = window.open("", "_blank",  strWindowFeatures);
		
		// was using getStationsTimeLine.php, replaced 2021-12-10
		fetch('getStationICS-214A.php', {
			method: 'POST',
			headers: {
			  'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
			  netid: netID,
			  call: callsign
			})
		  })
		  .then(response => {
			if (!response.ok) {
			  throw new Error('Network response was not ok');
			}
			return response.text();
		  })
		  .then(html => {
			// Handle the successful response data
			popupWindow.document.write(html);
		  })
		  .catch(error => {
			console.error('Error:', error);
			// Handle the error if needed
		  });
}

// A right click on this field changes it to 'STANDBY' or 'STANDBY' gets changed to 'Resolved'
function rightClickTraffic(recordID) {
    //alert(recordID+" in rightClickTraffic()");
		if(recordID) {
    		//alert(recordID+" in rightClickTraffic()");
			fetch('rightClickTraffic.php', {
				method: 'POST',
				headers: {
				  'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
				  q: recordID
				})
			  })
			  .then(response => {
				if (!response.ok) {
				  throw new Error('Network response was not ok');
				}
				return response.text();
			  })
			  .then(data => {
				// Handle the successful response data
				// You can perform any necessary actions here
			  })
			  .catch(error => {
				console.error('Error:', error);
				alert(error.message);
			  });
		}
		var netID = $("#idofnet").html().trim();
		showActivities( netID );
};

// This function changes a blank or no to yes, part of facility handling
function rightClickOnSite(recordID) {
    //if(recordID) {
        // The "Click to edit" is coming from jquery.jeditable.js but i don't know why
        fetch('rightClickOnSite.php', {
			method: 'POST',
			headers: {
			  'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
			  q: recordID
			})
		  })
		  .then(response => {
			if (!response.ok) {
			  throw new Error('Network response was not ok');
			}
			return response.text();
		  })
		  .then(data => {
			// Handle the successful response data
			// You can perform any necessary actions here
			// console.log(data);
		  })
		  .catch(error => {
			console.error('Error:', error);
			alert(error.message);
		  });
    //}
    var netID = $("#idofnet").html().trim();
	showActivities( netID );
};

// A right click on this field looks up the distrct from the HPD table and updates it in the NetLog
function rightClickDistrict(str) {
	if(str) {
    	var split    = str.split(",");
        var recordID = split[0];
        var state    = split[1];
        var county   = split[2];

		console.log('@1306 str:'+str+'\n recordID:'+recordID+', state:'+state+', county:'+county);
		
		fetch('rightClickDistrict.php', {
			method: 'POST',
			headers: {
			  'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
			  q: recordID,
			  st: state,
			  co: county
			})
		  })
		  .then(response => {
			if (!response.ok) {
			  throw new Error('Network response was not ok');
			}
			return response.text();
		  })
		  .then(data => {
			// Handle the successful response data
			// You can perform any necessary actions here
		  })
		  .catch(error => {
			console.error('Error:', error);
			// Handle the error if needed
		  });
	}
	var netID = $("#idofnet").html().trim();
	showActivities( netID );
};

// A right click on 'In' changes it to 'Out' and v.v.
function rightClickACT(recordID) {
    //alert(recordID+" in rightClickACT()");
		if(recordID) {
    		//console.log(recordID+" in rightClickACT()");
			fetch('rightClickACT.php', {
				method: 'POST',
				headers: {
				  'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
				  q: recordID
				})
			  })
			  .then(response => {
				if (!response.ok) {
				  throw new Error('Network response was not ok');
				}
				return response.text();
			  })
			  .then(data => {
				// Handle the successful response data
				// You can perform any necessary actions here
				// refresh();
			  })
			  .catch(error => {
				console.error('Error:', error);
				// Handle the error if needed
			  });
		}
		var netID = $("#idofnet").html().trim();
		showActivities( netID );
};

// require_once "wxLL2.php";	at line 45 
// This function uses the clickers callsign to look up their lat/lon from the stations table. 
function newWX() {
    var str = prompt('What is your callsign?');
        if (str =="") { alert("OK no valid call was entered");
        }else { var thiscall = [str.trim()]; 
               console.log('@1378 in index.php, The requested call is '+thiscall);
			   fetch(`getNewWX.php?str=${encodeURIComponent(str)}`, {
				method: 'GET',
			  })
			  .then(response => {
				if (!response.ok) {
				  throw new Error('Network response was not ok');
				}
				return response.text();
			  })
			  .then(data => {
				// Handle the successful response data
				// console.log(data);
				$(".theWX").html(data);
			  })
			  .catch(error => {
				console.error('Error:', error);
				alert('Last Query Failed, try again.');
			  });
	      } // end of else
}; // end newWX function



// This controls the where and when of the tip bubbles at <button class="tbb" in index.php
$(document).ready(function(){
    $(".tbb").click(function(){
        if ($("#refbutton").hasClass("hidden") ) {
            $(".tb1").toggle();
        }else {
            $(".tb2").toggle();
            
        }
    });
}); 

// this function creates a table view of the net call in the NetKind table if it doesn't already exist
function createNetKindView() { 
    var viewnm = $("#GroupInput").val().trim();
    //console.log("@1417 createNetKindView() in NetManager-p2.js viewnm= "+viewnm);
        if(viewnm) {
            fetch(`buildView4netcall.php?viewnm=${encodeURIComponent(viewnm)}`, {
				method: 'GET',
			  })
			  .then(response => {
				if (!response.ok) {
				  throw new Error('Network response was not ok');
				}
				return response.text();
			  })
			  .then(data => {
				// Handle the successful response data
				console.log("@1439 createNetKindView in NetManager-p2.js viewnm= " + viewnm);
			  })
			  .catch(error => {
				console.error('Error:', error);
				// Handle the error if needed
			  });
        }

} // end createNetKindView()
