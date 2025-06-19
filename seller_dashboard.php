<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'seller') {
    header("Location: index.php");
    exit;
}

// Count seller’s products
$stmtProducts = $pdo->prepare("SELECT COUNT(*) AS total FROM products WHERE seller_id = ?");
$stmtProducts->execute([$_SESSION['user_id']]);
$productCount = $stmtProducts->fetch()['total'];

// Count total orders of their products
$stmtOrders = $pdo->prepare("
    SELECT COUNT(DISTINCT oi.order_id) AS total
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
");
$stmtOrders->execute([$_SESSION['user_id']]);
$orderCount = $stmtOrders->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard - SmartShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
</head>
<body class="bg-gray-100 min-h-screen font-sans text-muted-black">

<!-- NAVBAR -->
<header class="bg-white border-b border-gray-200 px-8 py-5 shadow-sm flex justify-between items-center">
    <h1 class="text-2xl font-semibold tracking-tight">SmartShop</h1>
    <nav class="flex space-x-8 text-base font-medium">
        <a href="index.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Home</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="products.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Browse</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="my_products.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">My Products</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="logout.php" class="relative group text-red-600 hover:text-red-700 transition">
            Logout
        </a>
    </nav>
</header>

<!-- DASHBOARD CONTENT -->
<main class="max-w-5xl mx-auto mt-10 px-4 py-8 bg-white shadow-md rounded-2xl">
    <h2 class="text-3xl font-bold text-main-teal mb-2">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> 👋</h2>
    <p class="text-lg text-gray-600 mb-8">Here's your seller command center. Manage your store with confidence.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-teal-100 p-5 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-teal-800">📦 Total Products Listed</h3>
            <p class="text-3xl font-bold text-teal-900 mt-2"><?= $productCount ?></p>
        </div>

        <div class="bg-emerald-100 p-5 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-emerald-800">🛍️ Orders Received</h3>
            <p class="text-3xl font-bold text-emerald-900 mt-2"><?= $orderCount ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="add_product.php" class="block p-4 bg-teal-500 text-white rounded-lg shadow hover:bg-teal-600 transition text-center font-medium">
            ➕ Add New Product
        </a>

        <a href="my_products.php" class="block p-4 bg-cyan-500 text-white rounded-lg shadow hover:bg-cyan-600 transition text-center font-medium">
            📋 Manage My Products
        </a>

        <a href="edit_profile.php" class="block p-4 bg-yellow-500 text-white rounded-lg shadow hover:bg-yellow-600 transition text-center font-medium">
            ✏️ Edit Profile
        </a>

        <a href="logout.php" class="block p-4 bg-red-500 text-white rounded-lg shadow hover:bg-red-600 transition text-center font-medium">
            🚪 Logout
        </a>
    </div>
</main>

<!-- FOOTER -->
<footer class="text-center text-sm text-gray-500 py-6 mt-10">
    &copy; <?= date('Y') ?> SmartShop. All rights reserved.
</footer>

</body>
</html>
