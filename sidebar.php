<?php
// Dapatkan nama fail semasa (contoh: dashboard.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>

<div class="sidebar">
    <div class="logo-container">
        <img src="img/logo.png" alt="Logo" style="width: 150px; height: 150px; object-fit: contain;" onerror="this.onerror=null; this.src='default_logo.png';">
    </div>
    
    <h2>Bunga Empat Co.</h2>
    
    <ul>
        <li>
            <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">dashboard</span> Dashboard
            </a>
        </li>
        <li>
            <a href="product_mgmt.php" class="<?= ($current_page == 'product_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">inventory_2</span> Products
            </a>
        </li>
        <li>
            <a href="stock_mgmt.php" class="<?= ($current_page == 'stock_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">package_2</span> Stock
            </a>
        </li>
        <li>
            <a href="sales_mgmt.php" class="<?= ($current_page == 'sales_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">payments</span> Sales
            </a>
        </li>
        <li>
            <a href="customer_mgmt.php" class="<?= ($current_page == 'customer_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">group</span> Customers
            </a>
        </li>
        <li>
            <a href="promotion_mgmt.php" class="<?= ($current_page == 'promotion_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">sell</span> Promotions
            </a>
        </li>
        <li>
            <a href="staff_mgmt.php" class="<?= ($current_page == 'staff_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">badge</span> Staff
            </a>
        </li>
        <li>
            <a href="supplier_mgmt.php" class="<?= ($current_page == 'supplier_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">local_shipping</span> Suppliers
            </a>
        </li>
        <li>
            <a href="supplier_order.php" class="<?= ($current_page == 'supplier_order.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">shopping_cart_checkout</span> Order Products
            </a>
        </li>
        <li>
            <a href="branch_mgmt.php" class="<?= ($current_page == 'branch_mgmt.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">storefront</span> Branches
            </a>
        </li>
        <li class="logout-item">
            <a href="logout.php">
                <span class="material-symbols-outlined">logout</span> Logout
            </a>
        </li>
    </ul>
</div>