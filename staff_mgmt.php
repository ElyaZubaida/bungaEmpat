<?php
include 'db_connection.php';

// --- A. FETCH AVAILABLE YEARS FROM DB ---
$yearQuery = "SELECT DISTINCT TO_CHAR(SALE_DATE, 'YYYY') AS YEAR FROM SALE ORDER BY YEAR DESC";
$yearStid = oci_parse($conn, $yearQuery);
oci_execute($yearStid);
$availableYears = [];
while ($yRow = oci_fetch_assoc($yearStid)) {
    $availableYears[] = $yRow['YEAR'];
}

// --- B. SET FILTERS ---
// Logic: default to the latest year with actual data (likely 2025)
$defaultYear = !empty($availableYears) ? $availableYears[0] : date('Y');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : $defaultYear;
$minSales = isset($_GET['min_sales']) ? (float)$_GET['min_sales'] : 0;

// --- C. FETCH GENERAL STAFF DATA (RECURSIVE) ---
$query = "SELECT s1.*, s2.STAFF_NAME AS MANAGER_NAME 
          FROM STAFF s1 
          LEFT JOIN STAFF s2 ON s1.MANAGER_ID = s2.STAFF_ID 
          ORDER BY s1.STAFF_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);
$staffs = [];
while ($row = oci_fetch_assoc($stid)) { $staffs[] = $row; }

// --- 1. GENERATE NEXT STAFF ID ---
$id_query = "SELECT MAX(TO_NUMBER(SUBSTR(STAFF_ID, 3))) AS MAX_VAL FROM STAFF";
$id_stid = oci_parse($conn, $id_query);
oci_execute($id_stid);
$id_row = oci_fetch_assoc($id_stid);
$next_staff_id = "ST" . str_pad(($id_row['MAX_VAL'] ? $id_row['MAX_VAL'] + 1 : 1), 3, "0", STR_PAD_LEFT);

// --- 2. FETCH BRANCHES ---
$br_stid = oci_parse($conn, "SELECT BRANCH_ID, BRANCH_NAME FROM BRANCH ORDER BY BRANCH_ID");
oci_execute($br_stid);
$branches = [];
while ($r = oci_fetch_assoc($br_stid)) { $branches[] = $r; }

// --- 3. FETCH MANAGERS ---
// --- 3. FETCH MANAGERS (Include Branch ID for filtering) ---
$mgr_stid = oci_parse($conn, "SELECT STAFF_ID, STAFF_NAME, BRANCH_ID 
                             FROM STAFF 
                             WHERE STAFF_CATEGORY = 'Manager' 
                             ORDER BY STAFF_NAME");
oci_execute($mgr_stid);
$managers = [];
while ($r = oci_fetch_assoc($mgr_stid)) { 
    $managers[] = $r; 
}

// --- 4. FETCH CATEGORIES & AVG SALARY FOR AUTO-POPULATE ---
$cat_query = "SELECT STAFF_CATEGORY, ROUND(AVG(STAFF_SALARY), 2) AS AVG_SAL 
              FROM STAFF GROUP BY STAFF_CATEGORY ORDER BY STAFF_CATEGORY";
$cat_stid = oci_parse($conn, $cat_query);
oci_execute($cat_stid);
$salaryMap = [];
while ($r = oci_fetch_assoc($cat_stid)) { $salaryMap[$r['STAFF_CATEGORY']] = $r['AVG_SAL']; }

oci_free_statement($id_stid);
oci_free_statement($br_stid);
oci_free_statement($mgr_stid);
oci_free_statement($cat_stid);

/**
 * AMIRA'S COMPLEX QUERY (STAFF PERFORMANCE REPORT DISPLAY)
 * Fetches staff members whose sales exceed both a minimum threshold for the selected year.
 */
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
              HAVING SUM(sa.SALE_GRANDAMOUNT) >= :min_sales
                 AND SUM(sa.SALE_GRANDAMOUNT) >= (
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
    /**
         * Toggles between the General Staff list and Performance Reports.
         * Updates the UI heading and table visibility.
         */
    function showView(viewType) {
        const generalView = document.getElementById('general-view');
        const performerView = document.getElementById('performer-view');
        const filterContainer = document.getElementById('perf-filter-container');
        const searchSection = document.getElementById('search-section');
        const title = document.getElementById('view-title');

        if (viewType === 'PERFORMERS') {
            generalView.style.display = 'none';
            searchSection.style.display = 'none';
            performerView.style.display = 'block';
            filterContainer.style.display = 'block';
            title.innerText = "Staff Performance Report";
        } else {
            performerView.style.display = 'none';
            filterContainer.style.display = 'none';
            generalView.style.display = 'block';
            searchSection.style.display = 'flex';
            title.innerText = "General Staff List";
        }
    }

    function runLiveSearch() {
        const query = document.getElementById('txtSearch').value.toLowerCase();
        const rows = document.querySelectorAll("#general-view tbody tr");
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? "" : "none";
        });
    }

    function openAddModal() { document.getElementById("addStaffModal").style.display = "flex"; }
    function openEditModal(id, name, user, pass, tel, email, cat, sal, branch, mgr) {
        document.getElementById("editStaffModal").style.display = "flex";
        document.getElementById("editStaff_ID").value = id;
        document.getElementById("editName").value = name;
        document.getElementById("editUsername").value = user;
        document.getElementById("editPassword").value = pass;
        document.getElementById("editPhone").value = tel;
        document.getElementById("editEmail").value = email;
        document.getElementById("editCategory").value = cat;
        document.getElementById("editSalary").value = sal;
        document.getElementById("editBranch_ID").value = branch;
        document.getElementById("editManager_ID").value = mgr;
    }
    function closeModal() {
        document.getElementById("addStaffModal").style.display = "none";
        document.getElementById("editStaffModal").style.display = "none";
    }
    function confirmDelete(id) {
        if(confirm("Permanently delete staff member " + id + "?")) {
            window.location.href = "delete_staff.php?staff_id=" + id;
        }
    }
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('year')) showView('PERFORMERS');
    };
    window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }

    // Map PHP array to JavaScript Object
