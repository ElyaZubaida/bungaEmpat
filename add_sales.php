<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saleID = $_POST['saleID'];
    $saleDate = date('Y-m-d');
    $saleAmount = (float)$_POST['saleAmount']; 
    $grandAmount = (float)$_POST['saleGrandAmount']; 
    $paymentType = $_POST['salePaymentType'];
    $custID = (empty($_POST['custId']) || $_POST['custId'] == "WALK-IN") ? null : $_POST['custId'];
    $staffID = $_POST['staffId'];
    $promoID = (empty($_POST['promoId'])) ? null : $_POST['promoId'];

    $prodIds = $_POST['prodId'];
    $qtys = $_POST['qty'];

    // 1. INSERT INTO SALE HEADER
    $query1 = "INSERT INTO SALE (SALE_ID, SALE_DATE, SALE_AMOUNT, SALE_GRANDAMOUNT, SALE_PAYMENTTYPE, CUST_ID, STAFF_ID, PROMO_ID) 
               VALUES (:sid, TO_DATE(:sdate, 'YYYY-MM-DD'), :samt, :sgrand, :pay, :cid, :stid, :pid)";
    
    $stid1 = oci_parse($conn, $query1);
    oci_bind_by_name($stid1, ":sid", $saleID);
    oci_bind_by_name($stid1, ":sdate", $saleDate);
    oci_bind_by_name($stid1, ":samt", $saleAmount);
    oci_bind_by_name($stid1, ":sgrand", $grandAmount);
    oci_bind_by_name($stid1, ":pay", $paymentType);
    oci_bind_by_name($stid1, ":cid", $custID);
    oci_bind_by_name($stid1, ":stid", $staffID);
    oci_bind_by_name($stid1, ":pid", $promoID);

    if (!oci_execute($stid1, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stid1);
        die("Critical Error (Sale Table): " . $e['message']);
    }

    // 2. INSERT INTO PRODUCT_SALE (LOOP)
    $query2 = "INSERT INTO PRODUCT_SALE (PROD_ID, SALE_ID, PS_QUANTITY, PS_SUBPRICE) 
               VALUES (:pid, :sid, :qty, :sub)";
    $stid2 = oci_parse($conn, $query2);

    foreach ($prodIds as $index => $pid) {
        if (empty($pid)) continue;
        
        $currentQty = $qtys[$index];
        // Note: For multi-item, we calculate subprice per row
        // In this POS logic, we send the raw saleAmount per row for reporting
        oci_bind_by_name($stid2, ":pid", $pid);
        oci_bind_by_name($stid2, ":sid", $saleID);
        oci_bind_by_name($stid2, ":qty", $currentQty);
        oci_bind_by_name($stid2, ":sub", $saleAmount); 

        if (!oci_execute($stid2, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($conn);
            $e = oci_error($stid2);
            die("Critical Error (Item Table): " . $e['message']);
        }
    }

    // 3. AUTO-UPDATE LOYALTY POINTS (1 RM = 1 Point)
    if ($custID) {
        $pointsToAdd = floor($grandAmount); 
        $query3 = "UPDATE CUSTOMER 
                   SET CUST_LOYALTYPOINTS = COALESCE(CUST_LOYALTYPOINTS, 0) + :pts 
                   WHERE CUST_ID = :cid";
        
        $stid3 = oci_parse($conn, $query3);
        oci_bind_by_name($stid3, ":pts", $pointsToAdd);
        oci_bind_by_name($stid3, ":cid", $custID);
        oci_execute($stid3, OCI_NO_AUTO_COMMIT);
    }

    // Final Commit
    oci_commit($conn);
    header("Location: sales_mgmt.php?status=success");
    exit();
}
?>