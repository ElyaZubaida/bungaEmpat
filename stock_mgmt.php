<?php
// Dummy data for stock
$stock = [
    ['Stock_ID' => 1, 'Prod_ID' => 1, 'Branch_ID' => 1, 'Staff_ID' => 2, 'Stock_Quantity' => 100, 'Stock_In' => 50, 'Stock_Out' => 30],
    ['Stock_ID' => 2, 'Prod_ID' => 2, 'Branch_ID' => 2, 'Staff_ID' => 3, 'Stock_Quantity' => 80, 'Stock_In' => 30, 'Stock_Out' => 40],
    ['Stock_ID' => 3, 'Prod_ID' => 3, 'Branch_ID' => 3, 'Staff_ID' => 1, 'Stock_Quantity' => 120, 'Stock_In' => 70, 'Stock_Out' => 20],
    ['Stock_ID' => 4, 'Prod_ID' => 4, 'Branch_ID' => 4, 'Staff_ID' => 2, 'Stock_Quantity' => 90, 'Stock_In' => 60, 'Stock_Out' => 10],
];

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <?php
                foreach ($stock as $stockItem) {
                    echo "<tr>
                            <td>" . $stockItem['Stock_ID'] . "</td>
                            <td>" . $stockItem['Prod_ID'] . "</td>
                            <td>" . $stockItem['Branch_ID'] . "</td>
                            <td>" . $stockItem['Staff_ID'] . "</td>
                            <td>" . $stockItem['Stock_Quantity'] . "</td>
                            <td>" . $stockItem['Stock_In'] . "</td>
                            <td>" . $stockItem['Stock_Out'] . "</td>
                            <td>
                                <button onclick=\"openEditStockModal(" . $stockItem['Stock_ID'] . ", " . $stockItem['Prod_ID'] . ", " . $stockItem['Branch_ID'] . ", " . $stockItem['Staff_ID'] . ", " . $stockItem['Stock_Quantity'] . ", " . $stockItem['Stock_In'] . ", " . $stockItem['Stock_Out'] . ")\" class='edit'>Edit</button>
                                <button onclick=\"confirmDelete(" . $stockItem['Stock_ID'] . ")\" class='delete'>Delete</button>
                            </td>
                        </tr>";
                }
                ?>
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
                <input type="number" id="prodID" name="prodID" required>

                <label for="branchID">Branch ID</label>
                <input type="number" id="branchID" name="branchID" required>

                <label for="staffID">Staff ID</label>
                <input type="number" id="staffID" name="staffID" required>

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
                <input type="number" id="editProd_ID" name="prodID" required>

                <label for="editBranch_ID">Branch ID</label>
                <input type="number" id="editBranch_ID" name="branchID" required>

                <label for="editStaff_ID">Staff ID</label>
                <input type="number" id="editStaff_ID" name="staffID" required>

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
