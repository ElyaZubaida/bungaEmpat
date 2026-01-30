<?php
include 'db_connection.php';

/**
 * SUMMARY CARDS QUERY
 */
$countQuery = "SELECT 
                COUNT(*) AS TOTAL_CUST,
                NVL(SUM(CUST_LOYALTYPOINTS), 0) AS TOTAL_POINTS
               FROM CUSTOMER";
$countStid = oci_parse($conn, $countQuery);
oci_execute($countStid);
$counts = oci_fetch_assoc($countStid);

/**
 * GENERATE NEXT CUSTOMER ID FOR MODAL
 */
$id_query = "SELECT MAX(TO_NUMBER(SUBSTR(CUST_ID, 3))) AS MAX_VAL FROM CUSTOMER WHERE CUST_ID LIKE 'C-%'";
$id_stid = oci_parse($conn, $id_query);
oci_execute($id_stid);
$id_row = oci_fetch_assoc($id_stid);
$latest_num = $id_row['MAX_VAL'];
$next_num = ($latest_num) ? $latest_num + 1 : 3001;
$next_cust_id = "C-" . $next_num;

/**
 * DINA'S COMPLEX QUERY (TOP SPENDERS PER BRANCH)
 */
$queryTopSpenders = "SELECT 
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

$stidTopSpender = oci_parse($conn, $queryTopSpenders);
oci_execute($stidTopSpender);
$top_spenders = [];
while ($row = oci_fetch_assoc($stidTopSpender)) { 
    $top_spenders[] = $row; 
}

/**
 * STANDARD CUSTOMER DISPLAY
 */
$queryStandard = "SELECT CUST_ID, CUST_NAME, CUST_EMAIL, CUST_PHONE, CUST_LOYALTYPOINTS, TO_CHAR(CUST_DATEREGISTERED, 'YYYY-MM-DD') AS CUST_DATE 
                  FROM CUSTOMER ORDER BY CUST_ID ASC";

$stidStd = oci_parse($conn, $queryStandard);
oci_execute($stidStd);
$customers = [];
while ($row = oci_fetch_assoc($stidStd)) { 
    $customers[] = $row; 
}

oci_free_statement($id_stid);
oci_free_statement($countStid);
oci_free_statement($stidTopSpender);
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
            const spenderView = document.getElementById('spender-view');
            const title = document.getElementById('view-title');

            if (viewType === 'SPENDERS') {
                standardView.style.display = 'none';
                spenderView.style.display = 'block';
                title.innerText = "Top Spenders per Branch (Report)";
            } else {
                spenderView.style.display = 'none';
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
            <small style="display:block; color:#888;">Switch to General List</small>
        </div>

        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Total Loyalty Points</h3>
            <span class="stat-number" style="color: #4CAF50;"><?= number_format($counts['TOTAL_POINTS']); ?></span>
            <small style="display:block; color:#888;">Accumulated Points</small>
        </div>

        <div class="stat-card" onclick="showView('SPENDERS')" style="border-left-color: #4361ee; cursor:pointer;">
            <h3>Top Spenders</h3>
            <span class="stat-number" style="color: #4361ee;"><?= count($top_spenders); ?></span>
            <small style="display:block; color:#888; font-weight: 600;">Switch to Top Spenders Report</small>
        </div>
    </div>

    <h2 id="view-title" style="font-size: 1.1em; color: #555; margin-bottom: 15px;">General Customer List</h2>

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
                                '<?= $cust['CUST_DATE']; ?>'
                            )">Edit</button>
                            <button class="btn-delete" onclick="confirmDelete('<?= $cust['CUST_ID']; ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="spender-view" class="table-container" style="display:none;">
        <table>
            <thead style="background: #fdf6e3;">
                <tr>
                    <th>Branch Name</th>
                    <th>Top Spender Name</th>
                    <th>Total Spent (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_spenders as $ts): ?>
                <tr>
                    <td><strong><?= $ts['CAWANGAN']; ?></strong></td>
                    <td style="color: #fd79a8; font-weight: 600;"> <?= $ts['TOP_STUDENT']; ?></td>
                    <td style="font-weight: bold;">RM <?= $ts['TOTAL_SPENT']; ?></td>
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
                <label>Customer ID</label>
                <input type="text" name="custID" value="<?= $next_cust_id; ?>" readonly style="background:#f5f5f5;">

                <label>Name</label>
                <input type="text" name="custName" required placeholder="e.g. Siti Sarah">

                <label>Email</label>
                <input type="email" name="custEmail" required placeholder="example@email.com">

                <label>Phone</label>
                <input type="text" name="custPhone" required placeholder="e.g. 012-3456789">

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <div style="flex: 1;">
                        <label>Initial Points</label>
                        <input type="text" value="0" readonly style="background:#f9f9f9; color:#888;">
                    </div>
                    <div style="flex: 1;">
                        <label>Registration Date</label>
                        <input type="text" value="<?= date('d/m/Y'); ?>" readonly style="background:#f9f9f9; color:#888;">
                    </div>
                </div>

                <button type="submit" class="modal-btn-full" style="margin-top: 20px;">Register Customer</button>
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
                
                <label>Name</label>
                <input type="text" id="editCust_Name" name="custName" required placeholder="Update name">

                <label>Email</label>
                <input type="email" id="editCust_Email" name="custEmail" required placeholder="Update email">

                <label>Phone</label>
                <input type="text" id="editCust_Phone" name="custPhone" required placeholder="Update phone">

                <label>Loyalty Points</label>
                <input type="number" id="editCust_LoyaltyPoints" name="custLoyaltyPoints" placeholder="Current points">

                <label>Registration Date</label>
                <input type="date" id="editCust_DateRegistered" name="custDateRegistered" required>

                <button type="submit" class="btn-edit modal-btn-full">Update Customer</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>