<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branchID = $_POST['branchID']; 
    $branchName = $_POST['branchName'];
    $branchLocation = $_POST['branchLocation'];
    $branchPhone = $_POST['branchPhone'];
    $branchEmail = $_POST['branchEmail'];

    $query = "INSERT INTO BRANCH (BRANCH_ID, BRANCH_NAME, BRANCH_ADDRESS, BRANCH_PHONE, BRANCH_EMAIL)
              VALUES (:branchID, :branchName, :branchLocation, :branchPhone, :branchEmail)";
    
    $stid = oci_parse($conn, $query);
    
    oci_bind_by_name($stid, ":branchID", $branchID);
    oci_bind_by_name($stid, ":branchName", $branchName);
    oci_bind_by_name($stid, ":branchLocation", $branchLocation);
    oci_bind_by_name($stid, ":branchPhone", $branchPhone);
    oci_bind_by_name($stid, ":branchEmail", $branchEmail);

    $result = oci_execute($stid);
    
    if ($result) {
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
