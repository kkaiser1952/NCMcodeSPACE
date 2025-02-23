<?php
//date_default_timezone_set("America/Chicago");
	require_once "dbConnectDtls.php";
	// Check if there's any data in the NetLog table
$check_query = "SELECT COUNT(*) as count FROM `netcontrolcp_ncm`.`NetLog`";
$stmt = $db_found->query($check_query);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total records in NetLog table: " . $result['count'] . "<br>";

// Check data for specific netcalls
$netcall_query = "SELECT netcall, COUNT(*) as count
                  FROM `netcontrolcp_ncm`.`NetLog`
                  WHERE netcall IN ('W0KCN', 'PCARG', 'NR0AD', 'NARES', 'KCNARES')
                  GROUP BY netcall";
$stmt = $db_found->query($netcall_query);
$netcall_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Records for specific netcalls:<br>";
foreach ($netcall_results as $row) {
    echo $row['netcall'] . ": " . $row['count'] . "<br>";
}
?>