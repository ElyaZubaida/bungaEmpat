<?php
include 'db_connection.php';

if (isset($_GET['prod_id'])) {
    $prodID = $_GET['prod_id'];

    $query = "DELETE FROM PRODUCT WHERE prod_id = :prodID";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":prodID", $prodID);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: product_mgmt.php');
        exit(); 
    } else {
        echo "Error deleting product.";
    }

    oci_free_statement($stid);
    oci_close($conn);
} else {
    header('Location: product_mgmt.php');
    exit();
}
?>
