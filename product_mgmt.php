<?php
// Dummy data for products 
$products = [
    ['Prod_ID' => 1, 'Prod_Name' => 'Apple', 'Prod_ListPrice' => 3.00, 'Prod_NetPrice' => 2.50, 'Prod_Brand' => 'Brand A', 'Prod_Category' => 'Food', 'Food_Type' => 'Fruit', 'Expiry_Date' => '31/12/2023', 'Storage_Instructions' => 'Keep Refrigerated', 'NonFood_Category' => ''],
    ['Prod_ID' => 2, 'Prod_Name' => 'Banana', 'Prod_ListPrice' => 2.20, 'Prod_NetPrice' => 1.80, 'Prod_Brand' => 'Brand B', 'Prod_Category' => 'Food', 'Food_Type' => 'Fruit', 'Expiry_Date' => '31/12/2023', 'Storage_Instructions' => 'Keep Refrigerated', 'NonFood_Category' => ''],
    ['Prod_ID' => 3, 'Prod_Name' => 'Carrot', 'Prod_ListPrice' => 1.50, 'Prod_NetPrice' => 1.20, 'Prod_Brand' => 'Brand C', 'Prod_Category' => 'Food', 'Food_Type' => 'Vegetable', 'Expiry_Date' => '31/12/2023', 'Storage_Instructions' => 'Keep Refrigerated', 'NonFood_Category' => ''],
    ['Prod_ID' => 4, 'Prod_Name' => 'Lettuce', 'Prod_ListPrice' => 2.50, 'Prod_NetPrice' => 2.00, 'Prod_Brand' => 'Brand D', 'Prod_Category' => 'Food', 'Food_Type' => 'Vegetable', 'Expiry_Date' => '31/12/2023', 'Storage_Instructions' => 'Keep Refrigerated', 'NonFood_Category' => ''],
    ['Prod_ID' => 5, 'Prod_Name' => 'Chicken Breast', 'Prod_ListPrice' => 6.00, 'Prod_NetPrice' => 5.50, 'Prod_Brand' => 'Brand E', 'Prod_Category' => 'Food', 'Food_Type' => 'Meat', 'Expiry_Date' => '31/12/2023', 'Storage_Instructions' => 'Keep Refrigerated', 'NonFood_Category' => ''],
    ['Prod_ID' => 6, 'Prod_Name' => 'Toothpaste', 'Prod_ListPrice' => 2.50, 'Prod_NetPrice' => 2.00, 'Prod_Brand' => 'Brand F', 'Prod_Category' => 'Non-Food', 'Food_Type' => '', 'Expiry_Date' => '', 'Storage_Instructions' => '', 'NonFood_Category' => 'Personal Care'],
    ['Prod_ID' => 7, 'Prod_Name' => 'Shampoo', 'Prod_ListPrice' => 5.00, 'Prod_NetPrice' => 4.50, 'Prod_Brand' => 'Brand G', 'Prod_Category' => 'Non-Food', 'Food_Type' => '', 'Expiry_Date' => '', 'Storage_Instructions' => '', 'NonFood_Category' => 'Hair Care'],
];

