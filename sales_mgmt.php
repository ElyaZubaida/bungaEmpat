<?php
include 'db_connection.php';

// --- 1. EXISTING FETCH LOGIC ---
$cust_stid = oci_parse($conn, "SELECT CUST_ID, CUST_NAME FROM CUSTOMER");
oci_execute($cust_stid);
$customers = [];
while ($row = oci_fetch_assoc($cust_stid)) { $customers[] = $row; }

$staff_stid = oci_parse($conn, "SELECT STAFF_ID, STAFF_NAME FROM STAFF");
oci_execute($staff_stid);
$staff_members = [];
while ($row = oci_fetch_assoc($staff_stid)) { $staff_members[] = $row; }

// --- 2. NEW: BRANCH AUDIT QUERY (YOUR COMPLEX SQL) ---
$auditStart = $_GET['auditStart'] ?? date('Y-m-01');
$auditEnd   = $_GET['auditEnd']   ?? date('Y-m-d');

$queryAudit = "SELECT 
                B.BRANCH_ID,
                (SELECT BR.BRANCH_NAME FROM BRANCH BR WHERE BR.BRANCH_ID = B.BRANCH_ID) AS BRANCH_NAME,
                COALESCE(SUM(PS.PS_QUANTITY), 0) AS TOTAL_UNITS_SOLD,
                TO_CHAR(COALESCE(SUM(P.PROD_LISTPRICE * PS.PS_QUANTITY), 0), '999,990.00') AS TOTAL_REVENUE,
                TO_CHAR(COALESCE(SUM((P.PROD_LISTPRICE - P.PROD_NETPRICE) * PS.PS_QUANTITY), 0), '999,990.00') AS TOTAL_PROFIT
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
while ($row = oci_fetch_assoc($stidAudit)) { $auditData[] = $row; }

$all_time_profit = 0;
foreach ($auditData as $row) {
    // We strip the commas from the TO_CHAR result to make it a number again
    $numeric_profit = (float)str_replace(',', '', $row['TOTAL_PROFIT']);
    $all_time_profit += $numeric_profit;
}

// --- 3. STANDARD SALES LIST ---
$querySales = "SELECT s.SALE_ID, s.SALE_DATE, s.SALE_AMOUNT, s.SALE_GRANDAMOUNT, s.SALE_PAYMENTTYPE, 
                      s.CUST_ID, s.STAFF_ID, s.PROMO_ID, ps.PS_QUANTITY, ps.PS_SUBPRICE
               FROM SALE s
               JOIN PRODUCT_SALE ps ON s.SALE_ID = ps.SALE_ID
               ORDER BY s.SALE_ID DESC";

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
    <script>
        function showView(viewType) {
            const standardView = document.getElementById('standard-view');
            const auditView = document.getElementById('audit-view');
            const auditFilters = document.getElementById('audit-filters');
            const title = document.getElementById('view-title');

            if (viewType === 'AUDIT') {
                standardView.style.display = 'none';
                auditView.style.display = 'block';
                auditFilters.style.display = 'flex';
                title.innerText = "Branch Profit Audit Report";
            } else {
                standardView.style.display = 'block';
                auditView.style.display = 'none';
                auditFilters.style.display = 'none';
                title.innerText = "Recent Transactions";
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('auditStart')) showView('AUDIT');
        };
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Sales Management</h1>
        <button class="btn-add" onclick="window.location.href='create_sale.php'">+ Create New Sale</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card" onclick="showView('STANDARD')" style="cursor:pointer;">
            <h3 style="color: #fd79a8;">Total Transactions</h3>
            <span class="stat-number"  style="color: #fd79a8;"><?= count($sales); ?></span>
        </div>

        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3 style="color: #4CAF50;">Total Revenue</h3>
            <span class="stat-number" style="color: #4CAF50;">RM <?= number_format($total_revenue_stats, 2); ?></span>
        </div>

        <div class="stat-card" onclick="showView('AUDIT')" style="border-left-color: #4361ee; cursor:pointer;">
            <h3 style="color: #4361ee;">All-Time Total Profit</h3>
            <span class="stat-number" style="color: #4361ee;">RM <?= number_format($all_time_profit, 2); ?></span>
            <small style="display:block; color: #4361ee; font-weight:600;">View Branch Breakdown</small>
        </div>
    </div>
    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin-bottom: 15px;">Recent Transactions</h2>

    <div id="audit-filters" class="filter-container" style="display:none;">
        <form method="GET" style="display: contents;">
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" name="auditStart" value="<?= $auditStart ?>">
            </div>
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" name="auditEnd" value="<?= $auditEnd ?>">
            </div>
            <div class="filter-group">
                <label style="visibility: hidden;">Align</label>
                <button type="submit" class="btn-filter">Generate Report</button>
            </div>
        </form>
    </div>

    <div id="standard-view" class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Date</th><th>Grand Total</th><th>Payment</th><th>Qty</th><th>Customer</th><th>Staff</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale) : ?>
                <tr>
                    <td><?= $sale['SALE_ID']; ?></td>
                    <td><?= $sale['SALE_DATE']; ?></td>
                    <td style="font-weight:600;">RM <?= number_format($sale['SALE_GRANDAMOUNT'], 2); ?></td>
                    <td>
                        <span class="badge" style="<?= ($sale['SALE_PAYMENTTYPE'] == 'Cash') ? 'background:#e8f5e9; color:#2e7d32;' : 'background:#e3f2fd; color:#1565c0;'; ?> padding: 4px 10px; border-radius:4px;">
                            <?= $sale['SALE_PAYMENTTYPE']; ?>
                        </span>
                    </td>
                    <td><?= $sale['PS_QUANTITY']; ?></td>
                    <td><?= $sale['CUST_ID'] ?: '-'; ?></td>
                    <td><?= $sale['STAFF_ID']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="audit-view" class="table-container" style="display:none;">
        <table>
            <thead>
                <tr style="background:#e3f2fd;">
                    <th>Branch ID</th>
                    <th>Branch Name</th>
                    <th>Units Sold</th>
                    <th>Total Revenue</th>
                    <th>Total Profit</th>
                </tr>
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
</body>
</html>