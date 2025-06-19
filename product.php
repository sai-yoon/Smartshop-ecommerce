<?php
require 'db.php';
session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Product ID missing.";
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, u.full_name AS seller_name FROM products p 
                       JOIN users u ON p.seller_id = u.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - SmartShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
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
<body class="bg-gray-50 text-muted-black font-sans min-h-screen flex flex-col">

<!-- NAVBAR -->
<header class="border-b border-gray-200 px-8 py-5 flex justify-between items-center bg-white shadow-sm">
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

    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Login</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="signup.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Sign Up</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
    <?php else: ?>
        <a href="logout.php" class="relative group text-red-600 hover:text-red-700 font-semibold">
            <span class="transition-colors duration-300 group-hover:text-red-700">Logout</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-red-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
    <?php endif; ?>
</nav>


</header>

<!-- MAIN CONTENT -->
<main class="flex-grow px-6 py-12 max-w-6xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm p-8 md:flex md:gap-10">
        <!-- Product Image -->
        <div class="md:w-1/2 mb-6 md:mb-0">
            <div class="w-full aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                <img src="<?= ($product['image'] && file_exists($product['image'])) ? htmlspecialchars($product['image']) : 'default_product.png' ?>" 
                     alt="Product Image" class="w-full h-full object-contain">
            </div>
        </div>

        <!-- Product Info -->
        <div class="md:w-1/2 flex flex-col">
            <h2 class="text-3xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($product['name']) ?></h2>
            <p class="text-gray-700 leading-relaxed mb-4"><strong>Description:</strong><br><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p class="text-2xl font-semibold text-main-teal mb-4">$<?= number_format($product['price'], 2) ?></p>
            <p class="text-sm text-gray-600 mb-6"><strong>Seller:</strong> <?= htmlspecialchars($product['seller_name']) ?></p>

            <!-- Conditional Add to Cart -->
            <?php if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller'): ?>
                <form action="add_to_cart.php" method="POST" class="flex flex-col gap-3">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <label class="font-medium text-gray-700">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1"
                           class="w-24 border border-gray-300 rounded px-3 py-1 text-center focus:outline-none focus:ring-2 focus:ring-main-teal">
                    <button type="submit"
                            class="mt-4 bg-main-teal text-white font-semibold py-2 rounded-lg hover:bg-opacity-90 transition">
                        🛒 Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <p class="text-sm text-gray-500 italic">Sellers cannot add products to cart.</p>
            <?php endif; ?>

            <!-- Back to Products -->
            <a href="products.php" class="mt-6 inline-block text-main-teal hover:underline text-sm">← Back to Products</a>
        </div>
    </div>
</main>

<!-- FOOTER -->
<footer class="text-center text-sm text-gray-500 py-6">
    &copy; <?= date('Y') ?> SmartShop. All rights reserved.
</footer>

</body>
</html>

