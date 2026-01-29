<?php
include 'db_connection.php';

// --- A. NEW: FETCH AVAILABLE YEARS FROM DB ---
$yearQuery = "SELECT DISTINCT TO_CHAR(SALE_DATE, 'YYYY') AS YEAR FROM SALE ORDER BY YEAR DESC";
$yearStid = oci_parse($conn, $yearQuery);
oci_execute($yearStid);
$availableYears = [];
while ($yRow = oci_fetch_assoc($yearStid)) {
    $availableYears[] = $yRow['YEAR'];
}

// --- B. SET FILTERS ---
// Default to the latest year available in DB, otherwise current year
$defaultYear = !empty($availableYears) ? $availableYears[0] : date('Y');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : $defaultYear;
$minSales = isset($_GET['min_sales']) ? (float)$_GET['min_sales'] : 0;

// --- C. FETCH GENERAL STAFF DATA ---
$query = "SELECT * FROM STAFF ORDER BY STAFF_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);
$staffs = [];
while ($row = oci_fetch_assoc($stid)) { $staffs[] = $row; }

// --- D. FETCH TOP PERFORMERS ---
$perfQuery = "SELECT 
                s.STAFF_NAME, 
                b.BRANCH_NAME,
                COUNT(sa.SALE_ID) AS TOTAL_TRANS,
                SUM(sa.SALE_GRANDAMOUNT) AS TOTAL_SALES
              FROM STAFF s
              JOIN BRANCH b ON s.BRANCH_ID = b.BRANCH_ID
              JOIN SALE sa ON s.STAFF_ID = sa.STAFF_ID
              WHERE TO_CHAR(sa.SALE_DATE, 'YYYY') = :year
              GROUP BY s.STAFF_ID, s.STAFF_NAME, b.BRANCH_NAME, b.BRANCH_ID
              HAVING SUM(sa.SALE_GRANDAMOUNT) > :min_sales
                 AND SUM(sa.SALE_GRANDAMOUNT) > (
                     SELECT AVG(SUM(sa2.SALE_GRANDAMOUNT))
                     FROM SALE sa2
                     JOIN STAFF s2 ON sa2.STAFF_ID = s2.STAFF_ID
                     WHERE s2.BRANCH_ID = b.BRANCH_ID
                     AND TO_CHAR(sa2.SALE_DATE, 'YYYY') = :year
                     GROUP BY s2.STAFF_ID
                 )
              ORDER BY SUM(sa.SALE_GRANDAMOUNT) DESC";

$stidPerf = oci_parse($conn, $perfQuery);
oci_bind_by_name($stidPerf, ":year", $selectedYear);
oci_bind_by_name($stidPerf, ":min_sales", $minSales);
oci_execute($stidPerf);
$performers = [];
while ($row = oci_fetch_assoc($stidPerf)) { $performers[] = $row; }

oci_free_statement($yearStid);
oci_free_statement($stid);
oci_free_statement($stidPerf);
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
        function openAddModal() { document.getElementById("addStaffModal").style.display = "flex"; }
        
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

        function closeModal() {
            document.getElementById("addStaffModal").style.display = "none";
            document.getElementById("editStaffModal").style.display = "none";
        }

        // Toggles views and shows/hides the filter form
        function showView(viewType) {
            const generalView = document.getElementById('general-view');
            const performerView = document.getElementById('performer-view');
            const filterForm = document.getElementById('perf-filter');
            const title = document.getElementById('view-title');

            if (viewType === 'PERFORMERS') {
                generalView.style.display = 'none';
                performerView.style.display = 'block';
                filterForm.style.display = 'flex'; // Form appears below cards
                title.innerText = "Staff Performance Analysis (Above Branch Average)";
            } else {
                performerView.style.display = 'none';
                filterForm.style.display = 'none'; // Form is hidden
                generalView.style.display = 'block';
                title.innerText = "General Staff List";
            }
        }

        // Persist the view if a search was performed
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('year')) {
                showView('PERFORMERS');
            }
        }

        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }
    </script>
</head>

