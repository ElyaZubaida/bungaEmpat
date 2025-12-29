<?php
// Dummy data for branches
$branches = [
    ['Branch_ID' => 1, 'Branch_Name' => 'Main Branch', 'Branch_Location' => 'Kuala Lumpur'],
    ['Branch_ID' => 2, 'Branch_Name' => 'North Branch', 'Branch_Location' => 'Penang'],
];

include 'sidebar.php'; // sidebar navigation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Management</title>
    <link rel="stylesheet" href="styles.css">

    <script>
        function openAddBranchModal() {
            document.getElementById("addBranchModal").style.display = "block";
        }

        function openEditBranchModal(id, name, location) {
            document.getElementById("editBranchModal").style.display = "block";

            document.getElementById("editBranch_ID").value = id;
            document.getElementById("editBranch_Name").value = name;
            document.getElementById("editBranch_Location").value = location;
        }

        function confirmDelete(branchID) {
            if (confirm("Are you sure you want to delete this branch?")) {
                window.location.href = "delete_branch.php?branch_id=" + branchID;
            }
        }

        function closeModal() {
            document.getElementById("addBranchModal").style.display = "none";
            document.getElementById("editBranchModal").style.display = "none";
        }
    </script>
</head>

<body>

<div class="container">
    <div class="main-content">
        <h1>Branch Management</h1>

        <div class="button-container">
            <div class="addbutton">
                <button class="add-button" onclick="openAddBranchModal()">Add</button>
            </div>
        </div>

        <table id="branchTable">
            <tr>
                <th>ID</th>
                <th>Branch Name</th>
                <th>Location</th>
                <th>Action</th>
            </tr>

            <?php foreach ($branches as $branch): ?>
                <tr>
                    <td><?= $branch['Branch_ID']; ?></td>
                    <td><?= $branch['Branch_Name']; ?></td>
                    <td><?= $branch['Branch_Location']; ?></td>

                    <td>
                        <button 
                            class="edit"
                            onclick="openEditBranchModal(
                                '<?= $branch['Branch_ID']; ?>',
                                '<?= $branch['Branch_Name']; ?>',
                                '<?= $branch['Branch_Location']; ?>'
                            )">
                            Edit
                        </button>

                        <button 
                            class="delete" 
                            onclick="confirmDelete('<?= $branch['Branch_ID']; ?>')">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Add Branch Modal -->
<div id="addBranchModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Add Branch</h2>

        <form action="add_branch.php" method="post">
            <label>Branch Name</label>
            <input type="text" name="branchName" required>

            <label>Location</label>
            <input type="text" name="branchLocation" required>

            <button type="submit" class="add-button">Add Branch</button>
        </form>
    </div>
</div>

<!-- Edit Branch Modal -->
<div id="editBranchModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Edit Branch</h2>

        <form action="edit_branch.php" method="post">

            <input type="hidden" id="editBranch_ID" name="branchID">

            <label>Branch Name</label>
            <input type="text" id="editBranch_Name" name="branchName" required>

            <label>Location</label>
            <input type="text" id="editBranch_Location" name="branchLocation" required>

            <button type="submit" class="add-button">Update Branch</button>
        </form>
    </div>
</div>

</body>
</html>