const salaryData = <?= json_encode($salaryMap); ?>;

function updateSalary() {
    const categorySelect = document.getElementById('addStaffCat');
    const salaryInput = document.getElementById('addStaffSalary');
    const selectedCategory = categorySelect.value;

    if (salaryData[selectedCategory]) {
        salaryInput.value = salaryData[selectedCategory];
        // Visual cue that it auto-populated
        salaryInput.style.backgroundColor = "#fff9db";
        setTimeout(() => salaryInput.style.backgroundColor = "white", 500);
    }
}

function filterManagers() {
    const selectedBranch = document.getElementById('addBranchID').value;
    const managerSelect = document.getElementById('addManagerID');
    const options = managerSelect.options;

    // Reset manager selection
    managerSelect.value = "";

    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        const managerBranch = option.getAttribute('data-branch');

        // Always show the "None" option (value "")
        if (option.value === "") {
            option.style.display = "block";
            continue;
        }

        // Show only if manager's branch matches selected branch
        if (managerBranch === selectedBranch) {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    }
}

function filterManagersEdit() {
    const selectedBranch = document.getElementById('editBranch_ID').value;
    const managerSelect = document.getElementById('editManager_ID');
    const options = managerSelect.options;

    // We don't reset the value here immediately in case the initial 
    // load matches, but we show/hide based on the branch.
    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        const managerBranch = option.getAttribute('data-branch');

        if (option.value === "" || managerBranch === selectedBranch) {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    }
}

// Update your openEditModal function to trigger the filter immediately when opened
function openEditModal(id, name, user, pass, tel, email, cat, sal, branch, mgr) {
    document.getElementById("editStaffModal").style.display = "flex";
    document.getElementById("editStaff_ID").value = id;
    document.getElementById("editName").value = name;
    document.getElementById("editUsername").value = user;
    document.getElementById("editPassword").value = pass;
    document.getElementById("editPhone").value = tel;
    document.getElementById("editEmail").value = email;
    document.getElementById("editCategory").value = cat;
    document.getElementById("editSalary").value = sal;
    document.getElementById("editBranch_ID").value = branch;
    
    // Filter managers based on the branch before setting the manager value
    filterManagersEdit();
    document.getElementById("editManager_ID").value = mgr;
}

/**
 * Auto-populates salary in the EDIT modal based on category selection
 */
function updateSalaryEdit() {
    const categorySelect = document.getElementById('editCategory');
    const salaryInput = document.getElementById('editSalary');
    const selectedCategory = categorySelect.value;

    // salaryData is the JSON object created from PHP at the top of the file
    if (salaryData && salaryData[selectedCategory]) {
        salaryInput.value = salaryData[selectedCategory];
        
        // Brief visual highlight to show the value changed automatically
        salaryInput.style.backgroundColor = "#e8f5e9"; // Soft green
        setTimeout(() => {
            salaryInput.style.backgroundColor = "white";
        }, 500);
    }
}
</script>
</head>

