// sidebar.js
// Written: 2024-09-01
// Updated: 2024-09-28

document.addEventListener('DOMContentLoaded', function() {
    const columnSidebar = document.getElementById('columnSidebar');
    const columnOptions = document.getElementById('columnOptions');
    const applyChangesBtn = document.getElementById('applyChanges');
    const resetToDefaultBtn = document.getElementById('resetToDefault');
    const closeBtn = document.querySelector('.close-btn');

    // Use the columnsData variable defined in sidebar.php
    let columns = columnsData || {};
    let userPreferences = JSON.parse(localStorage.getItem('columnPreferences')) || {};

    function renderColumnOptions() {
        columnOptions.innerHTML = '';
        Object.entries(columns).forEach(([category, cols]) => {
            const categoryDiv = document.createElement('div');
            categoryDiv.innerHTML = `<h4 class="category-header">${category.charAt(0).toUpperCase() + category.slice(1)}</h4>`;
            Object.entries(cols).forEach(([id, title]) => {
                const isDefault = category === 'default';
                const isChecked = isDefault || userPreferences[id] !== false;
                const wrapper = document.createElement('div');
                wrapper.className = 'checkbox-wrapper';
                wrapper.innerHTML = `
                    <input type="checkbox" id="${id}" ${isChecked ? 'checked' : ''} ${isDefault ? 'disabled' : ''}>
                    <label for="${id}" ${isDefault ? 'class="default-column"' : ''}>${title}</label>
                `;
                categoryDiv.appendChild(wrapper);
            });
            columnOptions.appendChild(categoryDiv);
        });
    } // End renderColumnOptions()

    function showSidebar() {
        columnSidebar.classList.add('show');
    }

    function closeSidebar() {
        columnSidebar.classList.remove('show');
    }

    function applyChanges() {
        Object.entries(columns).forEach(([category, cols]) => {
            if (category !== 'default') {
                Object.keys(cols).forEach(id => {
                    const checkbox = document.getElementById(id);
                    if (checkbox) {
                        userPreferences[id] = checkbox.checked;
                    }
                });
            }
        });

        localStorage.setItem('columnPreferences', JSON.stringify(userPreferences));
        updateTableColumns();
        closeSidebar();
    }

    function resetToDefault() {
        Object.entries(columns).forEach(([category, cols]) => {
            if (category !== 'default') {
                Object.keys(cols).forEach(id => {
                    const checkbox = document.getElementById(id);
                    if (checkbox) {
                        checkbox.checked = false;
                        userPreferences[id] = false;
                    }
                });
            }
        });
        localStorage.setItem('columnPreferences', JSON.stringify(userPreferences));
        updateTableColumns();
    }

    function updateTableColumns() {
        const table = document.getElementById('thisNet');
        if (!table) return;

        const headers = table.querySelectorAll('th');
        const rows = table.querySelectorAll('tbody tr');

        headers.forEach(header => {
            const columnClass = Array.from(header.classList).find(cls => cls.startsWith('c'));
            if (columnClass) {
                const isDefault = Object.keys(columns.default).includes(columnClass);
                header.style.display = isDefault || userPreferences[columnClass] !== false ? '' : 'none';
            }
        });

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                const columnClass = Array.from(cell.classList).find(cls => cls.startsWith('c'));
                if (columnClass) {
                    const isDefault = Object.keys(columns.default).includes(columnClass);
                    cell.style.display = isDefault || userPreferences[columnClass] !== false ? '' : 'none';
                }
            });
        });
    }

    // Event listeners
    closeBtn.addEventListener('click', closeSidebar);
    applyChangesBtn.addEventListener('click', applyChanges);
    resetToDefaultBtn.addEventListener('click', resetToDefault);

    // Initialize
    renderColumnOptions();
    updateTableColumns(); // Apply saved preferences on page load

    // Expose showSidebar function globally
    window.openSidebar = showSidebar;
});