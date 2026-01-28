<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. AUTO-INCREMENT LOGIC for STAFF_ID ---
    // Extracting numeric part after 'ST' (assuming format ST11001)
    $id_query = "SELECT MAX(TO_NUMBER(SUBSTR(STAFF_ID, 3))) AS MAX_ID FROM STAFF";
    $id_stid = oci_parse($conn, $id_query);
    oci_execute($id_stid);
    $id_row = oci_fetch_assoc($id_stid);
    
    // If table is empty, start at 1001. Otherwise, increment.
    $next_num = ($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 1001;
    $newStaffID = "ST" . $next_num; 
    oci_free_statement($id_stid);

    // --- 2. COLLECT DATA FROM FORM ---
    $name      = $_POST['name'];
    $username  = $_POST['username'];
    $password  = $_POST['password']; // Note: In production, use password_hash()
    $phone     = $_POST['phone'];
    $email     = $_POST['email'];
    $category  = $_POST['category'];
    $salary    = $_POST['salary'];
    $branch_id = $_POST['branch_id'];
    $manager_id = $_POST['manager_id'];

    // --- 3. INSERT PROCESS ---
    $query = "INSERT INTO STAFF (
                STAFF_ID, STAFF_NAME, STAFF_USERNAME, STAFF_PASSWORD, 
                STAFF_PHONE, STAFF_EMAIL, STAFF_CATEGORY, STAFF_SALARY, 
                BRANCH_ID, MANAGER_ID
              ) VALUES (
                :staffID, :name, :username, :password, 
                :phone, :email, :category, :salary, 
                :branchID, :managerID
              )";
    
    $stid = oci_parse($conn, $query);
    
    // Bind variables
    oci_bind_by_name($stid, ":staffID", $newStaffID);
    oci_bind_by_name($stid, ":name", $name);
    oci_bind_by_name($stid, ":username", $username);
    oci_bind_by_name($stid, ":password", $password);
    oci_bind_by_name($stid, ":phone", $phone);
    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":category", $category);
    oci_bind_by_name($stid, ":salary", $salary);
    oci_bind_by_name($stid, ":branchID", $branch_id);
    oci_bind_by_name($stid, ":managerID", $manager_id);

    $result = oci_execute($stid);
    
    if ($result) {
        // Redirect back to staff management page
        header("Location: staff_mgmt.php");
        exit();
    } else {
        $e = oci_error($stid);
        echo "Error adding staff: " . htmlentities($e['message']);
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>