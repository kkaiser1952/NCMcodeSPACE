// tableUtils.js
// V2 Updated: 2024-06-08

// Function to create a table row with cells based on the row data
function createTableRow(rowData) {
    var row = document.createElement('tr');
    
    // Create cells for each relevant data property
    var idCell = createTableCell(rowData.id);
    var activeCell = createTableCell(rowData.active);
    var callsignCell = createTableCell(rowData.callsign);
    var FnameCell = createTableCell(rowData.Fname);
    var LnameCell = createTableCell(rowData.Lname);
    var netIDCell = createTableCell(rowData.netID);
    var gridCell = createTableCell(rowData.grid);
    var tacticalCell = createTableCell(rowData.tactical);
    var emailCell = createTableCell(rowData.email);
    var latitudeCell = createTableCell(rowData.latitude);
    var longitudeCell = createTableCell(rowData.longitude);
    var credsCell = createTableCell(rowData.creds);
    var activityCell = createTableCell(rowData.activity);
    var commentsCell = createTableCell(rowData.comments);
    var logdateCell = createTableCell(rowData.logdate);
    var netcallCell = createTableCell(rowData.netcall);
    var subNetOfIDCell = createTableCell(rowData.subNetOfID);
    var frequencyCell = createTableCell(rowData.frequency);
    var countyCell = createTableCell(rowData.county);
    var stateCell = createTableCell(rowData.state);
    var countryCell = createTableCell(rowData.country);
    var districtCell = createTableCell(rowData.district);
    var firstLogInCell = createTableCell(rowData.firstLogIn);
    var pbCell = createTableCell(rowData.pb);
    var ttCell = createTableCell(rowData.tt);
    var homeCell = createTableCell(rowData.home);
    var phoneCell = createTableCell(rowData.phone);
    var catCell = createTableCell(rowData.cat);
    var sectionCell = createTableCell(rowData.section);
    var trafficCell = createTableCell(rowData.traffic);
    var rowNumberCell = createTableCell(rowData.row_number);
    var cityCell = createTableCell(rowData.city);
    
    // Append the cells to the row
    row.appendChild(idCell);
    row.appendChild(activeCell);
    row.appendChild(callsignCell);
    row.appendChild(FnameCell);
    row.appendChild(LnameCell);
    row.appendChild(netIDCell);
    row.appendChild(gridCell);
    row.appendChild(tacticalCell);
    row.appendChild(emailCell);
    row.appendChild(latitudeCell);
    row.appendChild(longitudeCell);
    row.appendChild(credsCell);
    row.appendChild(activityCell);
    row.appendChild(commentsCell);
    row.appendChild(logdateCell);
    row.appendChild(netcallCell);
    row.appendChild(subNetOfIDCell);
    row.appendChild(frequencyCell);
    row.appendChild(countyCell);
    row.appendChild(stateCell);
    row.appendChild(countryCell);
    row.appendChild(districtCell);
    row.appendChild(firstLogInCell);
    row.appendChild(pbCell);
    row.appendChild(ttCell);
    row.appendChild(homeCell);
    row.appendChild(phoneCell);
    row.appendChild(catCell);
    row.appendChild(sectionCell);
    row.appendChild(trafficCell);
    row.appendChild(rowNumberCell);
    row.appendChild(cityCell);
    
    return row;
}

// Function to create a table cell with the given content
function createTableCell(content) {
    var cell = document.createElement('td');
    cell.textContent = content;
    return cell;
}

// Function to add a new row to the display table
function addNewRowToTable(rowData) {
    // Get the table element
    var table = document.getElementById('actLog').querySelector('thisNET');
    
    // Create a new row using the createTableRow function
    var newRow = createTableRow(rowData);
    
    // Append the new row to the table
    table.appendChild(newRow);
}