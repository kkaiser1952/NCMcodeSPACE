<?php
// dbFunctions.php
// split from checkIn.php on 6/8/24
// V2 Updated: 2024-08-06

//error_log("In dbFunctions.php Executing dbFunctions.php");

// Include necessary files and configurations
require_once "dbConnectDtls.php";

if (!$db_found) {
    // Log the error or handle it appropriately
    error_log("In dbFunctions.php Database connection failed");
    exit();
}

//error_log("In dbFunctions.php Database connection successful");

require_once "config.php";
//error_log("In dbFunctions.php Included config.php");

require_once "geocode.php";         /* added 2017-09-03 */
//error_log("In dbFunctions.php Included geocode.php");

require_once "GridSquare.php";      /* added 2017-09-03 */
//error_log("In dbFunctions.php Included GridSquare.php");

//require_once "getFCCrecord.php";  /* added 2019-11-24 */
//require_once "getJSONrecord.php";
require_once "getRealIpAddr.php";
//error_log("In dbFunctions.php Included getRealIpAddr.php");

if (!function_exists('logError')) {
    function logError($message) {
        error_log($message);
        return ['success' => false, 'message' => 'An error occurred. Please try again.'];
    }
}

//error_log("In dbFunctions.php Finished executing dbFunctions.php");
?>