<!doctype html>
<?php

ini_set('display_errors', 1); 
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";

// http://php.net/json_decode decodes JSON strings.
function json_clean_decode($json, $assoc = false, $depth = 512, $options = 0) {
    // Search and remove comments like /* */ and //
    $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);
    
    if (version_compare(phpversion(), '5.4.0', '>=')) {
        $json = json_decode($json, $assoc, $depth, $options);
    } elseif (version_compare(phpversion(), '5.3.0', '>=')) {
        $json = json_decode($json, $assoc, $depth);
    } else {
        $json = json_decode($json, $assoc);
    }

    return $json;
}

// Clean up the incoming data
$q = substr($_POST["q"], 0, 5000);

$str = explode("|", $q);

// Use PDO to handle database interactions
$eventDisc = $str[0];
$callsign = strtoupper($str[1]);
$contact = ucwords($str[2]);
$email = filter_var($str[3], FILTER_SANITIZE_EMAIL);
$eventTitle = htmlspecialchars($str[4]);
$eventURL = filter_var($str[5], FILTER_SANITIZE_URL);
$eventLocation = $str[6];
$startDate = $str[7];
$endDate = $str[8];
$domain = $str[9];
$docType = $str[10];
$netkind = $str[11];
$eventDate = $str[12];

if (strtotime($startDate) === false) {
    $fixStart = date("Y-m-d H:i:s", strtotime('today'));
} else {
    $fixStart = date("Y-m-d H:i:s", strtotime($startDate));
}

if (strtotime($endDate) === false) {
    $fixEnd = date("Y-m-d H:i:s", strtotime('+5 years'));
} else {
    $fixEnd = date("Y-m-d H:i:s", strtotime($endDate));
}

try {
    // Prepare SQL statements using PDO
    if (!$oldID) { // creates a new row
        $sql = "INSERT INTO events (callsign, title, description, location, contact, email, url, start, end, domain, doctype, netkind, eventDate) 
                VALUES (:callsign, :title, :description, :location, :contact, :email, :url, :start, :end, :domain, :doctype, :netkind, :eventDate)";
    } else { // updates an existing row
        $sql = "UPDATE events SET callsign = :callsign, title = :title, description = :description, 
                location = :location, contact = :contact, email = :email, url = :url, 
                start = :start, end = :end, domain = :domain, doctype = :doctype, netkind = :netkind, eventDate = :eventDate
                WHERE id = :id";
    }

    $stmt = $db_found->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':callsign', $callsign);
    $stmt->bindParam(':title', $eventTitle);
    $stmt->bindParam(':description', $eventDisc);
    $stmt->bindParam(':location', $eventLocation);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':url', $eventURL);
    $stmt->bindParam(':start', $fixStart);
    $stmt->bindParam(':end', $fixEnd);
    $stmt->bindParam(':domain', $domain);
    $stmt->bindParam(':doctype', $docType);
    $stmt->bindParam(':netkind', $netkind);
    $stmt->bindParam(':eventDate', $eventDate);
    if ($oldID) {
        $stmt->bindParam(':id', $oldID);
    }

    $stmt->execute();

    echo("<div id=\"subby\">Submitted by: $contact, $callsign</div>");
    echo("<div>Date(s): $fixStart to $fixEnd");
    echo("<div>Location: $eventLocation</div>");
    echo("<br>");
    echo("<div id=\"subj\">Subject: $eventTitle</div>");
    echo("<div id=\"whatitis\">$eventDisc</div>");
    echo("<br>");
    echo("<div id=\"qs\">Questions: $email");
    echo("<br><br>");
} catch (PDOException $e) {
    error_log("Database operation failed: " . $e->getMessage());
    echo "An error occurred while processing your request. Please try again later.";
}
?>
