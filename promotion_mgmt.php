<?php
// Dummy data for promotions
$promotions = [
    ['Promo_ID' => 1, 'Promo_Name' => 'New Year Sale', 'Discount' => 20, 'Start_Date' => '01/01/2025', 'End_Date' => '10/01/2025', 'Status' => 'Active'],
    ['Promo_ID' => 2, 'Promo_Name' => 'Student Discount', 'Discount' => 10, 'Start_Date' => '05/01/2025', 'End_Date' => '31/01/2025', 'Status' => 'Active'],
    ['Promo_ID' => 3, 'Promo_Name' => 'Clearance Sale', 'Discount' => 40, 'Start_Date' => '15/12/2024', 'End_Date' => '31/12/2024', 'Status' => 'Expired'],
];

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promotion Management</title>
    <link rel="stylesheet" href="styles.css">

    <script>
        function openAddPromotionModal() {
            document.getElementById("addPromotionModal").style.display = "block";
        }

        function openEditPromotionModal(id, name, discount, start, end, status) {
            document.getElementById("editPromotionModal").style.display = "block";

            document.getElementById("editPromo_ID").value = id;
            document.getElementById("editPromo_Name").value = name;
            document.getElementById("editPromo_Discount").value = discount;
            document.getElementById("editStart_Date").value = start;
            document.getElementById("editEnd_Date").value = end;
            document.getElementById("editStatus").value = status;
        }

        function confirmDelete(promoID) {
            if (confirm("Are you sure you want to delete this promotion?")) {
                window.location.href = 'delete_promotion.php?promo_id=' + promoID;
            }
        }

        function closeModal() {
            document.getElementById("addPromotionModal").style.display = "none";
            document.getElementById("editPromotionModal").style.display = "none";
        }
    </script>
</head>

<body>
<div class="container">
    <div class="main-content">
        <h1>Promotion Management</h1>

        <div class="button-container">
            <div class="addbutton">
                <button class="add-button" onclick="openAddPromotionModal()">Add</button>
            </div>
        </div>

        <table id="promotionTable">
            <tr>
                <th>ID</th>
                <th>Promotion Name</th>
                <th>Discount (%)</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php foreach ($promotions as $promo) : ?>
                <tr>
                    <td><?= $promo['Promo_ID']; ?></td>
                    <td><?= $promo['Promo_Name']; ?></td>
                    <td><?= $promo['Discount']; ?></td>
                    <td><?= $promo['Start_Date']; ?></td>
                    <td><?= $promo['End_Date']; ?></td>
                    <td><?= $promo['Status']; ?></td>

                    <td>
                        <button
                            class="edit"
                            onclick="openEditPromotionModal(
                                '<?= $promo['Promo_ID']; ?>',
                                '<?= $promo['Promo_Name']; ?>',
                                '<?= $promo['Discount']; ?>',
                                '<?= $promo['Start_Date']; ?>',
                                '<?= $promo['End_Date']; ?>',
                                '<?= $promo['Status']; ?>'
                            )"
                        >Edit</button>

                        <button class="delete" onclick="confirmDelete('<?= $promo['Promo_ID']; ?>')">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Add Promotion Modal -->
<div id="addPromotionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Add Promotion</h2>

        <form action="add_promotion.php" method="post">
            <label>Promotion Name</label>
            <input type="text" name="promoName" required>

            <label>Discount (%)</label>
            <input type="number" name="discount" required>

            <label>Start Date</label>
            <input type="text" name="startDate" placeholder="DD/MM/YYYY" required>

            <label>End Date</label>
            <input type="text" name="endDate" placeholder="DD/MM/YYYY" required>

            <label>Status</label>
            <select name="status">
                <option value="Active">Active</option>
                <option value="Upcoming">Upcoming</option>
                <option value="Expired">Expired</option>
            </select>

            <button type="submit" class="add-button">Add</button>
        </form>
    </div>
</div>

<!-- Edit Promotion Modal -->
<div id="editPromotionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Edit Promotion</h2>

        <form action="edit_promotion.php" method="post">
            <input type="hidden" id="editPromo_ID" name="promoID">

            <label>Promotion Name</label>
            <input type="text" id="editPromo_Name" name="promoName" required>

            <label>Discount (%)</label>
            <input type="number" id="editPromo_Discount" name="discount" required>

            <label>Start Date</label>
            <input type="text" id="editStart_Date" name="startDate" required>

            <label>End Date</label>
            <input type="text" id="editEnd_Date" name="endDate" required>

            <label>Status</label>
            <select id="editStatus" name="status">
                <option value="Active">Active</option>
                <option value="Upcoming">Upcoming</option>
                <option value="Expired">Expired</option>
            </select>

            <button type="submit" class="add-button">Update</button>
        </form>
    </div>
</div>

</body>
</html>
