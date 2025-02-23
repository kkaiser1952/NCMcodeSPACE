// sidebar.js
// Written: 2024-09-01
// Updated: 2024-09-28

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');

    function waitForColumnDefinitions(callback, maxAttempts = 10) {
        let attempts = 0;
        const checkInterval = setInterval(function() {
            attempts++;
            if (window.columnDefinitions) {
                clearInterval(checkInterval);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('Column definitions not found after ' + maxAttempts + ' attempts');
            }
        }, 100); // Check every 100ms
    }

    waitForColumnDefinitions(initializeSidebar);

    function initializeSidebar() {
        const columnPickerBtn = document.getElementById('columnPicker');
        const sidebar = document.getElementById('columnSidebar');
        const closeBtn = sidebar.querySelector('.close-btn');
        const applyBtn = document.getElementById('applyChanges');
        const resetBtn = document.getElementById('resetToDefault');

        if (columnPickerBtn) columnPickerBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (applyBtn) applyBtn.addEventListener('click', applyChanges);
        if (resetBtn) resetBtn.addEventListener('click', resetToDefault);

        loadSidebarContent();
    }

    function openSidebar() {
        console.log('Opening sidebar');
        const sidebar = document.getElementById('columnSidebar');
        if (sidebar) {
            sidebar.style.right = '0';
        } else {
        console.error('Sidebar element not found');
        }
    } // End openSidebar()
    
    // Make openSidebar() globally accessible
    window.openSidebar = openSidebar;

    function closeSidebar() {
        console.log('Closing sidebar');
        const sidebar = document.getElementById('columnSidebar');
        if (sidebar) {
            sidebar.style.right = '-300px';
        }
    }

    function loadSidebarContent() {
        console.log('Loading sidebar content');
        const defaultColumns = document.getElementById('defaultColumns');
        const optionalColumns = document.getElementById('optionalColumns');
        const adminColumns = document.getElementById('adminColumns');

        if (!defaultColumns) console.error('defaultColumns container not found');
        if (!optionalColumns) console.error('optionalColumns container not found');
        if (!adminColumns) console.error('adminColumns container not found');
    
        if (!defaultColumns || !optionalColumns || !adminColumns) {
            console.error('One or more column category containers not found');
            return;
        }

        // Clear existing content
        defaultColumns.innerHTML = '<h4>Default Columns</h4>';
        optionalColumns.innerHTML = '<h4>Optional Columns</h4>';
        adminColumns.innerHTML = '<h4>Admin Columns</h4>';

        console.log('Column definitions:', window.columnDefinitions);

        if (!window.columnDefinitions) {
            console.error('Column definitions not found');
            return;
        }

        // Categorize columns
        const defaultIds = ['c0', 'c1', 'c4', 'c6', 'c7', 'c12', 'c13', 'c14'];
        const adminIds = ['c25', 'c26', 'c27', 'c28', 'c29'];

        window.columnDefinitions.forEach(column => {
            console.log(`Processing column: ${column.title}`);
            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = column.id;
            checkbox.checked = column.hidden !== '1';
            
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(' ' + column.title));
            
            // Determine which container to append to
            let container;
            if (adminIds.includes(column.id)) {
                container = adminColumns;
            } else if (defaultIds.includes(column.id)) {
                container = defaultColumns;
            } else {
                container = optionalColumns;
            }
            
            container.appendChild(label);
        });
    }

    function applyChanges() {
        const selectedColumns = Array.from(document.querySelectorAll('#columnOptions input:checked'))
            .map(checkbox => checkbox.value);
        updateColumnVisibility(selectedColumns);
        closeSidebar();
    }

    function resetToDefault() {
        document.querySelectorAll('#columnOptions input').forEach(checkbox => {
            const column = window.columnDefinitions.find(col => col.id === checkbox.value);
            checkbox.checked = column && column.hidden !== '1';
        });
        applyChanges();
    }

    function updateColumnVisibility(visibleColumns) {
        const table = document.getElementById('thisNet');
        if (!table) {
            console.error('Table not found');
            return;
        }

        const headers = table.querySelectorAll('th');
        const rows = table.querySelectorAll('tbody tr');

        window.columnDefinitions.forEach(column => {
            const isVisible = visibleColumns.includes(column.id);
            const headerCell = table.querySelector(`th.${column.class}`);
            if (headerCell) {
                headerCell.style.display = isVisible ? '' : 'none';
            }
            rows.forEach(row => {
                const cell = row.querySelector(`.${column.class}`);
                if (cell) {
                    cell.style.display = isVisible ? '' : 'none';
                }
            });
        });

        // Reinitialize sorting if necessary
        if (typeof sorttable !== 'undefined') {
            sorttable.makeSortable(table);
        }
    }
});