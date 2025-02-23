// NetManager-p3.js
// V2 Updated: 2024-06-01

// function to handled dialog modal for the question mark in circle at time line & other places
// https://www.tutorialspoint.com/jqueryui/jqueryui_dialog.htm
$ ( function() {
    $( "#q-mark-in-circle" ).dialog({
        autoOpen:false, 
               buttons: {
                  OK: function() {$(this).dialog("close");}
               },
               title: "Search Advisory:",
               position: {
                  my: "center",
                  at: "center"
               }
    });
                                     
    $( "#QmarkInCircle" ).click(function() {
        $( "#q-mark-in-circle" ).dialog( "open" );
    });
}); // end click @ #q-mark-in-circle()



(function() {

    var quotes = $(".quotes"); //variables
    var quoteIndex = -1;
    
    function showNextQuote() {
        ++quoteIndex;  //increasing index
        quotes.eq(quoteIndex % quotes.length) //items ito animate?
            .fadeIn(6500) //fade in time
            .delay(250) //time until fade out
            .fadeOut(5800, showNextQuote); //fade out time, recall function
    }
    showNextQuote();  
})();

$("body").click(function(){
    $(".quotes").addClass("hidden");
    $(".preQuote").addClass("hidden");
});


// This javascript function tests the callsign being used to start a new net as to being in a list of callsigns that did not close a previous net.
function checkCall() {
    const cs = $("#callsign").val().trim().toUpperCase();
    const listOfCalls = new Set( ['ah6ez' ]);
    const isCallInSet = listOfCalls.has($("#callsign").val());
    
    console.log('@755 in index.php cs: '+cs+'  listOfCalls: '+listOfCalls+'  isCallInSet:  '+isCallInSet);
    
    // If the callsign starting this net is in the above list then ask for his email to send him a message
    if (!isCallInSet == '') {
        var mail = prompt('Please enter your email address.');
            if (mail == '' || mail == null) {
                alert("Please be sure to close your net when finished. Thank you!");
            } else {

                var str = cs+":"+mail;  //alert(str);
                console.log('@737 str= '+str);
            
                $.ajax({
                    type: 'GET',
                    url: 'addEmailToStations.php',
                    data: {q: str},
                    success: function(response) { 
                        //alert(response);
                } // end success
                }) // end ajax
                } // else 
        // Possible ways to send an email
        // Javascript:  https://smtpjs.com
        // PHP:         https://www.w3schools.com/php/func_mail_mail.asp
        // AJAX:        Put the collected email into his record in the stations table.
    } // End if
} // end checkCall function

// This function is used in the DIV GroupDropdown by the input **** DO NOT DELETE ++++
function removeSpaces(str) {
  return str.replace(/\s+/g, '');
}

function openDialogAndMoveCursor(recordID, w3w, latitude, longitude, callsign, netID) {
    // Open the dialog box
    document.getElementById('dialogBox').showModal();
    
    // Move the cursor to the textbox in the dialog box
    document.getElementById('w3wfield').focus();
    
    // Optionally, you can perform any additional actions based on the provided data (recordID, w3w, latitude, longitude, callsign, netID)
    // Example: console.log(recordID, w3w, latitude, longitude, callsign, netID);
} // End openDialogAndMoveCursor()


// Add event listener to the table
var table = document.querySelector('table');
table.addEventListener('input', function(event) {
    var cell = event.target;
    var recordID = cell.dataset.recordId;
    var column = cell.dataset.column;
    var value = cell.textContent.trim();
    handleChange(recordID, column, value);
});

// Add event listener to the delete buttons
var deleteButtons = document.querySelectorAll('.delete');
deleteButtons.forEach(function(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        var recordID = this.parentNode.dataset.recordId;
        handleDelete(recordID);
    });
});

// Check if the browser supports SSE
if (typeof (EventSource) !== 'undefined') {
    var source = new EventSource('sse.php');
    source.addEventListener('update', function(event) {
        // Handle the 'update' event here
        console.log('Received SSE event:', event);
    });
} else {
    console.log('Server-Sent Events not supported.');
}


// Get the footer element
/*
const footerElement = document.getElementById('pageFooter');

// Add an event listener to the document.body
document.body.addEventListener('click', hideFooterOnFirstInteraction, { once: true });
document.body.addEventListener('keydown', hideFooterOnFirstInteraction, { once: true });

// Function to hide the footer element
function hideFooterOnFirstInteraction() {
    footerElement.textContent = ''; // Set the text content to an empty string
}    
*/