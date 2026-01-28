<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. AUTO-INCREMENT LOGIC for STOCK_ID ---
    // Extract numeric part after 'K' (Assuming format K11001 or similar)
    // Adjust SUBSTR(STOCK_ID, 2) if your prefix is longer than one letter.
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(STOCK_ID, 2))) AS MAX_ID FROM STOCK";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    
    // If table is empty, start at 10001. Otherwise, increment.
    $next_num = ($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 10001;
    $newStockID = "K" . $next_num; 
    oci_free_statement($id_stid);

    // --- 2. COLLECT DATA FROM FORM ---
    $prodID        = $_POST['prodID'];
    $branchID      = $_POST['branchID'];
    $staffID       = $_POST['staffID'];
    $stockQuantity = $_POST['stockQuantity'];
    $stockIn       = $_POST['stockIn'];
    $stockOut      = $_POST['stockOut'];

    // --- 3. INSERT PROCESS ---
    $query = "INSERT INTO STOCK (
                STOCK_ID, PROD_ID, BRANCH_ID, STAFF_ID, 
                STOCK_QUANTITY, STOCK_IN, STOCK_OUT
              ) VALUES (
                :stockID, :prodID, :branchID, :staffID, 
                :quantity, :stockIn, :stockOut
              )";
    
    $stid = oci_parse($conn, $query);
    
    // Bind variables
    oci_bind_by_name($stid, ":stockID", $newStockID);
    oci_bind_by_name($stid, ":prodID", $prodID);
    oci_bind_by_name($stid, ":branchID", $branchID);
    oci_bind_by_name($stid, ":staffID", $staffID);
    oci_bind_by_name($stid, ":quantity", $stockQuantity);
    oci_bind_by_name($stid, ":stockIn", $stockIn);
    oci_bind_by_name($stid, ":stockOut", $stockOut);

    $result = oci_execute($stid);
    
    if ($result) {
        // Redirect back to stock management page
        header("Location: stock_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error adding stock: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>