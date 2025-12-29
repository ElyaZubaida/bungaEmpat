<?php
include 'db_connection.php';

$query = "SELECT * FROM SUPPLIER_ORDER";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$supplier_orders = [];
while ($row = oci_fetch_assoc($stid)) {
    $supplier_orders[] = $row;
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Order Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="main-content">
        <h1>Supplier Order Management</h1>

        <div class="button-container">
            <div class="addbutton">
                <button class="add-button" onclick="openAddOrderModal()">New Order</button>
            </div>
        </div>

        <table id="supplierOrderTable">
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Order Quantity</th>
                <th>Expected Delivery</th>
                <th>Order Amount</th>
                <th>Supplier ID</th>
                <th>Staff ID</th>
            </tr>

            <?php foreach ($supplier_orders as $order): ?>
                <tr>
                    <td><?= $order['ORDER_ID']; ?></td>
                    <td><?= $order['ORDER_DATE']; ?></td>
                    <td><?= $order['ORDER_QUANTITY']; ?></td>
                    <td><?= $order['EXPECTED_DELIVERY']; ?></td>
                    <td><?= $order['ORDER_AMOUNT']; ?></td>
                    <td><?= $order['SUPP_ID']; ?></td>
                    <td><?= $order['STAFF_ID']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Add Order Modal -->
<div id="addOrderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Add Supplier Order</h2>

        <form action="add_supplier_order.php" method="post">
            <label>Order ID</label>
            <input type="text" name="orderID" required>

            <label>Order Quantity</label>
            <input type="number" name="orderQuantity" required>

            <label>Expected Delivery</label>
            <input type="date" name="expectedDelivery" required>

            <label>Order Amount</label>
            <input type="number" step="0.01" name="orderAmount" required>

            <label>Supplier ID</label>
            <input type="text" name="suppID" required>

            <label>Staff ID</label>
            <input type="text" name="staffID" required>

            <button type="submit" class="add-button">Add Order</button>
        </form>
    </div>
</div>
</body>
</html>
