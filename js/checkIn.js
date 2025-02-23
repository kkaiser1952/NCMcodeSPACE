// checkIn.js
// Used to help with the checkIn process
// v4 Updated: 2024-08-29
console.log('OK to here, Now in checkIn.js');

var columnDefinitions = window.columnDefinitions || [];
console.log('OK to here, columnDefinitions:', columnDefinitions);

document.addEventListener('DOMContentLoaded', function() {
    console.log('OK to here, DOMContentLoaded event fired');
    const checkInButton = document.querySelector('.ckin1');
    console.log('OK to here, checkInButton:', checkInButton);
    if (checkInButton) {
        checkInButton.addEventListener('click', handleCheckIn);
    }

    const callsignInput = document.getElementById('cs1');
    console.log('OK to here, callsignInput:', callsignInput);
    if (callsignInput) {
        callsignInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                handleCheckIn();
            }
        });
    }
});

let checkInHandled = false;

window.handleCheckIn = function() {
    if (document.readyState !== 'complete') {
        console.log('DOM not fully loaded. Current state:', document.readyState);
    }
    if (checkInHandled) {
        console.log("Check-in already in progress, skipping duplicate execution");
        return;
    }
    checkInHandled = true;

    const cs1 = document.getElementById('cs1').value.trim();
    const currentNetID = document.getElementById('currentNetID').value;
    console.log('OK to here, cs1:', cs1);
    console.log('OK to here, currentNetID:', currentNetID);

    if (!cs1 || !/^[A-Za-z0-9]+$/.test(cs1)) {
        alert("Please enter a valid callsign (letters and numbers only)");
        checkInHandled = false;
        return;
    }

    const str = `${currentNetID}:${cs1}:0:TE0ST:0:`;
    console.log("OK to here, Sending check-in data:", str);

    fetch('checkIn.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'q=' + encodeURIComponent(str)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log("OK to here, Raw response from server:", text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.warn("Error parsing JSON:", e);
            const jsonStart = text.indexOf('{');
            const jsonEnd = text.lastIndexOf('}') + 1;
            if (jsonStart >= 0 && jsonEnd > jsonStart) {
                const jsonText = text.slice(jsonStart, jsonEnd);
                console.log("Extracted JSON text:", jsonText);
                return JSON.parse(jsonText);
            } else {
                throw new Error("Unable to extract valid JSON from response");
            }
        }
    })
    .then(data => {
        console.log("OK to here, Parsed data:", data);
        if (data.success && data.checkInRowData) {
            console.log('columnDefinitions:', columnDefinitions);
            let addedRow = addNewCallsignRow(data.checkInRowData, data.checkInRowData.recordID);
            if (!addedRow) {
                console.error('Failed to add new row to UI, attempting fallback method');
                const netBody = document.getElementById('netBody');
                if (netBody) {
                    addedRow = document.createElement('tr');
                    Object.entries(data.checkInRowData).forEach(([key, value]) => {
                        const cell = document.createElement('td');
                        cell.textContent = value;
                        addedRow.appendChild(cell);
                    });
                    netBody.insertBefore(addedRow, netBody.firstChild);
                    console.log('Row added using fallback method');
                } else {
                    console.error('netBody not found, cannot add row');
                }
            }
            
            if (addedRow) {
                console.log("Row added successfully, applying styling");
                applyRowStyling(addedRow, data.checkInRowData);
                
                // reset the cursor and clear the entered callsign
                document.getElementById('cs1').value = '';
                document.getElementById('cs1').focus();
            } else {
                console.error('Failed to add new row to UI');
                alert("Check-in successful, but failed to update the display. Please refresh the page.");
            }
        } else {
            console.error("Check-in failed:", data.message || "No error message provided");
            alert("Check-in failed. Please try again or contact support.");
        }
    })
    .catch(error => {
        console.error("Error during check-in:", error);
        alert("An unexpected error occurred during check-in. Please try again or contact support.");
    })
    .finally(() => {
        checkInHandled = false;
        console.log("Check-in process completed");
    });
};

function addNewCallsignRow(checkInRowData, recordID) {
    console.log('addNewCallsignRow called with:', JSON.stringify(checkInRowData), recordID);
    const netBody = document.getElementById('netBody');
    console.log('netBody found:', netBody);
    if (!netBody) {
        console.error('netBody element not found');
        return null;
    }
    console.log('Current row count:', netBody.children.length);
    const newRow = document.createElement('tr');
    try {
        columnDefinitions.forEach((colDef, index) => {
            console.log(`Processing column ${index}:`, colDef);
            const cell = document.createElement('td');
            cell.className = colDef.class;
            if (colDef.id) cell.id = colDef.id.replace(':', recordID);
            for (const [key, value] of Object.entries(colDef)) {
                if (key.startsWith('data-')) {
                    cell.setAttribute(key, value);
                }
            }
            const columnName = colDef['data-column'];
            if (columnName && checkInRowData[columnName]) {
                cell.textContent = checkInRowData[columnName];
            } else if (colDef.content) {
                cell.innerHTML = colDef.content.replace('{recordID}', recordID);
            }
            newRow.appendChild(cell);
            console.log(`Cell added for column: ${colDef['data-column']}`);
        });
        console.log('About to append new row to netBody');
        netBody.appendChild(newRow);
        console.log('New row successfully added to the UI table');
        console.log('New row count:', netBody.children.length);
        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return newRow;
    } catch (error) {
        console.error('Error in addNewCallsignRow:', error);
        return null;
    }
}

function applyRowStyling(row, data) {
    if (!row || !data) {
        console.error('Invalid row or data provided to applyRowStyling');
        return;
    }

    columnDefinitions.forEach(function(column, index) {
        var cell = row.cells[index];
        if (!cell) {
            console.warn('Cell not found for index:', index);
            return;
        }

        // Helper function to safely replace placeholders
        function safeReplace(str, placeholder, value) {
            return str.replace(new RegExp(placeholder, 'g'), value || '');
        }

        if (column.oncontextmenu) {
            cell.setAttribute('oncontextmenu', 
                safeReplace(
                    safeReplace(
                        safeReplace(column.oncontextmenu, '{recordID}', data.recordID),
                        '{callsign}', data.callsign
                    ),
                    '{netID}', data.netID
                ) + ';return false;'
            );
        }

        if (column.ondblclick) {
            cell.setAttribute('ondblclick', 
                safeReplace(
                    safeReplace(
                        safeReplace(column.ondblclick, '{recordID}', data.recordID),
                        '{callsign}', data.callsign
                    ),
                    '{netID}', data.netID
                ) + ';return false;'
            );
        }

        if (column.onclick) {
            cell.setAttribute('onclick', safeReplace(column.onclick, '{recordID}', data.recordID));
        }

        if (column.title) {
            cell.setAttribute('title', safeReplace(column.title, '{callsign}', data.callsign));
        }
    });

    console.log('Row styling applied for:', data.callsign);
}

if (typeof window.handleCheckIn !== 'function') {
    console.error('handleCheckIn function not properly defined');
}