<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. LOGIC AUTO-INCREMENT (Cari ID Terbesar & Susun ASC) ---
    // Kita ambil nombor dari ID (contoh: B101 -> 101) dan cari yang paling besar
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(SALE_ID, 2))) AS MAX_ID FROM SALE";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    
    // Jika table kosong, mula dengan 101. Jika ada, tambah 1.
    $next_num = ($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 11001;
    $newSaleID = "S" . $next_num; 
    oci_free_statement($id_stid);

    // --- 2. AMBIL DATA DARI FORM ---
    // (branchID tidak diambil dari $_POST sebab kita dah jana automatik di atas)
    $saleDate = $_POST['saleDate'];
    $saleAmount = $_POST['saleAmount'];
    $saleGrandAmount = $_POST['saleGrandAmount'];
    $salePaymentType = $_POST['salePaymentType'];
    $saleCustID = $_POST['custId'];
    $saleStaffID = $_POST['staffId'];
    $salePromoID = $_POST['promoId'];

    // --- 3. PROSES INSERT ---
    $query = "INSERT INTO SALE (SALE_ID, SALE_DATE, SALE_AMOUNT, SALE_GRANDAMOUNT, SALE_PAYMENTTYPE, CUST_ID, STAFF_ID, PROMO_ID)
              VALUES (:saleID, TO_DATE(:saleDate, 'YYYY-MM-DD'), :saleAmount, :saleGrandAmount, :salePaymentType, :custId, :staffId, :promoId)";
    
    $stid = oci_parse($conn, $query);
    
    // Bind ID yang kita jana tadi ($newSaleID)
    oci_bind_by_name($stid, ":saleID", $newSaleID);
    oci_bind_by_name($stid, ":saleDate", $saleDate);
    oci_bind_by_name($stid, ":saleAmount", $saleAmount);
    oci_bind_by_name($stid, ":saleGrandAmount", $saleGrandAmount);
    oci_bind_by_name($stid, ":salePaymentType", $salePaymentType);
    oci_bind_by_name($stid, ":custId", $saleCustID);
    oci_bind_by_name($stid, ":staffId", $saleStaffID);
    oci_bind_by_name($stid, ":promoId", $salePromoID);

    $result = oci_execute($stid);
    
    if ($result) {
        // Balik ke page management. Paparan di sana akan tersusun jika guna ORDER BY.
        header("Location: sales_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error adding promotion: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>