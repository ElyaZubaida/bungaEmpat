<?php
session_start();
include 'db_connection.php'; 
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Please fill up all of the form.";
    } else {
        // Query hanya untuk Staff
        $query = "SELECT STAFF_ID, STAFF_NAME, STAFF_PASSWORD FROM STAFF 
                  WHERE (STAFF_EMAIL = :usr OR STAFF_USERNAME = :usr)";

        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ":usr", $username);
        
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            die("Database error: " . $e['message']);
        }

        $user = oci_fetch_assoc($stid);

        if ($user) {
            $db_pass = $user['STAFF_PASSWORD'];
            
            // Semakan katalaluan pelbagai format
            if (($password) === $db_pass || password_verify($password, $db_pass) || $password === $db_pass) {
                $_SESSION['staff_id'] = $user['STAFF_ID'];
                $_SESSION['staff_name'] = $user['STAFF_NAME'];
                $_SESSION['role'] = 'staff';
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Sorry, wrong password.";
            }
        } else {
            $error_message = "Username not found.";
        }
        oci_free_statement($stid);
    }
    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 Bunga Empat Co. | Staff Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <style>
        :root {
            --pink-soft: #ffdeeb;
            --pink-dark: #ff85a1;
            --purple-soft: #f3e5f5;
            --cream: #fff9f9;
            --text-color: #7d5a5a;
        }

        body.login-body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--pink-soft);
            /* Cute background pattern */
            background-image: radial-gradient(#ffb6c1 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Quicksand', sans-serif;
            color: var(--text-color);
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 30px; /* Super rounded corners */
            box-shadow: 0 10px 25px rgba(255, 133, 161, 0.2);
            width: 100%;
            max-width: 380px;
            text-align: center;
            border: 4px solid white;
            position: relative;
        }

        /* Floating decoration "ears" */
        .login-card::before {
            content: '🌸';
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 40px;
            background: white;
            border-radius: 50%;
            padding: 5px;
        }

        .login-header h1 {
            font-size: 28px;
            margin: 0;
            color: var(--pink-dark);
            font-weight: 700;
        }

        .login-header p {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #bfa2a2;
            margin-top: 5px;
        }

        .login-form-container {
            margin-top: 30px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            margin-left: 10px;
            color: var(--pink-dark);
        }

        .input-group input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid var(--pink-soft);
            border-radius: 20px;
            font-family: 'Quicksand', sans-serif;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background-color: var(--cream);
        }

        .input-group input:focus {
            border-color: var(--pink-dark);
            box-shadow: 0 0 8px rgba(255, 133, 161, 0.2);
        }

        .login-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 25px;
            background: linear-gradient(135deg, #ff85a1, #fd79a8);
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 10px;
        }

        .login-submit:hover {
            transform: scale(1.03);
            box-shadow: 0 5px 15px rgba(255, 133, 161, 0.4);
        }

        .error-msg {
            background-color: #fff0f0;
            color: #ff5e5e;
            padding: 10px;
            border-radius: 15px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ffdada;
        }

        /* Small bouncy animation */
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .login-header h1 {
            animation: bounce 2s infinite ease-in-out;
        }

        /* Bonus: Falling Petals Animation */
@keyframes fall {
    0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
}

.login-body::after {
    content: '🌸';
    position: fixed;
    top: -10%;
    left: 10%;
    animation: fall 10s linear infinite;
    font-size: 20px;
    z-index: -1;
}

.login-body::before {
    content: '✨';
    position: fixed;
    top: -10%;
    right: 20%;
    animation: fall 7s linear infinite;
    animation-delay: 2s;
    font-size: 15px;
    z-index: -1;
}
    </style>
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-header">
        <h1>Bunga Empat Co.</h1>
        <p>STAFF'S PORTAL</p>
    </div>

    <div class="login-form-container">
        <?php if ($error_message): ?>
            <div class="error-msg">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 8px;">error</span>
                <?= $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div style="margin-bottom: 20px; color: #bfa2a2; font-weight: bold; font-size: 14px;">
                Welcome Back, Sweetie! ✨
            </div>

            <div class="input-group">
                <label>STAFF USERNAME</label>
                <input type="text" name="username" required placeholder="Your username...">
            </div>

            <div class="input-group">
                <label>PASSWORD</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="login-submit">LOG IN 🎀</button>
        </form>
        <div class="login-link">
        Still not part of our family yet ? <a href="signup.php">Sign up here!</a>
    </div>
    </div>
</div>

</body>
</html>