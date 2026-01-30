<?php
include 'db_connection.php';

// --- 1. FETCH PROMOTIONS ---
$query = "SELECT PROMO_ID, PROMO_NAME, PROMO_DESC, 
                 TO_CHAR(PROMO_STARTDATE, 'YYYY-MM-DD') AS START_DATE, 
                 TO_CHAR(PROMO_ENDDATE, 'YYYY-MM-DD') AS END_DATE, 
                 PROMO_AMOUNT 
          FROM PROMOTION 
          ORDER BY PROMO_ID ASC";

$stid = oci_parse($conn, $query);
oci_execute($stid);
$promotions = [];
while ($row = oci_fetch_assoc($stid)) { $promotions[] = $row; }
oci_free_statement($stid);

// --- 2. GENERATE NEXT PROMO ID ---
$id_query = "SELECT MAX(TO_NUMBER(SUBSTR(PROMO_ID, 2))) AS MAX_ID FROM PROMOTION";
$id_stid = oci_parse($conn, $id_query);
oci_execute($id_stid);
$id_row = oci_fetch_assoc($id_stid);
$next_num = ($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 1;
$next_promo_id = "P" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
oci_free_statement($id_stid);

oci_close($conn);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promotion Management | Bunga Admin</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddPromotionModal() { document.getElementById("addPromotionModal").style.display = "flex"; }
        function openEditPromotionModal(id, name, desc, start, end, amount) {
            document.getElementById("editPromotionModal").style.display = "flex";
            document.getElementById("editPromo_ID").value = id;
            document.getElementById("editPromo_Name").value = name;
            document.getElementById("editPromo_Desc").value = desc;
            document.getElementById("editStart_Date").value = start;
            document.getElementById("editEnd_Date").value = end;
            document.getElementById("editPromo_Amount").value = amount;
        }
        function closeModal() {
            document.getElementById("addPromotionModal").style.display = "none";
            document.getElementById("editPromotionModal").style.display = "none";
        }
        function confirmDelete(id) {
            if (confirm("Delete promotion " + id + "?")) window.location.href = 'delete_promotion.php?promo_id=' + id;
        }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Promotion Management</h1>
        <button class="btn-add" onclick="openAddPromotionModal()">+ Add Promotion</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Promotion</h3>
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
                    <th>Amount (RM)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promotions as $promo): ?>
                <tr>
                    <td><?= $promo['PROMO_ID']; ?></td>
                    <td><strong><?= $promo['PROMO_NAME']; ?></strong></td>
                    <td style="max-width: 250px; font-size: 0.85em;"><?= $promo['PROMO_DESC']; ?></td>
                    <td>
                        <small>From: <?= date('d M Y', strtotime($promo['START_DATE'])); ?></small><br>
                        <small>To: <?= date('d M Y', strtotime($promo['END_DATE'])); ?></small>
                    </td>
                    <td style="color:#f44336; font-weight:700;">RM <?= number_format($promo['PROMO_AMOUNT'], 2); ?></td>
                    <td>
                        <button class="btn-edit" onclick="openEditPromotionModal('<?= $promo['PROMO_ID']; ?>', '<?= addslashes($promo['PROMO_NAME']); ?>', '<?= addslashes($promo['PROMO_DESC']); ?>', '<?= $promo['START_DATE']; ?>', '<?= $promo['END_DATE']; ?>', '<?= $promo['PROMO_AMOUNT']; ?>')">Edit</button>
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
            <h2>New Promotion</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_promotion.php" method="post">
                <label>Promotion ID</label>
                <input type="text" name="promoID" value="<?= $next_promo_id; ?>" readonly style="background:#f5f5f5;">

                <label>Promotion Name</label>
                <input type="text" name="promoName" required placeholder="e.g. End of Year Clearance">

                <label>Description</label>
                <input type="text" name="promoDesc" required placeholder="Brief description">

                <div class="input-row">
                    <div style="flex:1;"><label>Start Date</label><input type="date" name="startDate" required></div>
                    <div style="flex:1;"><label>End Date</label><input type="date" name="endDate" required></div>
                </div>

                <label>Discount (RM)</label>
                <input type="number" step="0.01" name="promoAmount" required placeholder="0.00">

                <button type="submit" class="modal-btn-full">Add Promotion</button>
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
                <label>Promotion Name</label><input type="text" id="editPromo_Name" name="promoName" required>
                <label>Description</label><input type="text" id="editPromo_Desc" name="promoDesc" required>
                <div class="input-row">
                    <div style="flex:1;"><label>Start Date</label><input type="date" id="editStart_Date" name="startDate" required></div>
                    <div style="flex:1;"><label>End Date</label><input type="date" id="editEnd_Date" name="endDate" required></div>
                </div>
                <label>Discount (RM)</label><input type="number" step="0.01" id="editPromo_Amount" name="promoAmount" required>
                <button type="submit" class="btn-edit modal-btn-full">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>