<?php
error_log("Before log_error.php database connection");
require_once "dbFunctions.php";
error_log("After log_error.php database connection");

if (isset($_POST['error'])) {
    $error = $_POST['error'];
    error_log("JavaScript error: " . $error);
}
?>