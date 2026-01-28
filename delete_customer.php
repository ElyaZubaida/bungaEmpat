<?php
include 'db_connection.php';

if (isset($_GET['cust_id'])) {
    $custID = $_GET['cust_id'];

    $query = "DELETE FROM CUSTOMER WHERE cust_id = :custID";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":custID", $custID);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: customer_mgmt.php');
        exit(); 
    } else {
        echo "Error deleting customer details.";
    }

    oci_free_statement($stid);
    oci_close($conn);
} else {
    header('Location: customer_mgmt.php');
    exit();
}
?>
