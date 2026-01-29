<?php
include 'db_connection.php';
// Fetch Customers
$cust_query = "SELECT CUST_ID, CUST_NAME FROM CUSTOMER";
$cust_stid = oci_parse($conn, $cust_query);
oci_execute($cust_stid);
$customers = [];
while ($row = oci_fetch_assoc($cust_stid)) { $customers[] = $row; }

// Fetch Staff
$staff_query = "SELECT STAFF_ID, STAFF_NAME FROM STAFF";
$staff_stid = oci_parse($conn, $staff_query);
oci_execute($staff_stid);
$staff_members = [];
while ($row = oci_fetch_assoc($staff_stid)) { $staff_members[] = $row; }

// Fetch Promotions
$promo_query = "SELECT PROMO_ID, PROMO_NAME FROM PROMOTION";
$promo_stid = oci_parse($conn, $promo_query);
oci_execute($promo_stid);
$promotions = [];
while ($row = oci_fetch_assoc($promo_stid)) { $promotions[] = $row; }

// Fetch Sales with Product Details
$query = "SELECT s.SALE_ID, s.SALE_DATE, s.SALE_AMOUNT, s.SALE_GRANDAMOUNT, s.SALE_PAYMENTTYPE, 
                 s.CUST_ID, s.STAFF_ID, s.PROMO_ID, ps.PS_QUANTITY, ps.PS_SUBPRICE
          FROM SALE s
          JOIN PRODUCT_SALE ps ON s.SALE_ID = ps.SALE_ID
          ORDER BY s.SALE_ID";

$stid = oci_parse($conn, $query);
oci_execute($stid);

$sales = [];
$total_revenue = 0;
$cash_sales = 0;
$card_sales = 0;

while ($row = oci_fetch_assoc($stid)) {
    $sales[] = $row;
    $total_revenue += $row['SALE_GRANDAMOUNT'];
    
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddSalesModal() {
            document.getElementById("addSalesModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("addSalesModal").style.display = "none";
        }
    </script>

</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h1>Sales Management</h1>
        </div>
        <button class="btn-add" onclick="window.location.href='create_sale.php'">+ Create New Sale</button>
    </div>

    <div class="section-divider"></div>

   <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Transactions</h3>
            <span class="stat-number"><?= count($sales); ?></span>
        </div>
        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Total Revenue</h3>
            <span class="stat-number" style="color: #4CAF50;">RM <?= number_format($total_revenue, 2); ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Grand Total</th>
                    <th>Payment</th>
                    <th>Qty</th>
                    <th>Sub Price</th>
                    <th>Customer</th>
                    <th>Staff</th>
                    <th>Promo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale) : ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $sale['SALE_ID']; ?></td>
                    <td><?= $sale['SALE_DATE']; ?></td>
                    <td>RM <?= number_format($sale['SALE_AMOUNT'], 2); ?></td>
                    <td style="font-weight:600;">RM <?= number_format($sale['SALE_GRANDAMOUNT'], 2); ?></td>
                    <td>
                        <?php 
                            // Dynamic Pill Colors based on Payment Type
                            $payType = strtoupper($sale['SALE_PAYMENTTYPE']);
                            $pillStyle = ($payType == 'CASH') ? 'background:#e8f5e9; color:#2e7d32;' : 'background:#e3f2fd; color:#1565c0;';
                        ?>
                        <span style="padding: 4px 10px; border-radius: 4px; font-size: 0.8em; font-weight: 500; <?= $pillStyle; ?>">
                            <?= $sale['SALE_PAYMENTTYPE']; ?>
                        </span>
                    </td>
                    <td><?= $sale['PS_QUANTITY']; ?></td>
                    <td>RM <?= number_format($sale['PS_SUBPRICE'], 2); ?></td>
                    <td><?= $sale['CUST_ID'] ?: '-'; ?></td>
                    <td><?= $sale['STAFF_ID']; ?></td>
                    <td><?= $sale['PROMO_ID'] ?: 'None'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>