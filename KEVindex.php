<!doctype html>
<!-- V2 Updated: 2024-04-09 -->
<!-- This code is now in net-control.space -->
<?php
/***********************************************************************************************************
 *
 * Net Control Manager is a Create, Read, Update, Delete (CRUD) application used by Amateur Radio operators to
 * document various net operations such as weather emergencies, club meetings, bike ride support and any other
 * logging and/or reporting intensive communications support and management needs.
 * A variety of reports can be created such as mapping stations locations and other DHS/FEMA needs. Including
 * the ICS-214 and ICS-309 reports and access to all the many others.
 *
 * No Guarantees or Warranties. EXCEPT AS EXPRESSLY PROVIDED IN THIS AGREEMENT, NO PARTY MAKES ANY GUARANTEES OR WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, ANY WARRANTIES OF MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE, WHETHER ARISING BY OPERATION OF LAW OR OTHERWISE. PROVIDER SPECIFICALLY DISCLAIMS ANY IMPLIED WARRANTY OF MERCHANTABILITY AND/OR ANY IMPLIED WARRANTY OF FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Extensive help is available by clicking Help in the upper right corner of the opening page.
 *
 * First written some time in late 2015 and in continous enhancment and upgrade since.
 * copyright 2015-2024 by: Keith Kaiser, WA0TJT
 * Written by: Keith Kaiser, WA0TJT, with the help of many others. See the help file for more details.
 * I can be reached at wa0tjt at gmail.com
 *
 * The version number will be v2.0 when put in production
 *
 * How NCM works (for the most part, sorta, kinda):
 * If a net is selected from the dropdown
 * 1) The list of nets is selected from #select1, the past 10 days only. Nets highlighted in green are open, blue are pre-built nets, no color are closed nets.
 * 2) The selected net information is passed to the showActivities() function in NetManager.js
 * 3) If this net is for logging custom contacts, the code is in NetManager.js @ showActivities()
 * 3a) This extra code change the Name field to a custom field for logging purposes
 * 4) It runs buildUpperRightCorner.php and getactivities.php to build the page
 * 4a) buildUpperRightCorner.php is used to retrieve data and populate #ourfreqs in #rightCorner
 * 4b) getactivities.php is used to retrieve the data of the selcted net and populate #actLog
 *
 * If a new net is created
 * 1) Each of the dropdowns supplies part of the information needed to start the new net
 * 2) The callsign of the person starting the net is also ented into the new net
 * 3) When that data is complete it is passed to the newNet() function in NetManager-p2.js
 * 4) NetManager-p2.js runs newNet.php to create, and populate NetLog and TimeLog appropriatly
 ***************************************************************************************/

//phpinfo();
// PHP Version => 7.0.33
// MySQL Version => 8.0

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";  // Access to MySQL
require_once "wx.php";               // Makes the weather information available
//require_once "NCMStats.php";       // Get some stats
?>

<html lang="en">
<head>
    <meta charset="UTF-8"/>

    <title>Amateur Radio Net Control Manager</title>

    <!-- Below is all about favicon images https://www.favicon-generator.org -->
    <link rel="apple-touch-icon" sizes="57x57" href="favicons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="favicons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="favicons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="favicons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicons/favicon-16x16.png">
    <link rel="manifest" href="favicons/manifest.json">

    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!-- End all about favicon images -->

    <!-- The meta tag below sends the user to the help file after 90+ minutes of inactivity. -->
    <meta http-equiv="refresh" content="9200; URL=https://net-control.space/help.php">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Amateur Radio Net Control Manager">
    <meta name="author" content="Keith Kaiser, WA0TJT">
    <meta name="Rating" content="General">
    <meta name="Revisit" content="1 month">
    <meta name="keywords"
          content="Amateur Radio Net, Ham Net, Net Control, Call Sign, NCM, Emergency Management Net, Net Control Manager, Net Control Manager, Amateur Radio Net Control, Ham Radio Net Control">

    <!-- https://fonts.google.com -->
    <!-- Allerta is used to slash zeros so don't delete -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Allerta&display=swap">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Stoke&display=swap">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Cantora+One&display=swap">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Risque&display=swap">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


    <link rel="stylesheet" href="/css/KEVsite.css">
