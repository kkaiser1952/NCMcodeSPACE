<?php
// gethintSuspects.php
// V2 Updated: 2024-06-03

require_once "dbConnectDtls.php";

// Sanitize and validate input parameters
$term = isset($_GET['term']) ? trim(htmlspecialchars($_GET['term'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
$netc = isset($_GET['nc']) ? trim(htmlspecialchars($_GET['nc'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';

if (empty($term) || empty($netc)) {
    // Return an empty result if term or netc is missing or empty
    echo json_encode([]);
    exit;
}

// Remove whitespace from the term
$term = str_replace(' ', '', $term);

try {
    // Prepare the SQL statement with placeholders
    $sql = "SELECT a.callsign, CONCAT(a.Fname, ' ', a.Lname, ' --> ', a.state, ' ', a.county, '  ', a.district) as name
            FROM stations a
            JOIN NetLog b ON a.callsign = b.callsign
            WHERE a.active_call = 'y'
            AND b.netcall LIKE :netc
            AND b.logdate <= NOW() - INTERVAL 60 DAY
            AND (a.callsign LIKE :term OR a.Fname LIKE :term OR a.Lname LIKE :term)
            GROUP BY b.callsign";

    // Prepare the statement
    $stmt = $db_found->prepare($sql);

    // Bind the parameters securely
    $stmt->bindValue(':netc', "%$netc%", PDO::PARAM_STR);
    $stmt->bindValue(':term', "%$term%", PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();

    // Fetch the results as an associative array
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build the response array
    $response = [];
    foreach ($results as $row) {
        $response[] = [
            'label' => $row['callsign'],
            'desc' => $row['name'],
            'value' => $row['callsign']
        ];
    }

    // Return the JSON-encoded response
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    // Handle database errors
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing the request']);
}
?>