<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saleID = $_POST['saleID'];
    $saleDate = date('Y-m-d');
    $grandAmount = (float)$_POST['saleGrandAmount']; 
    $custID = (empty($_POST['custId']) || $_POST['custId'] == "WALK-IN") ? null : $_POST['custId'];
    $staffID = $_POST['staffId'];
    $branchID = $_SESSION['branch_id'];
    
    // --- ADDED: PROMO ID HANDLING ---
    // If no promo is selected, we pass null to the database
    $promoID = (empty($_POST['promoId'])) ? null : $_POST['promoId'];

    $prodIds = $_POST['prodId'];
    $qtys = $_POST['qty'];
    $subprices = $_POST['subprice']; 

    // --- STEP 1: INSERT SALE HEADER 
    $query1 = "INSERT INTO SALE (SALE_ID, SALE_DATE, SALE_GRANDAMOUNT, CUST_ID, STAFF_ID, PROMO_ID) 
               VALUES (:sid, TO_DATE(:sdate, 'YYYY-MM-DD'), :sgrand, :cid, :stid, :pid)";
    
    $stid1 = oci_parse($conn, $query1);
    oci_bind_by_name($stid1, ":sid", $saleID);
    oci_bind_by_name($stid1, ":sdate", $saleDate);
    oci_bind_by_name($stid1, ":sgrand", $grandAmount);
    oci_bind_by_name($stid1, ":cid", $custID);
    oci_bind_by_name($stid1, ":stid", $staffID);
    oci_bind_by_name($stid1, ":pid", $promoID); 

    if (!oci_execute($stid1, OCI_NO_AUTO_COMMIT)) {
        oci_rollback($conn);
        die("Error creating sale record.");
    }

    // [Step 2: Loop through items and Step 3: Loyalty points remain the same as your previous code]
    
    foreach ($prodIds as $index => $pid) {
        if (empty($pid)) continue;
        $qtySold = $qtys[$index];
        $itemSub = $subprices[$index];

        $queryItem = "INSERT INTO PRODUCT_SALE (PROD_ID, SALE_ID, PS_QUANTITY, PS_SUBPRICE) 
                      VALUES (:pid, :sid, :qty, :sub)";
        $stidItem = oci_parse($conn, $queryItem);
        oci_bind_by_name($stidItem, ":pid", $pid);
        oci_bind_by_name($stidItem, ":sid", $saleID);
        oci_bind_by_name($stidItem, ":qty", $qtySold);
        oci_bind_by_name($stidItem, ":sub", $itemSub);
        
        if (!oci_execute($stidItem, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($conn);
            die("Error processing item.");
        }

        $queryStock = "UPDATE STOCK SET STOCK_QUANTITY = STOCK_QUANTITY - :qty 
                       WHERE PROD_ID = :pid AND BRANCH_ID = :bid";
        $stidStock = oci_parse($conn, $queryStock);
        oci_bind_by_name($stidStock, ":qty", $qtySold);
        oci_bind_by_name($stidStock, ":pid", $pid);
        oci_bind_by_name($stidStock, ":bid", $branchID);

        if (!oci_execute($stidStock, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($conn);
            die("Error: Insufficient stock.");
        }
    }

    if ($custID) {
        $points = floor($grandAmount);
        $stid3 = oci_parse($conn, "UPDATE CUSTOMER SET CUST_LOYALTYPOINTS = COALESCE(CUST_LOYALTYPOINTS,0) + :p WHERE CUST_ID = :c");
        oci_bind_by_name($stid3, ":p", $points);
        oci_bind_by_name($stid3, ":c", $custID);
        oci_execute($stid3, OCI_NO_AUTO_COMMIT);
    }

    oci_commit($conn);
    header("Location: sales_mgmt.php?status=success");
    exit();
}