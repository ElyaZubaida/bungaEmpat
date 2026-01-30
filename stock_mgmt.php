<?php
include 'db_connection.php';

session_start(); // 1. Always start session first!

// 2. The Gatekeeper: Check if logged in here
if (!isset($_SESSION['staff_id'])) {
    header("Location: index.php"); 
    exit();
}
$currentStaffName = $_SESSION['staff_name'];
$currentStaffID   = $_SESSION['staff_id'];

// Fetch all stock data
$query = "SELECT s.STOCK_ID, s.PROD_ID, p.PROD_NAME, s.BRANCH_ID, b.BRANCH_NAME, 
                 s.STAFF_ID, s.STOCK_QUANTITY, s.STOCK_IN, s.STOCK_OUT 
          FROM STOCK s
          JOIN PRODUCT p ON s.PROD_ID = p.PROD_ID
          JOIN BRANCH b ON s.BRANCH_ID = b.BRANCH_ID
          ORDER BY s.STOCK_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);

$stocks = [];
$total_qty = 0;
$total_in = 0;
$total_out = 0;

while ($row = oci_fetch_assoc($stid)) {
    $stocks[] = $row;
    // Calculate totals for the cards
    $total_qty += $row['STOCK_QUANTITY'];
    $total_in += $row['STOCK_IN'];
    $total_out += $row['STOCK_OUT'];
}

// 2. FETCH LISTS FOR DROPDOWNS
$prod_list = oci_parse($conn, "SELECT PROD_ID, PROD_NAME FROM PRODUCT ORDER BY PROD_NAME");
oci_execute($prod_list);
$allProds = []; while($r = oci_fetch_assoc($prod_list)) { $allProds[] = $r; }

$branch_list = oci_parse($conn, "SELECT BRANCH_ID, BRANCH_NAME FROM BRANCH ORDER BY BRANCH_NAME");
oci_execute($branch_list);
$allBranches = []; while($r = oci_fetch_assoc($branch_list)) { $allBranches[] = $r; }

$staff_list = oci_parse($conn, "SELECT STAFF_ID, STAFF_NAME FROM STAFF ORDER BY STAFF_NAME");
oci_execute($staff_list);
$allStaff = []; while($r = oci_fetch_assoc($staff_list)) { $allStaff[] = $r; }

// 3. GENERATE NEXT STOCK ID
$id_q = "SELECT MAX(TO_NUMBER(SUBSTR(STOCK_ID, 2))) AS MAX_ID FROM STOCK";
$id_stid = oci_parse($conn, $id_q);
oci_execute($id_stid);
$id_row = oci_fetch_assoc($id_stid);
$next_stock_id = "K" . (($id_row['MAX_ID']) ? $id_row['MAX_ID'] + 1 : 10001);

$staff_map_query = "SELECT STAFF_ID, STAFF_NAME, BRANCH_ID FROM STAFF ORDER BY STAFF_NAME";
$sm_stid = oci_parse($conn, $staff_map_query);
oci_execute($sm_stid);
$staffMapping = [];
while ($row = oci_fetch_assoc($sm_stid)) {
    $staffMapping[] = $row;
}

oci_free_statement($id_stid);
oci_free_statement($stid);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function openAddStockModal() { 
            document.getElementById("addStockModal").style.display = "flex"; 
        }

        function openEditStockModal(id, prodID, prodName, branchID, branchName, lastStaffID, qty, s_in, s_out) {
    document.getElementById("editStockModal").style.display = "flex";
    
    // IDs for hidden fields
    document.getElementById("editStock_ID").value = id;
    document.getElementById("hidden_ProdID").value = prodID;
    document.getElementById("hidden_BranchID").value = branchID;

    // Mapping details to locked inputs
    document.getElementById("display_StockID_Edit").value = id;
    document.getElementById("display_BranchName_Edit").value = branchName + " (" + branchID + ")";
    document.getElementById("display_ProdName_Edit").value = prodName + " (" + prodID + ")";
    
    // Display the Previous Staff ID
    document.getElementById("label_LastStaffID").value = lastStaffID;

    // Editable fields
    document.getElementById("editStock_Quantity").value = qty;
    document.getElementById("editStock_In").value = s_in;
    document.getElementById("editStock_Out").value = s_out;
}
        function closeModal() {
            document.getElementById("addStockModal").style.display = "none";
            document.getElementById("editStockModal").style.display = "none";
        }

        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }

        function confirmDelete(stockID) {
            if (confirm("Permanently delete stock record " + stockID + "?")) {
                window.location.href = 'delete_stock.php?stock_id=' + stockID;
            }
        }
        const staffData = <?= json_encode($staffMapping); ?>;

function filterStaffByBranch() {
    const selectedBranch = document.getElementById("addBranchID").value;
    const staffSelect = document.getElementById("addStaffID");
    
    // Clear existing options
    staffSelect.innerHTML = '<option value="" disabled selected>Select Staff from this Branch</option>';
    
    // Filter and Add relevant staff
    staffData.forEach(staff => {
        if (staff.BRANCH_ID === selectedBranch) {
            const opt = document.createElement("option");
            opt.value = staff.STAFF_ID;
            opt.text = staff.STAFF_NAME + " (" + staff.STAFF_ID + ")";
            staffSelect.add(opt);
        }
    });
}
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div class="header-title">
        <h1 style="margin: 0;">Stock Management</h1>
        <p style="color: #888; margin: 5px 0 0 0; font-size: 0.9em;">Manage and monitor your inventory levels</p>
    </div>
    <button class="btn-add" onclick="openAddStockModal()">+ Add New Stock</button>
