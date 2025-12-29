<?php
include 'db_connection.php';

$query = "SELECT PROMO_ID, PROMO_NAME, PROMO_DESC, TO_CHAR(PROMO_STARTDATE, 'DD/MM/YYYY') AS START_DATE, TO_CHAR(PROMO_ENDDATE, 'DD/MM/YYYY') AS END_DATE, PROMO_AMOUNT FROM PROMOTION";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$promotions = [];
while ($row = oci_fetch_assoc($stid)) {
    $promotions[] = $row;
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
    <title>Promotion Management</title>
    <link rel="stylesheet" href="styles.css">

    <script>
        function openAddPromotionModal() {
            document.getElementById("addPromotionModal").style.display = "block";
        }

        function openEditPromotionModal(id, name, description, startDate, endDate, amount) {
            document.getElementById("editPromotionModal").style.display = "block";

            document.getElementById("editPromo_ID").value = id;
            document.getElementById("editPromo_Name").value = name;
            document.getElementById("editPromo_Desc").value = description;
            document.getElementById("editStart_Date").value = startDate;
            document.getElementById("editEnd_Date").value = endDate;
            document.getElementById("editPromo_Amount").value = amount;
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
                <th>Description</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>

            <?php foreach ($promotions as $promo) : ?>
                <tr>
                    <td><?= $promo['PROMO_ID']; ?></td>
                    <td><?= $promo['PROMO_NAME']; ?></td>
                    <td><?= $promo['PROMO_DESC']; ?></td>
                    <td><?= $promo['START_DATE']; ?></td>
                    <td><?= $promo['END_DATE']; ?></td>
                    <td><?= $promo['PROMO_AMOUNT']; ?></td>

                    <td>
                        <button 
                            class="edit"
                            onclick="openEditPromotionModal(
                                '<?= $promo['PROMO_ID']; ?>',
                                '<?= $promo['PROMO_NAME']; ?>',
                                '<?= $promo['PROMO_DESC']; ?>',
                                '<?= $promo['START_DATE']; ?>',
                                '<?= $promo['END_DATE']; ?>',
                                '<?= $promo['PROMO_AMOUNT']; ?>'
                            )">
                            Edit
                        </button>

                        <button class="delete" onclick="confirmDelete('<?= $promo['PROMO_ID']; ?>')">
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

            <label>Description</label>
            <input type="text" name="promoDesc" required>

            <label>Start Date</label>
            <input type="text" name="startDate" placeholder="DD/MM/YYYY" required>

            <label>End Date</label>
            <input type="text" name="endDate" placeholder="DD/MM/YYYY" required>

            <label>Amount</label>
            <input type="number" name="promoAmount" required>

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

            <label>Description</label>
            <input type="text" id="editPromo_Desc" name="promoDesc" required>

            <label>Start Date</label>
            <input type="text" id="editStart_Date" name="startDate" required>

            <label>End Date</label>
            <input type="text" id="editEnd_Date" name="endDate" required>

            <label>Amount</label>
            <input type="number" id="editPromo_Amount" name="promoAmount" required>

            <button type="submit" class="add-button">Update</button>
        </form>
    </div>
</div>

</body>
</html>
