<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $custID             = $_POST['custID']; // Hidden input dalam modal/form
    $custName           = $_POST['custName'];
    $custEmail          = $_POST['custEmail'];
    $custPhone          = $_POST['custPhone'];
    $custLoyaltyPoints  = $_POST['custLoyaltyPoints'];
    $custDateRegistered = $_POST['custDateRegistered']; // Format: YYYY-MM-DD

    $query = "UPDATE CUSTOMER 
              SET CUST_NAME = :custName, 
                  CUST_EMAIL = :custEmail, 
                  CUST_PHONE = :custPhone, 
                  CUST_LOYALTYPOINTS = :custLoyaltyPoints,
                  CUST_DATEREGISTERED = TO_DATE(:custDateRegistered, 'YYYY-MM-DD')
              WHERE CUST_ID = :custID";

    $stid = oci_parse($conn, $query);

    oci_bind_by_name($stid, ":custID", $custID);
    oci_bind_by_name($stid, ":custName", $custName);
    oci_bind_by_name($stid, ":custEmail", $custEmail);
    oci_bind_by_name($stid, ":custPhone", $custPhone);
    oci_bind_by_name($stid, ":custLoyaltyPoints", $custLoyaltyPoints);
    oci_bind_by_name($stid, ":custDateRegistered", $custDateRegistered);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: customer_mgmt.php');
        exit(); 
    } else {
        $e = oci_error($stid);
        echo "Error updating customer: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>