include 'sidebar.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Open the Add Product modal
        function openAddProductModal() {
            document.getElementById("addProductModal").style.display = "block";
        }

        // Open the Edit Product modal and populate data
        function openEditProductModal(prodID, prodName, prodListPrice, prodCategory) {
            document.getElementById("editProductModal").style.display = "block";
            document.getElementById("editProd_ID").value = prodID;
            document.getElementById("editProd_Name").value = prodName;
            document.getElementById("editProd_ListPrice").value = prodListPrice;
            document.getElementById("editProd_Category").value = prodCategory;
        }

        // Confirm Delete Product
        function confirmDelete(prodID) {
            const confirmation = confirm("Are you sure you want to delete this product?");
            
            if (confirmation) {
                // Redirect to the delete_product.php page
                window.location.href = 'delete_product.php?prod_id=' + prodID;
            }
        }

        // Close the modal
        function closeModal() {
            document.getElementById("addProductModal").style.display = "none";
            document.getElementById("editProductModal").style.display = "none";
        }

        function toggleFields() {
            const category = document.getElementById("prodCategory").value;
            const foodFields = document.getElementById("foodFields");
            const nonFoodFields = document.getElementById("nonFoodFields");

            if (category === "Food") {
                foodFields.style.display = "block";
                nonFoodFields.style.display = "none";
            } else if (category === "Non-Food") {
                nonFoodFields.style.display = "block";
                foodFields.style.display = "none";
            }
        }

        // Run the function once to set the initial state
        window.onload = toggleFields;
    </script>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <h1>Product Management</h1>
            <div class="button-container">
                <div class="addbutton">
                    <button class="add-button" onclick="openAddProductModal()">Add</button>
                </div>
            </div>
            <table id="productTable">
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>List Price</th>
                    <th>Net Price</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Food Type</th> <!-- New column for Food Type -->
                    <th>Expiry Date</th>
                    <th>Storage Instructions</th>
                    <th>Non-Food Category</th>
                    <th>Action</th>
                </tr>
                <?php
                foreach ($products as $product) {
                    echo "<tr>
                            <td>" . $product['Prod_ID'] . "</td>
                            <td>" . $product['Prod_Name'] . "</td>
                            <td>" . $product['Prod_ListPrice'] . "</td>
                            <td>" . $product['Prod_NetPrice'] . "</td>
                            <td>" . $product['Prod_Brand'] . "</td>
                            <td>" . $product['Prod_Category'] . "</td>
                            <td>" . ($product['Food_Type'] ?: '-') . "</td> <!-- Display Food Type for Food Products -->
                            <td>" . ($product['Expiry_Date'] ?: '-') . "</td>
                            <td>" . ($product['Storage_Instructions'] ?: '-') . "</td>
                            <td>" . ($product['NonFood_Category'] ?: '-') . "</td>
                            <td>
                                <button onclick=\"openEditProductModal('". $product['Prod_ID'] ."', '". $product['Prod_Name'] ."', '". $product['Prod_ListPrice'] ."', '". $product['Prod_Category'] ."')\" class='edit'>Edit</button>
                                <button onclick=\"confirmDelete('". $product['Prod_ID'] ."')\" class='delete'>Delete</button>
                            </td>
                        </tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Product</h2>
            <form action="add_product.php" method="post">
                <label for="prodName">Product Name</label>
                <input type="text" id="prodName" name="prodName" required>

                <label for="prodListPrice">List Price</label>
                <input type="number" id="prodListPrice" name="prodListPrice" required>

                <label for="prodNetPrice">Net Price</label>
                <input type="number" id="prodNetPrice" name="prodNetPrice" required>

                <label for="prodCategory">Category</label>
                <select id="prodCategory" name="prodCategory" required onchange="toggleFields()">
                    <option value="Food">Food</option>
                    <option value="Non-Food">Non-Food</option>
                </select>

                <!-- Food Product Fields (Visible if "Food" is selected) -->
                <div id="foodFields" style="display: none;">
                    <label for="foodType">Food Type</label>
                    <select id="foodType" name="foodType">
                        <option value="">Select Food Type</option>
                        <option value="Fruit">Fruit</option>
                        <option value="Vegetable">Vegetable</option>
                        <option value="Meat">Meat</option>
                        <option value="Drink">Drink</option>
                    </select>

                    <label for="expiryDate">Expiry Date</label>
                    <input type="text" id="expiryDate" name="expiryDate" placeholder="DD/MM/YYYY">

                    <label for="storageInstructions">Storage Instructions</label>
                    <select id="storage" name="storage">
                        <option value="">Select Storage Instructions</option>
                        <option value="refrigerated">Keep Refrigerated</option>
                        <option value="room">Room Temperature</option>
                    </select>
                </div>

                <!-- Non-Food Product Fields (Visible if "Non-Food" is selected) -->
                <div id="nonFoodFields" style="display: none;">
                    <label for="nonFoodCategory">Non-Food Category</label>
                    <select id="nonFoodCategory" name="nonFoodCategory">
                        <option value="Personal Care">Personal Care</option>
                        <option value="Hair Care">Hair Care</option>
                        <option value="Household">Household</option>
                    </select>
                </div>

                <button type="submit" class="add-button">Add</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Product</h2>
            <form action="edit_product.php" method="post">
                <input type="hidden" id="editProd_ID" name="prodID">

                <label for="editProd_Name">Product Name</label>
                <input type="text" id="editProd_Name" name="prodName" required>

                <label for="editProd_ListPrice">List Price</label>
                <input type="number" id="editProd_ListPrice" name="prodListPrice" required>

                <label for="editProd_Category">Category</label>
                <select id="editProd_Category" name="prodCategory" required>
                    <option value="Food">Food</option>
                    <option value="Non-Food">Non-Food</option>
                </select>

                <button type="submit" class="add-button">Update</button>
            </form>
        </div>
    </div>

</body>
</html>
