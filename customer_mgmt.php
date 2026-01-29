<?php
include 'db_connection.php';

// --- 1. SQL QUERY FOR SUMMARY CARDS (Standard Counts) ---
$countQuery = "SELECT 
                COUNT(*) AS TOTAL_CUST,
                NVL(SUM(CUST_LOYALTYPOINTS), 0) AS TOTAL_POINTS
               FROM CUSTOMER";
$countStid = oci_parse($conn, $countQuery);
oci_execute($countStid);
$counts = oci_fetch_assoc($countStid);

// --- 5. DINA COMPLEX QUERY ---
// Identifies the top spender for every individual branch
$queryChampions = "SELECT 
                    b.Branch_Name AS CAWANGAN, 
                    c.Cust_Name AS TOP_STUDENT, 
                    TO_CHAR(SUM(ps.PS_SubPrice), '99,999.00') AS TOTAL_SPENT
                  FROM Branch b
                  JOIN Staff st ON b.Branch_ID = st.Branch_ID
                  JOIN Sale s ON st.Staff_ID = s.Staff_ID
                  JOIN Customer c ON s.Cust_ID = c.Cust_ID
                  JOIN Product_Sale ps ON s.Sale_ID = ps.Sale_ID
                  GROUP BY b.Branch_Name, c.Cust_Name, b.Branch_ID
                  HAVING SUM(ps.PS_SubPrice) >= ALL (
                    SELECT SUM(ps2.PS_SubPrice)
                    FROM Staff st2
                    JOIN Sale s2 ON st2.Staff_ID = s2.Staff_ID
                    JOIN Product_Sale ps2 ON s2.Sale_ID = ps2.Sale_ID
                    WHERE st2.Branch_ID = b.Branch_ID
                    GROUP BY s2.Cust_ID
                  )
                  ORDER BY b.Branch_Name";

// --- 3. STANDARD CUSTOMER DISPLAY ---
$queryStandard = "SELECT CUST_ID, CUST_NAME, CUST_EMAIL, CUST_PHONE, CUST_LOYALTYPOINTS, CUST_DATEREGISTERED 
                  FROM CUSTOMER ORDER BY CUST_ID ASC";

// Execute Champion Data
$stidChamp = oci_parse($conn, $queryChampions);
oci_execute($stidChamp);
$champions = [];
while ($row = oci_fetch_assoc($stidChamp)) { $champions[] = $row; }

// Execute Standard Data
$stidStd = oci_parse($conn, $queryStandard);
oci_execute($stidStd);
$customers = [];
while ($row = oci_fetch_assoc($stidStd)) { $customers[] = $row; }

oci_free_statement($countStid);
oci_free_statement($stidChamp);
oci_free_statement($stidStd);
oci_close($conn);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management | Bunga Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        function showView(viewType) {
            const standardView = document.getElementById('standard-view');
            const championView = document.getElementById('champion-view');
            const title = document.getElementById('view-title');

            if (viewType === 'CHAMPIONS') {
                standardView.style.display = 'none';
                championView.style.display = 'block';
                title.innerText = "Top Spenders per Branch";
            } else {
                championView.style.display = 'none';
                standardView.style.display = 'block';
                title.innerText = "General Customer List";
            }
        }

        function openAddCustomerModal() { 
            document.getElementById("addCustomerModal").style.display = "flex"; 
        }

        function openEditCustomerModal(id, name, email, phone, loyaltyPoints, dateRegistered) {
            document.getElementById("editCustomerModal").style.display = "flex";
            document.getElementById("editCust_ID").value = id;
            document.getElementById("editCust_Name").value = name;
            document.getElementById("editCust_Email").value = email;
            document.getElementById("editCust_Phone").value = phone;
            document.getElementById("editCust_LoyaltyPoints").value = loyaltyPoints;
            document.getElementById("editCust_DateRegistered").value = dateRegistered;
        }

        function closeModal() {
            document.getElementById("addCustomerModal").style.display = "none";
            document.getElementById("editCustomerModal").style.display = "none";
        }

        window.onclick = function(event) { if (event.target.className === 'modal') closeModal(); }

        function confirmDelete(custID) {
            if (confirm("Permanently delete customer " + custID + "?")) {
                window.location.href = 'delete_customer.php?cust_id=' + custID;
            }
        }
    </script>
