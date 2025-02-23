// CellEditFunctions.js
// Based on jquery_jeditable by NicolasCarpi --> in GitHub
// Added 2018-02-12 -- replaces the same in the NetManager.js &amp; NetManager-p2.js javascripts
// Data source is json/dropdownOptions.json
// V2 Updated: 2024-05-31

function CellEditFunction(jQuery) {
  // Add an event listener for SSE messages
  var source = new EventSource('sse.php');

  source.addEventListener('update', function(event) {
    var data = JSON.parse(event.data);
    var recordID = data.recordID;
    var column = data.column;
    var value = data.value;

    // Find the corresponding cell in the table
    var cell = $('td[data-record-id="' + recordID + '"][data-column="' + column + '"]');

    // Update the cell value
    cell.text(value);
  });

  const dothework = $(".closenet").html().trim();
  if (dothework === "Close Net") {
    const netid = $("#idofnet").eq(0).html().trim();
    $(".editGComms").editable("SaveGenComm.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Click to add...",
      style: "inherit",
      onsubmit: function () {
        //const submitdata = {};
        submitdata[""] = prompt("Enter Your Call");
        return submitdata;
      },
      id: netid,
    });
    
    // ...

    $(".editGComms").editable("SaveGenComm.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Click to add...",
      style: "inherit",
      onsubmit: function () {
        const submitdata = {};
        submitdata[""] = prompt("Enter Your Call");
        return submitdata;
      },
      id: netid,
    });

    $(".editTimeOut").editable("save.php", {
      indicator: "",
      placeholder: "",
      tooltip: "Time Out, double Click to edit. 2018-04-12 19:25:00",
      event: "dblclick",
      style: "inherit",
      callback: function (result, settings, submitdata, id) {
        fetch("updateTOD.php?recordID=" + this.id.split(":").pop())
          .then((response) => {
            if (!response.ok) {
              throw new Error("Update of TOD Query Failed, try again.");
            }
          })
          .catch((error) => {
            alert(error.message);
          });
      },
    });

    $(".editTimeIn").editable("save.php", {
      indicator: "",
      placeholder: "",
      tooltip: "Time In, double Click to edit. 2018-04-12 19:25:00",
      event: "dblclick",
      style: "inherit",
      callback: function (result, settings, submitdata, id) {
        fetch("updateTOD.php?recordID=" + this.id.split(":").pop())
          .then((response) => {
            if (!response.ok) {
              throw new Error("Update of TOD Query Failed, try again.");
            }
          })
          .catch((error) => {
            alert(error.message);
          });
      },
    });

    $(".editCS1").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Call Sign No edit, except when Pre-Built",
      style: "inherit",
    });

    $(".editFnm").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "First Name Click to edit...",
      style: "inherit",
    });

    $(".editonSite").editable("save.php", {
      data: '{"YES":"YES", "NO":"NO"}',
      type: "select",
      placeholder: "",
      onblur: "submit",
      submit: "OK",
      tooltip: "onSite selector dropdown",
      style: "inherit",
    });

    $(".editTAC").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Tactical Call Click to edit...",
      style: "inherit",
    });

    $(".editPhone").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Phone Number Click to edit...",
      style: "inherit",
    });

    $(".editLnm").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Last Name Click to edit...",
      style: "inherit",
    });

    $(".editEMAIL").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Enter a valid email address",
      style: "inherit",
    });

    $(".editCREDS").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Credentials Click to edit...",
      style: "inherit",
    });

    $(".editcnty").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "County Click to edit...",
      style: "inherit",
    });

    $(".editstate").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "State Click to edit...",
      style: "inherit",
    });

    $(".editcity").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "City Click to edit...",
      style: "inherit",
    });

    $(".editcntry").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Country Click to edit...",
      style: "inherit",
    });

    $(".editdist").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "District Click to edit...",
      style: "inherit",
    });

    $(".W3W").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "what3words Click to edit...",
      style: "inherit",
      callback: function (result, settings, submitdata, id) {
        fetch("updateLATLONw3w.php?recordID=" + this.id.split(":").pop())
          .then((response) => {
            if (!response.ok) {
              throw new Error("Update to Lat and Lon failed!");
            }
          })
          .catch((error) => {
            alert(error.message);
          });
      },
    });

    $(".editGRID").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Grid Click to edit...",
      style: "inherit",
      callback: function (result, settings, submitdata, id) {
        fetch("updateLATLON.php?recordID=" + this.id.split(":").pop())
          .then((response) => {
            if (!response.ok) {
              throw new Error("Update to Lat & Lon failed!");
            }
          })
          .catch((error) => {
            alert(error.message);
          });
      },
    });

    $(".editLAT").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Latitude Click to edit...",
      style: "inherit",
      callback: function (result, settings, submitdata, id) {
        fetch("updateGRID.php?recordID=" + this.id.split(":").pop())
          .then((response) => {
            if (!response.ok) {
              throw new Error("Update to GRID failed!");
            }
          })
          .catch((error) => {
            alert(error.message);
          });
      },
    });

    $(".editLON").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Longitude Click to edit...",
      style: "inherit",
      callback: function (result, settings, submitdata, id) {
        fetch("updateGRID.php?recordID=" + this.id.split(":").pop())
          .then((response) => {
            if (!response.ok) {
              throw new Error("Update to GRID failed!");
            }
          })
          .catch((error) => {
            alert(error.message);
          });
      },
    });

    $(".editaprs_call").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "APRS Call including SSD  Click to edit...",
      style: "inherit",
    });

    $(".editteam").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Team name or number Click to edit...",
      style: "inherit",
    });

    $(".editTT").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "TT Click to edit...",
      style: "inherit",
    });

    $(".editCat").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "Trfk-For Click to edit...",
      style: "inherit",
    });

    $(".editSec").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "FD Click to edit...",
      style: "inherit",
    });

    $(".editC").editable("save.php", {
      type: "text",
      onblur: "submit",
      placement: "top",
      placeholder: "",
      indicator: "Saving...",
      tooltip: "Comments, Click to edit...",
      style: "inherit",
    });
    
    $(".c28").editable("save.php", {
      indicator: "Saving...",
      placeholder: "",
      tooltip: "home, Concatination of lat/lon, grid, county & state..",
      style: "inherit",
    });

    $(".editfacility").click(function () {
      const recordID = $(this).attr("id").split(":").pop();
      $(this).data("getFacilityData", { recordID: recordID });
    });

    $(".editfacility").editable("save.php", {
      type: "select",
      onblur: "submit",
      cancel: "Cancel",
      placeholder: "",
      tootltip: "Facility names, click to edit...",
      callback: function () {},
      submit: "Save",
      style: "inherit",
      loadurl: "getListOfFacilities.php",
      loaddata: function () {
        return $(this).data("getFacilityData");
      },
      success: function (data) {
        console.log("JSON data from loadurl:", data);
      },
    });
    
    // Load dropdown options from the JSON file
    fetch('json/dropdownOptions.json')
      .then(response => response.json())
      .then(dropdownOptions => {

    // Configure editable cells with dropdown options
        $(".editable_selectACT").editable("save.php", {
          data: dropdownOptions.editable_selectACT,
          type: "select",
          placeholder: "",
          onblur: "submit",
          submit: "OK",
          callback: function () {
            // refresh();
          },
          tooltip: "Status dropdown",
          style: "inherit",
        });

        $(".editable_selectTFC").editable("save.php", {
          data: dropdownOptions.editable_selectTFC,
          type: "select",
          placeholder: "",
          onblur: "submit",
          callback: function (value, settings) {
            console.log("Dropdown value changed to:", value);
            // refresh();
          },
          tooltip: "Traffic dropdown, select",
          style: "inherit",
        });

        $(".editable_selectMode").editable("save.php", {
          data: dropdownOptions.editable_selectMode,
          type: "select",
          placeholder: "",
          onblur: "submit",
          submit: "OK",
          callback: function () {
            // refresh();
          },
          tooltip: "Mode dropdown, select",
          style: "inherit",
        });

        $(".editable_selectNC").editable("save.php", {
          data: dropdownOptions.editable_selectNC,
          type: "select",
          onblur: "submit",
          placeholder: "",
          callback: function () {
            // refresh();
          },
          tooltip: "Role dropdown",
          style: "inherit",
        });

        $(".editBand").editable("save.php", {
          data: dropdownOptions.editBand,
          type: "select",
          onblur: "submit",
          placeholder: "",
          tooltip: "Band dropdown",
          style: "inherit",
        });
      })
      .catch(error => {
        console.error('Error loading dropdown options:', error);
      });
  }
}