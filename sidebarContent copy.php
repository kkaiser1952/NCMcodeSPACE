<?php
// sidebarContent.php
// fetch the column definitions based on the group name
// Written: 2024-09-01
// Updated: 2024-09-20

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the file with column definitions
require_once 'CellRowHeaderDefinitions.php';

// Function to get group-specific column settings from the database
function getGroupColumnSettings($groupIdentifier) {
    // TODO: Implement database connection and query
    // This is a placeholder. Replace with actual database query.
    // The query should handle both group name and call, or return default settings
    return []; // Return an empty array for now
}

// Function to merge default settings with group-specific settings
function mergeColumnSettings($defaultColumns, $groupSettings) {
    foreach ($groupSettings as $columnId => $settings) {
        if (isset($defaultColumns[$columnId])) {
            $defaultColumns[$columnId] = array_merge($defaultColumns[$columnId], $settings);
        }
    }
    return $defaultColumns;
}

// Get the group identifier from the request
$groupIdentifier = $_GET['group'] ?? $_GET['call'] ?? 'default';

// Ensure $columns is defined in CellRowHeaderDefinitions.php
if (!isset($columns) || !is_array($columns)) {
    echo json_encode(['error' => 'Column definitions not found']);
    exit;
}

// Get group-specific settings
$groupSettings = getGroupColumnSettings($groupIdentifier);

// Prepare the response
$response = [
    'default' => [],
    'optional' => [],
    'admin' => []
];

// Merge default columns with group settings and categorize
foreach ($columns as $category => $categoryColumns) {
    foreach ($categoryColumns as $columnId => $columnTitle) {
        $columnInfo = [
            'id' => $columnId,
            'title' => $columnTitle,
            'visible' => in_array($category, ['default', 'optional']), // Default visibility
            'order' => $columnId // Default order
        ];

        // Apply group-specific settings if any
        if (isset($groupSettings[$columnId])) {
            $columnInfo = array_merge($columnInfo, $groupSettings[$columnId]);
        }

        $response[$category][] = $columnInfo;
    }
}

// Sort columns within each category based on 'order'
foreach ($response as &$categoryColumns) {
    usort($categoryColumns, function($a, $b) {
        return $a['order'] - $b['order'];
    });
}

// Add group identifier to the response
$response['groupIdentifier'] = $groupIdentifier;

// Send the JSON response
echo json_encode($response);