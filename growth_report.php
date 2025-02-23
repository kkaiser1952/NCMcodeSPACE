<?php
// growth_report.php
// Written: 2024-07-30
// Last Modified: 2024-08-01

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "dbConnectDtls.php";
$sql_file = 'sql/growth_report.sql';
$sql = file_get_contents($sql_file);

try {
    $stmt = $db_found->prepare($sql);
    $stmt->execute();
    
    do {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } while ($stmt->nextRowset());

    if (count($results) > 0) {
        echo "<h2>xNCM -- Net Growth Report</h2>";
        echo "<h3>Entries are last/current year</h3>";
        echo "<table border='1'>";
        echo "<tr>";
        foreach ($results[0] as $key => $value) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        foreach ($results as $row) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                $style = '';
                $cellValue = $value ?? ''; // Use empty string if value is null

                if ($key !== 'grouped_netcall' && $key !== 'grouped_activity' && $key !== 'Past %' && $key !== 'YTD %') {
                    $parts = explode('/', $cellValue);
                    if (count($parts) == 2) {
                        $prev = intval($parts[0]);
                        $curr = intval($parts[1]);
                        if ($curr > $prev) {
                            $style = ' style="background-color: lightgreen;"';
                        }
                    }
                } elseif ($key === 'Past %' && $cellValue !== '') {
                    // Format Past % to always have two decimal places
                    $cellValue = number_format((float)$cellValue, 2) . '%';
                }
                // YTD % is already formatted correctly, so we don't need to modify it

                echo "<td$style>" . htmlspecialchars($cellValue) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No results found.</p>";
    }
} catch (PDOException $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCM -- Net Growth Report</title>
<style>
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #f5f5f5;
    }
</style>

</head>
<body>
    <h1>NCM -- Net Growth Report</h1>
    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php elseif (!empty($results)): ?>
        <table>
            <tr>
                <th>Net</th>
                <th>Activity</th>
                <th>Past %</th>
                <th>Jan</th>
                <th>Feb</th>
                <th>Mar</th>
                <th>Apr</th>
                <th>May</th>
                <th>Jun</th>
                <th>Jul</th>
                <th>Aug</th>
                <th>Sep</th>
                <th>Oct</th>
                <th>Nov</th>
                <th>Dec</th>
                <th>YTD %</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['grouped_netcall']); ?></td>
                <td><?php echo htmlspecialchars($row['grouped_activity']); ?></td>
                <td><?php echo htmlspecialchars($row['Past %']); ?></td>
                <td><?php echo htmlspecialchars($row['Jan']); ?></td>
                <td><?php echo htmlspecialchars($row['Feb']); ?></td>
                <td><?php echo htmlspecialchars($row['Mar']); ?></td>
                <td><?php echo htmlspecialchars($row['Apr']); ?></td>
                <td><?php echo htmlspecialchars($row['May']); ?></td>
                <td><?php echo htmlspecialchars($row['Jun']); ?></td>
                <td><?php echo htmlspecialchars($row['Jul']); ?></td>
                <td><?php echo htmlspecialchars($row['Aug']); ?></td>
                <td><?php echo htmlspecialchars($row['Sep']); ?></td>
                <td><?php echo htmlspecialchars($row['Oct']); ?></td>
                <td><?php echo htmlspecialchars($row['Nov']); ?></td>
                <td><?php echo htmlspecialchars($row['Dec']); ?></td>
                <td><?php echo htmlspecialchars($row['YTD %']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No data available.</p>
    <?php endif; ?>
</body>
</html>