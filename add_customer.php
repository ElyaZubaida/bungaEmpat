<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. AUTO-INCREMENT LOGIC ---
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(CUST_ID, 3))) AS MAX_VAL FROM CUSTOMER WHERE CUST_ID LIKE 'C-%'";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    
    $next_num = ($id_row['MAX_VAL']) ? $id_row['MAX_VAL'] + 1 : 3001;
    $newCustID = "C-" . $next_num; 
    oci_free_statement($id_stid);

    // --- 2. AMBIL DATA DARI FORM ---
    $custName  = $_POST['custName'];
    $custEmail = $_POST['custEmail'];
    $custPhone = $_POST['custPhone'];

    // INSERT NEW CUSTOMER 
    // Note: We use '0' and 'SYSDATE' directly in the VALUES clause
    $query = "INSERT INTO CUSTOMER 
              (CUST_ID, CUST_NAME, CUST_EMAIL, CUST_PHONE, CUST_LOYALTYPOINTS, CUST_DATEREGISTERED)
              VALUES (:custID, :custName, :custEmail, :custPhone, 0, SYSDATE)";
    
    $stid = oci_parse($conn, $query);
    
    oci_bind_by_name($stid, ":custID", $newCustID);
    oci_bind_by_name($stid, ":custName", $custName);
    oci_bind_by_name($stid, ":custEmail", $custEmail);
    oci_bind_by_name($stid, ":custPhone", $custPhone);

    $result = oci_execute($stid);
    
    if ($result) {
        header("Location: customer_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error adding customer: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>