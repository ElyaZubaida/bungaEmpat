<?php
// Dummy data for suppliers
$suppliers = [
    ['Supp_ID' => 1, 'Supp_Name' => 'ABC Supplier', 'Supp_Phone' => '1234567890', 'Supp_Company' => 'ABC Corp', 'Supp_Email' => 'abc@company.com', 'Supp_Address' => '123 Street, City'],
    ['Supp_ID' => 2, 'Supp_Name' => 'XYZ Supplier', 'Supp_Phone' => '0987654321', 'Supp_Company' => 'XYZ Ltd', 'Supp_Email' => 'xyz@company.com', 'Supp_Address' => '456 Avenue, City'],
];

include 'sidebar.php'; // Include sidebar navigation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            document.getElementById("editSupp_Company").value = suppCompany;
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
                            <td>" . $supplier['Supp_ID'] . "</td>
                            <td>" . $supplier['Supp_Name'] . "</td>
                            <td>" . $supplier['Supp_Phone'] . "</td>
                            <td>" . $supplier['Supp_Company'] . "</td>
                            <td>" . $supplier['Supp_Email'] . "</td>
                            <td>" . $supplier['Supp_Address'] . "</td>
                            <td>
                                <button onclick=\"openEditSupplierModal('" . $supplier['Supp_ID'] . "', '" . $supplier['Supp_Name'] . "', '" . $supplier['Supp_Phone'] . "', '" . $supplier['Supp_Company'] . "', '" . $supplier['Supp_Email'] . "', '" . $supplier['Supp_Address'] . "')\" class='edit'>Edit</button>
                                <button onclick=\"confirmDelete('" . $supplier['Supp_ID'] . "')\" class='delete'>Delete</button>
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
