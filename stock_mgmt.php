<?php
include 'db_connection.php';

// Fetch all stock data
$query = "SELECT STOCK_ID, PROD_ID, BRANCH_ID, STAFF_ID, STOCK_QUANTITY, STOCK_IN, STOCK_OUT FROM STOCK ORDER BY STOCK_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$stocks = [];
$total_qty = 0;
$total_in = 0;
$total_out = 0;

while ($row = oci_fetch_assoc($stid)) {
    $stocks[] = $row;
    // Calculate totals for the cards
    $total_qty += $row['STOCK_QUANTITY'];
    $total_in += $row['STOCK_IN'];
    $total_out += $row['STOCK_OUT'];
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddStockModal() { 
            document.getElementById("addStockModal").style.display = "flex"; 
        }

        function openEditStockModal(id, prod, branch, staff, qty, s_in, s_out) {
            document.getElementById("editStockModal").style.display = "flex";
            document.getElementById("editStock_ID").value = id;
            document.getElementById("editProd_ID").value = prod;
            document.getElementById("editBranch_ID").value = branch;
            document.getElementById("editStaff_ID").value = staff;
            document.getElementById("editStock_Quantity").value = qty;
            document.getElementById("editStock_In").value = s_in;
            document.getElementById("editStock_Out").value = s_out;
        }

        function closeModal() {
            document.getElementById("addStockModal").style.display = "none";
            document.getElementById("editStockModal").style.display = "none";
        }

        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }

        function confirmDelete(stockID) {
            if (confirm("Permanently delete stock record " + stockID + "?")) {
                window.location.href = 'delete_stock.php?stock_id=' + stockID;
            }
        }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h1>Stock Management</h1>
        </div>
        <button class="btn-add" onclick="openAddStockModal()">+ Add New Stock</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Items in Stock</h3>
            <span class="stat-number"><?= $total_qty; ?></span>
        </div>
        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Total Stock In</h3>
            <span class="stat-number" style="color: #4CAF50;"><?= $total_in; ?></span>
        </div>
        <div class="stat-card" style="border-left-color: #f44336;">
            <h3>Total Stock Out</h3>
            <span class="stat-number" style="color: #f44336;"><?= $total_out; ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Product ID</th>
                    <th>Branch ID</th>
                    <th>Staff ID</th>
                    <th>Quantity</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stocks as $stockItem) : ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $stockItem['STOCK_ID']; ?></td>
                    <td><?= $stockItem['PROD_ID']; ?></td>
                    <td><?= $stockItem['BRANCH_ID']; ?></td>
                    <td><?= $stockItem['STAFF_ID']; ?></td>
                    <td style="font-weight:600;"><?= $stockItem['STOCK_QUANTITY']; ?></td>
                    <td style="color:#4CAF50;">+ <?= $stockItem['STOCK_IN']; ?></td>
                    <td style="color:#f44336;">- <?= $stockItem['STOCK_OUT']; ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditStockModal(
                                '<?= $stockItem['STOCK_ID']; ?>',
                                '<?= $stockItem['PROD_ID']; ?>',
                                '<?= $stockItem['BRANCH_ID']; ?>',
                                '<?= $stockItem['STAFF_ID']; ?>',
                                '<?= $stockItem['STOCK_QUANTITY']; ?>',
                                '<?= $stockItem['STOCK_IN']; ?>',
                                '<?= $stockItem['STOCK_OUT']; ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $stockItem['STOCK_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addStockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add Stock Entry</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_stock.php" method="post">
                <label>Product ID</label>
                <input type="text" name="prodID" placeholder="e.g. P101" required>

                <label>Branch ID</label>
                <input type="text" name="branchID" placeholder="e.g. B101" required>

                <label>Staff ID</label>
                <input type="text" name="staffID" placeholder="e.g. S101" required>

                <label>Initial Quantity</label>
                <input type="number" name="stockQuantity" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Stock In</label>
                        <input type="number" name="stockIn" value="0">
                    </div>
                    <div style="flex: 1;">
                        <label>Stock Out</label>
                        <input type="number" name="stockOut" value="0">
                    </div>
                </div>

                <button type="submit" class="btn-add" style="width: 100%; margin-top: 10px;">Save Stock</button>
            </form>
        </div>
    </div>
</div>

<div id="editStockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Stock Record</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_stock.php" method="post">
                <input type="hidden" id="editStock_ID" name="stockID">
                
                <label>Product ID</label>
                <input type="text" id="editProd_ID" name="prodID" required>

                <label>Branch ID</label>
                <input type="text" id="editBranch_ID" name="branchID" required>

                <label>Staff ID</label>
                <input type="text" id="editStaff_ID" name="staffID" required>

                <label>Quantity</label>
                <input type="number" id="editStock_Quantity" name="stockQuantity" required>

                <label>Stock In</label>
                <input type="number" id="editStock_In" name="stockIn">

                <label>Stock Out</label>
                <input type="number" id="editStock_Out" name="stockOut">

                <button type="submit" class="btn-edit" style="width: 100%; margin-top: 10px;">Update Record</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>