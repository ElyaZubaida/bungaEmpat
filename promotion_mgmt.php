<?php
include 'db_connection.php';

// Fetch Promotions with formatted dates
$query = "SELECT PROMO_ID, PROMO_NAME, PROMO_DESC, 
                 TO_CHAR(PROMO_STARTDATE, 'DD/MM/YYYY') AS START_DATE, 
                 TO_CHAR(PROMO_ENDDATE, 'DD/MM/YYYY') AS END_DATE, 
                 PROMO_AMOUNT 
          FROM PROMOTION 
          ORDER BY PROMO_ID ASC";

$stid = oci_parse($conn, $query);
oci_execute($stid);

$promotions = [];
$total_discount = 0;

while ($row = oci_fetch_assoc($stid)) {
    $promotions[] = $row;
    $total_discount += $row['PROMO_AMOUNT'];
}

oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promotion Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddPromotionModal() { 
            document.getElementById("addPromotionModal").style.display = "flex"; 
        }

        function openEditPromotionModal(id, name, description, startDate, endDate, amount) {
            document.getElementById("editPromotionModal").style.display = "flex";
            document.getElementById("editPromo_ID").value = id;
            document.getElementById("editPromo_Name").value = name;
            document.getElementById("editPromo_Desc").value = description;
            document.getElementById("editStart_Date").value = startDate;
            document.getElementById("editEnd_Date").value = endDate;
            document.getElementById("editPromo_Amount").value = amount;
        }

        function closeModal() {
            document.getElementById("addPromotionModal").style.display = "none";
            document.getElementById("editPromotionModal").style.display = "none";
        }

        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }

        function confirmDelete(promoID) {
            if (confirm("Permanently delete promotion " + promoID + "?")) {
                window.location.href = 'delete_promotion.php?promo_id=' + promoID;
            }
        }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h1>Promotion Management</h1>
        </div>
        <button class="btn-add" onclick="openAddPromotionModal()">+ Add Promotion</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Promotions</h3>
            <span class="stat-number"><?= count($promotions); ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Promotion Name</th>
                    <th>Description</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promotions as $promo) : ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $promo['PROMO_ID']; ?></td>
                    <td><strong><?= $promo['PROMO_NAME']; ?></strong></td>
                    <td style="max-width: 250px; color: #666; font-size: 0.85em;"><?= $promo['PROMO_DESC']; ?></td>
                    <td>
                        <small style="display:block;">Start: <?= $promo['START_DATE']; ?></small>
                        <small style="display:block;">End: <?= $promo['END_DATE']; ?></small>
                    </td>
                    <td style="font-weight:600; color: #f44336;">RM <?= number_format($promo['PROMO_AMOUNT'], 2); ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditPromotionModal(
                                '<?= $promo['PROMO_ID']; ?>',
                                '<?= addslashes($promo['PROMO_NAME']); ?>',
                                '<?= addslashes($promo['PROMO_DESC']); ?>',
                                '<?= $promo['START_DATE']; ?>',
                                '<?= $promo['END_DATE']; ?>',
                                '<?= $promo['PROMO_AMOUNT']; ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $promo['PROMO_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addPromotionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New Promotion</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_promotion.php" method="post">
                <label>Promotion Name</label>
                <input type="text" name="promoName" required placeholder="e.g. Lunar New Year Sale">

                <label>Description</label>
                <input type="text" name="promoDesc" required placeholder="Brief campaign details">

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Start Date</label>
                        <input type="text" name="startDate" placeholder="DD/MM/YYYY" required>
                    </div>
                    <div style="flex: 1;">
                        <label>End Date</label>
                        <input type="text" name="endDate" placeholder="DD/MM/YYYY" required>
                    </div>
                </div>

                <label>Discount Amount (RM)</label>
                <input type="number" step="0.01" name="promoAmount" required>

                <button type="submit" class="btn-add" style="width:100%">Publish Promotion</button>
            </form>
        </div>
    </div>
</div>

<div id="editPromotionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Promotion</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_promotion.php" method="post">
                <input type="hidden" id="editPromo_ID" name="promoID">

                <label>Promotion Name</label>
                <input type="text" id="editPromo_Name" name="promoName" required>

                <label>Description</label>
                <input type="text" id="editPromo_Desc" name="promoDesc" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Start Date</label>
                        <input type="text" id="editStart_Date" name="startDate" required>
                    </div>
                    <div style="flex: 1;">
                        <label>End Date</label>
                        <input type="text" id="editEnd_Date" name="endDate" required>
                    </div>
                </div>

                <label>Discount Amount (RM)</label>
                <input type="number" step="0.01" id="editPromo_Amount" name="promoAmount" required>

                <button type="submit" class="btn-edit" style="width:100%">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>