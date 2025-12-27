<?php
// Dummy data for sales
$sales = [
    ['Sale_ID' => 1, 'Sale_Date' => '27/12/2025', 'Sale_Amount' => 50.00, 'Sale_GrandAmount' => 100.00, 'Sale_PaymentType' => 'Cash', 'Cust_ID' => 1, 'Staff_ID' => 2, 'Promo_ID' => 1, 'Quantity' => 2, 'Sub_Amount' => 50.00],
    ['Sale_ID' => 2, 'Sale_Date' => '21/12/2025', 'Sale_Amount' => 30.00, 'Sale_GrandAmount' => 60.00, 'Sale_PaymentType' => 'Card', 'Cust_ID' => 2, 'Staff_ID' => 3, 'Promo_ID' => 2, 'Quantity' => 1, 'Sub_Amount' => 30.00],
    
];
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
                foreach ($sales as $sale) {
                    echo "<tr>
                            <td>" . $sale['Sale_ID'] . "</td>
                            <td>" . $sale['Sale_Date'] . "</td>
                            <td>" . $sale['Sale_Amount'] . "</td>
                            <td>" . $sale['Sale_GrandAmount'] . "</td>
                            <td>" . $sale['Sale_PaymentType'] . "</td>
                            <td>" . $sale['Quantity'] . "</td>
                            <td>" . $sale['Sub_Amount'] . "</td>
                            <td>" . $sale['Cust_ID'] . "</td>
                            <td>" . $sale['Staff_ID'] . "</td>
                            <td>" . $sale['Promo_ID'] . "</td>
                        </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
