<?php
session_start();
// Clear all session variables
$_SESSION = array();
// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goodbye! | Bunga Empat Co.</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --pink-soft: #ffdeeb;
            --pink-dark: #ff85a1;
            --text-color: #7d5a5a;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--pink-soft);
            background-image: radial-gradient(#ffb6c1 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Quicksand', sans-serif;
            color: var(--text-color);
            overflow: hidden;
        }

        .logout-card {
            background: white;
            padding: 50px 30px;
            border-radius: 30px;
            box-shadow: 0 10px 25px rgba(255, 133, 161, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 4px solid white;
            position: relative;
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .icon-container {
            font-size: 60px;
            margin-bottom: 20px;
            display: inline-block;
            animation: wave 2s infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
        }

        h1 {
            color: var(--pink-dark);
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            color: #bfa2a2;
            margin-bottom: 30px;
        }

        .loader {
            width: 40px;
            height: 40px;
            border: 4px solid var(--pink-soft);
            border-top: 4px solid var(--pink-dark);
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .back-link {
            text-decoration: none;
            color: var(--pink-dark);
            font-weight: 700;
            font-size: 14px;
            border-bottom: 2px dashed var(--pink-dark);
            transition: opacity 0.3s;
        }

        .back-link:hover {
            opacity: 0.7;
        }
    </style>
    
    <meta http-equiv="refresh" content="3;url=login.php">
</head>
<body>

<div class="logout-card">
    <div class="icon-container">👋🌸</div>
    <h1>See You Soon!</h1>
    <p>You have been safely logged out.<br>Rest well, hard worker!</p>
    
    <div class="loader"></div>
    <p style="font-size: 12px;">Redirecting you to login page...</p>
    
    <a href="login.php" class="back-link">Click here if you aren't redirected</a>
</div>

</body>
</html>