</head>

<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid" style="display: flex; justify-content: space-between">
        <a class="navbar-brand" href="/">
            <img class="siteLogo1" src="images/NCM.png" alt="NCM">
            <img class="siteLogo2" src="images/NCM3.png" alt="Net Control Manager">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="utcSelector">
            Timezone
            <div>
                <span>UTC</span>
                <div class="form-check form-switch form-check-inline">
                    <input id="timezone" class="form-check-input form-check-inline" type="checkbox">
                </div>
                <span>CDT</span>  <!-- TODO: Make this work, and make the "CDT" text dynamic based on user local time -->
            </div>
        </div>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Net Info
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item disabled" href="#" style="color: black;">Select a Net to Access Options</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item disabled" href="#">Preamble</a></li>
                        <li><a class="dropdown-item disabled" href="#">Agenda</a></li>
                        <li><a class="dropdown-item disabled" href="#">Closing</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">NET INFO EDITOR</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="buildCallHistoryByNetCall" onclick="buildCallHistoryByNetCall()" title="build a Call History By NetCall">The Usual Suspects</a></li>
                        <li><a class="dropdown-item" href="buildGroupList.php" target="_blank" rel="noopener" title="Group List">Groups Information</a></li>
                        <li><a class="dropdown-item" href="groupScoreCard.php" target="_blank" rel="noopener" title="Group Scores">Group Score Card</a></li>
                        <li><a class="dropdown-item" href="listNets.php" target="_blank" rel="noopener" title="All the nets">List/Find ALL nets</a></li>
                        <li><a class="dropdown-item" href="#" onclick="net_by_number();" title="Net by the Number">Browse a Net by Number</a></li>
                        <li><a class="dropdown-item" href="NCMreports.php" target="_blank" rel="noopener" title="Stats about NCM">Statistics</a></li>
                        <li><a class="dropdown-item" href="listAllPOIs.php" target="_blank" rel="noopener" id="PoiList" title="List all Pois">List all POIs</a></li>
                        <li><a class="dropdown-item" href="AddRF-HolePOI.php" target="_blank" rel="noopener" id="PoiList" title="Create New RF Hole POI">Add RF Hole POI</a></li>
                        <li><a class="dropdown-item" href="#" id="geoDist" onclick="geoDistance()" title="GeoDistance">GeoDistance</a></li>
                        <li><a class="dropdown-item" href="#" id="mapIDs" onclick="map2()" title="Map This Net">Map This Net</a></li>
                        <li><a class="dropdown-item" href="https://vhf.dxview.org" id="mapdxView" target="_blank">DXView Propagation Map</a></li>
                        <li><a class="dropdown-item" href="https://www.swpc.noaa.gov" id="noaaSWX" target="_blank">NOAA Space Weather</a></li>
                        <li><a class="dropdown-item" href="https://spaceweather.com" id="SpaceWX" target="_blank">Space Weather</a></li>
                        <li><a class="dropdown-item" href="#" id="graphtimeline" onclick="graphtimeline()" title="Graphic Time Line of the active net">Graphic Time Line</a></li>
                        <li><a class="dropdown-item" href="#" id="ics205Abutton" onclick="ics205Abutton()" title="ICS-205A Report of the active net">ICS-205A</a></li>
                        <li><a class="dropdown-item" href="#" id="ics214button" onclick="ics214button()" title="ICS-214 Report of the active net">ICS-214</a></li>
                        <li><a class="dropdown-item" href="#" id="ics309button" onclick="ics309button()" title="ICS-309 Report of the active net">ICS-309</a></li>
                        <li><a class="dropdown-item" href="https://training.fema.gov/icsresource/icsforms.aspx" id="icsforms" target="_blank" rel="noopener">Addional ICS Forms</a></li>
                        <li><a class="dropdown-item" href="https://docs.google.com/spreadsheets/d/1eFUfVLfHp8uo58ryFwxncbONJ9TZ1DKGLX8MZJIRZmM/edit#gid=0" target="_blank" rel="noopener" title="The MECC Communications Plan">MECC Comm Plan</a></li>
                        <li><a class="dropdown-item" href="https://upload.wikimedia.org/wikipedia/commons/e/e7/Timezones2008.png" target="_blank" rel="noopener" title="World Time Zone Map">World Time Zone Map</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More...
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Convert to a Pre-Built (Roll Call) net.</a></li>
                        <li><a class="dropdown-item" href="#">Create a Group Profile</a></li>
                        <li><a class="dropdown-item" href="#">Create a Heard List</a></li>
                        <li><a class="dropdown-item" href="#">Create FSQ Macro List</a></li>
                        <li><a class="dropdown-item" href="#">Report by Call Sign</a></li>
                        <li><a class="dropdown-item" href="#">List all User Call Signs</a></li>
                        <li><a class="dropdown-item" href="#">NCM Documentation</a></li>
                        <li><a class="dropdown-item" href="#">KCNARES Deployment Manual</a></li>
                        <li><a class="dropdown-item" href="#">ARES Resources</a></li>
                        <li><a class="dropdown-item" href="#">ARES E-Letter</a></li>
                        <li><a class="dropdown-item" href="#">Download the ARES Manual(PDF)</a></li>
                        <li><a class="dropdown-item" href="#">Download ARES Field Resources Manual(PDF)</a></li>
                        <li><a class="dropdown-item" href="#">ARES Standardized Training Plan Task Book [Fillable PDF]</a></li>
                        <li><a class="dropdown-item" href="#">ARES Plan</a></li>
                        <li><a class="dropdown-item" href="#">ARES Group Registration</a></li>
                        <li><a class="dropdown-item" href="#">Emergency Communications Training</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/help">NCM HELP</a>
                </li>
            </ul>

        </div>
    </div>
