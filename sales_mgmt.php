<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

$currentStaffID = $_SESSION['staff_id'];

// --- 1. FETCH DATA FOR DROPDOWNS ---
$cust_stid = oci_parse($conn, "SELECT CUST_ID, CUST_NAME FROM CUSTOMER ORDER BY CUST_NAME");
oci_execute($cust_stid);
$allCustomers = [];
while ($row = oci_fetch_assoc($cust_stid)) { $allCustomers[] = $row; }

$p_stid = oci_parse($conn, "SELECT PROD_ID, PROD_NAME, PROD_LISTPRICE FROM PRODUCT ORDER BY PROD_NAME");
oci_execute($p_stid);
$allProducts = []; 
while($r = oci_fetch_assoc($p_stid)) { $allProducts[] = $r; }

$pr_stid = oci_parse($conn, "SELECT PROMO_ID, PROMO_AMOUNT FROM PROMOTION ORDER BY PROMO_ID");
oci_execute($pr_stid);
$allPromos = []; 
while($r = oci_fetch_assoc($pr_stid)) { $allPromos[] = $r; }

// --- 2. GENERATE NEXT SALE ID (S-XXXXX) ---
$id_q = "SELECT MAX(TO_NUMBER(SUBSTR(SALE_ID, 3))) AS MAX_VAL FROM SALE";
$id_stid = oci_parse($conn, $id_q);
oci_execute($id_stid);
$id_row = oci_fetch_assoc($id_stid);

$max_found = (int)$id_row['MAX_VAL'];
$next_num = ($max_found > 0) ? $max_found + 1 : 10001;
$next_sale_id = "S-" . $next_num;

oci_free_statement($id_stid);

// --- 3. ELYA COMPLEX QUERY (PROFIT REPORT) ---
$auditStart = $_GET['auditStart'] ?? date('Y-m-01');
$auditEnd   = $_GET['auditEnd']   ?? date('Y-m-d');

$queryAudit = "SELECT 
                B.BRANCH_ID,
                (SELECT BR.BRANCH_NAME 
                FROM BRANCH BR 
                WHERE BR.BRANCH_ID = B.BRANCH_ID) AS BRANCH_NAME,
                COALESCE(SUM(PS.PS_QUANTITY), 0) AS TOTAL_UNITS_SOLD,
                TO_CHAR(COALESCE(SUM(P.PROD_LISTPRICE * PS.PS_QUANTITY), 0), '999,990.00') AS TOTAL_REVENUE,
                TO_CHAR(COALESCE(SUM((P.PROD_LISTPRICE - P.PROD_NETPRICE) * PS.PS_QUANTITY), 0), '999,990.00') 
                AS TOTAL_PROFIT
               FROM BRANCH B
               LEFT JOIN STAFF ST ON B.BRANCH_ID = ST.BRANCH_ID
               LEFT JOIN SALE S ON ST.STAFF_ID = S.STAFF_ID 
                    AND S.SALE_DATE BETWEEN TO_DATE(:start_dt, 'YYYY-MM-DD') AND TO_DATE(:end_dt, 'YYYY-MM-DD')
               LEFT JOIN PRODUCT_SALE PS ON S.SALE_ID = PS.SALE_ID
               LEFT JOIN PRODUCT P ON PS.PROD_ID = P.PROD_ID
               GROUP BY B.BRANCH_ID
               ORDER BY COALESCE(SUM((P.PROD_LISTPRICE - P.PROD_NETPRICE) * PS.PS_QUANTITY), 0) DESC";

$stidAudit = oci_parse($conn, $queryAudit);
oci_bind_by_name($stidAudit, ":start_dt", $auditStart);
oci_bind_by_name($stidAudit, ":end_dt", $auditEnd);
oci_execute($stidAudit);

$auditData = [];
$all_time_profit = 0;
while ($row = oci_fetch_assoc($stidAudit)) { 
    $auditData[] = $row; 
    $all_time_profit += (float)str_replace(',', '', $row['TOTAL_PROFIT']);
}

