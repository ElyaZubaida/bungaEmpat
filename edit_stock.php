<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Collect data from the Edit Stock Modal form
    // Note: 'stockID', 'prodID', etc., must match the 'name' attribute in your HTML form
    $stockID       = $_POST['stockID']; 
    $prodID        = $_POST['prodID'];
    $branchID      = $_POST['branchID'];
    $staffID       = $_POST['staffID'];
    $stockQuantity = $_POST['stockQuantity'];
    $stockIn       = $_POST['stockIn'];
    $stockOut      = $_POST['stockOut'];

    // 2. Prepare the Update Query for the STOCK table
    $query = "UPDATE STOCK 
              SET PROD_ID = :prod_id, 
                  BRANCH_ID = :branch_id, 
                  STAFF_ID = :staff_id,
                  STOCK_QUANTITY = :quantity,
                  STOCK_IN = :stock_in,
                  STOCK_OUT = :stock_out
              WHERE STOCK_ID = :stock_id";

    $stid = oci_parse($conn, $query);

    // 3. Bind the variables to the query placeholders
    oci_bind_by_name($stid, ":stock_id", $stockID);
    oci_bind_by_name($stid, ":prod_id", $prodID);
    oci_bind_by_name($stid, ":branch_id", $branchID);
    oci_bind_by_name($stid, ":staff_id", $staffID);
    oci_bind_by_name($stid, ":quantity", $stockQuantity);
    oci_bind_by_name($stid, ":stock_in", $stockIn);
    oci_bind_by_name($stid, ":stock_out", $stockOut);

    // 4. Execute the statement
    $result = oci_execute($stid);

    if ($result) {
        // Redirect back to the stock management page on success
        header("Location: stock_mgmt.php");
        exit(); 
    } else {
        $e = oci_error($stid);
        echo "Error updating stock: " . htmlentities($e['message']);
    }

    // 5. Clean up resources
    oci_free_statement($stid);
    oci_close($conn);
}
?>