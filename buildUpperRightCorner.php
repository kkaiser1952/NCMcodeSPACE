<?php

// buildUpperRightCorner.php
// V2 Updated: 2024-05-14

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";

$call = trim($_GET["call"]); //$call = 'TE0ST';

// Check if the database connection is successful
//if (!$db_found) {
    //die("Connection failed: " . $db_found->errorInfo()[2]);
//}

// Get only one record for a return
$sql = "SELECT row1, row2, row3, row4, row5, row6, id
        FROM `NetKind`
        WHERE `call` LIKE :call
        ORDER BY `call` LIMIT 1";

$stmt = $db_found->prepare($sql);
$stmt->bindValue(':call', "%$call%", PDO::PARAM_STR);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $num_rows = 1;

    // Use a loop to process the row data
    for ($i = 1; $i <= 6; $i++) {
        $rcols = explode(",", $row["row$i"]);
        for ($j = 1; $j <= 5; $j++) {
            ${"r{$i}c{$j}"} = $rcols[$j - 1] ?? '';
        }
    }

    echo "<table id='ourfreqs'>";
    echo "<tr>
            <th class='edit_r1c1 r1c1 nobg'>$r1c1</th>
            <th class='edit_r1c2 r1c2 nobg'>$r1c2</th>
            <th class='edit_r1c3 r1c3 nobg'>$r1c3</th>
            <th class='edit_r1c4 r1c4 nobg'>$r1c4</th>
          </tr>";
    echo "<tr>
            <td class='edit_r2c1 r2c1 nobg1'>$r2c1</td>
            <td class='edit_r2c2 r2c2 nobg2' id='r2c2:{$row['id']}'>$r2c2</td>
            <td class='edit_r2c3 r2c3 nobg2'>$r2c3</td>
            <td class='edit_r2c4 r2c4 nobg2'>$r2c4</td>
          </tr>";
    echo "<tr>
            <td class='edit_r3c1 r3c1 nobg1'>$r3c1</td>
            <td class='edit_r3c2 r3c2 nobg2'>$r3c2</td>
            <td class='edit_r3c3 r3c3 nobg2'>$r3c3</td>
            <td class='edit_r3c4 r3c4 nobg2'>$r3c4</td>
          </tr>";
    echo "<tr>
            <td class='edit_r4c1 r4c1 nobg1'>$r4c1</td>
            <td class='edit_r4c2 r4c2 nobg' nowrap>$r4c2</td>
            <td class='edit_r4c3 r4c3 nobg'>$r4c3</td>
            <td class='edit_r4c4 r4c4 nobg'>$r4c4</td>
          </tr>";
    echo "<tr>
            <td class='edit_r5c1 r5c1 nobg1'>$r5c1</td>
            <td class='edit_r5c2 r5c2 nobg2'>$r5c2</td>
            <td class='edit_r5c3 r5c3 nobg2' nowrap>$r5c3</td>
            <td class='edit_r5c4 r5c4 nobg2'>$r5c4</td>
          </tr>";

    if ($call !== 'DMR') {
        echo "<tr>
                <td class='edit_r6c1 r6c1 nobg1'>$r6c1</td>
                <td class='edit_r6c2 r6c2 nobg2' colspan='3'>
                  <a href='$r6c2' target='_blank'>$r6c2</a><br>
                  <a href='$r6c3' target='_blank'>$r6c3</a>
                </td>
              </tr>";
    } elseif ($call === 'DMR') {
        echo "<tr>
                <td class='edit_r6c1 r6c1 nobg1'>$r6c1</td>
                <td class='edit_r6c2 r6c2 nobg2'>$r6c2</td>
                <td class='edit_r6c3 r6c3 nobg2'>$r6c3</td>
                <td class='edit_r6c4 r6c4 nobg2'>$r6c4</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No records found.";
}
?>