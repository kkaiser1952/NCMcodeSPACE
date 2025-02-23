// addNewCallsignRow.js
// This file contains a function that creates and inserts a new row into the NCM display table based on the data received for a new callsign.
// V2 Updated: 2024-08-16

function addNewCallsignRow(checkInRowData, recordID) {
    console.log('addNewCallsignRow called with:', JSON.stringify(checkInRowData), recordID);
    
    const netBody = document.getElementById('netBody');
    if (!netBody) {
        console.error('netBody element not found');
        return null;
    }
    console.log('netBody found:', netBody);

    const newRow = document.createElement('tr');
    console.log('New row created');
    
    // Use the columnDefinitions to ensure correct order and attributes
    columnDefinitions.forEach(colDef => {
        console.log(`Processing column ${index}:`, colDef);
        const cell = document.createElement('td');
        
        // Set class and other attributes
        cell.className = colDef.class;
        if (colDef.id) cell.id = colDef.id.replace(':', recordID);
        
        // Set data attributes
        for (const [key, value] of Object.entries(colDef)) {
            if (key.startsWith('data-')) {
                cell.setAttribute(key, value);
            }
        }
        
        // Set content
        const columnName = colDef['data-column'];
        if (columnName && checkInRowData[columnName]) {
            cell.textContent = checkInRowData[columnName];
        } else if (colDef.content) {
            cell.innerHTML = colDef.content.replace('{recordID}', recordID);
        }
        
        newRow.appendChild(cell);
        console.log(`Cell added for column ${index}`);
    });

    netBody.appendChild(newRow);
    console.log('New row added to the table:', newRow.outerHTML);
    return newRow;
}