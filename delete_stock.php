<?php
include 'db_connection.php';

if (isset($_GET['stock_id'])) {
    $stockID = $_GET['stock_id'];

    // DELETE STOCK QUERY
    $query = "DELETE FROM STOCK WHERE STOCK_ID = :stock_id";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":stock_id", $stockID);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: stock_mgmt.php');
        exit(); 
    } else {
        echo "Error deleting Stock details.";
    }

    oci_free_statement($stid);
    oci_close($conn);
} else {
    header('Location: stock_mgmt.php');
    exit();
}
?>
