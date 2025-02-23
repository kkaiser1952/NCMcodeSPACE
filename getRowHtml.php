<?php
// getRowHtml.php
// Written: 2024-06-25
// V2 Update: 2024-08-13
// This PHP is used to get the row definitions and generate HTML

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "dbConnectDtls.php";
require_once "CellRowHeaderDefinitions.php";

header('Content-Type: text/html');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo "Error: Invalid input data";
    exit;
}

// Create a $row variable that CellRowHeaderDefinitions.php might expect
$row = $input;

ob_start();

// Get the row definitions
$cellDefinitions = getRowDefinitions();

// Generate HTML for each cell
foreach ($cellDefinitions as $cell) {
    echo '<td';
    foreach ($cell as $attribute => $value) {
        if ($attribute !== 'content') {
            echo ' ' . $attribute . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
    }
    echo '>' . $cell['content'] . '</td>';
}

$rowHtml = ob_get_clean();
echo $rowHtml;