<body>
<div class="main-content">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h1 style="margin: 0;">Staff Management</h1>
            <p style="color: #888; margin: 5px 0 0 0; font-size: 0.9em;">Manage team and analyze performance</p>
        </div>
        <button class="btn-add" onclick="openAddModal()">+ Add Staff</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card" onclick="showView('GENERAL')" style="cursor:pointer;">
            <h3>Total Employees</h3>
            <span class="stat-number"><?= count($staffs); ?></span>
        </div>
        <div class="stat-card" onclick="showView('PERFORMERS')" style="cursor:pointer; border-left-color: #ffd700;">
            <h3>Top Performers</h3>
            <span class="stat-number" style="color: #ffd700;"><?= count($performers); ?></span>
            <small style="display:block; color: #888;">Above Branch Average</small>
        </div>
    </div>

    <div id="perf-filter-container" style="display: none; margin-bottom: 30px;">
        <div style="background: white; padding: 25px; border-radius: 20px; border: 1px solid #ffdeeb; box-shadow: 0 10px 25px rgba(255, 133, 161, 0.1);">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; color: var(--pink-dark); font-size: 1.1em;">Performance Parameters</h4>
                <p style="margin: 0; font-size: 0.8em; color: #aaa;">Data automatically fetched from existing sales records</p>
            </div>
            <form method="GET" style="display: flex; gap: 20px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.8em; font-weight: 700; margin-bottom: 8px; color: #7d5a5a;">Select Sales Year</label>
                    <select name="year" style="width: 100%; padding: 12px; border: 2px solid #ffdeeb; border-radius: 12px; outline: none; font-family: 'Quicksand'; background: white;">
                        <?php foreach ($availableYears as $year): ?>
                            <option value="<?= $year ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>
                                Year <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if(empty($availableYears)): ?>
                            <option disabled>No sales data found</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.8em; font-weight: 700; margin-bottom: 8px; color: #7d5a5a;">Min Sales (RM)</label>
                    <input type="number" name="min_sales" value="<?= $minSales ?>" 
                           style="width: 100%; padding: 12px; border: 2px solid #ffdeeb; border-radius: 12px; outline: none; font-family: 'Quicksand';">
                </div>
                <button type="submit" class="btn-add" style="margin-bottom: 0; padding: 12px 30px; width: auto; border-radius: 12px; cursor:pointer;">
                    Apply
                </button>
            </form>
        </div>
    </div>

    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin-bottom: 15px;">General Staff List</h2>

    <div id="general-view" class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Contact</th><th>Category</th><th>Salary</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffs as $staff): ?>
                <tr>
                    <td><?= $staff['STAFF_ID']; ?></td>
                    <td><strong><?= $staff['STAFF_NAME']; ?></strong></td>
                    <td><?= $staff['STAFF_EMAIL']; ?></td>
                    <td><?= $staff['STAFF_CATEGORY']; ?></td>
                    <td>RM <?= number_format($staff['STAFF_SALARY'], 2); ?></td>
                    <td>
                        <button class="btn-edit" onclick="openEditModal('<?= $staff['STAFF_ID']; ?>', '<?= addslashes($staff['STAFF_NAME']); ?>', '<?= addslashes($staff['STAFF_USERNAME']); ?>', '<?= addslashes($staff['STAFF_PASSWORD']); ?>', '<?= $staff['STAFF_PHONE']; ?>', '<?= $staff['STAFF_EMAIL']; ?>', '<?= $staff['STAFF_CATEGORY']; ?>', '<?= $staff['STAFF_SALARY']; ?>', '<?= $staff['BRANCH_ID']; ?>', '<?= $staff['MANAGER_ID']; ?>')">Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="performer-view" class="table-container" style="display:none;">
        <table>
            <thead style="background: #fff9e6;">
                <tr>
                    <th>Staff Name</th>
                    <th>Branch</th>
                    <th style="text-align: center;">Transactions</th>
                    <th style="text-align: right;">Total Sales (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($performers)): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 30px; color: #888;">No high-performing staff met the RM <?= number_format($minSales) ?> threshold for <?= $selectedYear ?>.</td></tr>
                <?php endif; ?>
                <?php foreach ($performers as $p): ?>
                <tr>
                    <td style="color: var(--pink-dark); font-weight: 700;"><?= $p['STAFF_NAME'] ?></td>
                    <td><?= $p['BRANCH_NAME'] ?></td>
                    <td style="text-align: center; font-weight: 600;"><?= $p['TOTAL_TRANS'] ?></td>
                    <td style="text-align: right; color: #2e7d32; font-weight: 700; font-family: 'Poppins';">RM <?= number_format($p['TOTAL_SALES'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    // --- 1. Toggle View & Show/Hide Filter ---
    function showView(viewType) {
            const generalView = document.getElementById('general-view');
            const performerView = document.getElementById('performer-view');
            const filterContainer = document.getElementById('perf-filter-container');
            const title = document.getElementById('view-title');

            if (viewType === 'PERFORMERS') {
                generalView.style.display = 'none';
                performerView.style.display = 'block';
                filterContainer.style.display = 'block';
                title.innerText = "Performance Analysis (Above Branch Average)";
            } else {
                performerView.style.display = 'none';
                filterContainer.style.display = 'none';
                generalView.style.display = 'block';
                title.innerText = "General Staff List";
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('year')) {
                showView('PERFORMERS');
            }
        };

    // --- 3. Modal Controls (Add/Edit) ---
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

    function closeModal() {
        document.getElementById("addStaffModal").style.display = "none";
        document.getElementById("editStaffModal").style.display = "none";
    }

    // Tutup modal kalau klik luar kotak
    window.onclick = function(event) { 
        if (event.target.className === 'modal') closeModal(); 
    }
</script>
</body>
</html>