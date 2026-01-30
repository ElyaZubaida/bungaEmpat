<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

$staffID = $_SESSION['staff_id'];

// 1. BRANCH CONTEXT
$branchQuery = "SELECT B.BRANCH_ID, B.BRANCH_NAME FROM BRANCH B 
                JOIN STAFF S ON B.BRANCH_ID = S.BRANCH_ID WHERE S.STAFF_ID = :stid";
$stidB = oci_parse($conn, $branchQuery);
oci_bind_by_name($stidB, ":stid", $staffID);
oci_execute($stidB);
$userBranch = oci_fetch_assoc($stidB);
$myBranchID = $userBranch['BRANCH_ID'];
$myBranchName = $userBranch['BRANCH_NAME'];

// 2. TOP PRODUCTS
$qTopProd = "SELECT P.PROD_NAME 
            FROM PRODUCT P 
            JOIN PRODUCT_SALE PS ON P.PROD_ID = PS.PROD_ID 
            JOIN SALE S ON PS.SALE_ID = S.SALE_ID 
            JOIN STAFF ST ON S.STAFF_ID = ST.STAFF_ID 
            WHERE ST.BRANCH_ID = :bid GROUP BY P.PROD_NAME 
            ORDER BY SUM(PS.PS_QUANTITY) DESC FETCH FIRST 1 ROWS ONLY";
$stidTP = oci_parse($conn, $qTopProd); oci_bind_by_name($stidTP, ":bid", $myBranchID); oci_execute($stidTP);
$topProd = oci_fetch_assoc($stidTP)['PROD_NAME'] ?? 'None';

// 3. TOP STAFF
$qTopStaff = "SELECT S.STAFF_NAME 
            FROM STAFF S 
            JOIN SALE SA ON S.STAFF_ID = SA.STAFF_ID 
            WHERE S.BRANCH_ID = :bid 
            GROUP BY S.STAFF_NAME 
            ORDER BY SUM(SA.SALE_GRANDAMOUNT) DESC FETCH FIRST 1 ROWS ONLY";
$stidTS = oci_parse($conn, $qTopStaff); oci_bind_by_name($stidTS, ":bid", $myBranchID); oci_execute($stidTS);
$topStaff = oci_fetch_assoc($stidTS)['STAFF_NAME'] ?? 'None';

// 4. TOP CUSTOMER
$qTopCust = "SELECT C.CUST_NAME 
            FROM CUSTOMER C 
            JOIN SALE S ON C.CUST_ID = S.CUST_ID 
            JOIN STAFF ST ON S.STAFF_ID = ST.STAFF_ID 
            WHERE ST.BRANCH_ID = :bid 
            GROUP BY C.CUST_NAME 
            ORDER BY SUM(S.SALE_GRANDAMOUNT) DESC FETCH FIRST 1 ROWS ONLY";
$stidTC = oci_parse($conn, $qTopCust); oci_bind_by_name($stidTC, ":bid", $myBranchID); oci_execute($stidTC);
$topCust = oci_fetch_assoc($stidTC)['CUST_NAME'] ?? 'None';

// 5. BRANCH PROFIT DATA FOR CHART
$queryProfit = "SELECT B.BRANCH_NAME, 
                       COALESCE(SUM((P.PROD_LISTPRICE - P.PROD_NETPRICE) * PS.PS_QUANTITY), 0) AS TOTAL_PROFIT
                FROM BRANCH B 
                LEFT JOIN STAFF ST ON B.BRANCH_ID = ST.BRANCH_ID
                LEFT JOIN SALE S ON ST.STAFF_ID = S.STAFF_ID 
                LEFT JOIN PRODUCT_SALE PS ON S.SALE_ID = PS.SALE_ID 
                LEFT JOIN PRODUCT P ON PS.PROD_ID = P.PROD_ID
                GROUP BY B.BRANCH_NAME 
                ORDER BY TOTAL_PROFIT DESC";

$stidP = oci_parse($conn, $queryProfit);
oci_execute($stidP);

$branchNames = []; 
$profitData = [];

