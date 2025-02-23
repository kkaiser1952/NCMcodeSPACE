<?php
// enhanced_ncm_debug_report.php
// Last Updated: 2024-09-06

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database_logger.php';
require_once 'dbFunctions.php';

class EnhancedNCMDebugReport {
    private $startTime;
    private $data = [];
    private $db_found;

    public function __construct($db_connection) {
        $this->startTime = microtime(true);
        $this->data['Generated'] = date('Y-m-d H:i:s');
        $this->db_found = $db_connection;
    }

    public function generateReport() {
        $this->addBasicInfo();
        $this->addEnvironmentInfo();
        $this->addNCMSpecificInfo();
        $this->addDatabaseInfo();
        $this->addCurrentNetInformation();
        $this->addRecentNCMActivity();
        $this->addServerConfiguration();
        $this->addSSEStatus();
        $this->addErrorLogEntries();
        $this->addDatabaseQueries();
        $this->addPerformanceMetrics();
        $this->addJavaScriptConsoleOutput();
        $this->addIndexPhpAnalysis();
        $this->addNCMSummary();
        $this->addEnhancedErrorLog();
        $this->addNCMStateInfo();
        $this->addJavaScriptErrors();
        return $this->formatReport();
    }

    private function addBasicInfo() {
        $this->data['NCM Version'] = '1.0.0';
        $this->data['PHP Version'] = phpversion();
        $this->data['Server Software'] = $_SERVER['SERVER_SOFTWARE'];
        $this->data['Operating System'] = php_uname();
    }

    private function addEnvironmentInfo() {
        $this->data['Environment Info'] = [
            'Document Root' => $_SERVER['DOCUMENT_ROOT'],
            'Server Name' => $_SERVER['SERVER_NAME'],
            'Request Method' => $_SERVER['REQUEST_METHOD'],
            'Request URI' => $_SERVER['REQUEST_URI'],
            'HTTPS' => isset($_SERVER['HTTPS']) ? 'On' : 'Off',
            'Remote IP' => $_SERVER['REMOTE_ADDR']
        ];
    }

    // ... [More methods will follow in the next part]
    
    private function addNCMSpecificInfo() {
        $currentNetID = 'Not set';
        if (isset($_POST['currentNetID'])) {
            $currentNetID = $_POST['currentNetID'];
        } elseif (isset($_GET['currentNetID'])) {
            $currentNetID = $_GET['currentNetID'];
        } else {
            $html = file_get_contents('php://input');
            if (preg_match('/<input[^>]*id="currentNetID"[^>]*value="([^"]*)"/', $html, $matches)) {
                $currentNetID = $matches[1];
            }
        }
    
        $this->data['NCM Specific Info'] = [
            'Current Net ID' => $currentNetID,
            'Active Modules' => get_loaded_extensions(),
        ];
    
        if ($this->db_found && $currentNetID !== 'Not set') {
            try {
                $stmt = $this->db_found->prepare("SELECT * FROM NetLog WHERE netID = :netID ORDER BY logdate DESC LIMIT 1");
                $stmt->execute(['netID' => $currentNetID]);
                $recentActivity = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($recentActivity) {
                    $this->data['NCM Specific Info']['Most Recent Net Activity'] = $recentActivity;
                } else {
                    $this->data['NCM Specific Info']['Most Recent Net Activity'] = 'No recent activity found for this net';
                }
            } catch (PDOException $e) {
                $this->data['NCM Specific Info']['Most Recent Net Activity'] = 'Error retrieving recent activity: ' . $e->getMessage();
            }
        } 
    }

    private function addDatabaseInfo() {
        $this->data['Database Info'] = [
            'Connection Status' => $this->db_found ? 'Connected' : 'Not Connected',
            'MySQL Version' => $this->getMySQLVersion(),
            'Current Database' => $this->getCurrentDatabase()
        ];
    }

    private function getMySQLVersion() {
        if (!$this->db_found) {
            return 'Database connection not available';
        }

        try {
            $stmt = $this->db_found->query("SELECT VERSION() as version");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['version'] ?? 'Unknown';
        } catch (PDOException $e) {
            return 'Unable to retrieve MySQL version: ' . $e->getMessage();
        }
    }

    private function getCurrentDatabase() {
        if (!$this->db_found) {
            return 'Database connection not available';
        }

        try {
            $stmt = $this->db_found->query("SELECT DATABASE() as db_name");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['db_name'] ?? 'Unknown';
        } catch (PDOException $e) {
            return 'Unable to retrieve current database: ' . $e->getMessage();
        }
    }

    // ... [More methods will follow in the next part]
    
    private function addCurrentNetInformation() {
        $currentNetID = isset($_SESSION['currentNetID']) ? $_SESSION['currentNetID'] : 'Not set';
        
        $netInfo = ['Net ID' => $currentNetID];
        if ($currentNetID !== 'Not set' && $this->db_found) {
            try {
                $stmt = $this->db_found->prepare("SELECT * FROM NetLog WHERE netID = :netID LIMIT 1");
                $stmt->execute(['netID' => $currentNetID]);
                $netDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($netDetails) {
                    $netInfo = array_merge($netInfo, $netDetails);
                }
            } catch (PDOException $e) {
                $netInfo['Error'] = 'Unable to retrieve net details: ' . $e->getMessage();
            }
        }
        
        $this->data['Current Net Information'] = $netInfo;
    }

