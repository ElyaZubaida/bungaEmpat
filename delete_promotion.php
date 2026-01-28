<?php
include 'db_connection.php';

if (isset($_GET['promo_id'])) {
    $prodID = $_GET['promo_id'];

    $query = "DELETE FROM PROMOTION WHERE PROMO_ID = :promoID";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":promoID", $prodID);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: promotion_mgmt.php');
        exit(); 
    } else {
        echo "Error deleting promotion.";
    }

    oci_free_statement($stid);
    oci_close($conn);
} else {
    header('Location: promotion_mgmt.php');
    exit();
}
?>
