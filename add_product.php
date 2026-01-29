<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tentukan Prefix & Start Position berdasarkan Kategori
    $prodCategory = $_POST['prodCategory']; // 'Food' atau 'Non-Food'
    
    if ($prodCategory === 'Food') {
        $prefix = "F-";
        $startPos = 3; // 'F-' adalah 2 karakter, nombor mula posisi 3
    } else {
        $prefix = "NF-";
        $startPos = 4; // 'NF-' adalah 3 karakter, nombor mula posisi 4
    }

    // 2. LOGIK AUTO-INCREMENT DINAMIK
    // Cari MAX ID spesifik untuk prefix yang dipilih sahaja
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(PROD_ID, :startPos))) AS MAX_VAL 
                 FROM PRODUCT WHERE PROD_ID LIKE :prefixPattern";
    
    $id_stid = oci_parse($conn, $id_query);
    $pattern = $prefix . '%';
    oci_bind_by_name($id_stid, ":startPos", $startPos);
    oci_bind_by_name($id_stid, ":prefixPattern", $pattern);
    oci_execute($id_stid);
    
    $id_row = oci_fetch_assoc($id_stid);
    $latest_num = $id_row['MAX_VAL'];

    // Mula dari 1001 (atau apa-apa nombor anda mahu)
    $next_num = ($latest_num) ? $latest_num + 1 : 1001;
    $newProdID = $prefix . $next_num; 
    oci_free_statement($id_stid);

    // 3. AMBIL DATA UMUM
    $prodName      = $_POST['prodName'];
    $prodListPrice = $_POST['prodListPrice'];
    $prodNetPrice  = $_POST['prodNetPrice'];
    $prodBrand     = $_POST['prodBrand']; 
    $suppID        = $_POST['suppID'];

    // 4. INSERT KE TABLE PRODUCT
    $query1 = "INSERT INTO PRODUCT (PROD_ID, PROD_NAME, PROD_LISTPRICE, PROD_NETPRICE, PROD_BRAND, PROD_CATEGORY, SUPP_ID)
               VALUES (:pid, :pname, :plp, :pnp, :pbrand, :pcat, :sid)";
    
    $stid1 = oci_parse($conn, $query1);
    oci_bind_by_name($stid1, ":pid", $newProdID);
    oci_bind_by_name($stid1, ":pname", $prodName);
    oci_bind_by_name($stid1, ":plp", $prodListPrice);
    oci_bind_by_name($stid1, ":pnp", $prodNetPrice);
    oci_bind_by_name($stid1, ":pbrand", $prodBrand);
    oci_bind_by_name($stid1, ":pcat", $prodCategory);
    oci_bind_by_name($stid1, ":sid", $suppID);

    $result1 = oci_execute($stid1, OCI_NO_AUTO_COMMIT);

    if ($result1) {
        // 5. INSERT KE SUBTYPE
        if ($prodCategory === 'Food') {
            $foodCat = $_POST['foodType'];
            $expiry  = $_POST['expiryDate'];
            $storage = $_POST['storageInstructions'];

            $query2 = "INSERT INTO FOOD_PRODUCT (PROD_ID, FOOD_CATEGORY, EXPIRY_DATE, STORAGE_INSTRUCTIONS)
                       VALUES (:pid, :fcat, TO_DATE(:edate, 'YYYY-MM-DD'), :storage)";
            $stid2 = oci_parse($conn, $query2);
            oci_bind_by_name($stid2, ":fcat", $foodCat);
            oci_bind_by_name($stid2, ":edate", $expiry);
            oci_bind_by_name($stid2, ":storage", $storage);
        } else {
            $nfCat = $_POST['nonFoodCategory'];
            $query2 = "INSERT INTO NONFOOD_PRODUCT (PROD_ID, NONFOOD_CATEGORY)
                       VALUES (:pid, :nfcat)";
            $stid2 = oci_parse($conn, $query2);
            oci_bind_by_name($stid2, ":nfcat", $nfCat);
        }
        
        oci_bind_by_name($stid2, ":pid", $newProdID);
        $result2 = oci_execute($stid2, OCI_NO_AUTO_COMMIT);

        if ($result2) {
            oci_commit($conn);
            header("Location: product_mgmt.php");
            exit();
        } else {
            oci_rollback($conn);
            $e = oci_error($stid2);
            die("Error Subtype: " . $e['message']);
        }
    } else {
        $e = oci_error($stid1);
        die("Error Product: " . $e['message']);
    }

    oci_free_statement($stid1);
    if (isset($stid2)) oci_free_statement($stid2);
    oci_close($conn);
}
?>