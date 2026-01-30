<?php
session_start(); // Must start session to get current staff
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Collect data
    $stockID       = $_POST['stockID']; 
    $prodID        = $_POST['prodID'];
    $branchID      = $_POST['branchID'];
    $stockQuantity = $_POST['stockQuantity'];
    $stockIn       = $_POST['stockIn'] ?? 0;
    $stockOut      = $_POST['stockOut'] ?? 0;

    // Use Session ID instead of POST ID for better security
    $staffID = $_SESSION['staff_id']; 

    // 2. Prepare the Update Query
    // We update the STAFF_ID to the person currently logged in
    $query = "UPDATE STOCK 
              SET PROD_ID = :prod_id, 
                  BRANCH_ID = :branch_id, 
                  STAFF_ID = :staff_id,
                  STOCK_QUANTITY = :quantity,
                  STOCK_IN = :stock_in,
                  STOCK_OUT = :stock_out
              WHERE STOCK_ID = :stock_id";

    $stid = oci_parse($conn, $query);

    // 3. Bind the variables
    oci_bind_by_name($stid, ":stock_id", $stockID);
    oci_bind_by_name($stid, ":prod_id", $prodID);
    oci_bind_by_name($stid, ":branch_id", $branchID);
    oci_bind_by_name($stid, ":staff_id", $staffID);
    oci_bind_by_name($stid, ":quantity", $stockQuantity);
    oci_bind_by_name($stid, ":stock_in", $stockIn);
    oci_bind_by_name($stid, ":stock_out", $stockOut);

    // 4. Execute
    $result = oci_execute($stid);

    if ($result) {
        header("Location: stock_mgmt.php?status=success");
        exit(); 
    } else {
        $e = oci_error($stid);
        echo "Error updating stock: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>