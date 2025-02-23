<?php
// This page is used to get some information needed to either copy or build a pre-built Net used for furture nets
// Written: 2018-12-12

// preBuild.php
// V2 Updated: 2024-06-06

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnectDtls.php";  // Access to MySQL

$sql = "SELECT AUTO_INCREMENT 
        FROM information_schema.tables
        WHERE table_name = 'NetKind' AND table_schema = 'ncm'";

$stmt = $db_found->prepare($sql);
$stmt->execute();
$newID = $stmt->fetchColumn();

if (isset($_POST["submit"])) {
    $eventCall = strtoupper($_POST["eventCall"] ?? '');
    $eventName = ucwords($_POST["eventName"] ?? '');
    $eventDate = $_POST["eventDate"] ?? '';
    $primaryRptr = $_POST["primaryRptr"] ?? '';
    $secondaryRptr = $_POST["secondaryRptr"] ?? '';
    $primarySmplx = $_POST["primarySmplx"] ?? '';
    $secondarySmplx = $_POST["secondarySmplx"] ?? '';
    $primaryPhone = $_POST["primaryPhone"] ?? '';
    $secondaryPhone = $_POST["secondaryPhone"] ?? '';
    $primaryEmail = $_POST["primaryEmail"] ?? '';
    $secondaryEmail = $_POST["secondaryEmail"] ?? '';
    $primaryURL = $_POST["primaryURL"] ?? '';
    $secondaryURL = $_POST["secondaryURL"] ?? '';
    $buildorcopy = $_POST["buildorcopy"] ?? '';
    $previousPreBuild = $_POST["previousPreBuild"] ?? '';

    $row1 = htmlspecialchars(",Repeater,Simplex,Phone");
    $row2 = htmlspecialchars("Primary, $primaryRptr, $primarySmplx, $primaryPhone");
    $row3 = htmlspecialchars("Secondary, $secondaryRptr, $secondarySmplx, $secondaryPhone");
    $row4 = htmlspecialchars("URL, $primaryURL, $secondaryURL");
    $row5 = htmlspecialchars("Email, $primaryEmail, $secondaryEmail");
    $row6 = htmlspecialchars(",$eventName");

    $sqlKind = "INSERT INTO NetKind (`call`, org, dflt_kind, kindofnet, dflt_freq, freq, row1, row2, row3, row4, row5, row6)
                VALUES (:eventCall, :eventName, :newID, :eventName, :newID, :primaryRptr, :row1, :row2, :row3, :row4, :row5, :row6)";

    $stmtKind = $db_found->prepare($sqlKind);
    $stmtKind->bindValue(':eventCall', $eventCall);
    $stmtKind->bindValue(':eventName', $eventName);
    $stmtKind->bindValue(':newID', $newID);
    $stmtKind->bindValue(':primaryRptr', $primaryRptr);
    $stmtKind->bindValue(':row1', $row1);
    $stmtKind->bindValue(':row2', $row2);
    $stmtKind->bindValue(':row3', $row3);
    $stmtKind->bindValue(':row4', $row4);
    $stmtKind->bindValue(':row5', $row5);
    $stmtKind->bindValue(':row6', $row6);
    $stmtKind->execute();
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Pre-Build Net Questions</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/NetManager.css" />
    <link rel="stylesheet" type="text/css" href="css/preBuild.css" />
    <link rel="stylesheet" type="text/css" href="css/eventsBuild.css" />

    <link rel="stylesheet" type="text/css" href="css/jquery.modal.min.css" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
    <script src="js/jquery.freezeheader.js"></script>
    <script src="js/jquery.simpleTab.min.js"></script>

    <script src="js/jquery.modal.min.js" charset="utf-8"></script>
    <script src="js/jquery.base64.js"></script>
    <script src="js/jspdf/libs/sprintf.js"></script>

    <script src="js/jspdf/jspdf.js"></script>
    <script src="js/jspdf/libs/base64.js"></script>
    <script src="js/jeditable.js" charset="utf-8"></script>

    <script src="js/sortTable.js"></script>
    <script src="js/NetManager.js"></script>
    <script src="js/hamgridsquare.js"></script>

    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="js/datetimepicker_css.js"></script>

    <script>
        function closeWin() {
            close();   // Closes the new window
        }

        $(function() {
            $("#eventDate").datepicker({
                buttonImage: "images/calendar-green.gif",
                defaultDate: '+1d',
                showTrigger: '#calImg',
                changeMonth: true,
                changeYear: true,
                minDate: +1
            });
        });

        $(document).ready(function() {
            $('input[type=radio][name=buildorcopy]').on('change', function() {
                switch ($(this).val()) {
                    case 'build':
                        $("#theList").addClass("hidden");
                        break;
                    case 'copy':
                        $("#theList").removeClass("hidden");
                        break;
                }
            });
        });
    </script>

    <style>
        form label[for="yes"],
        form label[for="no"],
        form input[type="radio"] {
            clear: none;
            width: auto;
        }

        .eventName {
            text-transform: capitalize;
        }

        .eventCall {
            text-transform: uppercase;
        }

        input[type=text] {
            width: 60%;
            padding: 12px 20px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid red;
            border-radius: 4px;
            color: red;
            font-size: 20px;
            font-weight: 400;
        }

        input[type=text]:focus {
            background-color: #d2eaf3;
        }

        input[type=text]:focus {
            border: 3px solid #3809f1;
        }

        select.previousPreBuild {
            width: 60%;
            padding: 12px 20px;
            box-sizing: border-box;
            border: 2px solid red;
            border-radius: 4px;
            background-color: #f1f1f1;
            font-size: 20px;
            font-weight: 400;
        }
    </style>

</head>
<body>

    <div class="body">
        <p>Help Using the Pre-Build Net system is <a href="http://net-control.space/help.php" target="_blank">here:</a></p>

        <br>
        <button onclick="closeWin()">Close Window</button>
        <br><br>

        <h2>Pre-build Information</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="eventCall">Enter a group call an acronym or primary call for the net. (required):</label><br>
            <input type="text" id="eventCall" name="eventCall" class="eventCall" placeholder="KS0JC" required>
            <br><br>

            <label for="eventName">What is the name of this event? (required):</label><br>
            <input type="text" id="eventName" name="eventName" class="eventName" placeholder="Kansas City Marathon" required>
            <br><br>

            <label for="eventDate" id="eventDateLabel">Click to Select an Event Date. (required):</label>&nbsp;&nbsp;&nbsp;<br>
            <input type="text" id="eventDate" name="eventDate" class="datepick" required>
            <br><br>

            <label for="eventRptr" id="eventRptrLabel">Enter Primary and Secondary Repeater Frequencies</label><br>
            <input type="text" id="primaryRptr" name="primaryRptr" class="primaryRptr" placeholder="147.330MHz, No Tone"><br>
            <input type="text" id="secondaryRptr" name="secondaryRptr" class="secondaryRptr" placeholder="146.790MHz, T:107.2">
            <br><br>

            <label for="eventSmplx" id="eventSmplxLabel">Enter Primary and Secondary Simplex Frequencies</label><br>
            <input type="text" id="primarySmplx" name="primarySmplx" class="primarySmplx" placeholder="145.520MHz"><br>
            <input type="text" id="secondarySmplx" name="secondarySmplx" class="secondarySmplx" placeholder="146.520MHz">
            <br><br>

            <label for="eventPhone" id="eventPhoneLabel">Enter Primary and Secondary Telephone numbers to use</label><br>
            <input type="text" id="primaryPhone" name="primaryPhone" class="primaryPhone" placeholder="816-555-5555"><br>
            <input type="text" id="secondaryPhone" name="secondaryPhone" class="secondaryPhone" placeholder="816-555-5555">
            <br><br>

            <label for="eventURL" id="eventURLLabel">Enter Primary and Secondary URL's</label><br>
            <input type="text" id="primaryURL" name="primaryURL" class="primaryURL" placeholder="http://KCMarathon.com"><br>
            <input type="text" id="secondaryURL" name="secondaryURL" class="secondaryURL" placeholder="http://redcross.com">
            <br><br>

            <label for="eventEmail" id="eventEmailLabel">Enter Primary and Secondary Email Address</label><br>
            <input type="text" id="primaryEmail" name="primaryEmail" class="primaryEmail" placeholder="kcshorty@gmail.com"><br>
            <input type="text" id="secondaryEmail" name="secondaryEmail" class="secondaryEmail" placeholder="kcshorty@gmail.com">
            <br><br>

            <label for="previousEvent">Do you need to build a new net or copy the stations from an existing net?</label>

            <br>
            <span class="inline">
                <label class="radio-inline">Building</label>
                <input class="buildorcopy" type="radio" name="buildorcopy" id="build" value="build" checked>

                <label class="radio-inline">Copying</label>
                <input class="buildorcopy" type="radio" name="buildorcopy" id="copy" value="copy">
            </span>

            <br><br>

            <div id="theList" class="hidden">
                <label for="previousPreBuild">Select a Pre-Built Template to copy:</label><br>
                <select id="previousPreBuild" class="previousPreBuild" title="Select PB Group" name="previousPreBuild">
                    <option value="0" selected="selected">Select Group by Name</option>
                    <?php
                    $sql = "SELECT DISTINCT netID, activity, netcall
                            FROM NetLog
                            WHERE pb = 1
                            ORDER BY netID DESC";
                    $stmt = $db_found->prepare($sql);
                    $stmt->execute();
                    while ($net = $stmt->fetch()) {
                        echo "<option value='{$net['netID']}'> {$net['netID']} ({$net['netcall']}) {$net['activity']} </option>\n";
                    }
                    ?>
                </select>
            </div> <!-- End of id = theList -->

            <br><br>
            <input type="submit" value="Submit" name="submit">
        </form>

    </div> <!-- End class = body -->
</body>
</html>