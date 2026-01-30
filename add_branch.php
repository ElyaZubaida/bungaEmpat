<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. LOGIC AUTO-INCREMENT (Cari ID Terbesar & Susun ASC) ---
    // Kita ambil nombor dari ID (contoh: B101 -> 101) dan cari yang paling besar
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(BRANCH_ID, 2))) AS MAX_ID FROM BRANCH";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    
    // Jika table kosong, mula dengan 101. Jika ada, tambah 1.
    $next_num = ($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 101;
    $newBranchID = "B" . $next_num; 
    oci_free_statement($id_stid);

    // --- 2. AMBIL DATA DARI FORM ---
    // (branchID tidak diambil dari $_POST sebab kita dah jana automatik di atas)
    $branchName = $_POST['branchName'];
    $branchLocation = $_POST['branchLocation'];
    $branchPhone = $_POST['branchPhone'];
    $branchEmail = $_POST['branchEmail'];

    // --- INSERT NEW BRANCH ---
    $query = "INSERT INTO BRANCH (BRANCH_ID, BRANCH_NAME, BRANCH_ADDRESS, BRANCH_PHONE, BRANCH_EMAIL)
              VALUES (:branchID, :branchName, :branchLocation, :branchPhone, :branchEmail)";
    
    $stid = oci_parse($conn, $query);
    
    oci_bind_by_name($stid, ":branchID", $newBranchID);
    oci_bind_by_name($stid, ":branchName", $branchName);
    oci_bind_by_name($stid, ":branchLocation", $branchLocation);
    oci_bind_by_name($stid, ":branchPhone", $branchPhone);
    oci_bind_by_name($stid, ":branchEmail", $branchEmail);

    $result = oci_execute($stid);
    
    if ($result) {
        // Balik ke page management. Paparan di sana akan tersusun jika guna ORDER BY.
        header("Location: branch_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error adding branch: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>