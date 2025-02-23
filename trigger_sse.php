<?php
// trigger_sse.php
// V2 Updated: 2024-06-29

require_once 'sse_handler.php';

$netID = $_POST['netID'] ?? '';
$additionalInfo = $_POST['additionalInfo'] ?? '';

if ($netID && $additionalInfo) {
    sendSSEMessage($netID, $additionalInfo);
    echo "SSE message sent";
} else {
    http_response_code(400);
    echo "Invalid parameters";
}