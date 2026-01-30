<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = "UPDATE PROMOTION 
              SET PROMO_NAME = :name, 
                  PROMO_DESC = :descr, 
                  PROMO_STARTDATE = TO_DATE(:startD, 'YYYY-MM-DD'),
                  PROMO_ENDDATE = TO_DATE(:endD, 'YYYY-MM-DD'),
                  PROMO_AMOUNT = :amount
              WHERE PROMO_ID = :id";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":id", $_POST['promoID']);
    oci_bind_by_name($stid, ":name", $_POST['promoName']);
    oci_bind_by_name($stid, ":descr", $_POST['promoDesc']);
    oci_bind_by_name($stid, ":startD", $_POST['startDate']);
    oci_bind_by_name($stid, ":endD", $_POST['endDate']);
    oci_bind_by_name($stid, ":amount", $_POST['promoAmount']);

    if (oci_execute($stid)) {
        header('Location: promotion_mgmt.php');
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error: " . $e['message'];
    }
    oci_close($conn);
}
?>