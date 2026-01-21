<?php
include 'db_connection.php';

// Fetch all customers and calculate totals for cards
$query = "SELECT CUST_ID, CUST_NAME, CUST_EMAIL, CUST_PHONE, CUST_LOYALTYPOINTS, CUST_DATEREGISTERED 
          FROM CUSTOMER ORDER BY CUST_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$customers = [];
$total_customers = 0;
$total_loyalty_points = 0;

while ($row = oci_fetch_assoc($stid)) {
    $customers[] = $row;
    $total_customers++;
    $total_loyalty_points += $row['CUST_LOYALTYPOINTS'];
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddCustomerModal() { 
            document.getElementById("addCustomerModal").style.display = "flex"; 
        }

        function openEditCustomerModal(id, name, email, phone, loyaltyPoints, dateRegistered) {
            document.getElementById("editCustomerModal").style.display = "flex";
            document.getElementById("editCust_ID").value = id;
            document.getElementById("editCust_Name").value = name;
            document.getElementById("editCust_Email").value = email;
            document.getElementById("editCust_Phone").value = phone;
            document.getElementById("editCust_LoyaltyPoints").value = loyaltyPoints;
            document.getElementById("editCust_DateRegistered").value = dateRegistered;
        }

        function closeModal() {
            document.getElementById("addCustomerModal").style.display = "none";
            document.getElementById("editCustomerModal").style.display = "none";
        }

        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }

        function confirmDelete(custID) {
            if (confirm("Permanently delete customer " + custID + "?")) {
                window.location.href = 'delete_customer.php?cust_id=' + custID;
            }
        }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h1>Customer Management</h1>
        </div>
        <button class="btn-add" onclick="openAddCustomerModal()">+ Add Customer</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Customers</h3>
            <span class="stat-number"><?= $total_customers; ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Loyalty Points</th>
                    <th>Registration Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $cust) : ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $cust['CUST_ID']; ?></td>
                    <td><?= $cust['CUST_NAME']; ?></td>
                    <td><?= $cust['CUST_EMAIL']; ?></td>
                    <td><?= $cust['CUST_PHONE']; ?></td>
                    <td style="font-weight:600; color: #4CAF50;"><?= $cust['CUST_LOYALTYPOINTS']; ?> pts</td>
                    <td><?= $cust['CUST_DATEREGISTERED']; ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditCustomerModal(
                                '<?= $cust['CUST_ID']; ?>',
                                '<?= addslashes($cust['CUST_NAME']); ?>',
                                '<?= addslashes($cust['CUST_EMAIL']); ?>',
                                '<?= $cust['CUST_PHONE']; ?>',
                                '<?= $cust['CUST_LOYALTYPOINTS']; ?>',
                                '<?= $cust['CUST_DATEREGISTERED']; ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $cust['CUST_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addCustomerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Customer</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_customer.php" method="post">
                <label>Name</label>
                <input type="text" name="custName" required>

                <label>Email</label>
                <input type="email" name="custEmail" required>

                <label>Phone</label>
                <input type="text" name="custPhone" required>

                <label>Initial Loyalty Points</label>
                <input type="number" name="custLoyaltyPoints" value="0">

                <label>Registration Date</label>
                <input type="date" name="custDateRegistered" required>

                <button type="submit" class="btn-add" style="width:100%">Add Customer</button>
            </form>
        </div>
    </div>
</div>

<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Customer Details</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_customer.php" method="post">
                <input type="hidden" id="editCust_ID" name="custID">

                <label>Name</label>
                <input type="text" id="editCust_Name" name="custName" required>

                <label>Email</label>
                <input type="email" id="editCust_Email" name="custEmail" required>

                <label>Phone</label>
                <input type="text" id="editCust_Phone" name="custPhone" required>

                <label>Loyalty Points</label>
                <input type="number" id="editCust_LoyaltyPoints" name="custLoyaltyPoints">

                <label>Registration Date</label>
                <input type="date" id="editCust_DateRegistered" name="custDateRegistered" required>

                <button type="submit" class="btn-edit" style="width:100%">Update Customer</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>