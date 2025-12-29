<?php
include 'db_connection.php';

$query = "SELECT s.SALE_ID, s.SALE_DATE, s.SALE_AMOUNT, s.SALE_GRANDAMOUNT, s.SALE_PAYMENTTYPE, s.CUST_ID, s.STAFF_ID, s.PROMO_ID, ps.PS_QUANTITY, ps.PS_SUBPRICE
          FROM SALE s
          JOIN PRODUCT_SALE ps ON s.SALE_ID = ps.SALE_ID";
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
</head>
<body>
    <div class="container">
        <div class="main-content">
            <h1>Sales Management</h1>
            <div class="button-container">
                <div class="addbutton">
                    <button class="add-button" onclick="openAddSaleModal()">Add</button>
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
</body>
</html>
