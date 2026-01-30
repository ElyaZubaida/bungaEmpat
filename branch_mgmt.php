<?php
include 'db_connection.php';

// Sequence Fetch
$seq_query = "SELECT 'B' || (last_number) AS NEXT_ID FROM user_sequences WHERE sequence_name = 'BRANCH_SEQ'";
$seq_stid = oci_parse($conn, $seq_query);
oci_execute($seq_stid);
$seq_row = oci_fetch_assoc($seq_stid);
$next_branch_id = $seq_row['NEXT_ID'] ?? 'B105'; 
oci_free_statement($seq_stid);

// Data Fetch
$query = "SELECT * FROM BRANCH ORDER BY BRANCH_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);
$branches = [];
while ($row = oci_fetch_assoc($stid)) { $branches[] = $row; }
oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Portal | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddBranchModal() { document.getElementById("addBranchModal").style.display = "flex"; }
        function openEditBranchModal(id, name, location, phone, email) {
            document.getElementById("editBranchModal").style.display = "flex";
            document.getElementById("editBranch_ID").value = id;
            document.getElementById("editBranch_Name").value = name;
            document.getElementById("editBranch_Location").value = location;
            document.getElementById("editBranch_Phone").value = phone;
            document.getElementById("editBranch_Email").value = email;
        }
        function closeModal() {
            document.getElementById("addBranchModal").style.display = "none";
            document.getElementById("editBranchModal").style.display = "none";
        }
        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }
        function confirmDelete(id) { if (confirm("Delete branch " + id + "?")) window.location.href = "delete_branch.php?branch_id=" + id; }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="margin: 0;">Branch Management</h1>
        <p style="color: #888; margin: 5px 0 0 0; font-size: 0.9em;">Oversee and manage your outlet locations</p>
    </div>
    <button class="btn-add" onclick="openAddBranchModal()">+ Add New Branch</button>
</div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Branches</h3>
            <span class="stat-number"><?= count($branches); ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Branch Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($branches as $branch): ?>
                <tr>
                    <td><?= $branch['BRANCH_ID']; ?></td>
                    <td><?= $branch['BRANCH_NAME']; ?></td>
                    <td><?= $branch['BRANCH_ADDRESS']; ?></td>
                    <td><?= $branch['BRANCH_PHONE']; ?></td>
                    <td><?= $branch['BRANCH_EMAIL']; ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditBranchModal('<?= $branch['BRANCH_ID']; ?>','<?= addslashes($branch['BRANCH_NAME']); ?>','<?= addslashes($branch['BRANCH_ADDRESS']); ?>','<?= $branch['BRANCH_PHONE']; ?>','<?= $branch['BRANCH_EMAIL']; ?>')">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $branch['BRANCH_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addBranchModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Branch</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_branch.php" method="post">
                <label>Branch ID</label>
                <input type="text" name="branchID" value="<?= $next_branch_id; ?>" readonly>
                
                <label>Branch Name</label>
                <input type="text" name="branchName" placeholder="e.g. Bunga Empat UiTM Shah Alam" required>
                
                <label>Address</label>
                <input type="text" name="branchLocation" placeholder="Full address of the branch" required>
                
                <label>Phone</label>
                <input type="text" name="branchPhone" placeholder="e.g. 03-55442000" required>
                
                <label>Email</label>
                <input type="email" name="branchEmail" placeholder="branch@cloudbyte.com" required>
                
                <button type="submit" class="btn-add" style="width:100%">Save Branch</button>
            </form>
        </div>
    </div>
</div>

<div id="editBranchModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Branch</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_branch.php" method="post">
                <input type="hidden" id="editBranch_ID" name="branchID">
                
                <label>Branch Name</label>
                <input type="text" id="editBranch_Name" name="branchName" placeholder="Update branch name" required>
                
                <label>Address</label>
                <input type="text" id="editBranch_Location" name="branchLocation" placeholder="Update address" required>
                
                <label>Phone</label>
                <input type="text" id="editBranch_Phone" name="branchPhone" placeholder="Update contact number" required>
                
                <label>Email</label>
                <input type="email" id="editBranch_Email" name="branchEmail" placeholder="Update email address" required>
                
                <button type="submit" class="btn-edit" style="width:100%">Update Branch</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>