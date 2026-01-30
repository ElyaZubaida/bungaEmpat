<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recalculate ID (Server-side safety)
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(SUPP_ID, 2))) AS MAX_VAL FROM SUPPLIER";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    $next_num = ($id_row['MAX_VAL']) ? $id_row['MAX_VAL'] + 1 : 1;
    $newSuppID = "S" . str_pad($next_num, 3, "0", STR_PAD_LEFT);

    // 2. Collect Data - Corrected to use 'suppBrand'
    $suppName    = $_POST['suppName'];
    $suppPhone   = $_POST['suppPhone'];
    $suppBrand   = $_POST['suppBrand']; 
    $suppEmail   = $_POST['suppEmail'];
    $suppAddress = $_POST['suppAddress']; 

    // INSERT NEW SUPPLIER
    $query = "INSERT INTO SUPPLIER (SUPP_ID, SUPP_NAME, SUPP_PHONE, SUPP_BRAND, SUPP_EMAIL, SUPP_ADDRESS)
              VALUES (:id, :name, :phone, :brand, :email, :addr)";
    
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":id", $newSuppID);
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