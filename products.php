<?php 
require 'db.php';
session_start();

// Fetch products with seller info
$stmt = $pdo->query("SELECT p.*, u.full_name AS seller_name 
                     FROM products p
                     JOIN users u ON p.seller_id = u.id
                     ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Products - SmartShop</title>
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
    <header class="border-b border-gray-200 px-8 py-5 flex justify-between items-center bg-white">
        <h1 class="text-2xl font-semibold tracking-tight">SmartShop</h1>
        <nav class="flex space-x-8 text-base font-medium">
            <a href="index.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Home</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="products.php" class="relative group text-main-teal font-semibold">
                <span>Browse</span>
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-main-teal"></span>
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
                <a href="logout.php" class="relative group text-red-500 hover:text-red-600">
                    Logout
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- PRODUCTS SECTION -->
    <main class="flex-grow px-6 py-12 max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-main-teal mb-8">🛍️ All Products</h2>

        <?php if (empty($products)): ?>
            <p class="text-center text-gray-600">No products available at the moment.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white border border-teal-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition flex flex-col">
                        <a href="product.php?id=<?= $product['id'] ?>" class="block mb-4">
                            <div class="w-full h-48 bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                                <img src="<?= ($product['image'] && file_exists($product['image'])) ? htmlspecialchars($product['image']) : 'default_product.png' ?>" 
                                     alt="Product Image" class="w-full h-full object-contain">
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mt-3 line-clamp-1"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="text-gray-700"><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
                            <p class="text-sm text-gray-600"><strong>Seller:</strong> <?= htmlspecialchars($product['seller_name']) ?></p>
                        </a>

                        <?php if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller'): ?>
                            <form action="add_to_cart.php" method="POST" class="mt-auto">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="w-full bg-main-teal hover:bg-opacity-90 text-white font-semibold py-2 rounded-lg transition">
                                    🛒 Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="mt-auto text-center text-sm text-gray-500 italic">Sellers cannot add to cart.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer class="text-center text-sm text-gray-500 py-6">
        &copy; <?= date('Y') ?> SmartShop. All rights reserved.
    </footer>

</body>
</html>
