<?php
include 'db_connection.php';

// --- 1. SQL QUERY FOR SUMMARY CARDS ---
$countQuery = "SELECT 
                COUNT(*) AS TOTAL,
                COUNT(CASE WHEN PROD_CATEGORY = 'Food' THEN 1 END) AS FOOD,
                COUNT(CASE WHEN PROD_CATEGORY = 'Non-Food' THEN 1 END) AS NONFOOD
               FROM PRODUCT";
$countStid = oci_parse($conn, $countQuery);
oci_execute($countStid);
$counts = oci_fetch_assoc($countStid);

$totalProducts = $counts['TOTAL'];
$totalFood = $counts['FOOD'];
$totalNonFood = $counts['NONFOOD'];

// --- 2. QUERY FOR STANDARD DISPLAY ---
$queryStandard = "SELECT p.PROD_ID, p.PROD_NAME, p.PROD_LISTPRICE, p.PROD_NETPRICE, p.PROD_BRAND, p.PROD_CATEGORY, 
                         fp.FOOD_CATEGORY, fp.EXPIRY_DATE, fp.STORAGE_INSTRUCTIONS, nfp.NONFOOD_CATEGORY
                  FROM PRODUCT p
                  LEFT JOIN FOOD_PRODUCT fp ON p.PROD_ID = fp.PROD_ID
                  LEFT JOIN NONFOOD_PRODUCT nfp ON p.PROD_ID = nfp.PROD_ID
                  ORDER BY p.PROD_ID ASC";

// --- 3. EXACT EXPIRY QUERY ---
$queryExpiry = "SELECT P.PROD_ID, P.PROD_NAME, FP.EXPIRY_DATE, B.BRANCH_NAME, SK.STOCK_QUANTITY,
                    CASE 
                        WHEN FP.EXPIRY_DATE <= SYSDATE + 2 THEN 'URGENT'
                        ELSE 'WARNING'
                    END AS ALERT_LEVEL
                FROM PRODUCT P
                JOIN FOOD_PRODUCT FP ON P.PROD_ID = FP.PROD_ID
                JOIN STOCK SK ON P.PROD_ID = SK.PROD_ID
                JOIN BRANCH B ON SK.BRANCH_ID = B.BRANCH_ID
                WHERE FP.EXPIRY_DATE BETWEEN SYSDATE AND SYSDATE + 7 AND SK.STOCK_QUANTITY > 0
                ORDER BY FP.EXPIRY_DATE ASC";

// Execute Standard Data
$stid1 = oci_parse($conn, $queryStandard);
oci_execute($stid1);
$standardProducts = [];
while ($row = oci_fetch_assoc($stid1)) { $standardProducts[] = $row; }

// Execute Expiry Alert Data
$stid2 = oci_parse($conn, $queryExpiry);
oci_execute($stid2);
$expiryAlerts = [];
$uniqueBranches = []; // To populate the filter dropdown
while ($row = oci_fetch_assoc($stid2)) { 
    $expiryAlerts[] = $row; 
    if(!in_array($row['BRANCH_NAME'], $uniqueBranches)) $uniqueBranches[] = $row['BRANCH_NAME'];
}

