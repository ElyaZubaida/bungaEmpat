<?php
include 'db_connection.php';

$query = "SELECT * FROM CUSTOMER ORDER BY CUST_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$customers = [];
while ($row = oci_fetch_assoc($stid)) {
    $customers[] = $row;
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
    <title>Customer Management</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddCustomerModal() {
            document.getElementById("addCustomerModal").style.display = "block";
        }

        function openEditCustomerModal(id, name, email, phone, loyaltyPoints, dateRegistered) {
            document.getElementById("editCustomerModal").style.display = "block";

            document.getElementById("editCust_ID").value = id;
            document.getElementById("editCust_Name").value = name;
            document.getElementById("editCust_Email").value = email;
            document.getElementById("editCust_Phone").value = phone;
            document.getElementById("editCust_LoyaltyPoints").value = loyaltyPoints;
            document.getElementById("editCust_DateRegistered").value = dateRegistered;
        }

        function confirmDelete(custID) {
            if (confirm("Are you sure you want to delete this customer?")) {
                window.location.href = 'delete_customer.php?cust_id=' + custID;
            }
        }

        function closeModal() {
            document.getElementById("addCustomerModal").style.display = "none";
            document.getElementById("editCustomerModal").style.display = "none";
        }
    </script>
</head>

<body>

<div class="container">
    <div class="main-content">
        <h1>Customer Management</h1>

        <div class="button-container">
            <div class="addbutton">
                <button class="add-button" onclick="openAddCustomerModal()">Add</button>
            </div>
        </div>

        <table id="customerTable">
            <tr>
                <th>ID</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Loyalty Points</th>
                <th>Registration Date</th>
                <th>Action</th>
            </tr>

            <?php foreach ($customers as $cust) : ?>
                <tr>
                    <td><?= $cust['CUST_ID']; ?></td>
                    <td><?= $cust['CUST_NAME']; ?></td>
                    <td><?= $cust['CUST_EMAIL']; ?></td>
                    <td><?= $cust['CUST_PHONE']; ?></td>
                    <td><?= $cust['CUST_LOYALTYPOINTS']; ?></td>
                    <td><?= $cust['CUST_DATEREGISTERED']; ?></td>

                    <td>
                        <button 
                            class="edit"
                            onclick="openEditCustomerModal(
                                '<?= $cust['CUST_ID']; ?>',
                                '<?= $cust['CUST_NAME']; ?>',
                                '<?= $cust['CUST_EMAIL']; ?>',
                                '<?= $cust['CUST_PHONE']; ?>',
                                '<?= $cust['CUST_LOYALTYPOINTS']; ?>',
                                '<?= $cust['CUST_DATEREGISTERED']; ?>'
                            )">
                            Edit
                        </button>

                        <button 
                            class="delete" 
                            onclick="confirmDelete('<?= $cust['CUST_ID']; ?>')">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Add Customer Modal -->
<div id="addCustomerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Add Customer</h2>

        <form action="add_customer.php" method="post">
            <label>Name</label>
            <input type="text" name="custName" required>

            <label>Email</label>
            <input type="email" name="custEmail" required>

            <label>Phone</label>
            <input type="text" name="custPhone" required>

            <label>Loyalty Points</label>
            <input type="number" name="custLoyaltyPoints" value="0">

            <label>Registration Date</label>
            <input type="date" name="custDateRegistered" required>

            <button type="submit" class="add-button">Add Customer</button>
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Edit Customer</h2>

        <form action="edit_customer.php" method="post">
            <input type="hidden" id="editCust_ID" name="custID" value="<?php echo $row['CUST_ID']; ?>">

            <label>Name</label>
            <input type="text" id="editCust_Name" name="custName" value="<?php echo $row['CUST_NAME']; ?>">

            <label>Email</label>
            <input type="email" id="editCust_Email" name="custEmail" required>

            <label>Phone</label>
            <input type="text" id="editCust_Phone" name="custPhone" required>

            <label>Loyalty Points</label>
            <input type="number" id="editCust_LoyaltyPoints" name="custLoyaltyPoints">

            <label>Registration Date</label>
            <input type="date" id="editCust_DateRegistered" name="custDateRegistered" required>

            <button type="submit" class="add-button">Update Customer</button>
        </form>
    </div>
</div>

</body>
</html>
