// newnet.js
// This function is used to create a new net.
// V3 Updated: 2024-06-25

function newNet(str) {   
    console.log('newNet function called');

    // All callsigns require at least one number 
    // The callsign div must have a value
    var firstDigit = $('#callsign').val().match(/\d/);
    if (!$('#callsign').val() || firstDigit == null) {
        var usedCS = prompt("What is your FCC call sign?");
        if (!usedCS || usedCS.match(/\d/) == null) {
            location.reload(); 
            return;
        } else { 
            $("#callsign").val(usedCS.trim()); 
        }
    }

    $(".newstuff").addClass("hidden");
    $(".theBox").addClass("hidden"); 
    $("#refbutton").removeClass("hidden");
    $("#makeNewNet").addClass("hidden");
    $("#form-group-1").show();
    $("#timer").removeClass("hidden");
    
    var newnetnm = $("#GroupInput").val().trim();
    console.log("newnetnm: " + newnetnm);
    
    var netcall = $("#GroupInput").val().trim();
    console.log("netcall: " + netcall);

    var newnetfreq = $("#FreqInput").val() || '144.520MHz';
    
    var newnetkind = $("#KindInput").val();
    
    var callsign = $("#callsign").val().trim().toUpperCase(); 
    
    if (netcall == '') {
        netcall = prompt("What should I use as the net call? ex: NARES ") || callsign;
    }
    if (newnetkind == '') {
        newnetkind = prompt("What should I use as a group name or call? ex: North ARES") || callsign;
    }
        
    if (callsign == 'TEST') {
        callsign = prompt('Please enter your FCC call sign') || '';
    }
    
    if (newnetkind.includes("MARS")) {
        setCookie('custom', '50, 51', 5);
        custom_setup();
        $('#custom').prop('type','text').removeClass("hidden").attr('placeholder', 'Comment');
    }

    var org = $("#org").html();
    
    var pb = $("#pb").is(':checked') ? 1 : 0;
    
    var testnet = $("#testnet").is(':checked') ? 'y' : 'n';
     
    var satNet = $("#satNet").val();
    var testEmail = $("#testEmail").html();

    var str = `${callsign}:${netcall}:${org}:${newnetfreq}:${satNet}:${newnetkind}:${pb}:${testEmail}:${testnet}`;
    
    // Build the upper right corner
    $("#upperRightCorner, #urc").load("buildUpperRightCorner.php?call=" + newnetnm);
    
    console.log("Sending fetch request to newNet.php");
    
    fetch('newNet.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({ q: str }),
})
.then(response => {
    console.log('Response status:', response.status);
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json().catch(err => {
        console.error('Error parsing JSON:', err);
        throw new Error('Failed to parse server response as JSON');
    });
})
.then(data => {
    console.log('Response from newNet.php:', data);
    if (!data.netID || !data.netcall || !data.activity) {
        console.error('Incomplete data received:', data);
        throw new Error('Incomplete data received from server');
    }
    console.log('New netID:', data.netID);
    
    // Set the current net ID
    setCurrentNetID(data.netID);
    
    // Call showActivities with the new net data
    if (typeof showActivities === 'function') {
        showActivities(data.netID, `${data.netcall},${data.activity}`);
    } else {
        console.error('showActivities function is not defined');
    }
    
    // Update the dropdown to select the new net
    updateDropdown(data.netID, data.netcall, data.activity);

    // Additional success actions
    $("#form-group-1").hide();
    $(".theBox").removeClass("hidden");
    $("#makeNewNet").addClass("hidden");
    
    // You might want to trigger any other necessary UI updates here
})
.catch(error => {
    console.error('Error in newNet function:', error);
    alert('An error occurred while creating the new net. Please try again.');
});

}

function updateDropdown(netID, netcall, activity) {
    console.log('updateDropdown called with:', netID, netcall, activity);
    
    const select = document.getElementById('select1');
    if (!select) {
        console.error('select1 element not found');
        return;
    }

    const option = new Option(`${netcall} Net #: ${netID} --> ${activity}`, String(netID));
    option.selected = true;
    select.add(option);
    
    if ($.fn.selectpicker) {
        $(select).selectpicker('refresh');
    } else {
        console.warn('selectpicker not available');
    }

    console.log('Dropdown updated');
}

window.newNet = newNet;