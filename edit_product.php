<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $prodID             = $_POST['prodID']; // Hidden input dalam modal/form
    $prodName           = $_POST['prodName'];
    $prodListPrice          = $_POST['prodListPrice'];
    $prodCategory          = $_POST['prodCategory'];
   
    $query = "UPDATE PRODUCT 
              SET PROD_NAME = :prodName, 
                  PROD_LISTPRICE = :prodListPrice, 
                  PROD_CATEGORY = :prodCategory 
                WHERE PROD_ID = :prodID";

    $stid = oci_parse($conn, $query);

    oci_bind_by_name($stid, ":prodID", $prodID);
    oci_bind_by_name($stid, ":prodName", $prodName);
    oci_bind_by_name($stid, ":prodListPrice", $prodListPrice);
    oci_bind_by_name($stid, ":prodCategory", $prodCategory);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: product_mgmt.php');
        exit(); 
    } else {
        $e = oci_error($stid);
        echo "Error updating product: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>