<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderID  = $_POST['orderID'];
    $prodID   = $_POST['prodID'];
    $branchID = $_POST['branchID'];
    $orderQty = $_POST['orderQty'];
    $orderAmt = $_POST['orderAmt'];
    $expDate  = $_POST['expDate'];
    $suppID   = $_POST['suppID'];
    $staffID  = $_POST['staffID'];
    $today    = date('Y-m-d');

    // 1. Insert the main Procurement record
    $q1 = "INSERT INTO SUPPLIER_ORDER (ORDER_ID, ORDER_DATE, ORDER_QUANTITY, EXPECTED_DELIVERY, ORDER_AMOUNT, SUPP_ID, STAFF_ID, PROD_ID) 
           VALUES (:oid, TO_DATE(:odate, 'YYYY-MM-DD'), :oqty, TO_DATE(:edate, 'YYYY-MM-DD'), :oamt, :sid, :stid, :pid)";

    $stid1 = oci_parse($conn, $q1);
    oci_bind_by_name($stid1, ":oid", $orderID);
    oci_bind_by_name($stid1, ":odate", $today);
    oci_bind_by_name($stid1, ":oqty", $orderQty);
    oci_bind_by_name($stid1, ":edate", $expDate);
    oci_bind_by_name($stid1, ":oamt", $orderAmt);
    oci_bind_by_name($stid1, ":sid", $suppID);
    oci_bind_by_name($stid1, ":stid", $staffID);
    oci_bind_by_name($stid1, ":pid", $prodID);

    $success1 = oci_execute($stid1, OCI_NO_AUTO_COMMIT);

    // 2. Link to STOCK (Most Correct Approach)
    if ($success1) {
        // Check if this product is already tracked at this branch
        $qCheck = "SELECT STOCK_ID FROM STOCK WHERE PROD_ID = :pid AND BRANCH_ID = :bid";
        $stidCheck = oci_parse($conn, $qCheck);
        oci_bind_by_name($stidCheck, ":pid", $prodID);
        oci_bind_by_name($stidCheck, ":bid", $branchID);
        oci_execute($stidCheck);
        $stockExists = oci_fetch_assoc($stidCheck);

        if ($stockExists) {
            // Simply update the "Incoming" stock column (assuming STOCK_IN exists in your table)
            $qStock = "UPDATE STOCK SET STOCK_IN = COALESCE(STOCK_IN, 0) + :oqty WHERE STOCK_ID = :sid";
            $stidStock = oci_parse($conn, $qStock);
            oci_bind_by_name($stidStock, ":oqty", $orderQty);
            oci_bind_by_name($stidStock, ":sid", $stockExists['STOCK_ID']);
        } else {
            // Create new stock record for this branch/product
            $newStockID = "STK-" . rand(10000, 99999);
            $qStock = "INSERT INTO STOCK (STOCK_ID, STOCK_QUANTITY, STOCK_IN, PROD_ID, BRANCH_ID, STAFF_ID) 
                       VALUES (:sid, 0, :oqty, :pid, :bid, :stf)";
            $stidStock = oci_parse($conn, $qStock);
            oci_bind_by_name($stidStock, ":sid", $newStockID);
            oci_bind_by_name($stidStock, ":oqty", $orderQty);
            oci_bind_by_name($stidStock, ":pid", $prodID);
            oci_bind_by_name($stidStock, ":bid", $branchID);
            oci_bind_by_name($stidStock, ":stf", $staffID);
        }
        oci_execute($stidStock, OCI_NO_AUTO_COMMIT);
    }

    // Commit both or nothing
    oci_commit($conn);
    header("Location: supplier_orders.php?status=success");
    exit();
}
?>