    private function addServerConfiguration() {
        $this->data['Server Configuration'] = [
            'PHP Version' => phpversion(),
            'MySQL Version' => $this->getMySQLVersion(),
            'Web Server' => $_SERVER['SERVER_SOFTWARE'],
            'Max Execution Time' => ini_get('max_execution_time'),
            'Memory Limit' => ini_get('memory_limit'),
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size' => ini_get('post_max_size')
        ];
    }

    private function addSSEStatus() {
        $this->data['SSE Status'] = 'Implement SSE status checking here';
    }

    private function addErrorLogEntries() {
        $error_log_path = ini_get('error_log');
        if (file_exists($error_log_path) && is_readable($error_log_path)) {
            $error_log_content = file($error_log_path);
            $this->data['Recent Error Log Entries'] = array_slice($error_log_content, -20);
        } else {
            $this->data['Recent Error Log Entries'] = ['Error log not accessible.'];
        }
    }

    private function addDatabaseQueries() {
        if (function_exists('get_recent_queries')) {
            $this->data['Recent Database Queries'] = get_recent_queries(10);
        } else {
            $this->data['Recent Database Queries'] = ['Recent query logging not available.'];
        }
    }

    private function addPerformanceMetrics() {
        $this->data['Performance Metrics'] = [
            'Memory Usage' => memory_get_peak_usage(true),
            'Execution Time' => microtime(true) - $this->startTime,
            'PHP Extensions' => get_loaded_extensions()
        ];
    }

    private function addJavaScriptConsoleOutput() {
        $this->data['JavaScript Console Output'] = '<!-- JS_CONSOLE_OUTPUT -->';
    }

