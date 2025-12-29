<?php
// Dummy data for customers
$customers = [
    ['Cust_ID' => 1, 'Cust_Name' => 'Ali Ahmad', 'Cust_Email' => 'ali@example.com', 'Cust_Phone' => '0123456789', 'Cust_Type' => 'Regular', 'Cust_Address' => 'Kuala Lumpur'],
    ['Cust_ID' => 2, 'Cust_Name' => 'Siti Nur', 'Cust_Email' => 'siti@example.com', 'Cust_Phone' => '0178882222', 'Cust_Type' => 'VIP', 'Cust_Address' => 'Shah Alam'],
    ['Cust_ID' => 3, 'Cust_Name' => 'Rahman', 'Cust_Email' => 'rahman@example.com', 'Cust_Phone' => '0195553333', 'Cust_Type' => 'Regular', 'Cust_Address' => 'Penang'],
];

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

        function openEditCustomerModal(id, name, email, phone, type, address) {
            document.getElementById("editCustomerModal").style.display = "block";

            document.getElementById("editCust_ID").value = id;
            document.getElementById("editCust_Name").value = name;
            document.getElementById("editCust_Email").value = email;
            document.getElementById("editCust_Phone").value = phone;
            document.getElementById("editCust_Type").value = type;
            document.getElementById("editCust_Address").value = address;
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
                <th>Type</th>
                <th>Address</th>
                <th>Action</th>
            </tr>

            <?php foreach ($customers as $cust) : ?>
                <tr>
                    <td><?= $cust['Cust_ID']; ?></td>
                    <td><?= $cust['Cust_Name']; ?></td>
                    <td><?= $cust['Cust_Email']; ?></td>
                    <td><?= $cust['Cust_Phone']; ?></td>
                    <td><?= $cust['Cust_Type']; ?></td>
                    <td><?= $cust['Cust_Address']; ?></td>

                    <td>
                        <button 
                            class="edit"
                            onclick="openEditCustomerModal(
                                '<?= $cust['Cust_ID']; ?>',
                                '<?= $cust['Cust_Name']; ?>',
                                '<?= $cust['Cust_Email']; ?>',
                                '<?= $cust['Cust_Phone']; ?>',
                                '<?= $cust['Cust_Type']; ?>',
                                '<?= $cust['Cust_Address']; ?>'
                            )"
                        >
                            Edit
                        </button>

                        <button class="delete" onclick="confirmDelete('<?= $cust['Cust_ID']; ?>')">
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

            <label>Type</label>
            <select name="custType">
                <option value="Regular">Regular</option>
                <option value="VIP">VIP</option>
            </select>

            <label>Address</label>
            <input type="text" name="custAddress">

            <button type="submit" class="add-button">Add</button>
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Edit Customer</h2>

        <form action="edit_customer.php" method="post">
            <input type="hidden" id="editCust_ID" name="custID">

            <label>Name</label>
            <input type="text" id="editCust_Name" name="custName" required>

            <label>Email</label>
            <input type="email" id="editCust_Email" name="custEmail" required>

            <label>Phone</label>
            <input type="text" id="editCust_Phone" name="custPhone" required>

            <label>Type</label>
            <select id="editCust_Type" name="custType">
                <option value="Regular">Regular</option>
                <option value="VIP">VIP</option>
            </select>

            <label>Address</label>
            <input type="text" id="editCust_Address" name="custAddress">

            <button type="submit" class="add-button">Update</button>
        </form>
    </div>
</div>

</body>
</html>
