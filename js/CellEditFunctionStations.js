// CellEditFunctionStations.js
// Specifically for the stations table and editStationsTable.php
// V2 Updated: 2024-05-22

function CellEditFunctionStations(jQuery) {
  $(".editFnm").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "First Name Click to edit...",
    style: "inherit",
  });

  $(".editTAC").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "Tactical Call Click to edit...",
    style: "inherit",
  });

  $(".editPhone").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "Phone Number Click to edit...",
    style: "inherit",
  });

  $(".editLnm").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "Last Name Click to edit...",
    style: "inherit",
  });

  $(".editEMAIL").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "Enter a valid email address",
    style: "inherit",
  });

  $(".editCREDS").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "Credentials Click to edit...",
    style: "inherit",
  });

  $(".editcnty").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "County Click to edit...",
    style: "inherit",
  });

  $(".editstate").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "State Click to edit...",
    style: "inherit",
  });

  $(".editdist").editable("stationsave.php", {
    indicator: "Saving...",
    placeholder: "",
    tooltip: "District Click to edit...",
    style: "inherit",
  });

  $(".editGRID").editable("stationsave.php", {
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
        })
        .finally(() => {
          refresh();
        });
    },
  });

  $(".editLAT").editable("stationsave.php", {
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
        })
        .finally(() => {
          refresh();
        });
    },
  });

  $(".editLON").editable("stationsave.php", {
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
        })
        .finally(() => {
          refresh();
        });
    },
  });
}