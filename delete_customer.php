<?php
session_start();
include 'db_connection.php';

if (isset($_GET['cust_id'])) {
    $custID = $_GET['cust_id'];

    // STEP 1: Find all Sale IDs for this customer to delete their product details
    // This prevents "orphaned" records in PRODUCT_SALE
    $findSales = "SELECT SALE_ID FROM SALE WHERE CUST_ID = :id";
    $stidSales = oci_parse($conn, $findSales);
    oci_bind_by_name($stidSales, ":id", $custID);
    oci_execute($stidSales);

    while ($row = oci_fetch_assoc($stidSales)) {
        $sid = $row['SALE_ID'];
        $delPS = oci_parse($conn, "DELETE FROM PRODUCT_SALE WHERE SALE_ID = :sid");
        oci_bind_by_name($delPS, ":sid", $sid);
        oci_execute($delPS, OCI_NO_AUTO_COMMIT);
    }

    // STEP 2: Delete from SALE table
    $delSale = oci_parse($conn, "DELETE FROM SALE WHERE CUST_ID = :id");
    oci_bind_by_name($delSale, ":id", $custID);
    oci_execute($delSale, OCI_NO_AUTO_COMMIT);

    // STEP 3: Finally delete the CUSTOMER
    $delCust = oci_parse($conn, "DELETE FROM CUSTOMER WHERE CUST_ID = :id");
    oci_bind_by_name($delCust, ":id", $custID);
    
    $result = oci_execute($delCust, OCI_NO_AUTO_COMMIT);

    if ($result) {
        oci_commit($conn);
        header('Location: customer_mgmt.php?status=success');
        exit(); 
    } else {
        oci_rollback($conn);
        echo "Error: Could not delete customer. They might be linked to active records.";
    }

    oci_close($conn);
} else {
    header('Location: customer_mgmt.php');
    exit();
}
?>