oci_free_statement($countStid);
oci_free_statement($stid1);
oci_free_statement($stid2);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function showView(viewType) {
            const standardTable = document.getElementById('standard-view');
            const expiryTable = document.getElementById('expiry-view');
            const expiryFilters = document.getElementById('expiry-filters');
            const title = document.getElementById('view-title');

            if (viewType === 'EXPIRY') {
                standardTable.style.display = 'none';
                expiryTable.style.display = 'block';
                expiryFilters.style.display = 'flex';
                title.innerText = "Expiry Alerts (Next 7 Days)";
            } else {
                expiryTable.style.display = 'none';
                standardTable.style.display = 'block';
                expiryFilters.style.display = 'none';
                title.innerText = "Product List";
            }
        }

        // JS FILTER LOGIC FOR EXPIRY TABLE
        function applyExpiryFilters() {
            const branchVal = document.getElementById('filterBranch').value;
            const statusVal = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('.expiry-row');

            rows.forEach(row => {
                const branchMatch = (branchVal === 'ALL' || row.getAttribute('data-branch') === branchVal);
                const statusMatch = (statusVal === 'ALL' || row.getAttribute('data-status') === statusVal);

                if (branchMatch && statusMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function openAddProductModal() { document.getElementById("addProductModal").style.display = "flex"; toggleFields(); }
        function openEditProductModal(id, name, listPrice, category) {
            document.getElementById("editProductModal").style.display = "flex";
            document.getElementById("editProd_ID").value = id;
            document.getElementById("editProd_Name").value = name;
            document.getElementById("editProd_ListPrice").value = listPrice;
            document.getElementById("editProd_Category").value = category;
        }
        function closeModal() {
            document.getElementById("addProductModal").style.display = "none";
            document.getElementById("editProductModal").style.display = "none";
        }
        function toggleFields() {
            const category = document.getElementById("prodCategory").value;
            document.getElementById("foodFields").style.display = (category === "Food") ? "block" : "none";
            document.getElementById("nonFoodFields").style.display = (category === "Food") ? "none" : "block";
        }
        function confirmDelete(prodID) { if (confirm("Permanently delete product " + prodID + "?")) window.location.href = 'delete_product.php?prod_id=' + prodID; }
        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Product Management</h1>
        <button class="btn-add" onclick="openAddProductModal()">+ Add New Product</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card" onclick="showView('STANDARD')" style="cursor:pointer;">
            <h3>Total Products</h3>
            <span class="stat-number"><?= $totalProducts; ?></span>
            <small style="display:block; color:#888;">Show All Items</small>
        </div>
        <div class="stat-card" onclick="showView('EXPIRY')" style="border-left-color: #f44336; cursor:pointer; background: #fff5f5;">
            <h3 style="color: #c62828;">Urgent Expiry</h3>
            <span class="stat-number" style="color: #f44336;"><?= count($expiryAlerts); ?></span>
            <small style="display:block; color:#c62828; font-weight:600;">Check Alerts ⚠</small>
        </div>
        <div class="stat-card" style="border-left-color: #4CAF50;"><h3>Food Category</h3><span class="stat-number"><?= $totalFood; ?></span></div>
        <div class="stat-card" style="border-left-color: #2196F3;"><h3>Non-Food Category</h3><span class="stat-number"><?= $totalNonFood; ?></span></div>
    </div>

    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin-bottom: 15px;">General Product List</h2>

    <div id="expiry-filters" class="filter-container" style="display:none;">
        <div class="filter-group">
            <label>Branch Location</label>
            <select id="filterBranch" onchange="applyExpiryFilters()">
                <option value="ALL">All Branches</option>
                <?php foreach($uniqueBranches as $branch): ?>
                    <option value="<?= $branch ?>"><?= $branch ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Priority Level</label>
            <select id="filterStatus" onchange="applyExpiryFilters()">
                <option value="ALL">All Levels</option>
                <option value="URGENT">URGENT (Next 2 Days)</option>
                <option value="WARNING">WARNING (Next 7 Days)</option>
            </select>
        </div>

        <button type="button" class="btn-reset" onclick="resetExpiryFilters()">
            Reset Filters
        </button>
    </div>

    <div id="standard-view" class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price (L/N)</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Type Details</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($standardProducts as $product) : ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $product['PROD_ID']; ?></td>
                    <td><?= $product['PROD_NAME']; ?></td>
                    <td><span style="display:block;">L: RM<?= number_format($product['PROD_LISTPRICE'], 2); ?></span><small style="color: #4CAF50;">N: RM<?= number_format($product['PROD_NETPRICE'], 2); ?></small></td>
                    <td><?= $product['PROD_BRAND']; ?></td>
                    <td><?= $product['PROD_CATEGORY']; ?></td>
                    <td><?php if ($product['PROD_CATEGORY'] == 'Food'): ?><strong><?= $product['FOOD_CATEGORY']; ?></strong><br><small>Exp: <?= $product['EXPIRY_DATE']; ?></small><?php else: ?><strong><?= $product['NONFOOD_CATEGORY'] ?: 'General'; ?></strong><?php endif; ?></td>
                    <td><div style="display:flex; gap:10px;"><button class="btn-edit" onclick="openEditProductModal('<?= $product['PROD_ID']; ?>', '<?= addslashes($product['PROD_NAME']); ?>', '<?= $product['PROD_LISTPRICE']; ?>', '<?= $product['PROD_CATEGORY']; ?>')">Edit</button><button class="btn-delete" onclick="confirmDelete('<?= $product['PROD_ID']; ?>')">Delete</button></div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="expiry-view" class="table-container" style="display:none;">
        <table>
            <thead>
                <tr>
                    <th>PROD_ID</th>
                    <th>PROD_NAME</th>
                    <th>EXPIRY_DATE</th>
                    <th>BRANCH_NAME</th>
                    <th>STOCK_QUANTITY</th>
                    <th>ALERT_LEVEL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiryAlerts as $alert) : ?>
                <tr class="expiry-row" data-branch="<?= $alert['BRANCH_NAME']; ?>" data-status="<?= $alert['ALERT_LEVEL']; ?>">
                    <td><?= $alert['PROD_ID']; ?></td>
                    <td><strong><?= $alert['PROD_NAME']; ?></strong></td>
                    <td style="color:#f44336; font-weight:bold;"><?= $alert['EXPIRY_DATE']; ?></td>
                    <td><?= $alert['BRANCH_NAME']; ?></td>
                    <td><?= $alert['STOCK_QUANTITY']; ?></td>
                    <td>
                        <span style="padding:4px 8px; border-radius:4px; font-weight:600; font-size:0.8em; background: <?= ($alert['ALERT_LEVEL'] == 'URGENT') ? '#f44336' : '#ffa000'; ?>; color:white;">
                            <?= $alert['ALERT_LEVEL']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h2>Add New Product</h2><span class="close" onclick="closeModal()">&times;</span></div>
        <div class="modal-body">
            <form action="add_product.php" method="post">
                <label>Product Name</label><input type="text" name="prodName" required>
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;"><label>List Price</label><input type="number" step="0.01" name="prodListPrice" required></div>
                    <div style="flex: 1;"><label>Net Price</label><input type="number" step="0.01" name="prodNetPrice" required></div>
                </div>
                <label>Brand</label><input type="text" name="prodBrand" required>
                <label>Category</label>
                <select name="prodCategory" id="prodCategory" onchange="toggleFields()" style="width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">
                    <option value="Food">Food</option><option value="Non-Food">Non-Food</option>
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
                <div id="nonFoodFields" style="display:none;"><label>Non-Food Category</label><input type="text" name="nonFoodCategory"></div>
                <button type="submit" class="btn-add" style="width:100%">Save Product</button>
            </form>
        </div>
    </div>
</div>

<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h2>Edit Product</h2><span class="close" onclick="closeModal()">&times;</span></div>
        <div class="modal-body">
            <form action="edit_product.php" method="post">
                <input type="hidden" id="editProd_ID" name="prodID">
                <label>Product Name</label><input type="text" id="editProd_Name" name="prodName" required>
                <label>List Price (RM)</label><input type="number" step="0.01" id="editProd_ListPrice" name="prodListPrice" required>
                <label>Category</label><input type="text" id="editProd_Category" name="prodCategory" readonly style="background:#f9f9f9; color:#888;">
                <button type="submit" class="btn-edit" style="width:100%; margin-top:10px;">Update Product</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>