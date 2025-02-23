<?php
// rowDefinitions.php
// Written: 2019-04-04
// Update: 2024-04-28
// V2 Updated: 2024-07-02

if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!defined('INCLUDED_FROM_GENERATE_JS')) {
    error_log("Direct access to rowDefinitions.php");
}

if (!isset($row)) {
    $row = []; // Provide an empty array as a fallback
}

if (!function_exists('safeGet')) {
    function safeGet($array, $key, $default = '') {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

error_log("rowDefinitions.php is being executed");
error_log("--- Start of rowDefinitions.php execution ---");
error_log('$row variable: ' . print_r($row, true));

$cells = [
    [
        'class' => 'cent c0',
        'content' => '',
    ],
    [
        'class' => 'editable editable_selectNC cent c1',
        'id' => "netcontrol:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'netcontrol',
        'data-role' => safeGet($row, 'netcontrol', ''),
        'content' => h(safeGet($row, 'netcontrol')),
    ],
    [
        'class' => "editable editable_selectMode cent c2",
        'id' => "mode:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'mode',
        'data-mode' => safeGet($row, 'Mode', ''),
        'content' => h(safeGet($row, 'Mode')),
    ],
    [
        'class' => "editable editable_selectACT cent c3",
        'id' => "active:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'active',
        'data-status' => safeGet($row, 'active', ''),
        'oncontextmenu' => "rightClickACT('" . safeGet($row, 'recordID') . "');return false;",
        'content' => h(safeGet($row, 'active')),
    ],
    [
        'class' => "editable editable_selectTFC c4",
        'id' => "traffic:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'traffic',
        'data-traffic' => safeGet($row, 'traffic'),
        'oncontextmenu' => "rightClickTraffic('" . safeGet($row, 'recordID') . "');return false;",
        'content' => h(safeGet($row, 'traffic')),
    ],
    [
        'class' => 'editable editTT cent c5 TT',
        'id' => "tt:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'tt',
        'title' => "TT No. " . safeGet($row, 'tt') . " no edit",
        'content' => h(safeGet($row, 'tt')),
    ],
    [
        'class' => 'editable editBand c23',
        'id' => "band:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'band',
        'title' => safeGet($row, 'band') . " Band",
        'content' => h(safeGet($row, 'band')),   
    ],
    [
        'class' => 'editable editfacility c33',
        'id' => "facility:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'facility',
        'onclick' => "empty('facility:" . safeGet($row, 'recordID') . "');",
        'content' => h(safeGet($row, 'facility')),
    ],
    [
        'class' => 'editable editonSite c34',
        'id' => "onsite:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'onsite',
        'data-onsite' => safeGet($row, 'onsite', ''),
        'oncontextmenu' => "rightClickOnSite('" . safeGet($row, 'recordID') . "');return false;",
        'content' => h(safeGet($row, 'onSite', '')),
    ],
    [
        'class' => "editable cs1 " . safeGet($row, 'editCS1', '') . " c6",
        'id' => "callsign:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'callsign',
        'oncontextmenu' => "getCallHistory('" . h(safeGet($row, 'callsign')) . "');return false;",
        'ondblclick' => "doubleClickCall('" . safeGet($row, 'recordID') . "', '" . h(safeGet($row, 'callsign')) . "', '" . safeGet($row, 'netID') . "');return false;",
        'title' => "Call Sign " . h(safeGet($row, 'callsign')) . " no edit",
        'content' => h(safeGet($row, 'callsign')),
    ],
 /*   [
        'class' => 'editable editCat cent c50',
        'id' => "cat:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'cat',
        'style' => 'text-transform: uppercase; color:green;',
        'onclick' => "empty('cat:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'cat')) . '</div>',
    ], */
    [
        'class' => 'editable editFnm c7',
        'id' => "Fname:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'Fname',
        'style' => 'text-transform:capitalize',
        'onclick' => "empty('Fname:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'Fname')) . '</div>',
    ],
    [
        'class' => 'editable editLnm c8',
        'id' => "Lname:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'Lname',
        'style' => 'text-transform:capitalize',
        'onclick' => "empty('Lname:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'Lname')) . '</div>',
    ],
    [
        'class' => 'editable editTAC cent c9',
        'id' => "tactical:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'tactical',
        'onclick' => "empty('tactical:" . safeGet($row, 'recordID') . "');",
        'numsort' => safeGet($row, 'numsort', ''),
        'content' => "<div>" . h(safeGet($row, 'tactical')) . '</div>',
    ],
    [
        'class' => 'editable editPhone c10 cent',
        'id' => "phone:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'phone',
        'content' => h(safeGet($row, 'phone')),
    ],
    [
        'class' => 'editable editEMAIL c11',
        'id' => "email:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'email',
        'oncontextmenu' => "sendEMAIL('" . h(safeGet($row, 'email')) . "','" . safeGet($row, 'netID') . "');return false;",
        'content' => h(safeGet($row, 'email')),
    ],
    [
        'class' => 'editable editGRID c20',
        'id' => "grid:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'grid',
        'style' => 'text-transform:uppercase',
        'oncontextmenu' => "MapGridsquare('" . h(safeGet($row, 'latitude')) . ":" . h(safeGet($row, 'longitude')) . ":" . h(safeGet($row, 'callsign')) . "');return false;",
        'onclick' => "empty('grid:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'grid')) . '</div>',
    ],
    [
        'class' => 'editable editLAT c21',
        'id' => "latitude:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'latitude',
        'oncontextmenu' => "getCrossRoads('" . h(safeGet($row, 'latitude')) . "," . h(safeGet($row, 'longitude')) . "');return false;",
        'onclick' => "empty('latitude:" . safeGet($row, 'recordID') . "');",
        'content' => h(safeGet($row, 'latitude')),
    ],
    [
        'class' => 'editable editLON c22',
        'id' => "longitude:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'longitude',
        'oncontextmenu' => "getCrossRoads('" . h(safeGet($row, 'latitude')) . "," . h(safeGet($row, 'longitude')) . "');return false;",
        'onclick' => "empty('longitude:" . safeGet($row, 'recordID') . "');",
        'content' => h(safeGet($row, 'longitude')),
    ],
    [
        'class' => 'editable editTimeIn cent c12',
        'id' => "logdate:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'logdate',
        'content' => "<span class='tzld'>" . h(safeGet($row, 'logdate')) . "</span><span class='tzlld hidden'>" . h(safeGet($row, 'locallogdate', '')) . "</span>",
    ],
    [
        'class' => 'editable editTimeOut cent c13',
        'id' => "timeout:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'timeout',
        'content' => "<span class='tzto'>" . h(safeGet($row, 'timeout')) . "</span><span class='tzlto hidden'>" . h(safeGet($row, 'localtimeout', '')) . "</span>",
    ],
    [
        'class' => 'editable editC c14',
        'id' => "comments:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'comments',
        'oncontextmenu' => "stationTimeLineList('" . h(safeGet($row, 'callsign')) . ":" . safeGet($row, 'netID') . "');return false;",
        'onclick' => "empty('comments:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'comments')) . '</div>',
    ],
    [
        'class' => 'editable editCREDS c15',
        'id' => "creds:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'creds',
        'content' => h(safeGet($row, 'creds')),
    ],
    [
        'class' => 'editable c16 cent',
        'id' => "timeonduty:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'timeonduty',
        'content' => h(safeGet($row, 'time')),
    ],
    [
        'class' => 'editable editcnty c17 cent',
        'id' => "county:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'county',
        'style' => 'text-transform:capitalize',
        'oncontextmenu' => "MapCounty('" . h(safeGet($row, 'county')) . ":" . h(safeGet($row, 'state')) . "');return false;",
        'onclick' => "empty('county:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'county')) . '</div>',
    ],
    [
        'class' => 'editable editcity c35 cent',
        'id' => "city:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'city',
        'onclick' => "empty('city:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'city')) . '</div>',
    ],
    [
        'class' => 'editable editstate c18 cent',
        'id' => "state:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'city',
        'onclick' => "empty('state:" . safeGet($row, 'recordID') . "');",
        'content' => "<div>" . h(safeGet($row, 'state')) . '</div>',
    ],
    [
        'class' => 'editable editdist c59 cent',
        'id' => "district:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'district',
        'style' => 'text-transform:uppercase;',
        'sorttable_customkey' => safeGet($row, 'district') . " " . safeGet($row, 'county') . " " . safeGet($row, 'state'),
        'oncontextmenu' => "rightClickDistrict('" . safeGet($row, 'recordID') . ", " . safeGet($row, 'state') . ", " . safeGet($row, 'county') . "');return false;",
        'onclick' => "empty('district:" . safeGet($row, 'recordID') . "');",
        'content' => "<div class='" . safeGet($row, 'class', '') . "'>" . h(safeGet($row, 'district')) . "</div>",
    ],
    [
        'class' => 'readonly W3W c24 cent',
        'id' => "w3w:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'w3w',
        'oncontextmenu' => "mapWhat3Words('" . safeGet($row, 'w3w') . "');return false;",
        'onclick' => "empty('w3w:" . safeGet($row, 'recordID') . "'); getAPRSLocations('NOTUSED, " . safeGet($row, 'recordID') . ", " . safeGet($row, 'latitude') . "," . safeGet($row, 'longitude') . "," . safeGet($row, 'callsign') . "," . safeGet($row, 'netID') . ", W3W');",
        'style' => 'cursor: pointer;',
        'content' => "<div class='" . safeGet($row, 'class', '') . "' readonly>" . h(safeGet($row, 'w3w')) . "</div>",
    ],
    [
        'class' => 'editable editteam c30 cent',
        'id' => "team:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'team',
        'onclick' => "empty('team:" . safeGet($row, 'recordID') . "');",
        'content' => "<div class='" . safeGet($row, 'class', '') . "'>" . h(safeGet($row, 'team')) . "</div>",
    ],
    [
        'class' => 'editable editaprs_call c31 cent',
        'id' => "aprs_call:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'aprs_call',
        'style' => 'text-transform:uppercase',
        'oncontextmenu' => "getAPRSLocations('" . safeGet($row, 'aprs_call') . ", " . safeGet($row, 'recordID') . ", " . safeGet($row, 'latitude') . "," . safeGet($row, 'longitude') . "," . safeGet($row, 'callsign') . "," . safeGet($row, 'netID') . ", APRS');return false;",
        'onclick' => "empty('aprs_call:" . safeGet($row, 'recordID') . "');",
        'content' => "<div class='" . safeGet($row, 'class', '') . "'>" . h(safeGet($row, 'aprs_call')) . "</div>",
    ],
    [
        'class' => 'editable editcntry c32 cent',
        'id' => "country:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'country',
        'style' => 'text-transform:capitalize',
        'onclick' => "empty('country:" . safeGet($row, 'recordID') . "');",
        'content' => "<div class='" . safeGet($row, 'class', '') . "'>" . h(safeGet($row, 'country')) . "</div>",
    ],
    [
        'class' => 'editable c25 cent',
        'id' => "recordID:" . safeGet($row, 'recordID'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'recordID',
        'content' => h(safeGet($row, 'recordID')),
    ],
    [
        'class' => 'editable c26 cent',
        'id' => "id:" . safeGet($row, 'id'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'id',
        'content' => h(safeGet($row, 'id')),
    ],
    [
        'class' => 'editable c27 cent',
        'id' => "status:" . safeGet($row, 'status'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'status',
        'content' => "'" . h(safeGet($row, 'status')) . "'",
    ],
    [
        'class' => 'editable c28 cent',
        'id' => "home:" . safeGet($row, 'home'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'home',
        'content' => "'" . h(safeGet($row, 'home')) . "'",
    ],
    [
        'class' => 'editable c29 cent',
        'id' => "ipaddress:" . safeGet($row, 'ipaddress'),
        'data-record-id' => safeGet($row, 'recordID'),
        'data-column' => 'ipaddress',
        'content' => h(safeGet($row, 'ipaddress')),
    ],
]; 

ob_start(); // Start output buffering

foreach ($cells as $cell) {
    echo '<td';
    foreach ($cell as $attribute => $value) {
        if ($attribute !== 'content') {
            echo ' ' . $attribute . '="' . h($value) . '"';
        }
    }
    echo '>' . $cell['content'] . '</td>';
}

$output = ob_get_clean(); // Capture and clear the output buffer

error_log('$cells variable before processing: ' . print_r($cells, true));
error_log('HTML output generated: ' . $output);

// If this file is included by generateJsWithDefinitions.php, we don't want to output the HTML directly
if (!defined('INCLUDED_FROM_GENERATE_JS')) {
    echo $output; // Output the HTML only if not included by generateJsWithDefinitions.php
}

error_log('$cells variable after processing: ' . print_r($cells, true));
error_log("--- Ending of rowDefinitions.php execution ---");

// Convert $cells to $cellDefinitions format
$cellDefinitions = array_map(function($cell) {
    return [
        $cell['id'] ?? '',
        $cell['content'] ?? '',
        $cell['class'] ?? '',
        'text',  // Assuming all are text, adjust if needed
        isset($cell['data-column']) ? '1' : '0',  // Editable if data-column is set
        '1',  // Assuming all are sortable, adjust if needed
        '0',  // Assuming none are hidden, adjust if needed
        '0'   // Assuming none are graphable, adjust if needed
    ];
}, $cells);

// Generate columnMapping
// At the end of rowDefinitions.php

if (isset($cellDefinitions) && is_array($cellDefinitions)) {
    $columnMapping = [];
    $headerOrder = [];
    foreach ($cellDefinitions as $index => $item) {
        $cNumber = preg_match('/\bc(\d+)\b/', $item[2], $matches) ? $matches[0] : null;
        if ($cNumber) {
            $columnName = str_replace(':', '', $item[0]);
            $columnMapping[$cNumber] = [
                'name' => $columnName,
                'class' => $item[2],
                'editable' => isset($item[4]) && $item[4] === '1'
            ];
            $headerOrder[] = $cNumber;
        }
    }

    echo "<script>\n";
    echo "window.columnMapping = " . json_encode($columnMapping) . ";\n";
    echo "window.headerOrder = " . json_encode($headerOrder) . ";\n";
    echo "</script>\n";
} else {
    error_log("cellDefinitions is not set or is not an array in rowDefinitions.php");
}