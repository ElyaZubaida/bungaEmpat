<?php
include 'db_connection.php';

// --- 1. SUMMARY CARDS QUERY ---
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

// --- 2. FETCH ALL BRANCHES FOR DROPDOWNS ---
$bListStid = oci_parse($conn, "SELECT BRANCH_NAME FROM BRANCH ORDER BY BRANCH_NAME ASC");
oci_execute($bListStid);
$allBranches = [];
while($b = oci_fetch_assoc($bListStid)) { $allBranches[] = $b['BRANCH_NAME']; }

$sListStid = oci_parse($conn, "SELECT SUPP_ID, SUPP_NAME FROM SUPPLIER ORDER BY SUPP_NAME ASC");
oci_execute($sListStid);
$allSuppliers = [];
while($s = oci_fetch_assoc($sListStid)) { $allSuppliers[] = $s; }

// --- 3. QUERY FOR GENERAL DISPLAY (Joining 3 Tables) ---
$queryStandard = "SELECT p.PROD_ID, p.PROD_NAME, p.PROD_LISTPRICE, p.PROD_NETPRICE, p.PROD_BRAND, p.PROD_CATEGORY, 
                         fp.FOOD_CATEGORY, fp.EXPIRY_DATE, fp.STORAGE_INSTRUCTIONS, 
                         nfp.NONFOOD_CATEGORY
                  FROM PRODUCT p
                  LEFT JOIN FOOD_PRODUCT fp ON p.PROD_ID = fp.PROD_ID
                  LEFT JOIN NONFOOD_PRODUCT nfp ON p.PROD_ID = nfp.PROD_ID
                  ORDER BY p.PROD_ID ASC";
$stidStd = oci_parse($conn, $queryStandard);
oci_execute($stidStd);
$standardProducts = [];
while ($row = oci_fetch_assoc($stidStd)) { $standardProducts[] = $row; }

// --- GENERATE NEXT PRODUCT IDs ---

// 1. For Food (F-)
// Logic: Find the MAX numeric value by removing all non-digits from IDs starting with 'F-'
$food_id_query = "SELECT MAX(TO_NUMBER(REGEXP_REPLACE(PROD_ID, '[^0-9]', ''))) AS MAX_VAL 
                  FROM PRODUCT 
                  WHERE PROD_ID LIKE 'F-%'";
$f_stid = oci_parse($conn, $food_id_query);
oci_execute($f_stid);
$f_row = oci_fetch_assoc($f_stid);
$next_food_num = ($f_row && $f_row['MAX_VAL']) ? $f_row['MAX_VAL'] + 1 : 1001;
$next_food_id = "F-" . $next_food_num;

// 2. For Non-Food (NF-)
// Logic: Find the MAX numeric value by removing all non-digits from IDs starting with 'NF-'
$nf_id_query = "SELECT MAX(TO_NUMBER(REGEXP_REPLACE(PROD_ID, '[^0-9]', ''))) AS MAX_VAL 
                 FROM PRODUCT 
                 WHERE PROD_ID LIKE 'NF-%'";
$nf_stid = oci_parse($conn, $nf_id_query);
oci_execute($nf_stid);
$nf_row = oci_fetch_assoc($nf_stid);
$next_nf_num = ($nf_row && $nf_row['MAX_VAL']) ? $nf_row['MAX_VAL'] + 1 : 1001;
$next_nf_id = "NF-" . $next_nf_num;

oci_free_statement($f_stid);
oci_free_statement($nf_stid);

// --- FETCH FOOD SUB-CATEGORIES ---
$f_cat_stid = oci_parse($conn, "SELECT DISTINCT FOOD_CATEGORY FROM FOOD_PRODUCT WHERE FOOD_CATEGORY IS NOT NULL ORDER BY FOOD_CATEGORY ASC");
oci_execute($f_cat_stid);
$foodSubCats = [];
while ($r = oci_fetch_assoc($f_cat_stid)) { $foodSubCats[] = $r['FOOD_CATEGORY']; }