    private function addIndexPhpAnalysis() {
        $indexPhpPath = $_SERVER['DOCUMENT_ROOT'] . '/index.php';
        if (file_exists($indexPhpPath)) {
            $indexContent = file_get_contents($indexPhpPath);
            
            preg_match_all('/<script.*?src=["\'](.+?)["\'].*?><\/script>/i', $indexContent, $matches);
            $jsIncludes = $matches[1];

            preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $indexContent, $inlineMatches);
            $inlineJs = $inlineMatches[1];

            $this->data['Index.php Analysis'] = [
                'JavaScript Includes' => $jsIncludes,
                'Inline JavaScript Count' => count($inlineJs),
                'File Size' => filesize($indexPhpPath) . ' bytes',
                'Last Modified' => date('Y-m-d H:i:s', filemtime($indexPhpPath))
            ];
        } else {
            $this->data['Index.php Analysis'] = 'index.php not found';
        }
    }
    
    private function addRecentNCMActivity() {
        if ($this->db_found) {
            try {
                $recentNets = $this->db_found->query("
                    SELECT netID, netcall, activity, logdate 
                    FROM NetLog 
                    ORDER BY logdate DESC 
                    LIMIT 5
                ")->fetchAll(PDO::FETCH_ASSOC);

                $recentCheckIns = $this->db_found->query("
                    SELECT callsign, netID, logdate 
                    FROM TimeLog 
                    ORDER BY logdate DESC 
                    LIMIT 5
                ")->fetchAll(PDO::FETCH_ASSOC);

                $this->data['Recent NCM Activity'] = [
                    'Recent Nets' => $recentNets,
                    'Recent Check-ins' => $recentCheckIns
                ];
            } catch (PDOException $e) {
                $this->data['Recent NCM Activity'] = 'Error retrieving recent activity: ' . $e->getMessage();
            }
        } else {
            $this->data['Recent NCM Activity'] = 'Database connection not available';
        }
    }

    private function addEnhancedErrorLog() {
        $error_log_path = ini_get('error_log');
        if (file_exists($error_log_path) && is_readable($error_log_path)) {
            $error_log_content = file($error_log_path);
            $filtered_errors = array_filter($error_log_content, function($line) {
                return strpos($line, 'NCM') !== false || strpos($line, 'Fatal error') !== false;
            });
            $this->data['NCM-specific Errors'] = array_slice($filtered_errors, -20);
        } else {
            $this->data['NCM-specific Errors'] = ['Error log not accessible.'];
        }
    }

    private function addNCMStateInfo() {
        if ($this->db_found) {
            try {
                $totalNets = $this->db_found->query("SELECT COUNT(DISTINCT netID) FROM NetLog")->fetchColumn();
                $recentNets = $this->db_found->query("SELECT COUNT(DISTINCT netID) FROM NetLog WHERE logdate >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
                $totalStations = $this->db_found->query("SELECT COUNT(*) FROM stations")->fetchColumn();
                $recentCheckIns = $this->db_found->query("SELECT COUNT(*) FROM TimeLog WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
                
                $this->data['NCM State'] = [
                    'Total Nets' => $totalNets,
                    'Nets Created (Last 24h)' => $recentNets,
                    'Total Stations' => $totalStations,
                    'Check-ins (Last 24h)' => $recentCheckIns
                ];
            } catch (PDOException $e) {
                $this->data['NCM State'] = 'Error retrieving NCM state: ' . $e->getMessage();
            }
        } else {
            $this->data['NCM State'] = 'Database connection not available';
        }
    }

    private function addJavaScriptErrors() {
        if (isset($_POST['console_logs'])) {
            $logs = json_decode($_POST['console_logs'], true);
            $this->data['JavaScript Console Output'] = $logs;
        } else {
            $this->data['JavaScript Console Output'] = 'No JavaScript logs captured.';
        }
    }

    private function addNCMSummary() {
        $this->data['NCM Summary'] = [
            'Description' => 'Net Control Manager (NCM) is a tool designed to manage and coordinate amateur radio communication nets.',
            'Key Features' => [
                'Track check-ins by callsign',
                'Log net activity',
                'Support net control operations',
                'Follow ARRL EMCOMM training procedures',
                'Comply with NIMS and Homeland Security guidelines'
            ],
            'Tech Stack' => [
                'Backend' => 'PHP ' . phpversion(),
                'Database' => 'MySQL ' . $this->getMySQLVersion(),
                'Frontend' => 'JavaScript, jQuery, Bootstrap, Leaflet',
            ],
            'Key Files' => [
                'index.php', 'checkIn.js', 'checkIn.php', 'addNewCallsignRow.js',
                'newNet.js', 'addToNet.js', 'showActivities.js', 'getActivities.js',
                'handleSSE.js', 'sse.php', 'sse-listener.js', 'sendSseMessage.js',
                'sse_handler.php', 'save.php', 'processCheckIn.php'
            ],
            'Recent Updates' => 'Upgrading from MySQL v5.5, PHP v7.0 to MySQL v8.0 & PHP v8.2',
            'Current Challenges' => 'Implementing real-time updates using Server-Side Events (SSE)'
        ];
    }

    private function formatReport() {
        $report = "=== NCM Enhanced Debug Report ===\n\n";
        foreach ($this->data as $section => $content) {
            $report .= "=== $section ===\n";
            if (is_array($content)) {
                foreach ($content as $key => $value) {
                    if (is_array($value)) {
                        $report .= "$key:\n" . print_r($value, true) . "\n";
                    } else {
                        $report .= "$key: $value\n";
                    }
                }
            } else {
                $report .= "$content\n";
            }
            $report .= "\n";
        }
        return $report;
    }
}

// Main execution
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "Error: [$errno] $errstr in $errfile on line $errline\n";
    debug_print_backtrace();
});

try {
    if (!isset($db_found) || !$db_found) {
        throw new Exception("Database connection not established. Check dbFunctions.php");
    }

    $debugReport = new EnhancedNCMDebugReport($db_found);
    $report = $debugReport->generateReport();

    $report .= "\n=== Quick Reference for Next Session ===\n";
    $report .= "1. Check the 'NCM Summary' section for a quick overview of the application.\n";
    $report .= "2. Review 'Index.php Analysis' for any changes in JavaScript includes or structure.\n";
    $report .= "3. Look at 'Current Net Information' and 'Recent Operations' for context on recent activity.\n";
    $report .= "4. Examine 'Error Log Entries' and 'JavaScript Console Output' for any new issues.\n";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['console_logs'])) {
        $console_logs = $_POST['console_logs'];
        $report = str_replace("<!-- JS_CONSOLE_OUTPUT -->\n", $console_logs, $report);
        
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=ncm_debug_report.txt");
        echo $report;
        exit;
    }

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>NCM Debug Report</title>
        <script>
        (function(){
            var originalLog = console.log;
            var originalWarn = console.warn;
            var originalError = console.error;
            var logs = [];
            
            function captureLog(type, args) {
                logs.push(type + ': ' + JSON.stringify(Array.from(args)));
            }
            
            console.log = function() {
                captureLog('LOG', arguments);
                originalLog.apply(console, arguments);
            }
            
            console.warn = function() {
                captureLog('WARN', arguments);
                originalWarn.apply(console, arguments);
            }
            
            console.error = function() {
                captureLog('ERROR', arguments);
                originalError.apply(console, arguments);
            }
            
            window.onerror = function(message, source, lineno, colno, error) {
                logs.push('UNCAUGHT ERROR: ' + message + ' at ' + source + ':' + lineno + ':' + colno);
                if (error && error.stack) {
                    logs.push('Stack trace: ' + error.stack);
                }
                return false;
            };

            window.addEventListener('unhandledrejection', function(event) {
                logs.push('UNHANDLED PROMISE REJECTION: ' + event.reason);
            });
            
            window.onload = function() {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'enhanced_ncm_debug_report.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.body.innerHTML = '<pre>' + xhr.responseText + '</pre>';
                    }
                }
                xhr.send('console_logs=' + encodeURIComponent(JSON.stringify(logs)));
            }
        })();
        </script>
    </head>
    <body>
        <pre><?php echo htmlspecialchars($report); ?></pre>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    echo "An error occurred while generating the debug report: " . $e->getMessage();
    debug_print_backtrace();
}

restore_error_handler();
?>