<body>
<div class="main-content">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h1 style="margin: 0;">Staff Management</h1>
            <p style="color: #888; margin: 5px 0 0 0; font-size: 0.9em;">Core team management and analytics</p>
        </div>
        <button class="btn-add" onclick="openAddModal()">+ Add Staff</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card" onclick="showView('GENERAL')">
            <h3>Total Staff</h3>
            <span class="stat-number"><?= count($staffs); ?></span>
        </div>
        <div class="stat-card" onclick="showView('PERFORMERS')" style="border-left: 4px solid #ffd700;">
            <h3>Top Performers</h3>
            <span class="stat-number" style="color: #ffd700;"><?= count($performers); ?></span>
        </div>
    </div>

    <!-- Filters for staff performance report-->
    <div id="perf-filter-container" style="display: none; margin-bottom: 30px;">
        <div style="background: white; padding: 25px; border-radius: 20px; border: 1px solid #ffdeeb; box-shadow: 0 10px 25px rgba(255, 133, 161, 0.1);">
            <h4 style="margin: 0 0 15px 0; color: var(--pink-dark);">Filter Performance</h4>
            <form method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label style="display:block; font-size: 0.8em; font-weight:700; margin-bottom:5px;">Year</label>
                    <select name="year" class="filter-input" style="width:100%">
                        <?php foreach ($availableYears as $year): ?>
                            <option value="<?= $year ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>Year <?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="display:block; font-size: 0.8em; font-weight:700; margin-bottom:5px;">Min Revenue (RM)</label>
                    <input type="number" name="min_sales" value="<?= $minSales ?>" class="filter-input" style="width:100%">
                </div>
                <button type="submit" class="btn-analysis">Apply</button>
            </form>
        </div>
    </div>

    <div id="search-section" class="search-container">
        <input type="text" id="txtSearch" placeholder="Search by name or ID..." class="filter-input" style="flex:1;" onkeyup="runLiveSearch()">
    </div>

    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin-bottom: 15px;">General Staff List</h2>

    <div id="general-view" class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Staff Details</th><th>Contact</th><th>Category</th><th>Organization</th><th>Salary</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffs as $s): ?>
                <tr>
                    <td><?= $s['STAFF_ID']; ?></td>
                    <td><strong><?= $s['STAFF_NAME']; ?></strong><span class="sub-text">User: <?= $s['STAFF_USERNAME']; ?></span></td>
                    <td><?= $s['STAFF_EMAIL']; ?><span class="sub-text">Tel: <?= $s['STAFF_PHONE']; ?></span></td>
                    <td><?= $s['STAFF_CATEGORY']; ?></td>
                    <td>
                        <span class="sub-text">Branch: <?= $s['BRANCH_ID']; ?></span>
                        <span class="sub-text">Manager: <?= $s['MANAGER_NAME'] ? $s['MANAGER_NAME'] : '<em style="color:#ccc;">No Manager</em>'; ?></span>
                    </td>
                    <td>RM <?= number_format($s['STAFF_SALARY'], 2); ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditModal('<?= $s['STAFF_ID']; ?>', '<?= addslashes($s['STAFF_NAME']); ?>', '<?= addslashes($s['STAFF_USERNAME']); ?>', '<?= addslashes($s['STAFF_PASSWORD']); ?>', '<?= $s['STAFF_PHONE']; ?>', '<?= $s['STAFF_EMAIL']; ?>', '<?= $s['STAFF_CATEGORY']; ?>', '<?= $s['STAFF_SALARY']; ?>', '<?= $s['BRANCH_ID']; ?>', '<?= $s['MANAGER_ID']; ?>')">Edit</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- View for staff performance report-->
    <div id="performer-view" class="table-container" style="display:none;">
        <table>
            <thead style="background: #fff9e6;">
                <tr><th>Staff Name</th><th>Branch</th><th>Transactions</th><th>Total Sales (RM)</th></tr>
            </thead>
            <tbody>
                <?php if(empty($performers)): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 20px; color: #999;">No data for Year <?= $selectedYear ?>. Try changing the year.</td></tr>
                <?php endif; ?>
                <?php foreach ($performers as $p): ?>
                <tr>
                    <td><strong><?= $p['STAFF_NAME'] ?></strong></td>
                    <td><?= $p['BRANCH_NAME'] ?></td>
                    <td><?= $p['TOTAL_TRANS'] ?></td>
                    <td style="color: #2e7d32; font-weight: 700;">RM <?= number_format($p['TOTAL_SALES'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addStaffModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Staff</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_staff.php" method="post">
                <label>Staff ID</label>
                <input type="text" name="staffID" value="<?= $next_staff_id; ?>" readonly style="background:#f5f5f5;">

                <label>Staff Name</label>
                <input type="text" name="staffName" required placeholder="Full Name">
                
                <div class="input-row">
                    <div><label>Username</label><input type="text" name="staffUser" required placeholder="e.g. jdoe123"></div>
                    <div><label>Password</label><input type="password" name="staffPass" required></div>
                </div>

                <div class="input-row">
                    <div style="flex:1;"><label>Email</label><input type="email" name="staffEmail" required placeholder="staff@cloudbyte.com"></div>
                    <div style="flex:1;"><label>Phone</label><input type="text" name="staffPhone" required placeholder="e.g. 012-3456789"></div>
                </div>

                <div class="input-row">
                    <div style="flex:1;">
                        <label>Category</label>
                        <select name="staffCat" id="addStaffCat" required onchange="updateSalary()">
                            <option value="" disabled selected>Select Category</option>
                            <?php foreach ($salaryMap as $cat => $sal): ?>
                                <option value="<?= $cat; ?>"><?= $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Salary (RM)</label>
                        <input type="number" step="0.01" name="staffSalary" id="addStaffSalary" required placeholder="0.00">
                    </div>
                </div>

                <div class="input-row">
                    <div style="flex:1;">
                        <label>Branch</label>
                        <select name="branchID" id="addBranchID" required onchange="filterManagers()">
                            <option value="" disabled selected>Select Branch</option>
                            <?php foreach ($branches as $br): ?>
                                <option value="<?= $br['BRANCH_ID']; ?>"><?= $br['BRANCH_ID']; ?> - <?= $br['BRANCH_NAME']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Manager</label>
                        <select name="managerID" id="addManagerID">
                            <option value="">None (Top Level)</option>
                            <?php foreach ($managers as $mgr): ?>
                                <option value="<?= $mgr['STAFF_ID']; ?>" data-branch="<?= $mgr['BRANCH_ID']; ?>">
                                    <?= $mgr['STAFF_NAME']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="modal-btn-full">Save Member</button>
            </form>
        </div>
    </div>
</div>
<div id="editStaffModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Update Staff Profile</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_staff.php" method="post">
                <input type="hidden" id="editStaff_ID" name="staffID">
                
                <label>Full Name</label>
                <input type="text" id="editName" name="staffName" required placeholder="Update full name">
                
                <div class="input-row">
                    <div style="flex:1;"><label>Username</label><input type="text" id="editUsername" name="staffUser" required></div>
                    <div style="flex:1;"><label>Password</label><input type="text" id="editPassword" name="staffPass" required></div>
                </div>

                <div class="input-row">
                    <div style="flex:1;"><label>Email</label><input type="email" id="editEmail" name="staffEmail" required></div>
                    <div style="flex:1;"><label>Phone</label><input type="text" id="editPhone" name="staffPhone" required></div>
                </div>

                <div class="input-row">
                    <div style="flex:1;">
                        <label>Category</label>
                        <select name="staffCat" id="editCategory" required onchange="updateSalaryEdit()">
                            <?php foreach ($salaryMap as $cat => $sal): ?>
                                <option value="<?= $cat; ?>"><?= $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Salary (RM)</label>
                        <input type="number" step="0.01" id="editSalary" name="staffSalary" required placeholder="0.00">
                    </div>
                </div>

                <div class="input-row">
                    <div style="flex:1;">
                        <label>Branch</label>
                        <select name="branchID" id="editBranch_ID" required onchange="filterManagersEdit()">
                            <?php foreach ($branches as $br): ?>
                                <option value="<?= $br['BRANCH_ID']; ?>"><?= $br['BRANCH_ID']; ?> - <?= $br['BRANCH_NAME']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Manager</label>
                        <select name="managerID" id="editManager_ID">
                            <option value="">None (Top Level)</option>
                            <?php foreach ($managers as $mgr): ?>
                                <option value="<?= $mgr['STAFF_ID']; ?>" data-branch="<?= $mgr['BRANCH_ID']; ?>">
                                    <?= $mgr['STAFF_NAME']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-edit modal-btn-full">Save Changes</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>