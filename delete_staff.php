<?php
include 'db_connection.php';

if (isset($_GET['staff_id'])) {
    $staffID = $_GET['staff_id'];

    $query = "DELETE FROM STAFF WHERE staff_id = :staff_id";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":staff_id", $staffID);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: customer_mgmt.php');
        exit(); 
    } else {
        echo "Error deleting Staff details.";
    }

    oci_free_statement($stid);
    oci_close($conn);
} else {
    header('Location: customer_mgmt.php');
    exit();
}
?>
