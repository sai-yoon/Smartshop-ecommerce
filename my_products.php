<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'seller') {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - SmartShop</title>
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
<body class="bg-gray-100 font-sans min-h-screen">
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
    <div class="w-full max-w-6xl mx-auto mt-12 px-4 pb-10">
        <div class="bg-white shadow-xl rounded-xl p-6">
            <h1 class="text-3xl font-bold text-teal-700 mb-4">🛍️ My Products</h1>

            <div class="mb-6 flex flex-wrap gap-4">
                <a href="add_product.php"
                   class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow font-semibold transition">
                    ➕ Add New Product
                </a>
                <a href="seller_dashboard.php"
                   class="text-teal-600 hover:underline font-medium">
                    ← Back to Dashboard
                </a>
            </div>

            <?php if (!$products): ?>
                <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md shadow">
                    You haven't added any products yet.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <?php foreach ($products as $p): ?>
                        <div class="bg-gray-50 p-4 rounded-lg shadow-md border border-gray-200">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                                     class="w-full h-48 object-cover rounded-md mb-3">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 rounded-md mb-3 flex items-center justify-center text-gray-500">
                                    No Image
                                </div>
                            <?php endif; ?>

                            <h2 class="text-xl font-bold text-gray-800 mb-1"><?= htmlspecialchars($p['name']) ?></h2>
                            <p class="text-teal-700 font-semibold mb-3">$<?= number_format($p['price'], 2) ?></p>

                            <div class="flex gap-3 text-sm">
                                <a href="edit_product.php?id=<?= $p['id'] ?>"
                                   class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded shadow transition">
                                    ✏️ Edit
                                </a>
                                <a href="delete_product.php?id=<?= $p['id'] ?>"
                                   onclick="return confirm('Are you sure?');"
                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow transition">
                                    🗑️ Delete
                                </a>
                                <a href="product.php?id=<?= $p['id'] ?>"
                                   class="bg-teal-500 hover:bg-teal-600 text-white px-3 py-1 rounded shadow transition">
                                    🔍 View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
