// addToNet.js
// Used to help with the checkIn process
// Created: 2024-06-25
// v2 Updated: 2024-07-07

console.log('addToNet.js activated');

window.addToExistingNet = function() {
    console.log("addToExistingNet function called");
    var netID   = $("#idofnet").text().trim();
    var cs1     = $("#cs1").val().toUpperCase().trim();
    var netcall = $("#thenetcallsign").text().trim();
    var pb      = 0; // Set default value to 0
    
    console.log("addToExistingNet: NetID:", netID, "Callsign:", cs1, "Netcall:", netcall, "PB:", pb);
    var data = { netID: netID, callsign: cs1, netcall: netcall, pb: pb };
    console.log("Data being sent to addToNet.php:", JSON.stringify(data));
    
    fetch('addToNet.php', {
        method: 'POST',
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
    console.log('Full response from addToNet.php:', JSON.stringify(responseData));
    
    if (responseData.success) {
        console.log('Server response indicates successful addition');
        console.log('Response data:', responseData.data);
        if (typeof window.addNewCallsignRow === 'function') {
            console.log('Attempting to add new row using addNewCallsignRow');
            console.log('Data being passed to addNewCallsignRow:', responseData.data, responseData.data.recordID);
            var newRow = window.addNewCallsignRow(responseData.data, responseData.data.recordID);
            if (newRow) {
                console.log('New row added successfully');
                console.log('New row HTML:', newRow.outerHTML);
                
            // Populate empty cells with data
            Object.entries(responseData.data).forEach(([key, value]) => {
                const cell = newRow.querySelector(`[data-column="${key}"]`);
                if (cell && !cell.textContent) {
                    cell.textContent = value;
                }
            });    
                
                newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                if (typeof refreshUI === 'function') {
                    refreshUI();
                }
            } else {
                console.error('Failed to add new row');
            }
        } else {
            console.error('addNewCallsignRow function not available');
        }
        $("#cs1").val('');
        $("#cs1").focus();
    } else {
        console.error('Server response indicates failure:', responseData.message);
    }
})
    .catch(error => {
        console.error('Error in addToExistingNet:', error);
    });
};

// Basic checks
console.log("addNewCallsignRow function exists:", typeof addNewCallsignRow === 'function');
console.log("refresh function exists:", typeof refresh === 'function');