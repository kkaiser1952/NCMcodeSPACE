<!-- sidebar.php -->
<!-- responsible for rendering the initial structure of the sidebar -->
<!-- Created: 2024-09-01 -->
<!-- Updated: 2024-09-16 -->
    
<?php
require_once 'CellRowHeaderDefinitions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Column Management</title>
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>

<div id="columnSidebar">
    <span class="close-btn">&times;</span>
    <h3>Column Management</h3>
    <div id="columnOptions"></div>
    <button id="applyChanges">Apply Changes</button>
    <button id="resetToDefault">Reset to Default</button>
</div>

<script>
    // Pass the PHP array to JavaScript
    var columnsData = <?php echo json_encode($columns); ?>;
</script>
<script src="js/sidebar.js"></script>

</body>
</html>