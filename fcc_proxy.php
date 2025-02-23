<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set up logging for proxy
$proxyLogFile = '/var/www/NCM/logs/proxy_log.txt';

function logProxyMessage($message) {
    global $proxyLogFile;
    error_log(date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, 3, $proxyLogFile);
}

// Get parameters from request
$latitude = $_GET['latitude'] ?? null;
$longitude = $_GET['longitude'] ?? null;

if (!$latitude || !$longitude) {
    logProxyMessage("Error: Missing latitude or longitude parameters");
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

logProxyMessage("Received request for lat: $latitude, lon: $longitude");

// Construct FCC API URL
$fccApiUrl = "https://geo.fcc.gov/api/census/block/find";
$params = [
    'format' => 'json',
    'latitude' => $latitude,
    'longitude' => $longitude,
    'showall' => 'true'
];

$queryString = http_build_query($params);
$apiUrlWithParams = $fccApiUrl . '?' . $queryString;

logProxyMessage("Calling FCC API: $apiUrlWithParams");

// Initialize cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrlWithParams,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (compatible; ProxyServer/1.0)'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

logProxyMessage("FCC API HTTP Status Code: $httpCode");

if ($response === false) {
    $error = curl_error($ch);
    logProxyMessage("cURL Error: $error");
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get data from FCC']);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Check if response is valid JSON
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    logProxyMessage("JSON Decode Error: " . json_last_error_msg());
    logProxyMessage("Raw Response: " . $response);
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from FCC']);
    exit;
}

// If successful, return the data
logProxyMessage("Successfully retrieved and returning data");
header('Content-Type: application/json');
echo $response;
?>