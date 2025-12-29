<?php
include 'db_connection.php';

$query = "
    SELECT s.STOCK_ID, s.PROD_ID, s.BRANCH_ID, s.STAFF_ID, s.STOCK_QUANTITY, s.STOCK_IN, s.STOCK_OUT
    FROM STOCK s";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$stocks = [];
while ($row = oci_fetch_assoc($stid)) {
    $stocks[] = $row;
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Management</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Open the Add Stock modal
        function openAddStockModal() {
            document.getElementById("addStockModal").style.display = "block";
        }

        // Open the Edit Stock modal and populate data
        function openEditStockModal(stockID, prodID, branchID, staffID, quantity, stockIn, stockOut) {
            document.getElementById("editStockModal").style.display = "block";
            document.getElementById("editStock_ID").value = stockID;
            document.getElementById("editProd_ID").value = prodID;
            document.getElementById("editBranch_ID").value = branchID;
            document.getElementById("editStaff_ID").value = staffID;
            document.getElementById("editStock_Quantity").value = quantity;
            document.getElementById("editStock_In").value = stockIn;
            document.getElementById("editStock_Out").value = stockOut;
        }

        // Confirm Delete Stock
        function confirmDelete(stockID) {
            const confirmation = confirm("Are you sure you want to delete this stock entry?");
            if (confirmation) {
                // Redirect to the delete_stock.php page
                window.location.href = 'delete_stock.php?stock_id=' + stockID;
            }
        }

        // Close the modal
        function closeModal() {
            document.getElementById("addStockModal").style.display = "none";
            document.getElementById("editStockModal").style.display = "none";
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <h1>Stock Management</h1>
            <div class="button-container">
                <div class="addbutton">
                    <button class="add-button" onclick="openAddStockModal()">Add</button>
                </div>
            </div>
            <table id="stockTable">
                <tr>
                    <th>Stock ID</th>
                    <th>Product ID</th>
                    <th>Branch ID</th>
                    <th>Staff ID</th>
                    <th>Stock Quantity</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($stocks as $stockItem) : ?>
                    <tr>
                        <td><?= $stockItem['STOCK_ID']; ?></td>
                        <td><?= $stockItem['PROD_ID']; ?></td>
                        <td><?= $stockItem['BRANCH_ID']; ?></td>
                        <td><?= $stockItem['STAFF_ID']; ?></td>
                        <td><?= $stockItem['STOCK_QUANTITY']; ?></td>
                        <td><?= $stockItem['STOCK_IN']; ?></td>
                        <td><?= $stockItem['STOCK_OUT']; ?></td>
                        <td>
                            <button onclick="openEditStockModal(
                                '<?= $stockItem['STOCK_ID']; ?>',
                                '<?= $stockItem['PROD_ID']; ?>',
                                '<?= $stockItem['BRANCH_ID']; ?>',
                                '<?= $stockItem['STAFF_ID']; ?>',
                                '<?= $stockItem['STOCK_QUANTITY']; ?>',
                                '<?= $stockItem['STOCK_IN']; ?>',
                                '<?= $stockItem['STOCK_OUT']; ?>'
                            )" class="edit">Edit</button>
                            <button onclick="confirmDelete('<?= $stockItem['STOCK_ID']; ?>')" class="delete">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Add Stock Modal -->
    <div id="addStockModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Stock</h2>
            <form action="add_stock.php" method="post">
                <label for="prodID">Product ID</label>
                <input type="text" id="prodID" name="prodID" required>

                <label for="branchID">Branch ID</label>
                <input type="text" id="branchID" name="branchID" required>

                <label for="staffID">Staff ID</label>
                <input type="text" id="staffID" name="staffID" required>

                <label for="stockQuantity">Stock Quantity</label>
                <input type="number" id="stockQuantity" name="stockQuantity" required>

                <label for="stockIn">Stock In</label>
                <input type="number" id="stockIn" name="stockIn" required>

                <label for="stockOut">Stock Out</label>
                <input type="number" id="stockOut" name="stockOut" required>

                <button type="submit" class="add-button">Add Stock</button>
            </form>
        </div>
    </div>

    <!-- Edit Stock Modal -->
    <div id="editStockModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Stock</h2>
            <form action="edit_stock.php" method="post">
                <input type="hidden" id="editStock_ID" name="stockID">

                <label for="editProd_ID">Product ID</label>
                <input type="text" id="editProd_ID" name="prodID" required>

                <label for="editBranch_ID">Branch ID</label>
                <input type="text" id="editBranch_ID" name="branchID" required>

                <label for="editStaff_ID">Staff ID</label>
                <input type="text" id="editStaff_ID" name="staffID" required>

                <label for="editStock_Quantity">Stock Quantity</label>
                <input type="number" id="editStock_Quantity" name="stockQuantity" required>

                <label for="editStock_In">Stock In</label>
                <input type="number" id="editStock_In" name="stockIn" required>

                <label for="editStock_Out">Stock Out</label>
                <input type="number" id="editStock_Out" name="stockOut" required>

                <button type="submit" class="add-button">Update Stock</button>
            </form>
        </div>
    </div>
</body>
</html>
