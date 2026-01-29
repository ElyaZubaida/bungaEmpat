<?php
include 'db_connection.php';

$startDate = date('Y-m-01'); 
$endDate   = date('Y-m-d');

// --- 1. QISTINA: EXPIRY COUNT ---
$expiryCountQuery = "SELECT COUNT(*) AS EXPIRING_TOTAL
                     FROM PRODUCT P
                     JOIN FOOD_PRODUCT FP ON P.PROD_ID = FP.PROD_ID
                     JOIN STOCK SK ON P.PROD_ID = SK.PROD_ID
                     WHERE FP.EXPIRY_DATE BETWEEN SYSDATE AND SYSDATE + 7 
                     AND SK.STOCK_QUANTITY > 0";
$stidExp = oci_parse($conn, $expiryCountQuery);
oci_execute($stidExp);
$expiryData = oci_fetch_assoc($stidExp);
$expiringSoon = $expiryData['EXPIRING_TOTAL'];

// --- 2. ELYA: PROFIT REPORT ---
$queryAudit = "SELECT 
                B.BRANCH_ID,
                COALESCE(SUM((P.PROD_LISTPRICE - P.PROD_NETPRICE) * PS.PS_QUANTITY), 0) AS TOTAL_PROFIT
               FROM BRANCH B
               LEFT JOIN STAFF ST ON B.BRANCH_ID = ST.BRANCH_ID
               LEFT JOIN SALE S ON ST.STAFF_ID = S.STAFF_ID 
                    AND S.SALE_DATE BETWEEN TO_DATE(:sd, 'YYYY-MM-DD') AND TO_DATE(:ed, 'YYYY-MM-DD')
               LEFT JOIN PRODUCT_SALE PS ON S.SALE_ID = PS.SALE_ID
               LEFT JOIN PRODUCT P ON PS.PROD_ID = P.PROD_ID
               GROUP BY B.BRANCH_ID
               ORDER BY B.BRANCH_ID ASC";
$stidAudit = oci_parse($conn, $queryAudit);
oci_bind_by_name($stidAudit, ":sd", $startDate);
oci_bind_by_name($stidAudit, ":ed", $endDate);
oci_execute($stidAudit);

$branchIDs = []; $profitData = [];
while ($row = oci_fetch_assoc($stidAudit)) {
    $branchIDs[] = "ID: " . $row['BRANCH_ID'];
    $profitData[] = (float)$row['TOTAL_PROFIT'];
}

// --- 3. AMIRA: TOP PRODUCT ---
$queryTopProd = "SELECT p.PROD_NAME, SUM(ps.PS_QUANTITY) AS TOTAL_SOLD
                 FROM PRODUCT p
                 JOIN PRODUCT_SALE ps ON p.PROD_ID = ps.PROD_ID
                 GROUP BY p.PROD_NAME
                 ORDER BY TOTAL_SOLD DESC
                 FETCH FIRST 1 ROWS ONLY";
$stidTop = oci_parse($conn, $queryTopProd);
oci_execute($stidTop);
$topProd = oci_fetch_assoc($stidTop);

// --- 4. DINA: TOP SPENDERS ---
$queryChampions = "SELECT 
                    b.Branch_ID, 
                    c.Cust_Name AS TOP_STUDENT, 
                    TO_CHAR(SUM(ps.PS_SubPrice), '99,999.00') AS TOTAL_SPENT
                  FROM Branch b
                  JOIN Staff st ON b.Branch_ID = st.Branch_ID
                  JOIN Sale s ON st.Staff_ID = s.Staff_ID
                  JOIN Customer c ON s.Cust_ID = c.Cust_ID
                  JOIN Product_Sale ps ON s.Sale_ID = ps.Sale_ID
                  GROUP BY b.Branch_ID, c.Cust_Name
                  HAVING SUM(ps.PS_SubPrice) >= ALL (
                    SELECT SUM(ps2.PS_SubPrice)
                    FROM Staff st2
                    JOIN Sale s2 ON st2.Staff_ID = s2.Staff_ID
                    JOIN Product_Sale ps2 ON s2.Sale_ID = ps2.Sale_ID
                    WHERE st2.Branch_ID = b.Branch_ID
                    GROUP BY s2.Cust_ID
                  )
                  ORDER BY b.Branch_ID ASC";
$stidChamp = oci_parse($conn, $queryChampions);
oci_execute($stidChamp);
$champions = [];
while ($row = oci_fetch_assoc($stidChamp)) { $champions[] = $row; }

oci_close($conn);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Bunga Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-content"> <div class="db-wrapper"> <div class="dashboard-header">
            <h1>Dashboard</h1>
        </div>
        
        <div class="section-divider"></div>

        <div class="db-stats-grid">
            <div class="db-stat-card" style="border-left-color: #f44336;">
                <h3 style="color: #f44336; font-size: 0.9rem;">EXPIRY ALERTS</h3>
                <span class="db-stat-number db-text-danger"><?= $expiringSoon; ?></span>
            </div>
            <div class="db-stat-card" style="border-left-color: #4CAF50;">
                <h3 style="color:#4CAF50; font-size: 0.9rem;">TOP PRODUCT</h3>
                <span class="db-stat-number" style="font-size: 1.2rem; color:#4CAF50;"><?= $topProd['PROD_NAME']; ?></span>
            </div>
        </div>

        <div class="db-flex-row">
            <div class="db-container db-chart-box">
                <h2 style="font-size: 1.1rem; margin-bottom:10px;">Profit Analysis by Branch</h2>
                <div style="flex: 1; min-height: 0;">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>

            <div class="db-container db-list-box">
                <h2 style="font-size: 1.1rem; margin-bottom:10px;">Top Spenders</h2>
                <div class="db-scroll-area">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Branch ID</th>
                                <th>Customer</th>
                                <th>Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($champions as $champ): ?>
                            <tr>
                                <td><?= $champ['BRANCH_ID']; ?></td>
                                <td><?= $champ['TOP_STUDENT']; ?></td>
                                <td class="db-text-success">RM <?= $champ['TOTAL_SPENT']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> 
</div>

    <script>
        const ctx = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($branchIDs); ?>,
                datasets: [{
                    label: 'Profit (RM)',
                    data: <?php echo json_encode($profitData); ?>,
                    backgroundColor: 'rgba(67, 97, 238, 0.7)',
                    borderColor: '#4361ee',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>