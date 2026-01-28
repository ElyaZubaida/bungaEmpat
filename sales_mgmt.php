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

$query = "SELECT s.SALE_ID, s.SALE_DATE, s.SALE_AMOUNT, s.SALE_GRANDAMOUNT, s.SALE_PAYMENTTYPE, s.CUST_ID, s.STAFF_ID, s.PROMO_ID, ps.PS_QUANTITY, ps.PS_SUBPRICE
          FROM SALE s
          JOIN PRODUCT_SALE ps ON s.SALE_ID = ps.SALE_ID
          ORDER BY s.SALE_ID";

$stid = oci_parse($conn, $query);
oci_execute($stid);

$sales = [];
while ($row = oci_fetch_assoc($stid)) {
    $sales[] = $row;
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
    <title>Sales Management</title>
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
    <div class="container">
        <div class="main-content">
            <h1>Sales Management</h1>
            <div class="button-container">
                <div class="addbutton">
                    <button class="add-button" onclick="openAddSalesModal()">Add</button>
                </div>
            </div>
            <table id="salesTable">
                <tr>
                    <th>Sale ID</th>
                    <th>Sale Date</th>
                    <th>Sale Amount</th>
                    <th>Sale Grand Amount</th>
                    <th>Payment Type</th>
                    <th>Quantity</th>
                    <th>Sub Amount</th>
                    <th>Customer ID</th>
                    <th>Staff ID</th>
                    <th>Promo ID</th>
                </tr>
                <?php
                // Iterate over the sales array and display data in the table
                foreach ($sales as $sale) {
                    echo "<tr>
                            <td>" . $sale['SALE_ID'] . "</td>
                            <td>" . $sale['SALE_DATE'] . "</td>
                            <td>" . $sale['SALE_AMOUNT'] . "</td>
                            <td>" . $sale['SALE_GRANDAMOUNT'] . "</td>
                            <td>" . $sale['SALE_PAYMENTTYPE'] . "</td>
                            <td>" . $sale['PS_QUANTITY'] . "</td>
                            <td>" . $sale['PS_SUBPRICE'] . "</td>
                            <td>" . $sale['CUST_ID'] . "</td>
                            <td>" . $sale['STAFF_ID'] . "</td>
                            <td>" . $sale['PROMO_ID'] . "</td>
                        </tr>";
                }
                ?>
            </table>
        </div>
    </div>
    <div id="addSalesModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add New Sale</h2>
            
            <form action="add_sales.php" method="post">

                <label>Sale Date</label>
                <input type="date" name="saleDate" placeholder="DD-MMM-YY" required>

                <label>Sale Amount</label>      
                <input type="number" step="0.01" name="saleAmount" required>

                <label>Grand Total Amount</label>
                <input type="number" step="0.01" name="saleGrandAmount" required>

                <label>Payment Type</label>
                <select name="salePaymentType" required>
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>

                <label>Customer</label>
                <select name="custId">
                    <option value="">-- Select Customer (Optional) --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['CUST_ID'] ?>"><?= $c['CUST_ID'] ?> - <?= $c['CUST_NAME'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Staff Member</label>
                <select name="staffId">
                    <option value="">-- Select Staff --</option>
                    <?php foreach ($staff_members as $s): ?>
                        <option value="<?= $s['STAFF_ID'] ?>"><?= $s['STAFF_ID'] ?> - <?= $s['STAFF_NAME'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Promotion</label>
                <select name="promoId">
                    <option value="">-- None --</option>
                    <?php foreach ($promotions as $p): ?>
                        <option value="<?= $p['PROMO_ID'] ?>"><?= $p['PROMO_ID'] ?> - <?= $p['PROMO_NAME'] ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="add-button">Add Sale Record</button>
            </form>
        </div>
    </div>

    
</body>
</html>
