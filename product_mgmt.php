<?php
include 'db_connection.php';

$query = "SELECT p.PROD_ID, p.PROD_NAME, p.PROD_LISTPRICE, p.PROD_NETPRICE, p.PROD_BRAND, p.PROD_CATEGORY, fp.FOOD_CATEGORY, fp.EXPIRY_DATE, fp.STORAGE_INSTRUCTIONS, nfp.NONFOOD_CATEGORY
    FROM PRODUCT p
    LEFT JOIN FOOD_PRODUCT fp ON p.PROD_ID = fp.PROD_ID
    LEFT JOIN NONFOOD_PRODUCT nfp ON p.PROD_ID = nfp.PROD_ID
    ORDER BY p.PROD_ID ASC";

$stid = oci_parse($conn, $query);
$result = oci_execute($stid);

if (!$result) {
    $e = oci_error($stid);
    die("Query Error: " . $e['message']);
}

$products = [];
while ($row = oci_fetch_assoc($stid)) {
    $products[] = $row;
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Function to open Add Product Modal
        function openAddProductModal() {
            document.getElementById("addProductModal").style.display = "block";
        }

        // Function to open Edit Product Modal and populate fields
        function openEditProductModal(prodID, prodName, prodListPrice, prodCategory, expiryDate, storageInstructions, nonFoodCategory) {
            document.getElementById("editProductModal").style.display = "block";
            document.getElementById("editProd_ID").value = prodID;
            document.getElementById("editProd_Name").value = prodName;
            document.getElementById("editProd_ListPrice").value = prodListPrice;
            document.getElementById("editProd_Category").value = prodCategory;
            document.getElementById("editExpiryDate").value = expiryDate;
            document.getElementById("editStorageInstructions").value = storageInstructions;
            document.getElementById("editNonFoodCategory").value = nonFoodCategory;
        }

        // Function to confirm deletion of a product
        function confirmDelete(prodID) {
            const confirmation = confirm("Are you sure you want to delete this product?");
            if (confirmation) {
                window.location.href = 'delete_product.php?prod_id=' + prodID;
            }
        }

        // Function to close modals
        function closeModal() {
            document.getElementById("addProductModal").style.display = "none";
            document.getElementById("editProductModal").style.display = "none";
        }

        // Function to toggle fields based on product category
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
                    <th>Name</th>
                    <th>List Price</th>
                    <th>Net Price</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Food Type</th>
                    <th>Expiry Date</th>
                    <th>Storage Instructions</th>
                    <th>Non-Food Category</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><?= $product['PROD_ID']; ?></td>
                        <td><?= $product['PROD_NAME']; ?></td>
                        <td><?= $product['PROD_LISTPRICE']; ?></td>
                        <td><?= $product['PROD_NETPRICE']; ?></td>
                        <td><?= $product['PROD_BRAND']; ?></td>
                        <td><?= $product['PROD_CATEGORY']; ?></td>
                        <td><?= $product['FOOD_CATEGORY'] ?: '-'; ?></td>
                        <td><?= $product['EXPIRY_DATE'] ?: '-'; ?></td>
                        <td><?= $product['STORAGE_INSTRUCTIONS'] ?: '-'; ?></td>
                        <td><?= $product['NONFOOD_CATEGORY'] ?: '-'; ?></td>
                        <td>
                            <button onclick="openEditProductModal(
                                '<?= $product['PROD_ID']; ?>', 
                                '<?= $product['PROD_NAME']; ?>', 
                                '<?= $product['PROD_LISTPRICE']; ?>', 
                                '<?= $product['PROD_CATEGORY']; ?>', 
                                '<?= $product['EXPIRY_DATE']; ?>', 
                                '<?= $product['STORAGE_INSTRUCTIONS']; ?>', 
                                '<?= $product['NONFOOD_CATEGORY']; ?>'
                            )" class="edit">Edit</button>
                            <button onclick="confirmDelete('<?= $product['PROD_ID']; ?>')" class="delete">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Product</h2>
            <form action="add_product.php" method="post">
                <label>Product Name</label>
                <input type="text" name="prodName" required>

                <label>List Price</label>
                <input type="number" name="prodListPrice" required>

                <label>Net Price</label>
                <input type="number" name="prodNetPrice" required>

                <label>Product Brand</label>
                <input type="text" name="prodBrand" required>

                <label>Category</label>
                <select name="prodCategory" id="prodCategory" required onchange="toggleFields()">
                    <option value="Food">Food</option>
                    <option value="Non-Food">Non-Food</option>
                </select>

                <!-- Food Product Fields (Visible if "Food" is selected) -->
                <div id="foodFields" style="display: none;">
                    <label>Food Type</label>
                    <select name="foodType">
                        <option value="Fruit">Fruit</option>
                        <option value="Vegetable">Vegetable</option>
                        <option value="Meat">Meat</option>
                        <option value="Drink">Drink</option>
                        <option value="Snacks">Snacks</option>

                    </select>

                    <label>Expiry Date</label>
                    <input type="date" name="expiryDate">

                    <label>Storage Instructions</label>
                    <input type="text" name="storageInstructions">
                </div>

                <!-- Non-Food Product Fields (Visible if "Non-Food" is selected) -->
                <div id="nonFoodFields" style="display: none;">
                    <label>Non-Food Category</label>
                    <input type="text" name="nonFoodCategory">
                </div>

                <button type="submit" class="add-button">Add Product</button>
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

                <label>Product Name</label>
                <input type="text" id="editProd_Name" name="prodName" required>

                <label>List Price</label>
                <input type="number" id="editProd_ListPrice" name="prodListPrice" required>

                <label>Category</label>
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
