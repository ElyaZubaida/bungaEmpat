<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated values from the form
    $branchID = $_POST['branchID'];
    $branchName = $_POST['branchName'];
    $branchLocation = $_POST['branchLocation'];
    $branchPhone = $_POST['branchPhone'];
    $branchEmail = $_POST['branchEmail'];

    // Prepare the SQL query to update the branch details
    $query = "UPDATE BRANCH 
              SET BRANCH_NAME = :branchName, 
                  BRANCH_ADDRESS = :branchLocation, 
                  BRANCH_PHONE = :branchPhone, 
                  BRANCH_EMAIL = :branchEmail
              WHERE BRANCH_ID = :branchID";

    // Prepare and execute the statement
    $stid = oci_parse($conn, $query);

    oci_bind_by_name($stid, ":branchID", $branchID);
    oci_bind_by_name($stid, ":branchName", $branchName);
    oci_bind_by_name($stid, ":branchLocation", $branchLocation);
    oci_bind_by_name($stid, ":branchPhone", $branchPhone);
    oci_bind_by_name($stid, ":branchEmail", $branchEmail);

    // Execute the query and check if it was successful
    $result = oci_execute($stid);

    // If the update was successful, redirect back to the branch management page
    if ($result) {
        // Redirect to the branch management page
        header('Location: branch_mgmt.php');
        exit(); // Ensure the script stops after redirection
    } else {
        // If the query fails, show an error message
        echo "Error updating branch.";
    }

    // Free the statement and close the connection
    oci_free_statement($stid);
    oci_close($conn);
}
?>
