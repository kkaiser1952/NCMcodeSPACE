<?php
// sidebarContent.php
// fetch the column definitions based on the group name
// Written: 2024-09-01
// Updated: 2024-09-20

<?php
header('Content-Type: application/json');
require_once 'CellRowHeaderDefinitions.php';

// Function to get user-specific column settings from the database
function getUserColumnSettings($userId) {
    // TODO: Implement database query to get user settings
    return []; // Return empty array for now
}

$userId = $_GET['userId'] ?? 'default';
$userSettings = getUserColumnSettings($userId);

$response = [
    'default' => [],
    'optional' => [],
    'admin' => []
];

foreach ($columns as $category => $categoryColumns) {
    foreach ($categoryColumns as $columnId => $columnTitle) {
        $columnInfo = [
            'id' => $columnId,
            'title' => $columnTitle,
            'visible' => in_array($category, ['default', 'optional']),
            'defaultVisible' => in_array($category, ['default', 'optional'])
        ];
        
        // Apply user-specific settings if any
        if (isset($userSettings[$columnId])) {
            $columnInfo['visible'] = $userSettings[$columnId]['visible'];
        }
        
        $response[$category][] = $columnInfo;
    }
}

echo json_encode($response);