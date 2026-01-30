<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $suppID      = $_POST['suppID']; 
    $suppName    = $_POST['suppName'];
    $suppPhone   = $_POST['suppPhone'];
    $suppBrand   = $_POST['suppBrand']; 
    $suppEmail   = $_POST['suppEmail'];
    $suppAddress = $_POST['suppAddress'];

    // SQL query to update the supplier details
    $query = "UPDATE SUPPLIER 
              SET SUPP_NAME = :name, 
                  SUPP_PHONE = :phone, 
                  SUPP_BRAND = :brand, 
                  SUPP_EMAIL = :email,
                  SUPP_ADDRESS = :addr
              WHERE SUPP_ID = :id";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":id", $suppID);
    oci_bind_by_name($stid, ":name", $suppName);
    oci_bind_by_name($stid, ":phone", $suppPhone);
    oci_bind_by_name($stid, ":brand", $suppBrand);
    oci_bind_by_name($stid, ":email", $suppEmail);
    oci_bind_by_name($stid, ":addr", $suppAddress);

    if (oci_execute($stid)) {
        header("Location: supplier_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error: " . $e['message'];
    }
    oci_close($conn);
}
?>