// sse-listener.js
// V3 Updated: 2024-07-28
// No longer needed for the v2 of NCM

console.log('SSE Listener initialized');

function initSSE() {
    console.log('Initializing SSE connection');
    var source = new EventSource('sse_handler.php');
    var reconnectTimeout;

    source.addEventListener('message', function(event) {
        console.log('Received message event:', event.data);
        try {
            const data = JSON.parse(event.data);
            switch(data.type) {
                case 'newCheckIn':
                    console.log('New check-in detected. Refreshing net:', data.netID);
                    if (typeof refresh === 'function') {
                        refresh();
                    } else {
                        console.error('refresh function not found');
                    }
                    break;
                    
                case 'newCallsign':
                    console.log('New callsign detected:', data.content);
                    const callsignData = JSON.parse(data.content);
                    if (typeof window.addNewCallsignRow === 'function') {
                        var newRow = window.addNewCallsignRow(callsignData, callsignData.recordID);
                        if (newRow) {
                            newRow.offsetHeight; // Force a reflow
                            console.log('New row added via SSE:', newRow.outerHTML);
                            if (typeof refreshUI === 'function') {
                                refreshUI();
                            }
                        } else {
                            console.error('Failed to add new row via SSE');
                        }
                    } else {
                        console.error('addNewCallsignRow function not found');
                        // Fallback to refresh if addNewCallsignRow is not available
                        if (typeof refresh === 'function') {
                            refresh();
                        } else {
                            console.error('refresh function not found');
                        }
                    }
                    break;
                default:
                    console.log('Unhandled event type:', data.type);
            }
        } catch (error) {
            console.error('Error parsing SSE message:', error);
        }
    });

    source.addEventListener('error', function(event) {
        console.error('SSE connection error:', event);
        if (event.target.readyState === EventSource.CLOSED) {
            console.log('SSE connection closed');
            // Attempt to reconnect after 5 seconds
            clearTimeout(reconnectTimeout);
            reconnectTimeout = setTimeout(function() {
                console.log('Attempting to reconnect SSE');
                source.close();
                initSSE(); // Reinitialize the SSE connection
            }, 5000);
        }
    });

    return source; // Return the EventSource object
}

// Initialize SSE connection
var sseConnection = initSSE();

// Make initSSE function globally available
window.initSSE = initSSE