</head>
<body>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Customer Management</h1>
        <button class="btn-add" onclick="openAddCustomerModal()">+ Add Customer</button>
    </div>

    <div class="section-divider"></div>

    <div class="stats-grid">
        <div class="stat-card" onclick="showView('STANDARD')" style="cursor:pointer;">
            <h3>Total Customers</h3>
            <span class="stat-number" style="color: #fd79a8;"><?= $counts['TOTAL_CUST']; ?></span>
            <small style="display:block; color:#888;">Show All Items</small>
        </div>

        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Total Loyalty Points</h3>
            <span class="stat-number" style="color: #4CAF50;"><?= number_format($counts['TOTAL_POINTS']); ?></span>
        </div>

        <div class="stat-card" onclick="showView('CHAMPIONS')" style="border-left-color: #4361ee; cursor:pointer;">
            <h3>TOP SPENDERS</h3>
            <span class="stat-number" style="color: #4361ee;"><?= count($champions); ?></span>
            <small style="display:block; color:#888; font-weight: 600;">Top Spenders per Branch</small>
        </div>
    </div>

    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin-bottom: 15px;">Customer List</h2>

    <div id="standard-view" class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Loyalty Points</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $cust) : ?>
                <tr>
                    <td style="font-weight:600; color:#888;"><?= $cust['CUST_ID']; ?></td>
                    <td><?= $cust['CUST_NAME']; ?></td>
                    <td><?= $cust['CUST_EMAIL']; ?></td>
                    <td><?= $cust['CUST_PHONE']; ?></td>
                    <td style="font-weight:600; color: #4CAF50;"><?= $cust['CUST_LOYALTYPOINTS']; ?> pts</td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-edit" onclick="openEditCustomerModal(
                                '<?= $cust['CUST_ID']; ?>',
                                '<?= addslashes($cust['CUST_NAME']); ?>',
                                '<?= addslashes($cust['CUST_EMAIL']); ?>',
                                '<?= $cust['CUST_PHONE']; ?>',
                                '<?= $cust['CUST_LOYALTYPOINTS']; ?>',
                                '<?= $cust['CUST_DATEREGISTERED']; ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $cust['CUST_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="champion-view" class="table-container" style="display:none;">
        <table>
            <thead style="background: #fdf6e3;">
                <tr>
                    <th>Branch</th>
                    <th>Top Student Name</th>
                    <th>Total Spent (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($champions as $champ): ?>
                <tr>
                    <td><strong><?= $champ['CAWANGAN']; ?></strong></td>
                    <td style="color: #fd79a8; font-weight: 600;"> <?= $champ['TOP_STUDENT']; ?></td>
                    <td style="font-weight: bold;">RM <?= $champ['TOTAL_SPENT']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addCustomerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Customer</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="add_customer.php" method="post">
                <label>Name</label><input type="text" name="custName" required>
                <label>Email</label><input type="email" name="custEmail" required>
                <label>Phone</label><input type="text" name="custPhone" required>
                <label>Initial Loyalty Points</label><input type="number" name="custLoyaltyPoints" value="0">
                <label>Registration Date</label><input type="date" name="custDateRegistered" required>
                <button type="submit" class="btn-add" style="width:100%">Add Customer</button>
            </form>
        </div>
    </div>
</div>

<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Customer Details</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="edit_customer.php" method="post">
                <input type="hidden" id="editCust_ID" name="custID">
                <label>Name</label><input type="text" id="editCust_Name" name="custName" required>
                <label>Email</label><input type="email" id="editCust_Email" name="custEmail" required>
                <label>Phone</label><input type="text" id="editCust_Phone" name="custPhone" required>
                <label>Loyalty Points</label><input type="number" id="editCust_LoyaltyPoints" name="custLoyaltyPoints">
                <label>Registration Date</label><input type="date" id="editCust_DateRegistered" name="custDateRegistered" required>
                <button type="submit" class="btn-edit" style="width:100%">Update Customer</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>