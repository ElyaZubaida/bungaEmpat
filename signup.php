<?php
session_start();
include 'db_connection.php'; 

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['staff_name']);
    $username = trim($_POST['staff_username']);
    $password = $_POST['staff_password']; 
    $phone    = trim($_POST['staff_phone']);
    $email    = trim($_POST['staff_email']);
    $category = $_POST['staff_category'];

    if (empty($name) || empty($username) || empty($password) || empty($email)) {
        $error_message = "Please fill in all required fields! ✨";
    } elseif (strlen($password) > 20) {
        $error_message = "Password is too long! Max 20 characters. 🎀";
    } else {
        // --- 1. GENERATE THE NEXT STAFF_ID ---
        // Using NVL to handle empty tables and ensure we always get a value
        $id_query = "SELECT NVL(MAX(TO_NUMBER(SUBSTR(STAFF_ID, 3))), 001) AS MAX_ID FROM STAFF";
        $id_stid = oci_parse($conn, $id_query);
        
        if (oci_execute($id_stid)) {
            $row = oci_fetch_assoc($id_stid);
            // If the row exists (which it always will due to MAX/NVL), calculate next ID
            $next_num = $row['MAX_ID'] + 1;
        } else {
            $next_num = 001; // Fallback
        }
        $formatted_num = str_pad($next_num, 3, "0", pad_type: STR_PAD_LEFT);
        $new_staff_id = "ST" . $formatted_num;
        oci_free_statement($id_stid);

        // --- 1.5 CHECK IF EMAIL ALREADY EXISTS ---
        $check_query = "SELECT COUNT(*) AS TOTAL FROM STAFF WHERE STAFF_EMAIL = :email";
        $check_stid = oci_parse($conn, $check_query);
        oci_bind_by_name($check_stid, ":email", $email);
        oci_execute($check_stid);
        
        $check_row = oci_fetch_assoc($check_stid);
        
        if ($check_row && $check_row['TOTAL'] > 0) {
            $error_message = "Oops! This email is already registered. Try logging in! 🌸";
            oci_free_statement($check_stid);
        } else {
            oci_free_statement($check_stid);

            // --- 2. PREPARE THE INSERT ---
            $salary = 0.00; 
            
            $query = "INSERT INTO STAFF (
                        STAFF_ID, STAFF_NAME, STAFF_USERNAME, STAFF_PASSWORD, 
                        STAFF_PHONE, STAFF_EMAIL, STAFF_CATEGORY, STAFF_SALARY
                      ) VALUES (
                        :sid, :name, :usr, :pass, :phone, :email, :cat, :sal
                      )";

            $stid = oci_parse($conn, $query);

            oci_bind_by_name($stid, ":sid", $new_staff_id);
            oci_bind_by_name($stid, ":name", $name);
            oci_bind_by_name($stid, ":usr", $username);
            oci_bind_by_name($stid, ":pass", $password);
            oci_bind_by_name($stid, ":phone", $phone);
            oci_bind_by_name($stid, ":email", $email);
            oci_bind_by_name($stid, ":cat", $category);
            oci_bind_by_name($stid, ":sal", $salary);

            $result = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);

            if ($result) {
                $success_message = "Yay! Welcome to the family. Staff ID: **$new_staff_id** 🎀";
            } else {
                $e = oci_error($stid);
                $error_message = "Database error: " . $e['message'];
            }
            oci_free_statement($stid);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 Join the Family | Bunga Empat Co.</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pink-soft: #ffdeeb;
            --pink-dark: #ff85a1;
            --cream: #fff9f9;
            --text-color: #7d5a5a;
        }

        body {
            margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh;
            background-color: var(--pink-soft);
            background-image: radial-gradient(#ffb6c1 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Quicksand', sans-serif; color: var(--text-color);
        }

        .signup-card {
            background: white; padding: 35px; border-radius: 30px;
            box-shadow: 0 10px 25px rgba(255, 133, 161, 0.2);
            width: 100%; max-width: 500px; text-align: center; border: 4px solid white; margin: 20px;
        }

        .signup-header h1 { font-size: 26px; margin: 0; color: var(--pink-dark); }
        .signup-header p { font-size: 13px; color: #bfa2a2; margin-bottom: 25px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left; }
        .full-width { grid-column: span 2; }

        .input-group label { display: block; font-size: 11px; font-weight: 700; margin-bottom: 5px; margin-left: 10px; color: var(--pink-dark); text-transform: uppercase; }
        
        .input-group input, .input-group select {
            width: 100%; padding: 12px 15px; border: 2px solid var(--pink-soft); border-radius: 15px;
            font-family: 'Quicksand', sans-serif; outline: none; box-sizing: border-box; background-color: var(--cream);
        }

        .signup-submit {
            width: 100%; padding: 14px; border: none; border-radius: 20px;
            background: linear-gradient(135deg, #ff85a1, #fd79a8);
            color: white; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 20px; transition: 0.3s;
        }

        .signup-submit:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(255, 133, 161, 0.3); }

        .msg { padding: 10px; border-radius: 15px; font-size: 13px; margin-bottom: 15px; }
        .error { background: #fff0f0; color: #ff5e5e; border: 1px solid #ffdada; }
        .success { background: #f0fff4; color: #2ecc71; border: 1px solid #d1f7d1; }
    </style>
</head>
<body>

<div class="signup-card">
    <div class="signup-header">
        <h1>Welcome to the Family! 🎀</h1>
        <p>A new ID will be assigned automatically ✨</p>
    </div>

    <?php if ($error_message): ?> <div class="msg error"><?= $error_message; ?></div> <?php endif; ?>
    <?php if ($success_message): ?> <div class="msg success"><?= $success_message; ?></div> <?php endif; ?>

    <form action="" method="POST">
        <div class="form-grid">
            <div class="input-group full-width">
                <label>Full Name</label>
                <input type="text" name="staff_name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="staff_username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <label>Category</label>
                <select name="staff_category" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="Manager">Manager</option>
                    <option value="Asst Manager">Asst Manager</option>
                    <option value="Cashier">Cashier</option>
                    <option value="Stock Clerk">Stock Clerk</option>
                </select>
            </div>

            <div class="input-group">
                <label>Phone</label>
                <input type="text" name="staff_phone" placeholder="012-3456789">
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="staff_email" placeholder="email@example.com" required>
            </div>

            <div class="input-group full-width">
                <label>Password</label>
                <input type="password" name="staff_password" placeholder="Letters and numbers" required>
            </div>
        </div>

        <button type="submit" class="signup-submit">JOIN THE TEAM ✨</button>
    </form>

    <div style="margin-top:20px; font-size:13px;">
        Already a staff? <a href="login.php" style="color:var(--pink-dark); text-decoration:none; font-weight:700;">Login here!</a>
    </div>
</div>

</body>
</html>