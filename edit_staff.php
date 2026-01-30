<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Collect data from the Edit Staff Modal form
    // Updated keys to match the 'name' attributes in your Modal HTML
    $staffID       = $_POST['staffID'];
    $staffName     = $_POST['staffName'];
    $staffUsername = $_POST['staffUser'];
    $staffPassword = $_POST['staffPass'];
    $staffPhone     = $_POST['staffPhone'];
    $staffEmail     = $_POST['staffEmail'];
    $staffCategory = $_POST['staffCat'];
    $staffSalary   = $_POST['staffSalary'];
    $branchID      = $_POST['branchID'];
    
    // Handle Manager ID: If "None" (empty string) is selected, set to null for Oracle
    $managerID     = !empty($_POST['managerID']) ? $_POST['managerID'] : null;

    // 2. Prepare the Update Query for the STAFF table
    $query = "UPDATE STAFF 
              SET STAFF_NAME = :name, 
                  STAFF_USERNAME = :username, 
                  STAFF_PASSWORD = :password,
                  STAFF_PHONE = :phone,
                  STAFF_EMAIL = :email,
                  STAFF_CATEGORY = :category,
                  STAFF_SALARY = :salary,
                  BRANCH_ID = :branch_id,
                  MANAGER_ID = :manager_id
              WHERE STAFF_ID = :staff_id";

    $stid = oci_parse($conn, $query);

    // 3. Bind the staff variables to the query placeholders
    oci_bind_by_name($stid, ":staff_id", $staffID);
    oci_bind_by_name($stid, ":name", $staffName);
    oci_bind_by_name($stid, ":username", $staffUsername);
    oci_bind_by_name($stid, ":password", $staffPassword);
    oci_bind_by_name($stid, ":phone", $staffPhone);
    oci_bind_by_name($stid, ":email", $staffEmail);
    oci_bind_by_name($stid, ":category", $staffCategory);
    oci_bind_by_name($stid, ":salary", $staffSalary);
    oci_bind_by_name($stid, ":branch_id", $branchID);
    oci_bind_by_name($stid, ":manager_id", $managerID);

    // 4. Execute the statement
    $result = oci_execute($stid);

    if ($result) {
        // Redirect back to the staff management page on success
        header("Location: staff_mgmt.php");
        exit(); 
    } else {
        $e = oci_error($stid);
        echo "Error updating staff: " . htmlentities($e['message']);
    }

    // 5. Clean up resources
    oci_free_statement($stid);
    oci_close($conn);
}
?>