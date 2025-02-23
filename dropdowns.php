<?php
// dropdowns.php
// Updated: 2024-04-28
// V2 Updated: 2024-05-10
// The following is used in checkin.php and getactivities.php

//echo "\n<br>@3 top of dropdowns.php";

$os = ["PRM", "2nd", "3rd", "LSN", "Log", "PIO", "EM", "SEC", "RELAY", "CMD", "TL", "======", "Capt"]; // netcontrol

$digimodes = ["Dig", "D*", "Echo", "V&D", "FSQ", "DMR", "Fusion"]; // Mode

$mobilemode = ["Mob", "HT"]; // Mode

$statmodes = ["In-Out", "OUT"]; // active

$netTypes = ['Emergency', 'Priority', 'Welfare', 'Routine', 'Question', 'Announcement', 'Comment', 'Bulletin',
    'Pending', 'Traffic']; // traffic sent and resolved revert to original color so not listed here

$band = ["Rptr1","Rptr2","160m", "80m", "60m", "40m", "30m", "20m", "17m", "15m", "12m", "10m", "6m", "2m", "1.25m", "70cm", "33cm", "23cm", "FRS/GMRS", "CB"];

//echo "\n<br>@20 inside @ bottom of dropdowns.php";
?>