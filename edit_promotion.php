<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $promoID      = $_POST['promoID']; // Hidden input
    $promoName    = $_POST['promoName'];
    $promoDesc    = $_POST['promoDesc'];
    $startDate    = $_POST['startDate'];
    $endDate      = $_POST['endDate'];
    $promoAmount  = $_POST['promoAmount'];

    // 1. Updated query to include all fields and remove the comma error
    $query = "UPDATE PROMOTION 
              SET PROMO_NAME = :promoName, 
                  PROMO_DESC = :promoDesc, 
                  PROMO_STARTDATE = TO_DATE(SUBSTR(:startDate, 1, 10), 'YYYY-MM-DD'),
                  PROMO_ENDDATE = TO_DATE(SUBSTR(:endDate, 1, 10), 'YYYY-MM-DD'),
                  PROMO_AMOUNT = :promoAmount
              WHERE PROMO_ID = :promoID";

    $stid = oci_parse($conn, $query);

    // 2. Updated bind variables to match the Promo variables above
    oci_bind_by_name($stid, ":promoID", $promoID);
    oci_bind_by_name($stid, ":promoName", $promoName);
    oci_bind_by_name($stid, ":promoDesc", $promoDesc);
    oci_bind_by_name($stid, ":startDate", $startDate);
    oci_bind_by_name($stid, ":endDate", $endDate);
    oci_bind_by_name($stid, ":promoAmount", $promoAmount);

    $result = oci_execute($stid);

    if ($result) {
        // Redirecting back to the promotion management page
        header('Location: promotion_mgmt.php');
        exit(); 
    } else {
        $e = oci_error($stid);
        echo "Error updating promotion: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>