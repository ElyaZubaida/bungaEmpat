<?php
// Include the Oracle database connection file
include 'db_connection.php';

// Fetch supplier data from the database
$query = "SELECT SUPP_ID, SUPP_NAME, SUPP_PHONE, SUPP_BRAND, SUPP_EMAIL, SUPP_ADDRESS FROM SUPPLIER ORDER BY SUPP_ID ASC";
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
    <title>Supplier Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddSupplierModal() {
            document.getElementById("addSupplierModal").style.display = "flex";
        }

        function openEditSupplierModal(suppID, suppName, suppPhone, suppCompany, suppEmail, suppAddress) {
            document.getElementById("editSupplierModal").style.display = "flex";
            document.getElementById("editSupp_ID").value = suppID;
            document.getElementById("editSupp_Name").value = suppName;
            document.getElementById("editSupp_Phone").value = suppPhone;
            document.getElementById("editSupp_Company").value = suppCompany;
            document.getElementById("editSupp_Email").value = suppEmail;
            document.getElementById("editSupp_Address").value = suppAddress;
        }

        function confirmDelete(suppID) {
            if (confirm("Are you sure you want to delete this supplier?")) {
                window.location.href = 'delete_supplier.php?supp_id=' + suppID;
            }
        }

        function closeModal() {
            document.getElementById("addSupplierModal").style.display = "none";
            document.getElementById("editSupplierModal").style.display = "none";
        }

        // Close modal if user clicks outside of the box
        window.onclick = function(event) {
            if (event.target.className === 'modal') closeModal();
        }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h1>Supplier Management</h1>
        </div>
        <button class="btn-add" onclick="openAddSupplierModal()">+ Add Supplier</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Suppliers</h3>
            <span class="stat-number"><?= count($suppliers); ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Contact Info</th>
                    <th>Company/Brand</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $supplier['SUPP_ID']; ?></td>
                    <td style="font-weight:600;"><?= $supplier['SUPP_NAME']; ?></td>
                    <td>
                        <small style="display:block;"><?= $supplier['SUPP_EMAIL']; ?></small>
                        <small style="display:block; color:#666;"><?= $supplier['SUPP_PHONE']; ?></small>
                    </td>
                    <td>
                        <span style="background:#f0f0f0; padding:4px 8px; border-radius:4px; font-size:0.85em;">
                            <?= $supplier['SUPP_BRAND']; ?>
                        </span>
                    </td>
                    <td style="max-width: 200px; font-size: 0.85em; color: #555;"><?= $supplier['SUPP_ADDRESS']; ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditSupplierModal(
                                '<?= $supplier['SUPP_ID']; ?>',
                                '<?= addslashes($supplier['SUPP_NAME']); ?>',
                                '<?= $supplier['SUPP_PHONE']; ?>',
                                '<?= addslashes($supplier['SUPP_BRAND']); ?>',
                                '<?= $supplier['SUPP_EMAIL']; ?>',
                                '<?= addslashes($supplier['SUPP_ADDRESS']); ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $supplier['SUPP_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addSupplierModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Supplier</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_supplier.php" method="post">
                <label>Supplier Name</label>
                <input type="text" name="suppName" required placeholder="Contact Person Name">

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Phone Number</label>
                        <input type="text" name="suppPhone" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Email Address</label>
                        <input type="email" name="suppEmail" required>
                    </div>
                </div>

                <label>Company / Brand Name</label>
                <input type="text" name="suppCompany" required placeholder="Company Name">

                <label>Office Address</label>
                <textarea name="suppAddress" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px;"></textarea>

                <button type="submit" class="btn-add" style="width:100%">Register Supplier</button>
            </form>
        </div>
    </div>
</div>

<div id="editSupplierModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Supplier Details</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_supplier.php" method="post">
                <input type="hidden" id="editSupp_ID" name="suppID">

                <label>Supplier Name</label>
                <input type="text" id="editSupp_Name" name="suppName" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Phone</label>
                        <input type="text" id="editSupp_Phone" name="suppPhone" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Email</label>
                        <input type="email" id="editSupp_Email" name="suppEmail" required>
                    </div>
                </div>

                <label>Company</label>
                <input type="text" id="editSupp_Company" name="suppCompany" required>

                <label>Address</label>
                <textarea id="editSupp_Address" name="suppAddress" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px;"></textarea>

                <button type="submit" class="btn-edit" style="width:100%">Update Supplier</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>