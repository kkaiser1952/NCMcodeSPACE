<?php
// debug_report.php

function generate_debug_report() {
    $report = [];
    $report[] = "=== NCM Debug Report ===\n";
    $report[] = "Generated: " . date('Y-m-d H:i:s') . "\n\n";

    // 1. List accessed files
    $report[] = "=== Accessed Files ===\n";
    $included_files = get_included_files();
    foreach ($included_files as $file) {
        $report[] = basename($file) . "\n";
    }

    // 2. Basic server and PHP information
    $report[] = "\n=== Environment Info ===\n";
    $report[] = "PHP Version: " . phpversion() . "\n";
    $report[] = "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";

    // 3. Recent error log entries (last 20 lines)
    $report[] = "\n=== Recent Error Log Entries ===\n";
    $error_log_path = ini_get('error_log');
    if (file_exists($error_log_path) && is_readable($error_log_path)) {
        $error_log_content = file($error_log_path);
        $last_20_lines = array_slice($error_log_content, -20);
        $report = array_merge($report, $last_20_lines);
    } else {
        $report[] = "Error log not accessible.\n";
    }

    // 4. Session data (be careful with sensitive information)
    $report[] = "\n=== Session Data ===\n";
    foreach ($_SESSION as $key => $value) {
        if (!is_array($value) && !is_object($value)) {
            $report[] = "$key: $value\n";
        }
    }

    return implode('', $report);
}

// Generate and output the report
$debug_report = generate_debug_report();
echo "<pre>" . htmlspecialchars($debug_report) . "</pre>";

// Offer download option
header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=ncm_debug_report.txt");
echo $debug_report;
exit;
?>