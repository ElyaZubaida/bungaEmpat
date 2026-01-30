<?php
include 'db_connection.php';

if (isset($_GET['branch_id'])) {
    $branchID = $_GET['branch_id'];

    // DELETE BRANCH QUERY
    $query = "DELETE FROM BRANCH WHERE BRANCH_ID = :branchID";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":branchID", $branchID);

    $result = oci_execute($stid);

    if ($result) {
        header('Location: branch_mgmt.php');
        exit(); 
    } else {
        echo "Error deleting branch.";
    }

    oci_free_statement($stid);
    oci_close($conn);
} else {
    header('Location: branch_mgmt.php');
    exit();
}
?>