while ($row = oci_fetch_assoc($stidP)) { 
    $branchNames[] = $row['BRANCH_NAME']; 
    $profitData[] = (float)$row['TOTAL_PROFIT']; 
}

// 6. STOCK EXPIRY ALERTS
$queryExp = "SELECT P.PROD_NAME, 
                    CASE 
                        WHEN FP.EXPIRY_DATE <= SYSDATE + 3 THEN 'URGENT'
                        ELSE 'WARNING'
                    END AS STATUS
             FROM PRODUCT P 
             JOIN FOOD_PRODUCT FP ON P.PROD_ID = FP.PROD_ID 
             JOIN STOCK SK ON P.PROD_ID = SK.PROD_ID 
             WHERE SK.BRANCH_ID = :bid 
             AND FP.EXPIRY_DATE BETWEEN SYSDATE AND SYSDATE + 14 
             AND SK.STOCK_QUANTITY > 0 
             ORDER BY FP.EXPIRY_DATE ASC";
             
$stidE = oci_parse($conn, $queryExp); oci_bind_by_name($stidE, ":bid", $myBranchID); oci_execute($stidE);
$expiryItems = []; while ($row = oci_fetch_assoc($stidE)) { $expiryItems[] = $row; }

oci_close($conn);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Top 3 Cards Row */
        .top-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .mini-card { padding: 15px; border-radius: 15px; background: white; border: 1px solid #e2e8f0; }
        .mini-card label { font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; display: block; }
        .mini-card span { font-size: 1rem; font-weight: 600; color: #1e293b; display: block; margin-top: 5px; }

        /* Main Grid */
        .dashboard-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
        .panel { background: white; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; }
        .panel h3 { font-size: 0.9rem; margin: 0 0 15px 0; font-weight: 600; color: #475569; }

        /* Chart & List heights to fit one screen */
        .chart-container { height: 280px; width: 100%; }
        .list-container { height: 280px; overflow-y: auto; }
        
        .list-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; }
        .date-label { background: #f8fafc; padding: 2px 8px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.7rem; }
        /* CUTE STATUS BADGES */
        .status-badge { 
            padding: 2px 12px; 
            border-radius: 12px; 
            font-size: 0.7rem; 
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-urgent { background: #ff4d6d; color: white; }
        .status-warning { background: #ffd166; color: #7d5a00; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="header-section">
        <h1>Dashboard Overview</h1>
        <p>Real-time performance for <b><?= $myBranchName ?></b></p>
    </div>

    <div class="top-row">
        <div class="mini-card" style="border-top: 4px solid #a2d2ff;">
            <label>Top Seller</label>
            <span><?= $topStaff ?></span>
        </div>
        <div class="mini-card" style="border-top: 4px solid #b7e4c7;">
            <label>Best Product</label>
            <span><?= $topProd ?></span>
        </div>
        <div class="mini-card" style="border-top: 4px solid #ffafcc;">
            <label>Valued Customer</label>
            <span><?= $topCust ?></span>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="panel">
            <h3>Branch Profit Comparison</h3>
            <div class="chart-container">
                <canvas id="profitChart"></canvas>
            </div>
        </div>

        <div class="panel">
            <h3>Stock Expiry Alerts</h3>
            <div class="list-container">
                <?php if(empty($expiryItems)): ?>
                    <p style="text-align:center; font-size:0.8rem; color:#ccc; margin-top:100px;">Fresh Inventory ✨</p>
                <?php else: ?>
                    <?php foreach($expiryItems as $item): ?>
                    <div class="list-item">
                        <span><?= $item['PROD_NAME'] ?></span>
                        <span class="status-badge <?= ($item['STATUS'] == 'URGENT') ? 'status-urgent' : 'status-warning' ?>">
                            <?= $item['STATUS'] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('profitChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($branchNames) ?>,
            datasets: [{
                data: <?= json_encode($profitData) ?>,
                backgroundColor: '#a2d2ff',
                borderRadius: 8,
                barThickness: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { font: { size: 9 } }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });
</script>

</body>
</html>