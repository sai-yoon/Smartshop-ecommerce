<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'customer') {
    header("Location: index.php");
    exit;
}

// Fetch all orders by the user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Get cart item count (fallback in case not implemented elsewhere)
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmt_cart = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt_cart->execute([$_SESSION['user_id']]);
    $cartCount = $stmt_cart->fetchColumn() ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - SmartShop</title>
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
<body class="bg-gray-100 min-h-screen">

    <!-- NAVBAR -->
    <header class="bg-white border-b border-gray-200 px-8 py-5 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-semibold tracking-tight text-main-teal">SmartShop</h1>
        <nav class="flex space-x-8 text-base font-medium">
            <a href="customer_dashboard.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Dashboard</span>
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
            <a href="customer_orders.php" class="relative group text-main-teal font-semibold">
                <span class="border-b-2 border-main-teal pb-0.5">My Orders</span>
            </a>
            <a href="logout.php" class="relative group text-red-600 hover:text-red-700 transition">Logout</a>
        </nav>
    </header>

    <!-- Orders Content -->
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-teal-800 mb-2">📦 My Orders</h1>
            <a href="customer_dashboard.php" class="text-teal-600 hover:underline">← Back to Dashboard</a>
        </div>

        <?php if (!$orders): ?>
            <div class="text-center bg-white p-6 rounded shadow-md">
                <p class="text-gray-600 text-lg">You haven't placed any orders yet.</p>
            </div>
        <?php else: ?>
            <?php
            $isAlt = false;
            foreach ($orders as $order):
                $bgClass = $isAlt ? 'bg-teal-100' : 'bg-teal-50';
                $isAlt = !$isAlt;
            ?>
                <div class="<?= $bgClass ?> rounded-lg shadow-md mb-6 p-5">
                    <div class="mb-3">
                        <h2 class="text-lg font-semibold text-teal-900 mb-1">
                            🧾 Order #<?= $order['id'] ?> 
                            <span class="text-sm text-teal-700">(Placed on <?= date("F j, Y", strtotime($order['created_at'])) ?>)</span>
                        </h2>
                        <p class="text-teal-800"><strong>Total:</strong> $<?= number_format($order['total'], 2) ?></p>
                        <p class="text-teal-800 text-sm">
                            <strong>Shipping:</strong> <?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['zip_code']) ?>
                        </p>
                    </div>

                    <div class="overflow-x-auto rounded">
                        <table class="w-full table-auto border border-teal-200 text-sm text-teal-900 bg-white shadow-sm">
                            <thead class="bg-teal-200 text-teal-900 font-semibold">
                                <tr>
                                    <th class="px-3 py-2 text-left">Product</th>
                                    <th class="px-3 py-2 text-center">Qty</th>
                                    <th class="px-3 py-2 text-center">Price</th>
                                    <th class="px-3 py-2 text-center">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt_items = $pdo->prepare("
                                    SELECT oi.*, p.name 
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = ?
                                ");
                                $stmt_items->execute([$order['id']]);
                                $items = $stmt_items->fetchAll();

                                foreach ($items as $item):
                                ?>
                                    <tr class="border-t border-teal-100">
                                        <td class="px-3 py-2"><?= htmlspecialchars($item['name']) ?></td>
                                        <td class="px-3 py-2 text-center"><?= $item['quantity'] ?></td>
                                        <td class="px-3 py-2 text-center">$<?= number_format($item['price'], 2) ?></td>
                                        <td class="px-3 py-2 text-center font-semibold">$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