</div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Items in Stock</h3>
            <span class="stat-number"><?= $total_qty; ?></span>
        </div>
        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Total Stock In</h3>
            <span class="stat-number" style="color: #4CAF50;"><?= $total_in; ?></span>
        </div>
        <div class="stat-card" style="border-left-color: #f44336;">
            <h3>Total Stock Out</h3>
            <span class="stat-number" style="color: #f44336;"><?= $total_out; ?></span>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>Staff ID</th>
                    <th>Quantity</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stocks as $stockItem) : ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $stockItem['STOCK_ID']; ?></td>
                    <td>
                        <strong><?= $stockItem['PROD_NAME']; ?></strong><br>
                        <small style="color: #999;"><?= $stockItem['PROD_ID']; ?></small>
                    </td>
                    
                    <td>
                        <strong><?= $stockItem['BRANCH_NAME']; ?></strong><br>
                        <small style="color: #999;"><?= $stockItem['BRANCH_ID']; ?></small>
                    </td>
                    
                    <td><?= $stockItem['STAFF_ID']; ?></td>
                    <td style="font-weight:600;"><?= $stockItem['STOCK_QUANTITY']; ?></td>
                    <td style="color:#4CAF50;">+ <?= $stockItem['STOCK_IN']; ?></td>
                    <td style="color:#f44336;">- <?= $stockItem['STOCK_OUT']; ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditStockModal(
                                '<?= $stockItem['STOCK_ID']; ?>', 
                                '<?= $stockItem['PROD_ID']; ?>', 
                                '<?= addslashes($stockItem['PROD_NAME']); ?>', 
                                '<?= $stockItem['BRANCH_ID']; ?>', 
                                '<?= addslashes($stockItem['BRANCH_NAME']); ?>', 
                                '<?= $stockItem['STAFF_ID']; ?>', 
                                '<?= $stockItem['STOCK_QUANTITY']; ?>', 
                                '<?= $stockItem['STOCK_IN']; ?>', 
                                '<?= $stockItem['STOCK_OUT']; ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $stockItem['STOCK_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addStockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Initialize Branch Stock</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_stock.php" method="post">
                <input type="hidden" name="staffID" value="<?= $currentStaffID ?>">

                <label>Stock ID</label>
                <input type="text" name="stockID" value="<?= $next_stock_id; ?>" readonly class="locked-input">

                <label>Product</label>
                <select name="prodID" required>
                    <option value="" disabled selected>Select Product</option>
                    <?php foreach($allProds as $p): ?>
                        <option value="<?= $p['PROD_ID'] ?>"><?= $p['PROD_NAME'] ?> (<?= $p['PROD_ID'] ?>)</option>
                    <?php endforeach; ?>
                </select>

                <label>Branch Location</label>
                <select name="branchID" required>
                    <option value="" disabled selected>Select Branch</option>
                    <?php foreach($allBranches as $b): ?>
                        <option value="<?= $b['BRANCH_ID'] ?>"><?= $b['BRANCH_NAME'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Recorded By</label>
                <div class="staff-info-box">
                    <div style="font-weight: 600; color: #7d5a5a;">
                        <?= htmlspecialchars($currentStaffName) ?> (<?= htmlspecialchars($currentStaffID) ?>)
                    </div>
                </div>

                <label>Opening Quantity</label>
                <input type="number" name="stockQuantity" required value="0">

                <button type="submit" class="modal-btn-full">Initialize Stock</button>
            </form>
        </div>
    </div>
</div>

<div id="editStockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Stock Record</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_stock.php" method="post">
                <input type="hidden" id="editStock_ID" name="stockID">
                <input type="hidden" id="hidden_ProdID" name="prodID">
                <input type="hidden" id="hidden_BranchID" name="branchID">
                
                <input type="hidden" name="staffID" value="<?= $currentStaffID ?>">

                <label>Stock ID</label>
                <input type="text" id="display_StockID_Edit" readonly class="locked-input">

                <label>Staff ID</label>
                <input type="text" id="label_LastStaffID" readonly class="staff-info-box locked-input">

                <label>Branch Location</label>
                <input type="text" id="display_BranchName_Edit" readonly class="locked-input">

                <label>Product Details</label>
                <input type="text" id="display_ProdName_Edit" readonly class="locked-input">

                <label>Physical Quantity</label>
                <input type="number" id="editStock_Quantity" name="stockQuantity" required>

                <div class="input-row" style="display: flex; gap: 10px;">
                    <div style="flex: 1;"><label>Stock In (+)</label><input type="number" id="editStock_In" name="stockIn"></div>
                    <div style="flex: 1;"><label>Stock Out (-)</label><input type="number" id="editStock_Out" name="stockOut"></div>
                </div>

                <button type="submit" class="btn-edit modal-btn-full">Update Record</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>