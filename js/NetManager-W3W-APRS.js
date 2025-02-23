// NetManager-W3W-APRS.js
// Create a dialog box with radio buttons and a comment text area
// V4 Updated: 2024-09-14 (Using Fetch API)

// Create the dialog box with radio buttons and a comment text area
function createDialogBox(calledFrom) {
    const dialogBox = $("<div>")
        .attr("id", "APRSDialog")
        .append($("<h3>").text("Choose an option:"))
        .append($("<input>").attr({"type": "radio", "id": "option1", "name": "option", "value": "option1"}))
        .append($("<label>").attr("for", "option1").html('Change of Station Location Only:<br> (<span class="Boxhighlight">Change Only The Station Location</span>)'))
        .append($("<br>"))
        .append($("<input>").attr({"type": "radio", "id": "option2", "name": "option", "value": "option2"}))
        .append($("<label>").attr("for", "option2").html('Comment:<br> (<span class="Boxhighlight">The Sun came out</span>)'))
        .append($("<br>"))
        .append($("<input>").attr({"type": "radio", "id": "option3", "name": "option", "value": "option3"}))
        .append($("<label>").attr("for", "option3").html('Object:<br> (<span class="Boxhighlight">Tree across house</span>)'))
        .append($("<br>"))
        .append($("<h3>").text("Enter the W3W string here:"))
        .append($("<textarea>").attr({"id": "w3wfield", "rows": "1"}).css("width", "100%"))
        .append($("<br>"))
        .append($("<h3>").text("Enter a general comment here:"))
        .append($("<textarea>").attr({"id": "comment", "rows": "4"}).css("width", "100%"))
        .append("</div>");
    console.log("dialogBox created");
    return dialogBox;
}

// Reset the dialog box
function resetDialogBox() {
    $("#APRSDialog input[name='option']").prop("checked", false);
    $("#APRSDialog #comment").val("");
    console.log("dialogBox reset");
}

// This function is used by the aprs_call & W3W columns from rowDefinitions.php
function getAPRSLocations(parmsString) {
    console.log("1) parmsString: " + parmsString);
    let parmsArray = parmsString.split(',');
    let calledFrom = parmsArray[parmsArray.length - 1].trim();
    let url;
    
    console.log("calledFrom:", calledFrom);
    // Determine which PHP program to run
    if (calledFrom === 'APRS') {
        url = 'locations_APRS.php';
    } else if (calledFrom === 'W3W') {
        url = 'locations_W3W.php';
    }
    
    // Show the dialog box
    const dialogBox = createDialogBox(calledFrom);
    // Set the first 'LOC' radio button as checked by default
    dialogBox.find("input[name='option'][value='option1']").prop('checked', true);
    $("body").append(dialogBox);
    
    dialogBox.dialog({
        modal: true,
        buttons: {
            OK: function() {
                // Retrieve the selected option and comment
                const option = $("input[name='option']:checked").val();
                const comment = $("#comment").val().trim();
                const w3wfield = $("#w3wfield").val().trim();

                // Create a new Date object
                let currentTime = new Date();
                let hours = currentTime.getHours().toString().padStart(2, '0');
                let minutes = currentTime.getMinutes().toString().padStart(2, '0');
                let timestamp = hours + '' + minutes;
                
                const parms = parmsString.trim().split(',');
                let aprs_call = parms[0].toUpperCase();             
                console.log("3) aprs_call: " + aprs_call);
                let recordID = parms[1].trim();
                let CurrentLat = parms[2].trim();
                let CurrentLng = parms[3].trim();
                let cs1 = parms[4].trim();
                let nid = parms[5].trim();
                let calledFrom = parms[6].trim();
            
                // Concatenate the comment with the option prefix
                let objName = "MOV::" + timestamp;
                if (option === "option2") {
                    objName = "COM::" + timestamp + ' ' + aprs_call + ' : ' + comment;
                } else if (option === "option3") {
                    objName = "OBJ::" + timestamp + ' ' + aprs_call + ' : ' + comment;
                } 
                   
                console.log("6) objName: " + objName);
                console.log("7) Record ID: " + recordID);
                console.log("8) Current Latitude: " + CurrentLat);
                console.log("9) Current Longitude: " + CurrentLng);
                console.log("10) CS1: " + cs1);
                console.log("11) NID: " + nid);
                console.log("12) Called From: " + calledFrom);
                console.log("13) comment: " + comment);
                console.log("14) w3wfield: " + w3wfield);
                console.log("15) url: " + url);
                
                // Construct the query string
                const queryString = new URLSearchParams({
                    aprs_call: parms[0].trim(),
                    recordID: parms[1],
                    CurrentLat: parms[2].trim(),
                    CurrentLng: parms[3].trim(),
                    cs1: parms[4].trim(),
                    nid: parms[5],
                    objName: objName.trim(),
                    comment: comment.trim(),
                    w3wfield: w3wfield.trim()
                }).toString();

                // Run Fetch
                fetch(`${url}?${queryString}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("16) success from fetch");
                    console.log("17) Response:", data);
                    
                    if (data.error) {
                        console.error("Error:", data.error);
                        alert("Error: " + data.error);
                    } else if (data.success) {
                        console.log("18) Operation successful:", data.message);
                        // Handle success (e.g., update UI)
                    }
                })
                .catch(error => {
                    console.error("Error in getAPRSLocations fetch:", error);
                    alert("An unexpected error occurred. Please check the server logs.");
                });
                
                // Close the dialog box
                $(this).dialog("close");
            },
            Cancel: function() {
                // Close the dialog box
                $(this).dialog("close");
            }
        },
        close: function() {
            // Reset the dialog box values
            resetDialogBox();
            $(this).dialog("destroy").remove();
        }
    });
    
    // This little piece is to move (focus) the cursor to the dialogbox
    if (calledFrom === 'W3W') {
        setTimeout(function() {
            $("#w3wfield").focus();
        }, 100);
        
        // Prevent editing if the field has the c24 class
        $(".c24").addClass("readonly").prop("readonly", true);
    }
    
    // Disable textarea when 'APRS' is selected
    if (calledFrom === 'APRS') {
        dialogBox.find("#w3wfield").prop('disabled', true);
    }
}