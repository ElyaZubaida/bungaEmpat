<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

$currentStaffID = $_SESSION['staff_id'];

// 1. FETCH ORDERS 
$query = "SELECT o.ORDER_ID, 
                 o.ORDER_DATE, 
                 o.ORDER_QUANTITY, 
                 o.EXPECTED_DELIVERY, 
                 o.ORDER_AMOUNT, 
                 s.SUPP_NAME, 
                 p.PROD_NAME, 
                 b.BRANCH_NAME, 
                 st.STAFF_NAME
          FROM SUPPLIER_ORDER o
          JOIN SUPPLIER s ON o.SUPP_ID = s.SUPP_ID
          JOIN PRODUCT p ON o.PROD_ID = p.PROD_ID
          JOIN STAFF st ON o.STAFF_ID = st.STAFF_ID
          JOIN BRANCH b ON st.BRANCH_ID = b.BRANCH_ID
          ORDER BY o.ORDER_DATE DESC";

$stid = oci_parse($conn, $query);
oci_execute($stid);
$orders = [];
$total_inv = 0;
while ($row = oci_fetch_assoc($stid)) {
    $orders[] = $row;
    $total_inv += $row['ORDER_AMOUNT'];
}

// 2. FETCH DATA FOR MODAL SEARCH
$p_stid = oci_parse($conn, "SELECT PROD_ID, PROD_NAME, PROD_NETPRICE FROM PRODUCT ORDER BY PROD_NAME");
oci_execute($p_stid);
$allProducts = []; while($r = oci_fetch_assoc($p_stid)) { $allProducts[] = $r; }

$s_stid = oci_parse($conn, "SELECT SUPP_ID, SUPP_NAME FROM SUPPLIER ORDER BY SUPP_NAME");
oci_execute($s_stid);
$allSuppliers = []; while($r = oci_fetch_assoc($s_stid)) { $allSuppliers[] = $r; }

// 3. AUTO-GENERATE ORDER ID (ORD-10001)
$id_q = "SELECT MAX(TO_NUMBER(SUBSTR(ORDER_ID, 5))) AS MAX_VAL FROM SUPPLIER_ORDER";
$id_stid = oci_parse($conn, $id_q);
oci_execute($id_stid);
$id_row = oci_fetch_assoc($id_stid);
$next_num = ($id_row['MAX_VAL']) ? $id_row['MAX_VAL'] + 1 : 10001;
$next_order_id = "ORD-" . $next_num;

oci_close($conn);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Orders | Bunga Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .procure-input { border: 1px solid #ffe0eb; border-radius: 12px; padding: 12px; width: 100%; box-sizing: border-box; }
        .form-row { display: flex; gap: 15px; margin-bottom: 20px; }
        .form-col { flex: 1; }
        .form-col label { font-size: 0.75rem; font-weight: 800; color: #bfa2a2; display: block; margin-bottom: 8px; text-transform: uppercase; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
    </style>
    <script>
        function openModal() { document.getElementById("orderModal").style.display = "flex"; }
        function closeModal() { document.getElementById("orderModal").style.display = "none"; }
        const prodPrices = <?= json_encode(array_column($allProducts, 'PROD_NETPRICE', 'PROD_ID')); ?>;

    const productData = <?= json_encode(array_column($allProducts, 'PROD_NETPRICE', 'PROD_ID')); ?>;

    function calculateTotalCost() {
        const prodId = document.getElementById('prodID').value;
        const qty = parseFloat(document.getElementById('orderQty').value) || 0;
        
        // Find the net price for the selected product
        const netPrice = productData[prodId] || 0;
        
        // Calculate total
        const total = (qty * netPrice).toFixed(2);
        
        // Update the amount input
        document.getElementById('orderAmount').value = total;
}
    </script>
</head>
<body>
<div class="main-content">
    <div class="dashboard-header">
        <h1>Supplier Orders</h1>
        <button class="btn-add" onclick="openModal()">+ New Procurement</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><h3>Total Orders</h3><span class="stat-number"><?= count($orders) ?></span></div>
        <div class="stat-card" style="border-left-color: #f44336;"><h3>Investment</h3><span class="stat-number">RM <?= number_format($total_inv, 2) ?></span></div>
    </div>

    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Amount</th>
                <th>Supplier</th>
                <th>Branch (Staff Loc.)</th> <th>Exp. Delivery</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong><?= $o['ORDER_ID'] ?></strong></td>
                <td style="color: #4361ee; font-weight: 600;"><?= $o['PROD_NAME'] ?></td>
                <td><?= $o['ORDER_QUANTITY'] ?></td>
                <td style="font-weight: 700; color: #f44336;">RM <?= number_format($o['ORDER_AMOUNT'], 2) ?></td>
                <td><?= $o['SUPP_NAME'] ?></td>
                <td><span class="badge" style="background:#e3f2fd; color:#1565c0;"><?= $o['BRANCH_NAME'] ?></span></td>
                <td><?= $o['EXPECTED_DELIVERY'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

    <div id="orderModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 800px; border-radius: 30px; padding: 40px;">
        <div class="modal-header">
            <h2 style="color: #ff85a1;">Create Procurement Order</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        
        <form action="add_supplier_order.php" method="post">
            <input type="hidden" name="staffID" value="<?= $currentStaffID ?>">

            <div class="form-row">
                <div class="form-col">
                    <label>Order ID</label>
                    <input type="text" name="orderID" value="<?= $next_order_id ?>" readonly class="procure-input" style="background:#fdf6f9; font-weight:700;">
                </div>
                <div class="form-col">
                    <label>Search Product</label>
                    <input list="pList" name="prodID" id="prodID" onchange="calculateTotalCost()" class="procure-input" placeholder="Type product name..." required>
                    <datalist id="pList">
                        <?php foreach($allProducts as $p): ?>
                            <option value="<?= $p['PROD_ID'] ?>"><?= $p['PROD_NAME'] ?></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label>Quantity</label>
                    <input type="number" name="orderQty" id="orderQty" oninput="calculateTotalCost()" class="procure-input" placeholder="0" required>
                </div>
                <div class="form-col">
                    <label>Total Amount (RM)</label>
                    <input type="number" step="0.01" name="orderAmt" id="orderAmount" class="procure-input" placeholder="0.00" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label>Supplier Vendor</label>
                    <select name="suppID" class="procure-input" required>
                        <option value="">-- Select Vendor --</option>
                        <?php foreach($allSuppliers as $s): ?>
                            <option value="<?= $s['SUPP_ID'] ?>"><?= $s['SUPP_NAME'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label>Expected Delivery</label>
                    <input type="date" name="expDate" class="procure-input" required>
                </div>
            </div>

            <div style="background: #fff9fb; padding: 15px; border-radius: 12px; border: 1px dashed #ffdeeb; margin-top: 10px;">
                <p style="margin:0; font-size: 0.85rem; color: #7d5a5a;">
                    <strong>Auto-Calculation:</strong> Total is based on the <code>PROD_NETPRICE</code> stored in the database. You can manually adjust the total if the supplier offered a discount.
                </p>
            </div>

            <button type="submit" class="modal-btn-full" style="background:#ff85a1; height:60px; font-weight:700; margin-top:20px;">
                Confirm Order
            </button>
        </form>
    </div>
</div>
</body>
</html>