// --- FETCH NON-FOOD SUB-CATEGORIES ---
$nf_cat_stid = oci_parse($conn, "SELECT DISTINCT NONFOOD_CATEGORY FROM NONFOOD_PRODUCT WHERE NONFOOD_CATEGORY IS NOT NULL ORDER BY NONFOOD_CATEGORY ASC");
oci_execute($nf_cat_stid);
$nonFoodSubCats = [];
while ($r = oci_fetch_assoc($nf_cat_stid)) { $nonFoodSubCats[] = $r['NONFOOD_CATEGORY']; }

oci_free_statement($f_cat_stid);
oci_free_statement($nf_cat_stid);

// --- FETCH STORAGE METHODS FROM DB ---
$storage_stid = oci_parse($conn, "SELECT DISTINCT STORAGE_INSTRUCTIONS FROM FOOD_PRODUCT WHERE STORAGE_INSTRUCTIONS IS NOT NULL ORDER BY STORAGE_INSTRUCTIONS ASC");
oci_execute($storage_stid);
$storageMethods = [];
while ($r = oci_fetch_assoc($storage_stid)) { 
    $storageMethods[] = $r['STORAGE_INSTRUCTIONS']; 
}
oci_free_statement($storage_stid);

/**
 * 4. QISTINA'S COMPLEX QUERY (EXPIRY ALERTS DISPLAY)
 * Fetches products that are about to expire within 7 days.
 */
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
$stidExpiry = oci_parse($conn, $queryExpiry);
oci_execute($stidExpiry);
$expiryAlerts = [];
while ($row = oci_fetch_assoc($stidExpiry)) { $expiryAlerts[] = $row; }

/**
 * 5. AMIRA'S COMPLEX QUERY (PERFORMANCE REPORT DISPLAY)
 * Fetches product sales performance based on branch and date range.
 */
$perfBranch = $_GET['perfBranch'] ?? 'Bunga Empat UiTM Shah Alam';
$perfStart  = $_GET['perfStart'] ?? date('Y-m-01');
$perfEnd    = $_GET['perfEnd'] ?? date('Y-m-d');

$queryPerf = "SELECT p.PROD_ID, p.PROD_NAME, p.PROD_CATEGORY, SUM(ps.PS_QUANTITY) AS UNITS_SOLD, 
                TO_CHAR(SUM(ps.PS_SUBPRICE), '999,999.00') AS \"TOTAL REVENUE\",
                (SELECT sup.SUPP_NAME FROM SUPPLIER sup WHERE sup.SUPP_ID = p.SUPP_ID) AS SUPPLIER
              FROM PRODUCT p
              JOIN PRODUCT_SALE ps ON p.PROD_ID = ps.PROD_ID
              JOIN SALE s ON ps.SALE_ID = s.SALE_ID
              JOIN STAFF st ON s.STAFF_ID = st.STAFF_ID
              JOIN BRANCH b ON st.BRANCH_ID = b.BRANCH_ID
              WHERE b.BRANCH_NAME = :input_branch
              AND TRUNC(s.SALE_DATE) BETWEEN TO_DATE(:start_dt, 'YYYY-MM-DD') AND TO_DATE(:end_dt, 'YYYY-MM-DD')
              GROUP BY p.PROD_ID, p.PROD_NAME, p.PROD_CATEGORY, p.PROD_LISTPRICE, p.SUPP_ID
              ORDER BY SUM(ps.PS_QUANTITY) DESC";

