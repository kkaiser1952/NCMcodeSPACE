<?php
//  log-access.php file to handle the logging of JavaScript and HTML files. outputs to the php error_log but $log_file is locked out.

function log_file_access($filename, $type) {
    $log_file = 'access_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "$timestamp - Accessed $type file: $filename\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    error_log("Accessed $type file: $filename"); // This will log to the PHP error log
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['type']) && isset($data['files'])) {
        foreach ($data['files'] as $file) {
            log_file_access($file, $data['type']);
        }
    }
}

// Log HTML file access
$html_file = $_SERVER['PHP_SELF'];
log_file_access($html_file, 'html');
?>