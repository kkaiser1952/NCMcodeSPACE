<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 300);
    
// Update_V2_with_v1_Tables.php
// V2 Updated: 2024-05-19

// This program must run on the .space, v2 server
// To run in a browser use below
// http://net-control.space/Update_V2_with_v1_Tables.php
// php net-control.space/Update_V2_with_v1_Tables.php

// To run in terminal
// First ssh netcontrolcp@146.19.36.22  pw in 1password
// cd www
// php Update_V2_with_v1_Tables.php

echo ("Inside of Update_V2_with_v1_Tables.php\n");

// Include the database connection files
require_once 'dbConnectDtls_Special.php';
// $v1_db is the older server .us
// $v2_db is the newer server .space

// Function to log messages
function logMessage($message) {
    $logFile = 'update_script.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $logEntry;
}

// Function to log errors
function logError($message, $errorCode = null, $sqlState = null) {
    $errorLogFile = 'update_script_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message";
    if ($errorCode !== null) {
        $logEntry .= " | Error Code: $errorCode";
    }
    if ($sqlState !== null) {
        $logEntry .= " | SQL State: $sqlState";
    }
    $logEntry .= "\n";
    file_put_contents($errorLogFile, $logEntry, FILE_APPEND);
    echo $logEntry;
}

// Establish connections to both servers using PDO
try {
    logMessage("Connecting to the v1 source database...");
    $v1_db = new PDO("mysql:host=$sourceHost;dbname=$sourceDb", $sourceUser, $sourcePass);
    logMessage("Connected to the v1 source database.");
    
    logMessage("Connecting to the v2 destination database...");
    $v2_db = new PDO("mysql:host=$destHost;dbname=$destDb", $destUser, $destPass);
    logMessage("Connected to the v2 destination database.");
    
    // Set PDO error mode to exception
    $v1_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $v2_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    logMessage("Successfully connected to the source and destination databases.");
} catch (PDOException $e) {
    logError("Error processing table $table: " . $e->getMessage(), $e->getCode(), $e->errorInfo[0]);
    logError("Query: " . $selectQuery);
    logError("Error details: " . $e->getMessage());
}

// Array of tables to update
//$tables = ['stations', 'TimeLog', 'NetLog', 'NetKind'];
//$tables = ['NetLog', 'TimeLog', 'stations', 'NetKind'];
//$tables = ['NetLog', 'TimeLog'];
$tables = ['NetLog'];


// Iterate over each table
foreach ($tables as $table) {
    try {
        logMessage("Processing table: $table");
        
        // Retrieve records from the source table with netID greater than 11700
        $selectQuery = "SELECT * FROM $table WHERE netID > :netID";
        logMessage("Executing query: $selectQuery");
        $stmt = $v1_db->query($selectQuery);
        $stmt->bindValue(':netID', 11700, PDO::PARAM_INT);
        $stmt->execute();
        
        $rowCount = $stmt->rowCount();
        logMessage("Retrieved $rowCount records from the source table: $table");
        
        $insertedCount = 0;
        $skippedCount = 0;
        
        logMessage("Starting to iterate over retrieved rows");
        
        // Iterate over the retrieved rows
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            logMessage("Processing row: " . json_encode($row));
            
            // Check if the netID exists in the destination table
            $checkNetIdQuery = "SELECT COUNT(*) FROM $table WHERE netID = :netID";
            $checkNetIdStmt = $v2_db->prepare($checkNetIdQuery);
            $checkNetIdStmt->bindValue(':netID', $row['netID'], PDO::PARAM_INT);
            $checkNetIdStmt->execute();
            $netIdExists = $checkNetIdStmt->fetchColumn();
            
            if ($netIdExists) {
                // Extract the column names and values
                $columns = implode(', ', array_map(function($col) { return "`$col`"; }, array_keys($row)));
                $placeholders = implode(', ', array_fill(0, count($row), '?'));
                
                // Insert the record into the destination table using INSERT IGNORE
                $insertQuery = "INSERT IGNORE INTO $table ($columns) VALUES ($placeholders)";
                logMessage("Inserting record: $insertQuery");
                $insertStmt = $v2_db->prepare($insertQuery);
                $insertStmt->execute(array_values($row));
                
                if ($insertStmt->rowCount() > 0) {
                    $insertedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                logMessage("Skipping record with netID " . $row['netID'] . " as it doesn't exist in the destination table.");
                $skippedCount++;
            }
        }
        
        // Get the total record count in the destination table
        $countQuery = "SELECT COUNT(*) FROM $table";
        logMessage("Getting total record count: $countQuery");
        $countStmt = $v2_db->prepare($countQuery);
        $countStmt->execute();
        $totalCount = $countStmt->fetchColumn();
        
        logMessage("Finished processing table: $table");
        logMessage("Inserted records: $insertedCount");
        logMessage("Skipped records: $skippedCount");
        logMessage("Total records in $table: $totalCount");
        
    } catch (PDOException $e) {
        logError("Error processing table $table: " . 
        $e->getMessage(), $e->getCode(), $e->errorInfo[0]);
        logError("Query: " . $selectQuery);
        logError("Error details: " . $e->getMessage());
        continue; // Move to the next table
    }
} // End foreach

// Close the connections
$v1_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$v2_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

logMessage("Script execution completed.");
echo "Script execution completed. Check the log file for details.\n";
?>