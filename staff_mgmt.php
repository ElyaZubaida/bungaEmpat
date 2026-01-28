<?php
include 'db_connection.php';

$query = "SELECT * FROM STAFF ORDER BY STAFF_ID";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$staffs = [];
while ($row = oci_fetch_assoc($stid)) {
    $staffs[] = $row;
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Management</title>
    <link rel="stylesheet" href="styles.css">

    <script>
        function openAddModal() {
            document.getElementById("addStaffModal").style.display = "block";
        }

        function openEditModal(id, name, username, password, phone, email, category, salary, branch_id, manager_id) {
            document.getElementById("editStaffModal").style.display = "block";

            document.getElementById("editStaff_ID").value = id;
            document.getElementById("editName").value = name;
            document.getElementById("editUsername").value = username;
            document.getElementById("editPassword").value = password;
            document.getElementById("editPhone").value = phone;
            document.getElementById("editEmail").value = email;
            document.getElementById("editCategory").value = category;
            document.getElementById("editSalary").value = salary;
            document.getElementById("editBranch_ID").value = branch_id;
            document.getElementById("editManager_ID").value = manager_id;
        }

        function confirmDelete(staffID) {
            if (confirm("Are you sure you want to delete this staff?")) {
                window.location.href = 'delete_staff.php?staff_id=' + staffID;
            }
        }

        function closeModal() {
            document.getElementById("addStaffModal").style.display = "none";
            document.getElementById("editStaffModal").style.display = "none";
        }
    </script>
</head>

<body>
<div class="container">
    <div class="main-content">
        <h1>Staff Management</h1>
        <div class="button-container">
            <div class="addbutton">
                <button class="add-button" onclick="openAddModal()">Add</button>
            </div>
        </div>
        <table id="staffTable">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Category</th>
                <th>Salary</th>
                <th>Branch ID</th>
                <th>Manager ID</th>
                <th>Action</th>
            </tr>

            <?php foreach ($staffs as $staff): ?>
                <tr>
                    <td><?= $staff['STAFF_ID']; ?></td>
                    <td><?= $staff['STAFF_NAME']; ?></td>
                    <td><?= $staff['STAFF_USERNAME']; ?></td>
                    <td><?= $staff['STAFF_PHONE']; ?></td>
                    <td><?= $staff['STAFF_EMAIL']; ?></td>
                    <td><?= $staff['STAFF_CATEGORY']; ?></td>
                    <td><?= $staff['STAFF_SALARY']; ?></td>
                    <td><?= $staff['BRANCH_ID']; ?></td>
                    <td><?= $staff['MANAGER_ID']; ?></td>

                    <td>
                        <button
                            class="edit"
                            onclick="openEditModal(
                                '<?= $staff['STAFF_ID']; ?>',
                                '<?= $staff['STAFF_NAME']; ?>',
                                '<?= $staff['STAFF_USERNAME']; ?>',
                                '<?= $staff['STAFF_PASSWORD']; ?>',
                                '<?= $staff['STAFF_PHONE']; ?>',
                                '<?= $staff['STAFF_EMAIL']; ?>',
                                '<?= $staff['STAFF_CATEGORY']; ?>',
                                '<?= $staff['STAFF_SALARY']; ?>',
                                '<?= $staff['BRANCH_ID']; ?>',
                                '<?= $staff['MANAGER_ID']; ?>'
                            )"
                        >Edit</button>

                        <button class="delete" onclick="confirmDelete('<?= $staff['STAFF_ID']; ?>')">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Add Staff Modal -->
<div id="addStaffModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Add Staff</h2>

        <form action="add_staff.php" method="post">
            <label>Name</label>
            <input type="text" name="name" required>

            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Phone</label>
            <input type="text" name="phone" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Category</label>
            <input type="text" name="category" required>

            <label>Salary</label>
            <input type="number" name="salary" required>

            <label>Branch ID</label>
            <input type="text" name="branch_id" required>

            <label>Manager ID</label>
            <input type="text" name="manager_id" required>

            <button type="submit" class="add-button">Add</button>
        </form>
    </div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Edit Staff</h2>

        <form action="edit_staff.php" method="post">
            <input type="hidden" id="editStaff_ID" name="staff_id">

            <label>Name</label>
            <input type="text" id="editName" name="name" required>

            <label>Username</label>
            <input type="text" id="editUsername" name="username" required>

            <label>Password</label>
            <input type="password" id="editPassword" name="password" required>

            <label>Phone</label>
            <input type="text" id="editPhone" name="phone" required>

            <label>Email</label>
            <input type="email" id="editEmail" name="email" required>

            <label>Category</label>
            <input type="text" id="editCategory" name="category" required>

            <label>Salary</label>
            <input type="number" id="editSalary" name="salary" required>

            <label>Branch ID</label>
            <input type="text" id="editBranch_ID" name="branch_id" required>

            <label>Manager ID</label>
            <input type="text" id="editManager_ID" name="manager_id" required>

            <button type="submit" class="add-button">Update</button>
        </form>
    </div>
</div>

</body>
</html>
