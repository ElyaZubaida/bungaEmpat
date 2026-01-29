<?php
include 'db_connection.php';

// Fetch all staff data
$query = "SELECT * FROM STAFF ORDER BY STAFF_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$staffs = [];
$total_payroll = 0;

while ($row = oci_fetch_assoc($stid)) {
    $staffs[] = $row;
    $total_payroll += $row['STAFF_SALARY'];
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddModal() {
            document.getElementById("addStaffModal").style.display = "flex";
        }

        function openEditModal(id, name, username, password, phone, email, category, salary, branch_id, manager_id) {
            document.getElementById("editStaffModal").style.display = "flex";
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
            if (confirm("Permanently delete staff member " + staffID + "?")) {
                window.location.href = 'delete_staff.php?staff_id=' + staffID;
            }
        }

        function closeModal() {
            document.getElementById("addStaffModal").style.display = "none";
            document.getElementById("editStaffModal").style.display = "none";
        }

        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }
    </script>
</head>

<body>

<div class="main-content">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="margin: 0;">Staff Management</h1>
        <p style="color: #888; margin: 5px 0 0 0; font-size: 0.9em;">Manage your team members and roles</p>
    </div>
    <button class="btn-add" onclick="openAddModal()">+ Add Staff</button>
</div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Employees</h3>
            <span class="stat-number"><?= count($staffs); ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Category</th>
                    <th>Salary</th>
                    <th>Branch/Manager</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffs as $staff): ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $staff['STAFF_ID']; ?></td>
                    <td>
                        <div style="font-weight:600;"><?= $staff['STAFF_NAME']; ?></div>
                        <small style="color:#777;">@<?= $staff['STAFF_USERNAME']; ?></small>
                    </td>
                    <td>
                        <small style="display:block;"><?= $staff['STAFF_EMAIL']; ?></small>
                        <small style="display:block; color:#666;"><?= $staff['STAFF_PHONE']; ?></small>
                    </td>
                    <td>
                        <span style="background:#f0f0f0; padding:4px 8px; border-radius:4px; font-size:0.85em;">
                            <?= $staff['STAFF_CATEGORY']; ?>
                        </span>
                    </td>
                    <td style="font-weight:600;">RM <?= number_format($staff['STAFF_SALARY'], 2); ?></td>
                    <td>
                        <small style="display:block;">Branch: <?= $staff['BRANCH_ID']; ?></small>
                        <small style="display:block;">Mgr: <?= $staff['MANAGER_ID'] ?: 'N/A'; ?></small>
                    </td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditModal(
                                '<?= $staff['STAFF_ID']; ?>',
                                '<?= addslashes($staff['STAFF_NAME']); ?>',
                                '<?= addslashes($staff['STAFF_USERNAME']); ?>',
                                '<?= addslashes($staff['STAFF_PASSWORD']); ?>',
                                '<?= $staff['STAFF_PHONE']; ?>',
                                '<?= $staff['STAFF_EMAIL']; ?>',
                                '<?= $staff['STAFF_CATEGORY']; ?>',
                                '<?= $staff['STAFF_SALARY']; ?>',
                                '<?= $staff['BRANCH_ID']; ?>',
                                '<?= $staff['MANAGER_ID']; ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $staff['STAFF_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addStaffModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Staff Member</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_staff.php" method="post">
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Full Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                </div>

                <label>Password</label>
                <input type="password" name="password" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Phone</label>
                        <input type="text" name="phone" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Category</label>
                        <input type="text" name="category" placeholder="e.g. Cashier" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Salary (RM)</label>
                        <input type="number" step="0.01" name="salary" required>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Branch ID</label>
                        <input type="text" name="branch_id" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Manager ID</label>
                        <input type="text" name="manager_id" placeholder="Optional">
                    </div>
                </div>

                <button type="submit" class="btn-add" style="width:100%">Register Staff</button>
            </form>
        </div>
    </div>
</div>

<div id="editStaffModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Staff Record</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_staff.php" method="post">
                <input type="hidden" id="editStaff_ID" name="staff_id">

                <label>Full Name</label>
                <input type="text" id="editName" name="name" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Username</label>
                        <input type="text" id="editUsername" name="username" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Password</label>
                        <input type="password" id="editPassword" name="password" required>
                    </div>
                </div>

                <label>Email</label>
                <input type="email" id="editEmail" name="email" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Category</label>
                        <input type="text" id="editCategory" name="category" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Salary</label>
                        <input type="number" step="0.01" id="editSalary" name="salary" required>
                    </div>
                </div>

                <button type="submit" class="btn-edit" style="width:100%">Update Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>