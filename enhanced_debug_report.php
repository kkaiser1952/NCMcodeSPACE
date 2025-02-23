<?php
// enhanced_debug_report.php
// Written: 2024-09-03

// Include the database logger
require_once 'database_logger.php';

if (isset($_GET['clear_log'])) {
    $error_log_path = ini_get('error_log');
    if (file_exists($error_log_path) && is_writable($error_log_path)) {
        file_put_contents($error_log_path, '');
        error_log("Log cleared at " . date('Y-m-d H:i:s'));
    }
}

function generate_enhanced_debug_report() {
    $report = [];
    $report[] = "=== NCM Enhanced Debug Report ===\n";
    $report[] = "Generated: " . date('Y-m-d H:i:s') . "\n\n";

    // List all included files
    $report[] = "=== Included Files ===\n";
    $included_files = get_included_files();
    foreach ($included_files as $file) {
        $report[] = basename($file) . "\n";
    }

    // 2. Environment information
    $report[] = "\n=== Environment Info ===\n";
    $report[] = "PHP Version: " . phpversion() . "\n";
    $report[] = "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
    $report[] = "Database Type: " . (defined('DB_TYPE') ? DB_TYPE : 'Unknown') . "\n";
    $report[] = "Operating System: " . php_uname() . "\n";

    // 3. Recent error log entries (last 10 lines)
    $report[] = "\n=== Recent Error Log Entries ===\n";
    $error_log_path = ini_get('error_log');
    if (file_exists($error_log_path) && is_readable($error_log_path)) {
        $error_log_content = file($error_log_path);
        $last_10_lines = array_slice($error_log_content, -10);
        $report = array_merge($report, $last_10_lines);
    } else {
        $report[] = "Error log not accessible.\n";
    }

    // 4. Application-specific information
    $report[] = "\n=== Application Info ===\n";
    $report[] = "Current Route: " . $_SERVER['REQUEST_URI'] . "\n";
    $report[] = "User Role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set') . "\n";
    $report[] = "Debug Mode: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'On' : 'Off') : 'Not set') . "\n";

    // 5. Recent database queries (if available)
    $report[] = "\n=== Recent Database Queries ===\n";
    $recent_queries = get_recent_queries(5);
    if (!empty($recent_queries)) {
        foreach ($recent_queries as $query) {
            $report[] = $query . "\n";
        }
    } else {
        $report[] = "No recent queries logged.\n";
    }

    // 6. JavaScript console output (collected via AJAX)
    $report[] = "\n=== JavaScript Console Output ===\n";
    $report[] = "<!-- JS_CONSOLE_OUTPUT -->\n"; // Placeholder to be replaced by JavaScript
    
    // 7. Add Error Log section
    $report[] = "\n=== Error Log (Last 20 lines) ===\n";
    $error_log_path = ini_get('error_log');
    if (file_exists($error_log_path) && is_readable($error_log_path)) {
        $error_log_content = file($error_log_path);
        $last_20_lines = array_slice($error_log_content, -20);
        $report = array_merge($report, $last_20_lines);
    } else {
        $report[] = "Error log not accessible.\n";
    }

    // 8. Recent database queries (if available)
    $report[] = "\n=== Recent Database Queries ===\n";
    if (function_exists('get_recent_queries')) {
        $recent_queries = get_recent_queries(10);  // Increased to 10 for more comprehensive view
        foreach ($recent_queries as $query) {
            $report[] = $query . "\n";
        }
    } else {
        $report[] = "Recent query logging not available.\n";
    }

    return implode('', $report);
}

/// Generate and output the report
$debug_report = generate_enhanced_debug_report();
echo "<pre>" . htmlspecialchars($debug_report) . "</pre>";

// Check if this is an FETCH request to update with console logs
if (isset($_POST['console_logs'])) {
    $console_logs = $_POST['console_logs'];
    $debug_report = str_replace("<!-- JS_CONSOLE_OUTPUT -->\n", $console_logs, $debug_report);
    
    // Offer download option
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=ncm_debug_report.txt");
    echo $debug_report;
    exit;
}

// Output HTML with JavaScript to capture console.log
?>
<!DOCTYPE html>
<html>
<head>
    <title>NCM Debug Report</title>
    <script>
    (function(){
        var originalLog = console.log;
        var logs = [];
        console.log = function() {
            logs.push(Array.from(arguments).join(' '));
            originalLog.apply(console, arguments);
        }
        
        window.onload = function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.body.innerHTML = '<pre>' + xhr.responseText + '</pre>';
                }
            }
            xhr.send('console_logs=' + encodeURIComponent(logs.join("\n")));
        }
    })();
    </script>
</head>
<body>
    <p>Generating debug report...</p>
</body>
</html>
<?php
exit;
?>