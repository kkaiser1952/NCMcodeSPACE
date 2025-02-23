
<?php
// 
echo "intermediate_ncm_debug_report.php started";
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database_logger.php';
require_once 'dbConnectDtls.php';
require_once 'dbFunctions.php';

class IntermediateNCMDebugReport {
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
        $this->addDatabaseInfo();
        $this->addPerformanceMetrics();
        
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

    private function addPerformanceMetrics() {
        $this->data['Performance Metrics'] = [
            'Memory Usage' => memory_get_peak_usage(true),
            'Execution Time' => microtime(true) - $this->startTime
        ];
    }

    private function formatReport() {
        $report = "=== NCM Intermediate Debug Report ===\n\n";
        foreach ($this->data as $section => $content) {
            $report .= "=== $section ===\n";
            if (is_array($content)) {
                foreach ($content as $key => $value) {
                    $report .= "$key: $value\n";
                }
            } else {
                $report .= "$content\n";
            }
            $report .= "\n";
        }
        return $report;
    }
}

private function addNCMSpecificInfo() {
        $this->data['NCM Specific Info'] = [
            'Current Net ID' => isset($_SESSION['currentNetID']) ? $_SESSION['currentNetID'] : 'Not set',
            'Logger Callsign' => isset($_SESSION['loggedInCall']) ? $_SESSION['loggedInCall'] : 'Not set',
            'Active Modules' => get_loaded_extensions()
        ];
    }

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
            'Recent Updates' => 'Upgrading from MySQL v5.5, PHP v7.0 to MySQL v8.0 & PHP v8.2',
            'Current Challenges' => 'Implementing real-time updates using Server-Side Events (SSE)'
        ];
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
                $activeNets = $this->db_found->query("SELECT COUNT(*) FROM NetLog WHERE NetStatus = 'Active'")->fetchColumn();
                $totalStations = $this->db_found->query("SELECT COUNT(*) FROM stations")->fetchColumn();
                $recentCheckIns = $this->db_found->query("SELECT COUNT(*) FROM TimeLog WHERE logdate >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
                
                $this->data['NCM State'] = [
                    'Active Nets' => $activeNets,
                    'Total Stations' => $totalStations,
                    'Check-ins (Last 24h)' => $recentCheckIns
                ];
            } catch (PDOException $e) {
                $this->data['NCM State'] = 'Error retrieving NCM state: ' . $e->getMessage();
            }
        }
    }
}

try {
    if (!isset($db_found) || !$db_found) {
        throw new Exception("Database connection not established. Check dbFunctions.php");
    }
    
    $debugReport = new ExpandedNCMDebugReport($db_found);
    $report = $debugReport->generateReport();
    
    echo "<pre>" . htmlspecialchars($report) . "</pre>";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>