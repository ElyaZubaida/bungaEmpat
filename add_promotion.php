<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recalculate ID
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(PROMO_ID, 2))) AS MAX_ID FROM PROMOTION";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    $next_num = ($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 1;
    $newPromoID = "P" . str_pad($next_num, 3, "0", STR_PAD_LEFT);

    // INSERT NEW PROMOTION
    $query = "INSERT INTO PROMOTION (PROMO_ID, PROMO_NAME, PROMO_DESC, PROMO_STARTDATE, PROMO_ENDDATE, PROMO_AMOUNT)
              VALUES (:id, :name, :descr, TO_DATE(:startD, 'YYYY-MM-DD'), TO_DATE(:endD, 'YYYY-MM-DD'), :amount)";
    
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":id", $newPromoID);
    oci_bind_by_name($stid, ":name", $_POST['promoName']);
    oci_bind_by_name($stid, ":descr", $_POST['promoDesc']);
    oci_bind_by_name($stid, ":startD", $_POST['startDate']);
    oci_bind_by_name($stid, ":endD", $_POST['endDate']);
    oci_bind_by_name($stid, ":amount", $_POST['promoAmount']);

    if (oci_execute($stid)) {
        header("Location: promotion_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error: " . $e['message'];
    }
    oci_close($conn);
}
?>