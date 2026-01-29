<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. LOGIK AUTO-INCREMENT (Format C-3146) ---
    // Kita ambil bahagian nombor selepas 'C-' (bermula karakter ke-3)
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(SUPP_ID, 2))) AS MAX_VAL FROM SUPPLIER";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    
    $latest_num = $id_row['MAX_VAL'];

    // Jika table kosong, mula dengan 3001. Jika ada, tambah 1.
    $next_num = ($latest_num) ? $latest_num + 1 : 001;
    $formatted_num = str_pad($next_num, 3, "0", pad_type: STR_PAD_LEFT);

    // Cantumkan balik jadi C-3001, C-3002, dan seterusnya
    $newSuppID = "S" . $formatted_num; 
    oci_free_statement($id_stid);

    // --- 2. AMBIL DATA DARI FORM ---
    $suppName           = $_POST['suppName'];
    $suppPhone          = $_POST['suppPhone'];
    $suppCompany        = $_POST['suppCompany'];
    $suppEmail          = $_POST['suppEmail'];
    $suppAddress        = $_POST['suppAddress']; 
    

    // --- 3. PROSES INSERT ---
    $query = "INSERT INTO SUPPLIER (SUPP_ID, SUPP_NAME, SUPP_PHONE, SUPP_COMPANY, SUPP_EMAIL, SUPP_ADDRESS)
              VALUES (:suppID, :suppName, :suppPhone, :suppCompany, :suppEmail, :suppAddress)";
    
    $stid = oci_parse($conn, $query);
    
    oci_bind_by_name($stid, ":suppID", $newSuppID);
    oci_bind_by_name($stid, ":suppName", $suppName);
    oci_bind_by_name($stid, ":suppPhone", $suppPhone);
    oci_bind_by_name($stid, ":suppCompany", $suppCompany);
    oci_bind_by_name($stid, ":suppEmail", $suppEmail);
    oci_bind_by_name($stid, ":suppAddress", $suppAddress);

    $result = oci_execute($stid);
    
    if ($result) {
        header("Location: supplier_mgmt.php");
        exit();
    } else {    
        $e = oci_error($stid);
        echo "Error adding customer: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>