// --- 4. STANDARD SALES LIST 
$querySales = "SELECT s.SALE_ID, s.SALE_DATE, s.SALE_GRANDAMOUNT, s.SALE_PAYMENTTYPE, 
                      s.CUST_ID, s.STAFF_ID, 
                      SUM(ps.PS_QUANTITY) AS TOTAL_QTY
               FROM SALE s
               JOIN PRODUCT_SALE ps ON s.SALE_ID = ps.SALE_ID
               GROUP BY s.SALE_ID, s.SALE_DATE, s.SALE_GRANDAMOUNT, s.SALE_PAYMENTTYPE, s.CUST_ID, s.STAFF_ID
               ORDER BY s.SALE_DATE DESC, s.SALE_ID DESC";

$stidSales = oci_parse($conn, $querySales);
oci_execute($stidSales);

$sales = [];
$total_revenue_stats = 0;
while ($row = oci_fetch_assoc($stidSales)) {
    $sales[] = $row;
    $total_revenue_stats += $row['SALE_GRANDAMOUNT'];
}

oci_close($conn);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Management | Bunga Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .sales-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .sales-table th { text-transform: uppercase; font-size: 0.7rem; color: #bfa2a2; letter-spacing: 1px; padding: 10px; }
        .sales-table td { background: #fff; padding: 12px; vertical-align: middle; border-bottom: 1px solid #f9f9f9; }
        .row-input { border: 1px solid #ffdeeb !important; border-radius: 8px !important; height: 38px !important; margin: 0 !important; width: 100%; }
        .checkout-summary { background: #fdf6f9; padding: 20px; border-radius: 15px; border: 2px dashed #ffdeeb; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
        .grand-total-amount { font-size: 2.2rem; font-weight: 700; color: #2e7d32; background: transparent; border: none; text-align: right; width: 220px; }
        .btn-add-row { background: transparent; color: #ff85a1; border: 2px dashed #ff85a1; width: 100%; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 600; margin-top: 10px; transition: 0.3s; }
        .btn-add-row:hover { background: #fff0f5; }
        .btn-remove { color: #ff5e5e; border: none; background: none; font-size: 1.5rem; cursor: pointer; }
    </style>
    <script>
        let productList = <?= json_encode($allProducts); ?>;

        // Function to toggle between Standard and Audit views
function showView(viewType) {
    const standardView = document.getElementById('standard-view');
    const auditView = document.getElementById('audit-view');
    const auditFilters = document.getElementById('audit-filters');
    const title = document.getElementById('view-title');

    if (viewType === 'AUDIT') {
        standardView.style.display = 'none';
        auditView.style.display = 'block';
        auditFilters.style.display = 'block'; // Shows the Analysis Card
        title.innerText = "Branch Profit Audit Report";
    } else {
        standardView.style.display = 'block';
        auditView.style.display = 'none';
        auditFilters.style.display = 'none';
        title.innerText = "Recent Transactions";
    }
}

// CRITICAL: This part ensures the view stays on the Audit Table after clicking filter
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    // If the URL has auditStart, it means the user just pressed the filter button
    if (urlParams.has('auditStart')) {
        showView('AUDIT');
    }
};
        function addProductRow() {
            const tbody = document.getElementById('productBody');
            const rowId = Date.now();
            const tr = document.createElement('tr');
            tr.id = `row_${rowId}`;
            tr.innerHTML = `
                <td>
                    <input list="prodList_${rowId}" name="prodId[]" onchange="updatePrice(${rowId}, this)" class="filter-input row-input" placeholder="Search product...">
                    <datalist id="prodList_${rowId}">${productList.map(p => `<option value="${p.PROD_ID}">${p.PROD_NAME}</option>`).join('')}</datalist>
                </td>
                <td><input type="text" id="price_${rowId}" readonly style="border:none; background:transparent; text-align:center; width:100%;"></td>
                <td><input type="number" name="qty[]" id="qty_${rowId}" value="1" min="1" oninput="calculateRow(${rowId})" class="filter-input row-input"></td>
                <td><input type="text" id="total_${rowId}" readonly style="border:none; background:transparent; font-weight:bold; text-align:right; width:100%; color:#2e7d32;"></td>
                <td><button type="button" class="btn-remove" onclick="removeRow(${rowId})">&times;</button></td>`;
            tbody.appendChild(tr);
        }

        function updatePrice(rowId, input) {
            const prod = productList.find(p => p.PROD_ID === input.value);
            if (prod) {
                document.getElementById(`price_${rowId}`).value = prod.PROD_LISTPRICE;
                calculateRow(rowId);
            }
        }

        function calculateRow(rowId) {
            const price = parseFloat(document.getElementById(`price_${rowId}`).value) || 0;
            const qty = parseInt(document.getElementById(`qty_${rowId}`).value) || 0;
            document.getElementById(`total_${rowId}`).value = (price * qty).toFixed(2);
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let subtotal = 0;
            document.querySelectorAll('[id^="total_"]').forEach(i => subtotal += parseFloat(i.value) || 0);
            const promo = document.getElementById('pos_Promo');
            const disc = parseFloat(promo.options[promo.selectedIndex].getAttribute('data-amount')) || 0;
            const grandTotal = Math.max(0, subtotal - disc);
            document.getElementById('grandTotalDisplay').value = grandTotal.toFixed(2);
            document.getElementById('hiddenGrandTotal').value = grandTotal.toFixed(2);
            document.getElementById('hiddenAmount').value = subtotal.toFixed(2);
        }

        function removeRow(rowId) { document.getElementById(`row_${rowId}`).remove(); updateGrandTotal(); }
        function openAddSaleModal() { document.getElementById("addSaleModal").style.display = "flex"; if(document.getElementById('productBody').children.length === 0) addProductRow(); }
        function closeModal() { document.getElementById("addSaleModal").style.display = "none"; }
    </script>
</head>
<body>
<div class="main-content">
    <div class="dashboard-header">
        <h1>Sales Management</h1>
        <button class="btn-add" onclick="openAddSaleModal()">+ Create New Sale</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card" onclick="showView('STANDARD')" style="cursor:pointer;">
            <h3>Total Transactions</h3>
            <span class="stat-number"><?= count($sales); ?></span>
        </div>
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <span class="stat-number">RM <?= number_format($total_revenue_stats, 2); ?></span>
        </div>
        <div class="stat-card" onclick="showView('AUDIT')" style="cursor:pointer; border-left-color: #4361ee;">
            <h3>Total Profit</h3>
            <span class="stat-number" style="color: #4361ee;">RM <?= number_format($all_time_profit, 2); ?></span>
        </div>
    </div>

    <!-- Filter Card for profit report Parameters -->
    <div id="audit-filters" class="analysis-filter-card" style="display:none;">
    <div class="analysis-header">
        <h4>Branch Profit Audit Parameters</h4>
        <p>Review financial performance across branches for a specific date range.</p>
    </div>
    <form method="GET" class="filter-row">
        <div class="filter-field">
            <label>Start Date</label>
            <input type="date" name="auditStart" class="filter-input" value="<?= $auditStart ?>">
        </div>
        <div class="filter-field">
            <label>End Date</label>
            <input type="date" name="auditEnd" class="filter-input" value="<?= $auditEnd ?>">
        </div>
        <button type="submit" class="btn-analysis">Generate Audit</button>
    </form>
</div>

    <div id="standard-view" class="table-container">
        <table>
            <thead><tr><th>ID</th><th>Date</th><th>Grand Total</th><th>Payment</th><th>Qty</th><th>Customer</th><th>Staff</th></tr></thead>
            <tbody>
                <?php foreach ($sales as $s) : ?>
                <tr>
                    <td><strong><?= $s['SALE_ID']; ?></strong></td>
                    <td><?= $s['SALE_DATE']; ?></td>
                    <td style="font-weight:700; color: #2e7d32;">RM <?= number_format($s['SALE_GRANDAMOUNT'], 2); ?></td>
                    <td><span class="badge"><?= $s['SALE_PAYMENTTYPE']; ?></span></td>
                    <td><?= $s['TOTAL_QTY']; ?></td>
                    <td><?= $s['CUST_ID'] ?: 'Walk-in'; ?></td>
                    <td><?= $s['STAFF_ID']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- View for profit report -->
    <div id="audit-view" class="table-container" style="display:none;">
        <table>
            <thead style="background:#e3f2fd;">
                <tr><th>Branch ID</th><th>Branch Name</th><th>Units Sold</th><th>Revenue</th><th>Profit</th></tr>
            </thead>
            <tbody>
                <?php foreach ($auditData as $row) : ?>
                <tr>
                    <td><?= $row['BRANCH_ID']; ?></td>
                    <td><strong><?= $row['BRANCH_NAME']; ?></strong></td>
                    <td><?= $row['TOTAL_UNITS_SOLD']; ?></td>
                    <td style="color:#2e7d32; font-weight:bold;">RM <?= $row['TOTAL_REVENUE']; ?></td>
                    <td style="color:#1565c0; font-weight:bold;">RM <?= $row['TOTAL_PROFIT']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addSaleModal" class="modal">
    <div class="modal-content" style="max-width: 900px; border-radius: 30px; padding: 40px; border: none;">
        <div class="modal-header" style="border: none; padding-bottom: 0;">
            <h2 style="color: #ff85a1; font-size: 1.8rem;">New Transaction</h2>
            <span class="close-btn" onclick="closeModal()" style="font-size: 2rem;">&times;</span>
        </div>

        <div class="modal-body">
            <form action="add_sales.php" method="post" id="saleForm">
                <input type="hidden" name="staffId" value="<?= $currentStaffID ?>">

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px;">
                    <div style="background: #fdf6f9; padding: 15px; border-radius: 15px;">
                        <label style="color: #bfa2a2; font-size: 0.7rem; font-weight: 800; display: block; margin-bottom: 5px;">SALE ID</label>
                        <input type="text" value="<?= $next_sale_id ?>" readonly style="font-size: 1.2rem; font-weight: 700; color: #7d5a5a; border: none; background: transparent; padding: 0;">
                        <input type="hidden" name="saleID" value="<?= $next_sale_id ?>">
                    </div>
                    <div style="background: #fdf6f9; padding: 15px; border-radius: 15px;">
                        <label style="color: #bfa2a2; font-size: 0.7rem; font-weight: 800; display: block; margin-bottom: 5px;">CUSTOMER SEARCH</label>
                        <input list="customerList" name="custId" placeholder="Search by name or ID (Leave blank for Walk-in)" class="filter-input row-input" style="background: white;">
                        <datalist id="customerList">
                            <?php foreach($allCustomers as $c): ?>
                                <option value="<?= $c['CUST_ID'] ?>"><?= $c['CUST_NAME'] ?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <div style="max-height: 350px; overflow-y: auto; padding-right: 10px;">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width: 130px; text-align: center;">Price</th>
                                <th style="width: 100px; text-align: center;">Qty</th>
                                <th style="width: 150px; text-align: right;">Subtotal</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="productBody">
                            </tbody>
                    </table>
                </div>

                <button type="button" class="btn-add-row" onclick="addProductRow()">
                    <span style="font-size: 1.2rem; margin-right: 8px;">+</span> Add Another Item
                </button>

                <div style="display: flex; gap: 20px; margin-top: 30px; align-items: flex-start;">
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label style="color: #bfa2a2; font-size: 0.75rem; font-weight: 800; display: block; margin-bottom: 8px;">PAYMENT TYPE</label>
                            <select name="salePaymentType" class="filter-input row-input" required>
                                <option>Cash</option>
                                <option>Card</option>
                                <option>E-Wallet</option>
                            </select>
                        </div>
                        <div>
                            <label style="color: #bfa2a2; font-size: 0.75rem; font-weight: 800; display: block; margin-bottom: 8px;">APPLY PROMO</label>
                            <select name="promoId" id="pos_Promo" onchange="updateGrandTotal()" class="filter-input row-input">
                                <option value="" data-amount="0">No Promotion</option>
                                <?php foreach($allPromos as $pr): ?>
                                    <option value="<?= $pr['PROMO_ID'] ?>" data-amount="<?= $pr['PROMO_AMOUNT'] ?>">
                                        <?= $pr['PROMO_ID'] ?> (-RM <?= $pr['PROMO_AMOUNT'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="flex: 1.5;">
                        <div class="checkout-summary">
                            <div style="text-align: left;">
                                <span style="display: block; font-size: 0.8rem; font-weight: 800; color: #7d5a5a;">FINAL TOTAL</span>
                                <span style="font-size: 0.7rem; color: #bfa2a2;">Including all discounts</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span style="font-size: 1.8rem; font-weight: 800; color: #2e7d32; margin-right: 5px;">RM</span>
                                <input type="text" id="grandTotalDisplay" readonly value="0.00" class="total-amount-display">
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="saleGrandAmount" id="hiddenGrandTotal">
                <input type="hidden" name="saleAmount" id="hiddenAmount">

                <button type="submit" class="modal-btn-full" style="height: 60px; font-size: 1.2rem; background: #ff85a1; border-radius: 18px; margin-top: 30px; box-shadow: 0 10px 20px rgba(255, 133, 161, 0.2);">
                    Complete Payment & Save
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>