</nav>
<div class="wxDisplay">
    <a href="https://forecast.weather.gov/MapClick.php?lat=<?php echo getWxLat() ?>&lon=<?php echo getWxLong() ?>" class="theWX" target="_blank" rel="noopener">
        <img src="images/US-NWS-Logo.png" alt="US-NWS-Logo" onclick="newWX()">
        <!-- CurrentWX() was developed by Jeremy Geeo, KD0EAV Found it wx.php -->
        <?php echo getOpenWX(); ?> <!-- from wx.php -->
    </a>
</div>

<div class="page container-fluid">
    <!-- INITIAL LOADING SCREEN -->
    <div class="initialLoadScreen">
        <div>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#startNetModal">
                Start a New Net
            </button>
            <?php require_once "KEVstartNetModal.php"; ?>

            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#browseNetNumberModal">
                Browse a Net by Number
            </button>
            <?php require_once "KEVbrowseNetNumberModal.php"; ?>
        </div>
        <div>
            <hr>
            <b>...or, Select an Active Net:</b>
            <form>
                <select id="activeNetSelector" class="activeNetSelector">
                    <option disabled selected>Select a Net</option>
                </select>
            </form>
        </div>
    </div>

    <div class="netController" style="display: none;">
        <?php require_once "KEVnetController.php"; ?>
    </div>
</div>


