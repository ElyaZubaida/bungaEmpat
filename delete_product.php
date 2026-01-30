<?php
session_start();
include 'db_connection.php';

if (isset($_GET['prod_id'])) {
    $prodID = $_GET['prod_id'];

    // 1. Delete from child tables first to avoid FK constraints
    // We use a manual transaction to ensure all or nothing is deleted
    $queries = [
        "DELETE FROM FOOD_PRODUCT WHERE PROD_ID = :id",
        "DELETE FROM STOCK WHERE PROD_ID = :id",
        "DELETE FROM PRODUCT_SALE WHERE PROD_ID = :id",
        "DELETE FROM PRODUCT WHERE PROD_ID = :id"
    ];

    $error = false;
    foreach ($queries as $sql) {
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":id", $prodID);
        if (!oci_execute($stid, OCI_NO_AUTO_COMMIT)) {
            $error = true;
            break;
        }
    }

    if (!$error) {
        oci_commit($conn);
        header('Location: product_mgmt.php?status=deleted');
        exit();
    } else {
        oci_rollback($conn);
        $e = oci_error($stid);
        echo "Error: " . $e['message'];
    }

    oci_close($conn);
} else {
    header('Location: product_mgmt.php');
    exit();
}
?>