<?php
include 'db_connection.php';

if (isset($_GET['supp_id'])) {
    $suppID = $_GET['supp_id'];

    $query = "DELETE FROM SUPPLIER WHERE SUPP_ID = :supp_id";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":supp_id", $suppID);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: supplier_mgmt.php');
        exit(); 
    } else {
        echo "Error deleting Stock details.";
    }

    oci_free_statement($stid);
    oci_close($conn);
} else {
    header('Location: supplier_mgmt.php');
    exit();
}
?>
