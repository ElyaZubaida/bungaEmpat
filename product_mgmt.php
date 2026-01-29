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

// --- 2. QUERY FOR STANDARD DISPLAY ---
$queryStandard = "SELECT p.PROD_ID, p.PROD_NAME, p.PROD_LISTPRICE, p.PROD_NETPRICE, p.PROD_BRAND, p.PROD_CATEGORY, 
                         fp.FOOD_CATEGORY, fp.EXPIRY_DATE, fp.STORAGE_INSTRUCTIONS, nfp.NONFOOD_CATEGORY
                  FROM PRODUCT p
                  LEFT JOIN FOOD_PRODUCT fp ON p.PROD_ID = fp.PROD_ID
                  LEFT JOIN NONFOOD_PRODUCT nfp ON p.PROD_ID = nfp.PROD_ID
                  ORDER BY p.PROD_ID ASC";

// --- 3. QISTINA COMPLEX QUERY (EXPIRY) ---
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

// --- 4. AMIRA COMPLEX QUERY (1) ---
$perfBranch = $_GET['perfBranch'] ?? 'Bunga Empat UiTM Shah Alam';
$perfStart  = $_GET['perfStart'] ?? date('Y-m-01');
$perfEnd    = $_GET['perfEnd'] ?? date('Y-m-d');

$queryPerf = "SELECT p.PROD_ID, p.PROD_NAME, p.PROD_CATEGORY, SUM(ps.PS_QUANTITY) AS UNITS_SOLD, p.PROD_LISTPRICE,
                TO_CHAR(SUM(ps.PS_SUBPRICE), '999,999.00') AS \"TOTAL REVENUE\",
                (SELECT sup.SUPP_NAME 
                FROM SUPPLIER sup 
                WHERE sup.SUPP_ID = p.SUPP_ID) AS SUPPLIER
              FROM PRODUCT p
              JOIN PRODUCT_SALE ps ON p.PROD_ID = ps.PROD_ID
              JOIN SALE s ON ps.SALE_ID = s.SALE_ID
              JOIN STAFF st ON s.STAFF_ID = st.STAFF_ID
              JOIN BRANCH b ON st.BRANCH_ID = b.BRANCH_ID
              WHERE b.BRANCH_NAME = :input_branch
              AND TRUNC(s.SALE_DATE) BETWEEN TO_DATE(:start_dt, 'YYYY-MM-DD') 
              AND TO_DATE(:end_dt, 'YYYY-MM-DD')
              GROUP BY p.PROD_ID, p.PROD_NAME, p.PROD_CATEGORY, p.PROD_LISTPRICE, p.SUPP_ID
              ORDER BY SUM(ps.PS_QUANTITY) DESC";

$stidPerf = oci_parse($conn, $queryPerf);
oci_bind_by_name($stidPerf, ":input_branch", $perfBranch);
oci_bind_by_name($stidPerf, ":start_dt", $perfStart);
oci_bind_by_name($stidPerf, ":end_dt", $perfEnd);
oci_execute($stidPerf);

// Execution phase
$stid1 = oci_parse($conn, $queryStandard);
oci_execute($stid1);
$standardProducts = [];
while ($row = oci_fetch_assoc($stid1)) { $standardProducts[] = $row; }

$stid2 = oci_parse($conn, $queryExpiry);
oci_execute($stid2);
$expiryAlerts = [];
$uniqueBranches = []; 
while ($row = oci_fetch_assoc($stid2)) { 
    $expiryAlerts[] = $row; 
    if(!in_array($row['BRANCH_NAME'], $uniqueBranches)) $uniqueBranches[] = $row['BRANCH_NAME'];
}

$performanceData = [];
while ($row = oci_fetch_assoc($stidPerf)) { $performanceData[] = $row; }