$stidPerf = oci_parse($conn, $queryPerf);
oci_bind_by_name($stidPerf, ":input_branch", $perfBranch);
oci_bind_by_name($stidPerf, ":start_dt", $perfStart);
oci_bind_by_name($stidPerf, ":end_dt", $perfEnd);
oci_execute($stidPerf);
$performanceData = [];
while ($row = oci_fetch_assoc($stidPerf)) { $performanceData[] = $row; }

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
        /**
         * Toggles between the General Product list, Expiry Alerts, and Performance Reports.
         * Updates the UI heading and table visibility.
         */
        function showView(viewType) {
            const views = ['standard-view', 'expiry-view', 'perf-view'];
            const filters = ['expiry-filters', 'perf-filters'];
            const searchSection = document.getElementById('search-section');
            const title = document.getElementById('view-title');

            views.forEach(v => { if(document.getElementById(v)) document.getElementById(v).style.display = 'none'; });
            filters.forEach(f => { if(document.getElementById(f)) document.getElementById(f).style.display = 'none'; });

            if (viewType === 'EXPIRY') {
                document.getElementById('expiry-view').style.display = 'block';
                document.getElementById('expiry-filters').style.display = 'block';
                searchSection.style.display = 'none'; 
                title.innerText = "Expiry Alerts Report";
            } else if (viewType === 'PERFORMANCE') {
                document.getElementById('perf-view').style.display = 'block';
                document.getElementById('perf-filters').style.display = 'block';
                searchSection.style.display = 'none'; 
                title.innerText = "Product Sales Performance Report";
            } else {
                document.getElementById('standard-view').style.display = 'block';
                searchSection.style.display = 'flex'; 
                title.innerText = "General Product List";
            }
        }

        // Live Search Logic for General List
        function runLiveSearch() {
            const query = document.getElementById('txtSearch').value.toLowerCase();
            const category = document.getElementById('selCategory').value;
            const rows = document.querySelectorAll("#standard-view tbody tr");

            rows.forEach(row => {
                const name = row.cells[1].innerText.toLowerCase();
                const rowCat = row.getAttribute('data-category');
                
                const matchesText = name.includes(query);
                const matchesCat = (category === 'ALL' || rowCat === category);

                row.style.display = (matchesText && matchesCat) ? "" : "none";
            });
        }

        function filterExpiryTable() {
            const branch = document.getElementById('filterBranch').value.toUpperCase();
            const status = document.getElementById('filterStatus').value.toUpperCase();
            const rows = document.querySelectorAll("#expiry-view tbody tr");
            rows.forEach(row => {
                const bMatch = (branch === 'ALL' || row.cells[3].innerText.toUpperCase().includes(branch));
                const sMatch = (status === 'ALL' || row.getAttribute('data-status').toUpperCase() === status);
                row.style.display = (bMatch && sMatch) ? "" : "none";
            });
        }

        function toggleFields() {
            const cat = document.getElementById("prodCategory").value;
            document.getElementById("foodFields").style.display = (cat === "Food") ? "block" : "none";
            document.getElementById("nonFoodFields").style.display = (cat === "Non-Food") ? "block" : "none";
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('perfBranch')) showView('PERFORMANCE');
        };

        function openAddProductModal() { document.getElementById("addProductModal").style.display = "flex"; }
        
        function closeModal() {
            document.getElementById("addProductModal").style.display = "none";
            document.getElementById("editProductModal").style.display = "none";
        }

        function openEditProductModal(id, name, listPrice, category, subCat, expiry, storage, brand, netPrice) {
    document.getElementById("editProductModal").style.display = "flex";
    
    // 1. UPDATE EDITABLE INPUTS
    document.getElementById("editProd_ID").value = id;
    document.getElementById("editProd_Name").value = name;
    document.getElementById("editProd_ListPrice").value = listPrice;
    document.getElementById("hidden_Category").value = category; // Still need this for PHP logic

    // 2. UPDATE STATIC TEXT LABELS (No more ugly boxes!)
    document.getElementById("label_ID").innerText = id;
    document.getElementById("label_Brand").innerText = brand;
    document.getElementById("label_Category").innerText = category;
    document.getElementById("label_Cost").innerText = "RM " + netPrice;

    // 3. TOGGLE SUB-FIELDS
    const foodSection = document.getElementById("editFoodFields");
    const nonFoodSection = document.getElementById("editNonFoodFields");

    if (category === 'Food') {
        foodSection.style.display = "block";
        nonFoodSection.style.display = "none";
        document.getElementById("editFoodType").value = subCat;
        document.getElementById("editExpiryDate").value = expiry;
        document.getElementById("editStorage").value = storage;
    } else {
        foodSection.style.display = "none";
        nonFoodSection.style.display = "block";
        document.getElementById("editNonFoodCat").value = subCat;
    }
}
        function confirmDelete(id) { 
            if(confirm('Are you sure you want to delete product ' + id + '?')) {
                window.location.href = 'delete_product.php?prod_id=' + id; 
            }
        }

        const nextIDs = {
    "Food": "<?= $next_food_id ?>",
    "Non-Food": "<?= $next_nf_id ?>"
};

