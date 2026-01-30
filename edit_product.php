<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $prodID        = $_POST['prodID']; 
    $prodName      = $_POST['prodName'];
    $prodListPrice = $_POST['prodListPrice'];
    $prodCategory  = $_POST['prodCategory']; // 'Food' or 'Non-Food'

    // --- 1. UPDATE MAIN PRODUCT TABLE ---
    $query1 = "UPDATE PRODUCT 
               SET PROD_NAME = :prodName, 
                   PROD_LISTPRICE = :prodListPrice 
               WHERE PROD_ID = :prodID";

    $stid1 = oci_parse($conn, $query1);
    oci_bind_by_name($stid1, ":prodID", $prodID);
    oci_bind_by_name($stid1, ":prodName", $prodName);
    oci_bind_by_name($stid1, ":prodListPrice", $prodListPrice);

    // Execute with NO_AUTO_COMMIT to start transaction
    $result1 = oci_execute($stid1, OCI_NO_AUTO_COMMIT);

    if ($result1) {
        $result2 = true; // Default to true for logic check

        // --- 2. UPDATE SUBTYPE DETAILS (Optional but Recommended) ---
        if ($prodCategory === 'Food' && isset($_POST['expiryDate'])) {
            $query2 = "UPDATE FOOD_PRODUCT 
                       SET FOOD_CATEGORY = :fcat, 
                           EXPIRY_DATE = TO_DATE(:edate, 'YYYY-MM-DD'),
                           STORAGE_INSTRUCTIONS = :storage
                       WHERE PROD_ID = :pid";
            $stid2 = oci_parse($conn, $query2);
            oci_bind_by_name($stid2, ":pid", $prodID);
            oci_bind_by_name($stid2, ":fcat", $_POST['foodType']);
            oci_bind_by_name($stid2, ":edate", $_POST['expiryDate']);
            oci_bind_by_name($stid2, ":storage", $_POST['storageInstructions']);
            $result2 = oci_execute($stid2, OCI_NO_AUTO_COMMIT);
        } 
        elseif ($prodCategory === 'Non-Food' && isset($_POST['nonFoodCategory'])) {
            $query2 = "UPDATE NONFOOD_PRODUCT 
                       SET NONFOOD_CATEGORY = :nfcat 
                       WHERE PROD_ID = :pid";
            $stid2 = oci_parse($conn, $query2);
            oci_bind_by_name($stid2, ":pid", $prodID);
            oci_bind_by_name($stid2, ":nfcat", $_POST['nonFoodCategory']);
            $result2 = oci_execute($stid2, OCI_NO_AUTO_COMMIT);
        }

        if ($result2) {
            oci_commit($conn);
            header('Location: product_mgmt.php');
            exit(); 
        } else {
            oci_rollback($conn);
            $e = oci_error($stid2);
            echo "Error updating subtype: " . htmlentities($e['message']);
        }
    } else {
        $e = oci_error($stid1);
        echo "Error updating product: " . htmlentities($e['message']);
    }

    oci_free_statement($stid1);
    if(isset($stid2)) oci_free_statement($stid2);
    oci_close($conn);
}
?>