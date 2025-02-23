<?php
// callsignDupeCheck.php
// Written: 2024-08-24
// V2 Updated: 2024-08-24 

error_log("Entering file: " . __FILE__);
    
function callsignDupeCheck($db_found, $netID, $callsign) {
    error_log("Starting callsignDupeCheck for netID: $netID, callsign: $callsign");

    try {
        $stmt = $db_found->prepare("SELECT COUNT(*) FROM NetLog WHERE netID = ? AND callsign = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare duplicate check statement");
        }
        
        $result = $stmt->execute([$netID, $callsign]);
        if ($result === false) {
            throw new Exception("Failed to execute duplicate check statement");
        }
        
        // Debug: Show the query with filled parameters
        ob_start();
        $stmt->debugDumpParams();
        $paramDump = ob_get_clean();
        error_log("Duplicate check query: " . $paramDump);
        
        $dupes = intval($stmt->fetchColumn());
        
        error_log("Duplicate check result: $dupes for netID: $netID, callsign: $callsign");

        // Let's also check what entries actually exist
        $checkStmt = $db_found->prepare("SELECT * FROM NetLog WHERE netID = ? AND callsign = ?");
        $checkStmt->execute([$netID, $callsign]);
        $existingEntries = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Existing entries for netID: $netID, callsign: $callsign: " . json_encode($existingEntries));
        
        $isDupe = $dupes > 0 && $callsign != "NONHAM" && $callsign != "EMCOMM";
        error_log("Is duplicate? " . ($isDupe ? "Yes" : "No"));
        
        return $isDupe;
    } catch (Exception $e) {
        error_log("Error in callsignDupeCheck: " . $e->getMessage());
        throw $e;
    }
} // End callsignDupeCheck()
?>