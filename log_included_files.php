<?php
// log_included_files.php
// This is a debugging tool. It's designed to log all the PHP files that were included or required during the execution of the script.

// Commented at the bottom of index.php  
// Written: 2024-09-24

function log_included_files() {
    $executed_files = get_included_files();
    foreach ($executed_files as $file) {
        error_log("Executed file: " . $file);
    }
}

// Call the function immediately when this file is included
log_included_files();
?>