function toggleFields() {
    const cat = document.getElementById("prodCategory").value;
    
    // 1. Switch the ID Display
    document.getElementById("displayProdID").value = nextIDs[cat];
    
    // 2. Toggle field visibility (This part was being overwritten)
    document.getElementById("foodFields").style.display = (cat === "Food") ? "block" : "none";
    document.getElementById("nonFoodFields").style.display = (cat === "Non-Food") ? "block" : "none";
}
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">Product Management</h1>
        <button class="btn-add" onclick="openAddProductModal()">+ Add New Product</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card" onclick="showView('STANDARD')" style="cursor:pointer;">
            <h3 style="color: #fd79a8;">Total Products</h3><span class="stat-number"><?= $totalProducts; ?></span>
        </div>
        <div class="stat-card" onclick="showView('EXPIRY')" style="border-left-color: #f44336; cursor:pointer;">
            <h3 style="color: #c62828;">Urgent Expiry</h3><span class="stat-number" style="color: #f44336;"><?= count($expiryAlerts); ?></span>
        </div>
        <div class="stat-card" onclick="showView('PERFORMANCE')" style="border-left-color: #4361ee; cursor:pointer;">
            <h3 style="color: #4361ee;">Product Performance</h3><span class="stat-number" style="color: #4361ee;"><?= count($performanceData); ?></span>
        </div>
    </div>

    <!-- Filters for expiry alerts-->
    <div id="expiry-filters" class="analysis-filter-card" style="display:none;">
        <div class="analysis-header">
            <h4>Expiry Filter Parameters</h4>
            <p>Filter products by specific branch and alert levels.</p>
        </div>
        <form class="filter-row" onsubmit="event.preventDefault(); filterExpiryTable();">
            <div class="filter-field">
                <label>Branch Location</label>
                <select id="filterBranch" class="filter-select">
                    <option value="ALL">All Branches</option>
                    <?php foreach($allBranches as $branch): ?><option value="<?= $branch ?>"><?= $branch ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="filter-field">
                <label>Urgency Level</label>
                <select id="filterStatus" class="filter-select">
                    <option value="ALL">All Statuses</option>
                    <option value="URGENT">Urgent</option>
                    <option value="WARNING">Warning</option>
                </select>
            </div>
            <button type="submit" class="btn-analysis">Apply</button>
        </form>
    </div>

    <!-- Filters for product performance report-->
    <div id="perf-filters" class="analysis-filter-card" style="display:none;">
        <div class="analysis-header">
            <h4>Performance Analysis Parameters</h4>
            <p>Analyze product performance for a specific branch and date range.</p>
        </div>
        <form method="GET" class="filter-row">
            <div class="filter-field">
                <label>Branch Location</label>
                <select name="perfBranch" class="filter-select">
                    <?php foreach($allBranches as $bName): ?>
                        <option value="<?= $bName ?>" <?= ($perfBranch == $bName) ? 'selected' : '' ?>><?= $bName ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-field"><label>Start Date</label><input type="date" name="perfStart" class="filter-input" value="<?= $perfStart ?>"></div>
            <div class="filter-field"><label>End Date</label><input type="date" name="perfEnd" class="filter-input" value="<?= $perfEnd ?>"></div>
            <button type="submit" class="btn-analysis">Apply</button>
        </form>
    </div>

    <div id="search-section" class="search-row">
        <div class="search-box">
            <input type="text" id="txtSearch" placeholder="Search product name..." class="filter-input" style="width:100%" onkeyup="runLiveSearch()">
        </div>
        <div class="category-box">
            <select id="selCategory" class="filter-select" style="width:100%" onchange="runLiveSearch()">
                <option value="ALL">All Categories</option>
                <option value="Food">Food Only</option>
                <option value="Non-Food">Non-Food Only</option>
            </select>
        </div>
    </div>

    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin: 15px 0;">General Product List</h2>

    <div id="standard-view" class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Category Details</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($standardProducts as $p): ?>
                <tr data-category="<?= $p['PROD_CATEGORY'] ?>">
                    <td><?= $p['PROD_ID'] ?></td>
                    <td>
                        <strong><?= $p['PROD_NAME'] ?></strong>
                        <span class="sub-text">Brand: <?= $p['PROD_BRAND'] ?> | <?= $p['PROD_CATEGORY'] ?></span>
                    </td>
                    <td>RM <?= number_format($p['PROD_LISTPRICE'], 2) ?></td>
                    <td>
                        <?php if($p['PROD_CATEGORY'] == 'Food'): ?>
                            <span class="sub-text">Type: <?= $p['FOOD_CATEGORY'] ?></span>
                            <span class="sub-text" style="color:#d32f2f">Exp: <?= $p['EXPIRY_DATE'] ?></span>
                        <?php else: ?>
                            <span class="sub-text">Type: <?= $p['NONFOOD_CATEGORY'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditProductModal(
                                '<?= $p['PROD_ID'] ?>', 
                                '<?= addslashes($p['PROD_NAME']) ?>', 
                                '<?= $p['PROD_LISTPRICE'] ?>', 
                                '<?= $p['PROD_CATEGORY'] ?>',
                                '<?= ($p['PROD_CATEGORY'] == 'Food') ? addslashes($p['FOOD_CATEGORY'] ?? '') : addslashes($p['NONFOOD_CATEGORY'] ?? '') ?>',
                                '<?= $p['EXPIRY_DATE'] ?? '' ?>',
                                '<?= addslashes($p['STORAGE_INSTRUCTIONS'] ?? '') ?>',
                                '<?= addslashes($p['PROD_BRAND'] ?? '') ?>',
                                '<?= number_format($p['PROD_NETPRICE'] ?? 0, 2) ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $p['PROD_ID'] ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- View for expiry alerts-->
    <div id="expiry-view" class="table-container" style="display:none;">
        <table>
            <thead><tr><th>ID</th><th>Product Name</th><th>Expiry Date</th><th>Branch</th><th>Stock</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach($expiryAlerts as $a): ?>
                <tr data-status="<?= $a['ALERT_LEVEL'] ?>">
                    <td><?= $a['PROD_ID'] ?></td>
                    <td><?= $a['PROD_NAME'] ?></td>
                    <td style="color:#d32f2f; font-weight:bold;"><?= $a['EXPIRY_DATE'] ?></td>
                    <td><?= $a['BRANCH_NAME'] ?></td>
                    <td><?= $a['STOCK_QUANTITY'] ?></td>
                    <td><span class="status-badge <?= strtolower($a['ALERT_LEVEL']) ?>"><?= $a['ALERT_LEVEL'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- View for product performance report-->
    <div id="perf-view" class="table-container" style="display:none;">
        <table>
            <thead><tr><th>Product Name</th><th>Category</th><th>Units Sold</th><th>Total Revenue</th><th>Supplier</th></tr></thead>
            <tbody>
                <?php foreach ($performanceData as $row) : ?>
                <tr>
                    <td><strong><?= $row['PROD_NAME'] ?></strong></td>
                    <td><?= $row['PROD_CATEGORY'] ?></td>
                    <td><?= $row['UNITS_SOLD'] ?></td>
                    <td style="color:#2e7d32; font-weight:bold;">RM <?= $row['TOTAL REVENUE'] ?></td>
                    <td><?= $row['SUPPLIER'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Product</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_product.php" method="post">
                <label>Product ID</label>
                <input type="text" name="prodID" id="displayProdID" value="<?= $next_food_id; ?>" readonly style="background:#f5f5f5;">

                <label>Product Name</label>
                <input type="text" name="prodName" required placeholder="e.g. Jasmine Sunwhite Rice">
                
                <div class="input-row">
                    <div style="flex: 1;"><label>Sell Price (RM)</label><input type="number" step="0.01" name="prodListPrice" required></div>
                    <div style="flex: 1;"><label>Cost Price (RM)</label><input type="number" step="0.01" name="prodNetPrice" required></div>
                </div>

                <div class="input-row">
                    <div style="flex: 1;"><label>Brand</label><input type="text" name="prodBrand" required></div>
                    <div style="flex: 1;">
                        <label>Supplier</label>
                        <select name="suppID" required>
                            <option value="" disabled selected>Select Supplier</option>
                            <?php foreach ($allSuppliers as $sup): ?>
                                <option value="<?= $sup['SUPP_ID']; ?>"><?= $sup['SUPP_NAME']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <label>Main Category</label>
                <select name="prodCategory" id="prodCategory" onchange="toggleFields()" required>
                    <option value="Food">Food (F-)</option>
                    <option value="Non-Food">Non-Food (NF-)</option>
                </select>

                <div id="foodFields">
    <label>Food Sub-Category</label>
    <select name="foodType">
        <option value="" disabled selected>Select Food Type</option>
        <?php foreach ($foodSubCats as $fcat): ?>
            <option value="<?= $fcat ?>"><?= $fcat ?></option>
        <?php endforeach; ?>
    </select>
    
    <div class="input-row" style="margin-top:10px;">
        <div style="flex: 1;">
            <label>Expiry Date</label>
            <input type="date" name="expiryDate">
        </div>
        <div style="flex: 1;">
            <label>Storage Instructions</label>
            <select name="storageInstructions">
                <option value="" disabled selected>Select Storage from DB</option>
                <?php if (empty($storageMethods)): ?>
                    <option value="Ambient">Ambient (Default)</option>
                <?php else: ?>
                    <?php foreach ($storageMethods as $method): ?>
                        <option value="<?= htmlspecialchars($method) ?>"><?= htmlspecialchars($method) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
</div>

                <div id="nonFoodFields" style="display:none;">
                    <label>Non-Food Sub-Category</label>
                    <select name="nonFoodCategory">
                        <option value="" disabled selected>Select Category Type</option>
                        <?php foreach ($nonFoodSubCats as $nfcat): ?>
                            <option value="<?= $nfcat ?>"><?= $nfcat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="modal-btn-full" style="margin-top:20px;">Save Product</button>
            </form>
        </div>
    </div>
</div>

<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Product</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_product.php" method="post">
                <input type="hidden" id="editProd_ID" name="prodID">
                <input type="hidden" id="hidden_Category" name="prodCategory">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #fdf6e3; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px dashed #d3bd8d;">
                    <div>
                        <small style="color: #888; text-transform: uppercase; font-weight: 600; font-size: 0.75em;">Product ID</small>
                        <div id="label_ID" style="font-weight: 600; color: #333;"></div>
                    </div>
                    <div>
                        <small style="color: #888; text-transform: uppercase; font-weight: 600; font-size: 0.75em;">Brand</small>
                        <div id="label_Brand" style="font-weight: 600; color: #333;"></div>
                    </div>
                    <div>
                        <small style="color: #888; text-transform: uppercase; font-weight: 600; font-size: 0.75em;">Main Category</small>
                        <div id="label_Category" style="font-weight: 600; color: #333;"></div>
                    </div>
                    <div>
                        <small style="color: #888; text-transform: uppercase; font-weight: 600; font-size: 0.75em;">Cost Price</small>
                        <div id="label_Cost" style="font-weight: 600; color: #2e7d32;"></div>
                    </div>
                </div>

                <label>Product Name</label>
                <input type="text" id="editProd_Name" name="prodName" required class="filter-input">
                
                <label style="margin-top: 10px; display: block;">Selling Price (RM)</label>
                <input type="number" step="0.01" id="editProd_ListPrice" name="prodListPrice" required class="filter-input">

                <div id="editFoodFields" style="display:none; border-top: 1px solid #eee; margin-top: 15px; padding-top: 15px;">
                    <label>Food Sub-Category</label>
                    <select name="foodType" id="editFoodType" class="filter-select">
                        <?php foreach ($foodSubCats as $fcat): ?>
                            <option value="<?= $fcat ?>"><?= $fcat ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-row" style="margin-top: 10px;">
                        <div style="flex: 1;"><label>Expiry</label><input type="date" name="expiryDate" id="editExpiryDate" class="filter-input"></div>
                        <div style="flex: 1;"><label>Storage</label>
                            <select name="storageInstructions" id="editStorage" class="filter-select">
                                <?php foreach ($storageMethods as $method): ?>
                                    <option value="<?= $method ?>"><?= $method ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="editNonFoodFields" style="display:none; border-top: 1px solid #eee; margin-top: 15px; padding-top: 15px;">
                    <label>Non-Food Category Type</label>
                    <select name="nonFoodCategory" id="editNonFoodCat" class="filter-select">
                        <?php foreach ($nonFoodSubCats as $nfcat): ?>
                            <option value="<?= $nfcat ?>"><?= $nfcat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-edit modal-btn-full" style="margin-top: 25px;">Update Product</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>