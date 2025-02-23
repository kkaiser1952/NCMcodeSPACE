<?php
// checkIn.php
// This program is called when a station logs into NCM from checkIn.js.
// V3 Updated: 2024-08-29

//error_log("Entering file: " . __FILE__);
ob_start();
//ini_set('display_errors', 0);
//error_reporting(E_ALL);

require_once "dbFunctions.php";
require_once "processCheckIn.php";

// Set the Content-Type header once, outside of any conditional logic
header('Content-Type: application/json');

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . "CheckIn Error: " . $message);
}

try {
    if (!isset($_POST['q'])) {
        throw new Exception("No data received");
    }
    
    $params = extractParams($_POST['q']);
    //error_log("Extracted params: " . json_encode($params));
    
    if (!$db_found) {
        throw new Exception("Database connection failed");
    }
    //error_log("Params passed to processCheckIn: " . json_encode($params));
    $result = processCheckIn($db_found, $params);
    //error_log("Result from processCheckIn: " . json_encode($result));
    
    if (!$result['success']) {
        logError("Check-in failed: " . ($result['message'] ?? 'No specific error message'));
    }
    
    echo json_encode($result);
    exit;
} catch (Exception $e) {
    // Clean the output buffer before sending the error response
    ob_clean();
    
    $errorMessage = $e->getMessage();
    logError($errorMessage);
    
    echo json_encode([
        'success' => false, 
        'message' => $errorMessage
    ]);
} finally {
    ob_end_flush();
}