$bListStid = oci_parse($conn, "SELECT BRANCH_NAME FROM BRANCH");
oci_execute($bListStid);
$allBranches = [];
while($b = oci_fetch_assoc($bListStid)) { $allBranches[] = $b['BRANCH_NAME']; }

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
            const views = ['standard-view', 'expiry-view', 'perf-view'];
            const filters = ['expiry-filters', 'perf-filters'];
            const title = document.getElementById('view-title');

            views.forEach(v => { if(document.getElementById(v)) document.getElementById(v).style.display = 'none'; });
            filters.forEach(f => { if(document.getElementById(f)) document.getElementById(f).style.display = 'none'; });

            if (viewType === 'EXPIRY') {
                document.getElementById('expiry-view').style.display = 'block';
                document.getElementById('expiry-filters').style.display = 'flex';
                title.innerText = "Expiry Alerts (Next 7 Days)";
            } else if (viewType === 'PERFORMANCE') {
                document.getElementById('perf-view').style.display = 'block';
                document.getElementById('perf-filters').style.display = 'flex';
                title.innerText = "Product Sales Performance";
            } else {
                document.getElementById('standard-view').style.display = 'block';
                title.innerText = "General Product List";
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('perfBranch')) {
                showView('PERFORMANCE');
            } else {
                showView('STANDARD');
            }
        };

        function confirmDelete(id) { 
            if(confirm('Are you sure you want to delete product ' + id + '?')) {
                window.location.href = 'delete_product.php?prod_id=' + id; 
            }
        }

        function openAddProductModal() { document.getElementById("addProductModal").style.display = "flex"; }
        function closeModal() {
            document.getElementById("addProductModal").style.display = "none";
            document.getElementById("editProductModal").style.display = "none";
        }
        function openEditProductModal(id, name, listPrice, category) {
            document.getElementById("editProductModal").style.display = "flex";
            document.getElementById("editProd_ID").value = id;
            document.getElementById("editProd_Name").value = name;
            document.getElementById("editProd_ListPrice").value = listPrice;
            document.getElementById("editProd_Category").value = category;
        }
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
            <h3 style="color: #fd79a8;">Total Products</h3><span class="stat-number" style="color: #fd79a8;"><?= $totalProducts; ?></span>
        </div>
        <div class="stat-card" onclick="showView('EXPIRY')" style="border-left-color: #f44336; cursor:pointer; background: #fff5f5;">
            <h3 style="color: #c62828;">Urgent Expiry</h3><span class="stat-number" style="color: #f44336;"><?= count($expiryAlerts); ?></span>
        </div>
        <div class="stat-card" onclick="showView('PERFORMANCE')" style="border-left-color: #4361ee; cursor:pointer;">
            <h3 style="color: #4361ee;">Product Sales Performance</h3><span class="stat-number" style="color: #4361ee;"><?= count($performanceData); ?></span>
        </div>
    </div>

    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin-bottom: 15px;">General Product List</h2>

    <div id="expiry-filters" class="filter-container" style="display:none;">
        <div class="filter-group">
            <label>Branch Location</label>
            <select id="filterBranch">
                <option value="ALL">All Branches</option>
                <?php foreach($uniqueBranches as $branch): ?><option value="<?= $branch ?>"><?= $branch ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="perf-filters" class="filter-container" style="display:none;">
        <form method="GET" style="display: contents;">
            <div class="filter-group">
                <label>Branch Location</label>
                <select name="perfBranch" style="min-width: 250px;">
                    <?php foreach($allBranches as $bName): ?>
                        <option value="<?= $bName ?>" <?= ($perfBranch == $bName) ? 'selected' : '' ?>><?= $bName ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" name="perfStart" value="<?= $perfStart ?>">
            </div>
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" name="perfEnd" value="<?= $perfEnd ?>">
            </div>
            <div class="filter-group">
                <label style="visibility: hidden;">Align</label>
                <button type="submit" class="btn-filter">Apply</button>
            </div>
        </form>
    </div>

    <div id="standard-view" class="table-container">
        <table>
            <thead><tr><th>ID</th><th>Product Name</th><th>Price</th><th>Brand</th><th>Category</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($standardProducts as $p): ?>
                <tr>
                    <td><?= $p['PROD_ID'] ?></td>
                    <td><strong><?= $p['PROD_NAME'] ?></strong></td>
                    <td>RM <?= number_format($p['PROD_LISTPRICE'], 2) ?></td>
                    <td><?= $p['PROD_BRAND'] ?></td>
                    <td><?= $p['PROD_CATEGORY'] ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditProductModal('<?= $p['PROD_ID'] ?>', '<?= addslashes($p['PROD_NAME']) ?>', '<?= $p['PROD_LISTPRICE'] ?>', '<?= $p['PROD_CATEGORY'] ?>')">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $p['PROD_ID'] ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="expiry-view" class="table-container" style="display:none;">
        <table>
            <thead><tr><th>ID</th><th>Product Name</th><th>Expiry Date</th><th>Branch</th><th>Stock</th></tr></thead>
            <tbody>
                <?php foreach($expiryAlerts as $a): ?>
                <tr>
                    <td><?= $a['PROD_ID'] ?></td>
                    <td><?= $a['PROD_NAME'] ?></td>
                    <td style="color:red; font-weight:bold;"><?= $a['EXPIRY_DATE'] ?></td>
                    <td><?= $a['BRANCH_NAME'] ?></td>
                    <td><?= $a['STOCK_QUANTITY'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="perf-view" class="table-container" style="display:none;">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Units Sold</th>
                    <th>Unit Price</th>
                    <th>Total Revenue</th>
                    <th>Supplier</th></tr>
                </thead>
            <tbody>
                <?php foreach ($performanceData as $row) : ?>
                <tr>
                    <td><strong><?= $row['PROD_NAME'] ?></strong></td>
                    <td><?= $row['PROD_CATEGORY'] ?></td>
                    <td><?= $row['UNITS_SOLD'] ?></td>
                    <td style="color: #7d5a5a;">RM <?= number_format($row['PROD_LISTPRICE'], 2) ?></td>
                    <td style="color:#2e7d32; font-weight:bold;">RM <?= $row['TOTAL REVENUE'] ?></td>
                    <td><?= $row['SUPPLIER'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addProductModal" class="modal"><div class="modal-content">...</div></div>
<div id="editProductModal" class="modal"><div class="modal-content">...</div></div>

</body>
</html>