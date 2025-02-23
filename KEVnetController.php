<!-- NET CONTROL TABLE -->
<link href="//cdn.datatables.net/2.0.6/css/dataTables.dataTables.min.css" rel="stylesheet">
<script src="//cdn.datatables.net/2.0.6/js/dataTables.min.js"></script>

<div class="freqTableTray">
    <div class="fttTab" id="fttTab" title="Group Frequency Information">
        <img src="/images/broadcast-tower-regular.svg">
    </div>
    <div class="fttPanel">
        <span class="fttTitle">Group Information</span>
        <table id="freqTable">
            <tr>
                <th class='edit_r1c1 r1c1 nobg'>$r1c1</th>
                <th class='edit_r1c2 r1c2 nobg'>$r1c2</th>
                <th class='edit_r1c3 r1c3 nobg'>$r1c3</th>
                <th class='edit_r1c4 r1c4 nobg'>$r1c4</th>
            </tr>
            <tr>
                <td class='edit_r2c1 r2c1 nobg1'>$r2c1</td>
                <td class='edit_r2c2 r2c2 nobg2'>$r2c2</td>
                <td class='edit_r2c3 r2c3 nobg2'>$r2c3</td>
                <td class='edit_r2c4 r2c4 nobg2'>$r2c4</td>
            </tr>
            <tr>
                <td class='edit_r3c1 r3c1 nobg1'>$r3c1</td>
                <td class='edit_r3c2 r3c2 nobg2'>$r3c2</td>
                <td class='edit_r3c3 r3c3 nobg2'>$r3c3</td>
                <td class='edit_r3c4 r3c4 nobg2'>$r3c4</td>
            </tr>
            <tr>
                <td class='edit_r4c1 r4c1 nobg1'>$r4c1</td>
                <td class='edit_r4c2 r4c2 nobg' nowrap>$r4c2</td>
                <td class='edit_r4c3 r4c3 nobg'>$r4c3</td>
                <td class='edit_r4c4 r4c4 nobg'>$r4c4</td>
            </tr>
            <tr>
                <td class='edit_r5c1 r5c1 nobg1'>$r5c1</td>
                <td class='edit_r5c2 r5c2 nobg2'>$r5c2</td>
                <td class='edit_r5c3 r5c3 nobg2' nowrap>$r5c3</td>
                <td class='edit_r5c4 r5c4 nobg2'>$r5c4</td>
            </tr>

            <!--            if ($call !== 'DMR') {-->
            <tr>
                <td class='edit_r6c1 r6c1 nobg1'>$r6c1</td>
                <td class='edit_r6c2 r6c2 nobg2' colspan='3'>
                    <a href='$r6c2' target='_blank'>$r6c2</a><br>
                    <a href='$r6c3' target='_blank'>$r6c3</a>
                </td>
            </tr>
            <!--            } elseif ($call === 'DMR') {-->
            <tr>
                <td class='edit_r6c1 r6c1 nobg1'>$r6c1</td>
                <td class='edit_r6c2 r6c2 nobg2'>$r6c2</td>
                <td class='edit_r6c3 r6c3 nobg2'>$r6c3</td>
                <td class='edit_r6c4 r6c4 nobg2'>$r6c4</td>
            </tr>
            <!--            }-->

        </table>

    </div> <!-- Filled by buildUpperRightCorner.php -->
</div>

<h1 id="netTitle" class="netTitle"></h1>
<hr>
<div class="netControlBar">
    <form id="ncbCheckinForm" class="ncbCheckinForm">
        <input type="text" id="ncbfCallsign" name="callsign" placeholder="CALLSIGN">
        <input type="text" id="ncbfName" name="name" placeholder="Name">
        <input type="text" id="ncbfTraffic" name="traffic" placeholder="T">
        <button type="submit">Check In</button>
    </form>

    <div class="nbcCenter">
        <button id="showHideColumns" class="showHideColumns">Show/Hide Columns</button>
        <button id="openTimeline" class="openTimeline">Open Timeline</button>
    </div>

    <div class="ncbRight">
        <button id="closeNet" class="closeNet">Close Net</button>
    </div>

</div>

<div class="netControlTable">
    <table class="nct" id="nct">
        <thead>
        <tr>
            <th>#</th>
            <th>Role</th>
            <th>Mode</th>
            <th>Status</th>
            <th>Traffic</th>
            <th>Callsign</th>
            <th>First Name</th>
            <th>Tactical</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Timeline Comments</th>
            <th>State</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="netStats">
    <span id="netTime">Time Goes Here</span>
    <span id="volunteerHours">Volunteer Hours Go Here</span>
</div>
<div class="netFooter">
    <span id="netInfoString"></span>
    <a id="exportCSV" href="#">Export CSV</a>
    <a id="geoDistance" href="#">geoDistance</a>
    <a id="mapNet" href="#">Map This Net</a>
</div>

<div class="timeline">
    <hr>

    <div class="timelogTable">
        <table class="tt" id="tt">
            <thead>
            <tr>
                <th class="fit">Date/Time</th>
                <th class="fit">ID</th>
                <th class="fit">Actor</th>
                <th>Entry Details</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ======= FREQ TABLE SCRIPTS ======= -->
<script>
    $(document).ready(function () {
        $('.fttPanel').css("right", "-" + $('.fttPanel').width() + "px");
    });

    $('#fttTab').on('click', function (e) {
        var ftt = $('.freqTableTray');
        var width = $('.freqTableTray').width();

        if (ftt.hasClass('visible')) {
            ftt.animate({"right": "-" + $('.fttPanel').width() + "px"}, 500);
            ftt.removeClass("visible");
        } else {
            ftt.animate({"right": "0px"}, 500);
            ftt.addClass("visible");
        }
    });
</script>