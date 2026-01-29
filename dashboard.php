<?php
include 'db_connection.php';
include 'sidebar.php';

// --- BAHAGIAN 1: PHP QUERIES (Kira Data Sebenar) ---

// 1. KIRA EXPIRY ALERT (Tamat tempoh dalam 7 hari)
$query1 = "SELECT COUNT(*) AS COUNT_EXPIRY
           FROM PRODUCT P
           JOIN FOOD_PRODUCT FP ON P.PROD_ID = FP.PROD_ID
           JOIN STOCK SK ON P.PROD_ID = SK.PROD_ID
           WHERE FP.EXPIRY_DATE BETWEEN SYSDATE AND SYSDATE + 7 
           AND SK.STOCK_QUANTITY > 0";
$stid1 = oci_parse($conn, $query1);
oci_execute($stid1);
$row1 = oci_fetch_assoc($stid1);
$totalExpiry = $row1['COUNT_EXPIRY'];

// 2. KIRA TOTAL SALES
$query2 = "SELECT COUNT(*) AS COUNT_SALES FROM SALE";
$stid2 = oci_parse($conn, $query2);
oci_execute($stid2);
$row2 = oci_fetch_assoc($stid2);
$totalSales = $row2['COUNT_SALES'];

// 3. KIRA TOTAL CUSTOMERS
$query3 = "SELECT COUNT(*) AS COUNT_CUST FROM CUSTOMER";
$stid3 = oci_parse($conn, $query3);
oci_execute($stid3);
$row3 = oci_fetch_assoc($stid3);
$totalCust = $row3['COUNT_CUST'];

// 4. KIRA TOTAL PRODUCTS
$query4 = "SELECT COUNT(*) AS COUNT_PROD FROM PRODUCT";
$stid4 = oci_parse($conn, $query4);
oci_execute($stid4);
$row4 = oci_fetch_assoc($stid4);
$totalProd = $row4['COUNT_PROD'];

// Bebaskan memory
oci_free_statement($stid1);
oci_free_statement($stid2);
oci_free_statement($stid3);
oci_free_statement($stid4);
oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Bunga Admin</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>🌸 Bunga Management Dashboard</h1>
            <p style="color: #888; margin-top: -10px;">Welcome back, Admin!</p>
        </div>

        <div class="section-divider"></div>

        <div class="stats-grid">
            
            <div class="stat-card" onclick="window.location.href='product_mgmt.php'" 
                 style="border-left: 5px solid #ff7675; cursor: pointer;">
                <h3 style="color: #ff7675;">⚠️ Expiry Alerts</h3>
                <div class="stat-number"><?= $totalExpiry; ?></div>
                <p>Items expiring within 7 days.</p>
            </div>

            <div class="stat-card" onclick="window.location.href='sales_mgmt.php'" 
                 style="border-left: 5px solid #55efc4; cursor: pointer;">
                <h3 style="color: #00b894;">💰 Total Sales</h3>
                <div class="stat-number"><?= $totalSales; ?></div>
                <p>Total transactions made.</p>
            </div>

            <div class="stat-card" onclick="window.location.href='customer_mgmt.php'" 
                 style="border-left: 5px solid #74b9ff; cursor: pointer;">
                <h3 style="color: #0984e3;">👥 Customers</h3>
                <div class="stat-number"><?= $totalCust; ?></div>
                <p>Registered members.</p>
            </div>

            <div class="stat-card" onclick="window.location.href='product_mgmt.php'" 
                 style="border-left: 5px solid #a29bfe; cursor: pointer;">
                <h3 style="color: #6c5ce7;">📦 Total Products</h3>
                <div class="stat-number"><?= $totalProd; ?></div>
                <p>Items in inventory.</p>
            </div>

        </div>
        
        <div style="margin-top: 40px; text-align: center; color: #bfa2a2;">
            <p>Select a card above to manage data ✨</p>
        </div>

    </div>
</body>
</html>