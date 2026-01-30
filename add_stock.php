<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect data
    $stockID = $_POST['stockID'];
    $prodID = $_POST['prodID'];
    $branchID = $_POST['branchID'];
    $staffID = $_POST['staffID'];
    $qty = $_POST['stockQuantity'];

    // INSERT INTO STOCK TABLE
    $query = "INSERT INTO STOCK (STOCK_ID, PROD_ID, BRANCH_ID, STAFF_ID, STOCK_QUANTITY, STOCK_IN, STOCK_OUT) 
              VALUES (:sid, :pid, :bid, :stid, :qty, 0, 0)";
    
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":sid", $stockID);
    oci_bind_by_name($stid, ":pid", $prodID);
    oci_bind_by_name($stid, ":bid", $branchID);
    oci_bind_by_name($stid, ":stid", $staffID);
    oci_bind_by_name($stid, ":qty", $qty);

    if (oci_execute($stid)) {
        header("Location: stock_mgmt.php?status=success");
        exit();
    } else {
        $e = oci_error($stid);
        die("Error: " . $e['message']);
    }
}
?>