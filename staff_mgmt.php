<?php
// Dummy staff list
$staffs = [
    ['Staff_ID' => 1, 'Name' => 'Ali Ahmad', 'Email' => 'ali@example.com', 'Role' => 'Manager', 'Status' => 'Active'],
    ['Staff_ID' => 2, 'Name' => 'Siti Aminah', 'Email' => 'siti@example.com', 'Role' => 'Cashier', 'Status' => 'Active'],
    ['Staff_ID' => 3, 'Name' => 'Rahman Jamal', 'Email' => 'rahman@example.com', 'Role' => 'Assistant', 'Status' => 'Inactive'],
];

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

        function openEditModal(id, name, email, role, status) {
            document.getElementById("editStaffModal").style.display = "block";

            document.getElementById("editStaff_ID").value = id;
            document.getElementById("editName").value = name;
            document.getElementById("editEmail").value = email;
            document.getElementById("editRole").value = role;
            document.getElementById("editStatus").value = status;
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
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php foreach ($staffs as $staff): ?>
                <tr>
                    <td><?= $staff['Staff_ID']; ?></td>
                    <td><?= $staff['Name']; ?></td>
                    <td><?= $staff['Email']; ?></td>
                    <td><?= $staff['Role']; ?></td>
                    <td><?= $staff['Status']; ?></td>

                    <td>
                        <button
                            class="edit"
                            onclick="openEditModal(
                                '<?= $staff['Staff_ID']; ?>',
                                '<?= $staff['Name']; ?>',
                                '<?= $staff['Email']; ?>',
                                '<?= $staff['Role']; ?>',
                                '<?= $staff['Status']; ?>'
                            )"
                        >Edit</button>

                        <button class="delete" onclick="confirmDelete('<?= $staff['Staff_ID']; ?>')">
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

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Role</label>
            <input type="text" name="role" required>

            <label>Status</label>
            <select name="status">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

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
            <input type="hidden" id="editStaff_ID" name="staffID">

            <label>Name</label>
            <input type="text" id="editName" name="name" required>

            <label>Email</label>
            <input type="email" id="editEmail" name="email" required>

            <label>Role</label>
            <input type="text" id="editRole" name="role" required>

            <label>Status</label>
            <select id="editStatus" name="status">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <button type="submit" class="add-button">Update</button>
        </form>
    </div>
</div>

</body>
</html>
