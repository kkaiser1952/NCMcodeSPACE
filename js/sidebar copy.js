// sidebar.js
// Written: 2024-09-01
// Updated: 2024-09-17

document.addEventListener('DOMContentLoaded', function() {
    const columnPickerBtn = document.getElementById('columnPickern');
    const sidebar = document.getElementById('columnSidebar');
    const closeBtn = sidebar.querySelector('.close-btn');
    const applyBtn = document.getElementById('applyChanges');
    const resetBtn = document.getElementById('resetToDefault');

    columnPickerBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    applyBtn.addEventListener('click', applyChanges);
    resetBtn.addEventListener('click', resetToDefault);

    function openSidebar() {
        sidebar.style.right = '0';
        loadSidebarContent();
    }

    function closeSidebar() {
        sidebar.style.right = '-300px';
    }

function loadSidebarContent() {
    console.log('Loading sidebar content');
    const defaultColumns = document.getElementById('defaultColumns');
    const optionalColumns = document.getElementById('optionalColumns');
    const adminColumns = document.getElementById('adminColumns');

    if (!defaultColumns || !optionalColumns || !adminColumns) {
        console.error('One or more column category containers not found');
        return;
    }

    // Clear existing content
    defaultColumns.innerHTML = '<h4>Default Columns</h4>';
    optionalColumns.innerHTML = '<h4>Optional Columns</h4>';
    adminColumns.innerHTML = '<h4>Admin Columns</h4>';

    // Use the existing columnDefinitions if available
    if (window.columnDefinitions && window.columnDefinitions.allColumns) {
        const categories = {
            'default': defaultColumns,
            'optional': optionalColumns,
            'admin': adminColumns
        };

        Object.entries(categories).forEach(([category, container]) => {
            if (Array.isArray(window.columnDefinitions[category])) {
                window.columnDefinitions[category].forEach(columnId => {
                    const column = window.columnDefinitions.allColumns.find(col => col.id == columnId);
                    if (column) {
                        const label = document.createElement('label');
                        label.innerHTML = `
                            <input type="checkbox" value="${column.id}" ${category === 'default' ? 'checked' : ''}>
                            ${column.title}
                        `;
                        container.appendChild(label);
                    }
                });
            } else {
                console.error(`Column definitions for ${category} is not an array`);
            }
        });
    } else {
        console.error('Column definitions not found');
    }
}

    function getCurrentGroupName() {
        // Implement logic to get the current group name
        // This might come from a global variable, a data attribute, or another source
        return 'default'; // Placeholder
    }
});
/*
function openSidebar() {
     console.log('Opening sidebar');
    const sidebar = document.getElementById('columnSidebar');
    if (sidebar) {
        if (!sidebar.hasAttribute('data-initialized')) {
            initializeSidebar();
            sidebar.setAttribute('data-initialized', 'true');
        }
        sidebar.classList.add('show');
    } else {
        console.error('Sidebar element not found');
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('columnSidebar');
    if (sidebar) {
        sidebar.classList.remove('show');
    }
}

// Make sure these functions are available globally
window.openSidebar = openSidebar;
window.closeSidebar = closeSidebar;

function initializeSidebar() {
    console.log('Initializing sidebar');
    const sidebar = document.getElementById('columnSidebar');
    if (!sidebar) {
        console.error('Sidebar element not found');
        return;
    }

    const columnOptions = sidebar.querySelector('#columnOptions');
    if (!columnOptions) {
        console.error('Column options container not found');
        return;
    }

    // Clear existing content
    columnOptions.innerHTML = '';

    if (!window.columnDefinitions || !Array.isArray(window.columnDefinitions.allColumns)) {
        console.error('Column definitions not found or invalid');
        return;
    }

    const categories = ['default', 'optional', 'admin'];
    categories.forEach(category => {
        const categoryDiv = document.createElement('div');
        categoryDiv.id = `${category}Columns`;
        categoryDiv.className = 'column-category';
        categoryDiv.innerHTML = `<h4>${category.charAt(0).toUpperCase() + category.slice(1)} Columns</h4>`;
        columnOptions.appendChild(categoryDiv);

        if (Array.isArray(window.columnDefinitions[category])) {
            window.columnDefinitions[category].forEach(columnId => {
                const column = window.columnDefinitions.allColumns.find(col => col.id == columnId);
                if (column) {
                    const label = document.createElement('label');
                    label.innerHTML = `
                        <input type="checkbox" value="${column.id}" ${category === 'default' ? 'checked' : ''} onchange="previewColumn(${column.id})">
                        ${column.title}
                    `;
                    categoryDiv.appendChild(label);
                }
            });
        } else {
            console.error(`Column definitions for ${category} is not an array`);
        }
    });

    console.log('Sidebar initialized');
}

function previewColumn(columnId) {
    if (typeof window.toggleCol === 'function') {
        window.toggleCol(columnId);
    } else {
        console.error('toggleCol function not found');
    }
}

function applyChanges() {
    const selectedColumns = Array.from(document.querySelectorAll('#columnOptions input:checked'))
        .map(checkbox => parseInt(checkbox.value))
        .sort((a, b) => a - b);

    updateTableStructure(selectedColumns);
    
    console.log('Selected columns:', selectedColumns);
    closeSidebar();

    // You might want to save this configuration to the server or local storage
    // saveColumnConfiguration(selectedColumns);
}

function updateTableStructure(selectedColumns) {
    const table = document.getElementById('thisNet');
    if (!table) {
        console.error('Table not found');
        return;
    }

    const headerRow = table.querySelector('thead tr');
    const bodyRows = table.querySelectorAll('tbody tr');

    if (!headerRow || bodyRows.length === 0) {
        console.error('Table structure is invalid');
        return;
    }

    // Update headers
    headerRow.innerHTML = '';
    selectedColumns.forEach(columnId => {
        const headerDef = window.columnDefinitions.allColumns.find(def => def.id == columnId);
        if (headerDef) {
            const th = document.createElement('th');
            th.className = headerDef.class;
            th.textContent = headerDef.title;
            headerRow.appendChild(th);
        }
    });

    // Update body rows
    bodyRows.forEach(row => {
        const newRow = document.createElement('tr');
        newRow.id = row.id;
        selectedColumns.forEach(columnId => {
            const cell = row.querySelector(`.c${columnId}`);
            if (cell) {
                newRow.appendChild(cell.cloneNode(true));
            } else {
                const newCell = document.createElement('td');
                newCell.className = `c${columnId}`;
                newRow.appendChild(newCell);
            }
        });
        row.parentNode.replaceChild(newRow, row);
    });
}

function resetToDefault() {
    document.querySelectorAll('#columnOptions input').forEach(checkbox => {
        checkbox.checked = checkbox.closest('.column-category').id === 'defaultColumns';
    });
    
    applyChanges();
}

// We're not auto-initializing anymore, so this line is removed:
// window.addEventListener('load', () => initializeSidebar(window.columnViews || ''));
*/