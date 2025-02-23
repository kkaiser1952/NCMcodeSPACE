<?php
// sse.php
// V2 Updated: 2024-08-24
// This file is responsible for handling Server-Sent Events (SSE) connections and broadcasting events.

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');  // Or specify your domain instead of *

require_once "dbFunctions.php";

// Function to send SSE message
function sendSSEMessage($event, $data) {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    flush();  // Changed: Removed ob_flush()
}

// Function to check for new data
function checkForNewData($db) {
    static $lastCheckedId = 0;
    $stmt = $db->prepare("SELECT * FROM NetLog WHERE recordID > :lastId ORDER BY recordID ASC LIMIT 1");
    $stmt->bindParam(':lastId', $lastCheckedId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $lastCheckedId = $result['recordID'];
        return $result;
    }
    return false;
}

// Added: Ensure database connection
if (!isset($db_found) || !$db_found) {
    error_log("Database connection failed in sse.php");
    die("Database connection failed");
}

// Main SSE loop
try {
    // Added: Log SSE connection initiation
    //error_log("SSE connection initiated");
    
    // Added: Send initial connection message
    sendSSEMessage('connected', ['message' => 'SSE connection established']);
    
    $lastHeartbeatTime = time();
    
    while (true) {
        // Check for new data
        $newData = checkForNewData($db_found);
        
        if ($newData) {
            sendSSEMessage('newData', $newData);
        }
        
        // Send heartbeat every 30 seconds
        $currentTime = time();
        if ($currentTime - $lastHeartbeatTime >= 30) {
            sendSSEMessage('heartbeat', ['timestamp' => $currentTime]);
            $lastHeartbeatTime = $currentTime;
        }
        
        // Sleep to prevent excessive CPU usage
        sleep(5);
        
        // Check if the connection has been closed
        if (connection_aborted()) {
            error_log("SSE connection aborted");
            break;
        }
    }
} catch (Exception $e) {
    error_log('Caught exception in sse.php: ' . $e->getMessage());
    sendSSEMessage('error', ['message' => 'An error occurred. Please check the server logs.']);
}