// column-management.js
// This file handles the drag-and-drop functionality for reordering columns
// Written: 2024-09-01
// Updated: 2024-10-08

function initializeSortable(table) {
    if (!table) {
        console.error('Table not provided to initializeSortable');
        return;
    }

    const headerRow = table.querySelector('thead tr');
    if (!headerRow) {
        console.error('Header row not found in the provided table');
        return;
    }

    if (typeof Sortable === 'undefined') {
        console.error('Sortable library not found. Please include Sortable.js in your HTML.');
        return;
    }

    try {
        new Sortable(headerRow, {
            draggable: 'th',
            onEnd: function(evt) {
                const newOrder = Array.from(evt.to.children).map(th => {
                    const match = th.className.match(/c(\d+)/);
                    return match ? match[1] : null;
                }).filter(Boolean);
                updateColumnOrder(newOrder, table);
            }
        });
        console.log('Sortable initialized successfully');
    } catch (error) {
        console.error('Error initializing Sortable:', error);
        console.error('Header row HTML:', headerRow.outerHTML);
    }
}

function updateColumnOrder(newOrder, table) {
    const tbody = table.querySelector('tbody');
    if (!tbody) {
        console.error('Table body not found');
        return;
    }
    Array.from(tbody.rows).forEach(row => {
        const newRow = document.createElement('tr');
        newOrder.forEach(colNum => {
            const cell = row.querySelector(`.c${colNum}`);
            if (cell) newRow.appendChild(cell.cloneNode(true));
        });
        row.parentNode.replaceChild(newRow, row);
    });
    saveColumnOrder(newOrder);
}

function saveColumnOrder(order) {
    try {
        const groupName = getCurrentGroupName();
        localStorage.setItem(`columnOrder_${groupName}`, JSON.stringify(order));
    } catch (error) {
        console.error('Error saving column order:', error);
    }
}

function getCurrentGroupName() {
    return document.getElementById('domain')?.textContent || 'defaultGroup';
}

function loadAndApplyColumnOrder(table) {
    const groupName = getCurrentGroupName();
    const savedOrder = localStorage.getItem(`columnOrder_${groupName}`);
    if (savedOrder) {
        updateColumnOrder(JSON.parse(savedOrder), table);
    }
}

function loadSavedColumnSelection(table) {
    const savedSelection = localStorage.getItem('selectedColumns');
    if (savedSelection) {
        const selectedColumns = JSON.parse(savedSelection);
        Array.from(table.querySelectorAll('th, td')).forEach(cell => {
            const columnId = Array.from(cell.classList).find(cls => cls.startsWith('c'));
            if (columnId) {
                cell.style.display = selectedColumns.includes(columnId) ? '' : 'none';
            }
        });
    }
}

function applyColumnManagement(tableId = 'thisNet') {
    console.log(`Attempting to apply column management to table: ${tableId}`);
    
    // If tableId is undefined, try to find the table by a known ID
    if (!tableId) {
        console.warn('tableId is undefined, attempting to find table by default ID');
        tableId = 'thisNet';
    }

    const table = document.getElementById(tableId);
    if (!table) {
        console.error(`Table with id ${tableId} not found`);
        // Try to find any table in the document
        const anyTable = document.querySelector('table');
        if (anyTable) {
            console.log('Found a table without the specified ID, attempting to apply column management');
            table = anyTable;
        } else {
            console.error('No tables found in the document');
            return;
        }
    }

    try {
        loadAndApplyColumnOrder(table);
        loadSavedColumnSelection(table);
        initializeSortable(table);
        console.log(`Column management applied to table: ${table.id || 'unnamed table'}`);
    } catch (error) {
        console.error('Error applying column management:', error);
    }
} // End applyColumnManagement()

// Set up MutationObserver to watch for table creation
document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            const addedTable = Array.from(mutation.addedNodes).find(node => node.tagName === 'TABLE');
            if (addedTable) {
                console.log('Table added to DOM. Applying column management.');
                applyColumnManagement(addedTable.id || 'thisNet');
            }
        }
    });
});

    const actLog = document.getElementById('actLog');
    if (actLog) {
        observer.observe(actLog, { childList: true, subtree: true });
    } else {
        console.error('actLog div not found');
    }
});

// Export functions for use in other files if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initializeSortable,
        updateColumnOrder,
        saveColumnOrder,
        loadAndApplyColumnOrder,
        applyColumnManagement
    };
} else {
    // Make functions available globally
    window.initializeSortable = initializeSortable;
    window.updateColumnOrder = updateColumnOrder;
    window.saveColumnOrder = saveColumnOrder;
    window.loadAndApplyColumnOrder = loadAndApplyColumnOrder;
    window.applyColumnManagement = applyColumnManagement;
}