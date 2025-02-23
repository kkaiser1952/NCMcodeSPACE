<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log'); // Specify the relative path to the error_log file

error_log("This is a test message from test_error_log.php");

echo "Check the error_log file to see if the test message was written.";
?>