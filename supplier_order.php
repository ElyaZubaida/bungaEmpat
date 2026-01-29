<?php
include 'db_connection.php';

// Fetch all supplier orders
$query = "SELECT ORDER_ID, ORDER_DATE, ORDER_QUANTITY, EXPECTED_DELIVERY, ORDER_AMOUNT, SUPP_ID, STAFF_ID 
          FROM SUPPLIER_ORDER 
          ORDER BY ORDER_DATE DESC";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$supplier_orders = [];
$total_cost = 0;
$total_qty = 0;

while ($row = oci_fetch_assoc($stid)) {
    $supplier_orders[] = $row;
    $total_cost += $row['ORDER_AMOUNT'];
    $total_qty += $row['ORDER_QUANTITY'];
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Order Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddOrderModal() {
            document.getElementById("addOrderModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("addOrderModal").style.display = "none";
        }

        // Close modal if user clicks outside of the box
        window.onclick = function(event) {
            if (event.target.className === 'modal') closeModal();
        }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h1 style="margin: 0;">Supplier Order Management</h1>
            <p style="color: #888; margin: 5px 0 0 0; font-size: 0.9em;">Track and manage stock orders from vendors</p>
        </div>
        <button class="btn-add" onclick="openAddOrderModal()">+ New Order</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Orders</h3>
            <span class="stat-number"><?= count($supplier_orders); ?></span>
        </div>

        <div class="stat-card" style="border-left-color: #f44336;">
            <h3>Total Amount</h3>
            <span class="stat-number" style="font-size: 1.5em;">RM <?= number_format($total_cost, 2); ?></span>
        </div>
        
        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Total Qty</h3>
            <span class="stat-number"><?= $total_qty; ?></span>
        </div>
    </div>

    <h2 style="font-size: 1.1em; color: #555; margin-bottom: 15px;">Order History</h2>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Quantity</th>
                    <th>Expected Delivery</th>
                    <th>Amount</th>
                    <th>Supplier ID</th>
                    <th>Staff ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supplier_orders as $order): ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $order['ORDER_ID']; ?></td>
                    <td><?= $order['ORDER_DATE']; ?></td>
                    <td style="font-weight:600;"><?= $order['ORDER_QUANTITY']; ?></td>
                    <td>
                        <span style="background:#fff3e0; color:#e65100; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: 500;">
                            <?= $order['EXPECTED_DELIVERY']; ?>
                        </span>
                    </td>
                    <td style="font-weight:600; color:#f44336;">RM <?= number_format($order['ORDER_AMOUNT'], 2); ?></td>
                    <td><?= $order['SUPP_ID']; ?></td>
                    <td><?= $order['STAFF_ID']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="addOrderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Place New Supplier Order</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_supplier_order.php" method="post">
                <label>Order ID</label>
                <input type="text" name="orderID" placeholder="e.g. ORD7782" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Order Quantity</label>
                        <input type="number" name="orderQuantity" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Order Amount (RM)</label>
                        <input type="number" step="0.01" name="orderAmount" required>
                    </div>
                </div>

                <label>Expected Delivery Date</label>
                <input type="date" name="expectedDelivery" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Supplier ID</label>
                        <input type="text" name="suppID" placeholder="e.g. SUP101" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Staff ID (Purchaser)</label>
                        <input type="text" name="staffID" placeholder="e.g. STF005" required>
                    </div>
                </div>

                <button type="submit" class="btn-add" style="width:100%; margin-top: 10px;">Confirm Procurement</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>