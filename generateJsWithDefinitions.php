<?php
// generateJsWithDefinitions.php
// This PHP script generates JavaScript code dynamically, creating column definitions based on PHP data and defining a function to add new rows to a table. It combines server-side logic (PHP) to generate client-side functionality (JavaScript), allowing for dynamic table manipulation in the browser based on server-defined structures.
// Written: 2024-09-01
// Updated: 2024-09-24

header('Content-Type: application/javascript');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'www/error.log');

function getColumnDefinitions() {
    $cacheFile = __DIR__ . '/column_definitions_cache.json';
    $cacheLifetime = 86400 * 7; // 1 week, adjust as needed

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
        // Use cached data
        return json_decode(file_get_contents($cacheFile), true);
    } else {
        // Fetch fresh data
        require_once 'CellRowHeaderDefinitions.php';
        if (!function_exists('getRowDefinitions')) {
            throw new Exception('getRowDefinitions function not found');
        }
        $rowDefinitions = getRowDefinitions('raw');
        if (!is_array($rowDefinitions)) {
            throw new Exception('getRowDefinitions() did not return an array');
        }
        $columnDefinitions = array_map(function($cell) {
            return [
                'id' => $cell['class'] ?? '',
                'title' => strip_tags($cell['content'] ?? ''),
                'class' => $cell['class'] ?? '',
                'type' => 'text',
                'editable' => isset($cell['data-column']) ? '1' : '0',
                'sortable' => '1',
                'hidden' => '0',
                'graph' => '0'
            ];
        }, $rowDefinitions);
        
        // Cache the data
        file_put_contents($cacheFile, json_encode($columnDefinitions));
        
        return $columnDefinitions;
    }
}

try {
    $columnDefinitions = getColumnDefinitions();

    echo "console.log('generateJsWithDefinitions.php started');\n";
    echo "window.columnDefinitions = " . json_encode($columnDefinitions) . ";\n";
    echo "console.log('Column definitions set:', window.columnDefinitions);\n";

    $jsFunction = <<<EOT
function addNewCallsignRow(rowData) {
    console.log('addNewCallsignRow called with:', rowData);
    
    const netBody = document.getElementById('netBody');
    if (!netBody) {
        console.error('netBody element not found');
        return null;
    }
    
    if (typeof rowData === 'string') {
        try {
            rowData = JSON.parse(rowData);
        } catch (e) {
            console.error('Failed to parse rowData:', e);
            return null;
        }
    }
    
    const newRow = document.createElement('tr');
    newRow.id = 'row_' + (Math.random().toString(36).substr(2, 9));
    
    if (window.columnDefinitions) {
        window.columnDefinitions.forEach(column => {
            const cell = document.createElement('td');
            cell.className = column.class;
            cell.textContent = rowData[column.id] || '';
            newRow.appendChild(cell);
        });
    } else {
        console.error('columnDefinitions not found or invalid');
        return null;
    }
    
    netBody.appendChild(newRow);
    
    console.log('New row added successfully');
    return newRow;
}
window.addNewCallsignRow = addNewCallsignRow;
console.log('addNewCallsignRow function is now available');
EOT;

    echo $jsFunction;
    echo "console.log('generateJsWithDefinitions.php completed');\n";

} catch (Exception $e) {
    $errorMessage = 'PHP Exception in ' . __FILE__ . ' on line ' . $e->getLine() . ': ' . $e->getMessage();
    echo "console.error(" . json_encode($errorMessage) . ");";
    error_log($errorMessage);
} catch (Throwable $e) {
    $errorMessage = 'PHP Error in ' . __FILE__ . ' on line ' . $e->getLine() . ': ' . $e->getMessage();
    echo "console.error(" . json_encode($errorMessage) . ");";
    error_log($errorMessage);
}
?>