<script>
    // === LOAD NET FROM URL IF PROVIDED ===
    $(document).ready(function () {
        var netID = parseInt(window.location.hash.replace("#", ''));
        console.log("Opening Net: " + netID);
        if (!isNaN(netID)) {
            loadActiveNet(netID);
        }
    });

    // === GLOBAL STATE VARIABLES ===
    var NET_ID = null;
    var EDIT_IS_ACTIVE = false;
    var ACTIVE_EDIT_CELL = null;


    // === HANDLE NET SELECTOR DROPDOWN ===
    $("#activeNetSelector").on("click", function (e) {
        getActiveNets();
    });

    function getActiveNets() {
        $.getJSON("KEVbuildOptionsForSelect.php", function (data) {
            var activeNetsDropdown = $('#activeNetSelector');

            activeNetsDropdown.find('option').remove().end(); //clear existing options, if any

            // Create a default "Select a Net" option
            var newOption = document.createElement('option');
            newOption.text = "Select a Net";
            newOption.disabled = true;
            $(newOption).appendTo(activeNetsDropdown);

            data.forEach(function (net) {
                // Create an <option> element
                var newOption = document.createElement('option');

                newOption.text = net.activity;
                newOption.value = net.netID;

                $(newOption).appendTo(activeNetsDropdown);
            });
        })
            .fail(function () {
                console.log("error");
            }) //TODO ADD ERROR MESSAGING
    }

    $("#activeNetSelector").on("change", function (e) {
        alert(this.value);
        var selectedId = $('#activeNetSelector').find(":selected").val();
        document.location.hash = selectedId;

        loadActiveNet(selectedId);
    });


    // ========== LOAD SELECTED NET ==========
    function loadActiveNet(selectedId) {
        NET_ID = selectedId;
        buildStationsTable(selectedId);
        $('.initialLoadScreen').hide();
        $('.netController').fadeIn();
    }

    function buildStationsTable(selectedId) {
        var table = $('#nct').DataTable({
            paging: false,
            searching: false,
            info: false,
            "aaSorting": [],
            ajax: {
                url: "KEVgetActiveNetStations.php?netID=" + selectedId,
                dataSrc: "",
            },
            createdRow: function (row, data, dataIndex) {
                row.setAttribute("data-id", data.recordID);

                // === SET ROW COLORS ===
                var controlColors = ["PRM", "2nd", "Log", "LSN"];
                if (controlColors.includes(data.netcontrol)) {
                    $(row).addClass("rowCONTROL");
                }

                var digitalColors = ["Dig", "CW", "Winlink", "V&D"];
                if (digitalColors.includes(data.mode)) {
                    $(row).addClass("rowDIGITAL");
                }

                switch (data.active) {
                    case "OUT":
                    case "IN-OUT":
                        $(row).addClass("rowOUT");
                        break;
                    case "BRB":
                        $(row).addClass("rowBRB");
                        break;
                }

                switch (data.traffic) {
                    case "Priority":
                    case "Emergency":
                        $(row).addClass("rowEMERGENCY");
                        break;
                }


                // if (digitalColors.includes(data.mode)) { $(row).addClass("rowDIGITAL"); }
            },
            columns: [
                {
                    data: 'row_number',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "row_number");
                        td.setAttribute("data-editable", false);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'netcontrol',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "netcontrol");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())

                    }
                },
                {
                    data: 'Mode',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "Mode");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'active',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "active");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())

                        switch (cellData) {
                            case "MISSING":
                                $(td).addClass("cellMISSING");
                                break;
                            case "Moved":
                                $(td).addClass("cellMOVED");
                                break;
                        }
                    }
                },
                {
                    data: 'traffic',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "traffic");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID());

                        switch (cellData) {
                            case "Traffic":
                                $(td).addClass("cellTRAFFIC");
                                break;
                        }
                    }
                },
                {
                    data: 'callsign',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "callsign");
                        td.setAttribute("data-editable", false);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'Fname',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "Fname");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'tactical',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "tactical");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'logdate',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "logdate");
                        td.setAttribute("data-editable", false);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'timeout',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "timeout");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'comments',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "comments");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'state',
                    createdCell: function (td, cellData, rowData, row, col) {
                        td.setAttribute("data-name", "state");
                        td.setAttribute("data-editable", true);
                        td.setAttribute("data-uuid", crypto.randomUUID())
                    }
                },
                {
                    data: 'netID',
                    createdCell: function (td, cellData, rowData, row, col) {
                        var uuid = crypto.randomUUID();
                        td.setAttribute("data-name", "netID");
                        td.setAttribute("data-editable", false);
                        td.setAttribute("data-uuid", uuid)
                        $(td).html('<img src="/images/trash.svg" class="deleteRow" id="delete' + uuid + '">')
                    }
                },
            ]
        });

        // Automatically refresh net every 30 seconds even if websocket isn't called
        setInterval(function () {
            if (!EDIT_IS_ACTIVE) {
                table.ajax.reload();
                console.log("Automatic Reload - " + Date());
            }
        }, 5000);


        $.getJSON("KEVgetkind.php?netID=" + selectedId, function (data) {
            $('#netTitle').text(data['netcall'] + " - " + data['activity']);
        }).fail(function () {
            console.log("error");
        });
    }

    // ========== HANDLE CELL EDIT INPUT ==========
    $('body').on('click', '#nct td', function (e) {
        if ($(this).attr("data-editable") == "true") {
            showCellEdit(this);
        }
    });

    function showCellEdit(td) {
        var thisTD = $(td);
        var uuid = thisTD.attr("data-uuid");

        EDIT_IS_ACTIVE = true;
        ACTIVE_EDIT_CELL = uuid;

        thisTD.attr("data-prevVal", thisTD.html());

        thisTD.html(
            '<form id="cef-' + uuid + '">' +
            '<input name="value" id="cei-' + uuid + '">' +
            '</form>'
        );

        $('#cei-' + uuid).focus();

        $('body').on("blur", '#cei-' + uuid, function () {
            saveCellEdit(uuid);
        });

        $('body').on("submit", '#cef-' + uuid, function (e) {
            e.preventDefault();
            saveCellEdit(uuid);
        });

        // NOTE: Escape (cancel) is in the master keyboard shortcuts function below.
    }

    function saveCellEdit(uuid) {
        var value = $("#cei-" + uuid).val()
        var thisTD = $('*[data-uuid="' + uuid + '"]');

        table = $('#nct').DataTable({
            retrieve: true
        });

        thisTD.html(value);
        var columnName = thisTD.attr("data-name");
        var rowData = table.row(thisTD.parent()).data();

        rowData[columnName] = value;

        ///TODO: AJAX REQUEST TO KEVsave.php

        alert("Cell Psuedo-Saved!")

        EDIT_IS_ACTIVE = false;

        // Run an update just in case we missed one while we were editing
        table.ajax().reload();
    }

    function cancelCellEdit(uuid) {
        var thisTD = $('*[data-uuid="' + uuid + '"]');

        thisTD.html(thisTD.attr("data-prevVal"));
        EDIT_IS_ACTIVE = false;
    }


    // ============ TIMELINE ===============
    $('#openTimeline').on('click', function (e) {
        buildTimeline();
    })


    function buildTimeline() {
        var selectedId = NET_ID;

        var table = $('#tt').DataTable({
            retrieve: true,
            paging: false,
            searching: false,
            ordering: false,
            info: false,
            responsive: true,
            "aaSorting": [],
            ajax: {
                url: "KEVgetTimeLog.php?netID=" + selectedId,
                dataSrc: "",
            },
            columns: [
                {
                    data: 'timestamp',
                    className: "nowrap"
                },
                {
                    data: 'ID'
                },
                {
                    data: 'callsign'
                },
                {
                    data: 'comment',
                    width: '100%'
                },
            ],
        });

        // Automatically refresh net every 30 seconds even if websocket isn't called
        setInterval(function () {
            table.ajax.reload();
            console.log("Automatic Timeline Reload - " + Date());
        }, 5000);

        $('.timeline').fadeIn();
    }


    // === KEYBOARD SHORTCUTS ===
    $(document).on('keydown', function (event) {
        switch (event.keyCode) {
            case 27:  // ESCAPE
                if (EDIT_IS_ACTIVE) {
                    cancelCellEdit();
                }
                break;
        }
    });
</script>


</body>
</html>