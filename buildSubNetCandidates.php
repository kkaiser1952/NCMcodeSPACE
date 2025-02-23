<?php
// buildSubNetCandidates.php
// Written: 2017
// Updated: 2023-09-26
// Updated: 2024-06-03
// Modified: 2024-06-04 to return content instead of echoing

function buildSubNetCandidates($db_found) {
    $output = '';

    // Check if the database connection is valid
    if ($db_found) {
        // Prepare the SQL statement with placeholders
        $sql = "SELECT netID, activity, netcall
                FROM NetLog 
                WHERE (dttm >= NOW() - INTERVAL 3 DAY AND pb = 1)
                OR (logdate >= NOW() - INTERVAL 3 DAY AND pb = 0)
                GROUP BY netID 
                ORDER BY netID DESC";

        try {
            $stmt = $db_found->prepare($sql);
            // Execute the prepared statement
            $stmt->execute();
            // Fetch the results in a loop
            while ($act = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $netID = htmlspecialchars($act['netID']);
                $activity = htmlspecialchars($act['activity']);
                $netcall = htmlspecialchars($act['netcall']);
                
                $output .= "<option title='$netID' value='$netID'>Net #: $netID --> $activity</option>\n";
            } // End while
        } catch (PDOException $e) {
            $output = "Error: " . $e->getMessage();
        }
    } else {
        $output = "Error: Database connection is not established.";
    }

    return $output;
}

// If this file is called directly, return the output
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    if (!isset($db_found)) {
        require_once "dbConnectDtls.php";
    }
    echo buildSubNetCandidates($db_found);
}
?>