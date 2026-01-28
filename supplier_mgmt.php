<?php
// Include the Oracle database connection file
include 'db_connection.php';

// Fetch supplier data from the database
$query =    "SELECT SUPP_ID, SUPP_NAME, SUPP_PHONE, SUPP_COMPANY, SUPP_EMAIL, SUPP_ADDRESS
            FROM SUPPLIER";
    
$stid = oci_parse($conn, $query);
oci_execute($stid);

// Store the fetched data in an array
$suppliers = [];
while ($row = oci_fetch_assoc($stid)) {
    $suppliers[] = $row;
}

// Free the statement and close the connection
oci_free_statement($stid);
oci_close($conn);

// Include the sidebar navigation
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Management</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Open the Add Supplier modal
        function openAddSupplierModal() {
            document.getElementById("addSupplierModal").style.display = "block";
        }

        // Open the Edit Supplier modal and populate data
        function openEditSupplierModal(suppID, suppName, suppPhone, suppCompany, suppEmail, suppAddress) {
            document.getElementById("editSupplierModal").style.display = "block";
            document.getElementById("editSupp_ID").value = suppID;
            document.getElementById("editSupp_Name").value = suppName;
            document.getElementById("editSupp_Phone").value = suppPhone;
            document.getElementById("editSupp_Brand").value = suppBrand;
            document.getElementById("editSupp_Email").value = suppEmail;
            document.getElementById("editSupp_Address").value = suppAddress;
        }

        // Confirm Delete Supplier
        function confirmDelete(suppID) {
            const confirmation = confirm("Are you sure you want to delete this supplier?");
            if (confirmation) {
                // Redirect to delete_supplier.php page
                window.location.href = 'delete_supplier.php?supp_id=' + suppID;
            }
        }

        // Close the modal
        function closeModal() {
            document.getElementById("addSupplierModal").style.display = "none";
            document.getElementById("editSupplierModal").style.display = "none";
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <h1>Supplier Management</h1>
            <!-- Add Supplier Button -->
            <div class="button-container">
                <div class="addbutton">
                    <button class="add-button" onclick="openAddSupplierModal()">Add</button>
                </div>
            </div>
            <!-- Supplier Table -->
            <table id="supplierTable">
                <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
                <?php
                foreach ($suppliers as $supplier) {
                    echo "<tr>
                            <td>" . $supplier['SUPP_ID'] . "</td>
                            <td>" . $supplier['SUPP_NAME'] . "</td>
                            <td>" . $supplier['SUPP_PHONE'] . "</td>
                            <td>" . $supplier['SUPP_COMPANY'] . "</td>
                            <td>" . $supplier['SUPP_EMAIL'] . "</td>
                            <td>" . $supplier['SUPP_ADDRESS'] . "</td>
                            <td>
                                <button onclick=\"openEditSupplierModal('" . $supplier['SUPP_ID'] . "', '" . $supplier['SUPP_NAME'] . "', '" . $supplier['SUPP_PHONE'] . "', '" . $supplier['SUPP_COMPANY'] . "', '" . $supplier['SUPP_EMAIL'] . "', '" . $supplier['SUPP_ADDRESS'] . "')\" class='edit'>Edit</button>
                                <button onclick=\"confirmDelete('" . $supplier['SUPP_ID'] . "')\" class='delete'>Delete</button>
                            </td>
                        </tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Supplier</h2>
            <form action="add_supplier.php" method="post">
                <label for="suppName">Supplier Name</label>
                <input type="text" id="suppName" name="suppName" required>

                <label for="suppPhone">Supplier Phone</label>
                <input type="text" id="suppPhone" name="suppPhone" required>

                <label for="suppCompany">Company</label>
                <input type="text" id="suppCompany" name="suppCompany" required>

                <label for="suppEmail">Email</label>
                <input type="email" id="suppEmail" name="suppEmail" required>

                <label for="suppAddress">Address</label>
                <textarea id="suppAddress" name="suppAddress" required></textarea>

                <button type="submit" class="add-button">Add Supplier</button>
            </form>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="editSupplierModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Supplier</h2>
            <form action="edit_supplier.php" method="post">
                <input type="hidden" id="editSupp_ID" name="suppID">

                <label for="editSupp_Name">Supplier Name</label>
                <input type="text" id="editSupp_Name" name="suppName" required>

                <label for="editSupp_Phone">Phone</label>
                <input type="text" id="editSupp_Phone" name="suppPhone" required>

                <label for="editSupp_Company">Company</label>
                <input type="text" id="editSupp_Company" name="suppCompany" required>

                <label for="editSupp_Email">Email</label>
                <input type="email" id="editSupp_Email" name="suppEmail" required>

                <label for="editSupp_Address">Address</label>
                <textarea id="editSupp_Address" name="suppAddress" required></textarea>

                <button type="submit" class="add-button">Update Supplier</button>
            </form>
        </div>
    </div>

</body>
</html>
