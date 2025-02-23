<?php
// sse_handler.php
// V2 Updated: 2024-06-29
// Phase 1

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');  // Or specify your domain instead of *
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

function sendSSEMessage($netID, $additionalInfo) {
    $data = json_encode([
        'type' => 'newCheckIn',
        'netID' => $netID,
        'additionalInfo' => $additionalInfo
    ]);
    echo "data: $data\n\n";
    ob_flush();
    flush();
}

// Keep the connection open
while (true) {
    // Check for new check-ins here (you'll need to implement this logic)
    // For now, just send a keep-alive comment every 30 seconds
    echo ": keepalive\n\n";
    ob_flush();
    flush();
    sleep(30);
}