// showActivities.js
// This script is the central function for displaying and managing net activities.
// It fetches the table structure and net data, then combines them to create the full table HTML.
// Key responsibilities include:
// 1. Fetching table structure from getTablestructure.php
// 2. Fetching net data from getactivities.php
// 3. Combining the structure and data to build the complete table
// 4. Updating the UI with the net information
// 5. Setting up interactive features like cell editing and real-time updates via SSE
// 6. Handling UI adjustments based on the selected net
// 7. Initializing server-sent events for real-time updates
// This function is crucial for dynamically loading and displaying net information in the NCM interface.
// Written: 2015
// Updated: 2024-09-24

function setCurrentNetID(netID) {
    const currentNetIDElement = document.getElementById('currentNetID');
    if (currentNetIDElement) {
        currentNetIDElement.value = netID;
        console.log('Set currentNetID to:', netID);
        // Dispatch an event to notify other parts of the application
        window.dispatchEvent(new Event('netChanged'));
    } else {
        console.error('currentNetID element not found in the DOM');
    }
}

function changeNet(str) {
    console.log('changeNet called with:', str);
    
    const netID = str.split(',')[0];
    
    if (!netID) {
        console.error('Invalid netID in changeNet');
        return;
    }

    setCurrentNetID(netID);

    console.log('Net changed to:', netID);
}

function showActivities(netID, str, sseData = null) {
    console.log('showActivities called with:', netID, str, sseData);
    netID = String(netID);
    
    RefreshGenComm();

    // Set the currentNetID
    changeNet(netID + (str ? ',' + str : ''));

    // Upper right corner information
    if (str) {
        var netcall = str.split(",")[0];
        $("#upperRightCorner").load("buildUpperRightCorner.php?call=" + encodeURIComponent(netcall));
    
        // Custom net handling
        if (str.indexOf("MARS") >= 0) {
            $('#Fname').prop('type', 'hidden');
            $('#custom').prop('type', 'text').removeClass("hidden").attr('placeholder', 'Traffic For:');
        } else {
            $('#Fname').prop('type', 'text');
            $('#custom').prop('type', 'hidden');
            $('#section').prop('type', 'hidden');
        }
    }

    // UI adjustments
    $("#refbutton, #refrate, #time").removeClass("hidden");
    $("#openNets, #subNets, .newstuff, .makeaselection, #grad1").addClass("hidden");
    $("#tb").removeClass("tipsbutton").addClass("tipsbutton2");

    if (str == '0') {
        $("#closelog, #time, .multiselect, #primeNav, #cb1span, #refbutton, #refrate").addClass("hidden");
    } else {
        $("#closelog, #time, .multiselect, #primeNav").removeClass("hidden");
    }

    if (str == "newNet()") {
        newNet();
        return;
    }

    if (str == "") {
        $("#actLog, #netIDs, #closelog").html("");
        $("#cb1").prop("checked", false);
        return;
    }

    // Main fetch operation
    console.log('About to fetch table structure');
    fetch("getTableStructure.php")
    .then(response => {
        console.log('Table structure response received');
        if (!response.ok) throw new Error('Failed to fetch table structure');
        return response.text();
    })
    .then(tableStructure => {
        console.log('Table structure received, about to fetch getactivities.php');
        return fetch("getactivities.php?q=" + encodeURIComponent(netID)).then(response => {
            if (!response.ok) throw new Error('Failed to fetch activities');
            return response.text();
        }).then(data => {
            return { tableStructure, data };
        });
    })
    .then(({ tableStructure, data }) => {
    console.log('Raw data received from getactivities.php:', data);
    if (data.trim() === '') {
        console.error('Received empty data from getactivities.php');
        throw new Error('Empty data received from server');
    }
    
    // Use the table structure from PHP if available, otherwise use the fetched structure
    const tableHtml = window.tableStructure || tableStructure;
    
    // Insert the table structure
    document.getElementById('actLog').innerHTML = tableHtml;
    
    // Now find the table body and populate it
    const tableBody = document.getElementById('netBody');
    if (tableBody) {
        tableBody.innerHTML = data;
    } else {
        console.error('Table body not found after inserting structure');
    }

    console.log('DOM updated with table HTML and data');
    
    // Apply column management
    if (typeof window.applyColumnManagement === 'function') {
        window.applyColumnManagement('thisNet');
    } else {
        console.warn('applyColumnManagement function not found');
    }


        
        if (document.getElementById("thisNet")) {
            sorttable.makeSortable(document.getElementById("thisNet"));
            console.log('Table made sortable');
        } else {
            console.error('thisNet table not found for sorting');
        }

        try {
            $(document).ready(CellEditFunction);
            testCookies(netcall);

            var tz_domain = getCookie("tz_domain");
            tz_domain == 'Local' ? goLocal() : goUTC();

            // Additional UI updates
            $("#netIDs").html("");
            $("#cb1").prop("checked", false);
            $("#makeNewNet").addClass("hidden");
            $("#csnm, #cb0, #forcb1").removeClass("hidden");

            if (str == 0) {
                $(".c1, .c2, .c3").hide();
            }

            var ispbStat = $('#pbStat').html();
            console.log('UI updates completed');
            
            // If we have SSE data, update the UI
            if (sseData) {
                updateUIWithSSEData(sseData);
            }
        
            // Initialize SSE after loading net data
            if (typeof initSSE === 'function') {
                initSSE();
            } else {
                console.error('initSSE function not found');
            }

        } catch (error) {
            console.error('Error in UI updates:', error);
        }
    })
    .catch(error => {
        console.error('Error in showActivities:', error);
    });
} // End showActivities()

function updateUIWithSSEData(sseData) {
    console.log('Updating UI with SSE data:', sseData);
    if (typeof window.addNewCallsignRow === 'function') {
        var newRow = window.addNewCallsignRow(sseData, sseData.recordID);
        if (newRow) {
            console.log('New row added to table:', newRow.outerHTML);
            newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            newRow.style.backgroundColor = '#ffff99';
            setTimeout(() => { 
                newRow.style.backgroundColor = ''; 
                console.log('Row highlight removed');
            }, 3000);
        } else {
            console.error('Failed to create new row from SSE data');
        }
    } else {
        console.error('addNewCallsignRow function not found');
    }
} // End updateUIWithSSEData()

// allow global access
window.showActivities = showActivities;
window.updateUIWithSSEData = updateUIWithSSEData;