<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Collect data from the Edit Supplier Modal form
    // These names match the 'name' attributes in your HTML <input> tags
    $suppID      = $_POST['suppID']; 
    $suppName    = $_POST['suppName'];
    $suppPhone   = $_POST['suppPhone'];
    $suppCompany = $_POST['suppCompany'];
    $suppEmail   = $_POST['suppEmail'];
    $suppAddress = $_POST['suppAddress'];

    // 2. Prepare the Update Query for the SUPPLIER table
    $query = "UPDATE SUPPLIER 
              SET SUPP_NAME = :supp_name, 
                  SUPP_PHONE = :supp_phone, 
                  SUPP_COMPANY = :supp_company,
                  SUPP_EMAIL = :supp_email,
                  SUPP_ADDRESS = :supp_address
              WHERE SUPP_ID = :supp_id";

    $stid = oci_parse($conn, $query);

    // 3. Bind the variables to the query placeholders
    oci_bind_by_name($stid, ":supp_id", $suppID);
    oci_bind_by_name($stid, ":supp_name", $suppName);
    oci_bind_by_name($stid, ":supp_phone", $suppPhone);
    oci_bind_by_name($stid, ":supp_company", $suppCompany);
    oci_bind_by_name($stid, ":supp_email", $suppEmail);
    oci_bind_by_name($stid, ":supp_address", $suppAddress);

    // 4. Execute the statement
    $result = oci_execute($stid);

    if ($result) {
        // Redirect back to the supplier management page on success
        header("Location: supplier_mgmt.php");
        exit(); 
    } else {
        $e = oci_error($stid);
        echo "Error updating supplier: " . htmlentities($e['message']);
    }

    // 5. Clean up resources
    oci_free_statement($stid);
    oci_close($conn);
}
?>