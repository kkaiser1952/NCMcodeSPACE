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

    <!-- Link to the sidebar.css file -->
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>

<!-- sidebar.php -->
<div id="columnSidebar">
    <span class="close-btn">&times;</span>
    <h3>Column Management</h3>
    <button id="applyChanges">Apply Changes</button>
    <button id="resetToDefault">Reset to Default</button>
    <div id="columnOptions">
        <!-- Content will be populated by JavaScript -->
    </div>
    <button id="applyChanges">Apply Changes</button>
    <button id="resetToDefault">Reset to Default</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const columnData = <?php echo json_encode($columns); ?>;
        const columnOptionsContainer = document.getElementById('columnOptions');
        const columnSidebar = document.getElementById('columnSidebar');
        const applyChangesButton = document.getElementById('applyChanges');
        const resetToDefaultButton = document.getElementById('resetToDefault');
        const closeBtn = document.querySelector('.close-btn');

        // Function to create checkbox list for each category
        // Update the function to create checkbox list for each category
function createCheckboxGroup(category, columns) {
    const groupDiv = document.createElement('div');
    groupDiv.className = 'checkbox-group';

    // Create a label for the group
    const groupTitle = document.createElement('h4');
    groupTitle.textContent = category.charAt(0).toUpperCase() + category.slice(1) + ' Columns';
    groupDiv.appendChild(groupTitle);

    // Create a container for the checkboxes
    const checkboxList = document.createElement('div');
    checkboxList.className = 'checkbox-list'; // Add this line

    // Create checkboxes for each column
    for (const [key, label] of Object.entries(columns)) {
        const checkboxWrapper = document.createElement('div');
        checkboxWrapper.className = 'checkbox-wrapper';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = key;
        checkbox.name = key;
        checkbox.value = label;

        const checkboxLabel = document.createElement('label');
        checkboxLabel.setAttribute('for', key);
        checkboxLabel.textContent = label;

        checkboxWrapper.appendChild(checkbox);
        checkboxWrapper.appendChild(checkboxLabel);
        checkboxList.appendChild(checkboxWrapper); // Append to checkbox list
    }

    groupDiv.appendChild(checkboxList); // Append checkbox list to group
    return groupDiv;
} // End createCheckboxGroup()

        // Iterate over the categories (default, optional, admin)
        for (const [category, columns] of Object.entries(columnData)) {
            const checkboxGroup = createCheckboxGroup(category, columns);
            columnOptionsContainer.appendChild(checkboxGroup);
        }

        // Manually set the sidebar's position to hide it
        function closeSidebar() {
            console.log('Closing sidebar');
            columnSidebar.style.right = '-400px';  // Move the sidebar fully off-screen
            console.log('Sidebar right position:', columnSidebar.style.right);
        }

        // Manually set the sidebar's position to show it
        function showSidebar() {
            columnSidebar.style.right = '0';  // Move the sidebar fully on-screen
        }

        // Placeholder function for applying changes
        function applyChangesAction() {
            console.log('Applying changes...'); // Placeholder for actual functionality
            closeSidebar();
        }

        // Placeholder function for resetting to default
        function resetToDefaultAction() {
            console.log('Resetting to default...'); // Placeholder for actual functionality
            closeSidebar();
        }

        // Event listeners for buttons
        applyChangesButton.addEventListener('click', applyChangesAction);
        resetToDefaultButton.addEventListener('click', resetToDefaultAction);

        // Close button functionality
        closeBtn.addEventListener('click', () => {
            console.log('Close button clicked');
            closeSidebar();
        });
    });
</script>



</body>
</html>