<?php   
require 'auth.php';
require 'db.php';

// Get total items in cart
$stmtCart = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
$stmtCart->execute([$_SESSION['user_id']]);
$cartCount = $stmtCart->fetch()['total'] ?? 0;

// Get total orders
$stmtOrders = $pdo->prepare("SELECT COUNT(*) AS total FROM orders WHERE user_id = ?");
$stmtOrders->execute([$_SESSION['user_id']]);
$orderCount = $stmtOrders->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard - SmartShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'main-teal': '#008080',
                    'soft-teal': '#ccf2f2',
                    'muted-black': '#1a1a1a'
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif']
                }
            }
        }
    }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<!-- ✅ NAVBAR at the TOP -->
<header class="bg-white border-b border-gray-200 px-8 py-5 shadow-sm flex justify-between items-center">
    <h1 class="text-2xl font-semibold tracking-tight text-main-teal">SmartShop</h1>
    <nav class="flex space-x-8 text-base font-medium">
        <a href="index.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Home</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="products.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Browse</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="cart.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Cart (<?= $cartCount ?>)</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="customer_orders.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">My Orders</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="logout.php" class="relative group text-red-600 hover:text-red-700 transition">
            Logout
        </a>
    </nav>
</header>

<!-- ✅ MAIN CONTENT -->
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-xl">
    <h1 class="text-3xl font-bold text-teal-700 mb-2">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> 👋</h1>
    <p class="text-lg text-gray-600 mb-6">Here's your personalized customer dashboard</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-teal-100 p-5 rounded-lg shadow-sm">
            <h2 class="text-lg font-semibold text-teal-800">🧺 Items in Cart</h2>
            <p class="text-2xl font-bold text-teal-900 mt-2"><?= $cartCount ?></p>
        </div>

        <div class="bg-emerald-100 p-5 rounded-lg shadow-sm">
            <h2 class="text-lg font-semibold text-emerald-800">📦 Total Orders</h2>
            <p class="text-2xl font-bold text-emerald-900 mt-2"><?= $orderCount ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="products.php" class="block p-4 bg-teal-500 text-white rounded-lg shadow hover:bg-teal-600 transition">
            🛒 Browse Products
        </a>

        <a href="cart.php" class="block p-4 bg-cyan-500 text-white rounded-lg shadow hover:bg-cyan-600 transition">
            🧺 View Cart
        </a>

        <a href="customer_orders.php" class="block p-4 bg-emerald-500 text-white rounded-lg shadow hover:bg-emerald-600 transition">
            📦 My Orders
        </a>

        <a href="edit_profile.php" class="block p-4 bg-yellow-500 text-white rounded-lg shadow hover:bg-yellow-600 transition">
            ✏️ Edit Profile
        </a>

        <a href="logout.php" class="block p-4 bg-red-500 text-white rounded-lg shadow hover:bg-red-600 transition">
            🚪 Logout
        </a>
    </div>
</div>

</body>
</html>
