<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/javascript');

echo "console.log('generateJsWithDefinitions.php test');";

// Include your rowDefinitions.php file
require_once 'rowDefinitions.php';

// Check if $cells is defined and is an array
if (isset($cells) && is_array($cells)) {
    echo "var columnDefinitions = " . json_encode($cells) . ";";
} else {
    echo "console.error('$cells is not properly defined in rowDefinitions.php');";
}