<?php
include 'db_connection.php';

// --- 1. SQL QUERY FOR EXPIRY COUNT ---
// This uses your exact logic: Finds food items expiring in the next 7 days that are still in stock
$expiryCountQuery = "SELECT COUNT(*) AS EXPIRING_TOTAL
                     FROM PRODUCT P
                     JOIN FOOD_PRODUCT FP ON P.PROD_ID = FP.PROD_ID
                     JOIN STOCK SK ON P.PROD_ID = SK.PROD_ID
                     WHERE FP.EXPIRY_DATE BETWEEN SYSDATE AND SYSDATE + 7 
                     AND SK.STOCK_QUANTITY > 0";

$stid = oci_parse($conn, $expiryCountQuery);
oci_execute($stid);
$expiryData = oci_fetch_assoc($stid);
$expiringSoon = $expiryData['EXPIRING_TOTAL'];

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Bunga Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="dashboard-header">
                <div>
                    <h1>Dashboard</h1>
                </div>
            </div>

            <div class="section-divider"></div>

            <div class="stats-grid">
                
                <div class="stat-card" 
                     onclick="window.location.href='product_mgmt.php'" 
                     style="border-left-color: #f44336; cursor: pointer; transition: transform 0.2s;">
                    <h3 style="color: #d32f2f;">Stock Expiry Alerts</h3>
                    <span class="stat-number" style="color: #f44336;"><?= $expiringSoon; ?></span>
                    <p style="font-size: 0.85em; color: #666; margin-top: 10px;">
                        Items expiring within 7 days.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>