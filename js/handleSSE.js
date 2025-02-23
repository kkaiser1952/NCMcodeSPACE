// handleSSE.js
// V2 Updated: 2024-08-16
// Contains functions for processing SSE events and updating the UI accordingly.
// These functions handle the Server-Side Events (SSE) on the client-side.

console.log('SSE Handler initialized');
let evtSource;
let activityTimer;
const INACTIVITY_TIMEOUT = 60 * 60 * 1000; // 1 hour in milliseconds

function initSSE() {
    console.log('Initializing SSE connection');
    evtSource = new EventSource('https://net-control.space/sse.php');
    
    evtSource.onopen = function() {
        console.log('SSE connection opened successfully');
        resetActivityTimer();
    };
    
    // the problem is here ??
    evtSource.onmessage = function(event) {
        console.log('SSE activity detected');
        if (event && event.data) {
            console.log('Raw SSE message received:', event);
            handleSSEEvent(event);
        } else {
            console.log('SSE event triggered but no data received');
        }
    };
    
    evtSource.onerror = function(err) {
        console.error('SSE connection error:', err);
        console.log('SSE readyState:', evtSource.readyState);
        if (err instanceof Event) {
            console.log('Error event type:', err.type);
            console.log('Error event target:', err.target);
        }
        console.log('Attempting to reconnect in 5 seconds');
        setTimeout(initSSE, 5000);
    };
    
    setTimeout(() => {
    console.log('Checking SSE connection status after 5 seconds');
    console.log('SSE readyState:', evtSource.readyState);
    // 0 = CONNECTING, 1 = OPEN, 2 = CLOSED
}, 5000);
    console.log('SSE connection established');
}

function resetActivityTimer() {
    clearTimeout(activityTimer);
    activityTimer = setTimeout(() => {
        console.log('Closing SSE connection due to inactivity');
        evtSource.close();
        checkNetStatus();
    }, INACTIVITY_TIMEOUT);
}

function checkNetStatus() {
    fetch('checkNetStatus.php')
        .then(response => response.json())
        .then(data => {
            if (data.isActive) {
                console.log('Net still active, reconnecting...');
                initSSE();
            } else {
                console.log('Net is closed');
            }
        })
        .catch(error => {
            console.error('Error checking net status:', error);
        });
}

function handleSSEEvent(event) {
    console.log('Received SSE event:', event.type, event.data);
    
    resetActivityTimer();
    
    if (event.type === 'heartbeat') {
        console.log('SSE Heartbeat received:', JSON.parse(event.data).timestamp);
        return;
    }
    
    try {
        const data = JSON.parse(event.data);
        console.log('Parsed SSE data:', data);
        
        const currentNetID = getCurrentNetID();
        console.log('Current Net ID:', currentNetID);

        if (data.type === 'newCallsign' || data.type === 'newData') {
            const netID = data.type === 'newCallsign' ? data.data.netID : data.checkInRowData.netID;
            console.log('Received Net ID:', netID);
            
            if (netID == currentNetID) {
                console.log('New data detected for current net:', data.type === 'newCallsign' ? data.data : data.checkInRowData);
                if (typeof window.addNewCallsignRow === 'function') {
                    let rowData, recordID;
                    if (data.type === 'newCallsign') {
                        rowData = data.data;
                        recordID = data.data.recordID;
                    } else {
                        rowData = data.checkInRowData;
                        recordID = data.checkInRowData.recordID;
                    }
                    console.log('Calling addNewCallsignRow with:', rowData, recordID);
                    var newRow = window.addNewCallsignRow(rowData, recordID);
                    if (newRow) {
                        console.log('New row added via SSE:', newRow.outerHTML);
                        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        newRow.style.backgroundColor = '#ffff99';
                        setTimeout(() => { 
                            newRow.style.backgroundColor = ''; 
                            console.log('Row highlight removed');
                        }, 3000);
                    } else {
                        console.error('Failed to add new row via SSE');
                    }
                } else {
                    console.error('addNewCallsignRow function not found. Available global functions:', Object.keys(window).filter(key => typeof window[key] === 'function'));
                }
            } else {
                console.log('Received data for different net, ignoring');
            }
        } else {
            console.log('Unhandled event type:', data.type);
        }
    } catch (error) {
        console.error('Error parsing SSE message:', error, 'Raw data:', event.data);
    }
}

function getCurrentNetID() {
    var currentNetIDField = document.getElementById('currentNetID');
    if (currentNetIDField && currentNetIDField.value) {
        console.log('Net ID found in hidden field:', currentNetIDField.value);
        return currentNetIDField.value;
    }
    
    console.error('Unable to find current net ID. DOM element:', currentNetIDField);
    return null;
}

// Initialize SSE connection when the page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded. Initializing SSE.');
    initSSE();
});

// Make functions globally accessible
window.initSSE = initSSE;
window.handleSSEEvent = handleSSEEvent;

console.log('handleSSE.js fully loaded');