<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. LOGIC AUTO-INCREMENT (Cari ID Terbesar & Susun ASC) ---
    // Kita ambil nombor dari ID (contoh: B101 -> 101) dan cari yang paling besar
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(PROMO_ID, 2))) AS MAX_ID FROM PROMOTION";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    
    // Jika table kosong, mula dengan 001. Jika ada, tambah 1.
    $next_num = ($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 001;
    $formatted_num = str_pad($next_num, 3, "0", pad_type: STR_PAD_LEFT);
    $newPromoID = "P" . $formatted_num; 
    oci_free_statement($id_stid);

    // --- 2. AMBIL DATA DARI FORM ---
    // (branchID tidak diambil dari $_POST sebab kita dah jana automatik di atas)
    $promoName = $_POST['promoName'];
    $promoDesc = $_POST['promoDesc'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $promoAmount = $_POST['promoAmount'];

    // --- 3. PROSES INSERT ---
    $query = "INSERT INTO PROMOTION (PROMO_ID, PROMO_NAME, PROMO_DESC, PROMO_STARTDATE, PROMO_ENDDATE, PROMO_AMOUNT)
              VALUES (:promoID, :promoName, :promoDesc, TO_DATE(:startDate, 'DD/MM/YYYY'), TO_DATE(:endDate, 'DD/MM/YYYY'), :promoAmount)";
    
    $stid = oci_parse($conn, $query);
    
    // Bind ID yang kita jana tadi ($newBranchID)
    oci_bind_by_name($stid, ":promoID", $newPromoID);
    oci_bind_by_name($stid, ":promoName", $promoName);
    oci_bind_by_name($stid, ":promoDesc", $promoDesc);
    oci_bind_by_name($stid, ":startDate", $startDate);
    oci_bind_by_name($stid, ":endDate", $endDate);
    oci_bind_by_name($stid, ":promoAmount", $promoAmount);

    $result = oci_execute($stid);
    
    if ($result) {
        // Balik ke page management. Paparan di sana akan tersusun jika guna ORDER BY.
        header("Location: promotion_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error adding promotion: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>